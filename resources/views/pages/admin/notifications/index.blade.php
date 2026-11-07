@extends('layouts.admin')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Your alerts and updates')

@section('content')

<div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5">
    <div class="flex justify-between items-center mb-4">
        <p class="text-sm text-stone-500 dark:text-stone-400">{{ $notifications->total() }} notification{{ $notifications->total() != 1 ? 's' : '' }}</p>
        @if($unreadNotifs > 0)
            <form action="{{ route('admin.notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="text-xs text-teal-700 dark:text-teal-300 hover:underline">Mark all as read</button>
            </form>
        @endif
    </div>

    <div class="space-y-3">
        @forelse($notifications as $notif)
        <div class="border border-stone-200 dark:border-stone-700 rounded-lg p-4 flex items-start gap-4 hover:shadow-sm transition @if(!$notif->is_read) border-teal-200 dark:border-teal-800 bg-teal-50/30 dark:bg-teal-950/40 @endif">
            <div class="w-2.5 h-2.5 rounded-full mt-1.5 shrink-0 {{ $notif->is_read ? 'bg-stone-300 dark:bg-stone-600' : 'bg-teal-600' }}"></div>
            <div class="flex-1">
                <p class="font-medium text-stone-800 dark:text-stone-100 text-sm">{{ $notif->title }}</p>
                @if($notif->message)
                    <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">{{ $notif->message }}</p>
                @endif
            </div>
            <div class="flex flex-col items-end gap-1 shrink-0">
                <span class="text-xs text-stone-500 dark:text-stone-400">{{ $notif->created_at->diffForHumans() }}</span>
            </div>
        </div>
        @empty
        <div class="text-center py-8 text-stone-500 dark:text-stone-400">No notifications yet</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
</div>

@endsection