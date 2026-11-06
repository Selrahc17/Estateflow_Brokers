@extends('layouts.broker')
@section('title', 'Profile Settings')
@section('page-title', 'Profile Settings')
@section('page-subtitle', 'Manage your Broker profile and account security')

@section('content')
@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-5 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 px-4 py-3 text-sm text-red-600 dark:text-red-300">
    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
</div>
@endif

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-6">
        <div class="flex flex-col items-center text-center">
            <div class="mb-4 h-24 w-24 overflow-hidden rounded-full bg-teal-100 dark:bg-teal-900 text-center text-3xl font-bold leading-[6rem] text-teal-800 dark:text-teal-200">
                @if(auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }} profile picture" class="h-full w-full object-cover">
                @else
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
            </div>
            <h2 class="font-semibold text-stone-800 dark:text-stone-100">{{ auth()->user()->name }}</h2>
            <p class="mt-1 text-sm text-stone-400">{{ auth()->user()->email }}</p>
            <span class="mt-3 rounded-full bg-teal-100 dark:bg-teal-900 px-3 py-1 text-xs font-medium text-teal-800 dark:text-teal-200">Broker</span>
        </div>
        <div class="mt-6 flex justify-between border-t border-stone-100 dark:border-stone-800 pt-4 text-sm">
            <span class="text-stone-400">Member Since</span>
            <span class="text-stone-600 dark:text-stone-300">{{ auth()->user()->created_at->format('M Y') }}</span>
        </div>
    </div>

    <div class="space-y-5 xl:col-span-2">
        <div class="rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-6">
            <h2 class="mb-4 font-semibold text-stone-800 dark:text-stone-100">Edit Profile</h2>
            <form action="{{ route('broker.settings.profile') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs text-stone-500 dark:text-stone-400">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required class="w-full rounded-lg border border-stone-200 dark:border-stone-700 px-3 py-2 text-sm focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-stone-500 dark:text-stone-400">Email</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required class="w-full rounded-lg border border-stone-200 dark:border-stone-700 px-3 py-2 text-sm focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-stone-500 dark:text-stone-400">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}" class="w-full rounded-lg border border-stone-200 dark:border-stone-700 px-3 py-2 text-sm focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs text-stone-500 dark:text-stone-400">Profile Photo</label>
                        <input type="file" name="avatar" accept="image/*" class="w-full rounded-lg border border-stone-200 dark:border-stone-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    </div>
                </div>
                <button type="submit" class="rounded-lg bg-teal-700 px-5 py-2 text-sm font-medium text-white transition hover:bg-teal-800">Save Changes</button>
            </form>
        </div>

        <div class="rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-6">
            <h2 class="mb-4 font-semibold text-stone-800 dark:text-stone-100">Change Password</h2>
            <form action="{{ route('broker.settings.password') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-xs text-stone-500 dark:text-stone-400">Current Password</label>
                    <input type="password" name="current_password" required class="w-full rounded-lg border border-stone-200 dark:border-stone-700 px-3 py-2 text-sm focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-stone-500 dark:text-stone-400">New Password</label>
                    <input type="password" name="password" required class="w-full rounded-lg border border-stone-200 dark:border-stone-700 px-3 py-2 text-sm focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-stone-500 dark:text-stone-400">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required class="w-full rounded-lg border border-stone-200 dark:border-stone-700 px-3 py-2 text-sm focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <button type="submit" class="rounded-lg bg-stone-800 px-5 py-2 text-sm font-medium text-white transition hover:bg-stone-900">Update Password</button>
            </form>
        </div>
    </div>
</div>

<section class="mt-5 rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-6" aria-labelledby="license-heading">
    <h2 id="license-heading" class="font-semibold text-stone-800 dark:text-stone-100">License and Compliance</h2>
    <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">Keep your details current when you renew. New documents go to an admin for review.</p>

    <div class="mt-4 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs text-stone-500 dark:text-stone-400">
        <span class="flex items-center gap-2">PRC ID @include('pages.broker._renewal-badge', ['expiresAt' => auth()->user()->prc_id_expires_at])</span>
        <span class="flex items-center gap-2">DHSUD @include('pages.broker._renewal-badge', ['expiresAt' => auth()->user()->dhsud_registration_expires_at])</span>
    </div>

    <form action="{{ route('broker.settings.license') }}" method="POST" class="mt-5">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                ['license_number', 'PRC License Number', 'text', auth()->user()->license_number],
                ['prc_id_expires_at', 'PRC ID Expiry', 'date', auth()->user()->prc_id_expires_at?->format('Y-m-d')],
                ['tin', 'TIN', 'text', auth()->user()->tin],
                ['dhsud_registration_number', 'DHSUD Registration Number', 'text', auth()->user()->dhsud_registration_number],
                ['dhsud_registration_expires_at', 'DHSUD Registration Expiry', 'date', auth()->user()->dhsud_registration_expires_at?->format('Y-m-d')],
            ] as [$field, $label, $type, $value])
            <div>
                <label for="{{ $field }}" class="mb-1 block text-xs text-stone-500 dark:text-stone-400">{{ $label }}</label>
                <input id="{{ $field }}" type="{{ $type }}" name="{{ $field }}" value="{{ old($field, $value) }}" required class="w-full rounded-lg border border-stone-200 dark:border-stone-700 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
            </div>
            @endforeach
        </div>
        <button type="submit" class="mt-4 rounded-lg bg-teal-700 px-5 py-2 text-sm font-medium text-white transition hover:bg-teal-800">Save License Details</button>
    </form>

    <div class="mt-6 divide-y divide-stone-100 dark:divide-stone-800 border-t border-stone-100 dark:border-stone-800">
        @foreach($checklist as $type => $item)
        @php($document = $item['document'])
        <div class="flex flex-col gap-3 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-medium text-stone-700 dark:text-stone-200">{{ $item['label'] }}</p>
                <p class="text-xs text-stone-500 dark:text-stone-400">
                    {{ $document ? ucfirst(str_replace('_', ' ', $document->status)) : 'Not uploaded' }}
                    @if($document?->notes) · {{ $document->notes }} @endif
                </p>
                @if($document)
                <a href="{{ route('broker.settings.documents.download', $document) }}" class="text-xs font-medium text-teal-700 dark:text-teal-300 hover:underline">View uploaded file</a>
                @endif
            </div>
            <form action="{{ route('broker.settings.documents.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2 sm:flex-row">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
                <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required aria-label="New {{ $item['label'] }} file" class="min-w-0 rounded-lg border border-stone-200 dark:border-stone-700 px-3 py-1.5 text-xs">
                <button type="submit" class="shrink-0 rounded-lg border border-teal-200 dark:border-teal-800 px-4 py-1.5 text-xs font-medium text-teal-700 dark:text-teal-300 transition hover:bg-teal-50 dark:hover:bg-teal-900">{{ $document ? 'Replace' : 'Upload' }}</button>
            </form>
        </div>
        @endforeach
    </div>
</section>
@endsection
