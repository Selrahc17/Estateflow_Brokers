@extends('layouts.public')
@section('title', 'About EstateFlow')

@section('content')

{{-- Hero --}}
<div class="bg-gradient-to-r from-[#112E3B] to-[#1A6B79] text-white">
    <div class="max-w-6xl mx-auto px-6 py-16 text-center">
        <p class="text-teal-500 text-xs uppercase tracking-widest font-semibold mb-2">About Us</p>
        <h1 class="text-4xl font-bold mb-4">Your Trusted Real Estate Partner</h1>
        <p class="text-stone-300 text-lg max-w-2xl mx-auto leading-relaxed">EstateFlow connects clients with licensed brokers and agents to make property ownership simple, transparent, and stress-free.</p>
        <div class="flex flex-wrap justify-center gap-4 mt-8">
            <a href="{{ route('client.properties') }}" class="bg-teal-700 hover:bg-teal-800 text-white px-6 py-3 rounded-xl font-semibold text-sm transition">Browse Properties</a>
            <a href="{{ route('client.contact') }}" class="bg-white/10 hover:bg-white/20 border border-white/20 text-white px-6 py-3 rounded-xl font-semibold text-sm transition">Contact Us</a>
        </div>
    </div>
</div>

{{-- Stats --}}
<div class="bg-white border-b border-stone-200">
    <div class="max-w-6xl mx-auto px-6 py-8 grid grid-cols-2 sm:grid-cols-4 gap-6 text-center">
        @foreach([
            ['500+','Properties Listed'],
            ['1,200+','Happy Clients'],
            ['50+','Licensed Brokers'],
            ['98%','Client Satisfaction'],
        ] as $stat)
        <div>
            <p class="text-3xl font-bold text-teal-700">{{ $stat[0] }}</p>
            <p class="text-sm text-stone-500 mt-1">{{ $stat[1] }}</p>
        </div>
        @endforeach
    </div>
</div>

{{-- How It Works --}}
<div class="max-w-6xl mx-auto px-6 py-14">
    <div class="text-center mb-10">
        <p class="text-teal-700 text-xs uppercase tracking-widest font-semibold mb-2">Process</p>
        <h2 class="text-2xl font-bold text-stone-800">How EstateFlow Works</h2>
        <p class="text-stone-400 text-sm mt-2">From browsing to ownership — we guide you every step of the way</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach([
            ['1','Browse Properties','Explore our curated listings of house & lot, lot only, and condominium properties across the Philippines.','M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z'],
            ['2','Reserve a Lot','Choose your preferred lot and submit a reservation request. Our broker will guide you through the process.','M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['3','Submit Documents','Upload your required documents through your client portal. Track approval status in real time.','M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['4','Schedule a Site Visit','Choose a convenient time to view the property with your broker before making a decision.','M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ] as $step)
        <div class="relative bg-white rounded-2xl border border-stone-200 p-6 hover:shadow-md transition">
            <div class="w-10 h-10 bg-teal-700 rounded-xl flex items-center justify-center text-white font-bold text-lg mb-4">{{ $step[0] }}</div>
            <h3 class="font-bold text-stone-800 mb-2">{{ $step[1] }}</h3>
            <p class="text-sm text-stone-500 leading-relaxed">{{ $step[2] }}</p>
        </div>
        @endforeach
    </div>
</div>

{{-- Why Choose Us --}}
<div class="bg-stone-50 border-y border-stone-200">
    <div class="max-w-6xl mx-auto px-6 py-14">
        <div class="text-center mb-10">
            <p class="text-teal-700 text-xs uppercase tracking-widest font-semibold mb-2">Why Us</p>
            <h2 class="text-2xl font-bold text-stone-800">Why Choose EstateFlow?</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach([
                ['🏆','Licensed Professionals','All our brokers and agents are PRC-licensed and verified for your peace of mind.'],
                ['🤖','AI-Powered Assistance','Our AI assistant is available 24/7 to answer your property and reservation questions.'],
                ['📱','Easy Online Access','Manage your reservations, appointments, and documents anytime from any device.'],
                ['🔒','Secure & Transparent','All transactions and documents are handled securely with full transparency.'],
                ['💬','Dedicated Support','Your assigned broker is always available to guide you through every step.'],
                ['⭐','Proven Track Record','Over 1,200 satisfied clients and counting across the Philippines.'],
            ] as $feat)
            <div class="bg-white rounded-2xl border border-stone-200 p-5 flex items-start gap-4 hover:shadow-sm transition">
                <span class="text-2xl shrink-0">{{ $feat[0] }}</span>
                <div>
                    <p class="font-semibold text-stone-800 text-sm mb-1">{{ $feat[1] }}</p>
                    <p class="text-xs text-stone-500 leading-relaxed">{{ $feat[2] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- CTA --}}
<div class="max-w-6xl mx-auto px-6 py-14 text-center">
    <h2 class="text-2xl font-bold text-stone-800 mb-3">Ready to Find Your Dream Property?</h2>
    <p class="text-stone-400 text-sm mb-6 max-w-lg mx-auto">Browse our available listings and take the first step toward owning your dream home today.</p>
    <div class="flex flex-wrap justify-center gap-4">
        <a href="{{ route('client.properties') }}" class="bg-teal-700 hover:bg-teal-800 text-white px-8 py-3 rounded-xl font-semibold text-sm transition">Browse Properties</a>
        <a href="{{ route('auth.register') }}" class="border border-teal-700 text-teal-700 hover:bg-teal-50 px-8 py-3 rounded-xl font-semibold text-sm transition">Create Account</a>
    </div>
</div>

@endsection
