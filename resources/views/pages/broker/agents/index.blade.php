@extends('layouts.broker')
@section('title', 'Agent Management')
@section('page-title', 'Agent Management')
@section('page-subtitle', 'Manage Agents assigned to your Broker account')

@section('content')
@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl border border-stone-200">
    <div class="flex items-center justify-between gap-4 px-5 py-4 border-b border-stone-100">
        <div>
            <h2 class="font-semibold text-stone-800">Your Agents</h2>
            <p class="mt-1 text-xs text-stone-500">Only Agents assigned to your account are shown.</p>
        </div>
        <a href="{{ route('broker.agents.create') }}" class="shrink-0 rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700">Add Agent</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-stone-100">
                <tr class="text-left text-stone-500">
                    <th class="px-5 py-3 font-medium">Agent</th>
                    <th class="px-5 py-3 font-medium">Email</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Joined</th>
                    <th class="px-5 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse($agents as $agent)
                <tr class="hover:bg-stone-50 transition">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-3">
                            <div class="h-9 w-9 shrink-0 overflow-hidden rounded-full bg-amber-100 text-center text-sm font-bold leading-9 text-amber-700">
                                @if($agent->avatar)
                                    <img src="{{ $agent->avatar }}" alt="{{ $agent->name }} profile picture" class="h-full w-full object-cover">
                                @else
                                    {{ strtoupper(substr($agent->name, 0, 1)) }}
                                @endif
                            </div>
                            <span class="font-medium text-stone-700">{{ $agent->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-xs text-stone-500">{{ $agent->email }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $agent->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                            {{ $agent->is_active ? 'Active' : 'Suspended' }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs text-stone-400">{{ $agent->created_at->format('M d, Y') }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('broker.agents.edit', $agent) }}" class="text-xs font-medium text-amber-600 hover:underline">Edit</a>
                            <form action="{{ route('broker.agents.toggle-status', $agent) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs font-medium {{ $agent->is_active ? 'text-red-600' : 'text-green-600' }} hover:underline">
                                    {{ $agent->is_active ? 'Suspend' : 'Activate' }}
                                </button>
                            </form>
                            <form action="{{ route('broker.agents.destroy', $agent) }}" method="POST" onsubmit="return confirm('Delete this Agent?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-stone-500 hover:text-red-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-stone-400">No Agents assigned yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($agents->hasPages())
        <div class="border-t border-stone-100 px-5 py-4">{{ $agents->links() }}</div>
    @endif
</div>
@endsection
