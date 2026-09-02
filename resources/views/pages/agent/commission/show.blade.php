@extends('layouts.app')

@section('title', 'Commission Details')
@section('page-title', 'Commission Details')
@section('page-subtitle', 'Track your payout and broker payment updates')

@section('content')
<div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
    <div class="space-y-6">
        <div class="rounded-2xl border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-stone-800">My Commission Summary</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-stone-400">Property</dt>
                    <dd class="mt-1 text-sm font-medium text-stone-700">{{ $agreement->property?->name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-stone-400">Broker</dt>
                    <dd class="mt-1 text-sm font-medium text-stone-700">{{ $agreement->broker?->name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-stone-400">My Share</dt>
                    <dd class="mt-1 text-sm font-medium text-emerald-600">{{ number_format($agreement->agent_share, 2) }}%</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-stone-400">Payment Schedule</dt>
                    <dd class="mt-1 text-sm font-medium text-stone-700">{{ str_replace('_', ' ', $agreement->payment_schedule) }}</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-stone-800">Payment Status</h2>
            @php $payment = $agreement->payments->first(); @endphp
            @if($payment)
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-stone-400">Status</span><span class="font-medium text-stone-700">{{ ucfirst($payment->payment_status) }}</span></div>
                    <div class="flex justify-between"><span class="text-stone-400">Due Date</span><span class="font-medium text-stone-700">{{ $payment->due_date?->format('M d, Y') ?? 'Not set' }}</span></div>
                    <div class="flex justify-between"><span class="text-stone-400">Amount Due</span><span class="font-medium text-stone-700">₱{{ number_format($payment->agent_amount, 2) }}</span></div>
                    @if($payment->proof_path)
                        <div>
                            <span class="text-stone-400">Proof</span>
                            <a href="{{ Storage::disk('public')->url($payment->proof_path) }}" target="_blank" class="mt-1 block text-sm font-medium text-teal-600">View proof</a>
                        </div>
                    @endif
                </div>
            @else
                <p class="mt-3 text-sm text-stone-500">No payment data is available yet.</p>
            @endif
        </div>
    </div>

    <div class="rounded-2xl border border-stone-200 bg-white p-5">
        <h2 class="text-lg font-semibold text-stone-800">Broker Notes</h2>
        @php $payment = $agreement->payments->first(); @endphp
        @if($payment && $payment->notes->count())
            <ul class="mt-4 space-y-3">
                @foreach($payment->notes as $note)
                    <li class="rounded-lg bg-stone-50 p-3 text-sm text-stone-600">
                        <p class="font-medium text-stone-700">{{ $note->sender_type === 'broker' ? 'Broker' : 'Agent' }}</p>
                        <p class="mt-1">{{ $note->message }}</p>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="mt-3 text-sm text-stone-500">No broker note available yet.</p>
        @endif
    </div>
</div>
@endsection
