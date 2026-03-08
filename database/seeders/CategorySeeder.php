<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Music', 'color' => '#8B5CF6', 'icon' => 'musical-note'],
            ['name' => 'Food & Drink', 'color' => '#F59E0B', 'icon' => 'cake'],
            ['name' => 'Arts & Culture', 'color' => '#EC4899', 'icon' => 'paint-brush'],
            ['name' => 'Sports & Fitness', 'color' => '#10B981', 'icon' => 'trophy'],
            ['name' => 'Family & Kids', 'color' => '#3B82F6', 'icon' => 'face-smile'],
            ['name' => 'Nightlife', 'color' => '#1D4ED8', 'icon' => 'moon'],
            ['name' => 'Business & Networking', 'color' => '#6B7280', 'icon' => 'briefcase'],
            ['name' => 'Health & Wellness', 'color' => '#059669', 'icon' => 'heart'],
            ['name' => 'Charity & Causes', 'color' => '#DC2626', 'icon' => 'hand-raised'],
            ['name' => 'Outdoors & Adventure', 'color' => '#D97706', 'icon' => 'sun'],
            ['name' => 'Film & Media', 'color' => '#7C3AED', 'icon' => 'film'],
            ['name' => 'Holiday & Seasonal', 'color' => '#B45309', 'icon' => 'star'],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
