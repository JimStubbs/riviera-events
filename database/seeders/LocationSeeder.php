<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            ['name' => 'Miami Beach', 'city' => 'Miami Beach', 'state' => 'FL'],
            ['name' => 'Fort Lauderdale', 'city' => 'Fort Lauderdale', 'state' => 'FL'],
            ['name' => 'Boca Raton', 'city' => 'Boca Raton', 'state' => 'FL'],
            ['name' => 'West Palm Beach', 'city' => 'West Palm Beach', 'state' => 'FL'],
            ['name' => 'Hollywood', 'city' => 'Hollywood', 'state' => 'FL'],
            ['name' => 'Pompano Beach', 'city' => 'Pompano Beach', 'state' => 'FL'],
            ['name' => 'Delray Beach', 'city' => 'Delray Beach', 'state' => 'FL'],
            ['name' => 'Deerfield Beach', 'city' => 'Deerfield Beach', 'state' => 'FL'],
        ];

        foreach ($locations as $location) {
            Location::firstOrCreate(
                ['city' => $location['city'], 'state' => $location['state']],
                $location
            );
        }
    }
}
