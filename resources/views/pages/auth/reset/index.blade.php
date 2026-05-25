<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EstateFlow — Reset Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 font-sans min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl border border-stone-200 p-8 shadow-sm" x-data="{ showNew: false, showConfirm: false, strength: 0 }">

            {{-- Logo --}}
            <div class="flex items-center gap-2 mb-8">
                <div class="w-8 h-8 bg-amber-600 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <span class="text-xl font-bold text-amber-600">EstateFlow</span>
            </div>

            {{-- Icon --}}
            <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center mb-5">
                <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-stone-800 mb-1">Reset Your Password</h1>
            <p class="text-stone-400 text-sm mb-6">Enter your new password below. Make sure it's strong and secure.</p>

            <form class="space-y-4">

                {{-- New Password --}}
                <div>
                    <label class="text-sm font-semibold text-stone-600 mb-1.5 block">New Password</label>
                    <div class="relative">
                        <input :type="showNew ? 'text' : 'password'"
                            @input="
                                let v = $event.target.value;
                                strength = 0;
                                if(v.length >= 8) strength++;
                                if(/[A-Z]/.test(v)) strength++;
                                if(/[0-9]/.test(v)) strength++;
                                if(/[^A-Za-z0-9]/.test(v)) strength++;
                            "
                            placeholder="••••••••"
                            class="w-full border border-stone-200 rounded-xl px-4 py-3 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <button type="button" @click="showNew = !showNew"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600">
                            <svg x-show="!showNew" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showNew" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                    {{-- Strength Meter --}}
                    <div class="mt-2 space-y-1" x-show="strength > 0">
                        <div class="flex gap-1">
                            @for($i = 1; $i <= 4; $i++)
                            <div class="flex-1 h-1.5 rounded-full transition-colors duration-300"
                                :class="strength >= {{ $i }}
                                    ? (strength === 1 ? 'bg-red-400' : strength === 2 ? 'bg-yellow-400' : strength === 3 ? 'bg-blue-400' : 'bg-green-500')
                                    : 'bg-stone-200'">
                            </div>
                            @endfor
                        </div>
                        <p class="text-xs"
                            :class="strength === 1 ? 'text-red-500' : strength === 2 ? 'text-yellow-600' : strength === 3 ? 'text-blue-600' : 'text-green-600'"
                            x-text="['','Weak','Fair','Good','Strong'][strength]">
                        </p>
                    </div>
                </div>

                {{-- Confirm Password --}}
                <div>
                    <label class="text-sm font-semibold text-stone-600 mb-1.5 block">Confirm New Password</label>
                    <div class="relative">
                        <input :type="showConfirm ? 'text' : 'password'"
                            placeholder="••••••••"
                            class="w-full border border-stone-200 rounded-xl px-4 py-3 pr-12 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <button type="button" @click="showConfirm = !showConfirm"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600">
                            <svg x-show="!showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Requirements --}}
                <div class="bg-stone-50 rounded-xl p-4 space-y-2">
                    <p class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-2">Password Requirements</p>
                    @foreach([
                        'At least 8 characters',
                        'One uppercase letter (A-Z)',
                        'One number (0-9)',
                        'One special character (!@#$)',
                    ] as $req)
                    <div class="flex items-center gap-2 text-xs text-stone-500">
                        <svg class="w-3.5 h-3.5 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $req }}
                    </div>
                    @endforeach
                </div>

                <a href="{{ route('auth.login') }}"
                    class="block w-full text-center bg-amber-600 hover:bg-amber-700 text-white py-3 rounded-xl font-semibold text-sm transition">
                    Reset Password
                </a>

            </form>

            <p class="text-center text-xs text-stone-400 mt-5">
                <a href="{{ route('auth.login') }}" class="text-amber-600 hover:underline">← Back to Login</a>
            </p>

        </div>
    </div>

</body>
</html>
