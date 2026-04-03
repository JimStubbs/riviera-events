<?php

namespace App\Observers;

use App\Mail\EventApprovedMail;
use App\Mail\EventRejectedMail;
use App\Models\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class EventObserver
{
    public function created(Event $event): void
    {
        $this->flushCache();
    }

    public function updated(Event $event): void
    {
        $this->flushCache();

        if ($event->wasChanged('status')) {
            Cache::forget('ics_feed');

            // Approve all sibling occurrences when one event in a series is approved
            if ($event->status === 'approved' && $event->recurring_series_id) {
                Event::where('recurring_series_id', $event->recurring_series_id)
                    ->where('id', '!=', $event->id)
                    ->where('status', '!=', 'approved')
                    ->update(['status' => 'approved']);
            }

            $recipient = $event->submitter_email ?? optional($event->user)->email;

            if ($recipient) {
                match ($event->status) {
                    'approved' => Mail::to($recipient)->queue(new EventApprovedMail($event)),
                    'rejected' => Mail::to($recipient)->queue(new EventRejectedMail($event)),
                    default    => null,
                };
            }
        }
    }

    public function deleted(Event $event): void
    {
        $this->flushCache();
        Cache::forget('ics_feed');
    }

    private function flushCache(): void
    {
        Cache::flush();
    }
}
