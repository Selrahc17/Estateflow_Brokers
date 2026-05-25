@extends('layouts.admin')
@section('title', 'User Management')
@section('page-title', 'User Management')
@section('page-subtitle', 'Manage all brokers and clients in the system')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Total Users','1,342','text-stone-800'],
        ['Clients','1,290','text-blue-600'],
        ['Brokers','52','text-amber-600'],
        ['Suspended','8','text-red-500'],
    ] as $s)
    <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
        <p class="text-2xl font-bold {{ $s[2] }}">{{ $s[1] }}</p>
        <p class="text-xs text-stone-500 mt-1">{{ $s[0] }}</p>
    </div>
    @endforeach
</div>

{{-- Filters + Actions --}}
<div class="flex flex-col sm:flex-row gap-3 justify-between mb-5">
    <div class="flex gap-2 flex-wrap">
        <input type="text" placeholder="Search users..." class="border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 w-56">
        <select class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            <option>All Roles</option>
            <option>Client</option>
            <option>Broker</option>
            <option>Admin</option>
        </select>
        <select class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            <option>All Status</option>
            <option>Active</option>
            <option>Suspended</option>
            <option>Pending</option>
        </select>
    </div>
    <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add User
    </button>
</div>

{{-- Table --}}
<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-200">
            <tr class="text-left text-stone-500">
                <th class="px-5 py-3 font-medium">User</th>
                <th class="px-5 py-3 font-medium">Role</th>
                <th class="px-5 py-3 font-medium">Email</th>
                <th class="px-5 py-3 font-medium">Phone</th>
                <th class="px-5 py-3 font-medium">Joined</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @foreach([
                ['Juan dela Cruz','Client','juan@email.com','+63 912 345 6789','Jan 5, 2025','Active','green'],
                ['Maria Santos','Client','maria@email.com','+63 917 234 5678','Jan 8, 2025','Active','green'],
                ['Broker Santos','Broker','santos@email.com','+63 918 345 6789','Dec 1, 2024','Active','green'],
                ['Pedro Reyes','Client','pedro@email.com','+63 919 456 7890','Feb 3, 2025','Active','green'],
                ['Broker Reyes','Broker','reyes@email.com','+63 920 567 8901','Nov 15, 2024','Active','green'],
                ['Ana Lim','Client','ana@email.com','+63 921 678 9012','Mar 10, 2025','Suspended','red'],
                ['Carlos Tan','Client','carlos@email.com','+63 922 789 0123','Apr 2, 2025','Active','green'],
                ['Rosa Garcia','Client','rosa@email.com','+63 923 890 1234','Apr 15, 2025','Pending','yellow'],
            ] as $u)
            <tr class="hover:bg-stone-50 transition">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 {{ $u[1]==='Broker' ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' }} rounded-full flex items-center justify-center text-xs font-bold shrink-0">
                            {{ strtoupper(substr($u[0],0,1)) }}
                        </div>
                        <span class="font-medium text-stone-700">{{ $u[0] }}</span>
                    </div>
                </td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $u[1]==='Broker' ? 'bg-amber-100 text-amber-700' : ($u[1]==='Admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                        {{ $u[1] }}
                    </span>
                </td>
                <td class="px-5 py-3 text-stone-500 text-xs">{{ $u[2] }}</td>
                <td class="px-5 py-3 text-stone-500 text-xs">{{ $u[3] }}</td>
                <td class="px-5 py-3 text-stone-400 text-xs">{{ $u[4] }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $u[6]==='green' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $u[6]==='red' ? 'bg-red-100 text-red-600' : '' }}
                        {{ $u[6]==='yellow' ? 'bg-yellow-100 text-yellow-700' : '' }}">
                        {{ $u[5] }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex gap-2">
                        <button class="text-xs text-blue-600 hover:underline">View</button>
                        <button class="text-xs text-amber-600 hover:underline">Edit</button>
                        <button class="text-xs text-red-500 hover:underline">{{ $u[5]==='Suspended' ? 'Activate' : 'Suspend' }}</button>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{-- Pagination --}}
    <div class="px-5 py-4 border-t border-stone-100 flex items-center justify-between text-xs text-stone-500">
        <span>Showing 1–8 of 1,342 users</span>
        <div class="flex gap-1">
            @foreach(['←','1','2','3','...','168','→'] as $p)
            <button class="w-7 h-7 rounded-lg {{ $p==='1' ? 'bg-red-600 text-white' : 'hover:bg-stone-100' }} flex items-center justify-center transition">{{ $p }}</button>
            @endforeach
        </div>
    </div>
</div>

@endsection
