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

        {{-- Admin Profile Settings --}}
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="font-semibold text-stone-800 mb-5 flex items-center gap-2">
                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Admin Profile
            </h2>
            <form action="{{ route('admin.settings.profile') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full overflow-hidden bg-teal-700 flex items-center justify-center shrink-0">
                        @if(auth()->user()->avatar)
                            <img src="{{ auth()->user()->avatar }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-white text-2xl font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Profile Photo</label>
                        <input type="file" name="avatar" accept="image/*" class="w-full text-sm text-stone-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Full Name</label>
                        <input type="text" name="name" value="{{ auth()->user()->name }}" required class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 @error('name') ring-2 ring-red-400 @enderror">
                        @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Email</label>
                        <input type="email" name="email" value="{{ auth()->user()->email }}" required class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 @error('email') ring-2 ring-red-400 @enderror">
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Phone</label>
                        <input type="text" name="phone" value="{{ auth()->user()->phone }}" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    </div>
                </div>
                <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition">Save Profile</button>
            </form>
        </div>

        {{-- Change Password --}}
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="font-semibold text-stone-800 mb-5 flex items-center gap-2">
                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Change Password
            </h2>
            <form action="{{ route('admin.settings.password') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Current Password</label>
                    <input type="password" name="current_password" required class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 @error('current_password') ring-2 ring-red-400 @enderror">
                    @error('current_password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">New Password</label>
                        <input type="password" name="password" required class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 @error('password') ring-2 ring-red-400 @enderror">
                        @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Confirm Password</label>
                        <input type="password" name="password_confirmation" required class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    </div>
                </div>
                <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition">Update Password</button>
            </form>
        </div>

        {{-- Logo Upload --}}
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="font-semibold text-stone-800 mb-5 flex items-center gap-2">
                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                System Logo
            </h2>
            <div class="flex items-center gap-5 mb-5">
@php $currentLogo = \App\Models\Setting::get('logo_url'); @endphp
                <div class="w-16 h-16 rounded-xl bg-teal-700 flex items-center justify-center overflow-hidden shrink-0">
                    @if($currentLogo)
                        <img src="{{ $currentLogo }}" class="w-full h-full object-cover">
                    @else
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    @endif
                </div>
                <div>
                    <p class="text-sm font-medium text-stone-700">Current Logo</p>
                    <p class="text-xs text-stone-400 mt-0.5">Appears on all sidebars and the public navbar. Recommended: square image, at least 128×128px.</p>
                </div>
            </div>
            <form action="{{ route('admin.settings.logo') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
                @csrf
                <input type="file" name="logo" accept="image/*" required class="flex-1 text-sm text-stone-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white px-5 py-2 rounded-xl text-sm font-semibold transition shrink-0">Upload</button>
            </form>
        </div>

        {{-- Chatbot Logo --}}
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="font-semibold text-stone-800 mb-5 flex items-center gap-2">
                <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                AI Chatbot Logo
            </h2>
            @php $currentChatbotLogo = \App\Models\Setting::get('chatbot_logo'); @endphp
            <div class="flex items-center gap-5 mb-5">
                <div class="w-16 h-16 rounded-xl bg-teal-500 flex items-center justify-center overflow-hidden shrink-0">
                    @if($currentChatbotLogo)
                        <img src="{{ $currentChatbotLogo }}" class="w-full h-full object-cover">
                    @else
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                    @endif
                </div>
                <div>
                    <p class="text-sm font-medium text-stone-700">Current Chatbot Logo</p>
                    <p class="text-xs text-stone-400 mt-0.5">Appears in the floating chatbot widget header and message bubbles on the public site.</p>
                </div>
            </div>
            <form action="{{ route('admin.settings.chatbot-logo') }}" method="POST" enctype="multipart/form-data" class="flex items-center gap-3">
                @csrf
                <input type="file" name="chatbot_logo" accept="image/*" required class="flex-1 text-sm text-stone-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white px-5 py-2 rounded-xl text-sm font-semibold transition shrink-0">Upload</button>
            </form>
        </div>

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
