@extends('layouts.admin')
@section('title', 'Payments')
@section('page-title', 'Payment Management')
@section('page-subtitle', 'Monitor all payment transactions across the system')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Total Collected','₱12.4M','bg-green-50 border-green-100','text-green-600'],
        ['This Month','₱1.8M','bg-blue-50 border-blue-100','text-blue-600'],
        ['Pending','₱540K','bg-amber-50 border-amber-100','text-amber-600'],
        ['Overdue','₱210K','bg-red-50 border-red-100','text-red-500'],
    ] as $s)
    <div class="bg-white rounded-xl border {{ $s[2] }} p-5">
        <p class="text-xs text-stone-500 mb-1">{{ $s[0] }}</p>
        <p class="text-2xl font-bold {{ $s[3] }}">{{ $s[1] }}</p>
    </div>
    @endforeach
</div>

{{-- Monthly Chart --}}
<div class="bg-white rounded-xl border border-stone-200 p-5 mb-5">
    <div class="flex items-center justify-between mb-4">
        <h2 class="font-semibold text-stone-800">Monthly Revenue</h2>
        <select class="border border-stone-200 rounded-lg px-3 py-1.5 text-xs focus:outline-none">
            <option>2025</option><option>2024</option>
        </select>
    </div>
    <div class="flex items-end gap-2 h-32">
        @foreach([['Jan',8],['Feb',12],['Mar',10],['Apr',15],['May',14],['Jun',18],['Jul',16],['Aug',0],['Sep',0],['Oct',0],['Nov',0],['Dec',0]] as $m)
        <div class="flex-1 flex flex-col items-center gap-1">
            @if($m[1] > 0)
            <span class="text-xs text-stone-500">{{ $m[1] }}M</span>
            <div class="w-full bg-red-500 rounded-t-md hover:bg-red-600 transition cursor-pointer" style="height: {{ $m[1] * 7 }}px"></div>
            @else
            <span class="text-xs text-stone-300">—</span>
            <div class="w-full bg-stone-100 rounded-t-md" style="height: 8px"></div>
            @endif
            <span class="text-xs text-stone-400">{{ $m[0] }}</span>
        </div>
        @endforeach
    </div>
</div>

{{-- Filters + Table --}}
<div class="flex flex-wrap gap-3 justify-between mb-4">
    <div class="flex gap-2 flex-wrap">
        <input type="text" placeholder="Search client..." class="border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 w-48">
        <select class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            <option>All Status</option>
            <option>Paid</option>
            <option>Pending</option>
            <option>Overdue</option>
        </select>
    </div>
    <button class="border border-stone-200 text-stone-600 hover:bg-stone-50 px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Export CSV
    </button>
</div>

<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-200">
            <tr class="text-left text-stone-500">
                <th class="px-5 py-3 font-medium">Client</th>
                <th class="px-5 py-3 font-medium">Property</th>
                <th class="px-5 py-3 font-medium">Broker</th>
                <th class="px-5 py-3 font-medium">Amount</th>
                <th class="px-5 py-3 font-medium">Type</th>
                <th class="px-5 py-3 font-medium">Due Date</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @foreach([
                ['Juan dela Cruz','Palm Residences','Broker Santos','₱50,000','Monthly','Jul 15, 2025','Paid','green'],
                ['Maria Santos','Greenfield Villas','Broker Reyes','₱45,000','Monthly','Jul 10, 2025','Pending','yellow'],
                ['Pedro Reyes','Sunrise Homes','Broker Lim','₱60,000','Quarterly','Jul 20, 2025','Paid','green'],
                ['Ana Lim','Palm Residences','Broker Santos','₱48,000','Monthly','Jun 30, 2025','Overdue','red'],
                ['Carlos Tan','Greenfield Villas','Broker Reyes','₱42,000','Monthly','Jul 5, 2025','Paid','green'],
                ['Rosa Garcia','Hillside Estates','Broker Cruz','₱55,000','Monthly','Jul 12, 2025','Pending','yellow'],
            ] as $p)
            <tr class="hover:bg-stone-50 transition">
                <td class="px-5 py-3 font-medium text-stone-700">{{ $p[0] }}</td>
                <td class="px-5 py-3 text-stone-500 text-xs">{{ $p[1] }}</td>
                <td class="px-5 py-3 text-stone-500 text-xs">{{ $p[2] }}</td>
                <td class="px-5 py-3 font-semibold text-stone-700">{{ $p[3] }}</td>
                <td class="px-5 py-3 text-stone-500 text-xs">{{ $p[4] }}</td>
                <td class="px-5 py-3 text-stone-400 text-xs">{{ $p[5] }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $p[7]==='green' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $p[7]==='yellow' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $p[7]==='red' ? 'bg-red-100 text-red-600' : '' }}">
                        {{ $p[6] }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <button class="text-xs text-red-600 hover:underline">{{ $p[7]==='red' ? 'Follow Up' : 'View' }}</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
