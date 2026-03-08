<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Payment;
use App\Notifications\NewPremiumEventPendingNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class HandleStripeCheckoutCompleted implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly object $session) {}

    public function handle(): void
    {
        DB::transaction(function () {
            $payment = Payment::where('stripe_session_id', $this->session->id)->firstOrFail();

            $payment->update([
                'status'             => 'completed',
                'paid_at'            => now(),
                'stripe_payment_id'  => $this->session->payment_intent ?? null,
            ]);

            $payment->event()->update([
                'status'             => 'pending_approval',
                'is_paid'            => true,
                'stripe_payment_id'  => $this->session->payment_intent ?? null,
            ]);

            $event = $payment->event;

            Notification::route('mail', env('ADMIN_EMAIL', 'admin@example.com'))
                ->notify(new NewPremiumEventPendingNotification($event));
        });
    }
}
