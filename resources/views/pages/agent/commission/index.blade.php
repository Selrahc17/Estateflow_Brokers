@extends('layouts.app')
@section('title', 'My Sales')
@section('page-title', 'My Sales')
@section('page-subtitle', 'Properties you sold and their commission status')

@section('content')
@php
    $statusTone = [
        'pending' => 'bg-amber-100 text-amber-800',
        'transferred' => 'bg-sky-100 text-sky-800',
        'received' => 'bg-green-100 text-green-800',
    ];
@endphp

<div class="mb-5 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h2 class="font-semibold text-stone-800">{{ $soldCount }} {{ $soldCount === 1 ? 'sale' : 'sales' }}</h2>
        <p class="text-sm text-stone-500">To add a sale, use <span class="font-medium text-stone-700">Mark as Sold</span> on a listing or lot. Your broker manages the commission.</p>
    </div>
</div>

<div class="overflow-hidden rounded-xl border border-stone-200 bg-white">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] divide-y divide-stone-200 text-sm">
            <thead class="bg-stone-50">
                <tr class="text-left text-stone-500">
                    <th class="px-4 py-3 font-medium">Property / Project</th>
                    <th class="whitespace-nowrap px-4 py-3 font-medium">Date of Sale</th>
                    <th class="whitespace-nowrap px-4 py-3 text-right font-medium">Selling Price</th>
                    <th class="whitespace-nowrap px-4 py-3 font-medium">Commission Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse($sales as $sale)
                <tr>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-stone-100">
                                @if($sale->photo_url)
                                <img src="{{ $sale->photo_url }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                @else
                                <svg class="h-5 w-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                @endif
                            </div>
                            <p class="font-medium text-stone-800">{{ $sale->item_name }}</p>
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-stone-600">{{ $sale->sold_at?->format('M j, Y') }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-right text-stone-700">{{ $sale->selling_price !== null ? '₱' . number_format((float) $sale->selling_price, 2) : '—' }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        @if($sale->show_status_to_agent)
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusTone[$sale->status] ?? '' }}">{{ $sale->status_label }}</span>
                        @else
                        <span class="text-xs text-stone-500">Not shared by your broker</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-12 text-center text-sm text-stone-500">No sales yet. When you mark a listing or lot as Sold, it shows up here.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $sales->links() }}</div>
@endsection
