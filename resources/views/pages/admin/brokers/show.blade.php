@extends('layouts.admin')
@section('title', 'Broker Details')
@section('page-title', 'Broker Details')
@section('page-subtitle', $user->name)

@section('content')
@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
@endif

@error('approval')
<div class="mb-5 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ $message }}</div>
@enderror

@error('notes')
<div class="mb-5 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ $message }}</div>
@enderror

@error('reason')
<div class="mb-5 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ $message }}</div>
@enderror

@include('pages.admin.brokers._compliance')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-6">
        <div class="flex items-center gap-4 mb-5">
            <div class="w-14 h-14 overflow-hidden bg-teal-100 dark:bg-teal-900 rounded-full flex items-center justify-center text-teal-800 dark:text-teal-200 font-bold text-2xl">
                @if($user->avatar)
                    <img src="{{ str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . $user->avatar) }}" alt="{{ $user->name }} profile picture" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                @endif
            </div>
            <div>
                <p class="font-bold text-stone-800 dark:text-stone-100 text-lg">{{ $user->name }}</p>
                <p class="text-sm text-stone-500 dark:text-stone-400">{{ $user->email }}</p>
                <span class="text-xs bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 px-2 py-0.5 rounded-full font-medium">Broker</span>
                <p class="mt-2 text-xs text-stone-500 dark:text-stone-400">License: {{ $user->license_number ?? 'Not provided' }}</p>
            </div>
        </div>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-stone-500 dark:text-stone-400">Total Clients</span><span class="font-semibold text-stone-700 dark:text-stone-200">{{ $user->clients->count() }}</span></div>
            <div class="flex justify-between"><span class="text-stone-500 dark:text-stone-400">Joined</span><span class="text-stone-700 dark:text-stone-200">{{ $user->created_at->format('M d, Y') }}</span></div>
        </div>
    </div>

    <div class="xl:col-span-2 bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5">
        <h2 class="font-semibold text-stone-800 dark:text-stone-100 mb-4">Clients ({{ $user->clients->count() }})</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-stone-500 dark:text-stone-400 border-b border-stone-100 dark:border-stone-800">
                        <th class="pb-3 font-medium">Name</th>
                        <th class="pb-3 font-medium">Email</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th class="pb-3 font-medium">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50 dark:divide-stone-800">
                    @forelse($user->clients as $client)
                    <tr class="hover:bg-stone-50 dark:hover:bg-stone-800">
                        <td class="py-3 font-medium text-stone-700 dark:text-stone-200">{{ $client->full_name }}</td>
                        <td class="py-3 text-stone-500 dark:text-stone-400 text-xs">{{ $client->email }}</td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $client->status === 'active' ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300' : 'bg-stone-100 dark:bg-stone-800 text-stone-500 dark:text-stone-400' }}">
                                {{ ucfirst($client->status) }}
                            </span>
                        </td>
                        <td class="py-3 text-stone-500 dark:text-stone-400 text-xs">{{ $client->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-6 text-center text-stone-500 dark:text-stone-400">No clients assigned.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
