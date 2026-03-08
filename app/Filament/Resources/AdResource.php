<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AdResource\Pages;
use App\Models\Ad;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AdResource extends Resource
{
    protected static ?string $model = Ad::class;

    protected static ?string $navigationIcon = 'heroicon-o-megaphone';

    protected static ?string $navigationGroup = 'Advertising';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ad Details')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->disk('public')
                            ->directory('ads')
                            ->maxSize(2048),

                        Forms\Components\TextInput::make('url')
                            ->url()
                            ->maxLength(255),
                    ]),

                Forms\Components\Section::make('Placement')
                    ->schema([
                        Forms\Components\Select::make('placement')
                            ->options([
                                'native' => 'Native',
                                'sidebar' => 'Sidebar',
                                'leaderboard' => 'Leaderboard',
                                'carousel' => 'Carousel',
                                'geo' => 'Geo-targeted',
                            ])
                            ->required()
                            ->reactive(),

                        Forms\Components\Select::make('location_id')
                            ->relationship('location', 'name')
                            ->searchable()
                            ->preload()
                            ->hidden(fn (Forms\Get $get): bool => $get('placement') !== 'geo')
                            ->required(fn (Forms\Get $get): bool => $get('placement') === 'geo'),
                    ])->columns(2),

                Forms\Components\Section::make('Schedule & Display')
                    ->schema([
                        Forms\Components\Toggle::make('active')
                            ->default(true),

                        Forms\Components\DatePicker::make('start_date'),

                        Forms\Components\DatePicker::make('end_date')
                            ->afterOrEqual('start_date'),

                        Forms\Components\TextInput::make('order')
                            ->numeric()
                            ->default(0),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('placement')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('active')
                    ->sortable(),

                Tables\Columns\TextColumn::make('start_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order')
                    ->sortable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListAds::route('/'),
            'create' => Pages\CreateAd::route('/create'),
            'edit' => Pages\EditAd::route('/{record}/edit'),
        ];
    }
}
