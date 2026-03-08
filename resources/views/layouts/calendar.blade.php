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
</head>
<body class="font-sans antialiased">

    {{-- Masthead strip --}}
    <div style="background-color: var(--color-accent)">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-1.5 flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-[0.18em] text-white opacity-90">{{ config('app.name', 'Riviera Events') }}</span>
            <span class="text-xs text-white opacity-50 hidden sm:block">{{ now()->format('l, F j, Y') }}</span>
        </div>
    </div>

    <header class="bg-white" style="border-bottom: 1px solid var(--color-border)">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-14">
            <a href="{{ route('calendar.index') }}" class="font-display text-xl" style="color: var(--color-ink)">
                {{ config('app.name', 'Riviera Events') }}
            </a>
            <nav class="flex items-center gap-5 text-sm">
                <a href="{{ route('calendar.index') }}" class="font-medium transition-colors" style="color: var(--color-muted)">Events</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="font-medium transition-colors" style="color: var(--color-muted)">My Events</a>
                @else
                    <a href="{{ route('login') }}" class="font-medium transition-colors" style="color: var(--color-muted)">Login</a>
                    <a href="{{ route('register') }}" class="text-white text-xs font-bold uppercase tracking-widest px-4 py-2 transition-opacity hover:opacity-80" style="background-color: var(--color-accent)">Sign Up</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <footer class="mt-12 py-8 text-center text-sm" style="border-top: 1px solid var(--color-border); color: var(--color-muted)">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
    </footer>

    @stack('scripts')
</body>
</html>
