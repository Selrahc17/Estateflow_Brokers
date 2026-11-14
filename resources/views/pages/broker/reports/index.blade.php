@extends('layouts.broker')
@section('title', 'Reports and Analytics')
@section('page-title', 'Reports and Analytics')
@section('page-subtitle', 'Performance overview for your assigned Agents')

@section('content')
<div class="mb-4 grid grid-cols-2 gap-4 lg:grid-cols-4">
    @foreach([
        ['Revenue', '₱' . number_format($data['total_revenue'], 2), 'green'],
        ['Properties', $data['total_properties'], null],
        ['Clients', $data['total_clients'], null],
        ['Reservations', $data['total_reservations'], null],
    ] as [$label, $value, $tone])
        @include('layouts.partials.stat-tile', compact('label', 'value', 'tone'))
    @endforeach
</div>

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
    @foreach([
        ['Commission Pending', '₱' . number_format($data['commission_pending'] ?? 0, 2), 'amber'],
        ['Commission Transferred', '₱' . number_format($data['commission_transferred'] ?? 0, 2), null],
        ['Commission Received', '₱' . number_format($data['commission_received'] ?? 0, 2), 'green'],
    ] as [$label, $value, $tone])
        @include('layouts.partials.stat-tile', compact('label', 'value', 'tone'))
    @endforeach
</div>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    <div class="rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-5">
        <h2 class="mb-4 font-semibold text-stone-800 dark:text-stone-100">Monthly Revenue</h2>
        @php $maxRevenue = max(1, $data['monthly_revenue']->max()); @endphp
        <div class="flex h-44 items-end gap-2">
            @foreach($data['monthly_revenue'] as $month => $amount)
            <div class="flex min-w-0 flex-1 flex-col items-center gap-1">
                <span class="truncate text-[10px] text-stone-500 dark:text-stone-400">{{ $amount > 0 ? number_format($amount / 1000, 0) . 'K' : '—' }}</span>
                <div class="w-full rounded-t-md {{ $amount > 0 ? 'bg-teal-500 hover:bg-teal-600' : 'bg-stone-100 dark:bg-stone-800' }} transition" style="height: {{ max(4, round(($amount / $maxRevenue) * 120)) }}px" title="₱{{ number_format($amount, 2) }}"></div>
                <span class="text-[10px] text-stone-500 dark:text-stone-400">{{ date('M', mktime(0, 0, 0, $month, 1)) }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-5">
        <h2 class="mb-4 font-semibold text-stone-800 dark:text-stone-100">Reservations by Status</h2>
        @forelse(['pending' => 'bg-amber-500', 'confirmed' => 'bg-green-500', 'completed' => 'bg-blue-500', 'cancelled' => 'bg-red-500'] as $status => $color)
        @php $count = $data['reservations_by_status'][$status] ?? 0; $percent = $data['total_reservations'] ? round($count / $data['total_reservations'] * 100) : 0; @endphp
        <div class="mb-3 last:mb-0">
            <div class="mb-1 flex justify-between text-sm"><span class="font-medium text-stone-700 dark:text-stone-200">{{ ucfirst($status) }}</span><span class="text-stone-500 dark:text-stone-400">{{ $count }}</span></div>
            <div class="h-2 w-full rounded-full bg-stone-100 dark:bg-stone-800"><div class="h-2 rounded-full {{ $color }}" style="width: {{ $percent }}%"></div></div>
        </div>
        @empty
        <p class="py-6 text-center text-sm text-stone-500 dark:text-stone-400">No reservations yet.</p>
        @endforelse
    </div>
</div>

<div class="mt-5 overflow-hidden rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900">
    <div class="border-b border-stone-100 dark:border-stone-800 px-5 py-4"><h2 class="font-semibold text-stone-800 dark:text-stone-100">Agent Performance</h2></div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 dark:bg-stone-800/60 border-b border-stone-100 dark:border-stone-800"><tr class="text-left text-stone-500 dark:text-stone-400">
                <th class="px-5 py-3 font-medium">Agent</th><th class="px-5 py-3 font-medium">Leads</th><th class="px-5 py-3 font-medium">Viewings</th><th class="px-5 py-3 font-medium">Sales</th>
            </tr></thead>
            <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                @forelse($agents as $agent)
                <tr class="hover:bg-stone-50 dark:hover:bg-stone-800"><td class="px-5 py-3 font-medium text-stone-700 dark:text-stone-200">{{ $agent->name }}</td><td class="px-5 py-3 text-indigo-600 dark:text-indigo-300">{{ $agent->leads_count }}</td><td class="px-5 py-3 text-teal-700 dark:text-teal-300">{{ $agent->viewings_count }}</td><td class="px-5 py-3 text-green-600 dark:text-green-300">{{ $agent->sales_count }}</td></tr>
                @empty
                <tr><td colspan="4" class="px-5 py-8 text-center text-stone-500 dark:text-stone-400">No Agents assigned yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
