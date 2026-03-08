<?php

namespace App\Http\Controllers;

use App\Mail\EventVerificationMail;
use App\Models\Event;
use App\Notifications\NewEventPendingNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\View\View;

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
        ]);

        $isAllDay = $request->boolean('is_all_day');
        $startTime = (!$isAllDay && !empty($data['start_time'])) ? $data['start_time'] : '00:00';
        $data['start_date'] = $data['start_date'] . ' ' . $startTime . ':00';
        if (!empty($data['end_date'])) {
            $endTime = (!$isAllDay && !empty($data['end_time'])) ? $data['end_time'] : '23:59';
            $data['end_date'] = $data['end_date'] . ' ' . $endTime . ':00';
        }
        $data['is_all_day'] = $isAllDay;
        unset($data['start_time'], $data['end_time']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('events', 'public');
        }

        $data['status']             = 'pending_verification';
        $data['verification_token'] = Str::uuid()->toString();

        $event = Event::create($data);

        Mail::to($event->submitter_email)->queue(new EventVerificationMail($event));

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

        Notification::route('mail', env('ADMIN_EMAIL', 'admin@example.com'))
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
