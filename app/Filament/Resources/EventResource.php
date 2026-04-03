<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Models\Event;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationGroup = 'Events';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Shown only when editing an event that is part of a recurring series
                Forms\Components\Placeholder::make('series_info')
                    ->label('Recurring Series')
                    ->content('This event is part of a recurring series. When saving, you will be asked whether to apply changes to just this event or this and all future occurrences.')
                    ->visible(fn (?Event $record): bool => (bool) $record?->isPartOfSeries())
                    ->columnSpanFull()
                    ->visibleOn('edit'),

                Forms\Components\Section::make('Event Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\RichEditor::make('description')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('organizer')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('website')
                            ->url()
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Schedule')
                    ->schema([
                        Forms\Components\Toggle::make('is_all_day')
                            ->label('All Day Event')
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, bool $state): void {
                                if ($state) {
                                    $start = $get('start_date');
                                    $end   = $get('end_date');
                                    $set('start_date', $start
                                        ? \Carbon\Carbon::parse($start)->startOfDay()->format('Y-m-d H:i:s')
                                        : now()->startOfDay()->format('Y-m-d H:i:s'));
                                    $set('end_date', ($end ?: $start)
                                        ? \Carbon\Carbon::parse($end ?: $start)->setTime(23, 59)->format('Y-m-d H:i:s')
                                        : now()->setTime(23, 59)->format('Y-m-d H:i:s'));
                                }
                            })
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('start_date')
                            ->required()
                            ->label('Start Date & Time')
                            ->displayFormat(fn (Forms\Get $get): string => $get('is_all_day') ? 'Y-m-d' : 'Y-m-d H:i')
                            ->seconds(false),

                        Forms\Components\DateTimePicker::make('end_date')
                            ->after('start_date')
                            ->label('End Date & Time')
                            ->displayFormat(fn (Forms\Get $get): string => $get('is_all_day') ? 'Y-m-d' : 'Y-m-d H:i')
                            ->seconds(false),
                    ])->columns(2),

                Forms\Components\Section::make('Classification')
                    ->schema([
                        Forms\Components\Select::make('location_id')
                            ->relationship('location', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->disk('public')
                            ->directory('events')
                            ->maxSize(2048),
                    ]),

                // Recurrence section
                Forms\Components\Section::make('Recurrence')
                    ->schema([
                        Forms\Components\Toggle::make('is_recurring')
                            ->label('This is a recurring event')
                            ->live()
                            ->default(false)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('recurrence_type')
                            ->label('Repeats')
                            ->options([
                                'daily'           => 'Daily',
                                'weekly'          => 'Weekly (same weekday)',
                                'biweekly'        => 'Every other week (same weekday)',
                                'monthly_date'    => 'Monthly (same date)',
                                'monthly_weekday' => 'Monthly (same weekday position)',
                            ])
                            ->live()
                            ->dehydrated(false)
                            ->visible(fn (Forms\Get $get): bool => (bool) $get('is_recurring'))
                            ->required(fn (Forms\Get $get): bool => (bool) $get('is_recurring'))
                            ->afterStateUpdated(function (Forms\Set $set, Forms\Get $get, ?string $state): void {
                                if (! $state || ! $startDate = $get('start_date')) {
                                    return;
                                }
                                $date = \Carbon\Carbon::parse($startDate);
                                if ($state === 'weekly' || $state === 'biweekly') {
                                    $set('day_of_week', $date->dayOfWeek);
                                }
                                if ($state === 'monthly_weekday') {
                                    $set('weekday', $date->dayOfWeek);
                                    $set('week_of_month', (int) ceil($date->day / 7));
                                }
                            })
                            ->helperText(fn (Forms\Get $get): ?string => match($get('recurrence_type')) {
                                'monthly_date' => 'Months that do not have this date will be skipped.',
                                'monthly_weekday' => 'Months where this weekday position does not exist will be skipped.',
                                default => null,
                            }),

                        Forms\Components\Select::make('day_of_week')
                            ->label('Day of week')
                            ->options([
                                0 => 'Sunday',
                                1 => 'Monday',
                                2 => 'Tuesday',
                                3 => 'Wednesday',
                                4 => 'Thursday',
                                5 => 'Friday',
                                6 => 'Saturday',
                            ])
                            ->dehydrated(false)
                            ->visible(fn (Forms\Get $get): bool =>
                                (bool) $get('is_recurring') &&
                                in_array($get('recurrence_type'), ['weekly', 'biweekly'])
                            )
                            ->required(fn (Forms\Get $get): bool =>
                                (bool) $get('is_recurring') &&
                                in_array($get('recurrence_type'), ['weekly', 'biweekly'])
                            ),

                        Forms\Components\Select::make('week_of_month')
                            ->label('Week of month')
                            ->options([
                                1 => '1st',
                                2 => '2nd',
                                3 => '3rd',
                                4 => '4th',
                                5 => '5th',
                            ])
                            ->dehydrated(false)
                            ->visible(fn (Forms\Get $get): bool =>
                                (bool) $get('is_recurring') &&
                                $get('recurrence_type') === 'monthly_weekday'
                            )
                            ->required(fn (Forms\Get $get): bool =>
                                (bool) $get('is_recurring') &&
                                $get('recurrence_type') === 'monthly_weekday'
                            ),

                        Forms\Components\Select::make('weekday')
                            ->label('Weekday')
                            ->options([
                                0 => 'Sunday',
                                1 => 'Monday',
                                2 => 'Tuesday',
                                3 => 'Wednesday',
                                4 => 'Thursday',
                                5 => 'Friday',
                                6 => 'Saturday',
                            ])
                            ->dehydrated(false)
                            ->visible(fn (Forms\Get $get): bool =>
                                (bool) $get('is_recurring') &&
                                $get('recurrence_type') === 'monthly_weekday'
                            )
                            ->required(fn (Forms\Get $get): bool =>
                                (bool) $get('is_recurring') &&
                                $get('recurrence_type') === 'monthly_weekday'
                            ),

                        Forms\Components\DatePicker::make('recurrence_end_date')
                            ->label('Repeat until (inclusive)')
                            ->minDate(fn (Forms\Get $get) => $get('start_date') ?? now())
                            ->dehydrated(false)
                            ->visible(fn (Forms\Get $get): bool => (bool) $get('is_recurring'))
                            ->required(fn (Forms\Get $get): bool => (bool) $get('is_recurring')),
                    ])->columns(2),

                Forms\Components\Section::make('Status')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'pending_verification' => 'Pending Verification',
                                'pending_approval' => 'Pending Approval',
                                'pending_payment' => 'Pending Payment',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('draft')
                            ->reactive(),

                        Forms\Components\Textarea::make('rejection_reason')
                            ->rows(3)
                            ->hidden(fn (Forms\Get $get): bool => $get('status') !== 'rejected'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'approved',
                        'warning' => fn (string $state): bool => in_array($state, ['pending_approval', 'pending_payment']),
                        'info' => 'pending_verification',
                        'danger' => 'rejected',
                        'gray' => 'draft',
                    ]),

                Tables\Columns\IconColumn::make('is_premium')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('location.city')
                    ->label('Location')
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Views')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('upcoming_only')
                    ->label('Hide past events')
                    ->query(fn (Builder $query) => $query->where('start_date', '>=', now()->startOfDay()))
                    ->default(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_verification' => 'Pending Verification',
                        'pending_approval' => 'Pending Approval',
                        'pending_payment' => 'Pending Payment',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                Tables\Filters\TernaryFilter::make('is_premium')
                    ->label('Premium'),

                Tables\Filters\SelectFilter::make('location_id')
                    ->relationship('location', 'name')
                    ->label('Location')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $records->each(fn (Event $record) => $record->update(['status' => 'approved']));

                            try {
                                Cache::tags(['events'])->flush();
                            } catch (\Exception $e) {
                                // Cache driver may not support tags
                            }
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('reject')
                        ->label('Reject')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([
                            Forms\Components\Textarea::make('rejection_reason')
                                ->label('Rejection Reason')
                                ->required()
                                ->rows(3),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $records->each(fn (Event $record) => $record->update([
                                'status' => 'rejected',
                                'rejection_reason' => $data['rejection_reason'],
                            ]));
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'),
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }
}
