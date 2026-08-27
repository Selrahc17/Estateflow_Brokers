@extends('layouts.app')
@section('title', 'Reservations')
@section('page-title', 'Reservations')
@section('page-subtitle', 'Manage all client reservations')

@section('content')

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @php
        $brokerId = auth()->id();
        $counts = [
            'total'     => $reservations->total(),
            'confirmed' => \App\Models\Reservation::where('broker_id',$brokerId)->where('status','confirmed')->count(),
            'pending'   => \App\Models\Reservation::where('broker_id',$brokerId)->where('status','pending')->count(),
            'cancelled' => \App\Models\Reservation::where('broker_id',$brokerId)->where('status','cancelled')->count(),
        ];
    @endphp
    @foreach([['Total',$counts['total'],'stone'],['Confirmed',$counts['confirmed'],'green'],['Pending',$counts['pending'],'yellow'],['Cancelled',$counts['cancelled'],'red']] as $s)
    <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
        <p class="text-2xl font-bold text-{{ $s[2] }}-{{ $s[2]==='stone'?'800':'600' }}">{{ $s[1] }}</p>
        <p class="text-sm text-stone-500">{{ $s[0] }}</p>
    </div>
    @endforeach
</div>

<div class="flex flex-col sm:flex-row gap-3 justify-between mb-5">
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search client or lot..." class="border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 w-56">
        <select name="status" class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" onchange="this.form.submit()">
            <option value="">All Status</option>
            @foreach(['pending','confirmed','cancelled','completed'] as $s)
            <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </form>
    <a href="{{ route('agent.reservations.create') }}" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Reservation
    </a>
</div>

<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-200">
            <tr class="text-left text-stone-500">
                <th class="px-5 py-3 font-medium">Code</th>
                <th class="px-5 py-3 font-medium">Client</th>
                <th class="px-5 py-3 font-medium">Property / Lot</th>
                <th class="px-5 py-3 font-medium">Total</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Date</th>
                <th class="px-5 py-3 font-medium"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse($reservations as $res)
            <tr class="hover:bg-stone-50 transition">
                <td class="px-5 py-3 font-mono text-xs text-stone-500">{{ $res->reservation_code }}</td>
                <td class="px-5 py-3 font-medium text-stone-700">{{ $res->client?->full_name ?? '—' }}</td>
                <td class="px-5 py-3 text-stone-500 text-xs">{{ $res->lot?->property?->name }} / Lot {{ $res->lot?->lot_number }}</td>
                <td class="px-5 py-3 font-semibold text-stone-700">₱{{ number_format($res->total_price, 2) }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $res->status==='confirmed' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $res->status==='pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $res->status==='cancelled' ? 'bg-red-100 text-red-600' : '' }}
                        {{ $res->status==='completed' ? 'bg-blue-100 text-blue-700' : '' }}">
                        {{ ucfirst($res->status) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-stone-400 text-xs">{{ $res->created_at->format('M d, Y') }}</td>
                <td class="px-5 py-3">
                    <a href="{{ route('agent.reservations.show', $res) }}" class="text-xs text-amber-600 hover:underline">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-10 text-center text-stone-400">No reservations yet. <a href="{{ route('agent.reservations.create') }}" class="text-amber-600 hover:underline">Create one</a></td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-5 py-4 border-t border-stone-100">{{ $reservations->withQueryString()->links() }}</div>
</div>

@endsection
