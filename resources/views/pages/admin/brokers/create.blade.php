@extends('layouts.admin')
@section('title', 'Add Broker')
@section('page-title', 'Add Broker')
@section('page-subtitle', 'Register a new broker account')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl border border-stone-200 p-6">
        <form action="{{ route('admin.brokers.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Password</label>
                <input type="password" name="password" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Create Broker</button>
                <a href="{{ route('admin.brokers') }}" class="px-5 py-2 rounded-lg text-sm font-medium border border-stone-200 text-stone-600 hover:bg-stone-50 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
