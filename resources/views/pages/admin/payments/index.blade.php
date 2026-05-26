@extends('layouts.admin')
@section('title', 'Payments')
@section('page-title', 'Payment Management')
@section('page-subtitle', 'Monitor all payment transactions')

@section('content')

@php
    $totalRevenue = \App\Models\Payment::where('status','verified')->sum('amount');
    $pendingTotal = \App\Models\Payment::where('status','pending')->sum('amount');
@endphp

<div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-stone-200 p-4">
        <p class="text-xs text-stone-500 mb-1">Total Collected</p>
        <p class="text-xl font-bold text-green-600">₱{{ number_format($totalRevenue, 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-stone-200 p-4">
        <p class="text-xs text-stone-500 mb-1">Total Payments</p>
        <p class="text-xl font-bold text-stone-800">{{ $payments->total() }}</p>
    </div>
    <div class="bg-white rounded-xl border border-stone-200 p-4">
        <p class="text-xs text-stone-500 mb-1">Pending Amount</p>
        <p class="text-xl font-bold text-amber-600">₱{{ number_format($pendingTotal, 2) }}</p>
    </div>
</div>

<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-200">
            <tr class="text-left text-stone-500">
                <th class="px-5 py-3 font-medium">Code</th>
                <th class="px-5 py-3 font-medium">Client</th>
                <th class="px-5 py-3 font-medium">Broker</th>
                <th class="px-5 py-3 font-medium">Amount</th>
                <th class="px-5 py-3 font-medium">Type</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse($payments as $payment)
            <tr class="hover:bg-stone-50 transition">
                <td class="px-5 py-3 font-mono text-xs text-stone-500">{{ $payment->payment_code }}</td>
                <td class="px-5 py-3 font-medium text-stone-700">{{ $payment->client?->full_name ?? '—' }}</td>
                <td class="px-5 py-3 text-stone-500 text-xs">{{ $payment->broker?->name ?? '—' }}</td>
                <td class="px-5 py-3 font-semibold text-stone-700">₱{{ number_format($payment->amount, 2) }}</td>
                <td class="px-5 py-3 text-stone-500 text-xs">{{ ucfirst(str_replace('_',' ',$payment->payment_type)) }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $payment->status==='verified' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $payment->status==='pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $payment->status==='failed' ? 'bg-red-100 text-red-600' : '' }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-stone-400 text-xs">{{ $payment->created_at->format('M d, Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-10 text-center text-stone-400">No payments found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-5 py-4 border-t border-stone-100">{{ $payments->withQueryString()->links() }}</div>
</div>

@endsection
