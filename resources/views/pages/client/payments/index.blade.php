@extends('layouts.public')
@section('title', 'My Payments')

@section('content')

{{-- Page Hero --}}
<div class="bg-gradient-to-r from-stone-900 to-amber-900 text-white">
    <div class="max-w-6xl mx-auto px-6 py-10">
        <p class="text-amber-400 text-xs uppercase tracking-widest font-semibold mb-1">My Payments</p>
        <h1 class="text-2xl font-bold">Payment History & Schedule</h1>
        <p class="text-stone-300 text-sm mt-1">Palm Residences — Lot 12-B, Block 1</p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-6 py-8 space-y-6">

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
            ['Total Paid','₱300,000','bg-green-50 border-green-100','text-green-600','M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Remaining Balance','₱900,000','bg-red-50 border-red-100','text-red-500','M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['Next Due Date','Jul 15, 2025','bg-amber-50 border-amber-100','text-amber-600','M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['Monthly Amount','₱50,000','bg-blue-50 border-blue-100','text-blue-600','M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
        ] as $s)
        <div class="bg-white rounded-2xl border {{ $s[2] }} p-5">
            <p class="text-xs text-stone-500 mb-2">{{ $s[0] }}</p>
            <p class="text-xl font-bold {{ $s[3] }}">{{ $s[1] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Next Payment Banner --}}
    <div class="bg-white rounded-2xl border border-amber-200 overflow-hidden">
        <div class="bg-amber-600 px-6 py-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <p class="text-white text-sm font-semibold">Payment Due in 14 days</p>
        </div>
        <div class="px-6 py-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-amber-100 rounded-2xl flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div>
                    <p class="text-xl font-bold text-stone-800">₱50,000</p>
                    <p class="text-sm text-stone-500">Monthly Installment — July 2025</p>
                    <p class="text-xs text-amber-600 font-semibold mt-0.5">Due: July 15, 2025</p>
                </div>
            </div>
            <div class="flex gap-3">
                <button class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Pay Now
                </button>
                <button class="border border-stone-200 text-stone-600 hover:bg-stone-50 px-4 py-2.5 rounded-xl text-sm font-medium transition">
                    Set Reminder
                </button>
            </div>
        </div>
    </div>

    {{-- Progress Bar --}}
    <div class="bg-white rounded-2xl border border-stone-200 p-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-semibold text-stone-800">Overall Payment Progress</h2>
            <span class="text-sm font-bold text-amber-600">25% — 6 of 24 months paid</span>
        </div>
        <div class="w-full bg-stone-100 rounded-full h-3 overflow-hidden">
            <div class="bg-gradient-to-r from-amber-500 to-amber-400 h-3 rounded-full" style="width: 25%"></div>
        </div>
        <div class="flex justify-between text-xs text-stone-400 mt-2">
            <span>₱0</span>
            <span>₱600,000 (50%)</span>
            <span>₱1,200,000</span>
        </div>
    </div>

    {{-- Payment History Table --}}
    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
            <h2 class="font-semibold text-stone-800">Payment History</h2>
            <button class="flex items-center gap-2 text-sm text-stone-500 hover:text-amber-600 transition border border-stone-200 px-3 py-1.5 rounded-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export
            </button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 border-b border-stone-100">
                    <tr class="text-left text-stone-500">
                        <th class="px-6 py-3 font-medium">Period</th>
                        <th class="px-6 py-3 font-medium">Amount</th>
                        <th class="px-6 py-3 font-medium">Type</th>
                        <th class="px-6 py-3 font-medium">Date Paid</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium">Receipt</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach([
                        ['June 2025','₱50,000','Monthly Installment','Jun 10, 2025','Paid'],
                        ['May 2025','₱50,000','Monthly Installment','May 12, 2025','Paid'],
                        ['April 2025','₱50,000','Monthly Installment','Apr 8, 2025','Paid'],
                        ['March 2025','₱50,000','Monthly Installment','Mar 11, 2025','Paid'],
                        ['February 2025','₱50,000','Monthly Installment','Feb 9, 2025','Paid'],
                        ['January 2025','₱50,000','Reservation Fee','Jan 5, 2025','Paid'],
                    ] as $p)
                    <tr class="hover:bg-stone-50 transition">
                        <td class="px-6 py-4 font-medium text-stone-700">{{ $p[0] }}</td>
                        <td class="px-6 py-4 font-bold text-stone-800">{{ $p[1] }}</td>
                        <td class="px-6 py-4 text-stone-500 text-xs">{{ $p[2] }}</td>
                        <td class="px-6 py-4 text-stone-400 text-xs">{{ $p[3] }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                {{ $p[4] }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <button class="inline-flex items-center gap-1.5 text-xs text-amber-600 hover:text-amber-700 font-medium border border-amber-200 hover:border-amber-400 px-3 py-1.5 rounded-lg transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Download
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
