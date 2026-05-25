@extends('layouts.app')
@section('title', 'Documents')
@section('page-title', 'Document Submissions')
@section('page-subtitle', 'Manage client document uploads and requirements')

@section('content')

<div class="flex flex-col sm:flex-row gap-3 justify-between mb-5">
    <input type="text" placeholder="Search documents..." class="border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 w-64">
    <button class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
        Upload Document
    </button>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Document List --}}
    <div class="xl:col-span-2 bg-white rounded-xl border border-stone-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-stone-200">
                <tr class="text-left text-stone-500">
                    <th class="px-5 py-3 font-medium">Document</th>
                    <th class="px-5 py-3 font-medium">Client</th>
                    <th class="px-5 py-3 font-medium">Type</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Date</th>
                    <th class="px-5 py-3 font-medium">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach([
                    ['Valid ID','Juan dela Cruz','Identification','Approved','Jul 1, 2025','green'],
                    ['Proof of Income','Maria Santos','Financial','Pending','Jun 28, 2025','yellow'],
                    ['Birth Certificate','Pedro Reyes','Personal','Approved','Jun 25, 2025','green'],
                    ['TIN Certificate','Ana Lim','Financial','Missing','—','red'],
                    ['Marriage Certificate','Carlos Tan','Personal','Approved','Jun 18, 2025','green'],
                    ['Bank Statement','Rosa Garcia','Financial','Pending','Jun 15, 2025','yellow'],
                ] as $d)
                <tr class="hover:bg-stone-50 transition">
                    <td class="px-5 py-3 font-medium text-stone-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ $d[0] }}
                    </td>
                    <td class="px-5 py-3 text-stone-500">{{ $d[1] }}</td>
                    <td class="px-5 py-3 text-stone-500">{{ $d[2] }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $d[5]==='green' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $d[5]==='yellow' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $d[5]==='red' ? 'bg-red-100 text-red-700' : '' }}">
                            {{ $d[3] }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-stone-400 text-xs">{{ $d[4] }}</td>
                    <td class="px-5 py-3">
                        <button class="text-xs text-amber-600 hover:underline">View</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Requirements Checklist --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Requirements Checklist</h2>
        <p class="text-xs text-stone-400 mb-3">Juan dela Cruz — Palm Residences</p>
        <div class="space-y-3">
            @foreach([
                ['Valid ID','Submitted','green'],
                ['Proof of Income','Submitted','green'],
                ['Birth Certificate','Submitted','green'],
                ['TIN Certificate','Missing','red'],
                ['Marriage Certificate','Submitted','green'],
                ['Bank Statement','Pending','yellow'],
            ] as $req)
            <div class="flex items-center justify-between p-3 rounded-lg bg-stone-50">
                <span class="text-sm text-stone-700">{{ $req[0] }}</span>
                <span class="text-xs font-medium
                    {{ $req[2]==='green' ? 'text-green-600' : '' }}
                    {{ $req[2]==='yellow' ? 'text-yellow-600' : '' }}
                    {{ $req[2]==='red' ? 'text-red-500' : '' }}">
                    {{ $req[1] }}
                </span>
            </div>
            @endforeach
        </div>
    </div>

</div>

@endsection
