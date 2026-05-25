@extends('layouts.app')
@section('title', 'Lot Availability')
@section('page-title', 'Lot Availability')
@section('page-subtitle', 'Track and manage lot status across all properties')

@section('content')

{{-- Filters --}}
<div class="flex flex-wrap gap-3 mb-6">
    <select class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
        <option>All Properties</option>
        <option>Palm Residences</option>
        <option>Greenfield Villas</option>
        <option>Sunrise Homes</option>
    </select>
    <div class="flex gap-2 items-center text-sm">
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-green-400 inline-block"></span> Available</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-amber-400 inline-block"></span> Reserved</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-red-400 inline-block"></span> Sold</span>
        <span class="flex items-center gap-1"><span class="w-3 h-3 rounded bg-stone-300 inline-block"></span> Blocked</span>
    </div>
</div>

{{-- Lot Grid --}}
<div class="bg-white rounded-xl border border-stone-200 p-6">
    <div class="flex items-center justify-between mb-5">
        <h2 class="font-semibold text-stone-800">Palm Residences — Lot Map</h2>
        <div class="text-sm text-stone-500">24 total · <span class="text-green-600 font-medium">6 available</span> · <span class="text-amber-600 font-medium">18 reserved</span></div>
    </div>

    {{-- Grid Map --}}
    <div class="grid grid-cols-6 sm:grid-cols-8 gap-2">
        @php
        $lots = [
            ['1-A','reserved'],['1-B','reserved'],['1-C','available'],['1-D','sold'],['1-E','reserved'],['1-F','available'],
            ['2-A','sold'],['2-B','reserved'],['2-C','reserved'],['2-D','available'],['2-E','sold'],['2-F','reserved'],
            ['3-A','reserved'],['3-B','available'],['3-C','sold'],['3-D','reserved'],['3-E','reserved'],['3-F','blocked'],
            ['4-A','available'],['4-B','reserved'],['4-C','reserved'],['4-D','sold'],['4-E','available'],['4-F','reserved'],
        ];
        $colors = ['available'=>'bg-green-400 hover:bg-green-500','reserved'=>'bg-amber-400 hover:bg-amber-500','sold'=>'bg-red-400','blocked'=>'bg-stone-300'];
        @endphp

        @foreach($lots as $lot)
        <div class="relative group">
            <div class="w-full aspect-square {{ $colors[$lot[1]] }} rounded-lg flex items-center justify-center text-white text-xs font-bold cursor-pointer transition">
                {{ $lot[0] }}
            </div>
            <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 bg-stone-800 text-white text-xs rounded px-2 py-1 whitespace-nowrap opacity-0 group-hover:opacity-100 transition z-10">
                Lot {{ $lot[0] }} — {{ ucfirst($lot[1]) }}
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Lot Table --}}
<div class="bg-white rounded-xl border border-stone-200 p-5 mt-5">
    <h2 class="font-semibold text-stone-800 mb-4">Lot Details</h2>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-stone-400 border-b border-stone-100">
                    <th class="pb-3 font-medium">Lot No.</th>
                    <th class="pb-3 font-medium">Block</th>
                    <th class="pb-3 font-medium">Area (sqm)</th>
                    <th class="pb-3 font-medium">Price</th>
                    <th class="pb-3 font-medium">Status</th>
                    <th class="pb-3 font-medium">Client</th>
                    <th class="pb-3 font-medium">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-50">
                @foreach([
                    ['1-A','Block 1','120','₱1,200,000','Reserved','Juan dela Cruz'],
                    ['1-B','Block 1','135','₱1,350,000','Reserved','Maria Santos'],
                    ['1-C','Block 1','110','₱1,100,000','Available','—'],
                    ['1-D','Block 1','125','₱1,250,000','Sold','Pedro Reyes'],
                    ['2-A','Block 2','140','₱1,400,000','Sold','Ana Lim'],
                    ['2-D','Block 2','115','₱1,150,000','Available','—'],
                ] as $row)
                <tr class="hover:bg-stone-50 transition">
                    <td class="py-3 font-medium text-stone-700">{{ $row[0] }}</td>
                    <td class="py-3 text-stone-500">{{ $row[1] }}</td>
                    <td class="py-3 text-stone-500">{{ $row[2] }}</td>
                    <td class="py-3 text-stone-700 font-medium">{{ $row[3] }}</td>
                    <td class="py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium
                            {{ $row[4]==='Available' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $row[4]==='Reserved' ? 'bg-amber-100 text-amber-700' : '' }}
                            {{ $row[4]==='Sold' ? 'bg-red-100 text-red-700' : '' }}">
                            {{ $row[4] }}
                        </span>
                    </td>
                    <td class="py-3 text-stone-500">{{ $row[5] }}</td>
                    <td class="py-3">
                        <button class="text-xs text-amber-600 hover:underline">Edit</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
