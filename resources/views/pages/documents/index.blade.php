@extends('layouts.app')
@section('title', 'Documents')
@section('page-title', 'Document Submissions')
@section('page-subtitle', 'Manage client document uploads')

@section('content')

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-200">
            <tr class="text-left text-stone-500">
                <th class="px-5 py-3 font-medium">Document</th>
                <th class="px-5 py-3 font-medium">Client</th>
                <th class="px-5 py-3 font-medium">Type</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Uploaded</th>
                <th class="px-5 py-3 font-medium">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse($documents as $doc)
            <tr class="hover:bg-stone-50 transition">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <a href="{{ route('agent.documents.download', $doc) }}" class="font-medium text-stone-700 hover:text-amber-600">{{ $doc->name }}</a>
                    </div>
                </td>
                <td class="px-5 py-3 text-stone-500">{{ $doc->client?->full_name ?? '—' }}</td>
                <td class="px-5 py-3 text-stone-500 text-xs">{{ ucfirst(str_replace('_',' ',$doc->type)) }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        {{ $doc->status==='verified' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $doc->status==='pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $doc->status==='rejected' ? 'bg-red-100 text-red-600' : '' }}">
                        {{ ucfirst($doc->status) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-stone-400 text-xs">{{ $doc->created_at->format('M d, Y') }}</td>
                <td class="px-5 py-3">
                    @if($doc->status === 'pending')
                    <div class="flex gap-2">
                        <form action="{{ route('agent.documents.verify', $doc) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs bg-green-100 text-green-700 hover:bg-green-200 px-2.5 py-1 rounded-lg font-medium transition">Verify</button>
                        </form>
                        <form action="{{ route('agent.documents.reject', $doc) }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs bg-red-100 text-red-600 hover:bg-red-200 px-2.5 py-1 rounded-lg font-medium transition">Reject</button>
                        </form>
                    </div>
                    @else
                    <a href="{{ route('agent.documents.download', $doc) }}" class="text-xs text-amber-600 hover:underline">Download</a>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-10 text-center text-stone-400">No documents submitted yet.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-5 py-4 border-t border-stone-100">{{ $documents->withQueryString()->links() }}</div>
</div>

@endsection
