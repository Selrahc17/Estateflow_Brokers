@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'Settings & Profile')
@section('page-subtitle', 'Manage your account preferences')

@section('content')

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-lg text-sm">
    @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Profile Card --}}
    <div class="bg-white rounded-xl border border-stone-200 p-6">
        <div class="flex flex-col items-center text-center mb-6">
            <div class="w-20 h-20 bg-amber-600 rounded-full flex items-center justify-center text-white text-3xl font-bold mb-3">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <h2 class="font-semibold text-stone-800">{{ auth()->user()->name }}</h2>
            <p class="text-sm text-stone-400">{{ auth()->user()->email }}</p>
            <span class="mt-2 px-3 py-1 bg-amber-100 text-amber-700 text-xs rounded-full font-medium">{{ ucfirst(auth()->user()->role) }}</span>
        </div>
        <div class="space-y-2 text-sm text-stone-600">
            <div class="flex justify-between py-2 border-b border-stone-100">
                <span class="text-stone-400">Member Since</span>
                <span>{{ auth()->user()->created_at->format('M Y') }}</span>
            </div>
        </div>
    </div>

    <div class="xl:col-span-2 space-y-5">

        {{-- Edit Profile --}}
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <h2 class="font-semibold text-stone-800 mb-4">Edit Profile</h2>
            <form action="{{ route('broker.settings.profile') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-stone-500 mb-1 block">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="text-xs text-stone-500 mb-1 block">Email</label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                </div>
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Save Changes</button>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <h2 class="font-semibold text-stone-800 mb-4">Change Password</h2>
            <form action="{{ route('broker.settings.password') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs text-stone-500 mb-1 block">Current Password</label>
                    <input type="password" name="current_password" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="text-xs text-stone-500 mb-1 block">New Password</label>
                    <input type="password" name="password" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="text-xs text-stone-500 mb-1 block">Confirm New Password</label>
                    <input type="password" name="password_confirmation" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <button type="submit" class="bg-stone-800 hover:bg-stone-900 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Update Password</button>
            </form>
        </div>

    </div>
</div>

@endsection
