@extends('layouts.admin')
@section('title', 'Broker Management')
@section('page-title', 'Broker Management')
@section('page-subtitle', 'Manage all brokers')

@section('content')

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="flex flex-col sm:flex-row gap-3 justify-between mb-5">
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search brokers..." class="border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 w-56">
        <button type="submit" class="bg-stone-100 hover:bg-stone-200 text-stone-700 px-4 py-2 rounded-lg text-sm transition">Search</button>
    </form>
    <a href="{{ route('admin.brokers.create') }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Broker
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
    @forelse($brokers as $broker)
    <div class="bg-white rounded-xl border border-stone-200 p-5 hover:shadow-md transition">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 overflow-hidden bg-teal-100 rounded-xl flex items-center justify-center text-teal-800 font-bold text-lg shrink-0">
                @if($broker->avatar)
                    <img src="{{ str_starts_with($broker->avatar, 'http') ? $broker->avatar : asset('storage/' . $broker->avatar) }}" alt="{{ $broker->name }} profile picture" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($broker->name, 0, 1)) }}
                @endif
            </div>
            <div>
                <p class="font-semibold text-stone-800">{{ $broker->name }}</p>
                <p class="text-xs text-stone-400">{{ $broker->email }}</p>
                <p class="text-xs text-stone-500">License: {{ $broker->license_number ?? 'Not provided' }}</p>
                <span class="mt-1 inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $broker->is_approved ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                    {{ $broker->is_approved ? 'Approved' : 'Pending Approval' }}
                </span>
            </div>
        </div>
        <div class="flex justify-between text-xs text-stone-500 pt-3 border-t border-stone-100 mb-3">
            <span>{{ $broker->clients_count }} client(s)</span>
            <span class="text-stone-400">Since {{ $broker->created_at->format('M Y') }}</span>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.brokers.show', $broker) }}" class="flex-1 text-center border border-stone-200 hover:bg-stone-50 text-stone-600 py-2 rounded-lg text-xs font-medium transition">View Profile</a>
            <a href="{{ route('admin.users.edit', $broker) }}" class="flex-1 text-center border border-teal-200 hover:bg-teal-50 text-teal-700 py-2 rounded-lg text-xs font-medium transition">Edit</a>
            @if(!$broker->is_approved)
            <form action="{{ route('admin.brokers.approve', $broker) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-xs font-medium transition">Approve</button>
            </form>
            @endif
            @if($broker->is_approved)
            <form action="{{ route('admin.brokers.reject', $broker) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="flex-1 text-center bg-red-600 hover:bg-red-700 text-white py-2 rounded-lg text-xs font-medium transition">Reject</button>
            </form>
            @endif
        </div>
    </div>
    @empty
    <div class="col-span-3 py-12 text-center text-stone-400">
        No brokers found. <a href="{{ route('admin.brokers.create') }}" class="text-red-600 hover:underline">Add one</a>
    </div>
    @endforelse
</div>

<div class="mt-5">{{ $brokers->links() }}</div>

@endsection
