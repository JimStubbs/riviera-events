<?php

namespace Tests\Unit;

use App\Mail\EventApprovedMail;
use App\Mail\EventRejectedMail;
use App\Models\Category;
use App\Models\Event;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EventObserverTest extends TestCase
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

    public function test_creating_event_flushes_events_cache(): void
    {
        Cache::tags(['events'])->put('test_key', 'test_value', 60);

        $this->makeEvent('New Event');

        $this->assertNull(Cache::tags(['events'])->get('test_key'));
    }

    public function test_updating_event_flushes_events_cache(): void
    {
        $event = $this->makeEvent('Event');

        Cache::tags(['events'])->put('test_key', 'test_value', 60);

        $event->update(['title' => 'Updated Event']);

        $this->assertNull(Cache::tags(['events'])->get('test_key'));
    }

    public function test_deleting_event_flushes_events_cache(): void
    {
        $event = $this->makeEvent('Event');

        Cache::tags(['events'])->put('test_key', 'test_value', 60);

        $event->delete();

        $this->assertNull(Cache::tags(['events'])->get('test_key'));
    }

    public function test_approving_event_queues_approval_email(): void
    {
        Mail::fake();

        $event = $this->makeEvent('Pending Show', status: 'pending_approval');

        $event->update(['status' => 'approved']);

        Mail::assertQueued(EventApprovedMail::class, fn ($mail) => $mail->event->id === $event->id);
    }

    public function test_rejecting_event_queues_rejection_email(): void
    {
        Mail::fake();

        $event = $this->makeEvent('Bad Show', status: 'pending_approval');

        $event->update(['status' => 'rejected', 'rejection_reason' => 'Not suitable']);

        Mail::assertQueued(EventRejectedMail::class, fn ($mail) => $mail->event->id === $event->id);
    }

    public function test_status_change_flushes_ics_feed_cache(): void
    {
        Cache::put('ics_feed', 'cached_ics_content', 60);

        $event = $this->makeEvent('ICS Event', status: 'pending_approval');
        $event->update(['status' => 'approved']);

        $this->assertNull(Cache::get('ics_feed'));
    }

    public function test_non_status_change_does_not_send_email(): void
    {
        Mail::fake();

        $event = $this->makeEvent('Show', status: 'approved');

        $event->update(['title' => 'Updated Title']);

        Mail::assertNothingQueued();
    }

    private function makeEvent(string $title, string $status = 'pending_approval'): Event
    {
        return Event::create([
            'title'           => $title,
            'description'     => 'Desc',
            'start_date'      => now()->addDays(5),
            'end_date'        => now()->addDays(6),
            'status'          => $status,
            'submitter_email' => 'host@example.com',
            'location_id'     => $this->location->id,
            'category_id'     => $this->category->id,
            'organizer'       => 'Test Org',
        ]);
    }
}
