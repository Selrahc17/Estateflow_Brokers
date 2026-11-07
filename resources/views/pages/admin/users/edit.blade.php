@extends('layouts.admin')
@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('page-subtitle', $user->name)

@section('content')
<div class="max-w-lg" x-data="{ role: '{{ old('role', $user->role) }}' }">
    <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-6">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-200 mb-1">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                @error('name')<p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-200 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                @error('email')<p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-200 mb-1">Role</label>
                <select name="role" x-model="role" required class="w-full border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    @foreach(['admin','broker','agent','client'] as $r)
                    <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
            </div>
            <div x-show="role === 'agent'">
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-200 mb-1">Assign Broker</label>
                <select name="broker_id" class="w-full border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    <option value="">Select a Broker</option>
                    @foreach($brokers as $broker)
                    <option value="{{ $broker->id }}" {{ old('broker_id', $user->broker_id) == $broker->id ? 'selected' : '' }}>{{ $broker->name }}</option>
                    @endforeach
                </select>
                @error('broker_id')<p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-200 mb-1">New Password <span class="text-stone-500 dark:text-stone-400 font-normal">(leave blank to keep current)</span></label>
                <input type="password" name="password" class="w-full border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                @error('password')<p class="text-xs text-red-500 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 dark:text-stone-200 mb-1">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="w-full border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Update User</button>
                <a href="{{ route('admin.users') }}" class="px-5 py-2 rounded-lg text-sm font-medium border border-stone-200 dark:border-stone-700 text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
