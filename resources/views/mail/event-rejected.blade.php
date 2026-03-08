<x-mail::message>
# Update on your event submission

Thank you for submitting **{{ $event->title }}** to {{ config('app.name') }}.

After review, we were unable to approve this submission at this time.

@if($event->rejection_reason)
**Reason:**
{{ $event->rejection_reason }}

@endif
If you believe this is an error, or if you'd like to make changes and resubmit, you're welcome to submit a new event.

<x-mail::button :url="$submitUrl">
Submit Another Event
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
