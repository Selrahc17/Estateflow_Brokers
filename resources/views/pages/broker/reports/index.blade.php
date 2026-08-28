@extends('layouts.broker')
@section('title', 'Reports and Analytics')
@section('page-title', 'Reports and Analytics')
@section('page-subtitle', 'Performance overview for your assigned Agents')

@section('content')
<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6 mb-6">
    @foreach([
        ['Revenue', '₱' . number_format($data['total_revenue'], 2), 'text-green-600'],
        ['Properties', $data['total_properties'], 'text-stone-800'],
        ['Clients', $data['total_clients'], 'text-blue-600'],
        ['Reservations', $data['total_reservations'], 'text-teal-700'],
        ['Leads', $data['total_leads'], 'text-indigo-600'],
        ['Viewings', $data['total_viewings'], 'text-red-600'],
    ] as [$label, $value, $color])
    <div class="rounded-xl border border-stone-200 bg-white p-4">
        <p class="text-xs text-stone-500">{{ $label }}</p>
        <p class="mt-1 text-lg font-bold {{ $color }} truncate">{{ $value }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
    <div class="rounded-xl border border-stone-200 bg-white p-5">
        <h2 class="mb-4 font-semibold text-stone-800">Monthly Revenue</h2>
        @php $maxRevenue = max(1, $data['monthly_revenue']->max()); @endphp
        <div class="flex h-44 items-end gap-2">
            @foreach($data['monthly_revenue'] as $month => $amount)
            <div class="flex min-w-0 flex-1 flex-col items-center gap-1">
                <span class="truncate text-[10px] text-stone-400">{{ $amount > 0 ? number_format($amount / 1000, 0) . 'K' : '—' }}</span>
                <div class="w-full rounded-t-md {{ $amount > 0 ? 'bg-red-500 hover:bg-red-600' : 'bg-stone-100' }} transition" style="height: {{ max(4, round(($amount / $maxRevenue) * 120)) }}px" title="₱{{ number_format($amount, 2) }}"></div>
                <span class="text-[10px] text-stone-400">{{ date('M', mktime(0, 0, 0, $month, 1)) }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="rounded-xl border border-stone-200 bg-white p-5">
        <h2 class="mb-4 font-semibold text-stone-800">Reservations by Status</h2>
        @forelse(['pending' => 'yellow', 'confirmed' => 'green', 'completed' => 'blue', 'cancelled' => 'red'] as $status => $color)
        @php $count = $data['reservations_by_status'][$status] ?? 0; $percent = $data['total_reservations'] ? round($count / $data['total_reservations'] * 100) : 0; @endphp
        <div class="mb-3 last:mb-0">
            <div class="mb-1 flex justify-between text-sm"><span class="font-medium text-stone-700">{{ ucfirst($status) }}</span><span class="text-stone-500">{{ $count }}</span></div>
            <div class="h-2 w-full rounded-full bg-stone-100"><div class="h-2 rounded-full bg-{{ $color }}-500" style="width: {{ $percent }}%"></div></div>
        </div>
        @empty
        <p class="py-6 text-center text-sm text-stone-400">No reservations yet.</p>
        @endforelse
    </div>
</div>

<div class="mt-5 overflow-hidden rounded-xl border border-stone-200 bg-white">
    <div class="border-b border-stone-100 px-5 py-4"><h2 class="font-semibold text-stone-800">Agent Performance</h2></div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-stone-100"><tr class="text-left text-stone-500">
                <th class="px-5 py-3 font-medium">Agent</th><th class="px-5 py-3 font-medium">Leads</th><th class="px-5 py-3 font-medium">Viewings</th><th class="px-5 py-3 font-medium">Sales</th>
            </tr></thead>
            <tbody class="divide-y divide-stone-100">
                @forelse($agents as $agent)
                <tr class="hover:bg-stone-50"><td class="px-5 py-3 font-medium text-stone-700">{{ $agent->name }}</td><td class="px-5 py-3 text-indigo-600">{{ $agent->leads_count }}</td><td class="px-5 py-3 text-teal-700">{{ $agent->viewings_count }}</td><td class="px-5 py-3 text-green-600">{{ $agent->sales_count }}</td></tr>
                @empty
                <tr><td colspan="4" class="px-5 py-8 text-center text-stone-400">No Agents assigned yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
