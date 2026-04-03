<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class OrganizerDashboardController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $userId = $user->id;
        $email  = $user->email;

        // Match events owned by this user account OR submitted with their email address
        $scope = fn ($q) => $q->where('user_id', $userId)
                               ->orWhere(fn ($s) => $s->whereNull('user_id')
                                                       ->where('submitter_email', $email));

        $events = Event::query()
            ->where($scope)
            ->with(['location:id,name,city', 'category:id,name,color'])
            ->orderByDesc('created_at')
            ->paginate(15);

        $stats = [
            'total'    => Event::where($scope)->count(),
            'approved' => Event::where($scope)->where('status', 'approved')->count(),
            'pending'  => Event::where($scope)
                ->whereIn('status', ['pending_verification', 'pending_approval', 'pending_payment'])
                ->count(),
            'views'    => (int) Event::where($scope)->sum('views_count'),
        ];

        return view('dashboard.index', compact('events', 'stats'));
    }
}
