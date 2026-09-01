@extends('layouts.broker')

@section('title', 'Create Commission Agreement')
@section('page-title', 'Create Commission Agreement')
@section('page-subtitle', 'Set the agent payout and payment schedule for a deal')

@section('content')
<div class="mx-auto max-w-3xl rounded-2xl border border-stone-200 bg-white p-6 shadow-sm">
    <form action="{{ route('broker.commissions.store') }}" method="POST" class="space-y-5">
        @csrf

        <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">Agent</label>
                <select name="agent_id" class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" required>
                    <option value="">Select agent</option>
                    @foreach($agents as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">Property</label>
                <select name="property_id" class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    <option value="">Select property</option>
                    @foreach($properties as $property)
                        <option value="{{ $property->id }}">{{ $property->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">Commission Rate (%)</label>
                <input type="number" name="commission_rate" min="0" max="100" step="0.01" required class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" value="{{ old('commission_rate') }}">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">Agent Share (%)</label>
                <input type="number" name="agent_share" min="0" max="100" step="0.01" required class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" value="{{ old('agent_share') }}">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">Broker Share (%)</label>
                <input type="number" name="broker_share" min="0" max="100" step="0.01" required class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" value="{{ old('broker_share') }}">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">Payment Schedule</label>
                <select name="payment_schedule" class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" required>
                    <option value="monthly">Monthly</option>
                    <option value="every_15th">Every 15th</option>
                    <option value="quarterly">Quarterly</option>
                    <option value="annual">Annual</option>
                    <option value="custom">Custom</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">Payment Day</label>
                <input type="number" name="payment_day" min="1" max="31" class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" value="{{ old('payment_day', 15) }}">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">Start Date</label>
                <input type="date" name="start_date" required class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" value="{{ old('start_date', now()->toDateString()) }}">
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">End Date</label>
                <input type="date" name="end_date" class="w-full rounded-lg border border-stone-200 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" value="{{ old('end_date') }}">
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('broker.commissions.index') }}" class="rounded-lg border border-stone-200 px-4 py-2.5 text-sm font-medium text-stone-600 hover:bg-stone-50">Cancel</a>
            <button type="submit" class="rounded-lg bg-[var(--color-primary)] px-4 py-2.5 text-sm font-medium text-white hover:bg-[var(--color-primary-dark)]">Save Agreement</button>
        </div>
    </form>
</div>
@endsection
