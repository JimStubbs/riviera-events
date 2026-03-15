<?php

namespace Tests\Feature\Submission;

use App\Models\Category;
use App\Models\Event;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
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

    public function test_valid_token_moves_event_to_pending_approval(): void
    {
        $event = $this->makePendingEvent();

        $this->get(route('submit.verify', $event->verification_token))
            ->assertRedirect(route('submit.success'));

        $this->assertDatabaseHas('events', [
            'id'     => $event->id,
            'status' => 'pending_approval',
        ]);
    }

    public function test_verified_at_is_set_after_verification(): void
    {
        $event = $this->makePendingEvent();

        $this->get(route('submit.verify', $event->verification_token));

        $this->assertNotNull($event->fresh()->verified_at);
    }

    public function test_invalid_token_returns_404(): void
    {
        $this->get(route('submit.verify', 'invalid-token-xyz'))->assertStatus(404);
    }

    public function test_already_verified_token_returns_404(): void
    {
        $event = $this->makePendingEvent();
        // Move to pending_approval so status no longer matches
        $event->update(['status' => 'pending_approval']);

        $this->get(route('submit.verify', $event->verification_token))->assertStatus(404);
    }

    private function makePendingEvent(): Event
    {
        return Event::create([
            'title'              => 'Test Event',
            'description'        => 'Desc',
            'start_date'         => now()->addDays(5),
            'end_date'           => now()->addDays(6),
            'status'             => 'pending_verification',
            'verification_token' => Str::uuid()->toString(),
            'submitter_email'    => 'test@example.com',
            'location_id'        => $this->location->id,
            'category_id'        => $this->category->id,
            'organizer'          => 'Org',
        ]);
    }
}
