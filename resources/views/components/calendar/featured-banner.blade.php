@if($featured->isNotEmpty())
<div
    x-data="{
        current: 0,
        total: {{ $featured->count() }},
        timer: null,
        start() {
            this.timer = setInterval(() => {
                this.current = (this.current + 1) % this.total;
            }, 5000);
        }
    }"
    x-init="start()"
    class="relative overflow-hidden mb-6 h-72 sm:h-80 md:h-96"
    style="background-color: var(--color-ink); border-radius: 2px;"
>
    @foreach($featured as $i => $item)
    @if($item->event)
    <a
        href="{{ route('events.show', $item->event) }}"
        x-show="current === {{ $i }}"
        x-transition:enter="transition ease-out duration-500"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-0 flex flex-col"
    >
        {{-- Background image --}}
        @if($item->event->image_url)
        <img src="{{ $item->event->image_url }}" alt="{{ $item->event->title }}" class="absolute inset-0 w-full h-full object-cover opacity-30">
        @endif

        {{-- Top label bar --}}
        <div class="relative z-10 flex items-center justify-between px-6 py-3" style="border-bottom: 1px solid rgba(255,255,255,0.1)">
            <span class="text-xs font-bold uppercase tracking-[0.2em]" style="color: var(--color-accent)">★ Featured Event</span>
            <span class="text-xs hidden sm:block" style="color: rgba(255,255,255,0.4)">{{ $item->event->start_date->format('l, F j') }}</span>
        </div>

        {{-- Editorial content --}}
        <div class="relative z-10 flex-1 flex flex-col justify-end p-6">
            @if($item->event->category)
            <div class="mb-3">
                <span class="text-xs font-bold uppercase tracking-widest px-2 py-1 text-white" style="background-color: {{ $item->event->category->color }}">
                    {{ $item->event->category->name }}
                </span>
            </div>
            @endif

            <h2 class="font-display text-white leading-tight" style="font-size: clamp(1.6rem, 4vw, 2.8rem)">
                {{ $item->event->title }}
            </h2>
            <p class="mt-2 text-sm" style="color: rgba(255,255,255,0.55)">
                {{ $item->event->start_date->format('l, F j, Y') }}
                @if($item->event->location)
                &bull; {{ $item->event->location->city }}
                @endif
            </p>
            <div class="mt-4">
                <span class="text-sm font-bold uppercase tracking-widest text-white pb-0.5 inline-block" style="border-bottom: 2px solid var(--color-accent)">
                    View Details →
                </span>
            </div>
        </div>
    </a>
    @endif
    @endforeach

    {{-- Dot indicators --}}
    @if($featured->count() > 1)
    <div class="absolute bottom-4 right-5 flex gap-1.5 z-20">
        @foreach($featured as $i => $item)
        <button
            @click="current = {{ $i }}; clearInterval(timer); start()"
            class="w-2 h-2 rounded-full transition-colors"
            :class="current === {{ $i }} ? 'bg-white' : 'bg-white/30'"
        ></button>
        @endforeach
    </div>
    @endif
</div>
@endif
