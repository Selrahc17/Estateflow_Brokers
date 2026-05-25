@extends('layouts.admin')
@section('title', 'Broker Management')
@section('page-title', 'Broker Management')
@section('page-subtitle', 'Manage and verify licensed brokers and agents')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Total Brokers','52','text-stone-800'],
        ['Active','48','text-green-600'],
        ['Pending Approval','2','text-amber-600'],
        ['Suspended','2','text-red-500'],
    ] as $s)
    <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
        <p class="text-2xl font-bold {{ $s[2] }}">{{ $s[1] }}</p>
        <p class="text-xs text-stone-500 mt-1">{{ $s[0] }}</p>
    </div>
    @endforeach
</div>

{{-- Pending Approval Banner --}}
<div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-5 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <p class="text-sm font-semibold text-amber-800">2 broker applications are pending your approval</p>
    </div>
    <a href="#pending" class="text-xs text-amber-700 font-semibold hover:underline">Review Now →</a>
</div>

{{-- Filters --}}
<div class="flex flex-col sm:flex-row gap-3 justify-between mb-5">
    <div class="flex gap-2">
        <input type="text" placeholder="Search brokers..." class="border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 w-56">
        <select class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            <option>All Status</option>
            <option>Active</option>
            <option>Pending</option>
            <option>Suspended</option>
        </select>
    </div>
    <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Broker
    </button>
</div>

{{-- Broker Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach([
        ['Broker Santos','santos@email.com','+63 918 345 6789','PRC-2024-00123','Active','green','15 clients','₱3.2M'],
        ['Broker Reyes','reyes@email.com','+63 920 567 8901','PRC-2023-00456','Active','green','12 clients','₱2.8M'],
        ['Broker Lim','lim@email.com','+63 921 678 9012','PRC-2024-00789','Active','green','8 clients','₱1.9M'],
        ['Broker Cruz','cruz@email.com','+63 922 789 0123','PRC-2022-00321','Active','green','20 clients','₱4.1M'],
        ['Broker Garcia','garcia@email.com','+63 923 890 1234','PRC-2025-00654','Pending','yellow','0 clients','₱0'],
        ['Broker Torres','torres@email.com','+63 924 901 2345','PRC-2025-00987','Pending','yellow','0 clients','₱0'],
    ] as $b)
    <div class="bg-white rounded-xl border border-stone-200 p-5 hover:shadow-md transition">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center text-amber-700 font-bold text-lg shrink-0">
                    {{ strtoupper(substr($b[0], 7, 1)) }}
                </div>
                <div>
                    <p class="font-semibold text-stone-800">{{ $b[0] }}</p>
                    <p class="text-xs text-stone-400">{{ $b[3] }}</p>
                </div>
            </div>
            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $b[5]==='green' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                {{ $b[4] }}
            </span>
        </div>
        <div class="space-y-1.5 text-xs text-stone-500 mb-4">
            <p class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>{{ $b[1] }}</p>
            <p class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>{{ $b[2] }}</p>
        </div>
        <div class="flex justify-between text-xs text-stone-500 pt-3 border-t border-stone-100 mb-3">
            <span>{{ $b[6] }}</span>
            <span class="font-semibold text-green-600">{{ $b[7] }} revenue</span>
        </div>
        @if($b[4] === 'Pending')
        <div class="flex gap-2">
            <button class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-xs font-semibold transition">Approve</button>
            <button class="flex-1 bg-red-100 hover:bg-red-200 text-red-600 py-2 rounded-lg text-xs font-semibold transition">Reject</button>
        </div>
        @else
        <div class="flex gap-2">
            <button class="flex-1 border border-stone-200 hover:bg-stone-50 text-stone-600 py-2 rounded-lg text-xs font-medium transition">View Profile</button>
            <button class="flex-1 border border-red-200 hover:bg-red-50 text-red-500 py-2 rounded-lg text-xs font-medium transition">Suspend</button>
        </div>
        @endif
    </div>
    @endforeach
</div>

@endsection
