@extends('layouts.admin')
@section('title', 'Feedback Management')
@section('page-title', 'Feedback Management')
@section('page-subtitle', 'Review and manage all client feedback and ratings')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Total Reviews','128','text-stone-800'],
        ['Average Rating','4.8 ★','text-teal-700'],
        ['5-Star Reviews','78','text-green-600'],
        ['Flagged','2','text-red-500'],
    ] as $s)
    <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
        <p class="text-2xl font-bold {{ $s[2] }}">{{ $s[1] }}</p>
        <p class="text-xs text-stone-500 mt-1">{{ $s[0] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Feedback List --}}
    <div class="xl:col-span-2 space-y-4">
        <div class="flex gap-2 mb-2">
            @foreach(['All','5 Star','4 Star','3 Star','Flagged'] as $tab)
            <button class="text-xs px-3 py-1.5 rounded-lg {{ $tab==='All' ? 'bg-red-600 text-white' : 'bg-white border border-stone-200 text-stone-500 hover:bg-stone-50' }} transition font-medium">{{ $tab }}</button>
            @endforeach
        </div>

        @foreach([
            ['Maria S.','5','The broker was very professional and responsive. Highly recommended!','Jun 28, 2025','Broker Santos','Palm Residences','Published'],
            ['Pedro R.','4','Smooth reservation process. Very satisfied with the service.','Jun 20, 2025','Broker Reyes','Greenfield Villas','Published'],
            ['Ana L.','5','Great experience from start to finish. Very satisfied!','Jun 15, 2025','Broker Lim','Sunrise Homes','Published'],
            ['Carlo M.','2','The process was slow and communication was poor.','Jun 10, 2025','Broker Cruz','Hillside Estates','Flagged'],
            ['Rosa G.','5','Very transparent with pricing. Will recommend to friends!','Jun 5, 2025','Broker Santos','Metro Gardens','Published'],
        ] as $rev)
        <div class="bg-white rounded-xl border {{ $rev[6]==='Flagged' ? 'border-red-200' : 'border-stone-200' }} p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-teal-100 rounded-full flex items-center justify-center text-teal-800 font-bold text-sm shrink-0">
                        {{ strtoupper(substr($rev[0],0,1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-stone-800 text-sm">{{ $rev[0] }}</p>
                        <div class="flex items-center gap-1 mt-0.5">
                            @for($s=1;$s<=5;$s++)
                            <svg class="w-3 h-3 {{ $s<=(int)$rev[1] ? 'text-teal-500' : 'text-stone-200' }}" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            @endfor
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs {{ $rev[6]==='Flagged' ? 'bg-red-100 text-red-600' : 'bg-green-100 text-green-700' }} px-2 py-1 rounded-full font-medium">{{ $rev[6] }}</span>
                    <span class="text-xs text-stone-400">{{ $rev[3] }}</span>
                </div>
            </div>
            <p class="text-sm text-stone-600 italic mb-3">"{{ $rev[2] }}"</p>
            <div class="flex items-center justify-between text-xs text-stone-400">
                <span>{{ $rev[4] }} · {{ $rev[5] }}</span>
                <div class="flex gap-2">
                    @if($rev[6]==='Flagged')
                    <button class="text-green-600 hover:underline font-medium">Restore</button>
                    <button class="text-red-500 hover:underline font-medium">Delete</button>
                    @else
                    <button class="text-teal-700 hover:underline">Flag</button>
                    <button class="text-red-500 hover:underline">Delete</button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Rating Summary --}}
    <div class="space-y-5">
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h3 class="font-semibold text-stone-800 mb-4">Rating Breakdown</h3>
            <div class="text-center mb-4">
                <p class="text-5xl font-bold text-teal-600">4.8</p>
                <div class="flex justify-center gap-1 my-2">
                    @for($s=1;$s<=5;$s++)
                    <svg class="w-5 h-5 text-teal-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    @endfor
                </div>
                <p class="text-xs text-stone-400">128 total reviews</p>
            </div>
            <div class="space-y-2">
                @foreach([[5,78],[4,32],[3,12],[2,4],[1,2]] as $bar)
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-stone-500 w-3">{{ $bar[0] }}</span>
                    <svg class="w-3 h-3 text-teal-500 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    <div class="flex-1 bg-stone-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-teal-500 h-2 rounded-full" style="width: {{ round($bar[1]/128*100) }}%"></div>
                    </div>
                    <span class="text-stone-400 w-5 text-right">{{ $bar[1] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h3 class="font-semibold text-stone-800 mb-4">By Broker</h3>
            <div class="space-y-3">
                @foreach([
                    ['Broker Santos','4.9','32 reviews'],
                    ['Broker Reyes','4.8','28 reviews'],
                    ['Broker Lim','4.7','22 reviews'],
                    ['Broker Cruz','4.6','18 reviews'],
                ] as $b)
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-stone-700">{{ $b[0] }}</p>
                        <p class="text-xs text-stone-400">{{ $b[2] }}</p>
                    </div>
                    <span class="text-sm font-bold text-teal-700">{{ $b[1] }} ★</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

@endsection
