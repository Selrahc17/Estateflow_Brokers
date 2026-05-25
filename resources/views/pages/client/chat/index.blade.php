@extends('layouts.public')
@section('title', 'AI Assistant')

@section('content')

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6" style="height: calc(100vh - 180px);">

        {{-- Left: Info Panel --}}
        <div class="hidden xl:flex flex-col gap-4">

            {{-- AI Info --}}
            <div class="bg-gradient-to-br from-stone-800 to-amber-900 rounded-2xl p-5 text-white">
                <div class="w-12 h-12 bg-amber-600 rounded-xl flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                </div>
                <p class="font-bold text-base mb-1">EstateFlow AI</p>
                <p class="text-stone-300 text-xs leading-relaxed">Your 24/7 assistant for property inquiries, payment schedules, and document guidance.</p>
                <div class="flex items-center gap-2 mt-3">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                    <span class="text-xs text-green-400 font-medium">Online now</span>
                </div>
            </div>

            {{-- Quick Topics --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-4 flex-1">
                <p class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-3">Quick Topics</p>
                <div class="space-y-1.5">
                    @foreach([
                        ['Payment Schedule','M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                        ['Document Requirements','M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['Reservation Details','M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['Lot Information','M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7'],
                        ['Contact Broker','M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z'],
                    ] as $topic)
                    <button class="w-full flex items-center gap-2.5 p-2.5 rounded-xl text-left hover:bg-amber-50 hover:text-amber-700 text-stone-600 transition group">
                        <svg class="w-4 h-4 shrink-0 text-stone-300 group-hover:text-amber-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $topic[1] }}"/></svg>
                        <span class="text-sm font-medium">{{ $topic[0] }}</span>
                    </button>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Chat Window --}}
        <div class="xl:col-span-3 bg-white rounded-2xl border border-stone-200 flex flex-col overflow-hidden" x-data="{ message: '' }">

            {{-- Chat Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100 bg-stone-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-stone-800 text-sm">EstateFlow AI Assistant</p>
                        <p class="text-xs text-green-500 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full inline-block animate-pulse"></span>
                            Online · Typically replies instantly
                        </p>
                    </div>
                </div>
                <button class="w-8 h-8 flex items-center justify-center rounded-lg text-stone-400 hover:bg-stone-200 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"/></svg>
                </button>
            </div>

            {{-- Messages --}}
            <div class="flex-1 overflow-y-auto p-5 space-y-5 bg-stone-50/50">

                {{-- Date Divider --}}
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-px bg-stone-200"></div>
                    <span class="text-xs text-stone-400 shrink-0">Today</span>
                    <div class="flex-1 h-px bg-stone-200"></div>
                </div>

                {{-- AI Message --}}
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-amber-600 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                    </div>
                    <div class="max-w-sm">
                        <div class="bg-white border border-stone-200 rounded-2xl rounded-tl-none px-4 py-3 shadow-sm">
                            <p class="text-sm text-stone-700">Hi Juan! 👋 I'm your EstateFlow AI Assistant. How can I help you today?</p>
                            <ul class="text-sm text-stone-500 mt-2 space-y-1">
                                <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-amber-400 rounded-full shrink-0"></span>Payment schedules & due dates</li>
                                <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-amber-400 rounded-full shrink-0"></span>Document requirements</li>
                                <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-amber-400 rounded-full shrink-0"></span>Reservation details</li>
                                <li class="flex items-center gap-2"><span class="w-1.5 h-1.5 bg-amber-400 rounded-full shrink-0"></span>General property inquiries</li>
                            </ul>
                        </div>
                        <p class="text-xs text-stone-400 mt-1 ml-1">9:00 AM</p>
                    </div>
                </div>

                {{-- Client Message --}}
                <div class="flex items-start gap-3 flex-row-reverse">
                    <div class="w-8 h-8 bg-amber-100 rounded-xl flex items-center justify-center shrink-0 text-amber-700 font-bold text-sm">J</div>
                    <div class="max-w-sm">
                        <div class="bg-amber-600 rounded-2xl rounded-tr-none px-4 py-3">
                            <p class="text-sm text-white">When is my next payment due?</p>
                        </div>
                        <p class="text-xs text-stone-400 mt-1 text-right mr-1">9:01 AM</p>
                    </div>
                </div>

                {{-- AI Response --}}
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-amber-600 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h16a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                    </div>
                    <div class="max-w-sm">
                        <div class="bg-white border border-stone-200 rounded-2xl rounded-tl-none px-4 py-3 shadow-sm">
                            <p class="text-sm text-stone-700">Your next payment is due on <strong class="text-stone-800">July 15, 2025</strong>.</p>
                            <div class="mt-2 p-3 bg-amber-50 rounded-xl border border-amber-100">
                                <p class="text-xs text-stone-500">Amount Due</p>
                                <p class="text-base font-bold text-amber-600">₱50,000</p>
                                <p class="text-xs text-stone-400">Monthly Installment — Lot 12-B</p>
                            </div>
                            <p class="text-sm text-stone-600 mt-2">Would you like me to set a reminder 3 days before the due date?</p>
                        </div>
                        <p class="text-xs text-stone-400 mt-1 ml-1">9:01 AM</p>
                    </div>
                </div>

            </div>

            {{-- Quick Replies --}}
            <div class="px-5 py-3 border-t border-stone-100 bg-white">
                <p class="text-xs text-stone-400 mb-2">Suggested questions:</p>
                <div class="flex flex-wrap gap-2">
                    @foreach(['What documents am I missing?','How much is my balance?','Show my lot details','How to pay online?','Contact my broker']) as $q)
                    <button class="text-xs bg-stone-100 hover:bg-amber-50 hover:text-amber-700 text-stone-600 px-3 py-1.5 rounded-full transition border border-transparent hover:border-amber-200">{{ $q }}</button>
                    @endforeach
                </div>
            </div>

            {{-- Input --}}
            <div class="p-4 border-t border-stone-100 bg-white">
                <div class="flex gap-3 items-end">
                    <div class="flex-1 border border-stone-200 rounded-2xl px-4 py-3 focus-within:ring-2 focus-within:ring-amber-400 focus-within:border-transparent transition bg-stone-50">
                        <input x-model="message" type="text" placeholder="Ask me anything about your reservation..."
                            class="w-full bg-transparent text-sm outline-none text-stone-700 placeholder:text-stone-400">
                    </div>
                    <button class="w-11 h-11 bg-amber-600 hover:bg-amber-700 text-white rounded-2xl flex items-center justify-center transition shrink-0 shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
