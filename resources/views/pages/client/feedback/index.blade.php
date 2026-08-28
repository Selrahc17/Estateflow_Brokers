@extends('layouts.public')
@section('title', 'Feedback & Ratings')

@section('content')

{{-- Page Hero --}}
<div class="bg-gradient-to-r from-[#112E3B] to-[#1A6B79] text-white">
    <div class="max-w-6xl mx-auto px-6 py-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <p class="text-teal-500 text-xs uppercase tracking-widest font-semibold mb-1">Feedback & Ratings</p>
            <h1 class="text-2xl font-bold">Share Your Experience</h1>
            <p class="text-stone-300 text-sm mt-1">Your feedback helps us improve our service for everyone</p>
        </div>
        <div class="flex items-center gap-3 bg-white/10 px-5 py-3 rounded-2xl border border-white/10">
            <div class="text-center">
                <p class="text-3xl font-bold text-teal-500">4.8</p>
                <div class="flex gap-0.5 justify-center my-1">
                    @for($i=1;$i<=5;$i++)
                    <svg class="w-3.5 h-3.5 text-teal-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    @endfor
                </div>
                <p class="text-xs text-stone-400">128 reviews</p>
            </div>
        </div>
    </div>
</div>

{{-- Stats Bar --}}
<div class="bg-white border-b border-stone-200">
    <div class="max-w-6xl mx-auto px-6 py-4 grid grid-cols-2 sm:grid-cols-4 gap-4">
        @foreach([
            ['128','Total Reviews','text-stone-800'],
            ['94%','Recommend Us','text-green-600'],
            ['4.9','Broker Rating','text-teal-700'],
            ['4.7','Process Rating','text-blue-600'],
        ] as $s)
        <div class="text-center">
            <p class="text-xl font-bold {{ $s[2] }}">{{ $s[0] }}</p>
            <p class="text-xs text-stone-400">{{ $s[1] }}</p>
        </div>
        @endforeach
    </div>
</div>

