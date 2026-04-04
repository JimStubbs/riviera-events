@extends($isEmbed ? 'layouts.embed' : 'layouts.calendar')

@section('title', $event->title)

@push('head')
<meta property="og:title" content="{{ $event->title }}" />
<meta property="og:description" content="{{ Str::limit(html_entity_decode(strip_tags($event->description), ENT_QUOTES | ENT_HTML5, 'UTF-8'), 200) }}" />
@if($event->image_url)
<meta property="og:image" content="{{ $event->image_url }}" />
@endif
<meta property="og:url" content="{{ url()->current() }}" />
<meta property="og:type" content="event" />
@endpush

@section('content')
<div class="flex gap-8">

    {{-- Main Content --}}
    <article class="flex-1 min-w-0">

        {{-- Hero image --}}
        @if($event->image_url)
        <div class="aspect-video rounded-xl overflow-hidden mb-6">
            <img src="{{ $event->image_url }}" alt="{{ $event->title }}" class="w-full h-full object-cover">
        </div>
        @endif

        {{-- Category & Premium badge --}}
        <div class="flex flex-wrap gap-2 mb-3">
            @if($event->category)
            <span class="inline-block px-2 py-0.5 rounded text-sm font-semibold text-white" style="background-color: {{ $event->category->color }}">
                {{ $event->category->name }}
            </span>
            @endif
            @if($event->is_premium)
            <span class="inline-block px-2 py-0.5 rounded text-sm font-semibold bg-yellow-100 text-yellow-800">⭐ Premium</span>
            @endif
        </div>

        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $event->title }}</h1>

        {{-- Meta --}}
        <div class="flex flex-wrap gap-4 text-sm text-gray-500 mb-6">
            <span>📅 {{ $event->start_date->format('l, F j, Y') }}
                @if($event->end_date && !$event->start_date->isSameDay($event->end_date))
                    – {{ $event->end_date->format('F j, Y') }}
                @endif
            </span>
            @if($event->location)
            <span>📍 {{ $event->location->city }}, {{ $event->location->state }}</span>
            @endif
            @if($event->organizer)
            <span>👤 {{ $event->organizer }}</span>
            @endif
        </div>

        {{-- Description --}}
        <div class="prose prose-gray max-w-none mb-8">
            {!! $event->description !!}
        </div>

        {{-- Action Buttons --}}
        <div
            x-data="{
                shareOpen: false,
                copied: false,
                shareUrl: '{{ url()->current() }}',
                copyLink() {
                    navigator.clipboard.writeText(this.shareUrl);
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                }
            }"
            x-effect="document.body.style.overflow = shareOpen ? 'hidden' : ''"
            @keydown.escape.window="shareOpen = false"
        >
            <div class="flex flex-wrap gap-3">
                <a
                    href="{{ $event->google_calendar_url }}"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium"
                >
                    + Add to Google Calendar
                </a>
                <a
                    href="{{ route('events.ics', $event) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium"
                >
                    ↓ Download .ics
                </a>
                @if($event->website)
                <a
                    href="{{ $event->website }}"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium"
                >
                    🔗 Event Website
                </a>
                @endif
                <button
                    @click="shareOpen = true"
                    class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 text-sm font-medium"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/>
                    </svg>
                    Share With Friends
                </button>
            </div>

            {{-- Share Modal --}}
            <div
                x-show="shareOpen"
                x-cloak
                style="display:none"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @click.self="shareOpen = false"
            >
                {{-- Backdrop --}}
                <div
                    x-show="shareOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="absolute inset-0 bg-black/50"
                    @click="shareOpen = false"
                ></div>

                {{-- Panel --}}
                <div
                    x-show="shareOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm"
                >
                    {{-- Header --}}
                    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                        <h3 class="text-base font-semibold text-gray-900">Share With Friends</h3>
                        <button
                            @click="shareOpen = false"
                            class="text-gray-400 hover:text-gray-600 rounded-lg p-1 hover:bg-gray-100 transition-colors"
                            aria-label="Close"
                        >
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Share buttons grid --}}
                    <div class="p-5 grid grid-cols-3 gap-3">

                        {{-- Facebook --}}
                        <a
                            href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-xl border border-gray-200 text-gray-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 transition-colors text-xs font-medium"
                        >
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Facebook
                        </a>

                        {{-- Messenger --}}
                        <a
                            href="@if(config('services.facebook.app_id'))https://www.facebook.com/dialog/send?link={{ urlencode(url()->current()) }}&app_id={{ config('services.facebook.app_id') }}&redirect_uri={{ urlencode(url()->current()) }}@else fb-messenger://share/?link={{ urlencode(url()->current()) }}@endif"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-xl border border-gray-200 text-gray-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600 transition-colors text-xs font-medium"
                        >
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 0C5.373 0 0 4.974 0 11.111c0 3.498 1.744 6.614 4.469 8.654V24l4.088-2.242c1.092.3 2.246.464 3.443.464 6.627 0 12-4.975 12-11.111C24 4.974 18.627 0 12 0zm1.191 14.963l-3.055-3.26-5.963 3.26L10.732 8l3.131 3.259L19.752 8l-6.561 6.963z"/>
                            </svg>
                            Messenger
                        </a>

                        {{-- LinkedIn --}}
                        <a
                            href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-xl border border-gray-200 text-gray-600 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 transition-colors text-xs font-medium"
                        >
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 01-2.063-2.065 2.064 2.064 0 112.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                            LinkedIn
                        </a>

                        {{-- WhatsApp --}}
                        <a
                            href="https://wa.me/?text={{ urlencode($event->title . ' ' . url()->current()) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-xl border border-gray-200 text-gray-600 hover:border-green-200 hover:bg-green-50 hover:text-green-700 transition-colors text-xs font-medium"
                        >
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                            </svg>
                            WhatsApp
                        </a>

                        {{-- X (Twitter) --}}
                        <a
                            href="https://twitter.com/intent/tweet?text={{ urlencode($event->title) }}&url={{ urlencode(url()->current()) }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-xl border border-gray-200 text-gray-600 hover:border-gray-400 hover:bg-gray-100 hover:text-gray-900 transition-colors text-xs font-medium"
                        >
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                            </svg>
                            X
                        </a>

                        {{-- Email --}}
                        <a
                            href="mailto:?subject={{ rawurlencode($event->title) }}&body={{ rawurlencode('Check out this event: ' . url()->current()) }}"
                            class="flex flex-col items-center gap-1.5 px-2 py-3 rounded-xl border border-gray-200 text-gray-600 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800 transition-colors text-xs font-medium"
                        >
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="4" width="20" height="16" rx="2"/>
                                <path d="M2 7l10 7 10-7"/>
                            </svg>
                            Email
                        </a>

                        {{-- Instagram (no web share URL — copies link and opens Instagram) --}}
                        <button
                            x-data="{ igCopied: false }"
                            @click="
                                navigator.clipboard.writeText(shareUrl);
                                igCopied = true;
                                setTimeout(() => igCopied = false, 3000);
                                window.open('https://www.instagram.com', '_blank', 'noopener,noreferrer');
                            "
                            class="relative flex flex-col items-center gap-1.5 px-2 py-3 rounded-xl border border-gray-200 text-gray-600 hover:border-pink-200 hover:bg-pink-50 hover:text-pink-700 transition-colors text-xs font-medium w-full"
                        >
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/>
                            </svg>
                            <span x-show="!igCopied">Instagram</span>
                            <span x-show="igCopied" class="text-pink-600">Link copied!</span>
                        </button>

                    </div>

                    {{-- Copy link --}}
                    <div class="px-5 pb-5">
                        <div class="flex gap-2">
                            <input
                                type="text"
                                readonly
                                :value="shareUrl"
                                class="flex-1 min-w-0 text-xs text-gray-500 bg-gray-50 border border-gray-200 rounded-lg px-3 py-2 focus:outline-none select-all cursor-text"
                                @click="$el.select()"
                            >
                            <button
                                @click="copyLink()"
                                class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-xs font-medium transition-colors"
                                :class="copied ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-gray-100 text-gray-700 border border-gray-200 hover:bg-gray-200'"
                            >
                                <svg x-show="!copied" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                                </svg>
                                <svg x-show="copied" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                                <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </article>

    {{-- Sidebar --}}
    @if(!$isEmbed)
    <aside class="w-64 flex-shrink-0 hidden lg:block">
        <x-calendar.ad-sidebar :ad="$sidebarAd" />
    </aside>
    @endif

</div>
@endsection
