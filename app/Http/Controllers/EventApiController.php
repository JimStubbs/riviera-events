<?php

namespace App\Http\Controllers;

use App\Http\Resources\EventResource;
use App\Models\Category;
use App\Models\Event;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class EventApiController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = min((int) $request->integer('per_page', 20), 500);
        $filters = $request->only(['location', 'category', 'search', 'start', 'end', 'premium', 'page', 'per_page']);

        $cacheKey = 'events_api_' . md5(json_encode($filters));

        $events = Cache::remember($cacheKey, 300, function () use ($request, $perPage) {
            return Event::query()
                ->approved()
                ->upcoming()
                ->with([
                    'location:id,name,city',
                    'category:id,name,color,icon',
                ])
                ->select([
                    'id', 'title', 'slug', 'description', 'start_date', 'end_date',
                    'image', 'organizer', 'website', 'is_premium', 'is_featured', 'is_all_day', 'views_count',
                    'location_id', 'category_id',
                ])
                ->when($request->integer('location'), fn ($q, $v) => $q->where('location_id', $v))
                ->when($request->integer('category'), fn ($q, $v) => $q->where('category_id', $v))
                ->when($request->filled('search'), function ($q) use ($request) {
                    $v = $request->string('search');
                    $q->where(function ($sub) use ($v) {
                        $sub->where('title', 'like', "%{$v}%")
                            ->orWhere('description', 'like', "%{$v}%")
                            ->orWhere('organizer', 'like', "%{$v}%");
                    });
                })
                ->when($request->filled('start'), fn ($q) => $q->where('start_date', '>=', $request->string('start')))
                ->when($request->filled('end'), fn ($q) => $q->whereDate('start_date', '<=', $request->string('end')))
                ->when($request->boolean('premium'), fn ($q) => $q->premium())
                ->orderByDesc('is_premium')
                ->orderBy('start_date')
                ->paginate($perPage);
        });

        return EventResource::collection($events);
    }

    public function filterOptions(): JsonResponse
    {
        $options = Cache::remember('filter_options', 1800, function () {
            return [
                'locations' => Location::orderBy('city')
                    ->select('id', 'name', 'city', 'state')
                    ->get(),
                'categories' => Category::orderBy('name')
                    ->select('id', 'name', 'color', 'icon')
                    ->get(),
            ];
        });

        return response()->json($options);
    }
}
