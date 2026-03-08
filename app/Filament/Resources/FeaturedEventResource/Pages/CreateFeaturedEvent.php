<?php

namespace App\Filament\Resources\FeaturedEventResource\Pages;

use App\Filament\Resources\FeaturedEventResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateFeaturedEvent extends CreateRecord
{
    protected static string $resource = FeaturedEventResource::class;
}
