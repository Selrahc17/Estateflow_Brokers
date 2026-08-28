@extends('layouts.admin')
@section('title', 'Documents')
@section('page-title', 'Documents')
@section('page-subtitle', 'Manage broker documents and files')

@section('content')

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

@if($errors->any())
<div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
    <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Upload Form --}}
    <div class="bg-white rounded-xl border border-stone-200 p-6">
        <h2 class="font-semibold text-stone-800 mb-5 flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Upload Broker Document
        </h2>
        <form action="{{ route('admin.documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Broker</label>
                <select name="broker_id" class="w-full border border-stone-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                    <option value="">Select broker...</option>
                    @foreach($brokers as $broker)
                        <option value="{{ $broker->id }}" {{ old('broker_id') == $broker->id ? 'selected' : '' }}>{{ $broker->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Document Name</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. PRC License" class="w-full border border-stone-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>
            <div>
                <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Document Type</label>
                <select name="type" class="w-full border border-stone-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                    <option value="">Select type...</option>
                    @foreach(['license' => 'License', 'id' => 'Government ID', 'contract' => 'Contract', 'accreditation' => 'Accreditation', 'proof_of_income' => 'Proof of Income', 'photo' => 'Photo', 'other' => 'Other'] as $val => $label)
                        <option value="{{ $val }}" {{ old('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">File</label>
                <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="w-full border border-stone-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-red-50 file:text-red-600 hover:file:bg-red-100">
                <p class="text-xs text-stone-400 mt-1">PDF, JPG, PNG, DOC up to 10MB</p>
            </div>
            <div>
                <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Notes <span class="text-stone-300 normal-case">(optional)</span></label>
                <textarea name="notes" rows="3" placeholder="Additional notes..." class="w-full border border-stone-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none">{{ old('notes') }}</textarea>
            </div>
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                Upload Document
            </button>
        </form>
    </div>

    {{-- Document List --}}
    <div class="xl:col-span-2 bg-white rounded-xl border border-stone-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between">
            <h2 class="font-semibold text-stone-800">Broker Documents</h2>
            <span class="text-xs text-stone-400">{{ $documents->total() }} total</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 border-b border-stone-100">
                    <tr class="text-left text-stone-500">
                        <th class="px-5 py-3 font-medium">Document</th>
                        <th class="px-5 py-3 font-medium">Broker</th>
                        <th class="px-5 py-3 font-medium">Type</th>
                        <th class="px-5 py-3 font-medium">Size</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                        <th class="px-5 py-3 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($documents as $doc)
                    <tr class="hover:bg-stone-50 transition">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center shrink-0">
                                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </div>
                                <div>
                                    <p class="font-medium text-stone-700 text-xs">{{ $doc->name }}</p>
                                    <p class="text-stone-400 text-xs">{{ $doc->created_at->format('M d, Y') }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-stone-600 text-xs">{{ $doc->broker?->name ?? ($doc->client?->full_name ?? '—') }}</td>
                        <td class="px-5 py-3 text-stone-500 text-xs capitalize">{{ str_replace('_', ' ', $doc->type) }}</td>
                        <td class="px-5 py-3 text-stone-400 text-xs">{{ $doc->file_size ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $doc->status === 'verified'    ? 'bg-green-100 text-green-700'  : '' }}
                                {{ $doc->status === 'pending'     ? 'bg-yellow-100 text-yellow-700': '' }}
                                {{ $doc->status === 'rejected'    ? 'bg-red-100 text-red-600'      : '' }}
                                {{ $doc->status === 'needs_more'  ? 'bg-blue-100 text-blue-600'    : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $doc->status)) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <a href="{{ str_starts_with($doc->file_path, 'http') ? $doc->file_path : asset('storage/' . $doc->file_path) }}" target="_blank"
                                   class="text-xs bg-stone-100 text-stone-600 hover:bg-stone-200 px-2 py-1 rounded-lg font-medium transition">
                                    View
                                </a>
                                @if($doc->status === 'pending')
                                <form action="{{ route('admin.documents.verify', $doc) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs bg-green-100 text-green-700 hover:bg-green-200 px-2 py-1 rounded-lg font-medium transition">Approve</button>
                                </form>
                                <form action="{{ route('admin.documents.reject', $doc) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs bg-red-100 text-red-600 hover:bg-red-200 px-2 py-1 rounded-lg font-medium transition">Reject</button>
                                </form>
                                @endif
                                <form action="{{ route('admin.documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Delete this document?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs bg-stone-100 text-red-500 hover:bg-red-50 px-2 py-1 rounded-lg font-medium transition">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-stone-400">No documents uploaded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-5">{{ $documents->links() }}</div>
    </div>

</div>

@endsection
