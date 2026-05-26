@extends('layouts.admin')
@section('title', 'Edit User')
@section('page-title', 'Edit User')
@section('page-subtitle', '{{ $user->name }}')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-xl border border-stone-200 p-6">
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Full Name</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                @error('name')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                @error('email')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Role</label>
                <select name="role" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                    @foreach(['admin','broker','client'] as $r)
                    <option value="{{ $r }}" {{ old('role', $user->role) === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">New Password <span class="text-stone-400 font-normal">(leave blank to keep current)</span></label>
                <input type="password" name="password" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                @error('password')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Update User</button>
                <a href="{{ route('admin.users') }}" class="px-5 py-2 rounded-lg text-sm font-medium border border-stone-200 text-stone-600 hover:bg-stone-50 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
