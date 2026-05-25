@extends('layouts.public')
@section('title', 'My Profile')

@section('content')

{{-- Hero --}}
<div class="bg-gradient-to-r from-stone-900 to-amber-900 text-white">
    <div class="max-w-6xl mx-auto px-6 py-10">
        <p class="text-amber-400 text-xs uppercase tracking-widest font-semibold mb-1">Account</p>
        <h1 class="text-2xl font-bold">My Profile & Settings</h1>
        <p class="text-stone-300 text-sm mt-1">Manage your personal information and preferences</p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Left: Profile Card --}}
        <div class="space-y-5">

            {{-- Avatar + Info --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-6 text-center">
                <div class="relative inline-block mb-4">
                    <div class="w-20 h-20 bg-amber-600 rounded-2xl flex items-center justify-center text-white text-3xl font-bold mx-auto">J</div>
                    <button class="absolute -bottom-1 -right-1 w-7 h-7 bg-amber-600 rounded-full flex items-center justify-center border-2 border-white hover:bg-amber-700 transition">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </button>
                </div>
                <h2 class="font-bold text-stone-800 text-lg">Juan dela Cruz</h2>
                <p class="text-sm text-stone-400">juan@email.com</p>
                <span class="mt-2 inline-block px-3 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full">Client</span>
                <div class="mt-4 pt-4 border-t border-stone-100 space-y-2 text-sm text-stone-500 text-left">
                    <div class="flex justify-between"><span class="text-stone-400">Member Since</span><span class="font-medium text-stone-700">Jan 2025</span></div>
                    <div class="flex justify-between"><span class="text-stone-400">Reservation</span><span class="font-medium text-green-600">Active</span></div>
                    <div class="flex justify-between"><span class="text-stone-400">Documents</span><span class="font-medium text-stone-700">4 of 6</span></div>
                </div>
            </div>

            {{-- Danger Zone --}}
            <div class="bg-white rounded-2xl border border-red-100 p-5">
                <h3 class="font-semibold text-red-600 mb-3 text-sm">Danger Zone</h3>
                <button class="w-full text-center border border-red-200 text-red-500 hover:bg-red-50 py-2.5 rounded-xl text-sm font-medium transition">
                    Deactivate Account
                </button>
            </div>

        </div>

        {{-- Right: Edit Forms --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- Personal Information --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h2 class="font-semibold text-stone-800 mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Personal Information
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">First Name</label>
                        <input type="text" value="Juan" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 text-stone-700">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Last Name</label>
                        <input type="text" value="dela Cruz" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 text-stone-700">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Email Address</label>
                        <input type="email" value="juan@email.com" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 text-stone-700">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Phone Number</label>
                        <input type="text" value="+63 912 345 6789" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 text-stone-700">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Date of Birth</label>
                        <input type="date" value="1995-06-15" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 text-stone-700">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Civil Status</label>
                        <select class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 text-stone-700">
                            <option>Single</option>
                            <option selected>Married</option>
                            <option>Widowed</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Home Address</label>
                        <input type="text" value="123 Rizal St., Quezon City, Metro Manila" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 text-stone-700">
                    </div>
                </div>
                <button class="mt-5 bg-amber-600 hover:bg-amber-700 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition">
                    Save Changes
                </button>
            </div>

            {{-- Change Password --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h2 class="font-semibold text-stone-800 mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Change Password
                </h2>
                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Current Password</label>
                        <input type="password" placeholder="••••••••" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">New Password</label>
                            <input type="password" placeholder="••••••••" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Confirm Password</label>
                            <input type="password" placeholder="••••••••" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                    </div>
                </div>
                <button class="mt-5 bg-stone-800 hover:bg-stone-900 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition">
                    Update Password
                </button>
            </div>

            {{-- Notification Preferences --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h2 class="font-semibold text-stone-800 mb-5 flex items-center gap-2">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Notification Preferences
                </h2>
                <div class="space-y-4">
                    @foreach([
                        ['Email Notifications','Receive alerts and updates via email',true],
                        ['SMS Notifications','Receive alerts via text message',false],
                        ['Payment Reminders','Get reminded before payment due dates',true],
                        ['Document Alerts','Alerts when documents are missing or approved',true],
                        ['Reservation Updates','Updates on your reservation status',true],
                    ] as $pref)
                    <div class="flex items-center justify-between py-2 border-b border-stone-100 last:border-0" x-data="{ on: {{ $pref[2] ? 'true' : 'false' }} }">
                        <div>
                            <p class="text-sm font-medium text-stone-700">{{ $pref[0] }}</p>
                            <p class="text-xs text-stone-400">{{ $pref[1] }}</p>
                        </div>
                        <button @click="on = !on" :class="on ? 'bg-amber-500' : 'bg-stone-200'"
                            class="relative w-11 h-6 rounded-full transition-colors duration-200 shrink-0 ml-4">
                            <span :class="on ? 'translate-x-5' : 'translate-x-1'"
                                class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 inline-block"></span>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
