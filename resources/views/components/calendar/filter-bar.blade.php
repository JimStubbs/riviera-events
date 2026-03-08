<div id="filter-bar" class="bg-white p-4 flex flex-wrap gap-3 items-end mb-6" style="border: 1px solid var(--color-border); border-radius: 2px;">

    {{-- Search --}}
    <div class="flex-1 min-w-48">
        <label class="block text-xs font-bold uppercase tracking-widest mb-1.5" style="color: var(--color-muted)">Search</label>
        <input
            id="filter-search"
            type="text"
            placeholder="Events, organizers..."
            class="w-full px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 transition-shadow"
            style="border: 1px solid var(--color-border); border-radius: 2px; color: var(--color-ink); --tw-ring-color: var(--color-accent);"
        >
    </div>

    {{-- Location --}}
    <div class="min-w-36">
        <label class="block text-xs font-bold uppercase tracking-widest mb-1.5" style="color: var(--color-muted)">Location</label>
        <select id="filter-location" class="w-full px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 transition-shadow"
            style="border: 1px solid var(--color-border); border-radius: 2px; color: var(--color-ink); --tw-ring-color: var(--color-accent);">
            <option value="">All Locations</option>
        </select>
    </div>

    {{-- Category --}}
    <div class="min-w-36">
        <label class="block text-xs font-bold uppercase tracking-widest mb-1.5" style="color: var(--color-muted)">Category</label>
        <select id="filter-category" class="w-full px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 transition-shadow"
            style="border: 1px solid var(--color-border); border-radius: 2px; color: var(--color-ink); --tw-ring-color: var(--color-accent);">
            <option value="">All Categories</option>
        </select>
    </div>

    {{-- Date From --}}
    <div class="min-w-36">
        <label class="block text-xs font-bold uppercase tracking-widest mb-1.5" style="color: var(--color-muted)">From</label>
        <input id="filter-start" type="date" class="w-full px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 transition-shadow"
            style="border: 1px solid var(--color-border); border-radius: 2px; color: var(--color-ink); --tw-ring-color: var(--color-accent);">
    </div>

    {{-- Date To --}}
    <div class="min-w-36">
        <label class="block text-xs font-bold uppercase tracking-widest mb-1.5" style="color: var(--color-muted)">To</label>
        <input id="filter-end" type="date" class="w-full px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 transition-shadow"
            style="border: 1px solid var(--color-border); border-radius: 2px; color: var(--color-ink); --tw-ring-color: var(--color-accent);">
    </div>

    {{-- Premium Toggle --}}
    <div class="flex items-center gap-2 pb-0.5">
        <input id="filter-premium" type="checkbox" class="h-4 w-4 rounded focus:ring-2" style="border-color: var(--color-border); --tw-ring-color: var(--color-accent); accent-color: var(--color-accent);">
        <label for="filter-premium" class="text-sm font-medium" style="color: var(--color-ink)">Featured only</label>
    </div>

    {{-- View Toggles --}}
    <div class="flex overflow-hidden" style="border: 1px solid var(--color-border); border-radius: 2px;">
        <button data-view="list"  class="view-btn view-btn-active px-3 py-2 text-sm" title="List view">≡</button>
        <button data-view="month" class="view-btn view-btn-inactive px-3 py-2 text-sm border-l" style="border-color: var(--color-border)" title="Month view">▦</button>
        <button data-view="week"  class="view-btn view-btn-inactive px-3 py-2 text-sm border-l" style="border-color: var(--color-border)" title="Week view">W</button>
        <button data-view="day"   class="view-btn view-btn-inactive px-3 py-2 text-sm border-l" style="border-color: var(--color-border)" title="Day view">D</button>
    </div>

</div>
