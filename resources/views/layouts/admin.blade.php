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
    <meta name="apple-mobile-web-app-title" content="EstateFlow Admin">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <title>EstateFlow Admin — @yield('title', 'Dashboard')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .dark { color-scheme: dark; }
        .dark body { color: #e7e5e4; }
    </style>
</head>
<body class="bg-stone-100 dark:bg-stone-950 font-sans" x-data="{
        isDesktop: window.innerWidth >= 1024,
        sidebarOpen: window.innerWidth >= 1024,
        darkMode: document.documentElement.classList.contains('dark'),
    }" @resize.window="const wasDesktop = isDesktop; isDesktop = window.innerWidth >= 1024; if (wasDesktop !== isDesktop) sidebarOpen = isDesktop">

    @php
        $adminUser = auth()->user();
        $avatarUrl = $adminUser?->avatar
            ? (str_starts_with($adminUser->avatar, 'http') ? $adminUser->avatar : asset('storage/' . $adminUser->avatar))
            : null;
        $adminInitial = strtoupper(substr($adminUser->name ?? 'A', 0, 1));
        $navSections = [
            ['title' => 'Overview', 'links' => [
                ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'admin.reports', 'active' => 'admin.reports', 'label' => 'Reports & Analytics', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ]],
            ['title' => 'Management', 'links' => [
                ['route' => 'admin.users', 'active' => 'admin.users*', 'label' => 'User Management', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                ['route' => 'admin.brokers', 'active' => 'admin.brokers*', 'label' => 'Broker Management', 'badge' => $pendingBrokers, 'badgeColor' => 'bg-amber-600', 'icon' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ['route' => 'admin.properties', 'active' => ['admin.properties', 'admin.properties.show'], 'label' => 'Property Management', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['route' => 'admin.inquiries', 'active' => 'admin.inquiries', 'label' => 'Inquiries', 'icon' => 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['route' => 'admin.reservations', 'active' => 'admin.reservations*', 'label' => 'Reservations', 'badge' => $pendingReservations, 'badgeColor' => 'bg-amber-600', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['route' => 'admin.documents', 'active' => 'admin.documents*', 'label' => 'Documents', 'badge' => $pendingDocuments, 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['route' => 'admin.feedback', 'active' => 'admin.feedback*', 'label' => 'Feedback', 'badge' => $pendingFeedback, 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
                ['route' => 'admin.contact-messages', 'active' => 'admin.contact-messages*', 'label' => 'Contact Messages', 'badge' => $newContactMessages, 'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ['route' => 'admin.notifications', 'active' => 'admin.notifications', 'label' => 'Notifications', 'badge' => $unreadNotifs, 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
            ]],
            ['title' => 'System', 'links' => [
                ['route' => 'admin.audit', 'active' => 'admin.audit', 'label' => 'Audit Logs', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                ['route' => 'admin.backups', 'active' => 'admin.backups*', 'label' => 'Backups', 'icon' => 'M4 7v10c0 2.21 3.58 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.58 4 8 4s8-1.79 8-4M4 7c0-2.21 3.58-4 8-4s8 1.79 8 4m0 5c0 2.21-3.58 4-8 4s-8-1.79-8-4'],
                ['route' => 'admin.settings', 'active' => 'admin.settings', 'label' => 'System Settings', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ]],
        ];
    @endphp

    {{-- Sidebar --}}
    <div x-show="sidebarOpen && !isDesktop" x-transition.opacity @click="sidebarOpen = false" style="display: none" class="fixed inset-0 z-[9998] bg-black/40 lg:hidden" aria-hidden="true"></div>
    <aside class="fixed inset-y-0 left-0 z-[9999] flex w-64 flex-col bg-[var(--color-sidebar)] text-white transition-all duration-300 max-lg:-translate-x-full"
        :class="isDesktop ? (sidebarOpen ? '' : 'w-16!') : (sidebarOpen ? 'translate-x-0!' : '')">

        @include('layouts.partials.sidebar-brand', ['portal' => 'Admin Panel'])
        @include('layouts.partials.sidebar-nav', ['label' => 'Admin navigation', 'sections' => $navSections])

        {{-- Admin Profile --}}
        <div class="shrink-0 border-t border-[var(--color-primary-dark)] p-3 pb-[env(safe-area-inset-bottom)]">
            <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 rounded-lg px-2 py-2 text-teal-100 transition hover:bg-[var(--color-sidebar-hover)]">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-full bg-[var(--color-primary)] text-sm font-bold text-white">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="{{ $adminUser->name }}" class="h-full w-full object-cover">
                    @else
                        {{ $adminInitial }}
                    @endif
                </div>
                <div x-show="sidebarOpen" class="min-w-0">
                    <p class="truncate text-sm font-medium text-white">{{ $adminUser->name ?? 'Admin' }}</p>
                    <p class="truncate text-xs text-stone-400">{{ $adminUser->email ?? '' }}</p>
                </div>
            </a>
            <div x-show="sidebarOpen" class="mt-1">
                @include('pages.auth._sign-out-confirm', ['triggerClass' => 'flex w-full items-center gap-2 rounded-lg px-3 py-1.5 text-xs font-medium text-[var(--color-accent)] transition hover:bg-[var(--color-sidebar-hover)]'])
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <div :class="isDesktop && !sidebarOpen ? 'lg:ml-16!' : ''" class="flex min-h-screen flex-col transition-all duration-300 lg:ml-64">

        {{-- Top Header --}}
        <header class="sticky top-0 z-40 flex items-center gap-4 border-b border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-4 py-5 sm:px-8">
            <button @click="sidebarOpen = !sidebarOpen" aria-label="Toggle navigation menu" class="shrink-0 text-stone-500 dark:text-stone-400 transition hover:text-[var(--color-primary)] dark:hover:text-teal-400">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="min-w-0 flex-1">
                <h1 class="text-xl font-bold text-stone-800 dark:text-stone-100">@yield('page-title', 'Dashboard')</h1>
                <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">@yield('page-subtitle', 'System Administration')</p>
            </div>
            <div class="flex shrink-0 items-center gap-3">
                {{-- Pending Alerts --}}
                <div class="hidden items-center gap-2 lg:flex">
                    @if($pendingReservations > 0)
                        <a href="{{ route('admin.reservations', ['status' => 'pending']) }}" class="flex items-center gap-1.5 rounded-full border border-amber-200 dark:border-amber-900 bg-amber-50 dark:bg-amber-950 px-3 py-1.5 text-xs font-medium text-amber-800 dark:text-amber-300 transition hover:bg-amber-100 dark:hover:bg-amber-900">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500" aria-hidden="true"></span>
                            {{ $pendingReservations }} Pending Reservation{{ $pendingReservations != 1 ? 's' : '' }}
                        </a>
                    @endif
                    @if($pendingDocuments > 0)
                        <a href="{{ route('admin.documents') }}" class="flex items-center gap-1.5 rounded-full border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 px-3 py-1.5 text-xs font-medium text-red-700 dark:text-red-300 transition hover:bg-red-100 dark:hover:bg-red-900">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500" aria-hidden="true"></span>
                            {{ $pendingDocuments }} Doc{{ $pendingDocuments != 1 ? 's' : '' }} to Verify
                        </a>
                    @endif
                </div>
                @include('layouts.partials.theme-toggle')
                {{-- Notifications --}}
                <a href="{{ route('admin.notifications') }}" aria-label="Notifications" class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-stone-200 dark:border-stone-700 text-stone-500 dark:text-stone-400 transition hover:bg-stone-50 dark:hover:bg-stone-800 hover:text-[var(--color-primary)] dark:hover:text-teal-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if($unreadNotifs > 0)
                        <span class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-[11px] font-semibold text-white">{{ $unreadNotifs > 99 ? '99+' : $unreadNotifs }}</span>
                    @endif
                </a>
                {{-- Avatar --}}
                <div class="hidden h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-[var(--color-primary)] text-sm font-bold text-white sm:flex">
                    @if($avatarUrl)
                        <img src="{{ $avatarUrl }}" alt="" class="h-full w-full object-cover">
                    @else
                        {{ $adminInitial }}
                    @endif
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 p-4 sm:p-8">
            @yield('content')
        </main>

        <footer class="border-t border-stone-200 dark:border-stone-700 py-4 text-center text-xs text-stone-500 dark:text-stone-400">
            © {{ date('Y') }} EstateFlow Admin Panel. All rights reserved.
        </footer>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
        }
    </script>
</body>
</html>
