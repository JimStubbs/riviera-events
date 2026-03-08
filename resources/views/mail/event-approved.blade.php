<x-mail::message>
# Great news — your event is live!

Your submission **{{ $event->title }}** has been approved and is now live on {{ config('app.name') }}.

**Event Details:**
- **Date:** {{ $event->start_date->format('F j, Y') }}{{ $event->is_all_day ? ' (All Day)' : ' at ' . $event->start_date->format('g:i A') }}
- **Organizer:** {{ $event->organizer }}
@if($event->location)
- **Location:** {{ $event->location->name }}, {{ $event->location->city }}
@endif

<x-mail::button :url="$eventUrl">
View Your Event
</x-mail::button>

Share it with your audience to spread the word!

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
