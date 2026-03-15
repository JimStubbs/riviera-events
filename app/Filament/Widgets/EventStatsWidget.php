<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EventStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $pending = Event::where('status', 'pending_approval')->count();
        $approved = Event::where('status', 'approved')->count();
        $premium = Event::where('is_premium', true)->count();
        $revenue = Payment::where('status', 'completed')
            ->where('paid_at', '>=', now()->subDays(30))
            ->sum('amount');

        return [
            Stat::make('Pending Approval', $pending)
                ->description('Events awaiting review')
                ->color($pending > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-clock'),

            Stat::make('Approved Events', $approved)
                ->description('Live on the calendar')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Premium Listings', $premium)
                ->description('Total premium events')
                ->color('info')
                ->icon('heroicon-o-star'),

            Stat::make('Revenue (30 days)', '$' . number_format($revenue / 100, 2))
                ->description('From premium listings')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),
        ];
    }
}
