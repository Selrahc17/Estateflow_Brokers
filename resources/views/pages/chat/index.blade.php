@extends('layouts.app')
@section('title', 'AI Assistant')
@section('page-title', 'AI Chat Assistant')
@section('page-subtitle', 'AI-powered inquiry assistant for clients and brokers')

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-4 gap-5 h-[calc(100vh-200px)]">

    {{-- Chat History Sidebar --}}
    <div class="bg-white rounded-xl border border-stone-200 p-4 flex flex-col">
        <h2 class="font-semibold text-stone-800 mb-3 text-sm">Recent Conversations</h2>
        <div class="space-y-2 flex-1 overflow-y-auto">
            @foreach([
                ['Juan dela Cruz','What is the price of Lot 12-B?','2 min ago',true],
                ['Pedro Reyes','Can I change my lot preference?','Yesterday',false],
                ['Anonymous','What properties are available?','Yesterday',false],
                ['Ana Lim','How do I submit documents?','2 days ago',false],
            ] as $c)
            <div class="p-3 rounded-lg cursor-pointer {{ $c[3] ? 'bg-amber-50 border border-amber-200' : 'hover:bg-stone-50' }} transition">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-sm font-medium text-stone-700">{{ $c[0] }}</p>
                    <span class="text-xs text-stone-400">{{ $c[2] }}</span>
                </div>
                <p class="text-xs text-stone-400 truncate">{{ $c[1] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Chat Window --}}
    <div class="xl:col-span-3 bg-white rounded-xl border border-stone-200 flex flex-col" x-data="{ message: '' }">

        {{-- Chat Header --}}
        <div class="flex items-center gap-3 px-5 py-4 border-b border-stone-100">
            <div class="w-9 h-9 bg-amber-100 rounded-full flex items-center justify-center text-amber-700 font-bold">J</div>
            <div>
                <p class="font-semibold text-stone-800 text-sm">Juan dela Cruz</p>
                <p class="text-xs text-green-500 flex items-center gap-1"><span class="w-1.5 h-1.5 bg-green-500 rounded-full inline-block"></span> Active now</p>
            </div>
        </div>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto p-5 space-y-4">

            {{-- AI Message --}}
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-amber-600 rounded-full flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                </div>
                <div class="bg-stone-100 rounded-2xl rounded-tl-none px-4 py-3 max-w-md">
                    <p class="text-sm text-stone-700">Hello! I'm EstateFlow AI. How can I help you today? You can ask me about property listings, lot availability, or document requirements.</p>
                    <p class="text-xs text-stone-400 mt-1">9:00 AM</p>
                </div>
            </div>

            {{-- Client Message --}}
            <div class="flex items-start gap-3 flex-row-reverse">
                <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center shrink-0 text-amber-700 font-bold text-sm">J</div>
                <div class="bg-amber-600 rounded-2xl rounded-tr-none px-4 py-3 max-w-md">
                    <p class="text-sm text-white">What is the price of Lot 12-B in Palm Residences?</p>
                    <p class="text-xs text-amber-200 mt-1">9:01 AM</p>
                </div>
            </div>

            {{-- AI Response --}}
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 bg-amber-600 rounded-full flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                </div>
                <div class="bg-stone-100 rounded-2xl rounded-tl-none px-4 py-3 max-w-md">
                    <p class="text-sm text-stone-700">Lot 12-B in Palm Residences is priced at <strong>₱1,200,000</strong>. It has an area of <strong>120 sqm</strong> and is currently <strong>reserved</strong>. Would you like to know about other available lots?</p>
                    <p class="text-xs text-stone-400 mt-1">9:01 AM</p>
                </div>
            </div>

        </div>

        {{-- Suggested Questions --}}
        <div class="px-5 py-2 border-t border-stone-100">
            <p class="text-xs text-stone-400 mb-2">Suggested questions:</p>
            <div class="flex flex-wrap gap-2">
                @foreach(['What lots are available?','How to submit documents?','What are the requirements?'] as $q)
                <button class="text-xs bg-stone-100 hover:bg-amber-50 hover:text-amber-700 text-stone-600 px-3 py-1.5 rounded-full transition">{{ $q }}</button>
                @endforeach
            </div>
        </div>

        {{-- Input --}}
        <div class="p-4 border-t border-stone-100">
            <div class="flex gap-3">
                <input x-model="message" type="text" placeholder="Type your message..." class="flex-1 border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                <button class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2.5 rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </div>
        </div>

    </div>
</div>

@endsection
