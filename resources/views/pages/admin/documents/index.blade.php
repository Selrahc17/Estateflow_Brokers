@extends('layouts.admin')
@section('title', 'Document Verification')
@section('page-title', 'Document Verification')
@section('page-subtitle', 'Review and verify client document submissions')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Total Submitted','89','text-stone-800'],
        ['Pending Review','3','text-amber-600'],
        ['Approved','78','text-green-600'],
        ['Rejected','8','text-red-500'],
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
            <select class="border border-stone-200 rounded-lg px-3 py-1.5 text-xs focus:outline-none focus:ring-2 focus:ring-red-400">
                <option>All Status</option>
                <option>Pending Review</option>
                <option>Approved</option>
                <option>Rejected</option>
            </select>
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
                @foreach([
                    ['Valid ID','Juan dela Cruz','Identification','Jul 1, 2025','Pending Review','yellow'],
                    ['Proof of Income','Maria Santos','Financial','Jun 28, 2025','Pending Review','yellow'],
                    ['TIN Certificate','Pedro Reyes','Financial','Jun 25, 2025','Pending Review','yellow'],
                    ['Birth Certificate','Ana Lim','Personal','Jun 20, 2025','Approved','green'],
                    ['Bank Statement','Carlos Tan','Financial','Jun 18, 2025','Approved','green'],
                    ['Marriage Certificate','Rosa Garcia','Personal','Jun 15, 2025','Approved','green'],
                    ['Valid ID','Ben Cruz','Identification','Jun 10, 2025','Rejected','red'],
                    ['Proof of Income','Lea Ramos','Financial','Jun 5, 2025','Approved','green'],
                ] as $d)
                <tr class="hover:bg-stone-50 transition">
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            </div>
                            <span class="font-medium text-stone-700 text-xs">{{ $d[0] }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3 text-stone-500 text-xs">{{ $d[1] }}</td>
                    <td class="px-5 py-3 text-stone-500 text-xs">{{ $d[2] }}</td>
                    <td class="px-5 py-3 text-stone-400 text-xs">{{ $d[3] }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $d[5]==='green' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $d[5]==='yellow' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $d[5]==='red' ? 'bg-red-100 text-red-600' : '' }}">
                            {{ $d[4] }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        @if($d[5] === 'yellow')
                        <div class="flex gap-1.5">
                            <button class="text-xs bg-green-100 text-green-700 hover:bg-green-200 px-2 py-1 rounded-lg font-medium transition">Approve</button>
                            <button class="text-xs bg-red-100 text-red-600 hover:bg-red-200 px-2 py-1 rounded-lg font-medium transition">Reject</button>
                        </div>
                        @else
                        <button class="text-xs text-blue-600 hover:underline">View</button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Verification Stats --}}
    <div class="space-y-5">
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h3 class="font-semibold text-stone-800 mb-4">Verification Rate</h3>
            <div class="text-center mb-4">
                <p class="text-4xl font-bold text-green-600">87.6%</p>
                <p class="text-xs text-stone-400 mt-1">Documents approved</p>
            </div>
            <div class="space-y-2">
                @foreach([['Approved',78,'green'],['Pending',3,'yellow'],['Rejected',8,'red']] as $v)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-stone-600">{{ $v[0] }}</span>
                    <div class="flex items-center gap-2">
                        <div class="w-24 bg-stone-100 rounded-full h-2 overflow-hidden">
                            <div class="h-2 rounded-full {{ $v[2]==='green' ? 'bg-green-500' : ($v[2]==='yellow' ? 'bg-yellow-400' : 'bg-red-400') }}" style="width: {{ round($v[1]/89*100) }}%"></div>
                        </div>
                        <span class="text-xs font-bold text-stone-700 w-5 text-right">{{ $v[1] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h3 class="font-semibold text-stone-800 mb-4">By Document Type</h3>
            <div class="space-y-3">
                @foreach([
                    ['Identification','32'],
                    ['Financial','28'],
                    ['Personal','18'],
                    ['Other','11'],
                ] as $type)
                <div class="flex justify-between text-sm">
                    <span class="text-stone-500">{{ $type[0] }}</span>
                    <span class="font-semibold text-stone-700">{{ $type[1] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

@endsection
