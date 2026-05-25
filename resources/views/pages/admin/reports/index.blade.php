@extends('layouts.admin')
@section('title', 'Reports & Analytics')
@section('page-title', 'Reports & Analytics')
@section('page-subtitle', 'System-wide performance metrics and insights')

@section('content')

{{-- KPI Cards --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Total Revenue','₱12.4M','↑ 18% vs last month','text-green-600','bg-green-50 border-green-100'],
        ['Total Reservations','342','↑ 28 this month','text-blue-600','bg-blue-50 border-blue-100'],
        ['Active Clients','1,290','↑ 45 new','text-amber-600','bg-amber-50 border-amber-100'],
        ['Lots Sold','186','↑ 12 this month','text-red-600','bg-red-50 border-red-100'],
    ] as $s)
    <div class="bg-white rounded-xl border {{ $s[4] }} p-5">
        <p class="text-xs text-stone-500 mb-1">{{ $s[0] }}</p>
        <p class="text-2xl font-bold text-stone-800">{{ $s[1] }}</p>
        <p class="text-xs {{ $s[2] }} mt-1">{{ $s[2] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-2 gap-5 mb-5">

    {{-- Revenue Chart --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-stone-800">Monthly Revenue (2025)</h2>
            <span class="text-xs text-green-600 font-semibold bg-green-50 px-2 py-1 rounded-full">↑ 18% YoY</span>
        </div>
        <div class="flex items-end gap-2 h-40">
            @foreach([['Jan',8],['Feb',12],['Mar',10],['Apr',15],['May',14],['Jun',18],['Jul',16]] as $m)
            <div class="flex-1 flex flex-col items-center gap-1">
                <span class="text-xs text-stone-500">{{ $m[1] }}M</span>
                <div class="w-full bg-red-500 rounded-t-md hover:bg-red-600 transition" style="height: {{ $m[1] * 8 }}px"></div>
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
                ['Palm Residences',75,'₱3.2M','24 lots'],
                ['Greenfield Villas',55,'₱2.8M','36 lots'],
                ['Sunrise Homes',72,'₱4.1M','48 lots'],
                ['Hillside Estates',40,'₱1.5M','20 lots'],
                ['Metro Gardens',30,'₱0.8M','30 lots'],
            ] as $p)
            <div>
                <div class="flex justify-between text-sm mb-1">
                    <span class="font-medium text-stone-700">{{ $p[0] }}</span>
                    <div class="flex items-center gap-3 text-xs text-stone-500">
                        <span>{{ $p[3] }}</span>
                        <span class="font-semibold text-stone-700">{{ $p[2] }}</span>
                    </div>
                </div>
                <div class="w-full bg-stone-100 rounded-full h-2 overflow-hidden">
                    <div class="bg-red-500 h-2 rounded-full" style="width: {{ $p[1] }}%"></div>
                </div>
                <p class="text-xs text-stone-400 mt-0.5">{{ $p[1] }}% lots sold/reserved</p>
            </div>
            @endforeach
        </div>
    </div>

</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Top Brokers --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Top Brokers</h2>
        <div class="space-y-3">
            @foreach([
                ['Broker Cruz','₱4.1M','20 clients','1'],
                ['Broker Santos','₱3.2M','15 clients','2'],
                ['Broker Reyes','₱2.8M','12 clients','3'],
                ['Broker Lim','₱1.9M','8 clients','4'],
            ] as $b)
            <div class="flex items-center gap-3 p-3 bg-stone-50 rounded-xl">
                <div class="w-7 h-7 {{ $b[3]==='1' ? 'bg-amber-500' : 'bg-stone-200' }} rounded-full flex items-center justify-center text-xs font-bold {{ $b[3]==='1' ? 'text-white' : 'text-stone-600' }}">{{ $b[3] }}</div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-stone-700">{{ $b[0] }}</p>
                    <p class="text-xs text-stone-400">{{ $b[2] }}</p>
                </div>
                <span class="text-sm font-bold text-green-600">{{ $b[1] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Document Compliance --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Document Compliance</h2>
        <div class="text-center mb-4">
            <p class="text-4xl font-bold text-green-600">87.6%</p>
            <p class="text-xs text-stone-400 mt-1">Overall compliance rate</p>
        </div>
        <div class="space-y-3">
            @foreach([['Approved',78,'green'],['Pending',3,'yellow'],['Missing',8,'red']] as $d)
            <div class="flex items-center justify-between p-3 bg-stone-50 rounded-xl">
                <span class="text-sm text-stone-600">{{ $d[0] }}</span>
                <div class="flex items-center gap-2">
                    <div class="w-20 bg-stone-200 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full {{ $d[2]==='green' ? 'bg-green-500' : ($d[2]==='yellow' ? 'bg-yellow-400' : 'bg-red-400') }}" style="width: {{ round($d[1]/89*100) }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-stone-700 w-5 text-right">{{ $d[1] }}</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Client Growth --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Client Growth</h2>
        <div class="space-y-3">
            @foreach([
                ['January','45 new clients'],
                ['February','52 new clients'],
                ['March','38 new clients'],
                ['April','61 new clients'],
                ['May','55 new clients'],
                ['June','48 new clients'],
                ['July','45 new clients'],
            ] as $g)
            <div class="flex items-center justify-between text-sm">
                <span class="text-stone-500">{{ $g[0] }}</span>
                <span class="font-medium text-stone-700">{{ $g[1] }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>

@endsection
