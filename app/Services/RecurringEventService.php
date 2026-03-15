<?php

namespace App\Services;

use App\Models\Event;
use App\Models\RecurringEventSeries;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class RecurringEventService
{
    public const MAX_OCCURRENCES = 52;

    /**
     * Create a RecurringEventSeries and generate all occurrence Event records.
     * The $template event becomes occurrence #1.
     */
    public function generateSeries(Event $template, array $recurrenceData): RecurringEventSeries
    {
        $series = RecurringEventSeries::create([
            'recurrence_type'     => $recurrenceData['recurrence_type'],
            'day_of_week'         => $recurrenceData['day_of_week'] ?? null,
            'week_of_month'       => $recurrenceData['week_of_month'] ?? null,
            'weekday'             => $recurrenceData['weekday'] ?? null,
            'recurrence_end_date' => $recurrenceData['recurrence_end_date'],
            'occurrence_count'    => 0,
        ]);

        // Link the template event (occurrence #1) to the series
        $template->update(['recurring_series_id' => $series->id]);

        // Calculate remaining dates (strictly after template's start_date)
        $dates = $this->calculateOccurrenceDates(
            startAfter: $template->start_date,
            recurrenceType: $recurrenceData['recurrence_type'],
            recurrenceData: $recurrenceData,
            endDate: Carbon::parse($recurrenceData['recurrence_end_date'])->endOfDay(),
            maxOccurrences: self::MAX_OCCURRENCES - 1
        );

        $this->bulkCreateOccurrences($template, $series, $dates);

        $series->update(['occurrence_count' => count($dates) + 1]);

        return $series;
    }

    /**
     * Propagate field changes to all occurrences in the same series on or after $pivot's start_date.
     * Date fields, slugs, and identity fields are never propagated.
     */
    public function updateFutureOccurrences(Event $pivot, array $changes): void
    {
        if (! $pivot->recurring_series_id) {
            return;
        }

        $propagatable = collect($changes)->except([
            'id', 'slug', 'start_date', 'end_date',
            'recurring_series_id', 'views_count',
            'stripe_payment_id', 'is_paid',
            'verified_at', 'verification_token',
            'created_at', 'updated_at',
        ])->all();

        if (empty($propagatable)) {
            return;
        }

        Event::withoutObservers(function () use ($pivot, $propagatable) {
            Event::where('recurring_series_id', $pivot->recurring_series_id)
                 ->where('start_date', '>=', $pivot->start_date)
                 ->where('id', '!=', $pivot->id)
                 ->update($propagatable);
        });

        Cache::tags(['events', 'filter-options'])->flush();
    }

    /**
     * Calculate occurrence dates strictly after $startAfter, up to $maxOccurrences,
     * stopping when a date exceeds $endDate.
     *
     * @return Carbon[]
     */
    private function calculateOccurrenceDates(
        Carbon $startAfter,
        string $recurrenceType,
        array  $recurrenceData,
        Carbon $endDate,
        int    $maxOccurrences
    ): array {
        $dates  = [];
        $cursor = $startAfter->copy();

        while (count($dates) < $maxOccurrences) {
            $next = match ($recurrenceType) {
                'daily'           => $this->nextDaily($cursor),
                'weekly'          => $this->nextWeekly($cursor),
                'monthly_date'    => $this->nextMonthlyDate($cursor, $startAfter->day),
                'monthly_weekday' => $this->nextMonthlyWeekday(
                    $cursor,
                    (int) $recurrenceData['week_of_month'],
                    (int) $recurrenceData['weekday']
                ),
            };

            // null means the date doesn't exist in this month — skip to next month
            if ($next === null) {
                $cursor = $cursor->copy()->addMonthNoOverflow()->startOfMonth();
                continue;
            }

            if ($next->gt($endDate)) {
                break;
            }

            $dates[] = $next;
            $cursor  = $next;
        }

        return $dates;
    }

    private function nextDaily(Carbon $from): Carbon
    {
        return $from->copy()->addDay();
    }

    private function nextWeekly(Carbon $from): Carbon
    {
        // Adds exactly 7 days — weekday is preserved automatically
        return $from->copy()->addWeek();
    }

    /**
     * Advance by one month, keeping the same day-of-month.
     * Returns null if the target month doesn't have that day (e.g. Jan 31 → Feb).
     */
    private function nextMonthlyDate(Carbon $from, int $dayOfMonth): ?Carbon
    {
        $candidate = $from->copy()->addMonthNoOverflow();

        // addMonthNoOverflow clamps to last valid day — if it shifted, skip this month
        if ($candidate->day !== $dayOfMonth) {
            return null;
        }

        return $candidate;
    }

    /**
     * Find the nth occurrence of $weekday in the next month.
     * Returns null if that occurrence doesn't exist (e.g. 5th Monday).
     */
    private function nextMonthlyWeekday(Carbon $from, int $weekOfMonth, int $weekday): ?Carbon
    {
        // Move to the first day of the next month
        $firstOfMonth = $from->copy()->addMonthNoOverflow()->startOfMonth();

        // Find the first occurrence of $weekday in that month
        // Carbon::next() moves forward — if $firstOfMonth IS the weekday, use it directly
        if ($firstOfMonth->dayOfWeek === $weekday) {
            $firstOccurrence = $firstOfMonth->copy();
        } else {
            $firstOccurrence = $firstOfMonth->copy()->next($weekday);
        }

        // Verify we haven't overshot into the following month
        if ($firstOccurrence->month !== $firstOfMonth->month) {
            return null;
        }

        // Add (weekOfMonth - 1) full weeks to reach the nth occurrence
        $target = $firstOccurrence->copy()->addWeeks($weekOfMonth - 1);

        // Confirm it's still in the same month (e.g. "5th Monday" may not exist)
        if ($target->month !== $firstOfMonth->month) {
            return null;
        }

        // Preserve the original time-of-day from $from
        $target->setTime($from->hour, $from->minute, $from->second);

        return $target;
    }

    /**
     * Bulk-create Event records for each date, cloning the template.
     * Wraps creates in withoutObservers() to avoid N cache flushes; flushes once at the end.
     */
    private function bulkCreateOccurrences(
        Event $template,
        RecurringEventSeries $series,
        array $dates
    ): void {
        if (empty($dates)) {
            return;
        }

        // Duration in seconds between start and end (null if no end_date)
        $duration = $template->end_date
            ? $template->start_date->diffInSeconds($template->end_date)
            : null;

        // Fields to clone — exclude identity, date, and payment/verification fields
        $cloneFields = collect($template->toArray())->except([
            'id', 'slug', 'start_date', 'end_date',
            'recurring_series_id', 'created_at', 'updated_at',
            'views_count', 'stripe_payment_id', 'is_paid',
            'verified_at', 'verification_token',
        ])->all();

        Event::withoutObservers(function () use ($cloneFields, $template, $series, $dates, $duration) {
            foreach ($dates as $index => $occurrenceDate) {
                $endDate = $duration !== null
                    ? $occurrenceDate->copy()->addSeconds($duration)
                    : null;

                $slug = $this->generateUniqueSlug($template->slug, $index + 2);

                Event::create(array_merge($cloneFields, [
                    'slug'                => $slug,
                    'start_date'          => $occurrenceDate,
                    'end_date'            => $endDate,
                    'recurring_series_id' => $series->id,
                    'views_count'         => 0,
                    'stripe_payment_id'   => null,
                    'is_paid'             => false,
                    'verified_at'         => null,
                    'verification_token'  => null,
                ]));
            }
        });

        // Flush cache once after all occurrences are created
        Cache::tags(['events', 'filter-options'])->flush();
    }

    /**
     * Generate a unique slug by appending a numeric suffix and checking for collisions.
     */
    private function generateUniqueSlug(string $baseSlug, int $occurrenceNumber): string
    {
        $candidate = $baseSlug . '-' . $occurrenceNumber;
        $suffix    = $occurrenceNumber;

        while (Event::where('slug', $candidate)->exists()) {
            $suffix++;
            $candidate = $baseSlug . '-' . $suffix;
        }

        return $candidate;
    }
}
