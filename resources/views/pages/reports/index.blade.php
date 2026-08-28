@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports & Analytics')
@section('page-subtitle', 'Overview of your business performance')

@section('content')

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-stone-200 p-4">
        <p class="text-xs text-stone-500 mb-1">Total Revenue</p>
        <p class="text-xl font-bold text-green-600">₱{{ number_format($data['total_revenue'], 2) }}</p>
    </div>
    <div class="bg-white rounded-xl border border-stone-200 p-4">
        <p class="text-xs text-stone-500 mb-1">Total Properties</p>
        <p class="text-xl font-bold text-stone-800">{{ $data['total_properties'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-stone-200 p-4">
        <p class="text-xs text-stone-500 mb-1">Total Clients</p>
        <p class="text-xl font-bold text-blue-600">{{ $data['total_clients'] }}</p>
    </div>
    <div class="bg-white rounded-xl border border-stone-200 p-4">
        <p class="text-xs text-stone-500 mb-1">Total Reservations</p>
        <p class="text-xl font-bold text-teal-700">{{ $data['total_reservations'] }}</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

    {{-- Reservations by Status --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Reservations by Status</h2>
        @if($data['total_reservations'] > 0)
        <div class="space-y-3">
            @foreach(['pending'=>'yellow','confirmed'=>'green','cancelled'=>'red','completed'=>'blue'] as $status => $color)
            @php $count = $data['reservations_by_status'][$status] ?? 0; $pct = $data['total_reservations'] > 0 ? round($count/$data['total_reservations']*100) : 0; @endphp
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-stone-700 font-medium">{{ ucfirst($status) }}</span>
                    <span class="text-stone-500">{{ $count }}</span>
                </div>
                <div class="w-full bg-stone-100 rounded-full h-2">
                    <div class="bg-{{ $color }}-500 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-stone-400 text-sm text-center py-6">No reservations yet.</p>
        @endif
    </div>

    {{-- Properties by Status --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Properties by Status</h2>
        @if($data['total_properties'] > 0)
        <div class="space-y-3">
            @foreach($data['properties_by_status'] as $status => $count)
            @php $pct = $data['total_properties'] > 0 ? round($count/$data['total_properties']*100) : 0; @endphp
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-stone-700 font-medium">{{ ucfirst(str_replace('_',' ',$status)) }}</span>
                    <span class="text-stone-500">{{ $count }}</span>
                </div>
                <div class="w-full bg-stone-100 rounded-full h-2">
                    <div class="bg-teal-600 h-2 rounded-full" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-stone-400 text-sm text-center py-6">No properties yet.</p>
        @endif
    </div>

    {{-- Monthly Revenue --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Monthly Revenue (Verified Payments)</h2>
        @if($data['monthly_payments']->isNotEmpty())
        <div class="flex items-end gap-2 h-40">
            @foreach(range(1,12) as $month)
            @php $amount = $data['monthly_payments'][$month] ?? 0; $max = $data['monthly_payments']->max() ?: 1; $h = round($amount/$max*120); @endphp
            <div class="flex-1 flex flex-col items-center gap-1">
                @if($amount > 0)<span class="text-xs text-stone-500">{{ number_format($amount/1000,0) }}K</span>@else<span class="text-xs text-stone-300">—</span>@endif
                <div class="w-full {{ $amount > 0 ? 'bg-teal-500 hover:bg-teal-600' : 'bg-stone-100' }} rounded-t-md transition" style="height: {{ max($h,4) }}px"></div>
                <span class="text-xs text-stone-400">{{ date('M', mktime(0,0,0,$month,1)) }}</span>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-stone-400 text-sm text-center py-6">No payment data yet.</p>
        @endif
    </div>

    {{-- Top Clients --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Top Clients by Payment</h2>
        @php
            $topClients = \App\Models\Payment::where('broker_id', auth()->id())
                ->where('status','verified')
                ->selectRaw('client_id, SUM(amount) as total')
                ->groupBy('client_id')
                ->orderByDesc('total')
                ->take(5)
                ->with('client')
                ->get();
        @endphp
        @forelse($topClients as $tp)
        <div class="flex items-center justify-between p-3 bg-stone-50 rounded-lg mb-2">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-teal-100 rounded-full flex items-center justify-center text-teal-800 font-bold text-sm">{{ strtoupper(substr($tp->client?->first_name ?? '?', 0, 1)) }}</div>
                <p class="text-sm font-medium text-stone-700">{{ $tp->client?->full_name ?? '—' }}</p>
            </div>
            <span class="text-sm font-semibold text-stone-700">₱{{ number_format($tp->total, 2) }}</span>
        </div>
        @empty
        <p class="text-stone-400 text-sm text-center py-6">No verified payments yet.</p>
        @endforelse
    </div>

</div>

@endsection
