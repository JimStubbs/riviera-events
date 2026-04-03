<?php

namespace App\Http\Controllers;

use App\Jobs\HandleFeaturedCheckoutCompleted;
use App\Jobs\HandleStripeCheckoutCompleted;
use App\Jobs\HandleStripePaymentFailed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        match ($event->type) {
            'checkout.session.completed'    => $this->dispatchCheckoutJob($event->data->object),
            'payment_intent.payment_failed' => HandleStripePaymentFailed::dispatch($event->data->object),
            default => null,
        };

        return response()->json(['status' => 'ok']);
    }

    private function dispatchCheckoutJob(object $session): void
    {
        $type = $session->metadata->payment_type ?? 'premium';

        if ($type === 'featured') {
            HandleFeaturedCheckoutCompleted::dispatch($session);
        } else {
            HandleStripeCheckoutCompleted::dispatch($session);
        }
    }
}
