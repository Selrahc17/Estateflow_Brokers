@extends('layouts.public')
@section('title', 'My Payments')

@section('content')

<div class="bg-gradient-to-r from-stone-900 to-amber-900 text-white">
    <div class="max-w-6xl mx-auto px-6 py-10">
        <p class="text-amber-400 text-xs uppercase tracking-widest font-semibold mb-1">My Payments</p>
        <h1 class="text-2xl font-bold">Payment History</h1>
    </div>
</div>

<div class="max-w-6xl mx-auto px-6 py-8 space-y-6">

    @php
        $totalPaid = $payments->getCollection()->where('status','verified')->sum('amount');
        $pendingCount = $payments->getCollection()->where('status','pending')->count();
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-green-100 p-5">
            <p class="text-xs text-stone-500 mb-2">Total Verified</p>
            <p class="text-xl font-bold text-green-600">₱{{ number_format($totalPaid, 2) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-stone-200 p-5">
            <p class="text-xs text-stone-500 mb-2">Total Payments</p>
            <p class="text-xl font-bold text-stone-800">{{ $payments->total() }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-5">
            <p class="text-xs text-stone-500 mb-2">Pending Verification</p>
            <p class="text-xl font-bold text-amber-600">{{ $pendingCount }}</p>
        </div>
    </div>

    <div class="flex justify-end">
        <a href="{{ route('client.account.payments.pay') }}" class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Make a Payment
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 border-b border-stone-100">
                    <tr class="text-left text-stone-500">
                        <th class="px-6 py-3 font-medium">Code</th>
                        <th class="px-6 py-3 font-medium">Amount</th>
                        <th class="px-6 py-3 font-medium">Type</th>
                        <th class="px-6 py-3 font-medium">Method</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                        <th class="px-6 py-3 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($payments as $payment)
                    <tr class="hover:bg-stone-50 transition">
                        <td class="px-6 py-4 font-mono text-xs text-stone-500">{{ $payment->payment_code }}</td>
                        <td class="px-6 py-4 font-bold text-stone-800">₱{{ number_format($payment->amount, 2) }}</td>
                        <td class="px-6 py-4 text-stone-500 text-xs">{{ ucfirst(str_replace('_',' ',$payment->payment_type)) }}</td>
                        <td class="px-6 py-4 text-stone-500 text-xs">{{ $payment->payment_method ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ $payment->status==='verified' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $payment->status==='pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $payment->status==='failed' ? 'bg-red-100 text-red-600' : '' }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-stone-400 text-xs">{{ $payment->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-6 py-12 text-center text-stone-400">No payments yet. <a href="{{ route('client.account.payments.pay') }}" class="text-amber-600 hover:underline">Make your first payment</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-stone-100">{{ $payments->links() }}</div>
    </div>

</div>

@endsection
