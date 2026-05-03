<div id="filter-bar" class="bg-white p-4 flex flex-wrap gap-3 items-end mb-6" style="border: 1px solid var(--color-border); border-radius: 2px;">

    {{-- Search --}}
    <div class="flex-1 min-w-48">
        <label class="block text-xs font-bold uppercase tracking-widest mb-1.5" style="color: var(--color-muted)">{{ __('calendar.search') }}</label>
        <input
            id="filter-search"
            type="text"
            placeholder="{{ __('calendar.search_placeholder') }}"
            class="w-full px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 transition-shadow"
            style="border: 1px solid var(--color-border); border-radius: 2px; color: var(--color-ink); --tw-ring-color: var(--color-accent);"
        >
    </div>

    {{-- Location --}}
    <div class="min-w-36">
        <label class="block text-xs font-bold uppercase tracking-widest mb-1.5" style="color: var(--color-muted)">{{ __('calendar.location_label') }}</label>
        <select id="filter-location" class="w-full px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 transition-shadow"
            style="border: 1px solid var(--color-border); border-radius: 2px; color: var(--color-ink); --tw-ring-color: var(--color-accent);">
            <option value="">{{ __('calendar.all_locations') }}</option>
        </select>
    </div>

    {{-- Category --}}
    <div class="min-w-36">
        <label class="block text-xs font-bold uppercase tracking-widest mb-1.5" style="color: var(--color-muted)">{{ __('calendar.category_label') }}</label>
        <select id="filter-category" class="w-full px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 transition-shadow"
            style="border: 1px solid var(--color-border); border-radius: 2px; color: var(--color-ink); --tw-ring-color: var(--color-accent);">
            <option value="">{{ __('calendar.all_categories') }}</option>
        </select>
    </div>

    {{-- Date From --}}
    <div class="min-w-36">
        <label class="block text-xs font-bold uppercase tracking-widest mb-1.5" style="color: var(--color-muted)">{{ __('calendar.from_label') }}</label>
        <input id="filter-start" type="date" class="w-full px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 transition-shadow"
            style="border: 1px solid var(--color-border); border-radius: 2px; color: var(--color-ink); --tw-ring-color: var(--color-accent);">
    </div>

    {{-- Date To --}}
    <div class="min-w-36">
        <label class="block text-xs font-bold uppercase tracking-widest mb-1.5" style="color: var(--color-muted)">{{ __('calendar.to_label') }}</label>
        <input id="filter-end" type="date" class="w-full px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 transition-shadow"
            style="border: 1px solid var(--color-border); border-radius: 2px; color: var(--color-ink); --tw-ring-color: var(--color-accent);">
    </div>

    {{-- Featured Toggle --}}
    <div class="flex items-center gap-2 pb-0.5">
        <input id="filter-featured" type="checkbox" class="h-4 w-4 rounded focus:ring-2" style="border-color: var(--color-border); --tw-ring-color: var(--color-accent); accent-color: var(--color-accent);">
        <label for="filter-featured" class="text-sm font-medium" style="color: var(--color-ink)">{{ __('calendar.featured_only') }}</label>
    </div>

    {{-- View Toggles --}}
    <div class="flex overflow-hidden" style="border: 1px solid var(--color-border); border-radius: 2px;">
        <button data-view="list"  class="view-btn view-btn-active px-3 py-2 text-sm" title="{{ __('calendar.view_list') }}">≡</button>
        <button data-view="month" class="view-btn view-btn-inactive px-3 py-2 text-sm border-l" style="border-color: var(--color-border)" title="{{ __('calendar.view_month') }}">▦</button>
        <button data-view="week"  class="view-btn view-btn-inactive px-3 py-2 text-sm border-l" style="border-color: var(--color-border)" title="{{ __('calendar.view_week') }}">W</button>
        <button data-view="day"   class="view-btn view-btn-inactive px-3 py-2 text-sm border-l" style="border-color: var(--color-border)" title="{{ __('calendar.view_day') }}">D</button>
    </div>

</div>
