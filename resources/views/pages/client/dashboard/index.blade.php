@extends('layouts.client')
@section('title', 'My Portal')

@section('content')

{{-- Hero Section --}}
<section class="bg-gradient-to-br from-stone-900 to-amber-900 text-white">
    <div class="max-w-6xl mx-auto px-6 py-16 flex flex-col md:flex-row items-center justify-between gap-10">
        <div>
            <p class="text-amber-400 text-sm font-medium mb-2 uppercase tracking-widest">Welcome back</p>
            <h1 class="text-4xl font-bold leading-tight mb-3">Hello, Juan! 👋</h1>
            <p class="text-stone-300 text-lg mb-6">Here's an overview of your reservation at <span class="text-amber-400 font-semibold">Palm Residences</span>.</p>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('client.reservation') }}" class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition">
                    View My Reservation
                </a>
                <a href="{{ route('client.chat') }}" class="bg-white/10 hover:bg-white/20 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition">
                    Ask AI Assistant
                </a>
            </div>
        </div>
        <div class="bg-white/10 rounded-2xl p-6 min-w-64 backdrop-blur-sm border border-white/10">
            <p class="text-amber-300 text-xs uppercase tracking-widest mb-3">Reservation Summary</p>
            <div class="space-y-3">
                @foreach([
                    ['Property','Palm Residences'],
                    ['Lot','12-B, Block 1'],
                    ['Area','120 sqm'],
                    ['Status','Active'],
                ] as $d)
                <div class="flex justify-between text-sm">
                    <span class="text-stone-400">{{ $d[0] }}</span>
                    <span class="text-white font-medium">{{ $d[1] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- Status Cards --}}
<section class="max-w-6xl mx-auto px-6 -mt-6">
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
            ['Total Paid','₱300,000','text-green-600','bg-green-50 border-green-100'],
            ['Remaining Balance','₱900,000','text-red-500','bg-red-50 border-red-100'],
            ['Next Due','Jul 15, 2025','text-amber-600','bg-amber-50 border-amber-100'],
            ['Documents','4 of 6','text-blue-600','bg-blue-50 border-blue-100'],
        ] as $s)
        <div class="bg-white rounded-xl border {{ $s[3] }} p-4 shadow-sm">
            <p class="text-xs text-stone-500 mb-1">{{ $s[0] }}</p>
            <p class="text-lg font-bold {{ $s[2] }}">{{ $s[1] }}</p>
        </div>
        @endforeach
    </div>
</section>

