@extends('layouts.admin')
@section('title', 'Reservations')
@section('page-title', 'Reservation Management')
@section('page-subtitle', 'Review and approve all client reservations')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Total','342','text-stone-800'],
        ['Pending Approval','5','text-amber-600'],
        ['Active','298','text-green-600'],
        ['Cancelled','39','text-red-500'],
    ] as $s)
    <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
        <p class="text-2xl font-bold {{ $s[2] }}">{{ $s[1] }}</p>
        <p class="text-xs text-stone-500 mt-1">{{ $s[0] }}</p>
    </div>
    @endforeach
</div>

{{-- Filters --}}
<div class="flex flex-wrap gap-3 justify-between mb-5">
    <div class="flex gap-2 flex-wrap">
        <input type="text" placeholder="Search client or lot..." class="border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 w-56">
        <select class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            <option>All Status</option>
            <option>Pending Approval</option>
            <option>Active</option>
            <option>Cancelled</option>
        </select>
        <select class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            <option>All Properties</option>
            <option>Palm Residences</option>
            <option>Greenfield Villas</option>
            <option>Sunrise Homes</option>
        </select>
    </div>
    <button class="border border-stone-200 text-stone-600 hover:bg-stone-50 px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Export
    </button>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-200">
            <tr class="text-left text-stone-500">
                <th class="px-5 py-3 font-medium">Client</th>
                <th class="px-5 py-3 font-medium">Property & Lot</th>
                <th class="px-5 py-3 font-medium">Broker</th>
                <th class="px-5 py-3 font-medium">Amount</th>
                <th class="px-5 py-3 font-medium">Date</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @foreach([
                ['Juan dela Cruz','Palm Residences — Lot 12-B','Broker Santos','₱1,200,000','Jul 1, 2025','Pending Approval','yellow'],
                ['Maria Santos','Greenfield Villas — Lot 5-A','Broker Reyes','₱980,000','Jun 28, 2025','Pending Approval','yellow'],
                ['Pedro Reyes','Sunrise Homes — Lot 3-C','Broker Lim','₱1,500,000','Jun 25, 2025','Active','green'],
                ['Ana Lim','Palm Residences — Lot 8-D','Broker Santos','₱1,100,000','Jun 20, 2025','Active','green'],
                ['Carlos Tan','Greenfield Villas — Lot 2-A','Broker Reyes','₱870,000','Jun 18, 2025','Active','green'],
                ['Rosa Garcia','Hillside Estates — Lot 7-B','Broker Cruz','₱1,350,000','Jun 15, 2025','Pending Approval','yellow'],
                ['Ben Cruz','Sunrise Homes — Lot 11-A','Broker Lim','₱1,200,000','Jun 10, 2025','Cancelled','red'],
                ['Lea Ramos','Metro Gardens — Lot 4-C','Broker Cruz','₱2,100,000','Jun 5, 2025','Active','green'],
            ] as $r)
            <tr class="hover:bg-stone-50 transition">
                <td class="px-5 py-3 font-medium text-stone-700">{{ $r[0] }}</td>
                <td class="px-5 py-3 text-stone-500 text-xs">{{ $r[1] }}</td>
                <td class="px-5 py-3 text-stone-500 text-xs">{{ $r[2] }}</td>
                <td class="px-5 py-3 font-semibold text-stone-700">{{ $r[3] }}</td>
                <td class="px-5 py-3 text-stone-400 text-xs">{{ $r[4] }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $r[6]==='green' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $r[6]==='yellow' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $r[6]==='red' ? 'bg-red-100 text-red-600' : '' }}">
                        {{ $r[5] }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex gap-2">
                        @if($r[6] === 'yellow')
                        <button class="text-xs bg-green-100 text-green-700 hover:bg-green-200 px-2.5 py-1 rounded-lg font-medium transition">Approve</button>
                        <button class="text-xs bg-red-100 text-red-600 hover:bg-red-200 px-2.5 py-1 rounded-lg font-medium transition">Reject</button>
                        @else
                        <button class="text-xs text-blue-600 hover:underline">View</button>
                        <button class="text-xs text-stone-400 hover:underline">Edit</button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
