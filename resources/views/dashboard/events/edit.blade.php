@extends('layouts.calendar')

@section('title', 'Edit Event — ' . $event->title)

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">

    <div class="mb-6">
        <a href="{{ route('dashboard.events.show', $event) }}" class="text-sm text-blue-600 hover:underline">&larr; Back to Event</a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6 md:p-8">

        <h1 class="text-xl font-bold text-gray-900 mb-1">Edit Event</h1>

        @if ($event->status === 'approved')
            <div class="mb-6 rounded-lg bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 text-sm">
                This event is <strong>approved</strong> and live. You may only edit the description, organizer, website, and image.
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('dashboard.events.update', $event) }}" enctype="multipart/form-data"
              class="space-y-6">
            @csrf
            @method('PATCH')

            {{-- Title (locked when approved) --}}
            @if ($event->status !== 'approved')
                <div>
                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-1">
                        Event Title <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="title" name="title"
                           value="{{ old('title', $event->title) }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-400 @enderror">
                    @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            @endif

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-700 mb-1">
                    Description <span class="text-red-500">*</span>
                </label>
                <textarea id="description" name="description" rows="6"
                          class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('description') border-red-400 @enderror">{{ old('description', $event->description) }}</textarea>
                @error('description')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Organizer / Website --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="organizer" class="block text-sm font-semibold text-gray-700 mb-1">
                        Organizer <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="organizer" name="organizer"
                           value="{{ old('organizer', $event->organizer) }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('organizer') border-red-400 @enderror">
                    @error('organizer')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="website" class="block text-sm font-semibold text-gray-700 mb-1">Website</label>
                    <input type="url" id="website" name="website"
                           value="{{ old('website', $event->website) }}"
                           placeholder="https://"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('website') border-red-400 @enderror">
                    @error('website')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Dates + Location (locked when approved) --}}
            @if ($event->status !== 'approved')
                <div x-data="{ allDay: {{ old('is_all_day', $event->is_all_day) ? 'true' : 'false' }} }">
                    <div class="flex items-center gap-2 mb-3">
                        <input type="checkbox" id="is_all_day" name="is_all_day" value="1"
                               x-model="allDay"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                               {{ old('is_all_day', $event->is_all_day) ? 'checked' : '' }}>
                        <label for="is_all_day" class="text-sm font-semibold text-gray-700">All Day Event</label>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <div>
                                <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-1">
                                    Start Date <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="start_date" name="start_date"
                                       value="{{ old('start_date', $event->start_date?->format('Y-m-d')) }}"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('start_date') border-red-400 @enderror">
                                @error('start_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div x-show="!allDay">
                                <label for="start_time" class="block text-sm font-semibold text-gray-700 mb-1">Start Time</label>
                                <input type="time" id="start_time" name="start_time"
                                       value="{{ old('start_time', $event->is_all_day ? '' : $event->start_date?->format('H:i')) }}"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <div>
                                <label for="end_date" class="block text-sm font-semibold text-gray-700 mb-1">End Date</label>
                                <input type="date" id="end_date" name="end_date"
                                       value="{{ old('end_date', $event->end_date?->format('Y-m-d')) }}"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('end_date') border-red-400 @enderror">
                                @error('end_date')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div x-show="!allDay">
                                <label for="end_time" class="block text-sm font-semibold text-gray-700 mb-1">End Time</label>
                                <input type="time" id="end_time" name="end_time"
                                       value="{{ old('end_time', $event->is_all_day ? '' : $event->end_date?->format('H:i')) }}"
                                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="location_id" class="block text-sm font-semibold text-gray-700 mb-1">
                            Location <span class="text-red-500">*</span>
                        </label>
                        <select id="location_id" name="location_id"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('location_id') border-red-400 @enderror">
                            <option value="">Select a location…</option>
                            @foreach ($locations as $id => $name)
                                <option value="{{ $id }}" {{ old('location_id', $event->location_id) == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @error('location_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-1">Category</label>
                        <select id="category_id" name="category_id"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Select a category…</option>
                            @foreach ($categories as $id => $name)
                                <option value="{{ $id }}" {{ old('category_id', $event->category_id) == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif

            {{-- Image --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Event Image</label>
                @if ($event->image_url)
                    <img src="{{ $event->image_url }}" alt="Current event image"
                         class="h-32 w-auto rounded-lg mb-2 object-cover border border-gray-200">
                    <p class="text-xs text-gray-500 mb-2">Upload a new image to replace the current one.</p>
                @endif
                <input type="file" name="image" accept="image/*"
                       class="block w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-gray-400 mt-1">Max 2 MB. JPG, PNG, or GIF.</p>
                @error('image')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Submit --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                    Save Changes
                </button>
                <a href="{{ route('dashboard.events.show', $event) }}"
                   class="inline-flex items-center px-5 py-2.5 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                    Cancel
                </a>
            </div>

        </form>
    </div>

    {{-- Extend Recurring Series --}}
    @if ($event->isPartOfSeries() && $event->status === 'approved')
        @php
            $series = $event->recurringSeries;
            $futureCount = $series->events()->where('start_date', '>=', now())->count();
            $minExtendDate = \Carbon\Carbon::parse($series->recurrence_end_date)->addDay()->format('Y-m-d');
            $maxExtendDate = now()->addYear()->format('Y-m-d');
            $recurrenceLabels = [
                'daily'           => 'Daily',
                'weekly'          => 'Weekly',
                'biweekly'        => 'Every other week',
                'monthly_date'    => 'Monthly (same date)',
                'monthly_weekday' => 'Monthly (same weekday)',
            ];
        @endphp

        <div class="bg-white rounded-xl border border-gray-200 p-6 md:p-8 mt-6">

            <h2 class="text-lg font-bold text-gray-900 mb-4">Recurring Series</h2>

            <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm mb-6">
                <div>
                    <dt class="text-gray-500 font-medium mb-0.5">Pattern</dt>
                    <dd class="text-gray-900">{{ $recurrenceLabels[$series->recurrence_type] ?? $series->recurrence_type }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 font-medium mb-0.5">Series ends</dt>
                    <dd class="text-gray-900">{{ \Carbon\Carbon::parse($series->recurrence_end_date)->format('M j, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 font-medium mb-0.5">Upcoming occurrences</dt>
                    <dd class="flex items-center gap-2">
                        <span class="text-gray-900">{{ $futureCount }}</span>
                        @if ($futureCount <= 3)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                Ending soon
                            </span>
                        @endif
                    </dd>
                </div>
            </dl>

            @if (session('success') && str_contains(session('success'), 'occurrence'))
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->has('new_end_date'))
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                    {{ $errors->first('new_end_date') }}
                </div>
            @endif

            <form method="POST"
                  action="{{ route('dashboard.events.extend-series', $event) }}"
                  class="flex flex-col sm:flex-row items-end gap-3">
                @csrf
                @method('PATCH')

                <div class="flex-1">
                    <label for="new_end_date" class="block text-sm font-semibold text-gray-700 mb-1">
                        Extend series until <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="new_end_date" name="new_end_date"
                           min="{{ $minExtendDate }}"
                           max="{{ $maxExtendDate }}"
                           value="{{ old('new_end_date') }}"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('new_end_date') border-red-400 @enderror">
                </div>

                <button type="submit"
                        class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition whitespace-nowrap">
                    Extend Series
                </button>
            </form>

            <p class="text-xs text-gray-400 mt-2">
                Maximum extension is 1 year from today. Up to {{ \App\Services\RecurringEventService::MAX_OCCURRENCES }} new occurrences will be generated.
            </p>

        </div>
    @endif

</div>
@endsection
