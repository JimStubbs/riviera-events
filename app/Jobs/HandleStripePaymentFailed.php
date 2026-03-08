<?php

namespace App\Jobs;

use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class HandleStripePaymentFailed implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly object $paymentIntent) {}

    public function handle(): void
    {
        DB::transaction(function () {
            $payment = Payment::where('stripe_payment_id', $this->paymentIntent->id)
                ->orWhereHas('event', fn ($q) => $q->where('stripe_payment_id', $this->paymentIntent->id))
                ->first();

            if (! $payment) return;

            $payment->update(['status' => 'failed']);

            $payment->event()->update([
                'status'           => 'rejected',
                'rejection_reason' => 'Payment failed.',
            ]);
        });
    }
}
