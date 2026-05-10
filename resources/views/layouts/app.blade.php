<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — Sacca' : config('app.name', 'Sacca') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

{{--
    Layout strategy:
    - body is a full-viewport flex row (no scroll on body itself)
    - sidebar: fixed, full height, scrolls its nav independently
    - right side: fixed offset from sidebar, fills remaining width,
      internal flex column — topbar fixed at top, main scrolls
--}}
<body class="bg-gray-50 overflow-hidden h-screen" x-data="{ sidebarOpen: true }">

    {{-- ── Sidebar ─────────────────────────────────────────────── --}}
    <aside
        class="fixed inset-y-0 left-0 z-30 flex flex-col w-64 bg-gray-900 text-white transition-transform duration-300"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        {{-- Logo --}}
        <div class="flex-shrink-0 flex items-center justify-between h-16 px-6 border-b border-gray-700">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-indigo-500 rounded-lg flex items-center justify-center text-xs font-bold">S</div>
                <span class="text-lg font-bold tracking-wide">Sacca</span>
            </div>
            <span class="text-xs text-gray-500 uppercase tracking-wider font-medium">
                {{ auth()->user()?->branch?->code ?? 'All' }}
            </span>
        </div>

        {{-- Navigation — scrollable, never clips --}}
        <nav class="flex-1 min-h-0 overflow-y-auto px-3 py-4 space-y-0.5
                    scrollbar-thin scrollbar-track-gray-800 scrollbar-thumb-gray-600">
            @include('layouts.partials.sidebar-nav')
        </nav>

        {{-- User footer — always visible at bottom --}}
        <div class="flex-shrink-0 border-t border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-indigo-500 flex items-center justify-center text-sm font-bold flex-shrink-0">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ auth()->user()?->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ auth()->user()?->role?->label() }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit"
                    class="w-full text-left text-xs text-gray-400 hover:text-white transition-colors py-1">
                    Sign out →
                </button>
            </form>
        </div>
    </aside>

    {{-- ── Main wrapper — sits to the right of sidebar ──────────── --}}
    <div
        class="fixed inset-y-0 right-0 flex flex-col transition-all duration-300"
        :class="sidebarOpen ? 'left-64' : 'left-0'"
    >
        {{-- Topbar — always visible, never scrolls away --}}
        <header class="flex-shrink-0 flex items-center h-16 px-6 bg-white border-b border-gray-200 shadow-sm z-20">
            {{-- Hamburger --}}
            <button @click="sidebarOpen = !sidebarOpen"
                class="mr-4 p-1.5 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Page title (from Livewire #[Title] or $heading slot) --}}
            <div class="flex-1 min-w-0">
                @isset($heading)
                    <h2 class="text-base font-semibold text-gray-800 truncate">{{ $heading }}</h2>
                @else
                    <h2 class="text-base font-semibold text-gray-800">
                        {{ isset($title) ? $title : 'Dashboard' }}
                    </h2>
                @endisset
            </div>

            {{-- Right side: branch badge + quick links --}}
            <div class="flex items-center gap-3">
                @if(auth()->user()?->branch)
                    <span class="text-xs bg-indigo-100 text-indigo-700 px-2.5 py-1 rounded-full font-medium">
                        {{ auth()->user()->branch->name }}
                    </span>
                @else
                    <span class="text-xs bg-purple-100 text-purple-700 px-2.5 py-1 rounded-full font-medium">
                        All Branches
                    </span>
                @endif

                <a href="{{ route('dashboard') }}"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 hover:text-indigo-600 transition-colors"
                    title="Dashboard">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </a>
            </div>
        </header>

        {{-- Page content — this is the ONLY element that scrolls --}}
        <main class="flex-1 min-h-0 overflow-y-auto p-6">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
