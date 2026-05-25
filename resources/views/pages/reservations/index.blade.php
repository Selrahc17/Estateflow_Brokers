@extends('layouts.app')
@section('title', 'Reservations')
@section('page-title', 'Reservations')
@section('page-subtitle', 'Manage all client reservations')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Total','23','stone'],
        ['Active','15','green'],
        ['Pending','5','yellow'],
        ['Cancelled','3','red'],
    ] as $s)
    <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
        <p class="text-2xl font-bold text-stone-800">{{ $s[1] }}</p>
        <p class="text-sm text-stone-500">{{ $s[0] }}</p>
    </div>
    @endforeach
</div>

{{-- Actions --}}
<div class="flex flex-col sm:flex-row gap-3 justify-between mb-5">
    <div class="flex gap-2">
        <input type="text" placeholder="Search client or lot..." class="border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 w-64">
        <select class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            <option>All Status</option>
            <option>Active</option>
            <option>Pending</option>
            <option>Cancelled</option>
        </select>
    </div>
    <button class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Reservation
    </button>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-200">
            <tr class="text-left text-stone-500">
                <th class="px-5 py-3 font-medium">Client</th>
                <th class="px-5 py-3 font-medium">Property</th>
                <th class="px-5 py-3 font-medium">Lot</th>
                <th class="px-5 py-3 font-medium">Amount</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Date</th>
                <th class="px-5 py-3 font-medium">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @foreach([
                ['Juan dela Cruz','Palm Residences','Lot 12-B','₱1,200,000','Active','Jul 1, 2025','green'],
                ['Maria Santos','Greenfield Villas','Lot 5-A','₱980,000','Pending','Jun 28, 2025','yellow'],
                ['Pedro Reyes','Sunrise Homes','Lot 3-C','₱1,500,000','Active','Jun 25, 2025','green'],
                ['Ana Lim','Palm Residences','Lot 8-D','₱1,100,000','Overdue','Jun 20, 2025','red'],
                ['Carlos Tan','Greenfield Villas','Lot 2-A','₱870,000','Active','Jun 18, 2025','green'],
                ['Rosa Garcia','Hillside Estates','Lot 7-B','₱1,350,000','Pending','Jun 15, 2025','yellow'],
                ['Ben Cruz','Sunrise Homes','Lot 11-A','₱1,200,000','Cancelled','Jun 10, 2025','stone'],
            ] as $r)
            <tr class="hover:bg-stone-50 transition">
                <td class="px-5 py-3 font-medium text-stone-700">{{ $r[0] }}</td>
                <td class="px-5 py-3 text-stone-500">{{ $r[1] }}</td>
                <td class="px-5 py-3 text-stone-500">{{ $r[2] }}</td>
                <td class="px-5 py-3 font-medium text-stone-700">{{ $r[3] }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $r[6]==='green' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $r[6]==='yellow' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $r[6]==='red' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $r[6]==='stone' ? 'bg-stone-100 text-stone-500' : '' }}">
                        {{ $r[4] }}
                    </span>
                </td>
                <td class="px-5 py-3 text-stone-400 text-xs">{{ $r[5] }}</td>
                <td class="px-5 py-3">
                    <div class="flex gap-2">
                        <button class="text-xs text-amber-600 hover:underline">View</button>
                        <button class="text-xs text-stone-400 hover:underline">Edit</button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
