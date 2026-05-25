<!DOCTYPE html>
<html lang="en" x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" :class="darkMode ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EstateFlow — @yield('title', 'Find Your Dream Property')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        .dark body { background-color: #0c0a09; color: #e7e5e4; }
        .dark .dark\:bg-stone-950 { background-color: #0c0a09; }
    </style>
</head>
<body class="bg-white dark:bg-stone-950 font-sans transition-colors duration-300" x-data="{ mobileMenu: false, mobileSearch: false }">

    {{-- Sticky Navbar --}}
    <header class="sticky top-0 z-50 bg-white/95 dark:bg-stone-900/95 backdrop-blur border-b border-stone-200 dark:border-stone-800" x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between gap-4 py-4 transition-all duration-300" :class="scrolled ? 'py-2' : 'py-4'">

            {{-- Logo --}}
            <a href="{{ route('client.properties') }}" class="flex items-center gap-2 shrink-0">
                <div class="w-8 h-8 bg-amber-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <span class="text-lg font-bold text-stone-800 dark:text-white">Estate<span class="text-amber-600">Flow</span></span>
            </a>

            {{-- Search Bar (Desktop) --}}
            <div class="hidden sm:flex flex-1 max-w-2xl mx-4">
                <form action="{{ route('client.properties') }}" method="GET" class="flex w-full border border-stone-200 dark:border-stone-700 rounded-xl shadow-sm overflow-hidden bg-white dark:bg-stone-800">
                    <div class="flex-1 flex items-center border-r border-stone-200 dark:border-stone-700 px-4 py-2 cursor-pointer hover:bg-stone-50 dark:hover:bg-stone-700 transition">
                        <div>
                            <p class="text-xs font-semibold text-stone-700 dark:text-stone-300 leading-none">Location</p>
                            <input name="location" type="text" placeholder="Any location" value="{{ request('location') }}"
                                class="text-sm text-stone-400 dark:text-stone-400 bg-transparent border-0 outline-none w-full mt-0.5 placeholder:text-stone-400">
                        </div>
                    </div>
                    <div class="flex-1 flex items-center border-r border-stone-200 dark:border-stone-700 px-4 py-2 cursor-pointer hover:bg-stone-50 dark:hover:bg-stone-700 transition">
                        <div>
                            <p class="text-xs font-semibold text-stone-700 dark:text-stone-300 leading-none">Keywords</p>
                            <input name="search" type="text" placeholder="Search..." value="{{ request('search') }}"
                                class="text-sm text-stone-400 bg-transparent border-0 outline-none w-full mt-0.5 placeholder:text-stone-400">
                        </div>
                    </div>
                    <div class="flex-1 flex items-center px-4 py-2 cursor-pointer hover:bg-stone-50 dark:hover:bg-stone-700 transition">
                        <div class="w-full">
                            <p class="text-xs font-semibold text-stone-700 dark:text-stone-300 leading-none">Type</p>
                            <select name="type" class="text-sm text-stone-400 bg-transparent border-0 outline-none w-full mt-0.5 cursor-pointer dark:bg-stone-800">
                                <option value="">Any type</option>
                                <option value="House and Lot" {{ request('type') == 'House and Lot' ? 'selected' : '' }}>House and Lot</option>
                                <option value="Lot Only" {{ request('type') == 'Lot Only' ? 'selected' : '' }}>Lot Only</option>
                                <option value="Condominium" {{ request('type') == 'Condominium' ? 'selected' : '' }}>Condominium</option>
                                <option value="Commercial" {{ request('type') == 'Commercial' ? 'selected' : '' }}>Commercial</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-4 flex items-center justify-center transition shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                    </button>
                </form>
            </div>

            {{-- Right Actions --}}
            <div class="flex items-center gap-2 shrink-0">

                {{-- Mobile Search Toggle --}}
                <button @click="mobileSearch = !mobileSearch" class="sm:hidden w-10 h-10 flex items-center justify-center border border-stone-200 dark:border-stone-700 rounded-xl text-stone-500 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                </button>

                {{-- Dark Mode Toggle --}}
                <button @click="darkMode = !darkMode; localStorage.setItem('darkMode', darkMode)"
                    class="w-10 h-10 flex items-center justify-center border border-stone-200 dark:border-stone-700 rounded-xl text-stone-500 dark:text-amber-400 hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </button>

                {{-- Desktop: Guest --}}
                <div class="hidden sm:flex items-center gap-2">
                    <a href="{{ route('auth.login') }}" class="text-sm font-medium text-stone-600 dark:text-stone-300 hover:text-amber-600 transition px-3 py-2">Sign In</a>
                    <a href="{{ route('auth.register') }}" class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition">Register</a>

                    {{-- Logged-in Account Dropdown --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-2 border border-stone-200 dark:border-stone-700 rounded-xl px-3 py-2 hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                            <div class="w-7 h-7 bg-amber-600 rounded-full flex items-center justify-center text-white text-xs font-bold">J</div>
                            <span class="text-sm font-medium text-stone-700 dark:text-stone-200">Juan</span>
                            <svg class="w-4 h-4 text-stone-400 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="open" x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 top-full mt-2 w-56 bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-700 shadow-xl z-50 overflow-hidden">

                            {{-- Profile Header --}}
                            <div class="px-4 py-3 border-b border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-800">
                                <p class="text-sm font-semibold text-stone-800 dark:text-white">Juan dela Cruz</p>
                                <p class="text-xs text-stone-400">juan@email.com</p>
                                <a href="{{ route('client.account.profile') }}" class="text-xs text-amber-600 hover:underline mt-0.5 inline-block">Edit Profile →</a>
                            </div>

                            {{-- Nav Links --}}
                            <div class="py-1 dark:[&_a]:text-stone-300 dark:[&_a:hover]:bg-stone-800 dark:[&_a:hover]:text-amber-400">
                                <a href="{{ route('client.account.reservation') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-600 hover:bg-amber-50 hover:text-amber-700 transition {{ request()->routeIs('client.account.reservation') ? 'bg-amber-50 text-amber-700' : '' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    My Reservation
                                </a>
                                <a href="{{ route('client.account.payments') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-600 hover:bg-amber-50 hover:text-amber-700 transition {{ request()->routeIs('client.account.payments') ? 'bg-amber-50 text-amber-700' : '' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    My Payments
                                </a>
                                <a href="{{ route('client.account.documents') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-600 hover:bg-amber-50 hover:text-amber-700 transition {{ request()->routeIs('client.account.documents') ? 'bg-amber-50 text-amber-700' : '' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    My Documents
                                </a>
                                <a href="{{ route('client.account.notifications') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-600 hover:bg-amber-50 hover:text-amber-700 transition {{ request()->routeIs('client.account.notifications') ? 'bg-amber-50 text-amber-700' : '' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    Notifications
                                    <span class="ml-auto bg-red-500 text-white text-xs w-4 h-4 rounded-full flex items-center justify-center">2</span>
                                </a>
                                <a href="{{ route('client.account.feedback') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-600 hover:bg-amber-50 hover:text-amber-700 transition {{ request()->routeIs('client.account.feedback') ? 'bg-amber-50 text-amber-700' : '' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                                    Feedback & Ratings
                                </a>
                                <a href="{{ route('client.account.profile') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-600 hover:bg-amber-50 hover:text-amber-700 transition {{ request()->routeIs('client.account.profile') ? 'bg-amber-50 text-amber-700' : '' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    My Profile
                                </a>
                            </div>

                            {{-- Sign Out --}}
                            <div class="border-t border-stone-100 dark:border-stone-800 py-1">
                                <a href="{{ route('auth.login') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sign Out
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Mobile Menu Button --}}
                <button @click="mobileMenu = !mobileMenu" class="sm:hidden w-10 h-10 flex items-center justify-center border border-stone-200 dark:border-stone-700 rounded-xl text-stone-500 dark:text-stone-400 hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile Search --}}
        <div x-show="mobileSearch" x-transition class="sm:hidden border-t border-stone-100 dark:border-stone-800 px-4 py-3 bg-white dark:bg-stone-900">
            <form action="{{ route('client.properties') }}" method="GET" class="flex gap-2">
                <input name="search" type="text" placeholder="Search properties..." value="{{ request('search') }}"
                    class="flex-1 border border-stone-200 dark:border-stone-700 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white dark:bg-stone-800 dark:text-stone-200">
                <button type="submit" class="bg-amber-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium">Search</button>
            </form>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileMenu" x-transition class="sm:hidden border-t border-stone-100 dark:border-stone-800 px-4 py-4 bg-white dark:bg-stone-900 space-y-1">
            <p class="text-xs text-stone-400 uppercase tracking-widest font-semibold px-1 pb-1">Browse</p>
            <a href="{{ route('client.properties') }}" class="block text-sm text-stone-600 dark:text-stone-300 hover:text-amber-600 py-2 px-1 font-medium">All Properties</a>
            <a href="{{ route('client.properties') }}?type=House+and+Lot" class="block text-sm text-stone-500 dark:text-stone-400 hover:text-amber-600 py-2 px-1">House and Lot</a>
            <a href="{{ route('client.properties') }}?type=Lot+Only" class="block text-sm text-stone-500 dark:text-stone-400 hover:text-amber-600 py-2 px-1">Lot Only</a>
            <a href="{{ route('client.properties') }}?type=Condominium" class="block text-sm text-stone-500 dark:text-stone-400 hover:text-amber-600 py-2 px-1">Condominium</a>
            <div class="border-t border-stone-100 dark:border-stone-800 pt-3 mt-2">
                <p class="text-xs text-stone-400 uppercase tracking-widest font-semibold px-1 pb-2">My Account</p>
                <a href="{{ route('client.account.reservation') }}" class="block text-sm text-stone-600 dark:text-stone-300 hover:text-amber-600 py-2 px-1">My Reservation</a>
                <a href="{{ route('client.account.payments') }}" class="block text-sm text-stone-600 dark:text-stone-300 hover:text-amber-600 py-2 px-1">My Payments</a>
                <a href="{{ route('client.account.documents') }}" class="block text-sm text-stone-600 dark:text-stone-300 hover:text-amber-600 py-2 px-1">My Documents</a>
                <a href="{{ route('client.account.notifications') }}" class="block text-sm text-stone-600 dark:text-stone-300 hover:text-amber-600 py-2 px-1">Notifications</a>
                <a href="{{ route('client.account.feedback') }}" class="block text-sm text-stone-600 dark:text-stone-300 hover:text-amber-600 py-2 px-1">Feedback & Ratings</a>
                <a href="{{ route('client.account.profile') }}" class="block text-sm text-stone-600 dark:text-stone-300 hover:text-amber-600 py-2 px-1">My Profile</a>
            </div>
            <div class="border-t border-stone-100 dark:border-stone-800 pt-3 mt-2 flex gap-2">
                <a href="{{ route('auth.login') }}" class="flex-1 text-center border border-stone-200 dark:border-stone-700 text-stone-600 dark:text-stone-300 text-sm py-2 rounded-xl">Sign In</a>
                <a href="{{ route('auth.register') }}" class="flex-1 text-center bg-amber-600 text-white text-sm py-2 rounded-xl">Register</a>
            </div>
        </div>

        {{-- Desktop Nav Pills --}}
        <div class="hidden sm:flex max-w-7xl mx-auto px-6 gap-1 pb-2">
            <a href="{{ route('client.properties') }}" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ !request('type') && !request('status') ? 'bg-amber-100 text-amber-700' : 'text-stone-500 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800' }} transition">All Properties</a>
            <a href="{{ route('client.properties') }}?status=Pre-Selling" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ request('status') == 'Pre-Selling' ? 'bg-amber-100 text-amber-700' : 'text-stone-500 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800' }} transition">Pre-Selling</a>
            <a href="{{ route('client.properties') }}?status=RFO" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ request('status') == 'RFO' ? 'bg-amber-100 text-amber-700' : 'text-stone-500 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800' }} transition">Ready for Occupancy</a>
            <a href="{{ route('client.properties') }}?type=House+and+Lot" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ request('type') == 'House and Lot' ? 'bg-amber-100 text-amber-700' : 'text-stone-500 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800' }} transition">House & Lot</a>
            <a href="{{ route('client.properties') }}?type=Lot+Only" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ request('type') == 'Lot Only' ? 'bg-amber-100 text-amber-700' : 'text-stone-500 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800' }} transition">Lot Only</a>
            <a href="{{ route('client.properties') }}?type=Condominium" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ request('type') == 'Condominium' ? 'bg-amber-100 text-amber-700' : 'text-stone-500 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800' }} transition">Condominium</a>
            <a href="{{ route('client.properties') }}?type=Commercial" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ request('type') == 'Commercial' ? 'bg-amber-100 text-amber-700' : 'text-stone-500 dark:text-stone-400 hover:bg-stone-100 dark:hover:bg-stone-800' }} transition">Commercial</a>
        </div>
    </header>

    {{-- Page Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-stone-900 text-stone-400 mt-20">
        <div class="max-w-7xl mx-auto px-6 py-12 grid grid-cols-1 sm:grid-cols-4 gap-8">
            <div class="sm:col-span-1">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 bg-amber-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                    <span class="text-white font-bold">EstateFlow</span>
                </div>
                <p class="text-xs leading-relaxed">Your trusted real estate partner for finding the perfect property in the Philippines.</p>
            </div>
            <div>
                <p class="text-white text-sm font-semibold mb-3">Properties</p>
                <div class="space-y-2 text-xs">
                    <a href="{{ route('client.properties') }}?type=House+and+Lot" class="block hover:text-white transition">House and Lot</a>
                    <a href="{{ route('client.properties') }}?type=Lot+Only" class="block hover:text-white transition">Lot Only</a>
                    <a href="{{ route('client.properties') }}?type=Condominium" class="block hover:text-white transition">Condominium</a>
                    <a href="{{ route('client.properties') }}?type=Commercial" class="block hover:text-white transition">Commercial</a>
                </div>
            </div>
            <div>
                <p class="text-white text-sm font-semibold mb-3">Account</p>
                <div class="space-y-2 text-xs">
                    <a href="{{ route('auth.login') }}" class="block hover:text-white transition">Sign In</a>
                    <a href="{{ route('auth.register') }}" class="block hover:text-white transition">Register</a>
                    <a href="{{ route('client.account.home') }}" class="block hover:text-white transition">My Reservation</a>
                    <a href="{{ route('client.account.payments') }}" class="block hover:text-white transition">My Payments</a>
                </div>
            </div>
            <div>
                <p class="text-white text-sm font-semibold mb-3">Support</p>
                <div class="space-y-2 text-xs">
                    <a href="{{ route('client.account.feedback') }}" class="block hover:text-white transition">Feedback & Ratings</a>
                    <a href="{{ route('client.about') }}" class="block hover:text-white transition">About Us</a>
                    <a href="{{ route('client.contact') }}" class="block hover:text-white transition">Contact Us</a>
                    <a href="{{ route('client.legal.privacy') }}" class="block hover:text-white transition">Privacy Policy</a>
                    <a href="{{ route('client.legal.terms') }}" class="block hover:text-white transition">Terms of Use</a>
                </div>
            </div>
        </div>
        <div class="border-t border-stone-800 py-4 text-center text-xs">
            © {{ date('Y') }} EstateFlow. All rights reserved.
        </div>
    </footer>

    {{-- Floating AI Chatbot Widget --}}
    <div class="fixed bottom-6 right-6 z-50" x-data="{ open: false, message: '', messages: [{from:'ai',text:'Hi there! 👋 I\'m EstateFlow AI. Ask me anything about properties, reservations, or payments!'}] }">

        {{-- Chat Window --}}
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-90 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-90 translate-y-4"
             class="absolute bottom-16 right-0 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-stone-200 overflow-hidden mb-2">

            {{-- Chat Header --}}
            <div class="bg-gradient-to-r from-stone-800 to-amber-800 px-4 py-3 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-amber-500 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                    </div>
                    <div>
                        <p class="text-white font-semibold text-sm">EstateFlow AI</p>
                        <p class="text-amber-300 text-xs flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-green-400 rounded-full inline-block animate-pulse"></span>
                            Online · Always available
                        </p>
                    </div>
                </div>
                <button @click="open = false" class="text-white/70 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Messages --}}
            <div class="h-72 overflow-y-auto p-4 space-y-3 bg-stone-50" id="chatbot-messages">
                <template x-for="(msg, i) in messages" :key="i">
                    <div :class="msg.from === 'user' ? 'flex flex-row-reverse items-end gap-2' : 'flex items-end gap-2'">
                        <template x-if="msg.from === 'ai'">
                            <div class="w-6 h-6 bg-amber-600 rounded-full flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                            </div>
                        </template>
                        <div :class="msg.from === 'user' ? 'bg-amber-600 text-white rounded-2xl rounded-br-none' : 'bg-white border border-stone-200 text-stone-700 rounded-2xl rounded-bl-none'"
                             class="px-3 py-2 max-w-xs shadow-sm">
                            <p class="text-xs leading-relaxed" x-text="msg.text"></p>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Quick Replies --}}
            <div class="px-4 py-2 border-t border-stone-100 bg-white">
                <div class="flex gap-1.5 flex-wrap">
                    <button @click="messages.push({from:'user',text:'Payment due?'});setTimeout(()=>{messages.push({from:'ai',text:'Let me help you with that! For detailed information, please visit the relevant section in your account.'})},800)" class="text-xs bg-stone-100 hover:bg-amber-50 hover:text-amber-700 text-stone-500 px-2.5 py-1 rounded-full transition border border-transparent hover:border-amber-200">Payment due?</button>
                    <button @click="messages.push({from:'user',text:'My documents'});setTimeout(()=>{messages.push({from:'ai',text:'Let me help you with that! For detailed information, please visit the relevant section in your account.'})},800)" class="text-xs bg-stone-100 hover:bg-amber-50 hover:text-amber-700 text-stone-500 px-2.5 py-1 rounded-full transition border border-transparent hover:border-amber-200">My documents</button>
                    <button @click="messages.push({from:'user',text:'Lot details'});setTimeout(()=>{messages.push({from:'ai',text:'Let me help you with that! For detailed information, please visit the relevant section in your account.'})},800)" class="text-xs bg-stone-100 hover:bg-amber-50 hover:text-amber-700 text-stone-500 px-2.5 py-1 rounded-full transition border border-transparent hover:border-amber-200">Lot details</button>
                    <button @click="messages.push({from:'user',text:'Contact broker'});setTimeout(()=>{messages.push({from:'ai',text:'Let me help you with that! For detailed information, please visit the relevant section in your account.'})},800)" class="text-xs bg-stone-100 hover:bg-amber-50 hover:text-amber-700 text-stone-500 px-2.5 py-1 rounded-full transition border border-transparent hover:border-amber-200">Contact broker</button>
                </div>
            </div>

            {{-- Input --}}
            <div class="p-3 border-t border-stone-100 bg-white">
                <div class="flex gap-2">
                    <input
                        x-model="message"
                        @keydown.enter="if(message.trim()){messages.push({from:'user',text:message});message='';setTimeout(()=>{messages.push({from:'ai',text:'Thanks for your message! For detailed assistance, our team will get back to you shortly. You can also visit the AI Assistant page for more help.'})},800)}"
                        type="text"
                        placeholder="Ask anything..."
                        class="flex-1 border border-stone-200 rounded-xl px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-amber-400 bg-stone-50">
                    <button
                        @click="if(message.trim()){messages.push({from:'user',text:message});message='';setTimeout(()=>{messages.push({from:'ai',text:'Thanks for your message! For detailed assistance, our team will get back to you shortly. You can also visit the AI Assistant page for more help.'})},800)}"
                        class="w-8 h-8 bg-amber-600 hover:bg-amber-700 text-white rounded-xl flex items-center justify-center transition shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </div>
                <p class="text-center text-xs text-stone-400 mt-2">
                    <a href="{{ route('client.account.chat') }}" class="hover:text-amber-600 transition">Open full AI Assistant →</a>
                </p>
            </div>
        </div>

        {{-- Toggle Button --}}
        <button @click="open = !open"
            class="w-14 h-14 bg-amber-600 hover:bg-amber-700 text-white rounded-full shadow-lg flex items-center justify-center transition-all duration-200 hover:scale-110 active:scale-95 relative">
            <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            <svg x-show="open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            {{-- Notification dot --}}
            <span x-show="!open" class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full flex items-center justify-center text-white text-xs font-bold animate-bounce">1</span>
        </button>

    </div>

    @stack('scripts')
</body>
</html>
