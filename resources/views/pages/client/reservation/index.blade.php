@extends('layouts.public')
@section('title', 'My Reservation')

@section('content')

{{-- Page Hero --}}
<div class="bg-gradient-to-r from-stone-900 to-amber-900 text-white">
    <div class="max-w-6xl mx-auto px-6 py-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <p class="text-amber-400 text-xs uppercase tracking-widest font-semibold mb-1">My Reservation</p>
            <h1 class="text-2xl font-bold">Palm Residences — Lot 12-B</h1>
            <p class="text-stone-300 text-sm mt-1 flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                Quezon City, Metro Manila
            </p>
        </div>
        <span class="px-4 py-2 bg-green-500 text-white text-sm font-semibold rounded-full flex items-center gap-2">
            <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span> Active
        </span>
    </div>
</div>

<div class="max-w-6xl mx-auto px-6 py-8 grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Left --}}
    <div class="xl:col-span-2 space-y-6">

        {{-- Property Details --}}
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
                <h2 class="font-semibold text-stone-800">Reservation Details</h2>
                <span class="text-xs text-stone-400">Reserved: July 1, 2025</span>
            </div>
            <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-4">
                @foreach([
                    ['Lot Number','12-B','amber'],
                    ['Block','Block 1','stone'],
                    ['Area','120 sqm','stone'],
                    ['Total Price','₱1,200,000','green'],
                    ['Type','Residential','stone'],
                    ['Status','Active','green'],
                ] as $d)
                <div class="p-4 bg-stone-50 rounded-xl border border-stone-100">
                    <p class="text-xs text-stone-400 mb-1">{{ $d[0] }}</p>
                    <p class="text-sm font-bold {{ $d[2]==='green' ? 'text-green-600' : ($d[2]==='amber' ? 'text-amber-600' : 'text-stone-700') }}">{{ $d[1] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Payment Progress --}}
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-semibold text-stone-800">Payment Progress</h2>
                <span class="text-sm font-bold text-amber-600">25% Complete</span>
            </div>
            <div class="w-full bg-stone-100 rounded-full h-4 mb-3 overflow-hidden">
                <div class="bg-gradient-to-r from-amber-500 to-amber-400 h-4 rounded-full transition-all duration-700" style="width: 25%"></div>
            </div>
            <div class="flex justify-between text-sm">
                <div>
                    <p class="text-stone-400 text-xs">Amount Paid</p>
                    <p class="font-bold text-green-600">₱300,000</p>
                </div>
                <div class="text-center">
                    <p class="text-stone-400 text-xs">Remaining</p>
                    <p class="font-bold text-red-500">₱900,000</p>
                </div>
                <div class="text-right">
                    <p class="text-stone-400 text-xs">Total Price</p>
                    <p class="font-bold text-stone-700">₱1,200,000</p>
                </div>
            </div>
            <div class="mt-5 pt-5 border-t border-stone-100 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('client.account.payments') }}" class="flex-1 text-center bg-amber-600 hover:bg-amber-700 text-white py-2.5 rounded-xl text-sm font-semibold transition">
                    View Payment Schedule
                </a>
                <a href="{{ route('client.account.payments') }}" class="flex-1 text-center border border-amber-600 text-amber-600 hover:bg-amber-50 py-2.5 rounded-xl text-sm font-semibold transition">
                    Pay Now
                </a>
            </div>
        </div>

        {{-- Reservation Timeline --}}
        <div class="bg-white rounded-2xl border border-stone-200 p-6">
            <h2 class="font-semibold text-stone-800 mb-5">Reservation Timeline</h2>
            <div class="relative">
                <div class="absolute left-3.5 top-0 bottom-0 w-0.5 bg-stone-100"></div>
                <div class="space-y-5">
                    @foreach([
                        ['Reservation Filed','Your reservation for Lot 12-B has been filed and confirmed.','Jul 1, 2025','done'],
                        ['Documents Submitted','Required documents have been submitted for review.','Jul 3, 2025','done'],
                        ['Payment Started','First installment payment received.','Jul 5, 2025','done'],
                        ['Full Payment','Complete all monthly installments.','Pending','pending'],
                        ['Title Transfer','Transfer of title to your name.','Pending','pending'],
                    ] as $t)
                    <div class="flex items-start gap-4 relative">
                        <div class="w-7 h-7 rounded-full shrink-0 flex items-center justify-center z-10
                            {{ $t[3]==='done' ? 'bg-green-500' : 'bg-stone-200' }}">
                            @if($t[3]==='done')
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            @else
                            <div class="w-2 h-2 bg-stone-400 rounded-full"></div>
                            @endif
                        </div>
                        <div class="flex-1 pb-1">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold {{ $t[3]==='done' ? 'text-stone-800' : 'text-stone-400' }}">{{ $t[0] }}</p>
                                <span class="text-xs {{ $t[3]==='done' ? 'text-green-600' : 'text-stone-400' }}">{{ $t[2] }}</span>
                            </div>
                            <p class="text-xs text-stone-400 mt-0.5">{{ $t[1] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- Right --}}
    <div class="space-y-5">

        {{-- Quick Actions --}}
        <div class="bg-white rounded-2xl border border-stone-200 p-5">
            <h3 class="font-semibold text-stone-800 mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('client.account.payments') }}" class="flex items-center gap-3 p-3 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 transition">
                    <div class="w-9 h-9 bg-amber-600 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">Pay Installment</p>
                        <p class="text-xs text-amber-600">Due: July 15, 2025</p>
                    </div>
                </a>
                <a href="{{ route('client.account.documents') }}" class="flex items-center gap-3 p-3 rounded-xl bg-stone-50 hover:bg-stone-100 text-stone-700 transition">
                    <div class="w-9 h-9 bg-stone-200 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">Upload Document</p>
                        <p class="text-xs text-red-500">1 document missing</p>
                    </div>
                </a>
                <a href="{{ route('client.account.chat') }}" class="flex items-center gap-3 p-3 rounded-xl bg-stone-50 hover:bg-stone-100 text-stone-700 transition">
                    <div class="w-9 h-9 bg-stone-200 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">Ask AI Assistant</p>
                        <p class="text-xs text-stone-400">Available 24/7</p>
                    </div>
                </a>
            </div>
        </div>

        {{-- Broker Card --}}
        <div class="bg-white rounded-2xl border border-stone-200 p-5">
            <h3 class="font-semibold text-stone-800 mb-4">Your Broker</h3>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 bg-amber-600 rounded-full flex items-center justify-center text-white font-bold text-lg shrink-0">B</div>
                <div>
                    <p class="font-semibold text-stone-800 text-sm">Broker Name</p>
                    <p class="text-xs text-stone-400">Licensed Real Estate Broker</p>
                    <p class="text-xs text-stone-400">PRC-2024-00123</p>
                </div>
            </div>
            <div class="space-y-2 text-xs text-stone-500 mb-4">
                <p class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg> broker@estateflow.com</p>
                <p class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg> +63 912 345 6789</p>
            </div>
            <a href="{{ route('client.account.chat') }}" class="block w-full text-center bg-amber-600 hover:bg-amber-700 text-white py-2.5 rounded-xl text-sm font-semibold transition">
                Message Broker
            </a>
        </div>

    </div>
</div>

@endsection
