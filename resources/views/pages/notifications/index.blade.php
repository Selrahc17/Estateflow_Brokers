@extends('layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Alerts and system notifications')

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    <div class="xl:col-span-2 space-y-3">
        <div class="flex justify-between items-center mb-2">
            <p class="text-sm text-stone-500">{{ $notifications->total() }} notification(s)</p>
            <form action="{{ route('agent.notifications.read-all') }}" method="POST">
                @csrf
                <button type="submit" class="text-xs text-teal-700 hover:underline">Mark all as read</button>
            </form>
        </div>

        @forelse($notifications as $notif)
        <div class="bg-white rounded-xl border {{ $notif->is_read ? 'border-stone-200' : 'border-teal-200' }} p-4 flex items-start gap-4 hover:shadow-sm transition">
            <div class="w-2.5 h-2.5 rounded-full mt-1.5 shrink-0 {{ $notif->is_read ? 'bg-stone-300' : 'bg-teal-600' }}"></div>
            <div class="flex-1">
                <p class="font-medium text-stone-800 text-sm">{{ $notif->title }}</p>
                <p class="text-xs text-stone-500 mt-0.5">{{ $notif->message }}</p>
            </div>
            <div class="flex flex-col items-end gap-1 shrink-0">
                <span class="text-xs text-stone-400">{{ $notif->created_at->diffForHumans() }}</span>
                @if(!$notif->is_read)
                <form action="{{ route('agent.notifications.read', $notif) }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs text-teal-700 hover:underline">Mark read</button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="bg-white rounded-xl border border-stone-200 p-10 text-center text-stone-400">
            No notifications yet.
        </div>
        @endforelse

        <div class="mt-4">{{ $notifications->links() }}</div>
    </div>

    {{-- Send Notification --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Send Notification</h2>
        <form action="{{ route('agent.notifications.send') ?? '#' }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs text-stone-500 mb-1 block">Recipient (User ID)</label>
                <select name="user_id" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    @foreach(\App\Models\Client::where('broker_id', auth()->id())->get() as $c)
                    <option value="{{ $c->user_id }}">{{ $c->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs text-stone-500 mb-1 block">Title</label>
                <input type="text" name="title" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" placeholder="Notification title">
            </div>
            <div>
                <label class="text-xs text-stone-500 mb-1 block">Message</label>
                <textarea name="message" rows="4" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 resize-none" placeholder="Type your message..."></textarea>
            </div>
            <button type="submit" class="w-full bg-teal-700 hover:bg-teal-800 text-white py-2 rounded-lg text-sm font-medium transition">Send Notification</button>
        </form>
    </div>

</div>

@endsection
