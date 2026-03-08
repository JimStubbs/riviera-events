<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class PremiumEventController extends Controller
{
    public function create(): View
    {
        return view('submit.premium');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'start_date'  => ['required', 'date'],
            'end_date'    => ['nullable', 'date', 'after_or_equal:start_date'],
            'location_id' => ['required', 'exists:locations,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'organizer'   => ['required', 'string', 'max:150'],
            'website'     => ['nullable', 'url', 'max:255'],
            'image'       => ['nullable', 'image', 'max:4096'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('events', 'public');
        }

        $data['status']     = 'pending_payment';
        $data['is_premium'] = true;
        $data['user_id']    = auth()->id();

        $session = null;

        DB::transaction(function () use ($data, &$session) {
            $event = Event::create($data);

            Stripe::setApiKey(config('services.stripe.secret'));

            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items'           => [[
                    'price_data' => [
                        'currency'     => 'usd',
                        'product_data' => ['name' => $event->title . ' — Premium Listing'],
                        'unit_amount'  => (int) env('PREMIUM_EVENT_PRICE', 4900),
                    ],
                    'quantity' => 1,
                ]],
                'mode'        => 'payment',
                'success_url' => route('submit.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'  => route('submit.premium.create'),
                'metadata'    => ['event_id' => $event->id],
            ]);

            Payment::create([
                'user_id'           => auth()->id(),
                'event_id'          => $event->id,
                'stripe_session_id' => $session->id,
                'amount'            => (int) env('PREMIUM_EVENT_PRICE', 4900),
                'status'            => 'pending',
            ]);
        });

        return redirect($session->url);
    }
}
