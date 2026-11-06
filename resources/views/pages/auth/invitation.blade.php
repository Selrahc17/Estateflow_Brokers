@extends('layouts.auth')
@section('title', 'Agent Invitation')
@section('heading', $agent ? 'Join '.$agent->broker->name."'s Team" : 'Invitation Unavailable')
@section('subheading', $agent ? 'Finish setting up your agent account.' : 'This invitation link is invalid or has expired.')

@section('content')

    @if($agent)
    <form action="{{ route('auth.invitation.accept', $token) }}" method="POST" class="space-y-4"
        x-data="{ submitting: false }" @submit="submitting = true" @pageshow.window="submitting = false">
        @csrf

        <div class="text-sm text-stone-600 dark:text-stone-300">
            <p class="font-medium">{{ $agent->name }}</p>
            <p class="text-stone-500 dark:text-stone-400">{{ $agent->email }}</p>
        </div>

        @include('pages.auth._input', ['name' => 'phone', 'label' => 'Phone Number', 'type' => 'tel', 'icon' => 'phone', 'value' => $agent->phone, 'placeholder' => '+63 912 345 6789', 'autocomplete' => 'tel'])

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @include('pages.auth._input', ['name' => 'license_number', 'label' => 'PRC Accreditation Number', 'icon' => 'badge', 'attrs' => 'required'])
            @include('pages.auth._input', ['name' => 'prc_id_expires_at', 'label' => 'Accreditation Expiry', 'type' => 'date', 'icon' => 'calendar', 'attrs' => 'required'])
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @include('pages.auth._password-field', ['name' => 'password', 'label' => 'Create Password', 'autocomplete' => 'new-password'])
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

        @include('pages.auth._submit-button', ['label' => 'Accept Invitation', 'busyLabel' => 'Setting up...'])
    </form>
    @else
    <p class="text-sm text-stone-600 dark:text-stone-300">Please ask your broker to send you a new invitation.</p>
    <p class="text-center text-sm mt-6">
        <a href="{{ route('auth.login') }}" class="text-teal-700 dark:text-teal-400 hover:underline font-medium">← Back to Sign In</a>
    </p>
    @endif

@endsection
