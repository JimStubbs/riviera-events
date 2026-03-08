@extends($isEmbed ? 'layouts.embed' : 'layouts.calendar')

@section('title', $event->title . ' — ' . config('app.name'))

@section('content')
<div class="flex gap-8">

    {{-- Main Content --}}
    <article class="flex-1 min-w-0">

        {{-- Hero image --}}
        @if($event->image_url)
        <div class="aspect-video rounded-xl overflow-hidden mb-6">
            <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
        </div>
        @endif

        {{-- Category & Premium badge --}}
        <div class="flex flex-wrap gap-2 mb-3">
            @if($event->category)
            <span class="inline-block px-2 py-0.5 rounded text-sm font-semibold text-white" style="background-color: {{ $event->category->color }}">
                {{ $event->category->name }}
            </span>
            @endif
            @if($event->is_premium)
            <span class="inline-block px-2 py-0.5 rounded text-sm font-semibold bg-yellow-100 text-yellow-800">⭐ Premium</span>
            @endif
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $event->title }}</h1>

        {{-- Meta --}}
        <div class="flex flex-wrap gap-4 text-sm text-gray-500 mb-6">
            <span>📅 {{ $event->start_date->format('l, F j, Y') }}
                @if($event->end_date && !$event->start_date->isSameDay($event->end_date))
                    – {{ $event->end_date->format('F j, Y') }}
                @endif
            </span>
            @if($event->location)
            <span>📍 {{ $event->location->city }}, {{ $event->location->state }}</span>
            @endif
            @if($event->organizer)
            <span>👤 {{ $event->organizer }}</span>
            @endif
        </div>

        {{-- Description --}}
        <div class="prose prose-gray max-w-none mb-8">
            {!! nl2br(e($event->description)) !!}
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-wrap gap-3">
            <a
                href="{{ $event->google_calendar_url }}"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
            >
                + Add to Google Calendar
            </a>
            <a
                href="{{ route('events.ics', $event) }}"
                class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium"
            >
                ↓ Download .ics
            </a>
            @if($event->website)
            <a
                href="{{ $event->website }}"
                target="_blank"
                rel="noopener"
                class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium"
            >
                🔗 Event Website
            </a>
            @endif
        </div>

    </article>

    {{-- Sidebar --}}
    @if(!$isEmbed)
    <aside class="w-64 flex-shrink-0 hidden lg:block">
        <x-calendar.ad-sidebar :ad="$sidebarAd" />
    </aside>
    @endif

</div>
@endsection
