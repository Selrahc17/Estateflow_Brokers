@extends('layouts.app')
@section('title', 'Payments')
@section('page-title', 'Payment Monitoring')
@section('page-subtitle', 'Track and follow up on client payments')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach([['Total Collected','₱12.4M','green'],['This Month','₱1.8M','blue'],['Pending','₱540K','yellow'],['Overdue','₱210K','red']] as $s)
    <div class="bg-white rounded-xl border border-stone-200 p-4">
        <p class="text-xs text-stone-500 mb-1">{{ $s[0] }}</p>
        <p class="text-xl font-bold text-stone-800">{{ $s[1] }}</p>
    </div>
    @endforeach
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
        <h2 class="font-semibold text-stone-800">Payment Records</h2>
        <button class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Record Payment</button>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-200">
            <tr class="text-left text-stone-500">
                <th class="px-5 py-3 font-medium">Client</th>
                <th class="px-5 py-3 font-medium">Lot</th>
                <th class="px-5 py-3 font-medium">Amount</th>
                <th class="px-5 py-3 font-medium">Type</th>
                <th class="px-5 py-3 font-medium">Due Date</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @foreach([
                ['Juan dela Cruz','Lot 12-B','₱50,000','Monthly','Jul 15, 2025','Paid','green'],
                ['Maria Santos','Lot 5-A','₱45,000','Monthly','Jul 10, 2025','Pending','yellow'],
                ['Pedro Reyes','Lot 3-C','₱60,000','Quarterly','Jul 20, 2025','Paid','green'],
                ['Ana Lim','Lot 8-D','₱48,000','Monthly','Jun 30, 2025','Overdue','red'],
                ['Carlos Tan','Lot 2-A','₱42,000','Monthly','Jul 5, 2025','Paid','green'],
            ] as $r)
            <tr class="hover:bg-stone-50 transition">
                <td class="px-5 py-3 font-medium text-stone-700">{{ $r[0] }}</td>
                <td class="px-5 py-3 text-stone-500">{{ $r[1] }}</td>
                <td class="px-5 py-3 font-medium text-stone-700">{{ $r[2] }}</td>
                <td class="px-5 py-3 text-stone-500">{{ $r[3] }}</td>
                <td class="px-5 py-3 text-stone-400 text-xs">{{ $r[4] }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $r[6]==='green' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $r[6]==='yellow' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $r[6]==='red' ? 'bg-red-100 text-red-700' : '' }}">
                        {{ $r[5] }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <button class="text-xs text-amber-600 hover:underline">Follow Up</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