{{-- Main Content --}}
<section class="max-w-6xl mx-auto px-6 py-10 grid grid-cols-1 lg:grid-cols-3 gap-8">

    {{-- Left: Upcoming Payment + Recent Notifications --}}
    <div class="lg:col-span-2 space-y-8">

        {{-- Upcoming Payment --}}
        <div>
            <h2 class="text-lg font-bold text-stone-800 mb-4">Upcoming Payment</h2>
            <div class="bg-white rounded-2xl border border-stone-200 p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-amber-100 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div>
                        <p class="font-semibold text-stone-800 text-lg">₱50,000</p>
                        <p class="text-sm text-stone-500">Monthly Installment — July 2025</p>
                        <p class="text-xs text-amber-600 font-medium mt-1">Due: July 15, 2025</p>
                    </div>
                </div>
                <a href="{{ route('client.payments') }}" class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition whitespace-nowrap">
                    Pay Now
                </a>
            </div>
        </div>

        {{-- Payment History Preview --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-stone-800">Recent Payments</h2>
                <a href="{{ route('client.payments') }}" class="text-sm text-amber-600 hover:underline">View all</a>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
                @foreach([
                    ['June 2025','₱50,000','Jun 10, 2025','Paid'],
                    ['May 2025','₱50,000','May 12, 2025','Paid'],
                    ['April 2025','₱50,000','Apr 8, 2025','Paid'],
                ] as $p)
                <div class="flex items-center justify-between px-6 py-4 border-b border-stone-100 last:border-0 hover:bg-stone-50 transition">
                    <div>
                        <p class="text-sm font-medium text-stone-700">{{ $p[0] }}</p>
                        <p class="text-xs text-stone-400">{{ $p[2] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-stone-700">{{ $p[1] }}</p>
                        <span class="text-xs text-green-600 font-medium">{{ $p[3] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Document Status --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-stone-800">Document Requirements</h2>
                <a href="{{ route('client.documents') }}" class="text-sm text-amber-600 hover:underline">Upload</a>
            </div>
            <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
                @foreach([
                    ['Valid ID','Approved','green'],
                    ['Proof of Income','Approved','green'],
                    ['Birth Certificate','Approved','green'],
                    ['TIN Certificate','Missing — Upload required','red'],
                    ['Marriage Certificate','Approved','green'],
                    ['Bank Statement','Pending Review','yellow'],
                ] as $d)
                <div class="flex items-center justify-between px-6 py-3.5 border-b border-stone-100 last:border-0 hover:bg-stone-50 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-2 h-2 rounded-full shrink-0
                            {{ $d[2]==='green' ? 'bg-green-500' : '' }}
                            {{ $d[2]==='yellow' ? 'bg-yellow-500' : '' }}
                            {{ $d[2]==='red' ? 'bg-red-500' : '' }}">
                        </div>
                        <p class="text-sm text-stone-700">{{ $d[0] }}</p>
                    </div>
                    <span class="text-xs font-medium
                        {{ $d[2]==='green' ? 'text-green-600' : '' }}
                        {{ $d[2]==='yellow' ? 'text-yellow-600' : '' }}
                        {{ $d[2]==='red' ? 'text-red-500' : '' }}">
                        {{ $d[1] }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- Right: Notifications + AI Chat CTA --}}
    <div class="space-y-6">

        {{-- Notifications --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-stone-800">Notifications</h2>
                <a href="{{ route('client.notifications') }}" class="text-sm text-amber-600 hover:underline">See all</a>
            </div>
            <div class="space-y-3">
                @foreach([
                    ['Payment Reminder','July installment due on Jul 15.','yellow','2h ago',true],
                    ['Missing Document','TIN Certificate not yet uploaded.','red','5h ago',true],
                    ['Payment Confirmed','June payment received. ✓','green','Jun 10',false],
                ] as $n)
                <div class="bg-white rounded-xl border border-stone-200 p-4 {{ $n[4] ? 'border-l-4 border-l-amber-400' : '' }}">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-start gap-2">
                            <div class="w-2 h-2 rounded-full mt-1.5 shrink-0
                                {{ $n[2]==='yellow' ? 'bg-yellow-500' : '' }}
                                {{ $n[2]==='red' ? 'bg-red-500' : '' }}
                                {{ $n[2]==='green' ? 'bg-green-500' : '' }}">
                            </div>
                            <div>
                                <p class="text-sm font-medium text-stone-700">{{ $n[0] }}</p>
                                <p class="text-xs text-stone-400 mt-0.5">{{ $n[1] }}</p>
                            </div>
                        </div>
                        <span class="text-xs text-stone-400 shrink-0">{{ $n[3] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- AI Chat CTA --}}
        <div class="bg-gradient-to-br from-stone-800 to-amber-900 rounded-2xl p-6 text-white">
            <div class="w-12 h-12 bg-amber-600 rounded-xl flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            </div>
            <h3 class="font-bold text-lg mb-2">Have a question?</h3>
            <p class="text-stone-300 text-sm mb-4">Our AI Assistant is available 24/7 to answer your inquiries about payments, documents, and your reservation.</p>
            <a href="{{ route('client.chat') }}" class="block w-full text-center bg-amber-600 hover:bg-amber-700 text-white py-2.5 rounded-xl text-sm font-medium transition">
                Chat with AI Now
            </a>
        </div>

        {{-- Broker Contact --}}
        <div class="bg-white rounded-2xl border border-stone-200 p-5">
            <h3 class="font-semibold text-stone-800 mb-3">Your Broker</h3>
            <div class="flex items-center gap-3 mb-4">
                <div class="w-11 h-11 bg-amber-600 rounded-full flex items-center justify-center text-white font-bold">B</div>
                <div>
                    <p class="font-medium text-stone-800 text-sm">Broker Name</p>
                    <p class="text-xs text-stone-400">Licensed Real Estate Broker</p>
                </div>
            </div>
            <a href="{{ route('client.chat') }}" class="block w-full text-center border border-amber-600 text-amber-600 hover:bg-amber-50 py-2 rounded-xl text-sm font-medium transition">
                Send a Message
            </a>
        </div>

    </div>

</section>

@endsection
