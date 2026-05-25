@extends('layouts.admin')
@section('title', 'Audit Logs')
@section('page-title', 'Audit Logs')
@section('page-subtitle', 'Track all system activities and user actions')

@section('content')

{{-- Filters --}}
<div class="flex flex-wrap gap-3 justify-between mb-5">
    <div class="flex gap-2 flex-wrap">
        <input type="text" placeholder="Search logs..." class="border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 w-56">
        <select class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            <option>All Actions</option>
            <option>Login</option>
            <option>Reservation</option>
            <option>Payment</option>
            <option>Document</option>
            <option>User Management</option>
        </select>
        <select class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            <option>All Roles</option>
            <option>Admin</option>
            <option>Broker</option>
            <option>Client</option>
        </select>
        <input type="date" class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
    </div>
    <button class="border border-stone-200 text-stone-600 hover:bg-stone-50 px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Export Logs
    </button>
</div>

{{-- Logs Table --}}
<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-200">
            <tr class="text-left text-stone-500">
                <th class="px-5 py-3 font-medium">Timestamp</th>
                <th class="px-5 py-3 font-medium">User</th>
                <th class="px-5 py-3 font-medium">Role</th>
                <th class="px-5 py-3 font-medium">Action</th>
                <th class="px-5 py-3 font-medium">Details</th>
                <th class="px-5 py-3 font-medium">IP Address</th>
                <th class="px-5 py-3 font-medium">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @foreach([
                ['Jul 10, 2025 14:32:05','Super Admin','Admin','Approved Reservation','Reservation #342 — Juan dela Cruz','192.168.1.1','Success','green'],
                ['Jul 10, 2025 14:28:12','Broker Santos','Broker','Updated Client','Updated profile of Maria Santos','192.168.1.45','Success','green'],
                ['Jul 10, 2025 14:15:33','Juan dela Cruz','Client','Submitted Document','Uploaded Valid ID for verification','192.168.2.10','Success','green'],
                ['Jul 10, 2025 13:55:20','Super Admin','Admin','Suspended User','Suspended account of Ana Lim','192.168.1.1','Success','green'],
                ['Jul 10, 2025 13:42:08','Broker Reyes','Broker','Login','Logged in to broker dashboard','192.168.1.78','Success','green'],
                ['Jul 10, 2025 13:30:45','Unknown','—','Failed Login','3 failed login attempts','203.45.67.89','Failed','red'],
                ['Jul 10, 2025 13:15:22','Super Admin','Admin','Approved Broker','Approved broker application — Broker Garcia','192.168.1.1','Success','green'],
                ['Jul 10, 2025 12:58:11','Maria Santos','Client','Payment Submitted','Submitted payment receipt for July 2025','192.168.2.55','Success','green'],
                ['Jul 10, 2025 12:45:30','Broker Lim','Broker','Created Reservation','New reservation for Pedro Reyes — Lot 3-C','192.168.1.92','Success','green'],
                ['Jul 10, 2025 12:30:00','Super Admin','Admin','System Settings','Updated notification email template','192.168.1.1','Success','green'],
            ] as $log)
            <tr class="hover:bg-stone-50 transition">
                <td class="px-5 py-3 text-stone-400 text-xs font-mono">{{ $log[0] }}</td>
                <td class="px-5 py-3 font-medium text-stone-700 text-xs">{{ $log[1] }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $log[2]==='Admin' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $log[2]==='Broker' ? 'bg-amber-100 text-amber-700' : '' }}
                        {{ $log[2]==='Client' ? 'bg-blue-100 text-blue-700' : '' }}
                        {{ $log[2]==='—' ? 'bg-stone-100 text-stone-500' : '' }}">
                        {{ $log[2] }}
                    </span>
                </td>
                <td class="px-5 py-3 text-stone-600 text-xs font-medium">{{ $log[3] }}</td>
                <td class="px-5 py-3 text-stone-400 text-xs max-w-xs truncate">{{ $log[4] }}</td>
                <td class="px-5 py-3 text-stone-400 text-xs font-mono">{{ $log[5] }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $log[7]==='green' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                        {{ $log[6] }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-5 py-4 border-t border-stone-100 flex items-center justify-between text-xs text-stone-500">
        <span>Showing 1–10 of 4,821 logs</span>
        <div class="flex gap-1">
            @foreach(['←','1','2','3','...','483','→'] as $p)
            <button class="w-7 h-7 rounded-lg {{ $p==='1' ? 'bg-red-600 text-white' : 'hover:bg-stone-100' }} flex items-center justify-center transition">{{ $p }}</button>
            @endforeach
        </div>
    </div>
</div>

@endsection
