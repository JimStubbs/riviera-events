<?php

namespace App\Console\Commands;

use App\Mail\RecurringSeriesEndingMail;
use App\Models\RecurringEventSeries;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyExpiringRecurringSeries extends Command
{
    protected $signature = 'recurring:notify-expiring';

    protected $description = 'Send expiry warning emails for recurring series with 3 or fewer future occurrences';

    public function handle(): int
    {
        $now     = Carbon::now();
        $notified = 0;

        RecurringEventSeries::whereNull('expiry_notified_at')->each(function ($series) use ($now, &$notified) {
            $futureCount = $series->events()
                ->where('start_date', '>=', $now)
                ->count();

            if ($futureCount > 3) {
                return;
            }

            $event = $series->events()
                ->whereNotNull('submitter_email')
                ->orderBy('start_date')
                ->first();

            if (! $event?->submitter_email) {
                return;
            }

            Mail::to($event->submitter_email)->send(
                new RecurringSeriesEndingMail($event, $series)
            );

            $series->update(['expiry_notified_at' => now()]);
            $notified++;
        });

        $this->info("Sent expiry notifications for {$notified} series.");

        return self::SUCCESS;
    }
}
