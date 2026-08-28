@extends('layouts.public')
@section('title', 'Inquiry Sent')

@section('content')

<div class="max-w-lg mx-auto px-6 py-16 text-center">

    <div class="w-24 h-24 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-6">
        <svg class="w-12 h-12 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
    </div>

    <h1 class="text-2xl font-bold text-stone-800 mb-2">Inquiry Sent! 📬</h1>
    <p class="text-stone-400 text-sm mb-8 leading-relaxed">
        Thank you for your interest in <span class="font-semibold text-stone-600">Palm Residences</span>. Your inquiry has been received and our broker will get back to you within 24 hours.
    </p>

    {{-- Confirmation Card --}}
    <div class="bg-white rounded-2xl border border-stone-200 p-6 text-left mb-6">
        <p class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-4">Inquiry Details</p>
        <div class="space-y-3 text-sm">
            @foreach([
                ['Reference No.','INQ-2025-07-00456'],
                ['Property','Palm Residences'],
                ['Location','Quezon City, Metro Manila'],
                ['Submitted','July 10, 2025 · 3:15 PM'],
                ['Response Time','Within 24 hours'],
            ] as $row)
            <div class="flex justify-between">
                <span class="text-stone-400">{{ $row[0] }}</span>
                <span class="font-medium text-stone-700">{{ $row[1] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- What's Next --}}
    <div class="bg-stone-50 rounded-2xl p-5 text-left mb-6">
        <p class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-3">What Happens Next?</p>
        <div class="space-y-3">
            @foreach([
                ['1','Broker Review','Your inquiry is reviewed by our licensed broker.'],
                ['2','Broker Contact','The broker will contact you via email or phone.'],
                ['3','Property Visit','Schedule a site visit to view the property.'],
                ['4','Reservation','Reserve your preferred lot when ready.'],
            ] as $step)
            <div class="flex items-start gap-3">
                <div class="w-6 h-6 bg-teal-700 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0">{{ $step[0] }}</div>
                <div>
                    <p class="text-sm font-semibold text-stone-700">{{ $step[1] }}</p>
                    <p class="text-xs text-stone-400">{{ $step[2] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <a href="{{ route('client.properties') }}" class="flex-1 border border-stone-200 text-stone-600 hover:bg-stone-50 py-3 rounded-xl text-sm font-semibold transition text-center">
            Browse More Properties
        </a>
        <a href="{{ route('auth.register') }}" class="flex-1 bg-teal-700 hover:bg-teal-800 text-white py-3 rounded-xl text-sm font-semibold transition text-center">
            Create an Account
        </a>
    </div>

    <p class="mt-4 text-xs text-stone-400">
        Have questions?
        <a href="{{ route('client.contact') }}" class="text-teal-700 hover:underline">Contact us</a>
    </p>

</div>

@endsection
