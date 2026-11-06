<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1A6B79">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="EstateFlow">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <title>EstateFlow - @yield('title', 'Dashboard')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-stone-100 font-sans" x-data="{
        isDesktop: window.innerWidth >= 1024,
        sidebarOpen: window.innerWidth >= 1024 && localStorage.getItem('agentSidebar') !== 'collapsed',
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
            if (this.isDesktop) localStorage.setItem('agentSidebar', this.sidebarOpen ? 'open' : 'collapsed');
        },
    }" @resize.window="const wasDesktop = isDesktop; isDesktop = window.innerWidth >= 1024; if (wasDesktop !== isDesktop) sidebarOpen = isDesktop && localStorage.getItem('agentSidebar') !== 'collapsed'">

    @php
        $unreadCount = \App\Models\AppNotification::where('user_id', auth()->id())->where('is_read', false)->count();
        $navSections = [
            'Overview' => [
                ['route' => 'agent.dashboard', 'active' => 'agent.dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'agent.reports.index', 'active' => 'agent.reports.*', 'label' => 'Reports', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ],
            'Listings' => [
                ['route' => 'agent.properties.index', 'active' => 'agent.properties.*', 'label' => 'Properties', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['route' => 'agent.lots.index', 'active' => 'agent.lots.*', 'label' => 'Lot Availability', 'icon' => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7'],
            ],
            'Clients and Sales' => [
                ['route' => 'agent.inquiries.index', 'active' => 'agent.inquiries.*', 'label' => 'Inquiries', 'icon' => 'M21 15a4 4 0 01-4 4H7l-4 4V7a4 4 0 014-4h10a4 4 0 014 4v8z'],
                ['route' => 'agent.clients.index', 'active' => 'agent.clients.*', 'label' => 'Clients', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                ['route' => 'agent.site-visits.index', 'active' => 'agent.site-visits.*', 'label' => 'Site Visits', 'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0L6.343 16.657C5.481 15.795 5 14.563 5 13.234V7a2 2 0 012-2h10a2 2 0 012 2v6.234c0 1.329-.481 2.561-1.343 3.423z'],
                ['route' => 'agent.reservations.index', 'active' => 'agent.reservations.*', 'label' => 'Reservations', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['route' => 'agent.documents.index', 'active' => 'agent.documents.*', 'label' => 'Documents', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['route' => 'agent.commission', 'active' => 'agent.commission*', 'label' => 'My Commission', 'icon' => 'M12 8c-2.21 0-4 1.12-4 2.5s1.79 2.5 4 2.5 4 1.12 4 2.5-1.79 2.5-4 2.5m0-10V5m0 13v1m8-7a8 8 0 11-16 0 8 8 0 0116 0z'],
            ],
            'Communication' => [
                ['route' => 'agent.messages.index', 'active' => 'agent.messages.*', 'label' => 'Messages', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
                ['route' => 'agent.notifications.index', 'active' => 'agent.notifications.*', 'label' => 'Notifications', 'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                ['route' => 'agent.chat.index', 'active' => 'agent.chat.*', 'label' => 'AI Assistant', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
            ],
        ];
    @endphp

    {{-- Sidebar --}}
        <div x-show="sidebarOpen && !isDesktop" x-transition.opacity @click="sidebarOpen = false" class="fixed inset-0 bg-black/40 z-[9998] lg:hidden" aria-hidden="true"></div>
            <aside class="fixed top-0 left-0 h-screen bg-[var(--color-sidebar)] text-white transition-all duration-300 z-[9999] flex flex-col"
            :class="!isDesktop ? (sidebarOpen ? 'translate-x-0 w-64' : '-translate-x-full w-64') : (sidebarOpen ? 'w-64' : 'w-16')">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-4 py-5 border-b border-[var(--color-primary-dark)]">
            <div class="w-9 h-9 bg-[var(--color-primary)] rounded-lg flex items-center justify-center shrink-0 overflow-hidden">
                @if($appLogo)
                    <img src="{{ $appLogo }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                @endif
            </div>
            <div x-show="sidebarOpen">
                <p class="text-lg font-bold leading-tight tracking-wide text-[var(--color-accent)]">EstateFlow</p>
                <p class="text-xs font-semibold uppercase tracking-widest text-[var(--color-primary-light)]">Agent Portal</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav aria-label="Agent navigation" class="min-h-0 flex-1 overflow-y-auto px-2 py-4 [color-scheme:dark]">

            @foreach($navSections as $section => $links)
            <div class="{{ $loop->first ? '' : 'mt-4' }} space-y-1">
                <p x-show="sidebarOpen" class="px-3 pb-1 text-xs font-semibold uppercase tracking-widest text-[var(--color-primary-light)]">{{ $section }}</p>
                @unless($loop->first)
                <div x-show="!sidebarOpen" class="mx-2 mb-2 border-t border-[var(--color-primary-dark)]" aria-hidden="true"></div>
                @endunless
                @foreach($links as $link)
                @php($isActive = request()->routeIs($link['active']))
                <a href="{{ route($link['route']) }}" aria-label="{{ $link['label'] }}" @if($isActive) aria-current="page" @endif
                    :title="sidebarOpen ? '' : @js($link['label'])"
                    :class="sidebarOpen ? '' : 'justify-center'"
                    class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 transition {{ $isActive ? 'bg-[var(--color-primary)] text-white' : 'text-teal-100 hover:bg-[var(--color-sidebar-hover)] hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/></svg>
                    <span x-show="sidebarOpen" class="flex-1 text-sm font-medium">{{ $link['label'] }}</span>
                    @if($link['route'] === 'agent.notifications.index' && $unreadCount > 0)
                    <span x-show="sidebarOpen" class="rounded-full bg-red-600 px-1.5 text-xs font-semibold leading-5 text-white">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
                    <span x-show="!sidebarOpen" class="absolute right-2 top-2 h-2 w-2 rounded-full bg-red-500" aria-hidden="true"></span>
                    @endif
                </a>
                @endforeach
            </div>
            @endforeach

        </nav>

        {{-- Profile --}}
        <div class="shrink-0 border-t border-[var(--color-primary-dark)] p-3 pb-[env(safe-area-inset-bottom)]">
            <a href="{{ route('agent.settings.index') }}" class="flex items-center gap-3 px-2 py-2 rounded-lg text-teal-100 hover:bg-[var(--color-sidebar-hover)] transition">
                <div class="w-8 h-8 rounded-full overflow-hidden shrink-0 bg-[var(--color-primary)] flex items-center justify-center text-sm font-bold text-white">
                        @if(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        @endif
                    </div>
                <div x-show="sidebarOpen">
                    <p class="text-sm font-medium text-white">{{ auth()->user()->name ?? 'Agent' }}</p>
                    <p class="text-xs text-stone-400">{{ auth()->user()->email ?? '' }}</p>
                </div>
            </a>
            <div x-show="sidebarOpen" class="mt-1">
                @include('pages.auth._sign-out-confirm', ['triggerClass' => 'w-full flex items-center gap-2 px-3 py-1.5 rounded-lg text-[var(--color-accent)] hover:bg-[var(--color-sidebar-hover)] text-xs transition'])
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <div :class="!isDesktop ? 'ml-0' : (sidebarOpen ? 'ml-64' : 'ml-16')" class="transition-all duration-300 min-h-screen flex flex-col">

        {{-- Top Header --}}
        <header class="bg-white border-b border-stone-200 px-6 py-4 flex items-center justify-between sticky top-0 z-40">
            <div class="flex items-center gap-4">
                <button @click="toggleSidebar()" aria-label="Toggle navigation menu" class="text-stone-500 hover:text-[var(--color-primary)] transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h1 class="text-lg font-semibold text-stone-800">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-xs text-stone-500">@yield('page-subtitle', 'Welcome back!')</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                @if($unreadCount > 0)
                    <a href="{{ route('agent.notifications.index') }}" class="relative text-stone-500 hover:text-[var(--color-primary)] transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">{{ $unreadCount }}</span>
                    </a>
                @else
                    <a href="{{ route('agent.notifications.index') }}" class="text-stone-500 hover:text-[var(--color-primary)] transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </a>
                @endif
                <div class="w-9 h-9 rounded-full overflow-hidden bg-[var(--color-primary)] flex items-center justify-center text-white font-bold text-sm">
                        @if(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        @endif
                    </div>
            </div>
        </header>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mx-6 mt-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Page Content --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>

        <footer class="text-center text-xs text-stone-600 py-4 border-t border-stone-200">
            © {{ date('Y') }} EstateFlow. All rights reserved.
        </footer>
    </div>

    @stack('scripts')
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
        }
    </script>
</body>
</html>
