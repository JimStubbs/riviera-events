<?php

namespace Tests\Feature\Submission;

use App\Mail\EventVerificationMail;
use App\Models\Category;
use App\Models\Event;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FreeSubmissionTest extends TestCase
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

    public function test_submission_form_renders(): void
    {
        $this->get(route('submit.create'))->assertStatus(200);
    }

    public function test_valid_submission_creates_pending_event_and_queues_email(): void
    {
        Mail::fake();

        $this->post(route('submit.store'), $this->validPayload())
            ->assertRedirect(route('submit.success'));

        $this->assertDatabaseHas('events', [
            'title'  => 'Sunset Jazz',
            'status' => 'pending_verification',
        ]);

        Mail::assertQueued(EventVerificationMail::class);
    }

    public function test_submission_requires_title(): void
    {
        Mail::fake();
        $payload = $this->validPayload();
        unset($payload['title']);

        $this->post(route('submit.store'), $payload)
            ->assertSessionHasErrors('title');
    }

    public function test_submission_requires_valid_email(): void
    {
        Mail::fake();
        $payload = array_merge($this->validPayload(), ['submitter_email' => 'not-an-email']);

        $this->post(route('submit.store'), $payload)
            ->assertSessionHasErrors('submitter_email');
    }

    public function test_submission_requires_valid_location(): void
    {
        Mail::fake();
        $payload = array_merge($this->validPayload(), ['location_id' => 9999]);

        $this->post(route('submit.store'), $payload)
            ->assertSessionHasErrors('location_id');
    }

    public function test_verification_token_is_stored(): void
    {
        Mail::fake();

        $this->post(route('submit.store'), $this->validPayload());

        $event = Event::where('title', 'Sunset Jazz')->first();
        $this->assertNotNull($event->verification_token);
    }

    public function test_success_page_renders(): void
    {
        $this->get(route('submit.success'))->assertStatus(200);
    }

    private function validPayload(): array
    {
        return [
            'title'           => 'Sunset Jazz',
            'description'     => 'A wonderful jazz evening.',
            'start_date'      => now()->addDays(7)->format('Y-m-d'),
            'start_time'      => '19:00',
            'end_date'        => now()->addDays(7)->format('Y-m-d'),
            'end_time'        => '22:00',
            'location_id'     => $this->location->id,
            'category_id'     => $this->category->id,
            'organizer'       => 'Jazz Club',
            'submitter_email' => 'host@example.com',
        ];
    }
}
