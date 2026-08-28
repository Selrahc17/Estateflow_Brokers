@extends('layouts.public')
@section('title', 'Contact Us')

@section('content')

@if(session('success'))
<div class="max-w-6xl mx-auto px-6 pt-6">
    <div class="p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
</div>
@endif
@if($errors->any())
<div class="max-w-6xl mx-auto px-6 pt-6">
    <div class="p-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm">
        @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
    </div>
</div>
@endif

{{-- Hero --}}
<div class="bg-gradient-to-r from-[#112E3B] to-[#1A6B79] text-white">
    <div class="max-w-6xl mx-auto px-6 py-14 text-center">
        <p class="text-teal-500 text-xs uppercase tracking-widest font-semibold mb-2">Get In Touch</p>
        <h1 class="text-3xl font-bold mb-3">We're Here to Help</h1>
        <p class="text-stone-300 text-base max-w-xl mx-auto">Have questions about a property, your reservation, or our services? Reach out and we'll get back to you as soon as possible.</p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-6 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Contact Info --}}
        <div class="space-y-5">

            {{-- Info Cards --}}
            @foreach([
                ['📍','Our Office','Unit 5, Realty Building, Quezon City, Metro Manila, Philippines',''],
                ['📞','Phone','(02) 8123-4567','+63 912 345 6789'],
                ['📧','Email','info@estateflow.com','support@estateflow.com'],
                ['🕐','Office Hours','Mon – Fri: 8:00 AM – 6:00 PM','Sat: 9:00 AM – 3:00 PM'],
            ] as $info)
            <div class="bg-white rounded-2xl border border-stone-200 p-5 flex items-start gap-4">
                <span class="text-2xl shrink-0">{{ $info[0] }}</span>
                <div>
                    <p class="font-semibold text-stone-800 text-sm mb-1">{{ $info[1] }}</p>
                    <p class="text-sm text-stone-500">{{ $info[2] }}</p>
                    @if($info[3])<p class="text-sm text-stone-500">{{ $info[3] }}</p>@endif
                </div>
            </div>
            @endforeach

            {{-- Social Links --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <p class="font-semibold text-stone-800 text-sm mb-3">Follow Us</p>
                <div class="flex gap-3">
                    @foreach([['Facebook','M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z'],['Instagram','M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zm1.5-4.87h.01M6.5 19.5h11a3 3 0 003-3v-11a3 3 0 00-3-3h-11a3 3 0 00-3 3v11a3 3 0 003 3z']] as $social)
                    <a href="#" class="w-10 h-10 bg-stone-100 hover:bg-teal-100 hover:text-teal-700 text-stone-500 rounded-xl flex items-center justify-center transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $social[1] }}"/></svg>
                    </a>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Contact Form --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-stone-200 p-8">
            <h2 class="font-bold text-stone-800 text-xl mb-1">Send Us a Message</h2>
            <p class="text-stone-400 text-sm mb-6">Fill out the form and we'll respond within 24 hours.</p>

            <form action="{{ route('client.contact.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">First Name</label>
                        <input type="text" name="first_name" value="{{ old('first_name') }}" placeholder="Juan" required class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Last Name</label>
                        <input type="text" name="last_name" value="{{ old('last_name') }}" placeholder="dela Cruz" required class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="juan@email.com" required class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+63 912 345 6789" class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Subject</label>
                    <select name="subject" required class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 text-stone-600">
                        <option value="">Select a topic</option>
                        <option>Property Inquiry</option>
                        <option>Reservation Question</option>
                        <option>Document Submission</option>
                        <option>General Inquiry</option>
                        <option>Complaint / Feedback</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Message</label>
                    <textarea name="message" rows="5" placeholder="Tell us how we can help you..." required class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 resize-none">{{ old('message') }}</textarea>
                </div>
                <button type="submit" class="w-full bg-teal-700 hover:bg-teal-800 text-white py-3.5 rounded-xl font-semibold text-sm transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Send Message
                </button>
            </form>
        </div>

    </div>
</div>

@endsection
