<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EstateFlow — Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 font-sans min-h-screen flex">

    {{-- Left Panel --}}
    <div class="hidden lg:flex lg:w-1/2 bg-stone-900 flex-col justify-between p-12 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-amber-900/40 to-stone-900"></div>
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-16">
                <div class="w-10 h-10 bg-amber-600 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-amber-400">EstateFlow</span>
            </div>
            <h1 class="text-4xl font-bold text-white leading-tight mb-4">
                Your Real Estate<br>Management Hub
            </h1>
            <p class="text-stone-400 text-lg leading-relaxed">
                Manage properties, reservations, clients, and payments — all in one place.
            </p>
        </div>
        <div class="relative z-10 space-y-4">
            @foreach([
                ['Property Listings','Manage all your lots and properties'],
                ['Client Management','Track clients and reservations'],
                ['AI Assistant','Smart inquiry handling 24/7'],
            ] as $f)
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-amber-600/20 rounded-lg flex items-center justify-center">
                    <div class="w-2 h-2 bg-amber-400 rounded-full"></div>
                </div>
                <div>
                    <p class="text-white text-sm font-medium">{{ $f[0] }}</p>
                    <p class="text-stone-500 text-xs">{{ $f[1] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Right Panel --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8" x-data="{ role: 'broker' }">
        <div class="w-full max-w-md">

            {{-- Mobile Logo --}}
            <div class="flex items-center gap-2 mb-8 lg:hidden">
                <div class="w-8 h-8 bg-amber-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <span class="text-xl font-bold text-amber-600">EstateFlow</span>
            </div>

            <h2 class="text-2xl font-bold text-stone-800 mb-1">Welcome back</h2>
            <p class="text-stone-400 text-sm mb-6">Sign in to your account to continue</p>

            {{-- Role Toggle --}}
            <div class="flex bg-stone-200 rounded-xl p-1 mb-6">
                <button @click="role = 'broker'"
                    :class="role === 'broker' ? 'bg-white text-amber-700 shadow-sm' : 'text-stone-500'"
                    class="flex-1 py-2 rounded-lg text-sm font-medium transition">
                    Broker / Agent
                </button>
                <button @click="role = 'client'"
                    :class="role === 'client' ? 'bg-white text-amber-700 shadow-sm' : 'text-stone-500'"
                    class="flex-1 py-2 rounded-lg text-sm font-medium transition">
                    Client
                </button>
            </div>

            {{-- Form --}}
            <form class="space-y-4">
                <div>
                    <label class="text-sm text-stone-600 font-medium mb-1 block">Email Address</label>
                    <input type="email" placeholder="you@example.com"
                        class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
                </div>
                <div>
                    <label class="text-sm text-stone-600 font-medium mb-1 block">Password</label>
                    <input type="password" placeholder="••••••••"
                        class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-stone-500 cursor-pointer">
                        <input type="checkbox" class="rounded border-stone-300 text-amber-600">
                        Remember me
                    </label>
                    <a href="{{ route('auth.forgot') }}" class="text-sm text-amber-600 hover:underline">Forgot password?</a>
                </div>

                {{-- Broker Login --}}
                <div x-show="role === 'broker'">
                    <a href="{{ route('dashboard') }}"
                        class="block w-full bg-amber-600 hover:bg-amber-700 text-white text-center py-3 rounded-xl font-medium transition">
                        Sign In as Broker
                    </a>
                </div>

                {{-- Client Login --}}
                <div x-show="role === 'client'">
                    <a href="{{ route('client.account.home') }}"
                        class="block w-full bg-stone-800 hover:bg-stone-900 text-white text-center py-3 rounded-xl font-medium transition">
                        Sign In as Client
                    </a>
                </div>
            </form>

            <p class="text-center text-sm text-stone-400 mt-6">
                Don't have an account?
                <a href="{{ route('auth.register') }}" class="text-amber-600 hover:underline font-medium">Register here</a>
            </p>

        </div>
    </div>

</body>
</html>
