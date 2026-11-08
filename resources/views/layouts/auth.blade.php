<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    {{-- Runs before first paint so dark mode pages never flash white. --}}
    <script>
        if (localStorage.getItem('darkMode') === 'true') document.documentElement.classList.add('dark');
        // The page-to-page transition already animates the form, so skip the entrance animation.
        addEventListener('pagereveal', (event) => {
            if (event.viewTransition) document.documentElement.classList.add('auth-skip-enter');
        });
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1A6B79">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/favicon.ico" sizes="32x32 48x48">
    <link rel="icon" href="/icons/icon-192.png" type="image/png" sizes="192x192">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <title>EstateFlow — @yield('title')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .dark { color-scheme: dark; }

        @view-transition { navigation: auto; }

        ::view-transition-group(auth-switch-thumb) {
            animation-duration: 300ms;
            animation-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        }

        ::view-transition-old(auth-form),
        ::view-transition-new(auth-form) {
            animation-duration: 200ms;
        }

        @keyframes auth-rise {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: none; }
        }

        .auth-enter { animation: auth-rise 350ms ease-out both; }
        .auth-skip-enter .auth-enter { animation: none; }

        @media (prefers-reduced-motion: reduce) {
            .auth-enter { animation: none; }

            ::view-transition-group(*),
            ::view-transition-old(*),
            ::view-transition-new(*) {
                animation: none !important;
            }
        }
    </style>
</head>
<body class="bg-white dark:bg-stone-900 font-sans text-stone-800 dark:text-stone-200">

    @php
        $onLogin = request()->routeIs('auth.login');
        $showSwitch = request()->routeIs('auth.login', 'auth.register');
        $logoIcon = 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6';
    @endphp

    <div class="min-h-screen lg:grid lg:grid-cols-[2fr_3fr]">

        {{-- Brand Panel --}}
        <aside class="hidden lg:flex lg:sticky lg:top-0 lg:h-screen flex-col justify-between overflow-hidden bg-gradient-to-br from-[#112E3B] to-[#1A6B79] p-12 text-white" style="view-transition-name: auth-brand">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center overflow-hidden">
                    @if($appLogo)
                        <img src="{{ $appLogo }}" alt="EstateFlow" class="w-full h-full object-cover">
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $logoIcon }}"/></svg>
                    @endif
                </div>
                <span class="text-xl font-bold">EstateFlow</span>
            </div>

            <div>
                <h1 class="text-4xl font-bold leading-tight mb-4">Your Real Estate<br>Management Hub</h1>
                <p class="text-teal-100 leading-relaxed max-w-sm">Manage properties, clients, reservations, and commissions, all in one place.</p>
            </div>

            <ul class="space-y-3">
                @foreach(['Property listings', 'Client management', 'Reservations and commissions'] as $feature)
                <li class="flex items-center gap-3 text-sm text-teal-50">
                    <svg class="w-5 h-5 text-teal-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $feature }}
                </li>
                @endforeach
            </ul>
        </aside>

        {{-- Form Panel --}}
        <main class="flex justify-center px-6 py-8 sm:py-12">
            <div class="w-full max-w-md lg:pt-[6vh]">

                <div class="flex items-center justify-between gap-2 mb-8">
                    <div class="flex items-center gap-2 lg:invisible">
                        <div class="w-8 h-8 bg-teal-700 rounded-lg flex items-center justify-center overflow-hidden">
                            @if($appLogo)
                                <img src="{{ $appLogo }}" alt="" class="w-full h-full object-cover">
                            @else
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $logoIcon }}"/></svg>
                            @endif
                        </div>
                        <span class="text-xl font-bold text-teal-700 dark:text-teal-400">EstateFlow</span>
                    </div>
                    <a href="{{ url('/') }}" class="text-sm font-medium text-stone-500 dark:text-stone-400 hover:text-teal-700 dark:hover:text-teal-400 transition">
                        ← Back to Home
                    </a>
                </div>

                {{-- Sign In / Create Account switch. The thumb slides between pages via view transitions. --}}
                @if($showSwitch)
                <nav aria-label="Sign in or create an account" class="relative grid grid-cols-2 bg-stone-100 dark:bg-stone-800 rounded-xl p-1 mb-8">
                    <span aria-hidden="true" class="absolute inset-y-1 w-[calc(50%-0.25rem)] bg-white dark:bg-stone-700 rounded-lg shadow-sm {{ $onLogin ? 'left-1' : 'left-1/2' }}" style="view-transition-name: auth-switch-thumb"></span>
                    <a href="{{ route('auth.login') }}" @if($onLogin) aria-current="page" @endif
                        class="relative text-center py-2 rounded-lg text-sm font-medium transition {{ $onLogin ? 'text-teal-800 dark:text-teal-300' : 'text-stone-600 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200' }}">Sign In</a>
                    <a href="{{ route('auth.register') }}" @unless($onLogin) aria-current="page" @endunless
                        class="relative text-center py-2 rounded-lg text-sm font-medium transition {{ $onLogin ? 'text-stone-600 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200' : 'text-teal-800 dark:text-teal-300' }}">Create Account</a>
                </nav>
                @endif

                <div class="auth-enter" style="view-transition-name: auth-form">
                    <h2 class="text-2xl font-bold text-stone-800 dark:text-white mb-1">@yield('heading')</h2>
                    <p class="text-stone-500 dark:text-stone-400 text-sm mb-6">@yield('subheading')</p>

                    @yield('content')
                </div>

            </div>
        </main>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
        }
    </script>
</body>
</html>
