@extends('layouts.public')
@section('title', 'My Documents')

@section('content')

{{-- Page Hero --}}
<div class="bg-gradient-to-r from-stone-900 to-amber-900 text-white">
    <div class="max-w-6xl mx-auto px-6 py-10">
        <p class="text-amber-400 text-xs uppercase tracking-widest font-semibold mb-1">My Documents</p>
        <h1 class="text-2xl font-bold">Document Submissions</h1>
        <p class="text-stone-300 text-sm mt-1">Upload and manage your required documents for Palm Residences</p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-6 py-8 space-y-6">

    {{-- Status Summary --}}
    <div class="grid grid-cols-3 gap-4">
        @foreach([
            ['Approved','4','bg-green-50 border-green-100','text-green-600'],
            ['Pending Review','1','bg-yellow-50 border-yellow-100','text-yellow-600'],
            ['Missing','1','bg-red-50 border-red-100','text-red-500'],
        ] as $s)
        <div class="bg-white rounded-2xl border {{ $s[2] }} p-5 text-center">
            <p class="text-2xl font-bold {{ $s[3] }}">{{ $s[1] }}</p>
            <p class="text-xs text-stone-500 mt-1">{{ $s[0] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Missing Document Alert --}}
    <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex items-start gap-3">
        <div class="w-9 h-9 bg-red-100 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div class="flex-1">
            <p class="text-sm font-semibold text-red-700">Action Required: TIN Certificate Missing</p>
            <p class="text-xs text-red-500 mt-0.5">Please upload your TIN Certificate to complete your document requirements.</p>
        </div>
        <button class="shrink-0 bg-red-500 hover:bg-red-600 text-white text-xs font-semibold px-4 py-2 rounded-lg transition">Upload Now</button>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- Upload Area + Document List --}}
        <div class="xl:col-span-2 space-y-5">

            {{-- Upload Drop Zone --}}
            <div class="bg-white rounded-2xl border-2 border-dashed border-amber-300 hover:border-amber-500 transition cursor-pointer p-8 text-center" x-data="{ dragging: false }" @dragover.prevent="dragging=true" @dragleave="dragging=false" @drop.prevent="dragging=false" :class="dragging ? 'border-amber-500 bg-amber-50' : ''">
                <div class="w-16 h-16 bg-amber-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                </div>
                <p class="font-semibold text-stone-700 mb-1">Drag & drop your file here</p>
                <p class="text-sm text-stone-400 mb-4">or click to browse — PDF, JPG, PNG up to 10MB</p>
                <button class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    Choose File
                </button>
            </div>

            {{-- Uploaded Documents --}}
            <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-100">
                    <h2 class="font-semibold text-stone-800">Uploaded Documents</h2>
                </div>
                <div class="divide-y divide-stone-100">
                    @foreach([
                        ['Valid ID','PDF','2.1 MB','Jul 1, 2025','Approved','green'],
                        ['Proof of Income','PDF','1.8 MB','Jul 1, 2025','Approved','green'],
                        ['Birth Certificate','JPG','3.2 MB','Jul 2, 2025','Approved','green'],
                        ['Bank Statement','PDF','4.5 MB','Jul 3, 2025','Pending Review','yellow'],
                        ['Marriage Certificate','PDF','2.7 MB','Jul 3, 2025','Approved','green'],
                    ] as $d)
                    <div class="flex items-center justify-between px-6 py-4 hover:bg-stone-50 transition">
                        <div class="flex items-center gap-4">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0
                                {{ $d[1]==='PDF' ? 'bg-red-100' : 'bg-blue-100' }}">
                                <svg class="w-5 h-5 {{ $d[1]==='PDF' ? 'text-red-500' : 'text-blue-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-stone-700">{{ $d[0] }}</p>
                                <p class="text-xs text-stone-400">{{ $d[1] }} · {{ $d[2] }} · Uploaded {{ $d[3] }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ $d[5]==='green' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                {{ $d[4] }}
                            </span>
                            <button class="w-8 h-8 flex items-center justify-center rounded-lg text-stone-400 hover:bg-stone-100 hover:text-stone-600 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            </button>
                            <button class="w-8 h-8 flex items-center justify-center rounded-lg text-stone-400 hover:bg-red-50 hover:text-red-500 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Requirements Checklist --}}
        <div class="space-y-5">
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <h3 class="font-semibold text-stone-800 mb-4">Requirements Checklist</h3>
                <div class="space-y-2">
                    @foreach([
                        ['Valid ID','Approved','green'],
                        ['Proof of Income','Approved','green'],
                        ['Birth Certificate','Approved','green'],
                        ['TIN Certificate','Missing','red'],
                        ['Marriage Certificate','Approved','green'],
                        ['Bank Statement','Pending','yellow'],
                    ] as $r)
                    <div class="flex items-center justify-between p-3 rounded-xl
                        {{ $r[2]==='red' ? 'bg-red-50 border border-red-100' : 'bg-stone-50' }}">
                        <div class="flex items-center gap-2.5">
                            <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0
                                {{ $r[2]==='green' ? 'bg-green-500' : ($r[2]==='yellow' ? 'bg-yellow-400' : 'bg-red-400') }}">
                                @if($r[2]==='green')
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                @elseif($r[2]==='yellow')
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                @else
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                @endif
                            </div>
                            <p class="text-sm text-stone-700 font-medium">{{ $r[0] }}</p>
                        </div>
                        <span class="text-xs font-semibold
                            {{ $r[2]==='green' ? 'text-green-600' : ($r[2]==='yellow' ? 'text-yellow-600' : 'text-red-500') }}">
                            {{ $r[1] }}
                        </span>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4 pt-4 border-t border-stone-100">
                    <div class="flex justify-between text-xs text-stone-500 mb-1.5">
                        <span>Completion</span><span class="font-semibold text-stone-700">4 of 6</span>
                    </div>
                    <div class="w-full bg-stone-100 rounded-full h-2">
                        <div class="bg-amber-500 h-2 rounded-full" style="width: 66%"></div>
                    </div>
                </div>
            </div>

            {{-- Help Card --}}
            <div class="bg-gradient-to-br from-stone-800 to-amber-900 rounded-2xl p-5 text-white">
                <p class="font-semibold mb-1 text-sm">Need help with documents?</p>
                <p class="text-stone-300 text-xs mb-3">Our AI Assistant can guide you on what documents to prepare.</p>
                <a href="{{ route('client.account.chat') }}" class="block w-full text-center bg-amber-600 hover:bg-amber-700 py-2.5 rounded-xl text-sm font-semibold transition">
                    Ask AI Assistant
                </a>
            </div>
        </div>

    </div>
</div>

@endsection
