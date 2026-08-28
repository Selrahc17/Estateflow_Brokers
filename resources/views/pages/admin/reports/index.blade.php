@extends('layouts.admin')
@section('title', 'Reports & Analytics')
@section('page-title', 'Reports & Analytics')
@section('page-subtitle', 'Broker performance metrics and insights')

@section('content')

{{-- KPI Summary --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
    @foreach([
        ['Total Brokers',      $summary['total_brokers'],                              'text-teal-700',  'border-teal-100'],
        ['Total Agents',       $summary['total_agents'],                               'text-blue-600',   'border-blue-100'],
        ['Total Clients',      $summary['total_clients'],                              'text-indigo-600', 'border-indigo-100'],
        ['Total Properties',   $summary['total_properties'],                           'text-green-600',  'border-green-100'],
        ['Total Reservations', $summary['total_reservations'],                         'text-purple-600', 'border-purple-100'],
        ['Total Sales',        '₱' . number_format($summary['total_sales'], 0),        'text-red-600',    'border-red-100'],
    ] as $s)
    <div class="bg-white rounded-xl border {{ $s[3] }} p-4 text-center">
        <p class="text-xs text-stone-500 mb-1">{{ $s[0] }}</p>
        <p class="text-xl font-bold {{ $s[2] }}">{{ $s[1] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-5">

    {{-- Reservations by Status --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Reservations by Status</h2>
        @php $totalRes = $reservationsByStatus->sum() ?: 1; @endphp
        <div class="space-y-3">
            @forelse($reservationsByStatus as $status => $count)
            @php
                $color = match($status) {
                    'confirmed','completed' => ['bg-green-500', 'bg-green-100', 'text-green-700'],
                    'pending'               => ['bg-teal-600', 'bg-teal-100', 'text-teal-800'],
                    'cancelled'             => ['bg-red-500',   'bg-red-100',   'text-red-600'],
                    default                 => ['bg-stone-400', 'bg-stone-100', 'text-stone-600'],
                };
                $pct = round(($count / $totalRes) * 100);
            @endphp
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="{{ $color[2] }} font-medium">{{ ucfirst($status) }}</span>
                    <span class="text-stone-500">{{ $count }} ({{ $pct }}%)</span>
                </div>
                <div class="w-full {{ $color[1] }} rounded-full h-2">
                    <div class="{{ $color[0] }} h-2 rounded-full" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-center text-stone-400 py-4 text-sm">No reservations found.</p>
            @endforelse
        </div>
    </div>

    {{-- Properties by Status --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Properties by Status</h2>
        @php $totalProps = $propertiesByStatus->sum() ?: 1; @endphp
        <div class="space-y-3">
            @forelse($propertiesByStatus as $status => $count)
            @php
                $color = match($status) {
                    'Active', 'RFO'  => ['bg-green-500', 'bg-green-100', 'text-green-700'],
                    'Pre-Selling'    => ['bg-blue-500',  'bg-blue-100',  'text-blue-700'],
                    'Sold Out'       => ['bg-stone-500', 'bg-stone-100', 'text-stone-600'],
                    default          => ['bg-teal-600', 'bg-teal-100', 'text-teal-800'],
                };
                $pct = round(($count / $totalProps) * 100);
            @endphp
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="{{ $color[2] }} font-medium">{{ $status }}</span>
                    <span class="text-stone-500">{{ $count }} ({{ $pct }}%)</span>
                </div>
                <div class="w-full {{ $color[1] }} rounded-full h-2">
                    <div class="{{ $color[0] }} h-2 rounded-full" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-center text-stone-400 py-4 text-sm">No properties found.</p>
            @endforelse
        </div>
    </div>

</div>

{{-- Broker Performance Table --}}
<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between">
        <h2 class="font-semibold text-stone-800">Broker Performance Breakdown</h2>
        <span class="text-xs text-stone-400">{{ $brokers->count() }} broker(s)</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-stone-100">
                <tr class="text-left text-stone-500">
                    <th class="px-5 py-3 font-medium">Broker</th>
                    <th class="px-5 py-3 font-medium text-center">Agents</th>
                    <th class="px-5 py-3 font-medium text-center">Properties</th>
                    <th class="px-5 py-3 font-medium text-center">Clients</th>
                    <th class="px-5 py-3 font-medium text-center">Reservations</th>
                    <th class="px-5 py-3 font-medium text-center">Confirmed</th>
                    <th class="px-5 py-3 font-medium text-center">Pending</th>
                    <th class="px-5 py-3 font-medium text-right">Total Sales</th>
                    <th class="px-5 py-3 font-medium text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse($brokers as $broker)
                <tr class="hover:bg-stone-50 transition">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @if($broker->avatar)
                                <img src="{{ str_starts_with($broker->avatar, 'http') ? $broker->avatar : asset('storage/' . $broker->avatar) }}" class="w-9 h-9 rounded-full object-cover shrink-0">
                            @else
                                <div class="w-9 h-9 bg-teal-100 rounded-full flex items-center justify-center text-teal-800 font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($broker->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <p class="font-semibold text-stone-800">{{ $broker->name }}</p>
                                <p class="text-xs text-stone-400">{{ $broker->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="font-semibold text-blue-600">{{ $broker->agents_count }}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="font-semibold text-green-600">{{ $broker->properties_count }}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="font-semibold text-indigo-600">{{ $broker->clients_count }}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="font-semibold text-purple-600">{{ $broker->reservations_count }}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-block px-2 py-0.5 bg-green-100 text-green-700 rounded-full text-xs font-medium">{{ $broker->confirmed_res }}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-block px-2 py-0.5 bg-teal-100 text-teal-800 rounded-full text-xs font-medium">{{ $broker->pending_res }}</span>
                    </td>
                    <td class="px-5 py-4 text-right font-bold text-stone-800">
                        ₱{{ number_format($broker->total_sales, 0) }}
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $broker->is_approved ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ $broker->is_approved ? 'Approved' : 'Pending' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-5 py-12 text-center text-stone-400">No brokers found.</td>
                </tr>
                @endforelse
            </tbody>
            @if($brokers->count())
            <tfoot class="bg-stone-50 border-t border-stone-200">
                <tr class="text-stone-600 font-semibold text-sm">
                    <td class="px-5 py-3">Totals</td>
                    <td class="px-5 py-3 text-center text-blue-600">{{ $brokers->sum('agents_count') }}</td>
                    <td class="px-5 py-3 text-center text-green-600">{{ $brokers->sum('properties_count') }}</td>
                    <td class="px-5 py-3 text-center text-indigo-600">{{ $brokers->sum('clients_count') }}</td>
                    <td class="px-5 py-3 text-center text-purple-600">{{ $brokers->sum('reservations_count') }}</td>
                    <td class="px-5 py-3 text-center text-green-600">{{ $brokers->sum('confirmed_res') }}</td>
                    <td class="px-5 py-3 text-center text-teal-700">{{ $brokers->sum('pending_res') }}</td>
                    <td class="px-5 py-3 text-right text-stone-800">₱{{ number_format($brokers->sum('total_sales'), 0) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection
