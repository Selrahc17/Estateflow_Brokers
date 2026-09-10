@extends('layouts.broker')
@section('title', 'Add Agent')
@section('page-title', 'Add Agent')
@section('page-subtitle', 'Create an Agent account under your Broker account')

@section('content')
<div class="max-w-lg bg-white rounded-xl border border-stone-200 p-6">
    <form action="{{ route('broker.agents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-stone-700 mb-1">Full Name</label>
            <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-stone-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            @error('email')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-stone-700 mb-1">Password</label>
            <input type="password" name="password" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            @error('password')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-stone-700 mb-1">Confirm Password</label>
            <input type="password" name="password_confirmation" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
        </div>
        <div class="border-t border-stone-100 pt-4">
            <label class="block text-sm font-medium text-stone-700 mb-1">Supporting ID (optional)</label>
            <select name="document_type" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm mb-2">
                <option value="">Select ID type</option>
                <option value="drivers_license">Driver's License</option>
                <option value="physical_id">Physical ID</option>
                <option value="postal_id">Postal ID</option>
                <option value="national_id">National ID</option>
                <option value="other">Other ID</option>
            </select>
            <input type="file" name="document_file" accept=".jpg,.jpeg,.png,.pdf" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm">
            <p class="text-xs text-stone-400 mt-1">JPG, PNG, or PDF up to 10MB.</p>
        </div>
        <div class="flex gap-3 pt-2">
            <button type="submit" class="rounded-lg bg-red-600 px-5 py-2 text-sm font-medium text-white transition hover:bg-red-700">Add Agent</button>
            <a href="{{ route('broker.agents.index') }}" class="rounded-lg border border-stone-200 px-5 py-2 text-sm font-medium text-stone-600 transition hover:bg-stone-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
