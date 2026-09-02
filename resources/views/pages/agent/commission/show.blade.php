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
            @if($agreement->payments->count())
                <div class="mt-4 space-y-4">
                    @foreach($agreement->payments->sortBy('due_date') as $payment)
                        <div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-stone-400">Due Date</p>
                                    <p class="mt-1 text-sm font-medium text-stone-800">{{ $payment->due_date?->format('M d, Y') ?? 'Not set' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs uppercase tracking-wide text-stone-400">Amount Due</p>
                                    <p class="mt-1 text-sm font-medium text-stone-800">₱{{ number_format($payment->agent_amount, 2) }}</p>
                                </div>
                            </div>

                            @if($payment->proof_path)
                                <div class="mt-3">
                                    <a href="{{ Storage::disk('public')->url($payment->proof_path) }}" target="_blank" class="text-sm font-medium text-teal-600">View proof</a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mt-3 text-sm text-stone-500">No payment data is available yet.</p>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-2xl border border-stone-200 bg-white p-5">
            <h2 class="text-lg font-semibold text-stone-800">Confirm Payment</h2>
            @if($agreement->payments->count())
                <div class="mt-4 space-y-4">
                    @foreach($agreement->payments->sortBy('due_date') as $payment)
                        <form action="{{ route('agent.commission.update-payment', $agreement) }}" method="POST" enctype="multipart/form-data" class="space-y-3 rounded-xl border border-stone-200 bg-stone-50 p-4">
                            @csrf
                            <input type="hidden" name="payment_id" value="{{ $payment->id }}">

                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-stone-400">Due</p>
                                    <p class="mt-1 text-sm font-medium text-stone-800">{{ $payment->due_date?->format('M d, Y') ?? 'Not set' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs uppercase tracking-wide text-stone-400">Amount</p>
                                    <p class="mt-1 text-sm font-medium text-stone-800">₱{{ number_format($payment->agent_amount, 2) }}</p>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-stone-700">Payment Status</label>
                                <select name="payment_status" class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                                    @foreach(['pending','scheduled','sent','confirmed','disputed','paid'] as $status)
                                        <option value="{{ $status }}" {{ $payment->payment_status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-stone-700">Message</label>
                                <textarea name="payment_message" rows="3" class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" placeholder="Confirm the transfer or send a note to the broker...">{{ old('payment_message', $payment->payment_message) }}</textarea>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-stone-700">Upload Proof</label>
                                <input type="file" name="proof" class="block w-full text-sm text-stone-500 file:mr-4 file:rounded-full file:border-0 file:bg-teal-600 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-teal-700">
                            </div>

                            <div class="flex items-center justify-end">
                                <button type="submit" class="rounded-lg bg-teal-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-teal-700">Save Update</button>
                            </div>
                        </form>
                    @endforeach
                </div>
            @else
                <p class="mt-3 text-sm text-stone-500">No payment record is available for confirmation yet.</p>
            @endif
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
</div>
@endsection
