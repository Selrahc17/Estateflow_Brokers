@extends('layouts.broker')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Alerts and messages for your Broker account')

@section('content')
@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
@endif
@if($errors->any())
<div class="mb-5 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 px-4 py-3 text-sm text-red-600 dark:text-red-300">
    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
</div>
@endif

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="space-y-3 xl:col-span-2">
        <div class="mb-2 flex items-center justify-between">
            <p class="text-sm text-stone-500 dark:text-stone-400">{{ $notifications->total() }} notification(s)</p>
            <form action="{{ route('broker.notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="text-xs text-teal-700 dark:text-teal-300 hover:underline">Mark all as read</button>
            </form>
        </div>
        @forelse($notifications as $notification)
        <div class="flex items-start gap-4 rounded-xl border {{ $notification->is_read ? 'border-stone-200 dark:border-stone-700' : 'border-teal-200 dark:border-teal-800' }} bg-white dark:bg-stone-900 p-4">
            <div class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full {{ $notification->is_read ? 'bg-stone-300' : 'bg-teal-500' }}"></div>
            <div class="flex-1">
                <p class="text-sm font-medium text-stone-800 dark:text-stone-100">{{ $notification->title }}</p>
                <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">{{ $notification->message }}</p>
            </div>
            <div class="flex shrink-0 flex-col items-end gap-1">
                <span class="text-xs text-stone-400">{{ $notification->created_at->diffForHumans() }}</span>
                @if(!$notification->is_read)
                <form action="{{ route('broker.notifications.read', $notification) }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs text-teal-700 dark:text-teal-300 hover:underline">Mark read</button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-10 text-center text-stone-400">No notifications yet.</div>
        @endforelse
        <div class="mt-4">{{ $notifications->links() }}</div>
    </div>

    <div class="h-fit rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-5">
        <h2 class="mb-4 font-semibold text-stone-800 dark:text-stone-100">Notify an Agent</h2>
        <form action="{{ route('broker.notifications.send') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="mb-1 block text-xs text-stone-500 dark:text-stone-400">Agent</label>
                <select name="user_id" required class="w-full rounded-lg border border-stone-200 dark:border-stone-700 px-3 py-2 text-sm focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400">
                    <option value="">Select an Agent</option>
                    @foreach($agents as $agent)
                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs text-stone-500 dark:text-stone-400">Title</label>
                <input type="text" name="title" required class="w-full rounded-lg border border-stone-200 dark:border-stone-700 px-3 py-2 text-sm focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400">
            </div>
            <div>
                <label class="mb-1 block text-xs text-stone-500 dark:text-stone-400">Message</label>
                <textarea name="message" rows="4" class="w-full resize-none rounded-lg border border-stone-200 dark:border-stone-700 px-3 py-2 text-sm focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400"></textarea>
            </div>
            <button type="submit" class="w-full rounded-lg bg-teal-700 py-2 text-sm font-medium text-white transition hover:bg-teal-800">Send Notification</button>
        </form>
    </div>
</div>
@endsection
