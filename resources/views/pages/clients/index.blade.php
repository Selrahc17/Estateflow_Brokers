@extends('layouts.app')
@section('title', 'Clients')
@section('page-title', 'Client Management')
@section('page-subtitle', 'View and manage all your clients')

@section('content')

{{-- Actions --}}
<div class="flex flex-col sm:flex-row gap-3 justify-between mb-5">
    <div class="flex gap-2">
        <input type="text" placeholder="Search clients..." class="border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 w-64">
        <select class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            <option>All Clients</option>
            <option>With Reservation</option>
            <option>No Reservation</option>
        </select>
    </div>
    <button class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Client
    </button>
</div>

{{-- Client Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
    @foreach([
        ['Juan dela Cruz','juan@email.com','+63 912 345 6789','Palm Residences','Active','Jul 1, 2025'],
        ['Maria Santos','maria@email.com','+63 917 234 5678','Greenfield Villas','Pending','Jun 28, 2025'],
        ['Pedro Reyes','pedro@email.com','+63 918 345 6789','Sunrise Homes','Active','Jun 25, 2025'],
        ['Ana Lim','ana@email.com','+63 919 456 7890','Palm Residences','Overdue','Jun 20, 2025'],
        ['Carlos Tan','carlos@email.com','+63 920 567 8901','Greenfield Villas','Active','Jun 18, 2025'],
        ['Rosa Garcia','rosa@email.com','+63 921 678 9012','Hillside Estates','Pending','Jun 15, 2025'],
    ] as $c)
    <div class="bg-white rounded-xl border border-stone-200 p-5 hover:shadow-md transition">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-11 h-11 bg-amber-100 rounded-full flex items-center justify-center text-amber-700 font-bold text-lg">
                {{ strtoupper(substr($c[0], 0, 1)) }}
            </div>
            <div>
                <p class="font-semibold text-stone-800">{{ $c[0] }}</p>
                <p class="text-xs text-stone-400">{{ $c[1] }}</p>
            </div>
        </div>
        <div class="space-y-1.5 text-sm text-stone-500 mb-4">
            <p class="flex items-center gap-2">
                <svg class="w-4 h-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                {{ $c[2] }}
            </p>
            <p class="flex items-center gap-2">
                <svg class="w-4 h-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"/></svg>
                {{ $c[3] }}
            </p>
        </div>
        <div class="flex items-center justify-between pt-3 border-t border-stone-100">
            <span class="px-2 py-1 rounded-full text-xs font-medium
                {{ $c[4]==='Active' ? 'bg-green-100 text-green-700' : '' }}
                {{ $c[4]==='Pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                {{ $c[4]==='Overdue' ? 'bg-red-100 text-red-700' : '' }}">
                {{ $c[4] }}
            </span>
            <div class="flex gap-2">
                <button class="text-xs text-amber-600 hover:underline">View</button>
                <button class="text-xs text-stone-400 hover:underline">Edit</button>
            </div>
        </div>
    </div>
    @endforeach
</div>

@endsection
