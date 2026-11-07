@php
    $selectedContactId = (int) request('contact', $contacts->first()?->id);
    $selectedContact = $contacts->firstWhere('id', $selectedContactId);
    $conversation = $messagesByContact->get($selectedContactId, collect());
    $indexRoute = auth()->user()->role === 'broker' ? 'broker.messages.index' : 'agent.messages.index';
    $storeRoute = auth()->user()->role === 'broker' ? 'broker.messages.store' : 'agent.messages.store';
@endphp
@extends(auth()->user()->role === 'broker' ? 'layouts.broker' : 'layouts.app')
@section('title', 'Messages')
@section('page-title', 'Messages')
@section('page-subtitle', auth()->user()->role === 'broker' ? 'Message your Agents' : 'Message your Broker or Clients')

@section('content')
<div class="grid min-h-[620px] grid-cols-1 overflow-hidden rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 lg:grid-cols-[260px_1fr]">
    <aside class="border-b border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-800/60 lg:border-b-0 lg:border-r">
        <div class="border-b border-stone-200 dark:border-stone-700 px-4 py-4">
            <h2 class="font-semibold text-stone-800 dark:text-stone-100">{{ auth()->user()->role === 'broker' ? 'Your Agents' : 'Your Contacts' }}</h2>
            <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">{{ $contacts->count() }} contact{{ $contacts->count() !== 1 ? 's' : '' }}</p>
        </div>
        <div class="max-h-[520px] overflow-y-auto p-2">
            @forelse($contacts as $contact)
            <a href="{{ route($indexRoute, ['contact' => $contact->id]) }}" class="flex items-center gap-3 rounded-lg px-3 py-3 transition {{ $selectedContactId === $contact->id ? 'bg-teal-50 dark:bg-teal-950 text-teal-800 dark:text-teal-300' : 'text-stone-700 dark:text-stone-200 hover:bg-white' }}">
                <div class="h-9 w-9 shrink-0 overflow-hidden rounded-full bg-teal-100 dark:bg-teal-900 text-center text-sm font-bold leading-9 text-teal-800 dark:text-teal-200">
                    @if($contact->avatar)
                        <img src="{{ $contact->avatar }}" alt="{{ $contact->name }} profile picture" class="h-full w-full object-cover">
                    @else
                        {{ strtoupper(substr($contact->name, 0, 1)) }}
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium">{{ $contact->name }}</p>
                    <p class="truncate text-xs text-stone-400">{{ $contact->email }}</p>
                </div>
            </a>
            @empty
            <p class="px-3 py-8 text-center text-sm text-stone-400">No contacts available.</p>
            @endforelse
        </div>
    </aside>

    <section class="flex min-h-[620px] flex-col">
        @if($selectedContact)
        <header class="flex items-center gap-3 border-b border-stone-200 dark:border-stone-700 px-5 py-4">
            <div class="h-9 w-9 shrink-0 overflow-hidden rounded-full bg-teal-100 dark:bg-teal-900 text-center text-sm font-bold leading-9 text-teal-800 dark:text-teal-200">
                @if($selectedContact->avatar)
                    <img src="{{ $selectedContact->avatar }}" alt="{{ $selectedContact->name }} profile picture" class="h-full w-full object-cover">
                @else
                    {{ strtoupper(substr($selectedContact->name, 0, 1)) }}
                @endif
            </div>
            <div>
                <h2 class="font-semibold text-stone-800 dark:text-stone-100">{{ $selectedContact->name }}</h2>
                <p class="text-xs text-stone-400">{{ ucfirst($selectedContact->role) }}</p>
            </div>
        </header>
        <div class="flex-1 space-y-3 overflow-y-auto bg-stone-50 dark:bg-stone-800/60 p-5">
            @forelse($conversation as $message)
            <div class="flex {{ $message->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[80%] rounded-xl px-4 py-3 {{ $message->sender_id === auth()->id() ? 'bg-teal-700 text-white' : 'bg-white dark:bg-stone-900 text-stone-700 dark:text-stone-200 border border-stone-200 dark:border-stone-700' }}">
                    @if($message->message !== '')
                        <p class="whitespace-pre-wrap text-sm">{{ $message->message }}</p>
                    @endif
                    @if($message->attachment)
                        <a href="{{ $message->attachment_url }}" target="_blank" rel="noopener" class="mt-2 block">
                            <img src="{{ $message->attachment_url }}" alt="Attached photo" class="max-h-56 rounded-lg object-cover">
                        </a>
                    @endif
                    <div class="mt-2 flex items-center justify-end gap-2 text-[11px] {{ $message->sender_id === auth()->id() ? 'text-teal-100' : 'text-stone-400' }}">
                        <span>{{ $message->created_at->format('M d, g:i A') }}</span>
                        @if($message->sender_id === auth()->id())
                            <span>{{ $message->seen_at ? 'Seen' : ($message->delivered_at ? 'Delivered' : 'Sent') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <p class="py-16 text-center text-sm text-stone-400">Start the conversation.</p>
            @endforelse
        </div>
        <form action="{{ route($storeRoute) }}" method="POST" enctype="multipart/form-data" class="border-t border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-4">
            @csrf
            <input type="hidden" name="receiver_id" value="{{ $selectedContact->id }}">
            <div class="flex items-end gap-3">
                <div class="flex-1">
                    <textarea name="message" rows="2" placeholder="Write a message..." class="w-full resize-none rounded-lg border border-stone-200 dark:border-stone-700 px-3 py-2 text-sm focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-400"></textarea>
                    @error('message')<p class="mt-1 text-xs text-red-500 dark:text-red-300">{{ $message }}</p>@enderror
                </div>
                <label class="cursor-pointer rounded-lg border border-stone-200 dark:border-stone-700 px-3 py-2 text-xs font-medium text-stone-600 dark:text-stone-300 transition hover:bg-stone-50 dark:hover:bg-stone-800">
                    Photo
                    <input type="file" name="photo" accept="image/*" class="hidden">
                </label>
                <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-800">Send</button>
            </div>
            @error('photo')<p class="mt-1 text-xs text-red-500 dark:text-red-300">{{ $message }}</p>@enderror
        </form>
        @else
        <div class="flex flex-1 items-center justify-center p-8 text-center text-sm text-stone-400">Select a contact to start messaging.</div>
        @endif
    </section>
</div>
@endsection
