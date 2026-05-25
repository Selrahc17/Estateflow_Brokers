<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EstateFlow — @yield('title', 'Find Your Dream Property')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-white font-sans" x-data="{ mobileMenu: false, mobileSearch: false }">

    {{-- Sticky Navbar --}}
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-stone-200" x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between gap-4 py-4 transition-all duration-300" :class="scrolled ? 'py-2' : 'py-4'">

            {{-- Logo --}}
            <a href="{{ route('client.properties') }}" class="flex items-center gap-2 shrink-0">
                <div class="w-8 h-8 bg-amber-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <span class="text-lg font-bold text-stone-800">Estate<span class="text-amber-600">Flow</span></span>
            </a>

            {{-- Search Bar (Desktop) --}}
            <div class="hidden sm:flex flex-1 max-w-2xl mx-4">
                <form action="{{ route('client.properties') }}" method="GET" class="flex w-full border border-stone-200 rounded-xl shadow-sm overflow-hidden bg-white">
                    <div class="flex-1 flex items-center border-r border-stone-200 px-4 py-2 cursor-pointer hover:bg-stone-50 transition">
                        <div>
                            <p class="text-xs font-semibold text-stone-700 leading-none">Location</p>
                            <input name="location" type="text" placeholder="Any location" value="{{ request('location') }}"
                                class="text-sm text-stone-400 bg-transparent border-0 outline-none w-full mt-0.5 placeholder:text-stone-400">
                        </div>
                    </div>
                    <div class="flex-1 flex items-center border-r border-stone-200 px-4 py-2 cursor-pointer hover:bg-stone-50 transition">
                        <div>
                            <p class="text-xs font-semibold text-stone-700 leading-none">Keywords</p>
                            <input name="search" type="text" placeholder="Search..." value="{{ request('search') }}"
                                class="text-sm text-stone-400 bg-transparent border-0 outline-none w-full mt-0.5 placeholder:text-stone-400">
                        </div>
                    </div>
                    <div class="flex-1 flex items-center px-4 py-2 cursor-pointer hover:bg-stone-50 transition">
                        <div class="w-full">
                            <p class="text-xs font-semibold text-stone-700 leading-none">Type</p>
                            <select name="type" class="text-sm text-stone-400 bg-transparent border-0 outline-none w-full mt-0.5 cursor-pointer">
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
                <button @click="mobileSearch = !mobileSearch" class="sm:hidden w-10 h-10 flex items-center justify-center border border-stone-200 rounded-xl text-stone-500 hover:bg-stone-50 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                </button>

                {{-- Desktop: Guest --}}
                <div class="hidden sm:flex items-center gap-2">
                    <a href="{{ route('auth.login') }}" class="text-sm font-medium text-stone-600 hover:text-amber-600 transition px-3 py-2">Sign In</a>
                    <a href="{{ route('auth.register') }}" class="bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition">Register</a>

                    {{-- Logged-in Account Dropdown --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" @click.outside="open = false"
                            class="flex items-center gap-2 border border-stone-200 rounded-xl px-3 py-2 hover:bg-stone-50 transition">
                            <div class="w-7 h-7 bg-amber-600 rounded-full flex items-center justify-center text-white text-xs font-bold">J</div>
                            <span class="text-sm font-medium text-stone-700">Juan</span>
                            <svg class="w-4 h-4 text-stone-400 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="open" x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 top-full mt-2 w-56 bg-white rounded-2xl border border-stone-200 shadow-xl z-50 overflow-hidden">

                            {{-- Profile Header --}}
                            <div class="px-4 py-3 border-b border-stone-100 bg-stone-50">
                                <p class="text-sm font-semibold text-stone-800">Juan dela Cruz</p>
                                <p class="text-xs text-stone-400">juan@email.com</p>
                            </div>

                            {{-- Nav Links --}}
                            <div class="py-1">
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
                                <a href="{{ route('client.account.chat') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-stone-600 hover:bg-amber-50 hover:text-amber-700 transition {{ request()->routeIs('client.account.chat') ? 'bg-amber-50 text-amber-700' : '' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                                    AI Assistant
                                </a>
                            </div>

                            {{-- Sign Out --}}
                            <div class="border-t border-stone-100 py-1">
                                <a href="{{ route('auth.login') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Sign Out
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Mobile Menu Button --}}
                <button @click="mobileMenu = !mobileMenu" class="sm:hidden w-10 h-10 flex items-center justify-center border border-stone-200 rounded-xl text-stone-500 hover:bg-stone-50 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>

        {{-- Mobile Search --}}
        <div x-show="mobileSearch" x-transition class="sm:hidden border-t border-stone-100 px-4 py-3 bg-white">
            <form action="{{ route('client.properties') }}" method="GET" class="flex gap-2">
                <input name="search" type="text" placeholder="Search properties..." value="{{ request('search') }}"
                    class="flex-1 border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                <button type="submit" class="bg-amber-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium">Search</button>
            </form>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileMenu" x-transition class="sm:hidden border-t border-stone-100 px-4 py-4 bg-white space-y-1">
            <p class="text-xs text-stone-400 uppercase tracking-widest font-semibold px-1 pb-1">Browse</p>
            <a href="{{ route('client.properties') }}" class="block text-sm text-stone-600 hover:text-amber-600 py-2 px-1 font-medium">All Properties</a>
            <a href="{{ route('client.properties') }}?type=House+and+Lot" class="block text-sm text-stone-500 hover:text-amber-600 py-2 px-1">House and Lot</a>
            <a href="{{ route('client.properties') }}?type=Lot+Only" class="block text-sm text-stone-500 hover:text-amber-600 py-2 px-1">Lot Only</a>
            <a href="{{ route('client.properties') }}?type=Condominium" class="block text-sm text-stone-500 hover:text-amber-600 py-2 px-1">Condominium</a>
            <div class="border-t border-stone-100 pt-3 mt-2">
                <p class="text-xs text-stone-400 uppercase tracking-widest font-semibold px-1 pb-2">My Account</p>
                <a href="{{ route('client.account.reservation') }}" class="block text-sm text-stone-600 hover:text-amber-600 py-2 px-1">My Reservation</a>
                <a href="{{ route('client.account.payments') }}" class="block text-sm text-stone-600 hover:text-amber-600 py-2 px-1">My Payments</a>
                <a href="{{ route('client.account.documents') }}" class="block text-sm text-stone-600 hover:text-amber-600 py-2 px-1">My Documents</a>
                <a href="{{ route('client.account.notifications') }}" class="block text-sm text-stone-600 hover:text-amber-600 py-2 px-1">Notifications</a>
                <a href="{{ route('client.account.chat') }}" class="block text-sm text-stone-600 hover:text-amber-600 py-2 px-1">AI Assistant</a>
            </div>
            <div class="border-t border-stone-100 pt-3 mt-2 flex gap-2">
                <a href="{{ route('auth.login') }}" class="flex-1 text-center border border-stone-200 text-stone-600 text-sm py-2 rounded-xl">Sign In</a>
                <a href="{{ route('auth.register') }}" class="flex-1 text-center bg-amber-600 text-white text-sm py-2 rounded-xl">Register</a>
            </div>
        </div>

        {{-- Desktop Nav Pills --}}
        <div class="hidden sm:flex max-w-7xl mx-auto px-6 gap-1 pb-2">
            <a href="{{ route('client.properties') }}" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ !request('type') && !request('status') ? 'bg-amber-100 text-amber-700' : 'text-stone-500 hover:bg-stone-100' }} transition">All Properties</a>
            <a href="{{ route('client.properties') }}?status=Pre-Selling" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ request('status') == 'Pre-Selling' ? 'bg-amber-100 text-amber-700' : 'text-stone-500 hover:bg-stone-100' }} transition">Pre-Selling</a>
            <a href="{{ route('client.properties') }}?status=RFO" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ request('status') == 'RFO' ? 'bg-amber-100 text-amber-700' : 'text-stone-500 hover:bg-stone-100' }} transition">Ready for Occupancy</a>
            <a href="{{ route('client.properties') }}?type=House+and+Lot" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ request('type') == 'House and Lot' ? 'bg-amber-100 text-amber-700' : 'text-stone-500 hover:bg-stone-100' }} transition">House & Lot</a>
            <a href="{{ route('client.properties') }}?type=Lot+Only" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ request('type') == 'Lot Only' ? 'bg-amber-100 text-amber-700' : 'text-stone-500 hover:bg-stone-100' }} transition">Lot Only</a>
            <a href="{{ route('client.properties') }}?type=Condominium" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ request('type') == 'Condominium' ? 'bg-amber-100 text-amber-700' : 'text-stone-500 hover:bg-stone-100' }} transition">Condominium</a>
            <a href="{{ route('client.properties') }}?type=Commercial" class="text-xs font-medium px-3 py-1.5 rounded-lg {{ request('type') == 'Commercial' ? 'bg-amber-100 text-amber-700' : 'text-stone-500 hover:bg-stone-100' }} transition">Commercial</a>
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
                    <a href="{{ route('client.account.chat') }}" class="block hover:text-white transition">AI Assistant</a>
                    <a href="#" class="block hover:text-white transition">Contact Us</a>
                    <a href="#" class="block hover:text-white transition">Privacy Policy</a>
                </div>
            </div>
        </div>
        <div class="border-t border-stone-800 py-4 text-center text-xs">
            © {{ date('Y') }} EstateFlow. All rights reserved.
        </div>
    </footer>

</body>
</html>
