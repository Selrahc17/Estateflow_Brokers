@extends('layouts.auth')
@section('title', 'Register')
@section('heading', 'Create an Account')
@section('subheading', 'Fill in your details to get started')

@section('content')

    @php
        $selectedRole = old('role') === 'broker' ? 'broker' : 'client';
        $isBroker = $selectedRole === 'broker';
        // Step 1 fields keep their values after an error, so step 2 is safe to reopen directly.
        $hasAccountError = $errors->hasAny(['role', 'first_name', 'last_name', 'email', 'phone']);
        $initialStep = $isBroker && $errors->any() && ! $hasAccountError ? 2 : 1;
        $brokerRequired = ':required="role === \'broker\'"';
    @endphp

    @if($errors->any())
    <div role="alert" class="bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-900 rounded-xl p-3 mb-5 text-sm text-red-700 dark:text-red-300">
        Please check the {{ $errors->count() === 1 ? 'field' : 'fields' }} marked below.
        {{ $isBroker ? 'You will also need to enter your password and choose your PRC ID file again.' : 'You will also need to enter your password again.' }}
    </div>
    @endif

    <form class="space-y-4" action="{{ route('auth.register.post') }}" method="POST" enctype="multipart/form-data"
        x-data="registerForm({ role: @js($selectedRole), step: @js($initialStep) })"
        @submit="submitting = true" @pageshow.window="submitting = false" @keydown.enter="continueOnEnter($event)">
        @csrf

        <input type="hidden" name="role" :value="role" value="{{ $selectedRole }}">

        {{-- Role --}}
        <div x-show="step === 1" @if($initialStep !== 1) style="display: none" @endif>
            <span class="text-sm text-stone-600 dark:text-stone-300 font-medium mb-1 block">Register as</span>
            <div class="relative grid grid-cols-2 bg-stone-100 dark:bg-stone-800 rounded-xl p-1">
                <span aria-hidden="true" class="absolute inset-y-1 left-1 w-[calc(50%-0.25rem)] bg-white dark:bg-stone-700 rounded-lg shadow-sm transition-transform duration-300 {{ $isBroker ? 'translate-x-full' : '' }}"
                    :class="role === 'broker' ? 'translate-x-full' : 'translate-x-0'"></span>
                <button type="button" @click="role = 'client'" :aria-pressed="role === 'client'"
                    :class="role === 'client' ? 'text-teal-800 dark:text-teal-300' : 'text-stone-600 dark:text-stone-400'"
                    class="relative py-2 rounded-lg text-sm font-medium transition">Client</button>
                <button type="button" @click="role = 'broker'" :aria-pressed="role === 'broker'"
                    :class="role === 'broker' ? 'text-teal-800 dark:text-teal-300' : 'text-stone-600 dark:text-stone-400'"
                    class="relative py-2 rounded-lg text-sm font-medium transition">Broker</button>
            </div>
            <p class="text-xs text-stone-500 dark:text-stone-400 mt-2">Are you an agent? Your broker creates your account, so please ask them for your login details.</p>
        </div>

        {{-- Broker progress --}}
        <div x-show="role === 'broker'" @unless($isBroker) style="display: none" @endunless>
            <p class="text-xs font-medium text-stone-500 dark:text-stone-400 mb-1.5"
                x-text="step === 1 ? 'Step 1 of 2: Account details' : 'Step 2 of 2: Broker credentials'">Step {{ $initialStep }} of 2</p>
            <div class="h-1 bg-stone-200 dark:bg-stone-700 rounded-full overflow-hidden">
                <div class="h-full bg-teal-600 dark:bg-teal-400 rounded-full transition-all duration-300 {{ $initialStep === 2 ? 'w-full' : 'w-1/2' }}"
                    :class="step === 2 ? 'w-full' : 'w-1/2'"></div>
            </div>
        </div>

        {{-- Account details --}}
        <div x-ref="accountStep" x-show="step === 1" class="space-y-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-x-2" x-transition:enter-end="opacity-100 translate-x-0"
            @if($initialStep !== 1) style="display: none" @endif>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @include('pages.auth._input', ['name' => 'first_name', 'label' => 'First Name', 'icon' => 'user', 'placeholder' => 'Juan', 'autocomplete' => 'given-name', 'attrs' => 'required'])
                @include('pages.auth._input', ['name' => 'last_name', 'label' => 'Last Name', 'icon' => 'user', 'placeholder' => 'dela Cruz', 'autocomplete' => 'family-name', 'attrs' => 'required'])
            </div>
            @include('pages.auth._input', ['name' => 'email', 'label' => 'Email Address', 'type' => 'email', 'icon' => 'mail', 'placeholder' => 'you@example.com', 'autocomplete' => 'email', 'attrs' => 'required'])
            @include('pages.auth._input', ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'tel', 'icon' => 'phone', 'placeholder' => '+63 912 345 6789', 'autocomplete' => 'tel', 'attrs' => $brokerRequired])
        </div>

        {{-- Broker credentials --}}
        <fieldset x-ref="credentialsStep" x-show="role === 'broker' && step === 2" :disabled="role !== 'broker'" class="space-y-4"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0"
            @if($initialStep !== 2) style="display: none" @endif>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @include('pages.auth._input', ['name' => 'license_number', 'label' => 'PRC License Number', 'icon' => 'badge', 'attrs' => $brokerRequired])
                @include('pages.auth._input', ['name' => 'prc_id_expires_at', 'label' => 'PRC ID Expiry', 'type' => 'date', 'icon' => 'calendar', 'attrs' => $brokerRequired])
                @include('pages.auth._input', ['name' => 'dhsud_registration_number', 'label' => 'DHSUD Registration Number', 'icon' => 'badge', 'attrs' => $brokerRequired])
                @include('pages.auth._input', ['name' => 'dhsud_registration_expires_at', 'label' => 'DHSUD Registration Expiry', 'type' => 'date', 'icon' => 'calendar', 'attrs' => $brokerRequired])
            </div>

            @include('pages.auth._input', ['name' => 'tin', 'label' => 'TIN', 'icon' => 'hash', 'placeholder' => '123-456-789-000', 'attrs' => $brokerRequired])

            <div>
                <label for="prc_id_file" class="text-sm text-stone-600 dark:text-stone-300 font-medium mb-1 block">PRC ID</label>
                <input id="prc_id_file" type="file" name="prc_id_file" accept=".jpg,.jpeg,.png,.pdf" :required="role === 'broker'"
                    class="w-full border {{ $errors->has('prc_id_file') ? 'border-red-300 dark:border-red-500' : 'border-stone-200 dark:border-stone-700' }} bg-stone-50 dark:bg-stone-800 text-stone-600 dark:text-stone-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 file:mr-3 file:border-0 file:bg-teal-50 dark:file:bg-teal-900 file:text-teal-700 dark:file:text-teal-100 file:rounded-lg file:px-3 file:py-1 file:text-xs file:font-medium">
                <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">JPG, PNG, or PDF up to 10 MB. Stored privately.</p>
                @error('prc_id_file')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>
        </fieldset>

        {{-- Password, consent, and submit come last so nothing the browser cannot restore sits on a hidden step. --}}
        <div x-show="role === 'client' || step === 2" class="space-y-4" @if($isBroker && $initialStep === 1) style="display: none" @endif>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @include('pages.auth._password-field', ['name' => 'password', 'label' => 'Password', 'autocomplete' => 'new-password'])
                @include('pages.auth._password-field', ['name' => 'password_confirmation', 'label' => 'Confirm Password', 'autocomplete' => 'new-password'])
            </div>

            {{-- Data Privacy Act consent --}}
            <div>
                <label class="flex items-start gap-2 text-sm text-stone-500 dark:text-stone-400 cursor-pointer">
                    <input type="checkbox" name="privacy_consent" value="1" required @checked(old('privacy_consent')) class="mt-0.5 rounded border-stone-300 text-teal-700 focus:ring-teal-400">
                    <span>
                        I have read the <a href="{{ route('client.legal.privacy') }}" target="_blank" rel="noopener" class="text-teal-700 dark:text-teal-400 hover:underline font-medium">Privacy Policy</a>
                        and consent to the collection and processing of my personal information under the Data Privacy Act of 2012 (RA 10173).
                    </span>
                </label>
                @error('privacy_consent')<p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3">
                <button type="button" x-show="step === 2" @click="back()" @if($initialStep !== 2) style="display: none" @endif
                    class="px-5 border border-stone-200 dark:border-stone-700 text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800 rounded-xl text-sm font-medium transition">Back</button>
                <div class="flex-1">
                    @include('pages.auth._submit-button', ['label' => 'Create Account', 'busyLabel' => 'Creating account...'])
                </div>
            </div>

            <p x-show="role === 'broker'" class="text-center text-xs text-stone-500 dark:text-stone-400" @unless($isBroker) style="display: none" @endunless>
                Next, you'll upload your bond proof, BIR 2303, AMLC certificate, and PTR. Your account is activated once an admin approves them.
            </p>
        </div>

        <button type="button" x-show="role === 'broker' && step === 1" @click="next()" @unless($isBroker && $initialStep === 1) style="display: none" @endunless
            class="w-full bg-teal-700 hover:bg-teal-800 text-white py-3 rounded-xl font-medium transition active:scale-[0.98]">Continue</button>

    </form>

@endsection
