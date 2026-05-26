@extends('layouts.admin')
@section('title', 'User Management')
@section('page-title', 'User Management')
@section('page-subtitle', 'Manage all users in the system')

@section('content')

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">{{ session('error') }}</div>
@endif

<div class="flex flex-col sm:flex-row gap-3 justify-between mb-5">
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..." class="border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 w-56">
        <select name="role" class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            <option value="">All Roles</option>
            @foreach(['admin','broker','client'] as $r)
            <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
            @endforeach
        </select>
        <button type="submit" class="bg-stone-100 hover:bg-stone-200 text-stone-700 px-4 py-2 rounded-lg text-sm transition">Filter</button>
    </form>
    <a href="{{ route('admin.users.create') }}" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add User
    </a>
</div>

<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-200">
            <tr class="text-left text-stone-500">
                <th class="px-5 py-3 font-medium">User</th>
                <th class="px-5 py-3 font-medium">Role</th>
                <th class="px-5 py-3 font-medium">Email</th>
                <th class="px-5 py-3 font-medium">Joined</th>
                <th class="px-5 py-3 font-medium">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse($users as $user)
            <tr class="hover:bg-stone-50 transition">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 {{ $user->role === 'broker' ? 'bg-amber-100 text-amber-700' : ($user->role === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }} rounded-full flex items-center justify-center text-xs font-bold shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <span class="font-medium text-stone-700">{{ $user->name }}</span>
                    </div>
                </td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $user->role === 'broker' ? 'bg-amber-100 text-amber-700' : ($user->role === 'admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-stone-500 text-xs">{{ $user->email }}</td>
                <td class="px-5 py-3 text-stone-400 text-xs">{{ $user->created_at->format('M d, Y') }}</td>
                <td class="px-5 py-3">
                    <div class="flex gap-3">
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-xs text-amber-600 hover:underline">Edit</a>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete this user?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:underline">Delete</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-8 text-center text-stone-400">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-5 py-4 border-t border-stone-100">{{ $users->links() }}</div>
</div>

@endsection
