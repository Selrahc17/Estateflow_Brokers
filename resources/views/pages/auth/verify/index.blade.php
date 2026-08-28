<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EstateFlow — Verify Your Email</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 font-sans min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md">

        {{-- Card --}}
        <div class="bg-white rounded-2xl border border-stone-200 p-8 shadow-sm text-center">

            {{-- Logo --}}
            <div class="flex items-center justify-center gap-2 mb-8">
                <div class="w-8 h-8 bg-teal-700 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <span class="text-xl font-bold text-teal-700">EstateFlow</span>
            </div>

            {{-- Icon --}}
            <div class="w-20 h-20 bg-teal-100 rounded-2xl flex items-center justify-center mx-auto mb-5">
                <svg class="w-10 h-10 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-stone-800 mb-2">Check Your Email</h1>
            <p class="text-stone-400 text-sm leading-relaxed mb-2">
                We've sent a verification link to:
            </p>
            <p class="font-semibold text-stone-700 text-sm mb-6">juan@email.com</p>

            <p class="text-stone-400 text-xs leading-relaxed mb-6">
                Click the link in the email to verify your account. If you don't see it, check your spam folder.
            </p>

            {{-- Steps --}}
            <div class="bg-stone-50 rounded-xl p-4 mb-6 text-left space-y-3">
                @foreach([
                    ['1','Open your email inbox'],
                    ['2','Find the email from EstateFlow'],
                    ['3','Click "Verify Email Address"'],
                    ['4','You\'ll be redirected to your account'],
                ] as $step)
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 bg-teal-700 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0">{{ $step[0] }}</div>
                    <p class="text-sm text-stone-600">{{ $step[1] }}</p>
                </div>
                @endforeach
            </div>

            {{-- Resend --}}
            <button class="w-full bg-teal-700 hover:bg-teal-800 text-white py-3 rounded-xl font-semibold text-sm transition mb-3">
                Resend Verification Email
            </button>

            <a href="{{ route('auth.login') }}" class="block w-full text-center border border-stone-200 text-stone-500 hover:bg-stone-50 py-3 rounded-xl text-sm font-medium transition">
                Back to Login
            </a>

            <p class="text-xs text-stone-400 mt-5">
                Wrong email?
                <a href="{{ route('auth.register') }}" class="text-teal-700 hover:underline">Register again</a>
            </p>

        </div>
    </div>

</body>
</html>
