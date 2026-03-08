<x-mail::message>
# Verify Your Event Submission

Thank you for submitting **{{ $event->title }}** to {{ config('app.name') }}.

To complete your submission, please verify your email address by clicking the button below:

<x-mail::button :url="$verificationUrl">
Verify & Submit Event
</x-mail::button>

**Event Details:**
- **Date:** {{ $event->start_date->format('F j, Y') }}
- **Organizer:** {{ $event->organizer }}

Once verified, your event will be reviewed by our team and published within 24 hours.

If you did not submit this event, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
