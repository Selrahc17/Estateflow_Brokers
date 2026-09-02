@extends('layouts.broker')

@section('title', 'Commission Details')
@section('page-title', 'Commission Details')
@section('page-subtitle', 'Review split, due date, and payment monitoring')

@section('content')
@php($paymentMode = $paymentMode ?? false)
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

            @if($agreement->payments->count())
                <div class="mt-4 space-y-4">
                    @foreach($agreement->payments->sortBy('due_date') as $payment)
                        <div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-400">Due Date</p>
                                    <p class="mt-1 text-sm font-medium text-stone-800">{{ $payment->due_date?->format('M d, Y') ?? 'Not set' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-400">Amount Due</p>
                                    <p class="mt-1 text-sm font-medium text-stone-800">
                                        ₱{{ number_format($payment->amount_due, 2) }}
                                        <span class="ml-1 text-xs text-stone-500">{{ (int) $payment->amount_due }}</span>
                                    </p>
                                </div>
                            </div>

                            @if($paymentMode && $currentPayment?->id === $payment->id)
                                <form action="{{ route('broker.commissions.update-payment', $agreement) }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-3 border-t border-stone-200 pt-4">
                                    @csrf
                                    <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                    <p class="text-sm text-stone-500">This month's payment is due now. Complete the payment details below.</p>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-stone-700">Amount Paid</label>
                                            <input type="number" name="amount_paid" step="0.01" min="0.01" value="{{ old('amount_paid', $payment->amount_due) }}" required class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-stone-700">Payment Method</label>
                                            <select name="payment_method" required class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                                                <option value="">Select method</option>
                                                @foreach(['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'gcash' => 'GCash', 'maya' => 'Maya', 'check' => 'Check', 'other' => 'Other'] as $value => $label)
                                                    <option value="{{ $value }}" {{ old('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-stone-700">Payment Proof</label>
                                        <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" required class="block w-full text-sm text-stone-500 file:mr-4 file:rounded-full file:border-0 file:bg-[var(--color-primary)] file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-[var(--color-primary-dark)]">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-sm font-medium text-stone-700">Payment Note <span class="font-normal text-stone-400">(optional)</span></label>
                                        <textarea name="payment_message" rows="2" class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" placeholder="Add a note about this payment...">{{ old('payment_message') }}</textarea>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit" class="shrink-0 rounded-lg bg-[var(--color-primary)] px-4 py-2.5 text-sm font-medium text-white hover:bg-[var(--color-primary-dark)]">Submit Payment</button>
                                    </div>
                                </form>
                            @elseif($payment->payment_status === 'paid')
                                <p class="mt-4 border-t border-stone-200 pt-4 text-sm font-medium text-emerald-600">Paid</p>
                            @elseif(!$paymentMode && $currentPayment?->id === $payment->id)
                                <a href="{{ route('broker.commissions.pay', $agreement) }}" class="mt-4 inline-flex border-t border-stone-200 pt-4 text-sm font-medium text-teal-700 hover:text-teal-800">Open payment form</a>
                            @else
                                <p class="mt-4 border-t border-stone-200 pt-4 text-sm text-stone-400">Payment action opens during its due month.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-3 text-sm text-stone-500">No payment record has been created for this agreement yet.</p>
            @endif
        </div>
    </div>

    <aside class="space-y-6">
        <div class="rounded-2xl border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-stone-800">Current Payment State</h2>
            @if($currentPayment)
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-stone-400">Status</span><span class="font-medium text-stone-700">{{ ucfirst($currentPayment->payment_status) }}</span></div>
                    <div class="flex justify-between"><span class="text-stone-400">Amount Due</span><span class="font-medium text-stone-700">₱{{ number_format($currentPayment->amount_due, 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-stone-400">Due Date</span><span class="font-medium text-stone-700">{{ $currentPayment->due_date?->format('M d, Y') ?? 'Not set' }}</span></div>
                    @if($currentPayment->payment_method)
                        <div class="flex justify-between"><span class="text-stone-400">Payment Method</span><span class="font-medium text-stone-700">{{ ucfirst(str_replace('_', ' ', $currentPayment->payment_method)) }}</span></div>
                    @endif
                    @if($currentPayment->proof_path)
                        <div>
                            <span class="text-stone-400">Proof</span>
                            <a href="{{ Storage::url($currentPayment->proof_path) }}" target="_blank" class="mt-1 block text-sm font-medium text-teal-600">View proof</a>
                        </div>
                    @endif
                </div>
            @else
                <p class="mt-3 text-sm text-stone-500">No payment data is available.</p>
            @endif
        </div>

        <div class="rounded-2xl border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-stone-800">Notes</h2>
            @if($currentPayment && $currentPayment->notes->count())
                <ul class="mt-4 space-y-3">
                    @foreach($currentPayment->notes as $note)
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
