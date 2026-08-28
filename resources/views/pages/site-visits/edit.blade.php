@extends('layouts.app')
@section('title', 'Edit Site Visit')
@section('page-title', 'Edit Site Visit')
@section('page-subtitle', 'Update site visit details')

@section('content')

<div class="max-w-2xl mx-auto bg-white rounded-xl border border-stone-200 p-6">
    <form action="{{ route('agent.site-visits.update', $siteVisit) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="block text-sm font-medium text-stone-700 mb-1">Client</label>
            <select name="client_id" class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" required>
                <option value="">Select client...</option>
                @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id', $siteVisit->client_id) == $client->id ? 'selected' : '' }}>{{ $client->full_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-stone-700 mb-1">Property</label>
            <select name="property_id" class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" required>
                <option value="">Select property...</option>
                @foreach($properties as $property)
                    <option value="{{ $property->id }}" {{ old('property_id', $siteVisit->property_id) == $property->id ? 'selected' : '' }}>{{ $property->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-stone-700 mb-1">Inquiry (Optional)</label>
            <select name="inquiry_id" class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                <option value="">None</option>
                @foreach($inquiries as $inquiry)
                    <option value="{{ $inquiry->id }}" {{ old('inquiry_id', $siteVisit->inquiry_id) == $inquiry->id ? 'selected' : '' }}>{{ $inquiry->client?->full_name ?? '—' }} - {{ $inquiry->property?->name ?? '—' }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-stone-700 mb-1">Scheduled Date & Time</label>
            <input type="datetime-local" name="scheduled_at" class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" required value="{{ old('scheduled_at', $siteVisit->scheduled_at?->format('Y-m-d\TH:i')) }}">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-stone-700 mb-1">Notes</label>
            <textarea name="notes" class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" rows="3">{{ old('notes', $siteVisit->notes) }}</textarea>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-stone-700 mb-1">Status</label>
            <select name="status" class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" required>
                @foreach(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $key => $label)
                    <option value="{{ $key }}" {{ old('status', $siteVisit->status) == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-3 pt-4 border-t border-stone-100">
            <a href="{{ route('agent.site-visits.index') }}" class="flex-1 px-4 py-2 border border-stone-200 rounded-lg text-sm text-stone-600 hover:bg-stone-50 transition text-center">
                Cancel
            </a>
            <button type="submit" class="flex-1 px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white rounded-lg text-sm font-medium transition">
                Update
            </button>
        </div>
    </form>
</div>

@endsection