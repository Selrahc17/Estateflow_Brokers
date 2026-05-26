@extends('layouts.app')
@section('title', 'Payments')
@section('page-title', 'Payment Monitoring')
@section('page-subtitle', 'Track and verify client payments')

@section('content')

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

@php
    $brokerId = auth()->id();
    $totalRevenue = \App\Models\Payment::where('broker_id',$brokerId)->where('status','verified')->sum('amount');
    $pendingCount = \App\Models\Payment::where('broker_id',$brokerId)->where('status','pending')->count();
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
        <p class="text-xs text-stone-500 mb-1">Pending Verification</p>
        <p class="text-xl font-bold text-amber-600">{{ $pendingCount }}</p>
    </div>
</div>

<div class="flex flex-wrap gap-3 justify-between mb-4">
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search client..." class="border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 w-48">
        <select name="status" class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" onchange="this.form.submit()">
            <option value="">All Status</option>
            @foreach(['pending','verified','failed'] as $s)
            <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-200">
            <tr class="text-left text-stone-500">
                <th class="px-5 py-3 font-medium">Code</th>
                <th class="px-5 py-3 font-medium">Client</th>
                <th class="px-5 py-3 font-medium">Amount</th>
                <th class="px-5 py-3 font-medium">Type</th>
                <th class="px-5 py-3 font-medium">Method</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Date</th>
                <th class="px-5 py-3 font-medium">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse($payments as $payment)
            <tr class="hover:bg-stone-50 transition">
                <td class="px-5 py-3 font-mono text-xs text-stone-500">{{ $payment->payment_code }}</td>
                <td class="px-5 py-3 font-medium text-stone-700">{{ $payment->client?->full_name ?? '—' }}</td>
                <td class="px-5 py-3 font-semibold text-stone-700">₱{{ number_format($payment->amount, 2) }}</td>
                <td class="px-5 py-3 text-stone-500 text-xs">{{ ucfirst(str_replace('_',' ',$payment->payment_type)) }}</td>
                <td class="px-5 py-3 text-stone-500 text-xs">{{ $payment->payment_method ?? '—' }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $payment->status==='verified' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $payment->status==='pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $payment->status==='failed' ? 'bg-red-100 text-red-600' : '' }}">
                        {{ ucfirst($payment->status) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-stone-400 text-xs">{{ $payment->created_at->format('M d, Y') }}</td>
                <td class="px-5 py-3">
                    @if($payment->status === 'pending')
                    <div class="flex gap-2">
                        <form action="{{ route('broker.payments.verify', $payment) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs bg-green-100 text-green-700 hover:bg-green-200 px-2.5 py-1 rounded-lg font-medium transition">Verify</button>
                        </form>
                        <form action="{{ route('broker.payments.reject', $payment) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs bg-red-100 text-red-600 hover:bg-red-200 px-2.5 py-1 rounded-lg font-medium transition">Reject</button>
                        </form>
                    </div>
                    @else
                    <span class="text-xs text-stone-400">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="px-5 py-10 text-center text-stone-400">No payments recorded yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-5 py-4 border-t border-stone-100">{{ $payments->withQueryString()->links() }}</div>
</div>

@endsection