<div class="max-w-6xl mx-auto px-6 py-8">
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Left: Feedback Form --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- Overall Rating --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-6" x-data="{ rating: 0, hover: 0 }">
                <h2 class="font-semibold text-stone-800 mb-1">Overall Experience</h2>
                <p class="text-sm text-stone-400 mb-5">How would you rate your overall experience with EstateFlow?</p>
                <div class="flex items-center gap-2 mb-3">
                    @for($i = 1; $i <= 5; $i++)
                    <button @mouseenter="hover = {{ $i }}" @mouseleave="hover = 0" @click="rating = {{ $i }}" class="transition-transform hover:scale-110">
                        <svg class="w-10 h-10 transition-colors duration-150" :class="(hover || rating) >= {{ $i }} ? 'text-teal-500' : 'text-stone-200'" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                    </button>
                    @endfor
                    <span class="ml-3 text-base font-semibold text-stone-600" x-text="['','😞 Poor','😐 Fair','🙂 Good','😊 Very Good','🤩 Excellent'][rating] || 'Tap to rate'"></span>
                </div>
                <div class="flex justify-between text-xs text-stone-300 px-1">
                    <span>Poor</span><span>Excellent</span>
                </div>
            </div>

            {{-- Category Ratings --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h2 class="font-semibold text-stone-800 mb-5">Rate by Category</h2>
                <div class="space-y-5">
                    @foreach([
                        ['🏠','Broker / Agent Service','How satisfied are you with your broker\'s service?'],
                        ['📋','Property Information','Was the property information accurate and complete?'],
                        ['📅','Reservation Process','How smooth was the reservation process?'],
                        ['💳','Payment Experience','How easy was it to manage your payments?'],
                        ['📄','Document Handling','How well were your documents managed?'],
                        ['🤖','AI Assistant','How helpful was the AI Assistant?'],
                    ] as $i => $cat)
                    <div x-data="{ rating: 0, hover: 0 }" class="{{ $i < 5 ? 'pb-5 border-b border-stone-100' : '' }}">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <span class="text-lg">{{ $cat[0] }}</span>
                                <div>
                                    <p class="text-sm font-semibold text-stone-700">{{ $cat[1] }}</p>
                                    <p class="text-xs text-stone-400">{{ $cat[2] }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold text-teal-700 shrink-0 ml-2" x-text="['','Poor','Fair','Good','Very Good','Excellent'][rating] || ''"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            @for($j = 1; $j <= 5; $j++)
                            <button @mouseenter="hover = {{ $j }}" @mouseleave="hover = 0" @click="rating = {{ $j }}" class="transition-transform hover:scale-110">
                                <svg class="w-7 h-7 transition-colors duration-150" :class="(hover || rating) >= {{ $j }} ? 'text-teal-500' : 'text-stone-200'" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </button>
                            @endfor
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Written Feedback --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-6" x-data="{ chars: 0, selected: [] }">
                <h2 class="font-semibold text-stone-800 mb-1">Written Feedback</h2>
                <p class="text-sm text-stone-400 mb-5">Tell us more about your experience</p>

                {{-- What went well --}}
                <div class="mb-5">
                    <p class="text-xs font-semibold text-stone-600 uppercase tracking-widest mb-3">✅ What went well?</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['Fast Response','Professional Broker','Easy Process','Clear Communication','Good Property Info','Helpful AI','Smooth Payment','Quick Document Review','Friendly Staff','Transparent Pricing'] as $tag)
                        <button
                            @click="selected.includes('{{ $tag }}') ? selected.splice(selected.indexOf('{{ $tag }}'), 1) : selected.push('{{ $tag }}')"
                            :class="selected.includes('{{ $tag }}') ? 'bg-teal-700 text-white border-teal-700' : 'bg-white text-stone-600 border-stone-200 hover:border-teal-500 hover:text-teal-700'"
                            class="text-xs px-3 py-1.5 rounded-full border font-medium transition">
                            {{ $tag }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- What needs improvement --}}
                <div class="mb-5" x-data="{ issues: [] }">
                    <p class="text-xs font-semibold text-stone-600 uppercase tracking-widest mb-3">⚠️ What can be improved?</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach(['Faster Response','More Property Options','Clearer Payment Terms','Better Document Guidance','More AI Features','Easier Navigation','More Updates'] as $issue)
                        <button
                            @click="issues.includes('{{ $issue }}') ? issues.splice(issues.indexOf('{{ $issue }}'), 1) : issues.push('{{ $issue }}')"
                            :class="issues.includes('{{ $issue }}') ? 'bg-red-500 text-white border-red-500' : 'bg-white text-stone-600 border-stone-200 hover:border-red-300 hover:text-red-500'"
                            class="text-xs px-3 py-1.5 rounded-full border font-medium transition">
                            {{ $issue }}
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Comment --}}
                <div>
                    <p class="text-xs font-semibold text-stone-600 uppercase tracking-widest mb-2">💬 Your Comments</p>
                    <textarea
                        @input="chars = $event.target.value.length"
                        rows="4"
                        maxlength="500"
                        placeholder="Share your experience with EstateFlow. What did you like? What can we improve?"
                        class="w-full border border-stone-200 rounded-xl px-4 py-3 text-sm text-stone-700 focus:outline-none focus:ring-2 focus:ring-teal-400 resize-none placeholder:text-stone-300">
                    </textarea>
                    <div class="flex justify-between items-center mt-1">
                        <p class="text-xs text-stone-400">Be honest — your feedback is anonymous</p>
                        <p class="text-xs text-stone-400"><span x-text="chars"></span>/500</p>
                    </div>
                </div>
            </div>

            {{-- Recommendation --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-6" x-data="{ recommend: null }">
                <h2 class="font-semibold text-stone-800 mb-1">Would you recommend EstateFlow?</h2>
                <p class="text-sm text-stone-400 mb-4">Would you recommend our services to friends or family?</p>
                <div class="grid grid-cols-3 gap-3">
                    <button @click="recommend = 'yes'"
                        :class="recommend === 'yes' ? 'bg-green-600 text-white border-green-600 scale-105' : 'bg-white text-stone-600 border-stone-200 hover:border-green-400'"
                        class="flex flex-col items-center justify-center gap-2 py-4 rounded-2xl border font-medium text-sm transition">
                        <span class="text-2xl">👍</span>
                        <span>Yes, definitely!</span>
                    </button>
                    <button @click="recommend = 'maybe'"
                        :class="recommend === 'maybe' ? 'bg-teal-600 text-white border-teal-600 scale-105' : 'bg-white text-stone-600 border-stone-200 hover:border-teal-500'"
                        class="flex flex-col items-center justify-center gap-2 py-4 rounded-2xl border font-medium text-sm transition">
                        <span class="text-2xl">🤔</span>
                        <span>Maybe</span>
                    </button>
                    <button @click="recommend = 'no'"
                        :class="recommend === 'no' ? 'bg-red-500 text-white border-red-500 scale-105' : 'bg-white text-stone-600 border-stone-200 hover:border-red-400'"
                        class="flex flex-col items-center justify-center gap-2 py-4 rounded-2xl border font-medium text-sm transition">
                        <span class="text-2xl">👎</span>
                        <span>Not really</span>
                    </button>
                </div>
            </div>

            {{-- Photo Upload --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h2 class="font-semibold text-stone-800 mb-1">Add Photos <span class="text-xs text-stone-400 font-normal ml-1">Optional</span></h2>
                <p class="text-sm text-stone-400 mb-4">Share photos of your property visit or experience</p>
                <div class="flex gap-3 flex-wrap">
                    @for($p = 0; $p < 4; $p++)
                    <div class="w-20 h-20 border-2 border-dashed border-stone-200 rounded-xl flex items-center justify-center hover:border-teal-500 cursor-pointer transition group">
                        <svg class="w-6 h-6 text-stone-300 group-hover:text-teal-500 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    @endfor
                    <p class="w-full text-xs text-stone-400 mt-1">JPG, PNG up to 5MB each · Max 4 photos</p>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <button class="flex-1 bg-teal-700 hover:bg-teal-800 text-white py-3.5 rounded-2xl font-semibold text-sm transition flex items-center justify-center gap-2 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Submit Feedback
                </button>
                <button class="sm:w-40 border border-stone-200 text-stone-500 hover:bg-stone-50 py-3.5 rounded-2xl font-medium text-sm transition">
                    Save as Draft
                </button>
            </div>

        </div>

        {{-- Right Sidebar --}}
        <div class="space-y-5">

            {{-- Your Previous Feedback --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <h3 class="font-semibold text-stone-800 mb-4">Your Previous Feedback</h3>
                <div class="space-y-3">
                    @foreach([
                        ['Broker Service','5','Jul 1, 2025','Great experience overall! Very professional.'],
                        ['Property Visit','4','Jun 15, 2025','Very informative site visit.'],
                    ] as $prev)
                    <div class="p-3 bg-stone-50 rounded-xl border border-stone-100">
                        <div class="flex items-center justify-between mb-1.5">
                            <p class="text-sm font-semibold text-stone-700">{{ $prev[0] }}</p>
                            <div class="flex items-center gap-0.5">
                                @for($s = 1; $s <= 5; $s++)
                                <svg class="w-3 h-3 {{ $s <= (int)$prev[1] ? 'text-teal-500' : 'text-stone-200' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                @endfor
                            </div>
                        </div>
                        <p class="text-xs text-stone-500 italic">"{{ $prev[3] }}"</p>
                        <p class="text-xs text-stone-400 mt-1">{{ $prev[2] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Overall Ratings --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <h3 class="font-semibold text-stone-800 mb-4">Overall Ratings</h3>
                <div class="text-center mb-4">
                    <p class="text-5xl font-bold text-teal-600">4.8</p>
                    <div class="flex items-center justify-center gap-1 my-2">
                        @for($s = 1; $s <= 5; $s++)
                        <svg class="w-5 h-5 text-teal-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        @endfor
                    </div>
                    <p class="text-xs text-stone-400">Based on 128 reviews</p>
                </div>
                <div class="space-y-2">
                    @foreach([[5,78],[4,32],[3,12],[2,4],[1,2]] as $bar)
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-stone-500 w-3 shrink-0">{{ $bar[0] }}</span>
                        <svg class="w-3 h-3 text-teal-500 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <div class="flex-1 bg-stone-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-teal-500 h-2 rounded-full" style="width: {{ round($bar[1]/128*100) }}%"></div>
                        </div>
                        <span class="text-stone-400 w-5 text-right shrink-0">{{ $bar[1] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Category Scores --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <h3 class="font-semibold text-stone-800 mb-4">Category Scores</h3>
                <div class="space-y-3">
                    @foreach([
                        ['Broker Service','4.9'],
                        ['Property Info','4.7'],
                        ['Reservation','4.8'],
                        ['Payments','4.6'],
                        ['Documents','4.5'],
                        ['AI Assistant','4.8'],
                    ] as $cat)
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-stone-600">{{ $cat[0] }}</span>
                        <div class="flex items-center gap-2">
                            <div class="w-20 bg-stone-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-teal-500 h-1.5 rounded-full" style="width: {{ (float)$cat[1]/5*100 }}%"></div>
                            </div>
                            <span class="text-xs font-bold text-teal-700 w-6">{{ $cat[1] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Recent Reviews --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <h3 class="font-semibold text-stone-800 mb-4">Recent Reviews</h3>
                <div class="space-y-4">
                    @foreach([
                        ['Maria S.','5','The broker was very professional and responsive. Highly recommended!','Jun 28, 2025'],
                        ['Pedro R.','4','Smooth reservation process. The AI assistant was very helpful.','Jun 20, 2025'],
                        ['Ana L.','5','Great experience from start to finish. Very satisfied!','Jun 15, 2025'],
                        ['Carlo M.','5','Very transparent with pricing and process. Will recommend!','Jun 10, 2025'],
                    ] as $rev)
                    <div class="pb-4 border-b border-stone-100 last:border-0 last:pb-0">
                        <div class="flex items-center justify-between mb-1.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 bg-teal-100 rounded-full flex items-center justify-center text-teal-800 font-bold text-xs">{{ strtoupper(substr($rev[0],0,1)) }}</div>
                                <p class="text-sm font-semibold text-stone-700">{{ $rev[0] }}</p>
                            </div>
                            <div class="flex items-center gap-0.5">
                                @for($s = 1; $s <= 5; $s++)
                                <svg class="w-3 h-3 {{ $s <= (int)$rev[1] ? 'text-teal-500' : 'text-stone-200' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                @endfor
                            </div>
                        </div>
                        <p class="text-xs text-stone-500 italic leading-relaxed">"{{ $rev[2] }}"</p>
                        <p class="text-xs text-stone-400 mt-1">{{ $rev[3] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
