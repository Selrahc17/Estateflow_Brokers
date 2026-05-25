@extends('layouts.public')
@section('title', 'All Properties')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-stone-800">
            @if(request('search'))
                Results for "{{ request('search') }}"
            @elseif(request('type'))
                {{ request('type') }} Properties
            @elseif(request('status'))
                {{ request('status') }} Properties
            @else
                All Properties
            @endif
        </h1>
        <p class="text-stone-400 text-sm mt-1">Browse available properties from our trusted brokers and agents</p>
    </div>

    {{-- Pre-Selling Section --}}
    @if(!request('type') && !request('status') && !request('search'))

    <section class="mb-12">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h2 class="text-lg font-bold text-stone-800">Pre-Selling Properties</h2>
            </div>
            <a href="{{ route('client.properties') }}?status=Pre-Selling" class="text-sm text-amber-600 hover:underline font-medium">View all →</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach([
                ['Palm Residences','Quezon City, Metro Manila','House and Lot','Pre-Selling','₱1,200,000','120 sqm','3','2','https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=400&h=250&fit=crop'],
                ['Greenfield Villas','Laguna','House and Lot','Pre-Selling','₱980,000','110 sqm','2','1','https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=400&h=250&fit=crop'],
                ['Sunrise Homes','Cavite','Lot Only','Pre-Selling','₱450,000','150 sqm','—','—','https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=250&fit=crop'],
                ['Hillside Estates','Rizal','House and Lot','Pre-Selling','₱1,350,000','135 sqm','3','2','https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400&h=250&fit=crop'],
            ] as $p)
            @include('pages.client.properties._card', ['p' => $p])
            @endforeach
        </div>
    </section>

    {{-- RFO Section --}}
    <section class="mb-12">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="text-lg font-bold text-stone-800">Ready for Occupancy</h2>
            </div>
            <a href="{{ route('client.properties') }}?status=RFO" class="text-sm text-amber-600 hover:underline font-medium">View all →</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach([
                ['Metro Gardens','Bulacan','House and Lot','RFO','₱2,100,000','180 sqm','4','3','https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400&h=250&fit=crop'],
                ['Coastal View','Batangas','Lot Only','RFO','₱650,000','200 sqm','—','—','https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=400&h=250&fit=crop'],
                ['Villa Serena','Laguna','House and Lot','RFO','₱3,500,000','220 sqm','4','3','https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?w=400&h=250&fit=crop'],
                ['Brentwood Heights','Cavite','House and Lot','RFO','₱1,800,000','160 sqm','3','2','https://images.unsplash.com/photo-1583608205776-bfd35f0d9f83?w=400&h=250&fit=crop'],
            ] as $p)
            @include('pages.client.properties._card', ['p' => $p])
            @endforeach
        </div>
    </section>

    {{-- Lot Only Section --}}
    <section class="mb-12">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                </div>
                <h2 class="text-lg font-bold text-stone-800">Lot Only</h2>
            </div>
            <a href="{{ route('client.properties') }}?type=Lot+Only" class="text-sm text-amber-600 hover:underline font-medium">View all →</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach([
                ['Sunrise Homes','Cavite','Lot Only','Pre-Selling','₱450,000','150 sqm','—','—','https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=250&fit=crop'],
                ['Coastal View','Batangas','Lot Only','RFO','₱650,000','200 sqm','—','—','https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=400&h=250&fit=crop'],
                ['Hillside Lots','Rizal','Lot Only','Pre-Selling','₱380,000','120 sqm','—','—','https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=400&h=250&fit=crop'],
                ['Green Valley','Laguna','Lot Only','Pre-Selling','₱520,000','175 sqm','—','—','https://images.unsplash.com/photo-1501854140801-50d01698950b?w=400&h=250&fit=crop'],
            ] as $p)
            @include('pages.client.properties._card', ['p' => $p])
            @endforeach
        </div>
    </section>

    @else

    {{-- Filtered Results Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach([
            ['Palm Residences','Quezon City, Metro Manila','House and Lot','Pre-Selling','₱1,200,000','120 sqm','3','2','https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=400&h=250&fit=crop'],
            ['Greenfield Villas','Laguna','House and Lot','Pre-Selling','₱980,000','110 sqm','2','1','https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=400&h=250&fit=crop'],
            ['Sunrise Homes','Cavite','Lot Only','Pre-Selling','₱450,000','150 sqm','—','—','https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=250&fit=crop'],
            ['Hillside Estates','Rizal','House and Lot','Pre-Selling','₱1,350,000','135 sqm','3','2','https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400&h=250&fit=crop'],
            ['Metro Gardens','Bulacan','House and Lot','RFO','₱2,100,000','180 sqm','4','3','https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400&h=250&fit=crop'],
            ['Coastal View','Batangas','Lot Only','RFO','₱650,000','200 sqm','—','—','https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=400&h=250&fit=crop'],
            ['Villa Serena','Laguna','House and Lot','RFO','₱3,500,000','220 sqm','4','3','https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?w=400&h=250&fit=crop'],
            ['Brentwood Heights','Cavite','House and Lot','RFO','₱1,800,000','160 sqm','3','2','https://images.unsplash.com/photo-1583608205776-bfd35f0d9f83?w=400&h=250&fit=crop'],
        ] as $p)
        @include('pages.client.properties._card', ['p' => $p])
        @endforeach
    </div>

    @endif

</div>

@endsection
