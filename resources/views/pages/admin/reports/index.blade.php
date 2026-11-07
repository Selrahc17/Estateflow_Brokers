@extends('layouts.admin')
@section('title', 'Reports & Analytics')
@section('page-title', 'Reports & Analytics')
@section('page-subtitle', 'Broker performance metrics and insights')

@section('content')

{{-- KPI Summary --}}
<div class="mb-6 grid grid-cols-2 gap-4 lg:grid-cols-3 2xl:grid-cols-6">
    @foreach([
        ['Total Brokers', $summary['total_brokers'], null],
        ['Total Agents', $summary['total_agents'], null],
        ['Total Clients', $summary['total_clients'], null],
        ['Total Properties', $summary['total_properties'], null],
        ['Total Reservations', $summary['total_reservations'], null],
        ['Total Sales', '₱' . number_format($summary['total_sales'], 0), 'green'],
    ] as [$label, $value, $tone])
        @include('layouts.partials.stat-tile', compact('label', 'value', 'tone'))
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-5">

    {{-- Reservations by Status --}}
    <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5">
        <h2 class="font-semibold text-stone-800 dark:text-stone-100 mb-4">Reservations by Status</h2>
        @php $totalRes = $reservationsByStatus->sum() ?: 1; @endphp
        <div class="space-y-3">
            @forelse($reservationsByStatus as $status => $count)
            @php
                $color = match($status) {
                    'confirmed','completed' => ['bg-green-500', 'bg-green-100 dark:bg-green-950', 'text-green-700 dark:text-green-300'],
                    'pending'               => ['bg-teal-600', 'bg-teal-100 dark:bg-teal-900', 'text-teal-800 dark:text-teal-200'],
                    'cancelled'             => ['bg-red-500',   'bg-red-100 dark:bg-red-950',   'text-red-600 dark:text-red-300'],
                    default                 => ['bg-stone-400', 'bg-stone-100 dark:bg-stone-800', 'text-stone-600 dark:text-stone-300'],
                };
                $pct = round(($count / $totalRes) * 100);
            @endphp
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="{{ $color[2] }} font-medium">{{ ucfirst($status) }}</span>
                    <span class="text-stone-500 dark:text-stone-400">{{ $count }} ({{ $pct }}%)</span>
                </div>
                <div class="w-full {{ $color[1] }} rounded-full h-2">
                    <div class="{{ $color[0] }} h-2 rounded-full" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-center text-stone-500 dark:text-stone-400 py-4 text-sm">No reservations found.</p>
            @endforelse
        </div>
    </div>

    {{-- Properties by Status --}}
    <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5">
        <h2 class="font-semibold text-stone-800 dark:text-stone-100 mb-4">Properties by Status</h2>
        @php $totalProps = $propertiesByStatus->sum() ?: 1; @endphp
        <div class="space-y-3">
            @forelse($propertiesByStatus as $status => $count)
            @php
                $color = match($status) {
                    'Active', 'RFO'  => ['bg-green-500', 'bg-green-100 dark:bg-green-950', 'text-green-700 dark:text-green-300'],
                    'Pre-Selling'    => ['bg-blue-500',  'bg-blue-100 dark:bg-blue-950',  'text-blue-700 dark:text-blue-300'],
                    'Sold Out'       => ['bg-stone-500', 'bg-stone-100 dark:bg-stone-800', 'text-stone-600 dark:text-stone-300'],
                    default          => ['bg-teal-600', 'bg-teal-100 dark:bg-teal-900', 'text-teal-800 dark:text-teal-200'],
                };
                $pct = round(($count / $totalProps) * 100);
            @endphp
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="{{ $color[2] }} font-medium">{{ ucfirst(str_replace('_', ' ', $status)) }}</span>
                    <span class="text-stone-500 dark:text-stone-400">{{ $count }} ({{ $pct }}%)</span>
                </div>
                <div class="w-full {{ $color[1] }} rounded-full h-2">
                    <div class="{{ $color[0] }} h-2 rounded-full" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @empty
            <p class="text-center text-stone-500 dark:text-stone-400 py-4 text-sm">No properties found.</p>
            @endforelse
        </div>
    </div>

