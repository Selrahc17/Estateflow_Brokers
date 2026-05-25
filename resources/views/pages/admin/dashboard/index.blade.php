@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'System-wide overview and key metrics')

@section('content')

{{-- Stats Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-5 mb-6">
    @foreach([
        ['Total Users','1,342','↑ 24 this month','bg-blue-50 border-blue-100','text-blue-600','M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['Active Brokers','52','↑ 3 new this week','bg-amber-50 border-amber-100','text-amber-600','M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ['Total Revenue','₱12.4M','↑ 18% vs last month','bg-green-50 border-green-100','text-green-600','M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
        ['Pending Actions','8','Needs attention','bg-red-50 border-red-100','text-red-600','M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
    ] as $s)
    <div class="bg-white rounded-xl border {{ $s[3] }} p-5 flex items-center gap-4">
        <div class="w-12 h-12 {{ $s[3] }} rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 {{ $s[4] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $s[5] }}"/></svg>
        </div>
        <div>
            <p class="text-xs text-stone-500">{{ $s[0] }}</p>
            <p class="text-2xl font-bold text-stone-800">{{ $s[1] }}</p>
            <p class="text-xs {{ $s[4] }}">{{ $s[2] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- Pending Actions --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-6">

    {{-- Pending Reservations --}}
    <div class="xl:col-span-2 bg-white rounded-xl border border-stone-200">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
            <h2 class="font-semibold text-stone-800">Pending Reservations</h2>
            <a href="{{ route('admin.reservations') }}" class="text-xs text-red-600 hover:underline">View all →</a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-stone-100">
                <tr class="text-left text-stone-500">
                    <th class="px-5 py-3 font-medium">Client</th>
                    <th class="px-5 py-3 font-medium">Property</th>
                    <th class="px-5 py-3 font-medium">Broker</th>
                    <th class="px-5 py-3 font-medium">Date</th>
                    <th class="px-5 py-3 font-medium">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach([
                    ['Juan dela Cruz','Palm Residences — Lot 12-B','Broker Santos','Jul 1, 2025'],
                    ['Maria Santos','Greenfield Villas — Lot 5-A','Broker Reyes','Jun 28, 2025'],
                    ['Pedro Reyes','Sunrise Homes — Lot 3-C','Broker Lim','Jun 25, 2025'],
                    ['Ana Lim','Hillside Estates — Lot 7-B','Broker Cruz','Jun 22, 2025'],
                    ['Carlos Tan','Metro Gardens — Lot 2-A','Broker Santos','Jun 20, 2025'],
                ] as $r)
                <tr class="hover:bg-stone-50 transition">
                    <td class="px-5 py-3 font-medium text-stone-700">{{ $r[0] }}</td>
                    <td class="px-5 py-3 text-stone-500 text-xs">{{ $r[1] }}</td>
                    <td class="px-5 py-3 text-stone-500 text-xs">{{ $r[2] }}</td>
                    <td class="px-5 py-3 text-stone-400 text-xs">{{ $r[3] }}</td>
                    <td class="px-5 py-3">
                        <div class="flex gap-2">
                            <button class="text-xs bg-green-100 text-green-700 hover:bg-green-200 px-2.5 py-1 rounded-lg font-medium transition">Approve</button>
                            <button class="text-xs bg-red-100 text-red-600 hover:bg-red-200 px-2.5 py-1 rounded-lg font-medium transition">Reject</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Quick Stats + Alerts --}}
    <div class="space-y-5">

        {{-- System Alerts --}}
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-800 mb-4">System Alerts</h2>
            <div class="space-y-3">
                @foreach([
                    ['3 Documents Pending Verification','red','admin.documents'],
                    ['5 Reservations Awaiting Approval','yellow','admin.reservations'],
                    ['2 New Broker Applications','blue','admin.brokers'],
                    ['7 Overdue Payments','red','admin.payments'],
                    ['12 New Feedback Submissions','stone','admin.feedback'],
                ] as $alert)
                <a href="{{ route($alert[2]) }}" class="flex items-center gap-3 p-3 rounded-lg
                    {{ $alert[1]==='red' ? 'bg-red-50 hover:bg-red-100' : '' }}
                    {{ $alert[1]==='yellow' ? 'bg-amber-50 hover:bg-amber-100' : '' }}
                    {{ $alert[1]==='blue' ? 'bg-blue-50 hover:bg-blue-100' : '' }}
                    {{ $alert[1]==='stone' ? 'bg-stone-50 hover:bg-stone-100' : '' }}
                    transition">
                    <div class="w-2 h-2 rounded-full shrink-0
                        {{ $alert[1]==='red' ? 'bg-red-500' : '' }}
                        {{ $alert[1]==='yellow' ? 'bg-amber-500' : '' }}
                        {{ $alert[1]==='blue' ? 'bg-blue-500' : '' }}
                        {{ $alert[1]==='stone' ? 'bg-stone-400' : '' }}">
                    </div>
                    <p class="text-xs font-medium text-stone-700">{{ $alert[0] }}</p>
                </a>
                @endforeach
            </div>
        </div>

        {{-- Revenue Summary --}}
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-800 mb-4">Revenue Summary</h2>
            <div class="space-y-3">
                @foreach([
                    ['This Month','₱1.8M','green'],
                    ['Last Month','₱1.5M','stone'],
                    ['This Year','₱12.4M','green'],
                    ['Pending','₱540K','yellow'],
                ] as $rev)
                <div class="flex justify-between items-center">
                    <span class="text-sm text-stone-500">{{ $rev[0] }}</span>
                    <span class="text-sm font-bold {{ $rev[2]==='green' ? 'text-green-600' : ($rev[2]==='yellow' ? 'text-amber-600' : 'text-stone-700') }}">{{ $rev[1] }}</span>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

{{-- Bottom Row --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
    @foreach([
        ['Total Properties','128','Active listings','text-amber-600','bg-amber-50'],
        ['Total Reservations','342','All time','text-blue-600','bg-blue-50'],
        ['Client Satisfaction','4.8/5','Based on 128 reviews','text-green-600','bg-green-50'],
    ] as $stat)
    <div class="bg-white rounded-xl border border-stone-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 {{ $stat[4] }} rounded-xl flex items-center justify-center shrink-0">
            <p class="text-lg font-bold {{ $stat[2] }}">{{ substr($stat[1],0,2) }}</p>
        </div>
        <div>
            <p class="text-xs text-stone-500">{{ $stat[0] }}</p>
            <p class="text-xl font-bold text-stone-800">{{ $stat[1] }}</p>
            <p class="text-xs text-stone-400">{{ $stat[2] }}</p>
        </div>
    </div>
    @endforeach
</div>

@endsection
