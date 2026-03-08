<?php

namespace App\Console\Commands;

use App\Models\EventView;
use Illuminate\Console\Command;

class CleanEventViews extends Command
{
    protected $signature = 'views:cleanup {--days=90 : Delete view records older than this many days}';

    protected $description = 'Delete event_views records older than the configured number of days';

    public function handle(): int
    {
        $days   = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $count = EventView::where('viewed_at', '<', $cutoff)->delete();

        $this->info("Deleted {$count} event view record(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
