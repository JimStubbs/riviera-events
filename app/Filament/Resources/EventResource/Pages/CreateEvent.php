<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Services\RecurringEventService;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;

    /**
     * Strip recurrence fields before the Event record is saved —
     * they live on recurring_event_series, not on events.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset(
            $data['is_recurring'],
            $data['recurrence_type'],
            $data['day_of_week'],
            $data['week_of_month'],
            $data['weekday'],
            $data['recurrence_end_date'],
        );

        return $data;
    }

    /**
     * After the Event (occurrence #1) is persisted, generate remaining occurrences.
     */
    protected function afterCreate(): void
    {
        $raw = $this->form->getRawState();

        if (empty($raw['is_recurring'])) {
            return;
        }

        app(RecurringEventService::class)->generateSeries($this->record, [
            'recurrence_type'     => $raw['recurrence_type'],
            'day_of_week'         => $raw['day_of_week'] ?? null,
            'week_of_month'       => $raw['week_of_month'] ?? null,
            'weekday'             => $raw['weekday'] ?? null,
            'recurrence_end_date' => $raw['recurrence_end_date'],
        ]);
    }
}
