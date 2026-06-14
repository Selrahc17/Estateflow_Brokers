@extends('layouts.public')
@section('title', 'Notifications')

@section('content')

{{-- Page Hero --}}
<div class="bg-gradient-to-r from-stone-900 to-amber-900 text-white">
    <div class="max-w-6xl mx-auto px-6 py-10 flex items-center justify-between">
        <div>
            <p class="text-amber-400 text-xs uppercase tracking-widest font-semibold mb-1">Notifications</p>
            <h1 class="text-2xl font-bold">Your Alerts & Updates</h1>
            <p class="text-stone-300 text-sm mt-1">Stay updated on your reservation, payments, and documents</p>
        </div>
        <div class="hidden sm:flex items-center gap-2 bg-white/10 px-4 py-2 rounded-xl">
            <span class="w-2.5 h-2.5 bg-red-400 rounded-full"></span>
            <span class="text-sm font-semibold">2 unread</span>
        </div>
    </div>
</div>

<div class="max-w-6xl mx-auto px-6 py-8">

    {{-- Filter Tabs --}}
    <div class="flex gap-2 mb-6" x-data="{ tab: 'all' }">
        @foreach([['all','All'],['unread','Unread'],['payment','Payments'],['document','Documents']] as $t)
        <button @click="tab='{{ $t[0] }}'"
            :class="tab==='{{ $t[0] }}' ? 'bg-amber-600 text-white' : 'bg-white text-stone-500 border border-stone-200 hover:bg-stone-50'"
            class="px-4 py-2 rounded-xl text-sm font-medium transition">
            {{ $t[1] }}
        </button>
        @endforeach
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Notification List --}}
        <div class="xl:col-span-2 space-y-3">
            @foreach([
                ['Payment Reminder','Your July installment of ₱50,000 is due on July 15, 2025. Please settle on time to avoid penalties.','yellow','2 hours ago',false,'payment','M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                ['Missing Document','Your TIN Certificate is still missing. Please upload it to complete your requirements.','red','5 hours ago',false,'document','M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['Payment Confirmed','Your June payment of ₱50,000 has been received and confirmed. Thank you!','green','Jun 10, 2025',true,'payment','M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['Document Approved','Your Birth Certificate has been reviewed and approved by your broker.','green','Jul 2, 2025',true,'document','M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['Reservation Confirmed','Your reservation for Lot 12-B, Palm Residences has been confirmed and is now active.','blue','Jul 1, 2025',true,'reservation','M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ] as $n)
            <div class="bg-white rounded-2xl border border-stone-200 p-5 flex items-start gap-4 hover:shadow-sm transition
                {{ !$n[4] ? 'border-l-4 border-l-amber-500' : '' }}">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                    {{ $n[2]==='yellow' ? 'bg-yellow-100' : '' }}
                    {{ $n[2]==='red' ? 'bg-red-100' : '' }}
                    {{ $n[2]==='green' ? 'bg-green-100' : '' }}
                    {{ $n[2]==='blue' ? 'bg-blue-100' : '' }}">
                    <svg class="w-5 h-5
                        {{ $n[2]==='yellow' ? 'text-yellow-600' : '' }}
                        {{ $n[2]==='red' ? 'text-red-500' : '' }}
                        {{ $n[2]==='green' ? 'text-green-600' : '' }}
                        {{ $n[2]==='blue' ? 'text-blue-600' : '' }}"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $n[6] }}"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-2 mb-1">
                        <p class="font-semibold text-stone-800 text-sm">{{ $n[0] }}</p>
                        <div class="flex items-center gap-2 shrink-0">
                            @if(!$n[4])
                            <span class="bg-amber-100 text-amber-700 text-xs font-semibold px-2 py-0.5 rounded-full">New</span>
                            @endif
                            <span class="text-xs text-stone-400">{{ $n[3] }}</span>
                        </div>
                    </div>
                    <p class="text-sm text-stone-500 leading-relaxed">{{ $n[1] }}</p>
                    @if(!$n[4])
                    <div class="flex gap-2 mt-3">
                        @if($n[5]==='document')
                        <a href="{{ route('client.account.documents') }}" class="text-xs bg-amber-600 hover:bg-amber-700 text-white px-3 py-1.5 rounded-lg font-medium transition">Upload Now</a>
                        @endif
                        <button class="text-xs text-stone-400 hover:text-stone-600 px-3 py-1.5 rounded-lg border border-stone-200 hover:bg-stone-50 transition">Dismiss</button>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Sidebar --}}
        <div class="space-y-5">

            {{-- Summary --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <h3 class="font-semibold text-stone-800 mb-4">Summary</h3>
                <div class="space-y-3">
                    @foreach([
                        ['Unread','2','text-amber-600','bg-amber-100'],
                        ['Payment Alerts','1','text-red-500','bg-red-100'],
                        ['Document Alerts','1','text-yellow-600','bg-yellow-100'],
                        ['Total','5','text-stone-600','bg-stone-100'],
                    ] as $s)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-stone-600">{{ $s[0] }}</span>
                        <span class="w-7 h-7 {{ $s[3] }} {{ $s[2] }} rounded-full flex items-center justify-center text-xs font-bold">{{ $s[1] }}</span>
                    </div>
                    @endforeach
                </div>
                <button class="mt-4 w-full text-center text-xs text-stone-400 hover:text-stone-600 border border-stone-200 hover:bg-stone-50 py-2 rounded-xl transition">
                    Mark all as read
                </button>
            </div>

            {{-- Notification Settings --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <h3 class="font-semibold text-stone-800 mb-4">Notification Settings</h3>
                <div class="space-y-3">
                    @foreach([
                        ['Payment Reminders',true],
                        ['Document Alerts',true],
                        ['Email Notifications',true],
                        ['SMS Alerts',false],
                    ] as $pref)
                    <div class="flex items-center justify-between" x-data="{ on: {{ $pref[1] ? 'true' : 'false' }} }">
                        <span class="text-sm text-stone-600">{{ $pref[0] }}</span>
                        <button @click="on = !on" :class="on ? 'bg-amber-500' : 'bg-stone-200'"
                            class="relative w-10 h-5 rounded-full transition-colors duration-200">
                            <span :class="on ? 'translate-x-5' : 'translate-x-1'"
                                class="absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200 inline-block"></span>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
