@extends('layouts.public')
@section('title', 'AI Assistant')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6" style="height: calc(100vh - 180px);">

        {{-- Left Panel --}}
        <div class="hidden xl:flex flex-col gap-4">
            <div class="bg-gradient-to-br from-[#112E3B] to-[#1A6B79] rounded-2xl p-5 text-white">
                <div class="w-12 h-12 bg-teal-700 rounded-xl flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                </div>
                <p class="font-bold text-base mb-1">EstateFlow AI</p>
                <p class="text-stone-300 text-xs leading-relaxed">Your 24/7 assistant for property inquiries, site visits, and document guidance.</p>
                <div class="flex items-center gap-2 mt-3">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                    <span class="text-xs text-green-400 font-medium">Online now</span>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 p-4 flex-1">
                <p class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-3">Quick Topics</p>
                <div class="space-y-1.5">
                    @foreach([
                        'What documents do I need?',
                        'Tell me about my reservation',
                        'What properties are available?',
                        'How do I contact my broker?',
                    ] as $topic)
                    <form method="POST" action="{{ route('client.account.chat.store') }}">
                        @csrf
                        <input type="hidden" name="message" value="{{ $topic }}">
                        <button type="submit" class="w-full text-left text-sm text-stone-600 hover:text-teal-700 hover:bg-teal-50 px-3 py-2 rounded-xl transition">
                            {{ $topic }}
                        </button>
                    </form>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Chat Window --}}
        <div class="xl:col-span-3 bg-white rounded-2xl border border-stone-200 flex flex-col overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100 bg-stone-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-teal-700 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-stone-800 text-sm">EstateFlow AI Assistant</p>
                        <p class="text-xs text-green-500 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full inline-block animate-pulse"></span>
                            Online · Powered by AI
                        </p>
                    </div>
                </div>
                <form method="POST" action="{{ route('client.account.chat.store') }}">
                    @csrf
                    <input type="hidden" name="message" value="Hello! What can you help me with?">
                    @if($messages->isEmpty())
                    <button type="submit" class="text-xs text-teal-700 hover:underline">Start conversation</button>
                    @endif
                </form>
            </div>

            {{-- Messages --}}
            <div class="flex-1 overflow-y-auto p-5 space-y-4 bg-stone-50/50" id="chat-messages">

                @if($messages->isEmpty())
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-teal-700 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                    </div>
                    <div class="bg-white border border-stone-200 rounded-2xl rounded-tl-none px-4 py-3 shadow-sm max-w-sm">
                        <p class="text-sm text-stone-700">Hi {{ auth()->user()->name }}! 👋 I'm your EstateFlow AI Assistant. Ask me anything about your reservation, site visits, documents, or available properties.</p>
                        <p class="text-xs text-stone-400 mt-1">Just now</p>
                    </div>
                </div>
                @else
                @foreach($messages as $msg)
                @if($msg->sender_type === 'user')
                <div class="flex items-start gap-3 flex-row-reverse">
                    <div class="w-8 h-8 bg-teal-100 rounded-xl flex items-center justify-center shrink-0 text-teal-800 font-bold text-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="max-w-sm">
                        <div class="bg-teal-700 rounded-2xl rounded-tr-none px-4 py-3">
                            <p class="text-sm text-white">{{ $msg->message }}</p>
                        </div>
                        <p class="text-xs text-stone-400 mt-1 text-right">{{ $msg->created_at->format('g:i A') }}</p>
                    </div>
                </div>
                @else
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-teal-700 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                    </div>
                    <div class="max-w-sm">
                        <div class="bg-white border border-stone-200 rounded-2xl rounded-tl-none px-4 py-3 shadow-sm">
                            <p class="text-sm text-stone-700">{{ $msg->message }}</p>
                        </div>
                        <p class="text-xs text-stone-400 mt-1">{{ $msg->created_at->format('g:i A') }}</p>
                    </div>
                </div>
                @endif
                @endforeach
                @endif

            </div>

            {{-- Input --}}
            <div class="p-4 border-t border-stone-100 bg-white">
                <form method="POST" action="{{ route('client.account.chat.store') }}" class="flex gap-3 items-end">
                    @csrf
                    <div class="flex-1 border border-stone-200 rounded-2xl px-4 py-3 focus-within:ring-2 focus-within:ring-amber-400 transition bg-stone-50">
                        <input name="message" type="text" placeholder="Ask me anything about your reservation or property..."
                            class="w-full bg-transparent text-sm outline-none text-stone-700 placeholder:text-stone-400"
                            autocomplete="off" required>
                    </div>
                    <button type="submit" class="w-11 h-11 bg-teal-700 hover:bg-teal-800 text-white rounded-2xl flex items-center justify-center transition shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-scroll to bottom
    const el = document.getElementById('chat-messages');
    if (el) el.scrollTop = el.scrollHeight;
</script>
@endpush

@endsection
