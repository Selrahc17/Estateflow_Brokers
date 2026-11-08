@extends('layouts.app')
@section('title', 'AI Assistant')
@section('page-title', 'AI Assistant')
@section('page-subtitle', 'Ask about your listings, leads, and site visits')

@section('content')
<div class="mx-auto flex max-w-4xl flex-col rounded-xl border border-stone-200 bg-white" style="height: calc(100vh - 220px); min-height: 28rem;">

    <div class="flex items-center gap-3 border-b border-stone-100 px-5 py-4">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-teal-700" aria-hidden="true">
            <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <p class="text-sm font-semibold text-stone-800">EstateFlow AI</p>
            <p class="text-xs text-stone-500">Knows your own listings, leads, and upcoming site visits. Double-check anything important.</p>
        </div>
    </div>

    <div id="chat-messages" class="flex-1 space-y-4 overflow-y-auto p-5">
        @forelse($messages as $msg)
            @if($msg->sender_type === 'user')
            <div class="flex flex-row-reverse items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-100 text-sm font-bold text-teal-800">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                <div class="max-w-md">
                    <div class="rounded-2xl rounded-tr-none bg-teal-700 px-4 py-3">
                        <p class="whitespace-pre-wrap text-sm text-white">{{ $msg->message }}</p>
                    </div>
                    <p class="mt-1 text-right text-xs text-stone-500">{{ $msg->created_at->format('M j, g:i A') }}</p>
                </div>
            </div>
            @else
            <div class="flex items-start gap-3">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-teal-700" aria-hidden="true">
                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div class="max-w-md">
                    <div class="chat-md rounded-2xl rounded-tl-none bg-stone-100 px-4 py-3 text-sm text-stone-700">{!! $msg->message_html !!}</div>
                    <p class="mt-1 text-xs text-stone-500">{{ $msg->created_at->format('M j, g:i A') }}</p>
                </div>
            </div>
            @endif
        @empty
            <div class="flex h-full flex-col items-center justify-center text-center">
                <p class="text-sm font-medium text-stone-700">Hi {{ auth()->user()->name }}! Ask me anything.</p>
                <p class="mt-1 max-w-sm text-sm text-stone-500">For example, how many new leads you have, which listings still have open lots, or tips for a client follow-up.</p>
            </div>
        @endforelse
    </div>

    <div class="border-t border-stone-100 px-5 py-3">
        <p class="mb-2 text-xs text-stone-500">Suggested questions:</p>
        <div class="flex flex-wrap gap-2">
            @foreach(['How many new leads do I have?', 'Which of my listings still have available lots?', 'Write a friendly follow-up message for a new lead.'] as $question)
            <form method="POST" action="{{ route('agent.chat.store') }}">
                @csrf
                <input type="hidden" name="message" value="{{ $question }}">
                <button type="submit" class="rounded-full bg-stone-100 px-3 py-1.5 text-xs text-stone-600 transition hover:bg-teal-50 hover:text-teal-800">{{ $question }}</button>
            </form>
            @endforeach
        </div>
    </div>

    <form method="POST" action="{{ route('agent.chat.store') }}" class="flex gap-3 border-t border-stone-100 p-4" x-data="{ busy: false }" @submit="busy = true">
        @csrf
        <label for="ai-message" class="sr-only">Message</label>
        <input id="ai-message" name="message" type="text" required maxlength="1000" autocomplete="off" :readonly="busy" placeholder="Type your question..."
            class="flex-1 rounded-xl border border-stone-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
        <button type="submit" :disabled="busy" class="rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-teal-800 disabled:cursor-wait disabled:opacity-60">
            <span x-show="!busy">Send</span>
            <span x-show="busy" style="display: none">Thinking…</span>
        </button>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const list = document.getElementById('chat-messages');
        if (list) list.scrollTop = list.scrollHeight;
    });
</script>
@endpush
@endsection
