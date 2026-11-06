@extends('layouts.auth')
@section('title', 'Forgot Password')
@section('heading', 'Forgot Password?')
@section('subheading', "Enter your email and we'll send you a reset link.")

@section('content')

    @if(session('success'))
    <div class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-900 rounded-xl p-3 mb-5 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
    @endif

    <form action="{{ route('auth.forgot.post') }}" method="POST" class="space-y-4"
        x-data="{ submitting: false }" @submit="submitting = true" @pageshow.window="submitting = false">
        @csrf

        @include('pages.auth._input', ['name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'icon' => 'mail', 'placeholder' => 'you@example.com', 'autocomplete' => 'email', 'attrs' => 'required autofocus'])

        @include('pages.auth._submit-button', ['label' => 'Send Reset Link', 'busyLabel' => 'Sending...'])
    </form>

    <p class="text-center text-sm mt-6">
        <a href="{{ route('auth.login') }}" class="text-teal-700 dark:text-teal-400 hover:underline font-medium">← Back to Sign In</a>
    </p>

@endsection
