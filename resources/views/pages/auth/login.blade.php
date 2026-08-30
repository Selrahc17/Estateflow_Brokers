<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EstateFlow — Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(251,191,36,0.14),_transparent_28%),linear-gradient(135deg,_#f8fafc_0%,_#f5f5f4_100%)] font-sans flex">

    {{-- Left Panel --}}
    <div class="hidden lg:flex lg:w-1/2 bg-stone-900 flex-col justify-between p-12 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(245,158,11,0.25),_transparent_30%),linear-gradient(135deg,_rgba(120,53,15,0.7),_rgba(28,25,23,0.95))]"></div>
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-16">
                <div class="w-11 h-11 bg-teal-600 rounded-2xl flex items-center justify-center shadow-lg shadow-amber-500/20">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-teal-500">EstateFlow</span>
            </div>
            <h1 class="text-4xl font-bold text-white leading-tight mb-4">
                Your Real Estate<br>Management Hub
            </h1>
            <p class="text-stone-300 text-lg leading-relaxed max-w-lg">
                Manage properties, reservations, clients, and payments — all in one place.
            </p>
        </div>
        <div class="relative z-10 space-y-4">
            @foreach([
                ['Property Listings','Manage all your lots and properties'],
                ['Client Management','Track clients and reservations'],
                ['Payment Tracking','Monitor payments and documents'],
            ] as $f)
            <div class="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 backdrop-blur-sm">
                <div class="w-8 h-8 bg-teal-600/20 rounded-lg flex items-center justify-center">
                    <div class="w-2 h-2 bg-teal-500 rounded-full"></div>
                </div>
                <div>
                    <p class="text-white text-sm font-medium">{{ $f[0] }}</p>
                    <p class="text-stone-400 text-xs">{{ $f[1] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Right Panel --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-8 lg:p-12">
        <div class="w-full max-w-md rounded-3xl border border-stone-200/80 bg-white/80 p-8 shadow-[0_20px_60px_-20px_rgba(0,0,0,0.25)] backdrop-blur">

            {{-- Mobile Logo --}}
            <div class="flex items-center justify-between gap-2 mb-8 lg:hidden">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-teal-700 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                    <span class="text-xl font-bold text-teal-700">EstateFlow</span>
                </div>
                <a href="{{ url('/') }}" class="text-sm font-medium text-stone-500 hover:text-teal-700 transition">
                    ← Back to Home
                </a>
            </div>

            <div class="mb-8">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm font-medium text-stone-500 hover:text-teal-700 transition mb-4">
                    <span>←</span>
                    <span>Back to Home</span>
                </a>
                <p class="text-sm font-semibold uppercase tracking-[0.25em] text-teal-700 mb-2">Welcome back</p>
                <h2 class="text-3xl font-bold text-stone-800">Sign in to your account</h2>
                <p class="text-stone-500 text-sm mt-2">Access your dashboard and manage your real estate work.</p>
            </div>

            {{-- Error --}}
            @if($errors->any())
            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-3 text-sm text-red-600">
                {{ $errors->first() }}
            </div>
            @endif

            {{-- Form --}}
            <form action="{{ route('auth.login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-sm text-stone-600 font-medium mb-1 block">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required
                        class="w-full border border-stone-200 rounded-2xl px-4 py-3 text-sm text-stone-700 focus:outline-none focus:ring-2 focus:ring-teal-400 focus:border-teal-500 bg-white shadow-sm">
                </div>
                <div>
                    <label class="text-sm text-stone-600 font-medium mb-1 block">Password</label>
                    <div class="relative">
                        <input id="password" type="password" name="password" placeholder="••••••••" required
                            class="w-full border border-stone-200 rounded-2xl px-4 py-3 text-sm text-stone-700 focus:outline-none focus:ring-2 focus:ring-teal-400 focus:border-teal-500 bg-white shadow-sm pr-11">
                        <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-teal-700 transition">
                            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg id="eye-off-icon" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 012.163-3.592M6.343 6.343A9.97 9.97 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 01-1.357 2.498M6.343 6.343L3 3m3.343 3.343l11.314 11.314M17.657 17.657L21 21"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <script>
                    function togglePassword() {
                        const input = document.getElementById('password');
                        const eyeIcon = document.getElementById('eye-icon');
                        const eyeOffIcon = document.getElementById('eye-off-icon');
                        const isHidden = input.type === 'password';
                        input.type = isHidden ? 'text' : 'password';
                        eyeIcon.classList.toggle('hidden', isHidden);
                        eyeOffIcon.classList.toggle('hidden', !isHidden);
                    }
                </script>
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 text-sm text-stone-500 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded border-stone-300 text-teal-700 focus:ring-teal-400">
                        Remember me
                    </label>
                    <a href="{{ route('auth.forgot') }}" class="text-sm text-teal-700 hover:underline font-medium">Forgot password?</a>
                </div>

                <button type="submit"
                    class="block w-full bg-teal-700 hover:bg-teal-800 text-white text-center py-3 rounded-2xl font-semibold transition shadow-lg shadow-amber-600/20">
                    Sign In
                </button>
            </form>

            <p class="text-center text-sm text-stone-400 mt-6">
                Don't have an account?
                <a href="{{ route('auth.register') }}" class="text-teal-700 hover:underline font-medium">Register here</a>
            </p>

        </div>
    </div>

</body>
</html>
