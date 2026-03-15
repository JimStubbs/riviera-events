<?php

namespace Tests\Feature\Submission;

use App\Models\Category;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaidSubmissionTest extends TestCase
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

    public function test_premium_form_requires_authentication(): void
    {
        $this->get(route('submit.premium.create'))->assertRedirect(route('login'));
    }

    public function test_premium_form_renders_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('submit.premium.create'))->assertStatus(200);
    }

    public function test_premium_form_validates_required_fields(): void
    {
        $user = User::factory()->create();

        // Validation happens before Stripe is reached, so no API call occurs
        $this->actingAs($user)
            ->from(route('submit.premium.create'))
            ->post(route('submit.premium.store'), [])
            ->assertSessionHasErrors(['title', 'description', 'start_date', 'location_id', 'category_id', 'organizer']);
    }

    public function test_premium_form_rejects_invalid_website_url(): void
    {
        $user = User::factory()->create();

        $payload = array_merge($this->validPayload(), ['website' => 'not-a-url']);

        $this->actingAs($user)
            ->from(route('submit.premium.create'))
            ->post(route('submit.premium.store'), $payload)
            ->assertSessionHasErrors('website');
    }

    public function test_premium_form_rejects_invalid_location(): void
    {
        $user = User::factory()->create();

        $payload = array_merge($this->validPayload(), ['location_id' => 9999]);

        $this->actingAs($user)
            ->from(route('submit.premium.create'))
            ->post(route('submit.premium.store'), $payload)
            ->assertSessionHasErrors('location_id');
    }

    private function validPayload(): array
    {
        return [
            'title'       => 'VIP Concert',
            'description' => 'An exclusive premium event.',
            'start_date'  => now()->addDays(10)->format('Y-m-d'),
            'end_date'    => now()->addDays(10)->format('Y-m-d'),
            'location_id' => $this->location->id,
            'category_id' => $this->category->id,
            'organizer'   => 'VIP Events Co',
        ];
    }
}
