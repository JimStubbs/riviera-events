<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\EventView;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecordEventView implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $eventId,
        public readonly string $ipAddress,
    ) {}

    public function handle(): void
    {
        $cutoff = now()->subHours(24);

        $alreadyViewed = EventView::where('event_id', $this->eventId)
            ->where('ip_address', $this->ipAddress)
            ->where('viewed_at', '>=', $cutoff)
            ->exists();

        if ($alreadyViewed) return;

        EventView::create([
            'event_id'   => $this->eventId,
            'ip_address' => $this->ipAddress,
            'viewed_at'  => now(),
        ]);

        Event::where('id', $this->eventId)->increment('views_count');
    }
}
