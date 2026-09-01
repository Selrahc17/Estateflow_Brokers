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
    <title>EstateFlow — @yield('title', 'My Portal')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-50 font-sans" x-data="{ mobileMenu: false }" @resize.window="mobileMenu = false">

    {{-- Top Navigation --}}
    <nav class="bg-white border-b border-stone-200 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">

            {{-- Logo --}}
            <a href="{{ route('client.properties') }}" class="flex items-center gap-2">
                <div class="w-8 h-8 bg-[var(--color-primary)] rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <span class="text-lg font-bold text-stone-800">Estate<span class="text-[var(--color-primary)]">Flow</span></span>
            </a>

            {{-- Desktop Nav --}}
            <div class="hidden md:flex items-center gap-6 text-sm font-medium">
                <a href="{{ route('client.properties') }}" class="{{ request()->routeIs('client.properties') ? 'text-[var(--color-primary)]' : 'text-stone-500 hover:text-stone-800' }} transition">Browse</a>
                <a href="{{ route('client.account.home') }}" class="{{ request()->routeIs('client.account.home') ? 'text-[var(--color-primary)]' : 'text-stone-500 hover:text-stone-800' }} transition">Dashboard</a>
                <a href="{{ route('client.account.reservation') }}" class="{{ request()->routeIs('client.account.reservation') ? 'text-[var(--color-primary)]' : 'text-stone-500 hover:text-stone-800' }} transition">My Reservation</a>
                <a href="{{ route('client.account.documents') }}" class="{{ request()->routeIs('client.account.documents') ? 'text-[var(--color-primary)]' : 'text-stone-500 hover:text-stone-800' }} transition">Documents</a>
            </div>

            {{-- Right Side --}}
            <div class="hidden md:flex items-center gap-3">
                @php $unread = \App\Models\AppNotification::where('user_id', auth()->id())->where('is_read', false)->count(); @endphp
                <a href="{{ route('client.account.notifications') }}" class="relative text-stone-500 hover:text-[var(--color-primary)] transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    @if($unread > 0)
                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">{{ $unread }}</span>
                    @endif
                </a>
                <div class="flex items-center gap-2 pl-3 border-l border-stone-200">
                    <div class="w-8 h-8 bg-[var(--color-primary)] rounded-full flex items-center justify-center text-white font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-stone-700 leading-none">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-stone-400">Client</p>
                    </div>
                </div>
                <form action="{{ route('auth.logout') }}" method="POST" class="ml-1">
                    @csrf
                    <button type="submit" class="text-xs text-stone-400 hover:text-red-500 transition">Sign Out</button>
                </form>
            </div>

            {{-- Mobile Menu Button --}}
            <button @click="mobileMenu = !mobileMenu" class="md:hidden text-stone-500">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="mobileMenu" class="md:hidden border-t border-stone-100 px-6 py-4 space-y-3 bg-white">
            <a href="{{ route('client.properties') }}" class="block text-sm text-stone-600 hover:text-teal-600">Browse Properties</a>
            <a href="{{ route('client.account.home') }}" class="block text-sm text-stone-600 hover:text-teal-600">Dashboard</a>
            <a href="{{ route('client.account.reservation') }}" class="block text-sm text-stone-600 hover:text-teal-600">My Reservation</a>
            <a href="{{ route('client.account.documents') }}" class="block text-sm text-stone-600 hover:text-teal-600">Documents</a>
            <a href="{{ route('client.account.notifications') }}" class="block text-sm text-stone-600 hover:text-teal-600">Notifications</a>
            <form action="{{ route('auth.logout') }}" method="POST">
                @csrf
                <button type="submit" class="text-sm text-red-400">Sign Out</button>
            </form>
        </div>
    </nav>

    {{-- Page Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-stone-900 text-stone-400 mt-16">
        <div class="max-w-6xl mx-auto px-6 py-10 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-[var(--color-primary)] rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <span class="text-white font-semibold">EstateFlow</span>
            </div>
            <p class="text-xs">© {{ date('Y') }} EstateFlow. All rights reserved.</p>
            <div class="flex gap-4 text-xs">
                <a href="{{ route('client.legal.privacy') }}" class="hover:text-white transition">Privacy Policy</a>
                <a href="{{ route('client.legal.terms') }}" class="hover:text-white transition">Terms of Use</a>
                <a href="{{ route('client.account.chat') }}" class="hover:text-white transition">Contact Support</a>
            </div>
        </div>
    </footer>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
        }
    </script>
</body>
</html>
