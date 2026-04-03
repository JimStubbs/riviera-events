@extends('layouts.calendar')

@section('title', 'My Dashboard')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    {{-- Page header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Events</h1>
            <p class="text-gray-500 text-sm mt-1">Manage the events you've submitted</p>
        </div>
        <a href="{{ route('submit.create') }}"
           class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
            + Submit New Event
        </a>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
            <p class="text-3xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Events</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
            <p class="text-3xl font-bold text-green-600">{{ number_format($stats['approved']) }}</p>
            <p class="text-sm text-gray-500 mt-1">Approved</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
            <p class="text-3xl font-bold text-yellow-500">{{ number_format($stats['pending']) }}</p>
            <p class="text-sm text-gray-500 mt-1">Pending</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
            <p class="text-3xl font-bold text-blue-600">{{ number_format($stats['views']) }}</p>
            <p class="text-sm text-gray-500 mt-1">Total Views</p>
        </div>
    </div>

    {{-- Events table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @if ($events->isEmpty())
            <div class="text-center py-16 text-gray-400">
                <svg class="mx-auto w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <p class="font-medium">No events yet</p>
                <p class="text-sm mt-1">Submit your first event to get started.</p>
            </div>
        @else
            <table class="min-w-full divide-y divide-gray-100 text-sm">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-4 py-3 font-semibold text-gray-600">Event</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">Date</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 hidden md:table-cell">Location</th>
                        <th class="px-4 py-3 font-semibold text-gray-600">Status</th>
                        <th class="px-4 py-3 font-semibold text-gray-600 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($events as $event)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <div class="font-medium text-gray-900">{{ $event->title }}</div>
                                @if ($event->is_premium)
                                    <span class="inline-block mt-0.5 text-xs font-medium text-yellow-600 bg-yellow-50 px-1.5 py-0.5 rounded">
                                        Premium
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 hidden md:table-cell whitespace-nowrap">
                                {{ $event->start_date->format('M j, Y') }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 hidden md:table-cell">
                                {{ $event->location?->city ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @php
                                    $colors = [
                                        'approved'             => 'text-green-700 bg-green-50',
                                        'pending_approval'     => 'text-yellow-700 bg-yellow-50',
                                        'pending_verification' => 'text-blue-700 bg-blue-50',
                                        'pending_payment'      => 'text-purple-700 bg-purple-50',
                                        'rejected'             => 'text-red-700 bg-red-50',
                                        'draft'                => 'text-gray-600 bg-gray-50',
                                    ];
                                    $color = $colors[$event->status] ?? 'text-gray-600 bg-gray-50';
                                    $label = str_replace('_', ' ', ucfirst($event->status));
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('dashboard.events.show', $event) }}"
                                   class="text-gray-500 hover:text-gray-900 mr-3 text-xs">View</a>
                                @if ($event->status !== 'rejected')
                                    <a href="{{ route('dashboard.events.edit', $event) }}"
                                       class="text-blue-600 hover:text-blue-800 mr-3 text-xs font-medium">Edit</a>
                                @endif
                                <form method="POST" action="{{ route('dashboard.events.destroy', $event) }}"
                                      class="inline"
                                      onsubmit="return confirm('Delete this event? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-red-500 hover:text-red-700 text-xs">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if ($events->hasPages())
                <div class="px-4 py-3 border-t border-gray-100">
                    {{ $events->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
