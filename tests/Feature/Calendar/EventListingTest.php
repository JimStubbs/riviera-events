<?php

namespace Tests\Feature\Calendar;

use App\Models\Category;
use App\Models\Event;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventListingTest extends TestCase
{
    use RefreshDatabase;

    private Location $location;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->location = Location::create(['name' => 'Test Venue', 'city' => 'Nice', 'state' => 'FR']);
        $this->category = Category::create(['name' => 'Music']);
    }

    public function test_calendar_index_renders(): void
    {
        $this->get(route('calendar.index'))->assertStatus(200);
    }

    public function test_only_approved_events_appear_in_api(): void
    {
        $this->createEvent('Approved', 'approved');
        $this->createEvent('Pending', 'pending_approval');

        $this->getJson(route('api.events.index'))
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Approved');
    }

    public function test_past_events_are_excluded(): void
    {
        $this->createEvent('Past Event', 'approved', now()->subDays(5));
        $this->createEvent('Future Event', 'approved', now()->addDays(5));

        $this->getJson(route('api.events.index'))
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Future Event');
    }

    public function test_premium_events_appear_first(): void
    {
        $this->createEvent('Regular', 'approved', now()->addDays(1), false);
        $this->createEvent('Premium', 'approved', now()->addDays(2), true);

        $response = $this->getJson(route('api.events.index'))->assertStatus(200);

        $this->assertEquals('Premium', $response->json('data.0.title'));
    }

    public function test_event_detail_page_renders_for_approved_event(): void
    {
        $event = $this->createEvent('Test Show', 'approved');

        $this->get(route('events.show', $event))->assertStatus(200)->assertSee('Test Show');
    }

    public function test_event_detail_returns_404_for_pending_event(): void
    {
        $event = $this->createEvent('Hidden', 'pending_approval');

        $this->get(route('events.show', $event))->assertStatus(404);
    }

    public function test_filter_options_endpoint_returns_locations_and_categories(): void
    {
        $this->getJson(route('api.events.filter-options'))
            ->assertStatus(200)
            ->assertJsonStructure(['locations', 'categories']);
    }

    private function createEvent(
        string $title,
        string $status,
        ?\Carbon\Carbon $startDate = null,
        bool $isPremium = false,
    ): Event {
        return Event::create([
            'title'       => $title,
            'description' => 'Test description',
            'start_date'  => $startDate ?? now()->addDays(3),
            'end_date'    => ($startDate ?? now()->addDays(3))->addHour(),
            'status'      => $status,
            'is_premium'  => $isPremium,
            'location_id' => $this->location->id,
            'category_id' => $this->category->id,
            'organizer'   => 'Test Org',
        ]);
    }
}
