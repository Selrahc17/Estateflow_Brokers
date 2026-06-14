@extends('layouts.admin')
@section('title', 'Document Verification')
@section('page-title', 'Document Verification')
@section('page-subtitle', 'Review and verify client document submissions')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @php
        $total = $documents->total() ?? 0;
        $pending = $documents->where('status', 'pending')->count();
        $approved = $documents->where('status', 'verified')->count();
        $rejected = $documents->where('status', 'rejected')->count();
    @endphp
    @foreach([
        ['Total Submitted', $total, 'text-stone-800'],
        ['Pending Review', $pending, 'text-amber-600'],
        ['Approved', $approved, 'text-green-600'],
        ['Rejected', $rejected, 'text-red-500'],
    ] as $s)
    <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
        <p class="text-2xl font-bold {{ $s[2] }}">{{ $s[1] }}</p>
        <p class="text-xs text-stone-500 mt-1">{{ $s[0] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Document List --}}
    <div class="xl:col-span-2 bg-white rounded-xl border border-stone-200 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
            <h2 class="font-semibold text-stone-800">Document Submissions</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-stone-100">
                <tr class="text-left text-stone-500">
                    <th class="px-5 py-3 font-medium">Document</th>
                    <th class="px-5 py-3 font-medium">Client</th>
                    <th class="px-5 py-3 font-medium">Type</th>
                    <th class="px-5 py-3 font-medium">Submitted</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse($documents as $doc)
                <tr class="hover:bg-stone-50 transition">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <span class="font-medium text-stone-700 text-xs">{{ $doc->name }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-stone-500 text-xs">{{ $doc->client?->full_name ?? '—' }}</td>
                    <td class="px-5 py-3 text-stone-500 text-xs">{{ $doc->type }}</td>
                    <td class="px-5 py-3 text-stone-400 text-xs">{{ $doc->created_at?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $doc->status==='verified' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $doc->status==='pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $doc->status==='rejected' ? 'bg-red-100 text-red-600' : '' }}">
                            {{ ucfirst($doc->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        @if($doc->status === 'pending')
                        <div class="flex gap-1.5">
                            <form action="{{ route('admin.documents.verify', $doc) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs bg-green-100 text-green-700 hover:bg-green-200 px-2 py-1 rounded-lg font-medium transition">Approve</button>
                            </form>
                            <form action="{{ route('admin.documents.reject', $doc) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs bg-red-100 text-red-600 hover:bg-red-200 px-2 py-1 rounded-lg font-medium transition">Reject</button>
                            </form>
                            <form action="{{ route('admin.documents.request-more', $doc) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs bg-blue-100 text-blue-600 hover:bg-blue-200 px-2 py-1 rounded-lg font-medium transition">Request More</button>
                            </form>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-8 text-center text-stone-400">No documents found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-5">{{ $documents->links() }}</div>
    </div>
</div>

@endsection
