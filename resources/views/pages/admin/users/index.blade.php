@extends('layouts.admin')
@section('title', 'User Management')
@section('page-title', 'User Management')
@section('page-subtitle', 'Manage all users in the system')

@section('content')

@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-5 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ session('error') }}</div>
@endif

<div class="flex flex-col sm:flex-row gap-3 justify-between mb-5">
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search users..." class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 w-full sm:w-56">
        <select name="role" class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
            <option value="">All Roles</option>
            @foreach(['admin','agent','client'] as $r)
            <option value="{{ $r }}" {{ request('role') === $r ? 'selected' : '' }}>{{ ucfirst($r) }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-4 py-2 text-sm font-medium text-stone-700 dark:text-stone-200 transition hover:bg-stone-50 dark:hover:bg-stone-800">Filter</button>
    </form>
    <a href="{{ route('admin.users.create') }}" class="bg-teal-700 hover:bg-teal-800 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center justify-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add User
    </a>
</div>

<div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 dark:bg-stone-800/60 border-b border-stone-200 dark:border-stone-700">
            <tr class="text-left text-stone-500 dark:text-stone-400">
                <th class="px-5 py-3 font-medium">User</th>
                <th class="px-5 py-3 font-medium">Role</th>
                <th class="px-5 py-3 font-medium">Email</th>
                <th class="px-5 py-3 font-medium">Joined</th>
                <th class="px-5 py-3 font-medium">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
            @forelse($users as $user)
            <tr class="hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 {{ $user->role === 'agent' ? 'bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200' : ($user->role === 'admin' ? 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300' : 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300') }} rounded-full flex items-center justify-center text-xs font-bold shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <span class="font-medium text-stone-700 dark:text-stone-200">{{ $user->name }}</span>
                            <div class="flex gap-2 mt-1">
                                <span class="px-1.5 py-0.5 rounded text-xs font-medium {{ $user->is_active ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300' }}">
                                    {{ $user->is_active ? 'Active' : 'Suspended' }}
                                </span>
                                @if($user->role === 'agent')
                                <span class="px-1.5 py-0.5 rounded text-xs font-medium {{ $user->is_approved ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300' : 'bg-yellow-100 dark:bg-yellow-950 text-yellow-700 dark:text-yellow-300' }}">
                                    {{ $user->is_approved ? 'Approved' : 'Pending Approval' }}
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $user->role === 'agent' ? 'bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200' : ($user->role === 'admin' ? 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300' : 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300') }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-stone-500 dark:text-stone-400 text-xs">{{ $user->email }}</td>
                <td class="px-5 py-3 text-stone-500 dark:text-stone-400 text-xs whitespace-nowrap">{{ $user->created_at->format('M d, Y') }}</td>
                <td class="px-5 py-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-xs text-teal-700 dark:text-teal-300 hover:underline">Edit</a>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="text-xs {{ $user->is_active ? 'text-orange-600 dark:text-orange-300' : 'text-green-600 dark:text-green-300' }} hover:underline">
                                {{ $user->is_active ? 'Suspend' : 'Reactivate' }}
                            </button>
                        </form>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete this user?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 dark:text-red-400 hover:underline">Delete</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-8 text-center text-stone-500 dark:text-stone-400">No users found.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="px-5 py-4 border-t border-stone-100 dark:border-stone-800">{{ $users->links() }}</div>
</div>

@endsection
