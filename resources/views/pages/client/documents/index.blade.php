@extends('layouts.public')
@section('title', 'My Documents')

@section('content')

<div class="bg-gradient-to-r from-stone-900 to-amber-900 text-white">
    <div class="max-w-6xl mx-auto px-6 py-10">
        <p class="text-amber-400 text-xs uppercase tracking-widest font-semibold mb-1">My Documents</p>
        <h1 class="text-2xl font-bold">Document Submissions</h1>
    </div>
</div>

<div class="max-w-6xl mx-auto px-6 py-8 space-y-6">

    @if(session('success'))
    <div class="p-3 bg-green-50 border border-green-200 text-green-700 rounded-xl text-sm">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="p-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm">
        @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
    </div>
    @endif

    @php
        $verified = $documents->getCollection()->where('status','verified')->count();
        $pending  = $documents->getCollection()->where('status','pending')->count();
        $rejected = $documents->getCollection()->where('status','rejected')->count();
    @endphp

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-green-100 p-5 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $verified }}</p>
            <p class="text-xs text-stone-500 mt-1">Verified</p>
        </div>
        <div class="bg-white rounded-2xl border border-yellow-100 p-5 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $pending }}</p>
            <p class="text-xs text-stone-500 mt-1">Pending Review</p>
        </div>
        <div class="bg-white rounded-2xl border border-red-100 p-5 text-center">
            <p class="text-2xl font-bold text-red-500">{{ $rejected }}</p>
            <p class="text-xs text-stone-500 mt-1">Rejected</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <div class="xl:col-span-2 space-y-5">

            {{-- Upload Form --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h2 class="font-semibold text-stone-800 mb-4">Upload Document</h2>
                <form action="{{ route('client.account.documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-stone-500 mb-1 block">Document Name</label>
                            <input type="text" name="name" required placeholder="e.g. Valid ID" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label class="text-xs text-stone-500 mb-1 block">Document Type</label>
                            <select name="type" required class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                                <option value="">Select type...</option>
                                @foreach(['contract','id','proof_of_income','receipt','other'] as $t)
                                <option value="{{ $t }}">{{ ucfirst(str_replace('_',' ',$t)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-stone-500 mb-1 block">File (PDF, JPG, PNG — max 10MB)</label>
                        <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png" class="w-full border border-stone-200 rounded-xl px-4 py-2.5 text-sm file:mr-4 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                    </div>
                    <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition">Upload Document</button>
                </form>
            </div>

            {{-- Document List --}}
            <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-100">
                    <h2 class="font-semibold text-stone-800">Uploaded Documents</h2>
                </div>
                <div class="divide-y divide-stone-100">
                    @forelse($documents as $doc)
                    <div class="flex items-center justify-between px-6 py-4 hover:bg-stone-50 transition">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-stone-700">{{ $doc->name }}</p>
                                <p class="text-xs text-stone-400">{{ ucfirst(str_replace('_',' ',$doc->type)) }} · Uploaded {{ $doc->created_at->format('M d, Y') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                                {{ $doc->status==='verified' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $doc->status==='pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $doc->status==='rejected' ? 'bg-red-100 text-red-600' : '' }}">
                                {{ ucfirst($doc->status) }}
                            </span>
                            <a href="{{ route('client.account.documents.download', $doc) }}" class="text-xs text-amber-600 hover:underline">Download</a>
                        </div>
                    </div>
                    @empty
                    <div class="px-6 py-10 text-center text-stone-400">No documents uploaded yet.</div>
                    @endforelse
                </div>
                <div class="px-6 py-4 border-t border-stone-100">{{ $documents->links() }}</div>
            </div>

        </div>

        {{-- Help Card --}}
        <div class="bg-gradient-to-br from-stone-800 to-amber-900 rounded-2xl p-5 text-white h-fit">
            <p class="font-semibold mb-1 text-sm">Need help with documents?</p>
            <p class="text-stone-300 text-xs mb-3">Our AI Assistant can guide you on what documents to prepare.</p>
            <a href="{{ route('client.account.chat') }}" class="block w-full text-center bg-amber-600 hover:bg-amber-700 py-2.5 rounded-xl text-sm font-semibold transition">Ask AI Assistant</a>
        </div>

    </div>
</div>

@endsection
