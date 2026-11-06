@extends('layouts.auth')
@section('title', 'Sign In')
@section('heading', 'Welcome Back')
@section('subheading', 'Sign in to manage your real estate work')

@section('content')

    @if(session('success'))
    <div class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-900 rounded-xl p-3 mb-5 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
    @endif

    @if($errors->any())
    <div role="alert" class="bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-900 rounded-xl p-3 mb-5 text-sm text-red-700 dark:text-red-300">
        {{ $errors->first() }}
    </div>
    @endif

    <form action="{{ route('auth.login.post') }}" method="POST" class="space-y-4"
        x-data="{ submitting: false }" @submit="submitting = true" @pageshow.window="submitting = false">
        @csrf

        <div>
            <label for="email" class="text-sm text-stone-600 dark:text-stone-300 font-medium mb-1 block">Email Address</label>
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus autocomplete="email"
                    class="w-full border border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-800 text-stone-800 dark:text-stone-100 placeholder:text-stone-400 dark:placeholder:text-stone-500 rounded-xl pl-10 pr-4 py-2.5 text-sm transition focus:outline-none focus:bg-white dark:focus:bg-stone-800 focus:ring-2 focus:ring-teal-400">
            </div>
        </div>

        @include('pages.auth._password-field', ['name' => 'password', 'label' => 'Password'])

        <div class="flex items-center justify-between pt-1">
            <label class="flex items-center gap-2 text-sm text-stone-500 dark:text-stone-400 cursor-pointer">
                <input type="checkbox" name="remember" class="rounded border-stone-300 text-teal-700 focus:ring-teal-400">
                Remember me
            </label>
            <a href="{{ route('auth.forgot') }}" class="text-sm text-teal-700 dark:text-teal-400 hover:underline font-medium">Forgot password?</a>
        </div>

        @include('pages.auth._submit-button', ['label' => 'Sign In', 'busyLabel' => 'Signing in...'])
    </form>

@endsection
