@extends('layouts.admin')
@section('title', 'Documents')
@section('page-title', 'Documents')
@section('page-subtitle', 'Manage broker documents and files')

@section('content')

@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
@endif

@if($errors->any())
<div class="mb-5 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 px-4 py-3 text-sm text-red-700 dark:text-red-300">
    <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Upload Form --}}
    <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-6">
        <h2 class="font-semibold text-stone-800 dark:text-stone-100 mb-5 flex items-center gap-2">
            <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Upload Broker Document
        </h2>
        <form action="{{ route('admin.documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-semibold text-stone-500 dark:text-stone-400 uppercase tracking-widest mb-1.5 block">Broker</label>
                <select name="broker_id" class="w-full border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    <option value="">Select broker...</option>
                    @foreach($brokers as $broker)
                        <option value="{{ $broker->id }}" {{ old('broker_id') == $broker->id ? 'selected' : '' }}>{{ $broker->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-stone-500 dark:text-stone-400 uppercase tracking-widest mb-1.5 block">Document Name</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. PRC License" class="w-full border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
            </div>
            <div>
                <label class="text-xs font-semibold text-stone-500 dark:text-stone-400 uppercase tracking-widest mb-1.5 block">Document Type</label>
                <select name="type" class="w-full border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    <option value="">Select type...</option>
                    @foreach(['license' => 'License', 'id' => 'Government ID', 'contract' => 'Contract', 'accreditation' => 'Accreditation', 'proof_of_income' => 'Proof of Income', 'photo' => 'Photo', 'other' => 'Other'] as $val => $label)
                        <option value="{{ $val }}" {{ old('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-stone-500 dark:text-stone-400 uppercase tracking-widest mb-1.5 block">File</label>
                <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="w-full border border-stone-200 dark:border-stone-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-teal-50 dark:file:bg-teal-950 file:text-teal-700 dark:file:text-teal-300 hover:file:bg-teal-100 dark:hover:file:bg-teal-900">
                <p class="text-xs text-stone-500 dark:text-stone-400 mt-1">PDF, JPG, PNG, DOC up to 10MB</p>
            </div>
            <div>
                <label class="text-xs font-semibold text-stone-500 dark:text-stone-400 uppercase tracking-widest mb-1.5 block">Notes <span class="text-stone-500 dark:text-stone-400 normal-case">(optional)</span></label>
                <textarea name="notes" rows="3" placeholder="Additional notes..." class="w-full border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 resize-none">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="w-full bg-teal-700 hover:bg-teal-800 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                Upload Document
            </button>
        </form>
    </div>

    {{-- Document List --}}
    <div class="xl:col-span-2 bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 overflow-hidden">
        <div class="px-5 py-4 border-b border-stone-100 dark:border-stone-800 flex items-center justify-between">
            <h2 class="font-semibold text-stone-800 dark:text-stone-100">Broker Documents</h2>
            <span class="text-xs text-stone-500 dark:text-stone-400">{{ $documents->total() }} total</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 dark:bg-stone-800/60 border-b border-stone-100 dark:border-stone-800">
                    <tr class="text-left text-stone-500 dark:text-stone-400">
                        <th class="px-5 py-3 font-medium">Document</th>
                        <th class="px-5 py-3 font-medium">Broker</th>
                        <th class="px-5 py-3 font-medium">Type</th>
                        <th class="px-5 py-3 font-medium">Size</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                    @forelse($documents as $doc)
                    <tr class="hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-teal-50 dark:bg-teal-950 rounded-lg flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <p class="font-medium text-stone-700 dark:text-stone-200 text-xs">{{ $doc->name }}</p>
                                    <p class="text-stone-500 dark:text-stone-400 text-xs">{{ $doc->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-stone-600 dark:text-stone-300 text-xs">{{ $doc->broker?->name ?? ($doc->client?->full_name ?? '—') }}</td>
                        <td class="px-5 py-3 text-stone-500 dark:text-stone-400 text-xs capitalize">{{ str_replace('_', ' ', $doc->type) }}</td>
                        <td class="px-5 py-3 text-stone-500 dark:text-stone-400 text-xs">{{ $doc->file_size ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $doc->status === 'verified'    ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300'  : '' }}
                                {{ $doc->status === 'pending'     ? 'bg-yellow-100 dark:bg-yellow-950 text-yellow-700 dark:text-yellow-300': '' }}
                                {{ $doc->status === 'rejected'    ? 'bg-red-100 dark:bg-red-950 text-red-600 dark:text-red-300'      : '' }}
                                {{ $doc->status === 'needs_more'  ? 'bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-300'    : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $doc->status)) }}
                            </span>
                            @if($doc->notes)
                            <p class="mt-1 max-w-[12rem] text-xs text-stone-500 dark:text-stone-400">{{ $doc->notes }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <a href="{{ route('admin.documents.download', $doc) }}" target="_blank"
                                   class="text-xs bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-stone-700 px-2 py-1 rounded-lg font-medium transition">
                                    View
                                </a>
                                @if($doc->status === 'pending')
                                <form action="{{ route('admin.documents.verify', $doc) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300 hover:bg-green-200 dark:hover:bg-green-900 px-2 py-1 rounded-lg font-medium transition">Approve</button>
                                </form>
                                <form method="POST" class="flex items-center gap-1.5">
                                    @csrf
                                    <input type="text" name="notes" required maxlength="1000" placeholder="Reason" aria-label="Reason for {{ $doc->name }}" class="w-28 border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-teal-400">
                                    <button type="submit" formaction="{{ route('admin.documents.reject', $doc) }}" class="text-xs bg-red-100 dark:bg-red-950 text-red-600 dark:text-red-300 hover:bg-red-200 dark:hover:bg-red-900 px-2 py-1 rounded-lg font-medium transition">Reject</button>
                                    <button type="submit" formaction="{{ route('admin.documents.request-more', $doc) }}" class="whitespace-nowrap text-xs bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-900 px-2 py-1 rounded-lg font-medium transition">Request More</button>
                                </form>
                                @endif
                                <form action="{{ route('admin.documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Delete this document?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs bg-stone-100 dark:bg-stone-800 text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950 px-2 py-1 rounded-lg font-medium transition">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-stone-500 dark:text-stone-400">No documents uploaded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-5">{{ $documents->links() }}</div>
    </div>

</div>

@endsection
