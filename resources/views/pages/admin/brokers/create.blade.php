@extends('layouts.admin')
@section('title', 'Add Broker')
@section('page-title', 'Add Broker')
@section('page-subtitle', 'Register a new Broker account')

@section('content')
<div class="max-w-lg">
    <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-6">
        <form action="{{ route('admin.brokers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-200 mb-1">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                @error('name')<p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-200 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                @error('email')<p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-200 mb-1">Profile Picture</label>
                <input type="file" name="avatar" accept="image/*" required class="w-full border border-stone-200 dark:border-stone-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                @error('avatar')<p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-200 mb-1">License Number</label>
                <input type="text" name="license_number" value="{{ old('license_number') }}" required class="w-full border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                @error('license_number')<p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-200 mb-1">Password</label>
                <input type="password" name="password" required class="w-full border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                @error('password')<p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-200 mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" required class="w-full border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Create Broker</button>
                <a href="{{ route('admin.brokers') }}" class="px-5 py-2 rounded-lg text-sm font-medium border border-stone-200 dark:border-stone-700 text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
