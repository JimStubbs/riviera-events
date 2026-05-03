<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Riviera Events'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/calendar.js'])
    @stack('head')
</head>
<body class="font-sans antialiased">

    {{-- Single combined header --}}
    <header style="background-color: var(--color-accent)">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <a href="{{ route('calendar.index') }}" class="font-display text-2xl text-white" style="font-family: 'DM Sans', ui-sans-serif, system-ui, sans-serif; font-weight: 700;">
                {{ __('calendar.site_title') }}
            </a>
            <div class="flex items-center gap-4">
                {{-- Language toggle --}}
                @if(app()->getLocale() === 'es')
                    <a href="{{ route('locale.switch', 'en') }}" class="text-xs font-bold uppercase tracking-widest text-white opacity-70 hover:opacity-100 transition-opacity">
                        EN <span class="opacity-40">/</span> <span class="underline">ES</span>
                    </a>
                @else
                    <a href="{{ route('locale.switch', 'es') }}" class="text-xs font-bold uppercase tracking-widest text-white opacity-70 hover:opacity-100 transition-opacity">
                        <span class="underline">EN</span> <span class="opacity-40">/</span> ES
                    </a>
                @endif

                @if(request()->routeIs('submit.*'))
                <a href="{{ route('calendar.index') }}"
                   class="text-white text-xs font-bold uppercase tracking-widest px-4 py-2 transition-opacity hover:opacity-80"
                   style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.4);">
                    {{ __('calendar.view_events') }}
                </a>
                @else
                <a href="{{ route('submit.create') }}"
                   class="text-white text-xs font-bold uppercase tracking-widest px-4 py-2 transition-opacity hover:opacity-80"
                   style="background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.4);">
                    {{ __('calendar.add_event') }}
                </a>
                @endif
                @php
                    $headerDate = app()->getLocale() === 'es'
                        ? \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY')
                        : now()->format('l, F j, Y');
                @endphp
                <span class="text-sm text-white opacity-60 hidden sm:block">{{ $headerDate }}</span>
                @auth
                    <a href="{{ route('dashboard') }}" class="text-sm font-medium text-white opacity-80 hover:opacity-100 transition-opacity">{{ __('calendar.my_events') }}</a>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-white opacity-80 hover:opacity-100 transition-opacity">{{ __('calendar.login') }}</a>
                @endauth
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <footer class="mt-12 py-8 text-center text-sm" style="border-top: 1px solid var(--color-border); color: var(--color-muted)">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('calendar.all_rights') }}</p>
    </footer>

    @stack('scripts')
</body>
</html>
