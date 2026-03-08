@extends('layouts.calendar')

@section('title', $event->title . ' — My Events')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">

    <div class="mb-6">
        <a href="{{ route('dashboard') }}" class="text-sm text-blue-600 hover:underline">&larr; Back to My Events</a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        {{-- Image --}}
        @if ($event->image_url)
            <div class="aspect-video bg-gray-100 overflow-hidden">
                <img src="{{ $event->image_url }}" alt="{{ $event->title }}"
                     class="w-full h-full object-cover">
            </div>
        @endif

        <div class="p-6 md:p-8">

            {{-- Title & status --}}
            <div class="flex items-start justify-between gap-4 mb-4">
                <h1 class="text-2xl font-bold text-gray-900">{{ $event->title }}</h1>
                @php
                    $colors = [
                        'approved'             => 'text-green-700 bg-green-50 border-green-200',
                        'pending_approval'     => 'text-yellow-700 bg-yellow-50 border-yellow-200',
                        'pending_verification' => 'text-blue-700 bg-blue-50 border-blue-200',
                        'pending_payment'      => 'text-purple-700 bg-purple-50 border-purple-200',
                        'rejected'             => 'text-red-700 bg-red-50 border-red-200',
                        'draft'                => 'text-gray-600 bg-gray-50 border-gray-200',
                    ];
                    $color = $colors[$event->status] ?? 'text-gray-600 bg-gray-50 border-gray-200';
                @endphp
                <span class="shrink-0 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border {{ $color }}">
                    {{ str_replace('_', ' ', ucfirst($event->status)) }}
                </span>
            </div>

            {{-- Rejection reason --}}
            @if ($event->status === 'rejected' && $event->rejection_reason)
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                    <strong>Rejection reason:</strong> {{ $event->rejection_reason }}
                </div>
            @endif

            {{-- Details grid --}}
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 text-sm">
                <div>
                    <dt class="font-semibold text-gray-600">Date</dt>
                    <dd class="mt-1 text-gray-900">
                        @if ($event->is_all_day)
                            {{ $event->start_date->format('F j, Y') }}
                            @if ($event->end_date)
                                — {{ $event->end_date->format('F j, Y') }}
                            @endif
                            <span class="ml-1 text-xs text-gray-500">(All Day)</span>
                        @else
                            {{ $event->start_date->format('F j, Y g:i A') }}
                            @if ($event->end_date)
                                — {{ $event->end_date->format('F j, Y g:i A') }}
                            @endif
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-600">Location</dt>
                    <dd class="mt-1 text-gray-900">{{ $event->location?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-600">Category</dt>
                    <dd class="mt-1 text-gray-900">{{ $event->category?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-600">Organizer</dt>
                    <dd class="mt-1 text-gray-900">{{ $event->organizer ?? '—' }}</dd>
                </div>
                @if ($event->website)
                    <div>
                        <dt class="font-semibold text-gray-600">Website</dt>
                        <dd class="mt-1">
                            <a href="{{ $event->website }}" target="_blank" rel="noopener"
                               class="text-blue-600 hover:underline break-all">{{ $event->website }}</a>
                        </dd>
                    </div>
                @endif
                <div>
                    <dt class="font-semibold text-gray-600">Views</dt>
                    <dd class="mt-1 text-gray-900">{{ number_format($event->views_count) }}</dd>
                </div>
                @if ($event->is_premium)
                    <div>
                        <dt class="font-semibold text-gray-600">Type</dt>
                        <dd class="mt-1 text-yellow-600 font-medium">Premium</dd>
                    </div>
                @endif
            </dl>

            {{-- Description --}}
            <div class="prose prose-sm max-w-none mb-6">
                {!! nl2br(e($event->description)) !!}
            </div>

            {{-- Actions --}}
            <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-100">
                @if ($event->status !== 'rejected')
                    <a href="{{ route('dashboard.events.edit', $event) }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                        Edit Event
                    </a>
                @endif

                @if ($event->status === 'approved')
                    <a href="{{ route('events.show', $event->slug) }}" target="_blank"
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                        View Public Page ↗
                    </a>
                @endif

                @if (in_array($event->status, ['draft', 'pending_verification', 'pending_payment', 'rejected']))
                    <form method="POST" action="{{ route('dashboard.events.destroy', $event) }}"
                          onsubmit="return confirm('Delete this event? This cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-red-300 text-red-600 text-sm font-medium rounded-lg hover:bg-red-50 transition">
                            Delete
                        </button>
                    </form>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
