@extends($isEmbed ? 'layouts.embed' : 'layouts.calendar')

@section('title', __('calendar.page_title'))

@push('head')
<meta property="og:title" content="{{ __('calendar.page_title') }}" />
<meta property="og:description" content="{{ __('calendar.og_description') }}" />
<meta property="og:image" content="{{ asset('images/rm-events-calendar.png') }}" />
<meta property="og:url" content="{{ url('/') }}" />
<meta property="og:type" content="website" />
@endpush

@section('content')

    {{-- Leaderboard ad --}}
    <x-calendar.ad-leaderboard :ad="$leaderboardAd" />

    {{-- Featured Banner --}}
    <x-calendar.featured-banner :featured="$featured" />

    {{-- Filter Bar --}}
    <x-calendar.filter-bar />

    {{-- Events Grid / Month View --}}
    <div id="calendar-container" class="mt-6">
        <div id="events-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Populated by calendar.js --}}
        </div>

        <div id="events-month" class="hidden">
            {{-- Month grid populated by calendar.js --}}
        </div>

        <div id="events-week" class="hidden">
            {{-- Week grid populated by calendar.js --}}
        </div>

        <div id="events-day" class="hidden">
            {{-- Day list populated by calendar.js --}}
        </div>

        <div id="events-loading" class="flex justify-center py-12 hidden">
            <svg class="animate-spin h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
        </div>

        <div id="events-empty" class="hidden text-center py-16 text-gray-500">
            <p class="text-xl font-medium">{{ __('calendar.no_events_found') }}</p>
            <p class="mt-2 text-sm">{{ __('calendar.adjust_filters') }}</p>
        </div>

        <div id="events-pagination" class="mt-8 flex justify-center gap-2 hidden"></div>
    </div>

@endsection

@push('scripts')
<script>
    window.i18n = {
        prev:          "{{ __('calendar.prev') }}",
        next:          "{{ __('calendar.next') }}",
        allDay:        "{{ __('calendar.all_day') }}",
        noEventsDay:   "{{ __('calendar.no_events_day') }}",
        featuredBadge: "{{ __('calendar.featured_badge') }}",
        premiumBadge:  "{{ __('calendar.premium_badge') }}",
        jsLocale:      "{{ __('calendar.js_locale') }}",
        months:        {!! json_encode(__('calendar.months')) !!},
        monthsShort:   {!! json_encode(__('calendar.months_short')) !!},
        daysShort:     {!! json_encode(__('calendar.days_short')) !!},
    };
    window.calendarConfig = {
        apiUrl: '{{ route('api.events.index') }}',
        filterOptionsUrl: '{{ route('api.events.filter-options') }}',
        isEmbed: {{ $isEmbed ? 'true' : 'false' }},
    };
</script>
@endpush
