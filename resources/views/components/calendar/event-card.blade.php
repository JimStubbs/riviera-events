{{-- Used by JS template, but also available as Blade component for SSR --}}
@props(['event'])
@php
    $date = !empty($event['start_date']) ? new \DateTime($event['start_date']) : null;
    $monthsShort = __('calendar.months_short');
    $month = $date ? strtoupper($monthsShort[$date->format('n') - 1]) : '';
    $day = $date ? $date->format('j') : '';
    $catColor = $event['category']['color'] ?? 'var(--color-accent)';
@endphp
<a
    href="/events/{{ $event['slug'] }}"
    class="block bg-white overflow-hidden group transition-all duration-200 hover:shadow-lg"
    style="border: 1px solid var(--color-border); border-radius: 2px;"
>
    {{-- Date flag + category row --}}
    <div class="flex items-stretch" style="border-bottom: 1px solid var(--color-border)">
        @if($date)
        <div class="text-white px-3 py-2 text-center flex-shrink-0" style="min-width: 52px; background-color: var(--color-accent)">
            <div class="text-[10px] font-bold uppercase tracking-widest">{{ $month }}</div>
            <div class="text-xl font-bold leading-none">{{ $day }}</div>
        </div>
        @endif
        <div class="px-3 py-2 flex items-center flex-1 gap-2">
            @if(!empty($event['category']))
            <span class="text-xs font-bold uppercase tracking-widest" style="color: {{ $catColor }}">
                {{ $event['category']['name'] }}
            </span>
            @endif
            @if(!empty($event['is_premium']))
            <span class="text-xs font-bold uppercase tracking-wider" style="color: var(--color-accent-2)">{{ __('calendar.featured_badge') }}</span>
            @endif
        </div>
    </div>

    {{-- Image --}}
    @if(!empty($event['image_url']))
    <div class="aspect-video overflow-hidden">
        <img
            src="{{ $event['image_url'] }}"
            alt="{{ $event['title'] }}"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            loading="lazy"
        >
    </div>
    @endif

    {{-- Content --}}
    <div class="p-4">
        <h3 class="font-display text-lg leading-snug line-clamp-2 transition-colors" style="color: var(--color-ink)">
            {{ $event['title'] }}
        </h3>
        @if(!empty($event['location']))
        <p class="text-sm mt-1" style="color: var(--color-muted)">{{ $event['location']['city'] }}</p>
        @endif
        @if(!empty($event['excerpt']))
        <p class="text-sm mt-2 line-clamp-2" style="color: var(--color-muted)">{{ $event['excerpt'] }}</p>
        @endif
    </div>
</a>
