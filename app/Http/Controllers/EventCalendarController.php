<?php

namespace App\Http\Controllers;

use App\Jobs\RecordEventView;
use App\Models\Ad;
use App\Models\Event;
use App\Models\FeaturedEvent;
use Illuminate\View\View;

class EventCalendarController extends Controller
{
    public function index(): View
    {
        $featured = FeaturedEvent::active()
            ->with(['event:id,title,slug,description,image,start_date,end_date,location_id,category_id', 'event.location:id,city', 'event.category:id,name,color'])
            ->orderBy('order')
            ->get();

        $leaderboardAd = Ad::active()
            ->forPlacement('leaderboard')
            ->orderBy('order')
            ->first();

        return view('calendar.index', compact('featured', 'leaderboardAd'));
    }

    public function show(Event $event): View
    {
        abort_unless($event->status === 'approved', 404);

        $event->load(['location', 'category']);

        RecordEventView::dispatch($event->id, request()->ip());

        $sidebarAd = Ad::active()
            ->forPlacement('sidebar')
            ->orderBy('order')
            ->first();

        return view('calendar.show', compact('event', 'sidebarAd'));
    }
}
