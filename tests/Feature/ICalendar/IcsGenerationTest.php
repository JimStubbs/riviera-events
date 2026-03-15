<?php

namespace Tests\Feature\ICalendar;

use App\Models\Category;
use App\Models\Event;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IcsGenerationTest extends TestCase
{
    use RefreshDatabase;

    private Location $location;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->location = Location::create(['name' => 'Venue', 'city' => 'Nice', 'state' => 'FR']);
        $this->category = Category::create(['name' => 'Music']);
    }

    public function test_single_event_ics_download(): void
    {
        $event = $this->makeApprovedEvent('Jazz Night');

        $response = $this->get(route('events.ics', $event));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $this->assertStringContainsString('BEGIN:VCALENDAR', $response->getContent());
        $this->assertStringContainsString('Jazz Night', $response->getContent());
    }

    public function test_ics_for_pending_event_returns_404(): void
    {
        $event = Event::create([
            'title'       => 'Hidden Event',
            'description' => '-',
            'start_date'  => now()->addDays(5),
            'end_date'    => now()->addDays(6),
            'status'      => 'pending_approval',
            'location_id' => $this->location->id,
            'category_id' => $this->category->id,
            'organizer'   => 'Org',
        ]);

        $this->get(route('events.ics', $event))->assertStatus(404);
    }

    public function test_full_feed_returns_ics_content_type(): void
    {
        $this->makeApprovedEvent('Event A');
        $this->makeApprovedEvent('Event B');

        $response = $this->get(route('feed.ics'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/calendar; charset=utf-8');
        $this->assertStringContainsString('BEGIN:VCALENDAR', $response->getContent());
    }

    public function test_full_feed_contains_all_approved_events(): void
    {
        $this->makeApprovedEvent('Show One');
        $this->makeApprovedEvent('Show Two');

        Event::create([
            'title'       => 'Draft Event',
            'description' => '-',
            'start_date'  => now()->addDays(5),
            'end_date'    => now()->addDays(6),
            'status'      => 'draft',
            'location_id' => $this->location->id,
            'category_id' => $this->category->id,
            'organizer'   => 'Org',
        ]);

        $content = $this->get(route('feed.ics'))->getContent();

        $this->assertStringContainsString('Show One', $content);
        $this->assertStringContainsString('Show Two', $content);
        $this->assertStringNotContainsString('Draft Event', $content);
    }

    public function test_ics_contains_vevent_block(): void
    {
        $event = $this->makeApprovedEvent('Concert Night');

        $content = $this->get(route('events.ics', $event))->getContent();

        $this->assertStringContainsString('BEGIN:VEVENT', $content);
        $this->assertStringContainsString('END:VEVENT', $content);
        $this->assertStringContainsString('Concert Night', $content);
    }

    private function makeApprovedEvent(string $title): Event
    {
        return Event::create([
            'title'       => $title,
            'description' => 'Test description',
            'start_date'  => now()->addDays(5),
            'end_date'    => now()->addDays(6),
            'status'      => 'approved',
            'location_id' => $this->location->id,
            'category_id' => $this->category->id,
            'organizer'   => 'Test Org',
        ]);
    }
}
