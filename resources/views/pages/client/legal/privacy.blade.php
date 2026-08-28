@extends('layouts.public')
@section('title', 'Privacy Policy')

@section('content')

<div class="bg-gradient-to-r from-[#112E3B] to-[#1A6B79] text-white">
    <div class="max-w-4xl mx-auto px-6 py-12">
        <p class="text-teal-500 text-xs uppercase tracking-widest font-semibold mb-2">Legal</p>
        <h1 class="text-3xl font-bold mb-2">Privacy Policy</h1>
        <p class="text-stone-300 text-sm">Last updated: July 1, 2025</p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-6 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        {{-- Table of Contents --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-stone-200 p-5 sticky top-24">
                <p class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-3">Contents</p>
                <nav class="space-y-1.5 text-sm">
                    @foreach([
                        ['#overview','Overview'],
                        ['#collection','Data We Collect'],
                        ['#usage','How We Use It'],
                        ['#sharing','Data Sharing'],
                        ['#security','Data Security'],
                        ['#cookies','Cookies'],
                        ['#rights','Your Rights'],
                        ['#contact','Contact Us'],
                    ] as $item)
                    <a href="{{ $item[0] }}" class="block text-stone-500 hover:text-teal-700 transition py-0.5">{{ $item[1] }}</a>
                    @endforeach
                </nav>
            </div>
        </div>

        {{-- Content --}}
        <div class="lg:col-span-3 space-y-8">

            <div id="overview" class="bg-white rounded-2xl border border-stone-200 p-6">
                <h2 class="text-lg font-bold text-stone-800 mb-3 flex items-center gap-2">
                    <span class="w-7 h-7 bg-teal-100 rounded-lg flex items-center justify-center text-teal-700 text-xs font-bold">1</span>
                    Overview
                </h2>
                <p class="text-sm text-stone-500 leading-relaxed">EstateFlow ("we", "our", or "us") is committed to protecting your personal information. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our platform. By using EstateFlow, you agree to the terms of this policy.</p>
            </div>

            <div id="collection" class="bg-white rounded-2xl border border-stone-200 p-6">
                <h2 class="text-lg font-bold text-stone-800 mb-3 flex items-center gap-2">
                    <span class="w-7 h-7 bg-teal-100 rounded-lg flex items-center justify-center text-teal-700 text-xs font-bold">2</span>
                    Data We Collect
                </h2>
                <div class="space-y-4 text-sm text-stone-500">
                    <div>
                        <p class="font-semibold text-stone-700 mb-2">Personal Information</p>
                        <ul class="space-y-1.5 list-none">
                            @foreach(['Full name, email address, phone number','Date of birth and civil status','Home address and government IDs','Payment information and transaction history','Documents submitted for reservation requirements'] as $item)
                            <li class="flex items-start gap-2"><span class="w-1.5 h-1.5 bg-teal-600 rounded-full mt-1.5 shrink-0"></span>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <div>
                        <p class="font-semibold text-stone-700 mb-2">Usage Data</p>
                        <ul class="space-y-1.5">
                            @foreach(['Pages visited and features used','Device type, browser, and IP address','Search queries and property views','AI chat conversation logs'] as $item)
                            <li class="flex items-start gap-2"><span class="w-1.5 h-1.5 bg-teal-600 rounded-full mt-1.5 shrink-0"></span>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div id="usage" class="bg-white rounded-2xl border border-stone-200 p-6">
                <h2 class="text-lg font-bold text-stone-800 mb-3 flex items-center gap-2">
                    <span class="w-7 h-7 bg-teal-100 rounded-lg flex items-center justify-center text-teal-700 text-xs font-bold">3</span>
                    How We Use Your Data
                </h2>
                <ul class="space-y-2 text-sm text-stone-500">
                    @foreach([
                        'Process and manage your property reservations',
                        'Send payment reminders and transaction confirmations',
                        'Verify your identity and submitted documents',
                        'Provide AI-powered assistance and property recommendations',
                        'Improve our platform features and user experience',
                        'Comply with legal and regulatory requirements',
                        'Send important account and service notifications',
                    ] as $item)
                    <li class="flex items-start gap-2"><svg class="w-4 h-4 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>

            <div id="sharing" class="bg-white rounded-2xl border border-stone-200 p-6">
                <h2 class="text-lg font-bold text-stone-800 mb-3 flex items-center gap-2">
                    <span class="w-7 h-7 bg-teal-100 rounded-lg flex items-center justify-center text-teal-700 text-xs font-bold">4</span>
                    Data Sharing
                </h2>
                <p class="text-sm text-stone-500 leading-relaxed mb-3">We do not sell your personal data. We may share your information only with:</p>
                <div class="space-y-3">
                    @foreach([
                        ['Your Assigned Broker/Agent','To process your reservation and manage your account.'],
                        ['Payment Processors','To securely handle your payment transactions.'],
                        ['Legal Authorities','When required by law or court order.'],
                        ['Service Providers','Third-party tools that help us operate the platform (under strict confidentiality agreements).'],
                    ] as $share)
                    <div class="flex items-start gap-3 p-3 bg-stone-50 rounded-xl">
                        <div class="w-2 h-2 bg-teal-600 rounded-full mt-1.5 shrink-0"></div>
                        <div>
                            <p class="text-sm font-semibold text-stone-700">{{ $share[0] }}</p>
                            <p class="text-xs text-stone-400 mt-0.5">{{ $share[1] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div id="security" class="bg-white rounded-2xl border border-stone-200 p-6">
                <h2 class="text-lg font-bold text-stone-800 mb-3 flex items-center gap-2">
                    <span class="w-7 h-7 bg-teal-100 rounded-lg flex items-center justify-center text-teal-700 text-xs font-bold">5</span>
                    Data Security
                </h2>
                <p class="text-sm text-stone-500 leading-relaxed mb-3">We implement industry-standard security measures to protect your data:</p>
                <div class="grid grid-cols-2 gap-3">
                    @foreach(['SSL/TLS Encryption','Password Hashing (bcrypt)','Role-Based Access Control','Secure File Storage','Audit Logs','Session Management'] as $sec)
                    <div class="flex items-center gap-2 p-3 bg-green-50 rounded-xl text-sm text-green-700">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        {{ $sec }}
                    </div>
                    @endforeach
                </div>
            </div>

            <div id="cookies" class="bg-white rounded-2xl border border-stone-200 p-6">
                <h2 class="text-lg font-bold text-stone-800 mb-3 flex items-center gap-2">
                    <span class="w-7 h-7 bg-teal-100 rounded-lg flex items-center justify-center text-teal-700 text-xs font-bold">6</span>
                    Cookies
                </h2>
                <p class="text-sm text-stone-500 leading-relaxed">We use cookies and similar technologies to maintain your session, remember your preferences (such as dark mode), and analyze platform usage. You can disable cookies in your browser settings, but some features may not work properly.</p>
            </div>

            <div id="rights" class="bg-white rounded-2xl border border-stone-200 p-6">
                <h2 class="text-lg font-bold text-stone-800 mb-3 flex items-center gap-2">
                    <span class="w-7 h-7 bg-teal-100 rounded-lg flex items-center justify-center text-teal-700 text-xs font-bold">7</span>
                    Your Rights
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    @foreach([
                        ['Access','Request a copy of your personal data we hold.'],
                        ['Correction','Update or correct inaccurate information.'],
                        ['Deletion','Request deletion of your account and data.'],
                        ['Portability','Receive your data in a portable format.'],
                        ['Objection','Object to certain types of data processing.'],
                        ['Withdrawal','Withdraw consent at any time.'],
                    ] as $right)
                    <div class="p-3 bg-stone-50 rounded-xl">
                        <p class="font-semibold text-stone-700 mb-0.5">{{ $right[0] }}</p>
                        <p class="text-xs text-stone-400">{{ $right[1] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div id="contact" class="bg-white rounded-2xl border border-stone-200 p-6">
                <h2 class="text-lg font-bold text-stone-800 mb-3 flex items-center gap-2">
                    <span class="w-7 h-7 bg-teal-100 rounded-lg flex items-center justify-center text-teal-700 text-xs font-bold">8</span>
                    Contact Us
                </h2>
                <p class="text-sm text-stone-500 leading-relaxed mb-4">For privacy-related concerns or to exercise your rights, contact us:</p>
                <div class="space-y-2 text-sm text-stone-600">
                    <p class="flex items-center gap-2"><svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg> privacy@estateflow.com</p>
                    <p class="flex items-center gap-2"><svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg> Unit 5, Realty Building, Quezon City, Metro Manila</p>
                </div>
                <a href="{{ route('client.contact') }}" class="mt-4 inline-flex items-center gap-2 bg-teal-700 hover:bg-teal-800 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition">
                    Contact Us
                </a>
            </div>

        </div>
    </div>
</div>

@endsection
