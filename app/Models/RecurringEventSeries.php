<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringEventSeries extends Model
{
    protected $fillable = [
        'recurrence_type',
        'day_of_week',
        'week_of_month',
        'weekday',
        'recurrence_end_date',
        'occurrence_count',
        'expiry_notified_at',
    ];

    protected function casts(): array
    {
        return [
            'recurrence_end_date' => 'date',
            'day_of_week'         => 'integer',
            'week_of_month'       => 'integer',
            'weekday'             => 'integer',
            'occurrence_count'    => 'integer',
            'expiry_notified_at'  => 'datetime',
        ];
    }

    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'recurring_series_id')
                    ->orderBy('start_date');
    }

    public function futureEvents(Carbon $from): HasMany
    {
        return $this->events()->where('start_date', '>=', $from);
    }
}
