@extends('layouts.admin')
@section('title', 'System Settings')
@section('page-title', 'System Settings')
@section('page-subtitle', 'Configure system-wide settings and preferences')

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Left: Settings Menu --}}
    <div class="space-y-2" x-data="{ tab: 'general' }">
        @foreach([
            ['general','General Settings','M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ['notifications','Notifications','M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
            ['security','Security','M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            ['email','Email Templates','M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ['roles','Roles & Permissions','M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ] as $item)
        <button @click="tab='{{ $item[0] }}'"
            :class="tab==='{{ $item[0] }}' ? 'bg-red-600 text-white' : 'bg-white text-stone-600 hover:bg-stone-50 border border-stone-200'"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition text-left">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item[2] }}"/></svg>
            {{ $item[1] }}
        </button>
        @endforeach
    </div>

    {{-- Right: Settings Content --}}
    <div class="xl:col-span-2 space-y-5" x-data="{ tab: 'general' }">

        {{-- General Settings --}}
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="font-semibold text-stone-800 mb-5 flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                General Settings
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">System Name</label>
                    <input type="text" value="EstateFlow" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">System Email</label>
                    <input type="email" value="admin@estateflow.com" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Contact Phone</label>
                    <input type="text" value="(02) 8123-4567" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Timezone</label>
                    <select class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                        <option selected>Asia/Manila (UTC+8)</option>
                        <option>UTC</option>
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Office Address</label>
                    <input type="text" value="Unit 5, Realty Building, Quezon City, Metro Manila" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                </div>
            </div>
            <button class="mt-5 bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition">Save Changes</button>
        </div>

        {{-- Notification Settings --}}
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="font-semibold text-stone-800 mb-5 flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                Notification Settings
            </h2>
            <div class="space-y-4">
                @foreach([
                    ['Email Notifications','Send system alerts via email',true],
                    ['SMS Notifications','Send alerts via SMS',false],
                    ['Document Alerts','Alert admins when documents need verification',true],
                    ['Reservation Alerts','Alert admins on new reservation submissions',true],
                    ['Broker Approval Alerts','Alert admins on new broker applications',true],
                ] as $pref)
                <div class="flex items-center justify-between py-2 border-b border-stone-100 last:border-0" x-data="{ on: {{ $pref[2] ? 'true' : 'false' }} }">
                    <div>
                        <p class="text-sm font-medium text-stone-700">{{ $pref[0] }}</p>
                        <p class="text-xs text-stone-400">{{ $pref[1] }}</p>
                    </div>
                    <button @click="on = !on" :class="on ? 'bg-red-500' : 'bg-stone-200'"
                        class="relative w-11 h-6 rounded-full transition-colors duration-200 shrink-0 ml-4">
                        <span :class="on ? 'translate-x-5' : 'translate-x-1'"
                            class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 inline-block"></span>
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Security Settings --}}
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="font-semibold text-stone-800 mb-5 flex items-center gap-2">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Security Settings
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Session Timeout (minutes)</label>
                    <input type="number" value="120" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Max Login Attempts</label>
                    <input type="number" value="5" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                </div>
            </div>
            <div class="space-y-3">
                @foreach([
                    ['Two-Factor Authentication','Require 2FA for admin accounts',true],
                    ['Audit Logging','Log all user actions for security review',true],
                    ['IP Restriction','Restrict admin access to specific IPs',false],
                ] as $sec)
                <div class="flex items-center justify-between py-2 border-b border-stone-100 last:border-0" x-data="{ on: {{ $sec[2] ? 'true' : 'false' }} }">
                    <div>
                        <p class="text-sm font-medium text-stone-700">{{ $sec[0] }}</p>
                        <p class="text-xs text-stone-400">{{ $sec[1] }}</p>
                    </div>
                    <button @click="on = !on" :class="on ? 'bg-red-500' : 'bg-stone-200'"
                        class="relative w-11 h-6 rounded-full transition-colors duration-200 shrink-0 ml-4">
                        <span :class="on ? 'translate-x-5' : 'translate-x-1'"
                            class="absolute top-1 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 inline-block"></span>
                    </button>
                </div>
                @endforeach
            </div>
            <button class="mt-5 bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition">Save Security Settings</button>
        </div>

    </div>
</div>

@endsection
