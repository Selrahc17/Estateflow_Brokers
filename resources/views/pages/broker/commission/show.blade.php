@extends('layouts.broker')

@section('title', 'Commission Details')
@section('page-title', 'Commission Details')
@section('page-subtitle', 'Review split, due date, and payment monitoring')

@section('content')
<div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
    <div class="space-y-6">
        <div class="rounded-2xl border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-stone-800">Commission Summary</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-stone-400">Property</dt>
                    <dd class="mt-1 text-sm font-medium text-stone-700">{{ $agreement->property?->name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-stone-400">Agent</dt>
                    <dd class="mt-1 text-sm font-medium text-stone-700">{{ $agreement->agent?->name ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-stone-400">Commission Rate</dt>
                    <dd class="mt-1 text-sm font-medium text-stone-700">{{ number_format($agreement->commission_rate, 2) }}%</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-stone-400">Payment Schedule</dt>
                    <dd class="mt-1 text-sm font-medium text-stone-700">{{ str_replace('_', ' ', $agreement->payment_schedule) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-stone-400">Agent Share</dt>
                    <dd class="mt-1 text-sm font-medium text-emerald-600">{{ number_format($agreement->agent_share, 2) }}%</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-stone-400">Broker Share</dt>
                    <dd class="mt-1 text-sm font-medium text-amber-600">{{ number_format($agreement->broker_share, 2) }}%</dd>
                </div>
            </dl>
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-stone-800">Payment Monitoring</h2>

            @php $payment = $agreement->payments->first(); @endphp
            @if($payment)
                <form action="{{ route('broker.commissions.update-payment', $agreement) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label class="mb-1 block text-sm font-medium text-stone-700">Payment Status</label>
                        <select name="payment_status" class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                            @foreach(['pending','scheduled','sent','confirmed','disputed','paid'] as $status)
                                <option value="{{ $status }}" {{ $payment->payment_status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-stone-700">Payment Message</label>
                        <textarea name="payment_message" rows="3" class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" placeholder="Send payment note to the agent...">{{ old('payment_message', $payment->payment_message) }}</textarea>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-stone-700">Upload Proof</label>
                        <input type="file" name="proof" class="block w-full text-sm text-stone-500 file:mr-4 file:rounded-full file:border-0 file:bg-[var(--color-primary)] file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-[var(--color-primary-dark)]">
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" class="rounded-lg bg-[var(--color-primary)] px-4 py-2.5 text-sm font-medium text-white hover:bg-[var(--color-primary-dark)]">Save Payment Update</button>
                    </div>
                </form>
            @else
                <p class="mt-3 text-sm text-stone-500">No payment record has been created for this agreement yet.</p>
            @endif
        </div>
    </div>

    <aside class="space-y-6">
        <div class="rounded-2xl border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-stone-800">Current Payment State</h2>
            @if($payment)
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-stone-400">Status</span><span class="font-medium text-stone-700">{{ ucfirst($payment->payment_status) }}</span></div>
                    <div class="flex justify-between"><span class="text-stone-400">Amount Due</span><span class="font-medium text-stone-700">₱{{ number_format($payment->amount_due, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-stone-400">Due Date</span><span class="font-medium text-stone-700">{{ $payment->due_date?->format('M d, Y') ?? 'Not set' }}</span></div>
                    @if($payment->proof_path)
                        <div>
                            <span class="text-stone-400">Proof</span>
                            <a href="{{ Storage::disk('public')->url($payment->proof_path) }}" target="_blank" class="mt-1 block text-sm font-medium text-teal-600">View proof</a>
                        </div>
                    @endif
                </div>
            @else
                <p class="mt-3 text-sm text-stone-500">No payment data is available.</p>
            @endif
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-stone-800">Notes</h2>
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
                <p class="mt-3 text-sm text-stone-500">No notes yet.</p>
            @endif
        </div>
    </aside>
</div>
@endsection
