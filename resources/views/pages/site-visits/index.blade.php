@extends('layouts.app')
@section('title', 'Site Visits')
@section('page-title', 'Site Visits')
@section('page-subtitle', 'Manage your site visit schedules')

@section('content')

<div class="flex flex-wrap gap-4 items-center justify-between mb-6">
    <div class="flex gap-2">
        @foreach(['all' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $key => $label)
            <a href="{{ request()->fullUrlWithQuery(['status' => $key]) }}" 
               class="px-4 py-2 rounded-lg text-sm font-medium transition {{ request('status') === $key || (request('status') === null && $key === 'all') ? 'bg-amber-600 text-white' : 'bg-white border border-stone-200 text-stone-600 hover:bg-stone-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>
    <a href="{{ route('agent.site-visits.create') }}" class="flex items-center gap-2 px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Site Visit
    </a>
</div>

<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    @if($siteVisits->count())
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-stone-100">
                <tr class="text-left text-stone-500">
                    <th class="px-6 py-3 font-medium">Client</th>
                    <th class="px-6 py-3 font-medium">Property</th>
                    <th class="px-6 py-3 font-medium">Scheduled</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach($siteVisits as $visit)
                    <tr class="hover:bg-stone-50 transition">
                        <td class="px-6 py-4 font-medium text-stone-700">{{ $visit->client?->full_name ?? '—' }}</td>
                        <td class="px-6 py-4 text-stone-600">{{ $visit->property?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-stone-600">{{ $visit->scheduled_at?->format('M d, Y h:i A') ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ match($visit->status) {
                                'pending' => 'bg-yellow-100 text-yellow-700',
                                'confirmed' => 'bg-blue-100 text-blue-700',
                                'completed' => 'bg-green-100 text-green-700',
                                'cancelled' => 'bg-red-100 text-red-600',
                                default => 'bg-stone-100 text-stone-600',
                            } }}">
                                {{ ucfirst($visit->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center gap-2 justify-end">
                                <a href="{{ route('agent.site-visits.show', $visit) }}" class="text-xs text-amber-600 hover:underline">View</a>
                                <a href="{{ route('agent.site-visits.edit', $visit) }}" class="text-xs text-blue-600 hover:underline">Edit</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-6">
            {{ $siteVisits->links() }}
        </div>
    @else
        <div class="p-12 text-center text-stone-400">
            <p>No site visits yet.</p>
        </div>
    @endif
</div>

@endsection