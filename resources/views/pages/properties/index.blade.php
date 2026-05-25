@extends('layouts.app')
@section('title', 'Properties')
@section('page-title', 'Property Listings')
@section('page-subtitle', 'Manage all your property listings')

@section('content')

{{-- Header Actions --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex gap-2">
        <input type="text" placeholder="Search properties..." class="border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 w-64">
        <select class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            <option>All Status</option>
            <option>Available</option>
            <option>Sold Out</option>
        </select>
    </div>
    <button class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Property
    </button>
</div>

{{-- Property Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
    @foreach([
        ['Palm Residences', 'Quezon City', '24 lots', '6 available', 'Residential', 'bg-green-100 text-green-700', 'Active'],
        ['Greenfield Villas', 'Laguna', '36 lots', '16 available', 'Residential', 'bg-green-100 text-green-700', 'Active'],
        ['Sunrise Homes', 'Cavite', '48 lots', '13 available', 'Mixed Use', 'bg-green-100 text-green-700', 'Active'],
        ['Hillside Estates', 'Rizal', '20 lots', '12 available', 'Residential', 'bg-yellow-100 text-yellow-700', 'Pre-selling'],
        ['Metro Gardens', 'Bulacan', '30 lots', '30 available', 'Commercial', 'bg-blue-100 text-blue-700', 'New'],
        ['Coastal View', 'Batangas', '15 lots', '0 available', 'Residential', 'bg-red-100 text-red-700', 'Sold Out'],
    ] as $p)
    <div class="bg-white rounded-xl border border-stone-200 overflow-hidden hover:shadow-md transition">
        <div class="h-36 bg-gradient-to-br from-amber-100 to-stone-200 flex items-center justify-center">
            <svg class="w-16 h-16 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        </div>
        <div class="p-4">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <h3 class="font-semibold text-stone-800">{{ $p[0] }}</h3>
                    <p class="text-xs text-stone-400 flex items-center gap-1 mt-0.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        {{ $p[1] }}
                    </p>
                </div>
                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $p[5] }}">{{ $p[6] }}</span>
            </div>
            <div class="flex items-center justify-between text-xs text-stone-500 mt-3 pt-3 border-t border-stone-100">
                <span>{{ $p[2] }} total</span>
                <span class="text-green-600 font-medium">{{ $p[3] }}</span>
                <span class="bg-stone-100 px-2 py-1 rounded text-stone-600">{{ $p[4] }}</span>
            </div>
            <div class="flex gap-2 mt-3">
                <a href="{{ route('lots.index') }}" class="flex-1 text-center text-xs bg-amber-50 hover:bg-amber-100 text-amber-700 py-1.5 rounded-lg transition font-medium">View Lots</a>
                <button class="flex-1 text-center text-xs bg-stone-50 hover:bg-stone-100 text-stone-600 py-1.5 rounded-lg transition font-medium">Edit</button>
            </div>
        </div>
    </div>
    @endforeach
</div>

@endsection
