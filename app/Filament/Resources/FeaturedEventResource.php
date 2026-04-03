<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeaturedEventResource\Pages;
use App\Models\Event;
use App\Models\FeaturedEvent;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rule;

class FeaturedEventResource extends Resource
{
    protected static ?string $model = FeaturedEvent::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';

    protected static ?string $navigationGroup = 'Events';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Select::make('event_id')
                            ->label('Event')
                            ->options(fn () =>
                                Event::approved()
                                    ->upcoming()
                                    ->with('location')
                                    ->orderBy('start_date')
                                    ->get()
                                    ->mapWithKeys(fn ($e) => [
                                        $e->id => $e->title
                                            . ' — ' . $e->start_date->format('M j, Y')
                                            . ($e->location ? ' (' . $e->location->city . ')' : ''),
                                    ])
                            )
                            ->searchable()
                            ->required()
                            ->columnSpanFull()
                            ->rules(fn (Forms\Get $get, ?FeaturedEvent $record) => [
                                Rule::unique('featured_events', 'event_id')->ignore($record?->id),
                            ])
                            ->validationMessages([
                                'unique' => 'This event already has a featured listing.',
                            ]),

                        Forms\Components\TextInput::make('order')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Forms\Components\Toggle::make('active')
                            ->default(true),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Featured From'),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Featured Until')
                            ->afterOrEqual('start_date'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('order')
            ->defaultSort('order', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('event.title')
                    ->label('Event')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('event.location.city')
                    ->label('Location')
                    ->sortable(),

                Tables\Columns\TextInputColumn::make('order')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('active')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('active'),
                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('From'),
                        Forms\Components\DatePicker::make('until')->label('Until'),
                    ])
                    ->query(fn ($query, array $data) => $query
                        ->when($data['from'], fn ($q) => $q->where('end_date', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->where('start_date', '<=', $data['until']))
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListFeaturedEvents::route('/'),
            'create' => Pages\CreateFeaturedEvent::route('/create'),
            'edit' => Pages\EditFeaturedEvent::route('/{record}/edit'),
        ];
    }
}
