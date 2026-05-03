<x-mail::message>
# Your recurring event series is ending soon

Your recurring event **{{ $event->title }}** has 3 or fewer upcoming occurrences remaining and will end on **{{ $series->recurrence_end_date->format('F j, Y') }}**.

If you'd like to keep this event running, log in to your organizer dashboard and use the **Extend Series** option to set a new end date.

<x-mail::button :url="$dashboardUrl">
Go to Your Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
