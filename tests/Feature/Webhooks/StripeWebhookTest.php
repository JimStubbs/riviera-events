<?php

namespace Tests\Feature\Webhooks;

use App\Jobs\HandleStripeCheckoutCompleted;
use App\Jobs\HandleStripePaymentFailed;
use App\Models\Category;
use App\Models\Event;
use App\Models\Location;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Stripe\Webhook;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_signature_returns_400(): void
    {
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
            ->postJson(route('stripe.webhook'), [], ['Stripe-Signature' => 'invalid'])
            ->assertStatus(400);
    }

    public function test_checkout_completed_event_dispatches_job(): void
    {
        Queue::fake();

        $secret = 'whsec_test_secret';
        config(['services.stripe.webhook_secret' => $secret]);

        $payload = json_encode([
            'type' => 'checkout.session.completed',
            'data' => ['object' => ['id' => 'cs_test_123', 'payment_intent' => 'pi_test_123']],
        ]);

        $timestamp = time();
        $signature = 't=' . $timestamp . ',v1=' . hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

        $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $signature,
            'CONTENT_TYPE'          => 'application/json',
        ], $payload)
            ->assertStatus(200);

        Queue::assertPushed(HandleStripeCheckoutCompleted::class);
    }

    public function test_payment_failed_event_dispatches_job(): void
    {
        Queue::fake();

        $secret = 'whsec_test_secret';
        config(['services.stripe.webhook_secret' => $secret]);

        $payload = json_encode([
            'type' => 'payment_intent.payment_failed',
            'data' => ['object' => ['id' => 'pi_test_failed']],
        ]);

        $timestamp = time();
        $signature = 't=' . $timestamp . ',v1=' . hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

        $this->call('POST', route('stripe.webhook'), [], [], [], [
            'HTTP_STRIPE_SIGNATURE' => $signature,
            'CONTENT_TYPE'          => 'application/json',
        ], $payload)
            ->assertStatus(200);

        Queue::assertPushed(HandleStripePaymentFailed::class);
    }

    public function test_checkout_completed_job_updates_payment_and_event(): void
    {
        $location = Location::create(['name' => 'Venue', 'city' => 'City', 'state' => 'ST']);
        $category = Category::create(['name' => 'Music']);
        $user     = User::factory()->create();

        $event = Event::create([
            'title'       => 'Premium Show',
            'description' => '-',
            'start_date'  => now()->addDays(5),
            'end_date'    => now()->addDays(6),
            'status'      => 'pending_payment',
            'is_premium'  => true,
            'location_id' => $location->id,
            'category_id' => $category->id,
            'organizer'   => 'Org',
            'user_id'     => $user->id,
        ]);

        $payment = Payment::create([
            'user_id'           => $user->id,
            'event_id'          => $event->id,
            'stripe_session_id' => 'cs_test_abc',
            'amount'            => 4900,
            'status'            => 'pending',
        ]);

        $session = (object) [
            'id'             => 'cs_test_abc',
            'payment_intent' => 'pi_test_xyz',
        ];

        (new HandleStripeCheckoutCompleted($session))->handle();

        $this->assertDatabaseHas('payments', [
            'id'     => $payment->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('events', [
            'id'      => $event->id,
            'status'  => 'pending_approval',
            'is_paid' => true,
        ]);
    }
}
