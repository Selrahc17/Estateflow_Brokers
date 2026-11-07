<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    {{-- Runs before first paint so dark mode pages never flash white. --}}
    <script>
        if (localStorage.getItem('darkMode') === 'true') document.documentElement.classList.add('dark');
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1A6B79">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="EstateFlow">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <title>EstateFlow Broker - @yield('title', 'Dashboard')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .dark { color-scheme: dark; }
        .dark body { color: #e7e5e4; }
    </style>
</head>
<body class="bg-stone-100 dark:bg-stone-950 font-sans" x-data="{ sidebarOpen: window.innerWidth >= 1024, isDesktop: window.innerWidth >= 1024, darkMode: document.documentElement.classList.contains('dark') }" x-init="isDesktop = window.innerWidth >= 1024; sidebarOpen = isDesktop" @resize.window="const wasDesktop = isDesktop; isDesktop = window.innerWidth >= 1024; if (wasDesktop !== isDesktop) sidebarOpen = isDesktop">
    @php
        $navSections = [
            ['title' => null, 'links' => [
                ['route' => 'broker.dashboard', 'active' => 'broker.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'broker.performance', 'active' => 'broker.performance', 'label' => 'Agent Performance', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2'],
                ['route' => 'broker.commissions.index', 'active' => 'broker.commissions.*', 'label' => 'Commission', 'icon' => 'M12 8c-2.21 0-4 1.12-4 2.5s1.79 2.5 4 2.5 4 1.12 4 2.5-1.79 2.5-4 2.5m0-10V5m0 13v1m8-7a8 8 0 11-16 0 8 8 0 0116 0z'],
                ['route' => 'broker.property-lists', 'active' => 'broker.property-lists', 'label' => 'Agent Property Lists', 'icon' => 'M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm4 2h8m-8 4h8m-8 4h5'],
            ]],
            ['title' => 'Management', 'links' => [
                ['route' => 'broker.agents.index', 'active' => 'broker.agents.*', 'label' => 'Agent Management', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                ['route' => 'broker.reports.index', 'active' => 'broker.reports.*', 'label' => 'Reports and Analytics', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2'],
            ]],
            ['title' => 'Team', 'links' => [
                ['route' => 'broker.team.clients', 'active' => 'broker.team.clients*', 'label' => 'Clients', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ['route' => 'broker.team.inquiries', 'active' => 'broker.team.inquiries', 'label' => 'Inquiries', 'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['route' => 'broker.team.reservations', 'active' => 'broker.team.reservations', 'label' => 'Reservations', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ]],
            ['title' => 'Messages', 'links' => [
                ['route' => 'broker.messages.index', 'active' => 'broker.messages.*', 'label' => 'Message', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
                ['route' => 'broker.notifications.index', 'active' => 'broker.notifications.*', 'label' => 'Notification', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
            ]],
            ['title' => 'Oversight', 'links' => [
                ['route' => 'broker.audit.index', 'active' => 'broker.audit.*', 'label' => 'Audit Logs', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['route' => 'broker.settings.index', 'active' => 'broker.settings.*', 'label' => 'Profile Settings', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
            ]],
        ];
    @endphp

    <div x-show="sidebarOpen && !isDesktop" x-transition.opacity @click="sidebarOpen = false" style="display: none" class="fixed inset-0 z-[9998] bg-black/40 lg:hidden" aria-hidden="true"></div>
    <aside :class="!isDesktop && sidebarOpen ? 'translate-x-0!' : ''" class="fixed inset-y-0 left-0 z-[9999] flex w-64 flex-col bg-[var(--color-sidebar)] text-white transition-transform duration-300 max-lg:-translate-x-full">
        @include('layouts.partials.sidebar-brand', ['portal' => 'Broker Portal'])
        @include('layouts.partials.sidebar-nav', ['label' => 'Broker navigation', 'sections' => $navSections])

        <div class="shrink-0 border-t border-[var(--color-primary-dark)] p-3 pb-[env(safe-area-inset-bottom)]">
            <a href="{{ route('broker.settings.index') }}" class="flex items-center gap-3 rounded-lg px-2 py-2 text-teal-100 transition hover:bg-[var(--color-sidebar-hover)]">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[var(--color-primary)] text-sm font-bold text-white">
                    @if(auth()->user()->avatar)
                        <img src="{{ str_starts_with(auth()->user()->avatar, 'http') ? auth()->user()->avatar : asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="h-full w-full object-cover">
                    @else
                        {{ strtoupper(substr(auth()->user()->name ?? 'B', 0, 1)) }}
                    @endif
                </div>
                <div x-show="sidebarOpen" class="min-w-0">
                    <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name ?? 'Broker' }}</p>
                    <p class="truncate text-xs text-stone-400">{{ auth()->user()->email ?? '' }}</p>
                </div>
            </a>
            <div x-show="sidebarOpen" class="mt-1">
                @include('pages.auth._sign-out-confirm', ['triggerClass' => 'flex w-full items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] transition hover:bg-[var(--color-sidebar-hover)]'])
            </div>
        </div>
    </aside>

    <main class="flex min-h-screen flex-col transition-all duration-300 lg:ml-64">
        <header class="sticky top-0 z-40 flex items-center gap-4 border-b border-stone-200 dark:border-stone-800 bg-white dark:bg-stone-900 px-4 py-5 sm:px-8">
            <button @click="sidebarOpen = true" x-show="!isDesktop" aria-label="Open navigation menu" class="text-stone-500 dark:text-stone-400 transition hover:text-[var(--color-primary)] dark:hover:text-teal-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="min-w-0 flex-1">
                <h1 class="text-xl font-bold text-stone-800 dark:text-white">@yield('page-title', 'Broker Dashboard')</h1>
                <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">@yield('page-subtitle')</p>
            </div>
            @include('layouts.partials.theme-toggle')
        </header>
        <section class="flex-1 p-4 sm:p-8">
            @yield('content')
        </section>
        <footer class="border-t border-stone-200 dark:border-stone-800 py-4 text-center text-xs text-stone-500 dark:text-stone-400">
            © {{ date('Y') }} EstateFlow. All rights reserved.
        </footer>
    </main>
    @stack('scripts')
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
        }
    </script>
</body>
</html>
