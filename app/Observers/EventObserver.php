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
        Cache::tags(['events', 'filter-options'])->flush();
    }
}
