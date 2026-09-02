@extends('layouts.broker')

@section('title', 'Pay Commission')
@section('page-title', 'Pay Commission')
@section('page-subtitle', 'Record this month\'s commission payment')

@section('content')
<div class="mx-auto max-w-3xl">
    <a href="{{ route('broker.commissions.show', $agreement) }}" class="text-sm font-medium text-teal-700 hover:text-teal-800">&larr; Back to commission details</a>

    <div class="mt-4 rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 border-b border-stone-200 pb-5 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-stone-400">Property</p>
                <h2 class="mt-1 text-xl font-semibold text-stone-800">{{ $agreement->property?->name ?? 'N/A' }}</h2>
                <p class="mt-1 text-sm text-stone-500">Agent: {{ $agreement->agent?->name ?? 'N/A' }}</p>
            </div>
            @if($currentPayment)
                <div class="text-left sm:text-right">
                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-400">This Month\'s Amount Due</p>
                        <p class="text-xs font-semibold uppercase tracking-wide text-stone-400">Monthly Amount Due</p>
                    <p class="mt-1 text-xs text-stone-500">Due {{ $currentPayment->due_date?->format('M d, Y') }}</p>
                </div>
            @endif
        </div>

        @if($errors->any())
            <div class="mt-5 rounded-lg border border-rose-200 bg-rose-50 p-3 text-sm text-rose-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($currentPayment)
            <form action="{{ route('broker.commissions.update-payment', $agreement) }}" method="POST" enctype="multipart/form-data" class="mt-6 space-y-5">
                @csrf
                <input type="hidden" name="payment_id" value="{{ $currentPayment->id }}">

                <div>
                    <label for="amount_paid" class="mb-1 block text-sm font-medium text-stone-700">Amount Paid</label>
                    <input id="amount_paid" type="number" name="amount_paid" step="0.01" min="0.01" value="{{ old('amount_paid', $currentPayment->amount_due) }}" required class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    <p class="mt-1 text-xs text-stone-500">Enter the full monthly amount due.</p>
                </div>

                <div>
                    <label for="payment_method" class="mb-1 block text-sm font-medium text-stone-700">Payment Method</label>
                    <select id="payment_method" name="payment_method" required class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                        <option value="">Select method</option>
                        @foreach(['cash' => 'Cash', 'bank_transfer' => 'Bank Transfer', 'gcash' => 'GCash', 'maya' => 'Maya', 'check' => 'Check', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}" {{ old('payment_method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="proof" class="mb-1 block text-sm font-medium text-stone-700">Payment Proof</label>
                    <input id="proof" type="file" name="proof" accept=".jpg,.jpeg,.png" required class="block w-full text-sm text-stone-500 file:mr-4 file:rounded-full file:border-0 file:bg-[var(--color-primary)] file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-[var(--color-primary-dark)]">
                    <p class="mt-1 text-xs text-stone-500">Upload a JPG or PNG picture of the payment receipt.</p>
                </div>

                <div>
                    <label for="payment_message" class="mb-1 block text-sm font-medium text-stone-700">Payment Note <span class="font-normal text-stone-400">(optional)</span></label>
                    <textarea id="payment_message" name="payment_message" rows="3" class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" placeholder="Add a note about this payment...">{{ old('payment_message') }}</textarea>
                </div>

                <div class="flex justify-end border-t border-stone-200 pt-5">
                    <button type="submit" class="rounded-lg bg-[var(--color-primary)] px-5 py-2.5 text-sm font-semibold text-white hover:bg-[var(--color-primary-dark)]">Submit Payment</button>
                </div>
            </form>
        @else
            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                There is no unpaid commission installment due this month. The payment form will become available during the installment's due month.
            </div>
        @endif
    </div>
</div>
@endsection
