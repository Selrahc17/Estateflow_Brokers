@extends('layouts.auth')
@section('title', 'Reset Password')
@section('heading', 'Reset Your Password')
@section('subheading', 'Enter your new password below.')

@section('content')

    {{-- The email field is hidden here, so its errors (such as an expired link) need their own message. --}}
    @error('email')
    <div role="alert" class="bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-900 rounded-xl p-3 mb-5 text-sm text-red-700 dark:text-red-300">
        {{ $message }}
        <a href="{{ route('auth.forgot') }}" class="underline font-medium">Request a new link</a>
    </div>
    @enderror

    <form action="{{ route('auth.reset.post') }}" method="POST" class="space-y-4"
        x-data="{
            submitting: false,
            strength: 0,
            measure(value) {
                this.strength = [value.length >= 8, /[A-Z]/.test(value), /[0-9]/.test(value), /[^A-Za-z0-9]/.test(value)].filter(Boolean).length;
            },
        }"
        @submit="submitting = true" @pageshow.window="submitting = false">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email ?? old('email') }}">

        <div>
            @include('pages.auth._password-field', ['name' => 'password', 'label' => 'New Password', 'autocomplete' => 'new-password', 'attrs' => '@input="measure($event.target.value)"'])

            {{-- Strength Meter --}}
            <div class="mt-2 space-y-1" x-show="strength > 0" style="display: none">
                <div class="flex gap-1">
                    @for($i = 1; $i <= 4; $i++)
                    <div class="flex-1 h-1.5 rounded-full transition-colors duration-300"
                        :class="strength >= {{ $i }}
                            ? (strength === 1 ? 'bg-red-400' : strength === 2 ? 'bg-yellow-400' : strength === 3 ? 'bg-blue-400' : 'bg-green-500')
                            : 'bg-stone-200 dark:bg-stone-700'">
                    </div>
                    @endfor
                </div>
                <p class="text-xs"
                    :class="strength === 1 ? 'text-red-600 dark:text-red-400' : strength === 2 ? 'text-yellow-700 dark:text-yellow-400' : strength === 3 ? 'text-blue-700 dark:text-blue-400' : 'text-green-700 dark:text-green-400'"
                    x-text="['', 'Weak', 'Fair', 'Good', 'Strong'][strength]">
                </p>
            </div>
        </div>

        @include('pages.auth._password-field', ['name' => 'password_confirmation', 'label' => 'Confirm New Password', 'autocomplete' => 'new-password'])

        <p class="text-xs text-stone-500 dark:text-stone-400">Use at least 8 characters. Adding capital letters, numbers, and symbols makes it stronger.</p>

        @include('pages.auth._submit-button', ['label' => 'Reset Password', 'busyLabel' => 'Resetting...'])
    </form>

    <p class="text-center text-sm mt-6">
        <a href="{{ route('auth.login') }}" class="text-teal-700 dark:text-teal-400 hover:underline font-medium">← Back to Sign In</a>
    </p>

@endsection
