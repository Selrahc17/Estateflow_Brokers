@extends('layouts.public')
@section('title', 'Payment Successful')

@section('content')

<div class="max-w-lg mx-auto px-6 py-16 text-center">

    {{-- Success Animation --}}
    <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
    </div>

    <h1 class="text-2xl font-bold text-stone-800 mb-2">Payment Successful! 🎉</h1>
    <p class="text-stone-400 text-sm mb-8">Your payment has been received and confirmed. A receipt has been sent to your email.</p>

    {{-- Receipt Card --}}
    <div class="bg-white rounded-2xl border border-stone-200 p-6 text-left mb-6">
        <div class="flex items-center justify-between mb-4 pb-4 border-b border-stone-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-amber-600 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
                <span class="font-bold text-stone-800">EstateFlow</span>
            </div>
            <span class="text-xs bg-green-100 text-green-700 px-3 py-1 rounded-full font-semibold">Paid</span>
        </div>
        <div class="space-y-3 text-sm">
            @foreach([
                ['Receipt No.','EF-2025-07-001234'],
                ['Date','July 10, 2025 · 2:34 PM'],
                ['Property','Palm Residences'],
                ['Lot','12-B, Block 1'],
                ['Payment Type','Monthly Installment — July 2025'],
                ['Payment Method','Credit Card (•••• 3456)'],
            ] as $row)
            <div class="flex justify-between">
                <span class="text-stone-400">{{ $row[0] }}</span>
                <span class="font-medium text-stone-700">{{ $row[1] }}</span>
            </div>
            @endforeach
            <div class="border-t border-stone-100 pt-3 flex justify-between">
                <span class="font-bold text-stone-800">Amount Paid</span>
                <span class="text-xl font-bold text-green-600">₱50,000</span>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <button class="flex-1 flex items-center justify-center gap-2 border border-stone-200 text-stone-600 hover:bg-stone-50 py-3 rounded-xl text-sm font-semibold transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download Receipt
        </button>
        <a href="{{ route('client.account.payments') }}" class="flex-1 bg-amber-600 hover:bg-amber-700 text-white py-3 rounded-xl text-sm font-semibold transition text-center">
            View Payment History
        </a>
    </div>

    <a href="{{ route('client.account.home') }}" class="block mt-4 text-sm text-stone-400 hover:text-amber-600 transition">
        ← Back to My Account
    </a>

</div>

@endsection
