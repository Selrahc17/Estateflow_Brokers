@extends('layouts.public')
@section('title', 'Terms of Use')

@section('content')

<div class="bg-gradient-to-r from-[#112E3B] to-[#1A6B79] text-white">
    <div class="max-w-4xl mx-auto px-6 py-12">
        <p class="text-teal-500 text-xs uppercase tracking-widest font-semibold mb-2">Legal</p>
        <h1 class="text-3xl font-bold mb-2">Terms of Use</h1>
        <p class="text-stone-300 text-sm">Last updated: July 1, 2025</p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-6 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        {{-- TOC --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-stone-200 p-5 sticky top-24">
                <p class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-3">Contents</p>
                <nav class="space-y-1.5 text-sm">
                    @foreach([
                        ['#acceptance','Acceptance'],
                        ['#use','Permitted Use'],
                        ['#account','Your Account'],
                        ['#reservations','Reservations'],
                        ['#payments','Payments'],
                        ['#content','User Content'],
                        ['#liability','Liability'],
                        ['#termination','Termination'],
                        ['#changes','Changes'],
                    ] as $item)
                    <a href="{{ $item[0] }}" class="block text-stone-500 hover:text-teal-700 transition py-0.5">{{ $item[1] }}</a>
                    @endforeach
                </nav>
            </div>
        </div>

        {{-- Content --}}
        <div class="lg:col-span-3 space-y-6">

            @php
            $sections = [
                ['acceptance','1','Acceptance of Terms','By accessing or using EstateFlow, you confirm that you are at least 18 years old, have read and understood these Terms, and agree to be bound by them. If you do not agree, please do not use our platform.'],
                ['use','2','Permitted Use','You may use EstateFlow only for lawful purposes related to browsing property listings, managing reservations, submitting documents, and communicating with brokers. You may not use the platform to engage in fraudulent activity, impersonate others, upload malicious content, or violate any applicable laws.'],
                ['account','3','Your Account','You are responsible for maintaining the confidentiality of your account credentials. You agree to notify us immediately of any unauthorized access. EstateFlow is not liable for any loss resulting from unauthorized use of your account.'],
                ['reservations','4','Reservations','Submitting a reservation request does not guarantee ownership of a property. Reservations are subject to availability, document verification, and broker approval. EstateFlow acts as a platform connecting clients with brokers and is not a party to the reservation agreement.'],
                ['payments','5','Payments','All payment transactions are processed securely. Payment schedules are determined by the broker and property developer. Late payments may result in penalties as specified in your reservation agreement. EstateFlow is not responsible for disputes between clients and brokers regarding payments.'],
                ['content','6','User Content','By uploading documents or submitting information, you grant EstateFlow a limited license to use that content solely for the purpose of providing our services. You represent that you have the right to submit such content and that it does not violate any third-party rights.'],
                ['liability','7','Limitation of Liability','EstateFlow provides the platform "as is" without warranties of any kind. We are not liable for any indirect, incidental, or consequential damages arising from your use of the platform, including but not limited to loss of data, property disputes, or financial losses.'],
                ['termination','8','Termination','We reserve the right to suspend or terminate your account at any time for violation of these Terms, fraudulent activity, or any other reason at our discretion. You may also deactivate your account at any time through your profile settings.'],
                ['changes','9','Changes to Terms','We may update these Terms from time to time. We will notify you of significant changes via email or platform notification. Continued use of EstateFlow after changes constitutes acceptance of the updated Terms.'],
            ];
            @endphp

            @foreach($sections as $s)
            <div id="{{ $s[0] }}" class="bg-white rounded-2xl border border-stone-200 p-6">
                <h2 class="text-lg font-bold text-stone-800 mb-3 flex items-center gap-2">
                    <span class="w-7 h-7 bg-teal-100 rounded-lg flex items-center justify-center text-teal-700 text-xs font-bold">{{ $s[1] }}</span>
                    {{ $s[2] }}
                </h2>
                <p class="text-sm text-stone-500 leading-relaxed">{{ $s[3] }}</p>
            </div>
            @endforeach

            {{-- Agreement --}}
            <div class="bg-teal-50 border border-teal-200 rounded-2xl p-6 text-center">
                <p class="text-sm text-teal-800 font-semibold mb-1">By using EstateFlow, you agree to these Terms of Use.</p>
                <p class="text-xs text-teal-700 mb-4">If you have questions, please contact our support team.</p>
                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('client.contact') }}" class="bg-teal-700 hover:bg-teal-800 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition">Contact Support</a>
                    <a href="{{ route('client.legal.privacy') }}" class="border border-teal-700 text-teal-700 hover:bg-teal-50 px-6 py-2.5 rounded-xl text-sm font-semibold transition">Privacy Policy</a>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