</div>

{{-- Broker Performance Table --}}
<div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 overflow-hidden">
    <div class="px-5 py-4 border-b border-stone-100 dark:border-stone-800 flex items-center justify-between">
        <h2 class="font-semibold text-stone-800 dark:text-stone-100">Broker Performance Breakdown</h2>
        <span class="text-xs text-stone-500 dark:text-stone-400">{{ $brokers->count() }} broker(s)</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 dark:bg-stone-800/60 border-b border-stone-100 dark:border-stone-800">
                <tr class="text-left text-stone-500 dark:text-stone-400">
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
            <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                @forelse($brokers as $broker)
                <tr class="hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @if($broker->avatar)
                                <img src="{{ str_starts_with($broker->avatar, 'http') ? $broker->avatar : asset('storage/' . $broker->avatar) }}" class="w-9 h-9 rounded-full object-cover shrink-0">
                            @else
                                <div class="w-9 h-9 bg-teal-100 dark:bg-teal-900 rounded-full flex items-center justify-center text-teal-800 dark:text-teal-200 font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($broker->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <p class="font-semibold text-stone-800 dark:text-stone-100">{{ $broker->name }}</p>
                                <p class="text-xs text-stone-500 dark:text-stone-400">{{ $broker->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="font-semibold text-blue-600 dark:text-blue-300">{{ $broker->agents_count }}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="font-semibold text-green-600 dark:text-green-300">{{ $broker->properties_count }}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="font-semibold text-indigo-600 dark:text-indigo-300">{{ $broker->clients_count }}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="font-semibold text-purple-600 dark:text-purple-300">{{ $broker->reservations_count }}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-block px-2 py-0.5 bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300 rounded-full text-xs font-medium">{{ $broker->confirmed_res }}</span>
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="inline-block px-2 py-0.5 bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200 rounded-full text-xs font-medium">{{ $broker->pending_res }}</span>
                    </td>
                    <td class="px-5 py-4 text-right font-bold text-stone-800 dark:text-stone-100">
                        ₱{{ number_format($broker->total_sales, 0) }}
                    </td>
                    <td class="px-5 py-4 text-center">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $broker->is_approved ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300' : 'bg-yellow-100 dark:bg-yellow-950 text-yellow-700 dark:text-yellow-300' }}">
                            {{ $broker->is_approved ? 'Approved' : 'Pending' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-5 py-12 text-center text-stone-500 dark:text-stone-400">No brokers found.</td>
                </tr>
                @endforelse
            </tbody>
            @if($brokers->count())
            <tfoot class="bg-stone-50 dark:bg-stone-800/60 border-t border-stone-200 dark:border-stone-700">
                <tr class="text-stone-600 dark:text-stone-300 font-semibold text-sm">
                    <td class="px-5 py-3">Totals</td>
                    <td class="px-5 py-3 text-center text-blue-600 dark:text-blue-300">{{ $brokers->sum('agents_count') }}</td>
                    <td class="px-5 py-3 text-center text-green-600 dark:text-green-300">{{ $brokers->sum('properties_count') }}</td>
                    <td class="px-5 py-3 text-center text-indigo-600 dark:text-indigo-300">{{ $brokers->sum('clients_count') }}</td>
                    <td class="px-5 py-3 text-center text-purple-600 dark:text-purple-300">{{ $brokers->sum('reservations_count') }}</td>
                    <td class="px-5 py-3 text-center text-green-600 dark:text-green-300">{{ $brokers->sum('confirmed_res') }}</td>
                    <td class="px-5 py-3 text-center text-teal-700 dark:text-teal-300">{{ $brokers->sum('pending_res') }}</td>
                    <td class="px-5 py-3 text-right text-stone-800 dark:text-stone-100">₱{{ number_format($brokers->sum('total_sales'), 0) }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection
