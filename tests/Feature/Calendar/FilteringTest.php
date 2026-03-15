<?php

namespace Tests\Feature\Calendar;

use App\Models\Category;
use App\Models\Event;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilteringTest extends TestCase
{
    use RefreshDatabase;

    private Location $locationA;
    private Location $locationB;
    private Category $catA;
    private Category $catB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->locationA = Location::create(['name' => 'Cannes', 'city' => 'Cannes', 'state' => 'FR']);
        $this->locationB = Location::create(['name' => 'Monaco', 'city' => 'Monaco', 'state' => 'MC']);
        $this->catA      = Category::create(['name' => 'Music']);
        $this->catB      = Category::create(['name' => 'Food']);
    }

    public function test_filter_by_location(): void
    {
        $this->makeEvent('Cannes Show', $this->locationA, $this->catA);
        $this->makeEvent('Monaco Show', $this->locationB, $this->catB);

        $this->getJson(route('api.events.index', ['location' => $this->locationA->id]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Cannes Show');
    }

    public function test_filter_by_category(): void
    {
        $this->makeEvent('Rock Night', $this->locationA, $this->catA);
        $this->makeEvent('Food Fest', $this->locationA, $this->catB);

        $this->getJson(route('api.events.index', ['category' => $this->catB->id]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Food Fest');
    }

    public function test_filter_by_keyword(): void
    {
        $this->makeEvent('Jazz Evening', $this->locationA, $this->catA);
        $this->makeEvent('Art Fair', $this->locationA, $this->catB);

        $this->getJson(route('api.events.index', ['search' => 'jazz']))
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Jazz Evening');
    }

    public function test_filter_by_date_range(): void
    {
        Event::create([
            'title' => 'Near Future', 'description' => '-',
            'start_date' => now()->addDays(2), 'end_date' => now()->addDays(3),
            'status' => 'approved', 'location_id' => $this->locationA->id,
            'category_id' => $this->catA->id, 'organizer' => 'Org',
        ]);
        Event::create([
            'title' => 'Far Future', 'description' => '-',
            'start_date' => now()->addDays(30), 'end_date' => now()->addDays(31),
            'status' => 'approved', 'location_id' => $this->locationA->id,
            'category_id' => $this->catA->id, 'organizer' => 'Org',
        ]);

        $this->getJson(route('api.events.index', [
            'start' => now()->toDateString(),
            'end'   => now()->addDays(10)->toDateString(),
        ]))
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Near Future');
    }

    public function test_filter_by_premium_flag(): void
    {
        $this->makeEvent('Free Event', $this->locationA, $this->catA, false);
        $this->makeEvent('Premium Event', $this->locationA, $this->catB, true);

        $this->getJson(route('api.events.index', ['premium' => '1']))
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Premium Event');
    }

    private function makeEvent(
        string $title,
        Location $location,
        Category $category,
        bool $premium = false,
    ): Event {
        return Event::create([
            'title'       => $title,
            'description' => 'Desc',
            'start_date'  => now()->addDays(5),
            'end_date'    => now()->addDays(6),
            'status'      => 'approved',
            'is_premium'  => $premium,
            'location_id' => $location->id,
            'category_id' => $category->id,
            'organizer'   => 'Org',
        ]);
    }
}
