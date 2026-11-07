@extends('layouts.admin')
@section('title', 'Broker Management')
@section('page-title', 'Broker Management')
@section('page-subtitle', 'Manage all brokers')

@section('content')

@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
@endif

@error('approval')
<div class="mb-5 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ $message }}</div>
@enderror

<div class="flex flex-col sm:flex-row gap-3 justify-between mb-5">
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search brokers..." class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 min-w-0 flex-1 sm:w-56 sm:flex-none">
        <button type="submit" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-4 py-2 text-sm font-medium text-stone-700 dark:text-stone-200 transition hover:bg-stone-50 dark:hover:bg-stone-800">Search</button>
    </form>
    <a href="{{ route('admin.brokers.create') }}" class="bg-teal-700 hover:bg-teal-800 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Broker
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
    @forelse($brokers as $broker)
    <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5 hover:shadow-md transition">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-12 h-12 overflow-hidden bg-teal-100 dark:bg-teal-900 rounded-xl flex items-center justify-center text-teal-800 dark:text-teal-200 font-bold text-lg shrink-0">
                @if($broker->avatar)
                    <img src="{{ str_starts_with($broker->avatar, 'http') ? $broker->avatar : asset('storage/' . $broker->avatar) }}" alt="{{ $broker->name }} profile picture" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($broker->name, 0, 1)) }}
                @endif
            </div>
            <div class="min-w-0">
                <p class="truncate font-semibold text-stone-800 dark:text-stone-100">{{ $broker->name }}</p>
                <p class="truncate text-xs text-stone-500 dark:text-stone-400">{{ $broker->email }}</p>
                <p class="text-xs text-stone-500 dark:text-stone-400">License: {{ $broker->license_number ?? 'Not provided' }}</p>
                <span class="mt-1 inline-block px-2 py-0.5 rounded-full text-xs font-medium {{ $broker->is_approved ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300' : 'bg-yellow-100 dark:bg-yellow-950 text-yellow-700 dark:text-yellow-300' }}">
                    {{ $broker->is_approved ? 'Approved' : 'Pending Approval' }}
                </span>
            </div>
        </div>
        <div class="flex justify-between text-xs text-stone-500 dark:text-stone-400 pt-3 border-t border-stone-100 dark:border-stone-800 mb-3">
            <span>{{ $broker->clients_count }} client(s)</span>
            <span class="text-stone-500 dark:text-stone-400">Since {{ $broker->created_at->format('M Y') }}</span>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.brokers.show', $broker) }}" class="flex-1 text-center border border-stone-200 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800 text-stone-600 dark:text-stone-300 py-2 rounded-lg text-xs font-medium transition">{{ $broker->is_approved ? 'View Profile' : 'Review Application' }}</a>
            <a href="{{ route('admin.users.edit', $broker) }}" class="flex-1 text-center border border-teal-200 dark:border-teal-800 hover:bg-teal-50 dark:hover:bg-teal-950 text-teal-700 dark:text-teal-300 py-2 rounded-lg text-xs font-medium transition">Edit</a>
        </div>
    </div>
    @empty
    <div class="col-span-full rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 py-12 text-center text-stone-500 dark:text-stone-400">
        No brokers found. <a href="{{ route('admin.brokers.create') }}" class="text-teal-700 dark:text-teal-300 hover:underline">Add one</a>
    </div>
    @endforelse
</div>

<div class="mt-5">{{ $brokers->links() }}</div>

@endsection
