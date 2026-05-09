<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Sacca') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen flex" x-data="{ sidebarOpen: true }">

    {{-- Sidebar --}}
    <aside
        class="fixed inset-y-0 left-0 z-30 flex flex-col w-64 bg-gray-900 text-white transition-transform duration-300"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
        {{-- Logo --}}
        <div class="flex items-center justify-between h-16 px-6 border-b border-gray-700">
            <span class="text-xl font-bold tracking-wide">Sacca</span>
            <span class="text-xs text-gray-400 uppercase tracking-wider">{{ auth()->user()?->branch?->code ?? 'All' }}</span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            @include('layouts.partials.sidebar-nav')
        </nav>

        {{-- User Info --}}
        <div class="border-t border-gray-700 p-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-indigo-500 flex items-center justify-center text-sm font-bold">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ auth()->user()?->name }}</p>
                    <p class="text-xs text-gray-400">{{ auth()->user()?->role?->label() }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full text-left text-xs text-gray-400 hover:text-white transition">
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    {{-- Main content --}}
    <div class="flex-1 flex flex-col min-h-screen transition-all duration-300" :class="sidebarOpen ? 'ml-64' : 'ml-0'">

        {{-- Topbar --}}
        <header class="sticky top-0 z-20 flex items-center h-16 px-6 bg-white border-b border-gray-200 shadow-sm">
            <button @click="sidebarOpen = !sidebarOpen" class="mr-4 text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div class="flex-1">
                @isset($heading)
                    <h2 class="text-lg font-semibold text-gray-800">{{ $heading }}</h2>
                @endisset
            </div>

            <div class="flex items-center gap-4">
                {{-- Branch badge for non-super-admin --}}
                @if(auth()->user()?->branch)
                    <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full font-medium">
                        {{ auth()->user()->branch->name }}
                    </span>
                @endif
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 p-6">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
