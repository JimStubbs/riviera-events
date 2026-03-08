@extends('layouts.calendar')

@section('title', 'Submit an Event — ' . config('app.name'))

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-1">Submit a Free Event</h1>
    <p class="text-gray-500 mb-6 text-sm">After submitting, you'll receive a verification email. Your event goes live once our team approves it.</p>

    <form method="POST" action="{{ route('submit.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        {{-- reCAPTCHA token (hidden, populated by JS) --}}
        <input type="hidden" name="recaptcha_token" id="recaptcha_token">

        {{-- Title --}}
        <div>
            <x-input-label for="title" value="Event Title *" />
            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required />
            <x-input-error :messages="$errors->get('title')" class="mt-1" />
        </div>

        {{-- Organizer --}}
        <div>
            <x-input-label for="organizer" value="Organizer / Host *" />
            <x-text-input id="organizer" name="organizer" type="text" class="mt-1 block w-full" :value="old('organizer')" required />
            <x-input-error :messages="$errors->get('organizer')" class="mt-1" />
        </div>

        {{-- Location & Category --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="location_id" value="Location *" />
                <select id="location_id" name="location_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">Select location</option>
                    @foreach(\App\Models\Location::orderBy('city')->get() as $location)
                    <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                        {{ $location->city }}, {{ $location->state }}
                    </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('location_id')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="category_id" value="Category *" />
                <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">Select category</option>
                    @foreach(\App\Models\Category::orderBy('name')->get() as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
            </div>
        </div>

        {{-- Dates & Times --}}
        <div x-data="{ allDay: {{ old('is_all_day') ? 'true' : 'false' }} }">
            <div class="flex items-center gap-2 mb-3">
                <input type="checkbox" id="is_all_day" name="is_all_day" value="1"
                       x-model="allDay"
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                       {{ old('is_all_day') ? 'checked' : '' }}>
                <label for="is_all_day" class="text-sm font-medium text-gray-700">All Day Event</label>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <div>
                        <x-input-label for="start_date" value="Start Date *" />
                        <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date')" required />
                        <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                    </div>
                    <div x-show="!allDay">
                        <x-input-label for="start_time" value="Start Time" />
                        <x-text-input id="start_time" name="start_time" type="time" class="mt-1 block w-full" :value="old('start_time')" />
                    </div>
                </div>
                <div class="space-y-2">
                    <div>
                        <x-input-label for="end_date" value="End Date" />
                        <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date')" />
                        <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                    </div>
                    <div x-show="!allDay">
                        <x-input-label for="end_time" value="End Time" />
                        <x-text-input id="end_time" name="end_time" type="time" class="mt-1 block w-full" :value="old('end_time')" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Description --}}
        <div>
            <x-input-label for="description" value="Description *" />
            <textarea id="description" name="description" rows="5"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                required>{{ old('description') }}</textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-1" />
        </div>

        {{-- Website --}}
        <div>
            <x-input-label for="website" value="Event Website (optional)" />
            <x-text-input id="website" name="website" type="url" class="mt-1 block w-full" :value="old('website')" placeholder="https://" />
            <x-input-error :messages="$errors->get('website')" class="mt-1" />
        </div>

        {{-- Image --}}
        <div>
            <x-input-label for="image" value="Event Image (optional, max 4MB)" />
            <input id="image" name="image" type="file" accept="image/*"
                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <x-input-error :messages="$errors->get('image')" class="mt-1" />
        </div>

        {{-- Submitter Email --}}
        <div>
            <x-input-label for="submitter_email" value="Your Email Address *" />
            <x-text-input id="submitter_email" name="submitter_email" type="email" class="mt-1 block w-full" :value="old('submitter_email')" required />
            <p class="text-xs text-gray-500 mt-1">We'll send a verification link to this address.</p>
            <x-input-error :messages="$errors->get('submitter_email')" class="mt-1" />
        </div>

        <div class="flex items-center justify-between pt-2">
            <x-primary-button>Submit Event</x-primary-button>
            <a href="{{ route('calendar.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
        </div>
    </form>
</div>

@if(config('services.recaptcha.site_key'))
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        grecaptcha.ready(function() {
            grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'submit'}).then(function(token) {
                document.getElementById('recaptcha_token').value = token;
                form.submit();
            });
        });
    });
</script>
@endif
@endsection
