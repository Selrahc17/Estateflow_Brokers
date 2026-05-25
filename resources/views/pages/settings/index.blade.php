@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'Settings & Profile')
@section('page-subtitle', 'Manage your account and system preferences')

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Profile --}}
    <div class="bg-white rounded-xl border border-stone-200 p-6">
        <div class="flex flex-col items-center text-center mb-6">
            <div class="w-20 h-20 bg-amber-600 rounded-full flex items-center justify-center text-white text-3xl font-bold mb-3">B</div>
            <h2 class="font-semibold text-stone-800">Broker Name</h2>
            <p class="text-sm text-stone-400">broker@estateflow.com</p>
            <span class="mt-2 px-3 py-1 bg-amber-100 text-amber-700 text-xs rounded-full font-medium">Broker / Agent</span>
        </div>
        <div class="space-y-2 text-sm text-stone-600">
            <div class="flex justify-between py-2 border-b border-stone-100">
                <span class="text-stone-400">Phone</span>
                <span>+63 912 345 6789</span>
            </div>
            <div class="flex justify-between py-2 border-b border-stone-100">
                <span class="text-stone-400">License No.</span>
                <span>PRC-2024-00123</span>
            </div>
            <div class="flex justify-between py-2">
                <span class="text-stone-400">Member Since</span>
                <span>Jan 2024</span>
            </div>
        </div>
    </div>

    {{-- Edit Profile + Preferences --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- Edit Profile --}}
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <h2 class="font-semibold text-stone-800 mb-4">Edit Profile</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs text-stone-500 mb-1 block">First Name</label>
                    <input type="text" value="Broker" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="text-xs text-stone-500 mb-1 block">Last Name</label>
                    <input type="text" value="Name" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="text-xs text-stone-500 mb-1 block">Email</label>
                    <input type="email" value="broker@estateflow.com" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="text-xs text-stone-500 mb-1 block">Phone</label>
                    <input type="text" value="+63 912 345 6789" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
            </div>
            <button class="mt-4 bg-amber-600 hover:bg-amber-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Save Changes</button>
        </div>

        {{-- Notification Preferences --}}
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <h2 class="font-semibold text-stone-800 mb-4">Notification Preferences</h2>
            <div class="space-y-3">
                @foreach([
                    ['Email Notifications','Receive alerts via email'],
                    ['SMS Notifications','Receive alerts via SMS'],
                    ['Payment Reminders','Auto-remind clients of due payments'],
                    ['Document Alerts','Alert when documents are missing'],
                    ['Reservation Updates','Notify on reservation status changes'],
                ] as $pref)
                <div class="flex items-center justify-between py-2 border-b border-stone-100 last:border-0" x-data="{ on: true }">
                    <div>
                        <p class="text-sm font-medium text-stone-700">{{ $pref[0] }}</p>
                        <p class="text-xs text-stone-400">{{ $pref[1] }}</p>
                    </div>
                    <button @click="on = !on" :class="on ? 'bg-amber-500' : 'bg-stone-200'" class="relative w-11 h-6 rounded-full transition-colors duration-200">
                        <span :class="on ? 'translate-x-5' : 'translate-x-1'" class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 inline-block"></span>
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Change Password --}}
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <h2 class="font-semibold text-stone-800 mb-4">Change Password</h2>
            <div class="space-y-3">
                <div>
                    <label class="text-xs text-stone-500 mb-1 block">Current Password</label>
                    <input type="password" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="text-xs text-stone-500 mb-1 block">New Password</label>
                    <input type="password" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="text-xs text-stone-500 mb-1 block">Confirm Password</label>
                    <input type="password" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
            </div>
            <button class="mt-4 bg-stone-800 hover:bg-stone-900 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Update Password</button>
        </div>

    </div>
</div>

@endsection
