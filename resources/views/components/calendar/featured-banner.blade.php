@if($featured->isNotEmpty())
@php $count = $featured->count(); @endphp
<div
    x-data="{
        index: 0,
        count: {{ $count }},
        transitioning: true,
        timer: null,
        get cardWidth() {
            return this.$el.clientWidth / 3;
        },
        setup() {
            this.start();
        },
        get trackStyle() {
            const offset = this.index * this.cardWidth;
            const tr = this.transitioning ? 'transform 0.5s ease-in-out' : 'none';
            return `display:flex; transform: translateX(-${offset}px); transition: ${tr};`;
        },
        start() {
            this.timer = setInterval(() => this.advance(), 4000);
        },
        advance() {
            this.transitioning = true;
            this.index++;
            if (this.index >= this.count) {
                setTimeout(() => {
                    this.transitioning = false;
                    this.index = 0;
                    this.$nextTick(() => setTimeout(() => { this.transitioning = true; }, 30));
                }, 520);
            }
        },
        goNext() {
            clearInterval(this.timer);
            this.advance();
            this.start();
        },
        goPrev() {
            clearInterval(this.timer);
            if (this.index === 0) {
                this.transitioning = false;
                this.index = this.count;
                this.$nextTick(() => setTimeout(() => {
                    this.transitioning = true;
                    this.index = this.count - 1;
                }, 30));
            } else {
                this.transitioning = true;
                this.index--;
            }
            this.start();
        }
    }"
    x-init="setup()"
    class="relative overflow-hidden mb-6"
    style="border-radius: 2px;"
>
    {{-- Sliding track: original cards + clones for seamless loop --}}
    <div :style="trackStyle">

        @php $allItems = $featured->concat($featured); @endphp
        @foreach($allItems as $item)
        @if($item->event)
        @php
            $catColor = $item->event->category?->color ?? '#055c9d';
        @endphp
        <a href="{{ route('events.show', $item->event) }}"
           class="fc-card relative overflow-hidden group flex flex-col flex-shrink-0"
           style="width: calc(100% / 3); min-height: 300px; background-color: {{ $catColor }}; transition: transform 0.3s ease, box-shadow 0.3s ease;"
           onmouseenter="this.style.transform='translateY(-3px)';this.style.boxShadow='0 12px 40px rgba(0,0,0,0.25)'"
           onmouseleave="this.style.transform='translateY(0)';this.style.boxShadow='none'">

            {{-- Full-bleed image --}}
            @if($item->event->image_url)
            <img src="{{ $item->event->image_url }}"
                 alt="{{ $item->event->title }}"
                 class="absolute inset-0 w-full h-full object-cover"
                 style="transition: transform 0.5s ease; transform: scale(1.0);"
                 onmouseenter="this.style.transform='scale(1.06)'"
                 onmouseleave="this.style.transform='scale(1.0)'">
            @endif

            {{-- Gradient overlay: vibrant at top, deep at bottom --}}
            <div class="absolute inset-0"
                 style="background: linear-gradient(160deg, rgba(0,0,0,0.15) 0%, rgba(0,0,0,0.75) 100%);"></div>

            {{-- Featured badge --}}
            <div class="relative z-10 p-4">
                <span style="display:inline-flex; align-items:center; gap:5px; background: linear-gradient(90deg,#f59e0b,#fbbf24); color:#1a1a1a; font-size:0.65rem; font-weight:800; text-transform:uppercase; letter-spacing:0.15em; padding:3px 10px; border-radius:2px;">
                    ★ Featured
                </span>
            </div>

            {{-- Content anchored to bottom --}}
            <div class="relative z-10 flex-1 flex flex-col justify-end p-4 pt-0">
                @if($item->event->category)
                <span class="text-xs font-bold uppercase tracking-widest self-start mb-2 px-2 py-0.5"
                      style="background: rgba(255,255,255,0.18); color:#fff; border-radius:2px; backdrop-filter:blur(4px);">
                    {{ $item->event->category->name }}
                </span>
                @endif

                <h3 class="text-white leading-snug mb-1"
                    style="font-size: clamp(1.05rem, 2.5vw, 1.4rem); font-weight: 700; text-shadow: 0 1px 6px rgba(0,0,0,0.5); display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                    {{ $item->event->title }}
                </h3>

                <p class="text-sm mb-3" style="color: rgba(255,255,255,0.75); text-shadow: 0 1px 4px rgba(0,0,0,0.4);">
                    {{ $item->event->start_date->format('D, M j') }}
                    @if($item->event->location)
                    &nbsp;·&nbsp;{{ $item->event->location->city }}
                    @endif
                </p>

                <span style="display:inline-flex; align-items:center; gap:6px; color:#fbbf24; font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:0.1em;">
                    View Event <span style="font-size:1rem; line-height:1;">→</span>
                </span>
            </div>
        </a>
        @endif
        @endforeach

    </div>

    {{-- Prev / Next arrows --}}
    <button @click="goPrev()"
            style="position:absolute; left:8px; top:50%; transform:translateY(-50%); z-index:20; width:2rem; height:2rem; display:flex; align-items:center; justify-content:center; border-radius:9999px; background:rgba(0,0,0,0.55); color:#fff; font-size:1.4rem; line-height:1; border:none; cursor:pointer;">&#8249;</button>
    <button @click="goNext()"
            style="position:absolute; right:8px; top:50%; transform:translateY(-50%); z-index:20; width:2rem; height:2rem; display:flex; align-items:center; justify-content:center; border-radius:9999px; background:rgba(0,0,0,0.55); color:#fff; font-size:1.4rem; line-height:1; border:none; cursor:pointer;">&#8250;</button>

    {{-- Dot indicators --}}
    <div class="flex justify-center gap-2 py-2" style="background-color: var(--color-ink)">
        @for($i = 0; $i < $count; $i++)
        <button @click="index={{ $i }}; clearInterval(timer); start();"
                class="w-2 h-2 rounded-full transition-colors duration-300"
                :class="index % count === {{ $i }} ? 'bg-white' : 'bg-white/25'"
                aria-label="Event {{ $i + 1 }}"></button>
        @endfor
    </div>
</div>
@endif
