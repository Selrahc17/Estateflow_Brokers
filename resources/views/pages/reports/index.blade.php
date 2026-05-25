@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports & Analytics')
@section('page-subtitle', 'Overview of your business performance')

@section('content')

{{-- Summary Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Total Revenue','₱12.4M','↑ 18% vs last month','green'],
        ['Lots Sold','42','↑ 5 this month','green'],
        ['Active Clients','142','↑ 12 new','blue'],
        ['Pending Reservations','5','Needs action','yellow'],
    ] as $s)
    <div class="bg-white rounded-xl border border-stone-200 p-4">
        <p class="text-xs text-stone-500 mb-1">{{ $s[0] }}</p>
        <p class="text-xl font-bold text-stone-800">{{ $s[1] }}</p>
        <p class="text-xs mt-1 {{ $s[3]==='green' ? 'text-green-500' : ($s[3]==='blue' ? 'text-blue-500' : 'text-yellow-500') }}">{{ $s[2] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

    {{-- Monthly Sales Chart (Static Visual) --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Monthly Reservations</h2>
        <div class="flex items-end gap-2 h-40">
            @foreach([['Jan',4],['Feb',6],['Mar',5],['Apr',8],['May',7],['Jun',10],['Jul',9]] as $m)
            <div class="flex-1 flex flex-col items-center gap-1">
                <span class="text-xs text-stone-500">{{ $m[1] }}</span>
                <div class="w-full bg-amber-400 rounded-t-md" style="height: {{ $m[1] * 14 }}px"></div>
                <span class="text-xs text-stone-400">{{ $m[0] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Property Performance --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Property Performance</h2>
        <div class="space-y-4">
            @foreach([
                ['Palm Residences',75,'₱3.2M'],
                ['Greenfield Villas',55,'₱2.8M'],
                ['Sunrise Homes',72,'₱4.1M'],
                ['Hillside Estates',40,'₱1.5M'],
            ] as $p)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-stone-700 font-medium">{{ $p[0] }}</span>
                    <span class="text-stone-500">{{ $p[2] }}</span>
                </div>
                <div class="w-full bg-stone-100 rounded-full h-2">
                    <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $p[1] }}%"></div>
                </div>
                <p class="text-xs text-stone-400 mt-0.5">{{ $p[1] }}% lots sold</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Top Clients --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Top Clients by Payment</h2>
        <div class="space-y-3">
            @foreach([
                ['Juan dela Cruz','Palm Residences','₱600,000'],
                ['Pedro Reyes','Sunrise Homes','₱540,000'],
                ['Carlos Tan','Greenfield Villas','₱480,000'],
                ['Rosa Garcia','Hillside Estates','₱420,000'],
            ] as $c)
            <div class="flex items-center justify-between p-3 bg-stone-50 rounded-lg">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center text-amber-700 font-bold text-sm">{{ strtoupper(substr($c[0],0,1)) }}</div>
                    <div>
                        <p class="text-sm font-medium text-stone-700">{{ $c[0] }}</p>
                        <p class="text-xs text-stone-400">{{ $c[1] }}</p>
                    </div>
                </div>
                <span class="text-sm font-semibold text-stone-700">{{ $c[2] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Document Status --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Document Compliance</h2>
        <div class="space-y-3">
            @foreach([['Approved',18,'green'],['Pending',7,'yellow'],['Missing',4,'red']] as $d)
            <div class="flex items-center justify-between p-3 bg-stone-50 rounded-lg">
                <span class="text-sm text-stone-700">{{ $d[0] }}</span>
                <div class="flex items-center gap-3">
                    <div class="w-24 bg-stone-200 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $d[2]==='green' ? 'bg-green-500' : ($d[2]==='yellow' ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ $d[1]/29*100 }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-stone-700 w-6 text-right">{{ $d[1] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

</div>

@endsection
