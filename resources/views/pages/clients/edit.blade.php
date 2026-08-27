@extends('layouts.app')
@section('title', 'Edit Client')
@section('page-title', 'Edit Client')
@section('page-subtitle', 'Update client information')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-stone-200 p-6">
        <form action="{{ route('agent.clients.update', $client) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">First Name</label>
                    <input type="text" name="first_name" value="{{ old('first_name', $client->first_name) }}" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    @error('first_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Last Name</label>
                    <input type="text" name="last_name" value="{{ old('last_name', $client->last_name) }}" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    @error('last_name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $client->email) }}" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $client->phone) }}" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Address</label>
                <textarea name="address" rows="2" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">{{ old('address', $client->address) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Status</label>
                <select name="status" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    @foreach(['active', 'inactive', 'suspended'] as $s)
                    <option value="{{ $s }}" {{ old('status', $client->status) === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Update Client</button>
                <a href="{{ route('agent.clients.index') }}" class="px-5 py-2 rounded-lg text-sm font-medium border border-stone-200 text-stone-600 hover:bg-stone-50 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
