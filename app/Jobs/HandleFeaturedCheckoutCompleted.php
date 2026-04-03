<?php

namespace App\Jobs;

use App\Models\FeaturedEvent;
use App\Models\Payment;
use App\Notifications\NewFeaturedEventNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class HandleFeaturedCheckoutCompleted implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly object $session) {}

    public function handle(): void
    {
        DB::transaction(function () {
            $payment = Payment::where('stripe_session_id', $this->session->id)
                ->where('type', 'featured')
                ->firstOrFail();

            $payment->update([
                'status'            => 'completed',
                'paid_at'           => now(),
                'stripe_payment_id' => $this->session->payment_intent ?? null,
            ]);

            FeaturedEvent::where('event_id', $payment->event_id)->update([
                'active'     => true,
                'start_date' => today(),
                'end_date'   => today()->addDays(30),
            ]);

            $event = $payment->event;

            Notification::route('mail', env('ADMIN_EMAIL', 'admin@example.com'))
                ->notify(new NewFeaturedEventNotification($event));
        });
    }
}
