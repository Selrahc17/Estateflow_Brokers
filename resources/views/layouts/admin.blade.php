<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
</head>
<body class="bg-stone-100 font-sans" x-data="{ sidebarOpen: true }" @resize.window="sidebarOpen = window.innerWidth >= 1024">

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'w-64' : 'w-16'"
           class="fixed top-0 left-0 h-screen bg-[#112E3B] text-white transition-all duration-300 z-50 flex flex-col">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-4 py-5 border-b border-teal-900">
            <div class="w-9 h-9 bg-teal-700 rounded-lg flex items-center justify-center shrink-0 overflow-hidden">
                @if($appLogo)
                    <img src="{{ $appLogo }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                @endif
            </div>
            <div x-show="sidebarOpen">
                <p class="text-sm font-bold text-white leading-none">EstateFlow</p>
                <p class="text-xs text-teal-400 font-semibold">Admin Panel</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1">

            <p x-show="sidebarOpen" class="text-xs text-teal-600 uppercase tracking-widest px-3 pb-2">Overview</p>

            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">Dashboard</span>
            </a>

            <a href="{{ route('admin.reports') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.reports') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">Reports & Analytics</span>
            </a>

            <p x-show="sidebarOpen" class="text-xs text-teal-600 uppercase tracking-widest px-3 pb-2 pt-4">Management</p>

            <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.users') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">User Management</span>
            </a>

            <a href="{{ route('admin.brokers') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.brokers') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">Broker Management</span>
            </a>

            <a href="{{ route('admin.properties') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.properties') || request()->routeIs('admin.properties.show') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">Property Management</span>
            </a>

            <a href="{{ route('admin.reservations') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.reservations') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">Reservations</span>
                @if($pendingReservations > 0)
                    <span x-show="sidebarOpen" class="ml-auto bg-amber-500 text-white text-xs px-1.5 py-0.5 rounded-full">{{ $pendingReservations }}</span>
                @endif
            </a>

            <a href="{{ route('admin.documents') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.documents') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">Documents</span>
                @if($pendingDocuments > 0)
                    <span x-show="sidebarOpen" class="ml-auto bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">{{ $pendingDocuments }}</span>
                @endif
            </a>


            <a href="{{ route('admin.notifications') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.notifications') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">Notifications</span>
                @if($unreadNotifs > 0)
                    <span x-show="sidebarOpen" class="ml-auto bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full">{{ $unreadNotifs }}</span>
                @endif
            </a>

            <p x-show="sidebarOpen" class="text-xs text-teal-600 uppercase tracking-widest px-3 pb-2 pt-4">System</p>

            <a href="{{ route('admin.audit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.audit') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">Audit Logs</span>
            </a>

            <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('admin.settings') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">System Settings</span>
            </a>

        </nav>

        {{-- Admin Profile --}}
        <div class="border-t border-teal-900 p-3">
            <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 px-2 py-2 rounded-lg text-teal-100 hover:bg-[#224D5F] transition">
                <div class="w-8 h-8 rounded-full overflow-hidden shrink-0 bg-teal-700 flex items-center justify-center text-sm font-bold text-white">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    @endif
                </div>
                <div x-show="sidebarOpen">
                    <p class="text-sm font-medium text-white">{{ auth()->user()->name ?? 'Admin' }}</p>
                    <p class="text-xs text-stone-400">{{ auth()->user()->email ?? '' }}</p>
                </div>
            </a>
            <form action="{{ route('auth.logout') }}" method="POST" x-show="sidebarOpen" class="mt-1">
                @csrf
                <button type="submit" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-teal-300 hover:bg-[#224D5F] text-xs transition w-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content --}}
    <div :class="sidebarOpen ? 'ml-64' : 'ml-16'" class="transition-all duration-300 min-h-screen flex flex-col">

        {{-- Top Header --}}
        <header class="bg-white border-b border-stone-200 px-6 py-4 flex items-center justify-between sticky top-0 z-40">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="text-stone-500 hover:text-teal-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h1 class="text-lg font-semibold text-stone-800">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-xs text-stone-400">@yield('page-subtitle', 'System Administration')</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                {{-- Pending Alerts --}}
                <div class="hidden sm:flex items-center gap-2">
                    @if($pendingReservations > 0)
                        <span class="flex items-center gap-1.5 text-xs bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1.5 rounded-full font-medium">
                            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full animate-pulse"></span>
                            {{ $pendingReservations }} Pending Reservation{{ $pendingReservations != 1 ? 's' : '' }}
                        </span>
                    @endif
                    @if($pendingDocuments > 0)
                        <span class="flex items-center gap-1.5 text-xs bg-red-50 text-red-600 border border-red-200 px-3 py-1.5 rounded-full font-medium">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span>
                            {{ $pendingDocuments }} Doc{{ $pendingDocuments != 1 ? 's' : '' }} to Verify
                        </span>
                    @endif
                </div>
                {{-- Notifications --}}
                <a href="{{ route('admin.notifications') }}" class="relative text-stone-500 hover:text-teal-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if($unreadNotifs > 0)
                        <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">{{ $unreadNotifs }}</span>
                    @endif
                </a>
                <div class="w-9 h-9 rounded-full overflow-hidden bg-teal-700 flex items-center justify-center text-white font-bold text-sm">
                    @if(auth()->user()->avatar)
                        <img src="{{ auth()->user()->avatar }}" class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    @endif
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>

        <footer class="text-center text-xs text-stone-400 py-4 border-t border-stone-200">
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
