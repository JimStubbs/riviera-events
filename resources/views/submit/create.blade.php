@extends('layouts.calendar')

@section('title', __('calendar.submit_title_page') . ' — ' . config('app.name'))

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-gray-900 mb-1">{{ __('calendar.submit_heading') }}</h1>
    <p class="text-gray-500 mb-6 text-sm">{{ __('calendar.submit_subheading') }}</p>

    <form method="POST" action="{{ route('submit.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        {{-- reCAPTCHA token (hidden, populated by JS) --}}
        <input type="hidden" name="recaptcha_token" id="recaptcha_token">

        {{-- Title --}}
        <div>
            <x-input-label for="title" :value="__('calendar.field_title')" />
            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title')" required />
            <x-input-error :messages="$errors->get('title')" class="mt-1" />
        </div>

        {{-- Organizer --}}
        <div>
            <x-input-label for="organizer" :value="__('calendar.field_organizer')" />
            <x-text-input id="organizer" name="organizer" type="text" class="mt-1 block w-full" :value="old('organizer')" required />
            <x-input-error :messages="$errors->get('organizer')" class="mt-1" />
        </div>

        {{-- Location & Category --}}
        <div class="grid grid-cols-2 gap-4">
            <div>
                <x-input-label for="location_id" :value="__('calendar.field_location')" />
                <select id="location_id" name="location_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">{{ __('calendar.select_location') }}</option>
                    @foreach(\App\Models\Location::orderBy('city')->get() as $location)
                    <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                        {{ $location->city }}, {{ $location->state }}
                    </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('location_id')" class="mt-1" />
            </div>
            <div>
                <x-input-label for="category_id" :value="__('calendar.field_category')" />
                <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">{{ __('calendar.select_category') }}</option>
                    @foreach(\App\Models\Category::orderBy('name')->get() as $category)
                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                        {{ translateCategory($category->name) }}
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
                <label for="is_all_day" class="text-sm font-medium text-gray-700">{{ __('calendar.all_day_event') }}</label>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <div>
                        <x-input-label for="start_date" :value="__('calendar.field_start_date')" />
                        <x-text-input id="start_date" name="start_date" type="date" class="mt-1 block w-full" :value="old('start_date')" required />
                        <x-input-error :messages="$errors->get('start_date')" class="mt-1" />
                    </div>
                    <div x-show="!allDay">
                        <x-input-label for="start_time" :value="__('calendar.field_start_time')" />
                        <x-text-input id="start_time" name="start_time" type="time" class="mt-1 block w-full" :value="old('start_time')" />
                    </div>
                </div>
                <div class="space-y-2">
                    <div>
                        <x-input-label for="end_date" :value="__('calendar.field_end_date')" />
                        <x-text-input id="end_date" name="end_date" type="date" class="mt-1 block w-full" :value="old('end_date')" />
                        <x-input-error :messages="$errors->get('end_date')" class="mt-1" />
                    </div>
                    <div x-show="!allDay">
                        <x-input-label for="end_time" :value="__('calendar.field_end_time')" />
                        <x-text-input id="end_time" name="end_time" type="time" class="mt-1 block w-full" :value="old('end_time')" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Recurrence --}}
        <div x-data="{
            isRecurring: {{ old('is_recurring') ? 'true' : 'false' }},
            recurrenceType: '{{ old('recurrence_type', '') }}'
        }" class="border border-gray-200 rounded-lg p-4 space-y-4">

            <div class="flex items-center gap-2">
                <input type="checkbox" id="is_recurring" name="is_recurring" value="1"
                       x-model="isRecurring"
                       class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
                       {{ old('is_recurring') ? 'checked' : '' }}>
                <label for="is_recurring" class="text-sm font-medium text-gray-700">
                    {{ __('calendar.is_recurring') }}
                </label>
            </div>

            <div x-show="isRecurring" x-cloak class="space-y-4">

                {{-- Recurrence type --}}
                <div>
                    <x-input-label for="recurrence_type" :value="__('calendar.repeats_label')" />
                    <select id="recurrence_type" name="recurrence_type"
                            x-model="recurrenceType"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">{{ __('calendar.select_pattern') }}</option>
                        <option value="weekly"          {{ old('recurrence_type') === 'weekly'          ? 'selected' : '' }}>{{ __('calendar.weekly_same_day') }}</option>
                        <option value="monthly_date"    {{ old('recurrence_type') === 'monthly_date'    ? 'selected' : '' }}>{{ __('calendar.monthly_date') }}</option>
                        <option value="monthly_weekday" {{ old('recurrence_type') === 'monthly_weekday' ? 'selected' : '' }}>{{ __('calendar.monthly_weekday') }}</option>
                    </select>
                    <x-input-error :messages="$errors->get('recurrence_type')" class="mt-1" />
                </div>

                {{-- Weekly: day of week --}}
                <div x-show="recurrenceType === 'weekly'">
                    <x-input-label for="day_of_week" :value="__('calendar.day_of_week')" />
                    <select id="day_of_week" name="day_of_week"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="0" {{ old('day_of_week') == '0' ? 'selected' : '' }}>{{ __('calendar.day_sunday') }}</option>
                        <option value="1" {{ old('day_of_week', '1') == '1' ? 'selected' : '' }}>{{ __('calendar.day_monday') }}</option>
                        <option value="2" {{ old('day_of_week') == '2' ? 'selected' : '' }}>{{ __('calendar.day_tuesday') }}</option>
                        <option value="3" {{ old('day_of_week') == '3' ? 'selected' : '' }}>{{ __('calendar.day_wednesday') }}</option>
                        <option value="4" {{ old('day_of_week') == '4' ? 'selected' : '' }}>{{ __('calendar.day_thursday') }}</option>
                        <option value="5" {{ old('day_of_week') == '5' ? 'selected' : '' }}>{{ __('calendar.day_friday') }}</option>
                        <option value="6" {{ old('day_of_week') == '6' ? 'selected' : '' }}>{{ __('calendar.day_saturday') }}</option>
                    </select>
                    <x-input-error :messages="$errors->get('day_of_week')" class="mt-1" />
                </div>

                {{-- Monthly weekday: week of month + weekday --}}
                <div x-show="recurrenceType === 'monthly_weekday'" class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="week_of_month" :value="__('calendar.week_of_month')" />
                        <select id="week_of_month" name="week_of_month"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="1" {{ old('week_of_month', '1') == '1' ? 'selected' : '' }}>1st</option>
                            <option value="2" {{ old('week_of_month') == '2' ? 'selected' : '' }}>2nd</option>
                            <option value="3" {{ old('week_of_month') == '3' ? 'selected' : '' }}>3rd</option>
                            <option value="4" {{ old('week_of_month') == '4' ? 'selected' : '' }}>4th</option>
                            <option value="5" {{ old('week_of_month') == '5' ? 'selected' : '' }}>5th</option>
                        </select>
                        <x-input-error :messages="$errors->get('week_of_month')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="weekday" :value="__('calendar.weekday_label')" />
                        <select id="weekday" name="weekday"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="0" {{ old('weekday') == '0' ? 'selected' : '' }}>{{ __('calendar.day_sunday') }}</option>
                            <option value="1" {{ old('weekday', '1') == '1' ? 'selected' : '' }}>{{ __('calendar.day_monday') }}</option>
                            <option value="2" {{ old('weekday') == '2' ? 'selected' : '' }}>{{ __('calendar.day_tuesday') }}</option>
                            <option value="3" {{ old('weekday') == '3' ? 'selected' : '' }}>{{ __('calendar.day_wednesday') }}</option>
                            <option value="4" {{ old('weekday') == '4' ? 'selected' : '' }}>{{ __('calendar.day_thursday') }}</option>
                            <option value="5" {{ old('weekday') == '5' ? 'selected' : '' }}>{{ __('calendar.day_friday') }}</option>
                            <option value="6" {{ old('weekday') == '6' ? 'selected' : '' }}>{{ __('calendar.day_saturday') }}</option>
                        </select>
                        <x-input-error :messages="$errors->get('weekday')" class="mt-1" />
                    </div>
                </div>

                {{-- Repeat until date --}}
                <div>
                    <x-input-label for="recurrence_end_date" :value="__('calendar.repeat_until')" />
                    <x-text-input id="recurrence_end_date" name="recurrence_end_date" type="date"
                                  class="mt-1 block w-full"
                                  :value="old('recurrence_end_date')" />
                    <p class="text-xs text-gray-500 mt-1">{{ __('calendar.max_occurrences') }}</p>
                    <x-input-error :messages="$errors->get('recurrence_end_date')" class="mt-1" />
                </div>

            </div>
        </div>

        {{-- Description --}}
        <div>
            <x-input-label for="description" :value="__('calendar.field_description')" />
            <textarea id="description" name="description" rows="5"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                required>{{ old('description') }}</textarea>
            <x-input-error :messages="$errors->get('description')" class="mt-1" />
        </div>

        {{-- Website --}}
        <div>
            <x-input-label for="website" :value="__('calendar.field_website')" />
            <x-text-input id="website" name="website" type="url" class="mt-1 block w-full" :value="old('website')" placeholder="https://" />
            <x-input-error :messages="$errors->get('website')" class="mt-1" />
        </div>

        {{-- Image --}}
        <div>
            <x-input-label for="image" :value="__('calendar.field_image')" />
            <input id="image" name="image" type="file" accept="image/*"
                class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <x-input-error :messages="$errors->get('image')" class="mt-1" />
        </div>

        {{-- Submitter Email --}}
        <div>
            <x-input-label for="submitter_email" :value="__('calendar.field_email')" />
            <x-text-input id="submitter_email" name="submitter_email" type="email" class="mt-1 block w-full" :value="old('submitter_email')" required />
            <p class="text-xs text-gray-500 mt-1">{{ __('calendar.email_note') }}</p>
            <x-input-error :messages="$errors->get('submitter_email')" class="mt-1" />
        </div>

        {{-- Featured Listing Add-on --}}
        <div x-data="{ featured: false }">
            <div
                class="border-2 p-4 cursor-pointer transition-colors"
                :class="featured ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white'"
                style="border-radius: 2px;"
                @click="featured = !featured"
            >
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="feature_event" value="1" id="feature_event"
                           x-model="featured" @click.stop
                           class="mt-0.5 rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                    <div>
                        <label for="feature_event" class="font-semibold text-gray-900 cursor-pointer select-none">
                            {{ __('calendar.featured_addon_label') }}
                        </label>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ __('calendar.featured_addon_desc') }}
                        </p>
                    </div>
                </div>
            </div>
            <div x-show="featured" x-cloak
                 class="text-sm text-blue-700 bg-blue-50 px-4 py-2 mt-1"
                 style="border-radius: 2px; border: 1px solid #bfdbfe;">
                {{ __('calendar.featured_addon_note') }}
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <x-primary-button>{{ __('calendar.submit_button') }}</x-primary-button>
            <a href="{{ route('calendar.index') }}" class="text-sm text-gray-500 hover:text-gray-700">{{ __('calendar.cancel') }}</a>
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
