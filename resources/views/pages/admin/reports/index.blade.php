@extends('layouts.admin')
@section('title', 'Reports & Analytics')
@section('page-title', 'Reports & Analytics')
@section('page-subtitle', 'System-wide performance metrics and insights')

@section('content')

{{-- KPI Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @php
        $totalReservations = $data['reservations_by_status']->sum() ?? 0;
        $totalClients = $data['by_role']['client'] ?? 0;
        $totalBrokers = $data['by_role']['agent'] ?? 0;
        $totalProperties = \App\Models\Property::count();
    @endphp
    @foreach([
        ['Total Properties', $totalProperties, '', 'text-red-600', 'bg-red-50 border-red-100'],
        ['Total Reservations', $totalReservations, '', 'text-blue-600', 'bg-blue-50 border-blue-100'],
        ['Total Clients', $totalClients, '', 'text-amber-600', 'bg-amber-50 border-amber-100'],
        ['Total Brokers', $totalBrokers, '', 'text-green-600', 'bg-green-50 border-green-100'],
    ] as $s)
    <div class="bg-white rounded-xl border {{ $s[4] }} p-5">
        <p class="text-xs text-stone-500 mb-1">{{ $s[0] }}</p>
        <p class="text-2xl font-bold text-stone-800">{{ $s[1] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Top Brokers (from dashboard stats) --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Top Brokers</h2>
        @php
            $topBrokers = \App\Models\User::where('role', 'agent')
                ->withCount('properties')
                ->orderBy('properties_count', 'desc')
                ->take(4)
                ->get();
        @endphp
        <div class="space-y-3">
            @foreach($topBrokers as $index => $broker)
            <div class="flex items-center gap-3 p-3 bg-stone-50 rounded-xl">
                <div class="w-7 h-7 {{ $index===0 ? 'bg-amber-500' : 'bg-stone-200' }} rounded-full flex items-center justify-center text-xs font-bold {{ $index===0 ? 'text-white' : 'text-stone-600' }}">{{ $index+1 }}</div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-stone-700">{{ $broker->name }}</p>
                    <p class="text-xs text-stone-400">{{ $broker->properties_count }} properties listed</p>
                </div>
            </div>
            @empty
            <p class="text-center text-stone-400 py-4">No brokers found.</p>
            @endforeach
        </div>
    </div>

    {{-- Reservations by Status --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Reservations by Status</h2>
        <div class="space-y-3">
            @foreach($data['reservations_by_status'] as $status => $count)
            <div class="flex items-center justify-between p-3 bg-stone-50 rounded-xl">
                <span class="text-sm text-stone-600">{{ ucfirst($status) }}</span>
                <span class="text-sm font-bold text-stone-700">{{ $count }}</span>
            </div>
            @empty
            <p class="text-center text-stone-400 py-4">No reservations found.</p>
            @endforeach
        </div>
    </div>

    {{-- Users by Role --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Users by Role</h2>
        <div class="space-y-3">
            @foreach($data['by_role'] as $role => $count)
            <div class="flex items-center justify-between p-3 bg-stone-50 rounded-xl">
                <span class="text-sm text-stone-600">{{ ucfirst($role) }}</span>
                <span class="text-sm font-bold text-stone-700">{{ $count }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>

@endsection
