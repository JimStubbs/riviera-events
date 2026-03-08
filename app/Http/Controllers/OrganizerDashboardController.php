<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class OrganizerDashboardController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->hasAnyRole(['organizer', 'admin']), 403);

        $userId = auth()->id();

        $events = Event::query()
            ->where('user_id', $userId)
            ->with(['location:id,name,city', 'category:id,name,color'])
            ->orderByDesc('created_at')
            ->paginate(15);

        $stats = [
            'total'    => Event::where('user_id', $userId)->count(),
            'approved' => Event::where('user_id', $userId)->where('status', 'approved')->count(),
            'pending'  => Event::where('user_id', $userId)
                ->whereIn('status', ['pending_verification', 'pending_approval', 'pending_payment'])
                ->count(),
            'views'    => (int) Event::where('user_id', $userId)->sum('views_count'),
        ];

        return view('dashboard.index', compact('events', 'stats'));
    }
}
