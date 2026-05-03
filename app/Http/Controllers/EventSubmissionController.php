<?php

namespace App\Http\Controllers;

use App\Mail\EventVerificationMail;
use App\Models\Event;
use App\Models\FeaturedEvent;
use App\Models\Payment;
use App\Notifications\NewEventPendingNotification;
use App\Services\RecurringEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class EventSubmissionController extends Controller
{
    public function create(): View
    {
        return view('submit.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->verifyRecaptcha($request);

        $data = $request->validate([
            'title'           => ['required', 'string', 'max:255'],
            'description'     => ['required', 'string'],
            'start_date'      => ['required', 'date_format:Y-m-d'],
            'start_time'      => ['nullable', 'date_format:H:i'],
            'end_date'        => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'end_time'        => ['nullable', 'date_format:H:i'],
            'is_all_day'      => ['boolean'],
            'location_id'     => ['required', 'exists:locations,id'],
            'category_id'     => ['required', 'exists:categories,id'],
            'organizer'       => ['required', 'string', 'max:150'],
            'website'         => ['nullable', 'url', 'max:255'],
            'image'           => ['nullable', 'image', 'max:4096'],
            'submitter_email' => ['required', 'email', 'max:255'],

            // Featured add-on
            'feature_event'       => ['boolean'],

            // Recurrence fields
            'is_recurring'        => ['boolean'],
            'recurrence_type'     => ['nullable', 'required_if:is_recurring,1', 'in:daily,weekly,monthly_date,monthly_weekday'],
            'day_of_week'         => ['nullable', 'required_if:recurrence_type,weekly', 'integer', 'between:0,6'],
            'week_of_month'       => ['nullable', 'required_if:recurrence_type,monthly_weekday', 'integer', 'between:1,5'],
            'weekday'             => ['nullable', 'required_if:recurrence_type,monthly_weekday', 'integer', 'between:0,6'],
            'recurrence_end_date' => ['nullable', 'required_if:is_recurring,1', 'date', 'after:start_date'],
        ]);

        $isAllDay = $request->boolean('is_all_day');
        $startTime = (!$isAllDay && !empty($data['start_time'])) ? $data['start_time'] : '00:00';
        $data['start_date'] = $data['start_date'] . ' ' . $startTime . ':00';
        if (!empty($data['end_date'])) {
            $endTime = (!$isAllDay && !empty($data['end_time'])) ? $data['end_time'] : '23:59';
            $data['end_date'] = $data['end_date'] . ' ' . $endTime . ':00';
        }
        $data['is_all_day'] = $isAllDay;

        // Capture recurrence data before stripping from Event fields
        $isRecurring    = $request->boolean('is_recurring');
        $recurrenceData = [
            'recurrence_type'     => $data['recurrence_type'] ?? null,
            'day_of_week'         => $data['day_of_week'] ?? null,
            'week_of_month'       => $data['week_of_month'] ?? null,
            'weekday'             => $data['weekday'] ?? null,
            'recurrence_end_date' => $data['recurrence_end_date'] ?? null,
        ];

        unset(
            $data['start_time'], $data['end_time'],
            $data['is_recurring'], $data['recurrence_type'],
            $data['day_of_week'], $data['week_of_month'],
            $data['weekday'], $data['recurrence_end_date']
        );

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('events', 'public');
        }

        $data['status']             = 'pending_verification';
        $data['verification_token'] = Str::uuid()->toString();
        $data['user_id']            = auth()->id();

        $event = Event::create($data);

        // Generate recurring occurrences if requested
        if ($isRecurring && $recurrenceData['recurrence_type']) {
            app(RecurringEventService::class)->generateSeries($event, $recurrenceData);
        }

        Mail::to($event->submitter_email)->queue(new EventVerificationMail($event));

        if ($request->boolean('feature_event')) {
            FeaturedEvent::create([
                'event_id' => $event->id,
                'order'    => 99,
                'active'   => false,
            ]);

            Stripe::setApiKey(config('services.stripe.secret'));

            $stripeSession = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items'           => [[
                    'price_data' => [
                        'currency'     => 'mxn',
                        'product_data' => ['name' => $event->title . ' — Evento Destacado (200 MXN)'],
                        'unit_amount'  => (int) env('FEATURED_EVENT_PRICE', 20000),
                    ],
                    'quantity' => 1,
                ]],
                'mode'        => 'payment',
                'success_url' => route('submit.success') . '?featured=1',
                'cancel_url'  => route('submit.create'),
                'metadata'    => ['event_id' => $event->id, 'payment_type' => 'featured'],
            ]);

            Payment::create([
                'user_id'           => null,
                'event_id'          => $event->id,
                'stripe_session_id' => $stripeSession->id,
                'amount'            => (int) env('FEATURED_EVENT_PRICE', 20000),
                'type'              => 'featured',
                'status'            => 'pending',
            ]);

            return redirect($stripeSession->url);
        }

        return redirect()->route('submit.success');
    }

    public function verify(string $token): RedirectResponse
    {
        $event = Event::where('verification_token', $token)
            ->where('status', 'pending_verification')
            ->firstOrFail();

        $event->update([
            'status'      => 'pending_approval',
            'verified_at' => now(),
        ]);

        Notification::route('mail', config('app.admin_email'))
            ->notify(new NewEventPendingNotification($event));

        return redirect()->route('submit.success')->with('verified', true);
    }

    public function success(): View
    {
        return view('submit.success');
    }

    private function verifyRecaptcha(Request $request): void
    {
        $token = $request->input('recaptcha_token');

        if (app()->isProduction() && $token) {
            $recaptcha = new \ReCaptcha\ReCaptcha(config('services.recaptcha.secret_key'));
            $response  = $recaptcha->verify($token, $request->ip());

            if (! $response->isSuccess() || $response->getScore() < 0.5) {
                abort(422, 'reCAPTCHA verification failed. Please try again.');
            }
        }
    }
}
