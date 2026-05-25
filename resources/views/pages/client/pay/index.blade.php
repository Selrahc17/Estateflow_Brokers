@extends('layouts.public')
@section('title', 'Pay Now')

@section('content')

<div class="bg-gradient-to-r from-stone-900 to-amber-900 text-white">
    <div class="max-w-4xl mx-auto px-6 py-10">
        <p class="text-amber-400 text-xs uppercase tracking-widest font-semibold mb-1">Payments</p>
        <h1 class="text-2xl font-bold">Pay Your Installment</h1>
        <p class="text-stone-300 text-sm mt-1">Palm Residences — Lot 12-B · July 2025 Installment</p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-6 py-8">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Payment Form --}}
        <div class="lg:col-span-2 space-y-5" x-data="{ method: 'card' }">

            {{-- Payment Summary --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <h2 class="font-semibold text-stone-800 mb-4">Payment Summary</h2>
                <div class="space-y-3">
                    @foreach([
                        ['Property','Palm Residences'],
                        ['Lot','12-B, Block 1'],
                        ['Payment Type','Monthly Installment'],
                        ['Period','July 2025'],
                        ['Due Date','July 15, 2025'],
                    ] as $row)
                    <div class="flex justify-between text-sm">
                        <span class="text-stone-400">{{ $row[0] }}</span>
                        <span class="font-medium text-stone-700">{{ $row[1] }}</span>
                    </div>
                    @endforeach
                    <div class="border-t border-stone-100 pt-3 flex justify-between">
                        <span class="font-semibold text-stone-800">Amount Due</span>
                        <span class="text-xl font-bold text-amber-600">₱50,000</span>
                    </div>
                </div>
            </div>

            {{-- Payment Method --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <h2 class="font-semibold text-stone-800 mb-4">Select Payment Method</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
                    @foreach([
                        ['card','💳','Credit/Debit Card'],
                        ['gcash','📱','GCash'],
                        ['maya','🔵','Maya'],
                        ['bank','🏦','Bank Transfer'],
                    ] as $m)
                    <button @click="method = '{{ $m[0] }}'"
                        :class="method === '{{ $m[0] }}' ? 'border-amber-500 bg-amber-50 text-amber-700' : 'border-stone-200 text-stone-600 hover:border-amber-300'"
                        class="flex flex-col items-center gap-1.5 p-3 rounded-xl border-2 transition text-center">
                        <span class="text-2xl">{{ $m[1] }}</span>
                        <span class="text-xs font-medium">{{ $m[2] }}</span>
                    </button>
                    @endforeach
                </div>

                {{-- Card Form --}}
                <div x-show="method === 'card'" class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Card Number</label>
                        <input type="text" placeholder="1234 5678 9012 3456" maxlength="19"
                            class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Expiry Date</label>
                            <input type="text" placeholder="MM / YY" maxlength="7"
                                class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">CVV</label>
                            <input type="password" placeholder="•••" maxlength="4"
                                class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Cardholder Name</label>
                        <input type="text" placeholder="JUAN DELA CRUZ"
                            class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 uppercase">
                    </div>
                </div>

                {{-- GCash --}}
                <div x-show="method === 'gcash'" class="text-center py-6">
                    <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <span class="text-3xl">📱</span>
                    </div>
                    <p class="font-semibold text-stone-800 mb-1">Pay via GCash</p>
                    <p class="text-sm text-stone-400 mb-4">You will be redirected to GCash to complete your payment.</p>
                    <div class="bg-stone-50 rounded-xl p-4 text-left space-y-2 text-sm text-stone-600 mb-4">
                        <p class="flex justify-between"><span class="text-stone-400">GCash Number</span><span class="font-semibold">0912-345-6789</span></p>
                        <p class="flex justify-between"><span class="text-stone-400">Account Name</span><span class="font-semibold">EstateFlow Inc.</span></p>
                        <p class="flex justify-between"><span class="text-stone-400">Amount</span><span class="font-bold text-amber-600">₱50,000</span></p>
                    </div>
                    <p class="text-xs text-stone-400">After payment, upload your GCash receipt below.</p>
                </div>

                {{-- Maya --}}
                <div x-show="method === 'maya'" class="text-center py-6">
                    <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <span class="text-3xl">🔵</span>
                    </div>
                    <p class="font-semibold text-stone-800 mb-1">Pay via Maya</p>
                    <p class="text-sm text-stone-400 mb-4">You will be redirected to Maya to complete your payment.</p>
                    <div class="bg-stone-50 rounded-xl p-4 text-left space-y-2 text-sm text-stone-600">
                        <p class="flex justify-between"><span class="text-stone-400">Maya Number</span><span class="font-semibold">0917-234-5678</span></p>
                        <p class="flex justify-between"><span class="text-stone-400">Account Name</span><span class="font-semibold">EstateFlow Inc.</span></p>
                        <p class="flex justify-between"><span class="text-stone-400">Amount</span><span class="font-bold text-amber-600">₱50,000</span></p>
                    </div>
                </div>

                {{-- Bank Transfer --}}
                <div x-show="method === 'bank'" class="space-y-4">
                    <div class="bg-stone-50 rounded-xl p-4 space-y-2 text-sm text-stone-600">
                        @foreach([
                            ['Bank','BDO Unibank'],
                            ['Account Name','EstateFlow Inc.'],
                            ['Account Number','1234-5678-9012'],
                            ['Branch','Quezon City Main'],
                            ['Amount','₱50,000'],
                        ] as $b)
                        <div class="flex justify-between">
                            <span class="text-stone-400">{{ $b[0] }}</span>
                            <span class="font-semibold {{ $b[0]==='Amount' ? 'text-amber-600' : '' }}">{{ $b[1] }}</span>
                        </div>
                        @endforeach
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Upload Deposit Slip / Receipt</label>
                        <div class="border-2 border-dashed border-stone-200 hover:border-amber-400 rounded-xl p-6 text-center cursor-pointer transition">
                            <svg class="w-8 h-8 text-stone-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                            <p class="text-sm text-stone-400">Click to upload receipt</p>
                            <p class="text-xs text-stone-300 mt-1">JPG, PNG, PDF up to 5MB</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <a href="{{ route('client.account.payments.success') }}"
                class="block w-full text-center bg-amber-600 hover:bg-amber-700 text-white py-4 rounded-2xl font-bold text-base transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Confirm Payment — ₱50,000
            </a>

            <p class="text-center text-xs text-stone-400 flex items-center justify-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Secured with SSL encryption
            </p>

        </div>

        {{-- Right: Summary --}}
        <div class="space-y-5">

            {{-- Payment Progress --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <h3 class="font-semibold text-stone-800 mb-4">Payment Progress</h3>
                <div class="text-center mb-3">
                    <p class="text-3xl font-bold text-amber-600">25%</p>
                    <p class="text-xs text-stone-400 mt-0.5">of total amount paid</p>
                </div>
                <div class="w-full bg-stone-100 rounded-full h-2.5 mb-3 overflow-hidden">
                    <div class="bg-amber-500 h-2.5 rounded-full" style="width:25%"></div>
                </div>
                <div class="space-y-2 text-xs text-stone-500">
                    <div class="flex justify-between"><span>Paid</span><span class="font-semibold text-green-600">₱300,000</span></div>
                    <div class="flex justify-between"><span>This Payment</span><span class="font-semibold text-amber-600">₱50,000</span></div>
                    <div class="flex justify-between border-t border-stone-100 pt-2"><span>Remaining After</span><span class="font-semibold text-stone-700">₱850,000</span></div>
                </div>
            </div>

            {{-- Security Badges --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <h3 class="font-semibold text-stone-800 mb-3 text-sm">Secure Payment</h3>
                <div class="space-y-2">
                    @foreach(['SSL 256-bit Encryption','PCI DSS Compliant','Verified by EstateFlow','Official Receipt Provided'] as $badge)
                    <div class="flex items-center gap-2 text-xs text-stone-600">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        {{ $badge }}
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Need Help --}}
            <div class="bg-gradient-to-br from-stone-800 to-amber-900 rounded-2xl p-5 text-white">
                <p class="font-semibold text-sm mb-1">Need help with payment?</p>
                <p class="text-stone-300 text-xs mb-3">Contact your broker or our support team.</p>
                <a href="{{ route('client.contact') }}" class="block w-full text-center bg-amber-600 hover:bg-amber-700 py-2.5 rounded-xl text-sm font-semibold transition">
                    Contact Support
                </a>
            </div>

        </div>
    </div>
</div>

@endsection
