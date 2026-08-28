@extends('layouts.app')
@section('title', 'Client Details')
@section('page-title', 'Client Details')
@section('page-subtitle', '{{ $client->full_name }}')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Client Info --}}
    <div class="bg-white rounded-xl border border-stone-200 p-6">
        <div class="flex items-center gap-4 mb-5">
            <div class="w-14 h-14 bg-teal-100 rounded-full flex items-center justify-center text-teal-800 font-bold text-2xl">
                {{ strtoupper(substr($client->first_name, 0, 1)) }}
            </div>
            <div>
                <p class="font-bold text-stone-800 text-lg">{{ $client->full_name }}</p>
                <p class="text-sm text-stone-400">{{ $client->email }}</p>
            </div>
        </div>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between"><span class="text-stone-400">Phone</span><span class="text-stone-700">{{ $client->phone ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-stone-400">Status</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                    {{ $client->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-stone-100 text-stone-500' }}">
                    {{ ucfirst($client->status) }}
                </span>
            </div>
            <div class="flex justify-between"><span class="text-stone-400">Address</span><span class="text-stone-700 text-right max-w-[60%]">{{ $client->address ?? '—' }}</span></div>
            <div class="flex justify-between"><span class="text-stone-400">Joined</span><span class="text-stone-700">{{ $client->created_at->format('M d, Y') }}</span></div>
        </div>
        <div class="mt-5 pt-4 border-t border-stone-100">
            <a href="{{ route('agent.clients.edit', $client) }}" class="block text-center bg-teal-700 hover:bg-teal-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Edit Client</a>
        </div>
    </div>

    {{-- Reservations --}}
    <div class="xl:col-span-2 space-y-4">
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold text-stone-800">Reservations</h2>
                <a href="{{ route('agent.reservations.create') }}" class="text-xs text-teal-700 hover:underline">+ New</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-stone-400 border-b border-stone-100">
                            <th class="pb-3 font-medium">Code</th>
                            <th class="pb-3 font-medium">Property / Lot</th>
                            <th class="pb-3 font-medium">Status</th>
                            <th class="pb-3 font-medium">Total</th>
                            <th class="pb-3 font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-50">
                        @forelse($client->reservations as $res)
                        <tr class="hover:bg-stone-50">
                            <td class="py-3 font-mono text-xs text-stone-600">{{ $res->reservation_code }}</td>
                            <td class="py-3 text-stone-600">{{ $res->lot?->property?->name }} / {{ $res->lot?->lot_number }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $res->status === 'confirmed' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $res->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                    {{ $res->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                                    {{ $res->status === 'completed' ? 'bg-blue-100 text-blue-700' : '' }}">
                                    {{ ucfirst($res->status) }}
                                </span>
                            </td>
                            <td class="py-3 text-stone-700">₱{{ number_format($res->total_price, 2) }}</td>
                            <td class="py-3 text-stone-400 text-xs">{{ $res->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="py-6 text-center text-stone-400">No reservations yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
