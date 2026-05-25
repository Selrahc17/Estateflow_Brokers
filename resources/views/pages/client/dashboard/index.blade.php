@extends('layouts.public')
@section('title', 'My Account')

@section('content')

{{-- Hero --}}
<div class="bg-gradient-to-r from-stone-900 to-amber-900 text-white">
    <div class="max-w-6xl mx-auto px-6 py-12 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 bg-amber-500 rounded-2xl flex items-center justify-center text-white text-2xl font-bold shrink-0">J</div>
            <div>
                <p class="text-amber-400 text-xs uppercase tracking-widest font-semibold mb-1">Welcome back</p>
                <h1 class="text-2xl font-bold">Juan dela Cruz</h1>
                <p class="text-stone-300 text-sm mt-0.5">juan@email.com · Client since January 2025</p>
            </div>
        </div>
        <a href="{{ route('client.account.profile') }}" class="flex items-center gap-2 bg-white/10 hover:bg-white/20 border border-white/20 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit Profile
        </a>
    </div>
</div>

{{-- Status Cards --}}
<div class="max-w-6xl mx-auto px-6 -mt-5">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
            ['Total Paid','₱300,000','bg-green-50 border-green-100','text-green-600'],
            ['Remaining','₱900,000','bg-red-50 border-red-100','text-red-500'],
            ['Next Due','Jul 15','bg-amber-50 border-amber-100','text-amber-600'],
            ['Documents','4/6','bg-blue-50 border-blue-100','text-blue-600'],
        ] as $s)
        <div class="bg-white rounded-2xl border {{ $s[2] }} p-4 shadow-sm">
            <p class="text-xs text-stone-500 mb-1">{{ $s[0] }}</p>
            <p class="text-xl font-bold {{ $s[3] }}">{{ $s[1] }}</p>
        </div>
        @endforeach
    </div>
</div>

{{-- Quick Nav Cards --}}
<div class="max-w-6xl mx-auto px-6 py-8">
    <h2 class="font-semibold text-stone-800 mb-4">Quick Access</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
        @foreach([
            ['My Reservation','client.account.reservation','M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z','amber'],
            ['My Payments','client.account.payments','M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z','green'],
            ['My Documents','client.account.documents','M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z','blue'],
            ['Notifications','client.account.notifications','M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9','red'],
            ['Feedback','client.account.feedback','M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z','purple'],
        ] as $nav)
        <a href="{{ route($nav[1]) }}" class="bg-white rounded-2xl border border-stone-200 p-5 flex flex-col items-center gap-3 hover:shadow-md hover:border-amber-200 transition group text-center">
            <div class="w-12 h-12 bg-stone-100 group-hover:bg-amber-100 rounded-xl flex items-center justify-center transition">
                <svg class="w-6 h-6 text-stone-500 group-hover:text-amber-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $nav[2] }}"/></svg>
            </div>
            <p class="text-sm font-semibold text-stone-700 group-hover:text-amber-700 transition">{{ $nav[0] }}</p>
        </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Reservation Summary --}}
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between">
                <h2 class="font-semibold text-stone-800">My Reservation</h2>
                <a href="{{ route('client.account.reservation') }}" class="text-xs text-amber-600 hover:underline">View details →</a>
            </div>
            <div class="p-5">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <p class="font-bold text-stone-800">Palm Residences</p>
                        <p class="text-sm text-stone-400 flex items-center gap-1 mt-0.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            Quezon City · Lot 12-B, Block 1
                        </p>
                    </div>
                    <span class="px-2.5 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">Active</span>
                </div>
                <div class="mb-3">
                    <div class="flex justify-between text-xs text-stone-500 mb-1">
                        <span>Payment Progress</span><span class="font-semibold text-amber-600">25%</span>
                    </div>
                    <div class="w-full bg-stone-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-amber-500 h-2 rounded-full" style="width:25%"></div>
                    </div>
                </div>
                <div class="flex justify-between text-xs text-stone-500">
                    <span>₱300,000 paid</span><span>₱900,000 remaining</span>
                </div>
            </div>
        </div>

        {{-- Recent Notifications --}}
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between">
                <h2 class="font-semibold text-stone-800">Recent Notifications</h2>
                <a href="{{ route('client.account.notifications') }}" class="text-xs text-amber-600 hover:underline">See all →</a>
            </div>
            <div class="divide-y divide-stone-100">
                @foreach([
                    ['Payment Reminder','July installment due Jul 15.','yellow','2h ago',true],
                    ['Missing Document','TIN Certificate not uploaded.','red','5h ago',true],
                    ['Payment Confirmed','June payment received ✓','green','Jun 10',false],
                ] as $n)
                <div class="flex items-start gap-3 px-5 py-3.5 {{ $n[4] ? 'bg-amber-50/50' : '' }}">
                    <div class="w-2 h-2 rounded-full mt-1.5 shrink-0
                        {{ $n[2]==='yellow' ? 'bg-yellow-500' : '' }}
                        {{ $n[2]==='red' ? 'bg-red-500' : '' }}
                        {{ $n[2]==='green' ? 'bg-green-500' : '' }}"></div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-stone-700">{{ $n[0] }}</p>
                        <p class="text-xs text-stone-400">{{ $n[1] }}</p>
                    </div>
                    <span class="text-xs text-stone-400 shrink-0">{{ $n[3] }}</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

@endsection
