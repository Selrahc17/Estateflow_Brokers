<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EstateFlow — Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 font-sans min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-lg bg-white rounded-2xl border border-stone-200 p-8 shadow-sm">

        <div class="flex items-center gap-2 mb-6">
            <div class="w-8 h-8 bg-amber-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </div>
            <span class="text-xl font-bold text-amber-600">EstateFlow</span>
        </div>

        <h2 class="text-2xl font-bold text-stone-800 mb-1">Create an Account</h2>
        <p class="text-stone-400 text-sm mb-6">Fill in your details to get started</p>

        <form class="space-y-4" x-data="{ role: 'client' }">

            {{-- Role --}}
            <div>
                <label class="text-sm text-stone-600 font-medium mb-1 block">Register as</label>
                <div class="flex bg-stone-100 rounded-xl p-1">
                    <button type="button" @click="role = 'client'"
                        :class="role === 'client' ? 'bg-white text-amber-700 shadow-sm' : 'text-stone-500'"
                        class="flex-1 py-2 rounded-lg text-sm font-medium transition">Client</button>
                    <button type="button" @click="role = 'broker'"
                        :class="role === 'broker' ? 'bg-white text-amber-700 shadow-sm' : 'text-stone-500'"
                        class="flex-1 py-2 rounded-lg text-sm font-medium transition">Broker / Agent</button>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-stone-600 font-medium mb-1 block">First Name</label>
                    <input type="text" placeholder="Juan" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="text-sm text-stone-600 font-medium mb-1 block">Last Name</label>
                    <input type="text" placeholder="dela Cruz" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
            </div>

            <div>
                <label class="text-sm text-stone-600 font-medium mb-1 block">Email Address</label>
                <input type="email" placeholder="you@example.com" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div>
                <label class="text-sm text-stone-600 font-medium mb-1 block">Phone Number</label>
                <input type="text" placeholder="+63 912 345 6789" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            {{-- Broker-only field --}}
            <div x-show="role === 'broker'">
                <label class="text-sm text-stone-600 font-medium mb-1 block">PRC License Number</label>
                <input type="text" placeholder="PRC-2024-00000" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div>
                <label class="text-sm text-stone-600 font-medium mb-1 block">Password</label>
                <input type="password" placeholder="••••••••" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div>
                <label class="text-sm text-stone-600 font-medium mb-1 block">Confirm Password</label>
                <input type="password" placeholder="••••••••" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white py-3 rounded-xl font-medium transition">
                Create Account
            </button>

        </form>

        <p class="text-center text-sm text-stone-400 mt-5">
            Already have an account?
            <a href="{{ route('auth.login') }}" class="text-amber-600 hover:underline font-medium">Sign in</a>
        </p>

    </div>

</body>
</html>
