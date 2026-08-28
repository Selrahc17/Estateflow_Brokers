<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EstateFlow — Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 font-sans min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md bg-white rounded-2xl border border-stone-200 p-8 shadow-sm">

        <div class="flex items-center gap-2 mb-6">
            <div class="w-8 h-8 bg-teal-700 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </div>
            <span class="text-xl font-bold text-teal-700">EstateFlow</span>
        </div>

        <div class="w-14 h-14 bg-teal-100 rounded-2xl flex items-center justify-center mb-5">
            <svg class="w-7 h-7 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
        </div>

        <h2 class="text-2xl font-bold text-stone-800 mb-1">Forgot Password?</h2>
        <p class="text-stone-400 text-sm mb-6">Enter your email and we'll send you a reset link.</p>

        @if(session('success'))
        <div class="mb-5 rounded-2xl border border-green-200 bg-green-50 p-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-3 text-sm text-red-600">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('auth.forgot.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm text-stone-600 font-medium mb-1 block">Email Address</label>
                <input type="email" placeholder="you@example.com"
                    class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
            </div>
            <button type="submit" class="w-full bg-teal-700 hover:bg-teal-800 text-white py-3 rounded-xl font-medium transition">
                Send Reset Link
            </button>
        </form>

        <p class="text-center text-sm text-stone-400 mt-5">
            <a href="{{ route('auth.login') }}" class="text-teal-700 hover:underline font-medium">← Back to Login</a>
        </p>

    </div>

</body>
</html>
