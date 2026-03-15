<?php

namespace Tests\Feature\Calendar;

use App\Models\Category;
use App\Models\Event;
use App\Models\FeaturedEvent;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeaturedBannerTest extends TestCase
{
    use RefreshDatabase;

    private Location $location;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->location = Location::create(['name' => 'Venue', 'city' => 'City', 'state' => 'ST']);
        $this->category = Category::create(['name' => 'Music']);
    }

    public function test_calendar_index_includes_featured_events_data(): void
    {
        $event = $this->makePremiumEvent('Featured Show');
        FeaturedEvent::create([
            'event_id'   => $event->id,
            'order'      => 1,
            'expires_at' => now()->addDays(30),
        ]);

        $this->get(route('calendar.index'))
            ->assertStatus(200)
            ->assertSee('Featured Show');
    }

    public function test_non_premium_event_can_still_be_featured(): void
    {
        // Featured events relationship is separate from is_premium flag
        $event = Event::create([
            'title'       => 'Community Pick',
            'description' => '-',
            'start_date'  => now()->addDays(5),
            'end_date'    => now()->addDays(6),
            'status'      => 'approved',
            'is_premium'  => false,
            'location_id' => $this->location->id,
            'category_id' => $this->category->id,
            'organizer'   => 'Org',
        ]);

        FeaturedEvent::create(['event_id' => $event->id, 'order' => 1]);

        $this->assertDatabaseHas('featured_events', ['event_id' => $event->id]);
    }

    public function test_featured_event_relationship_exists(): void
    {
        $event   = $this->makePremiumEvent('Test');
        $featured = FeaturedEvent::create(['event_id' => $event->id, 'order' => 1]);

        $this->assertInstanceOf(FeaturedEvent::class, $event->fresh()->featuredEvent);
        $this->assertEquals($event->id, $featured->event_id);
    }

    private function makePremiumEvent(string $title): Event
    {
        return Event::create([
            'title'       => $title,
            'description' => '-',
            'start_date'  => now()->addDays(5),
            'end_date'    => now()->addDays(6),
            'status'      => 'approved',
            'is_premium'  => true,
            'location_id' => $this->location->id,
            'category_id' => $this->category->id,
            'organizer'   => 'Org',
        ]);
    }
}
