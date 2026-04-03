<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use App\Services\RecurringEventService;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    /**
     * Stores the user's scope choice from the recurring-event save modal.
     * Values: 'just_this' | 'this_and_future'
     */
    public string $editScope = 'just_this';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * Override the default Save button for recurring events to ask the user
     * whether the edit should affect just this occurrence or this + all future ones.
     */
    protected function getSaveFormAction(): Action
    {
        if (! $this->record->isPartOfSeries()) {
            return parent::getSaveFormAction();
        }

        return Action::make('save')
            ->label('Save changes')
            ->requiresConfirmation()
            ->modalHeading('Save recurring event')
            ->modalDescription('How should these changes be applied?')
            ->modalSubmitActionLabel('Save')
            ->form([
                Forms\Components\Radio::make('edit_scope')
                    ->label('')
                    ->options([
                        'just_this'       => 'Just this event',
                        'this_and_future' => 'This and all future events in the series',
                    ])
                    ->default('just_this')
                    ->required(),
            ])
            ->action(function (array $data): void {
                $this->editScope = $data['edit_scope'] ?? 'just_this';
                $this->save();
            });
    }

    /**
     * After saving, propagate changes to future occurrences if the user chose to.
     * getChanges() is used here (not getDirty() in beforeSave) because Filament
     * fills the model with form data—including processed file uploads—only
     * immediately before save(), so getDirty() misses file fields.
     */
    protected function afterSave(): void
    {
        if ($this->editScope !== 'this_and_future') {
            return;
        }

        if (! $this->record->isPartOfSeries()) {
            return;
        }

        $changes = $this->record->getChanges();

        if (! empty($changes)) {
            app(RecurringEventService::class)->updateFutureOccurrences(
                $this->record,
                $changes
            );
        }

        $this->editScope = 'just_this';
    }
}
