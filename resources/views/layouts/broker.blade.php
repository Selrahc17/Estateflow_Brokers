<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EstateFlow Broker - @yield('title', 'Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 font-sans">
    <aside class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-[#112E3B] text-white">
        <div class="border-b border-teal-900 px-5 py-5">
            <div class="flex items-center gap-2 mb-1">
                <div class="w-8 h-8 bg-teal-700 rounded-lg flex items-center justify-center shrink-0 overflow-hidden">
                    @if($appLogo)
                        <img src="{{ $appLogo }}" class="w-full h-full object-cover">
                    @else
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    @endif
                </div>
                <p class="text-lg font-bold tracking-wide text-teal-300">EstateFlow</p>
            </div>
            <p class="mt-1 text-xs font-semibold uppercase tracking-widest text-teal-500">Broker Portal</p>
        </div>
        <nav class="flex-1 px-3 py-5">
            <a href="{{ route('broker.dashboard') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('broker.dashboard') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <a href="{{ route('broker.performance') }}" class="mt-1 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('broker.performance') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2"/></svg>
                Agent Performance
            </a>
            <a href="{{ route('broker.property-lists') }}" class="mt-1 flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('broker.property-lists') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm4 2h8m-8 4h8m-8 4h5"/></svg>
                Agent Property Lists
            </a>
            <div class="mt-6 space-y-1">
                <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-widest text-teal-500">Management</p>
                <a href="{{ route('broker.agents.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('broker.agents.*') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Agent Management
                </a>
                <a href="{{ route('broker.reports.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('broker.reports.*') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2"/></svg>
                    Reports and Analytics
                </a>
            </div>
            <div class="mt-6 space-y-1">
                <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-widest text-teal-500">Messages</p>
                <a href="{{ route('broker.messages.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('broker.messages.*') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    Message
                </a>
                <a href="{{ route('broker.notifications.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('broker.notifications.*') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Notification
                </a>
            </div>
            <div class="mt-6 space-y-1">
                <p class="px-3 pb-2 text-xs font-semibold uppercase tracking-widest text-teal-500">Oversight</p>
                <a href="{{ route('broker.audit.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('broker.audit.*') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Audit Logs
                </a>
                <a href="{{ route('broker.settings.index') }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('broker.settings.*') ? 'bg-teal-700 text-white' : 'text-teal-100 hover:bg-[#224D5F]' }} transition">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 2.924 1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94-3.31 2.37-2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 2.924-2.37 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94-3.31 2.37-2.37 2.37a1.724 1.724 0 001.065 2.572c1.756-.426 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c.94-.608 2.296-.07 2.572 1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Profile Settings
                </a>
            </div>
        </nav>
        <div class="border-t border-teal-900 p-4">
            <p class="truncate text-sm font-medium text-white">{{ auth()->user()->name }}</p>
            <p class="truncate text-xs text-stone-400">{{ auth()->user()->email }}</p>
            <form action="{{ route('auth.logout') }}" method="POST" class="mt-3">
                @csrf
                    <button type="submit" class="flex w-full items-center gap-2 rounded-lg bg-teal-700 px-3 py-2.5 text-left text-sm font-medium text-white transition hover:bg-teal-800">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Sign Out
                    </button>
            </form>
        </div>
    </aside>

    <main class="ml-64 min-h-screen">
        <header class="border-b border-stone-200 bg-white px-8 py-5">
            <h1 class="text-xl font-bold text-stone-800">@yield('page-title', 'Broker Dashboard')</h1>
            <p class="mt-1 text-sm text-stone-500">@yield('page-subtitle')</p>
        </header>
        <section class="p-8">
            @yield('content')
        </section>
    </main>
    @stack('scripts')
</body>
</html>
