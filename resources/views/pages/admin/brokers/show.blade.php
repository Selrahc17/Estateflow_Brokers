@extends('layouts.admin')
@section('title', 'Broker Details')
@section('page-title', 'Broker Details')
@section('page-subtitle', '{{ $user->name }}')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    <div class="bg-white rounded-xl border border-stone-200 p-6">
        <div class="flex items-center gap-4 mb-5">
            <div class="w-14 h-14 bg-amber-100 rounded-full flex items-center justify-center text-amber-700 font-bold text-2xl">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <p class="font-bold text-stone-800 text-lg">{{ $user->name }}</p>
                <p class="text-sm text-stone-400">{{ $user->email }}</p>
                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">Broker</span>
            </div>
        </div>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-stone-400">Total Clients</span><span class="font-semibold text-stone-700">{{ $user->clients->count() }}</span></div>
            <div class="flex justify-between"><span class="text-stone-400">Joined</span><span class="text-stone-700">{{ $user->created_at->format('M d, Y') }}</span></div>
        </div>
    </div>

    <div class="xl:col-span-2 bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Clients ({{ $user->clients->count() }})</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-stone-400 border-b border-stone-100">
                        <th class="pb-3 font-medium">Name</th>
                        <th class="pb-3 font-medium">Email</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th class="pb-3 font-medium">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50">
                    @forelse($user->clients as $client)
                    <tr class="hover:bg-stone-50">
                        <td class="py-3 font-medium text-stone-700">{{ $client->full_name }}</td>
                        <td class="py-3 text-stone-500 text-xs">{{ $client->email }}</td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $client->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-stone-100 text-stone-500' }}">
                                {{ ucfirst($client->status) }}
                            </span>
                        </td>
                        <td class="py-3 text-stone-400 text-xs">{{ $client->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="py-6 text-center text-stone-400">No clients assigned.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
