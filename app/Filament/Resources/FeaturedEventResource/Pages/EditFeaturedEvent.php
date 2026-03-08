<?php

namespace App\Filament\Resources\FeaturedEventResource\Pages;

use App\Filament\Resources\FeaturedEventResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFeaturedEvent extends EditRecord
{
    protected static string $resource = FeaturedEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
