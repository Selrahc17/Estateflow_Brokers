<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EstateFlow - @yield('title', 'Dashboard')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 font-sans" x-data="{ sidebarOpen: true }">

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'w-64' : 'w-16'"
           class="fixed top-0 left-0 h-screen bg-stone-900 text-white transition-all duration-300 z-50 flex flex-col">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-4 py-5 border-b border-stone-700">
            <div class="w-9 h-9 bg-amber-600 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
            </div>
            <span x-show="sidebarOpen" class="text-lg font-bold text-amber-400 tracking-wide">EstateFlow</span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1">

            <p x-show="sidebarOpen" class="text-xs text-stone-500 uppercase tracking-widest px-3 pb-2">Main</p>

            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-amber-600 text-white' : 'text-stone-300 hover:bg-stone-800' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">Dashboard</span>
            </a>

            <a href="{{ route('properties.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('properties.*') ? 'bg-amber-600 text-white' : 'text-stone-300 hover:bg-stone-800' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">Properties</span>
            </a>

            <a href="{{ route('lots.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('lots.*') ? 'bg-amber-600 text-white' : 'text-stone-300 hover:bg-stone-800' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">Lot Availability</span>
            </a>

            <a href="{{ route('reservations.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('reservations.*') ? 'bg-amber-600 text-white' : 'text-stone-300 hover:bg-stone-800' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">Reservations</span>
            </a>

            <a href="{{ route('clients.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('clients.*') ? 'bg-amber-600 text-white' : 'text-stone-300 hover:bg-stone-800' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">Clients</span>
            </a>

            <p x-show="sidebarOpen" class="text-xs text-stone-500 uppercase tracking-widest px-3 pb-2 pt-4">Finance</p>

            <a href="{{ route('payments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('payments.*') ? 'bg-amber-600 text-white' : 'text-stone-300 hover:bg-stone-800' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">Payments</span>
            </a>

            <a href="{{ route('documents.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('documents.*') ? 'bg-amber-600 text-white' : 'text-stone-300 hover:bg-stone-800' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">Documents</span>
            </a>

            <p x-show="sidebarOpen" class="text-xs text-stone-500 uppercase tracking-widest px-3 pb-2 pt-4">Tools</p>

            <a href="{{ route('notifications.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('notifications.*') ? 'bg-amber-600 text-white' : 'text-stone-300 hover:bg-stone-800' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">Notifications</span>
            </a>

            <a href="{{ route('chat.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('chat.*') ? 'bg-amber-600 text-white' : 'text-stone-300 hover:bg-stone-800' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">AI Assistant</span>
            </a>

            <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg {{ request()->routeIs('reports.*') ? 'bg-amber-600 text-white' : 'text-stone-300 hover:bg-stone-800' }} transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span x-show="sidebarOpen" class="text-sm font-medium">Reports</span>
            </a>

        </nav>

        {{-- Profile --}}
        <div class="border-t border-stone-700 p-3">
            <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-2 py-2 rounded-lg text-stone-300 hover:bg-stone-800 transition">
                <div class="w-8 h-8 bg-amber-600 rounded-full flex items-center justify-center shrink-0 text-sm font-bold text-white">B</div>
                <div x-show="sidebarOpen">
                    <p class="text-sm font-medium text-white">Broker Name</p>
                    <p class="text-xs text-stone-400">broker@estateflow.com</p>
                </div>
            </a>
        </div>
    </aside>

    {{-- Main Content --}}
    <div :class="sidebarOpen ? 'ml-64' : 'ml-16'" class="transition-all duration-300 min-h-screen flex flex-col">

        {{-- Top Header --}}
        <header class="bg-white border-b border-stone-200 px-6 py-4 flex items-center justify-between sticky top-0 z-40">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = !sidebarOpen" class="text-stone-500 hover:text-amber-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div>
                    <h1 class="text-lg font-semibold text-stone-800">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-xs text-stone-400">@yield('page-subtitle', 'Welcome back!')</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button class="relative text-stone-500 hover:text-amber-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                </button>
                <div class="w-9 h-9 bg-amber-600 rounded-full flex items-center justify-center text-white font-bold text-sm">B</div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>

        <footer class="text-center text-xs text-stone-400 py-4 border-t border-stone-200">
            © {{ date('Y') }} EstateFlow. All rights reserved.
        </footer>
    </div>

</body>
</html>
