@extends('layouts.broker')
@section('title', 'Profile Settings')
@section('page-title', 'Profile Settings')
@section('page-subtitle', 'Manage your Broker profile and account security')

@section('content')
@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600">
    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
</div>
@endif

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="rounded-xl border border-stone-200 bg-white p-6">
        <div class="flex flex-col items-center text-center">
            <div class="mb-4 h-24 w-24 overflow-hidden rounded-full bg-red-100 text-center text-3xl font-bold leading-[6rem] text-red-700">
                @if(auth()->user()->avatar)
                    <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }} profile picture" class="h-full w-full object-cover">
                @else
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
            </div>
            <h2 class="font-semibold text-stone-800">{{ auth()->user()->name }}</h2>
            <p class="mt-1 text-sm text-stone-400">{{ auth()->user()->email }}</p>
            <span class="mt-3 rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">Broker</span>
        </div>
        <div class="mt-6 flex justify-between border-t border-stone-100 pt-4 text-sm">
            <span class="text-stone-400">Member Since</span>
            <span class="text-stone-600">{{ auth()->user()->created_at->format('M Y') }}</span>
        </div>
    </div>

    <div class="space-y-5 xl:col-span-2">
        <div class="rounded-xl border border-stone-200 bg-white p-6">
            <h2 class="mb-4 font-semibold text-stone-800">Edit Profile</h2>
            <form action="{{ route('broker.settings.profile') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs text-stone-500">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-stone-500">Email</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-400">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs text-stone-500">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', auth()->user()->phone) }}" class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-400">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs text-stone-500">Profile Photo</label>
                        <input type="file" name="avatar" accept="image/*" class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                    </div>
                </div>
                <button type="submit" class="rounded-lg bg-red-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-red-700">Save Changes</button>
            </form>
        </div>

        <div class="rounded-xl border border-stone-200 bg-white p-6">
            <h2 class="mb-4 font-semibold text-stone-800">Change Password</h2>
            <form action="{{ route('broker.settings.password') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-xs text-stone-500">Current Password</label>
                    <input type="password" name="current_password" required class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-400">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-stone-500">New Password</label>
                    <input type="password" name="password" required class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-400">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-stone-500">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required class="w-full rounded-lg border border-stone-200 px-3 py-2 text-sm focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-400">
                </div>
                <button type="submit" class="rounded-lg bg-stone-800 px-5 py-2 text-sm font-medium text-white transition hover:bg-stone-900">Update Password</button>
            </form>
        </div>
    </div>
</div>
@endsection
