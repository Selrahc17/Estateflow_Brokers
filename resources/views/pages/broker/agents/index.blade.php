@extends('layouts.broker')
@section('title', 'Agent Management')
@section('page-title', 'Agent Management')
@section('page-subtitle', 'Manage agents assigned to your account')

@section('content')

@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
@endif

@if($errors->any())
<div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
    <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Add Agent Form --}}
    <div class="bg-white rounded-xl border border-stone-200 p-6">
        <h2 class="font-semibold text-stone-800 mb-5 flex items-center gap-2">
            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            Add New Agent
        </h2>
        <form action="{{ route('broker.agents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Profile Photo</label>
                <div class="flex items-center gap-3">
                    <div class="w-14 h-14 bg-teal-100 rounded-xl flex items-center justify-center text-teal-800 font-bold text-xl shrink-0" id="avatar-preview-container">
                        <svg class="w-6 h-6 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <input type="file" name="avatar" id="avatar-input" accept="image/*" class="flex-1 border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-red-50 file:text-red-600 hover:file:bg-red-100">
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Full Name</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Juan dela Cruz" class="w-full border border-stone-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>
            <div>
                <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="agent@email.com" class="w-full border border-stone-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>
            <div>
                <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Phone</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+63 912 345 6789" class="w-full border border-stone-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>
            <div>
                <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Password</label>
                <input type="password" name="password" placeholder="••••••••" class="w-full border border-stone-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>
            <div>
                <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Confirm Password</label>
                <input type="password" name="password_confirmation" placeholder="••••••••" class="w-full border border-stone-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>
            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-lg text-sm font-semibold transition">
                Add Agent
            </button>
        </form>
    </div>

    {{-- Agent Cards --}}
    <div class="xl:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-stone-500">{{ $agents->total() }} agent(s) under your account</p>
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search agents..." class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 w-48">
                <button type="submit" class="bg-stone-100 hover:bg-stone-200 text-stone-700 px-3 py-2 rounded-lg text-sm transition">Search</button>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @forelse($agents as $agent)
            <div class="bg-white rounded-xl border border-stone-200 p-5 hover:shadow-md transition">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 overflow-hidden bg-teal-100 rounded-xl flex items-center justify-center text-teal-800 font-bold text-lg shrink-0">
                        @if($agent->avatar)
                            <img src="{{ str_starts_with($agent->avatar, 'http') ? $agent->avatar : asset('storage/' . $agent->avatar) }}" alt="{{ $agent->name }}" class="w-full h-full object-cover">
                        @else
                            {{ strtoupper(substr($agent->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-stone-800 truncate">{{ $agent->name }}</p>
                        <p class="text-xs text-stone-400 truncate">{{ $agent->email }}</p>
                        @if($agent->phone)
                            <p class="text-xs text-stone-400">{{ $agent->phone }}</p>
                        @endif
                    </div>
                    <span class="shrink-0 px-2 py-0.5 rounded-full text-xs font-medium {{ $agent->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
                        {{ $agent->is_active ? 'Active' : 'Suspended' }}
                    </span>
                </div>
                <div class="flex justify-between text-xs text-stone-500 pt-3 border-t border-stone-100 mb-3">
                    <span>Since {{ $agent->created_at->format('M Y') }}</span>
                    <span class="text-stone-400">{{ $agent->properties_count ?? 0 }} propert(ies)</span>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('broker.agents.edit', $agent) }}" class="flex-1 text-center border border-teal-200 hover:bg-teal-50 text-teal-700 py-2 rounded-lg text-xs font-medium transition">Edit</a>
                    <form action="{{ route('broker.agents.toggle-status', $agent) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="px-3 py-2 rounded-lg text-xs font-medium border transition {{ $agent->is_active ? 'border-red-200 text-red-500 hover:bg-red-50' : 'border-green-200 text-green-600 hover:bg-green-50' }}">
                            {{ $agent->is_active ? 'Suspend' : 'Activate' }}
                        </button>
                    </form>
                    <form action="{{ route('broker.agents.destroy', $agent) }}" method="POST" onsubmit="return confirm('Delete this agent?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="px-3 py-2 rounded-lg text-xs font-medium border border-stone-200 text-stone-500 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition">Delete</button>
                    </form>
                </div>
            </div>
            @empty
            <div class="sm:col-span-2 py-16 text-center text-stone-400 bg-white rounded-xl border border-stone-200">
                <svg class="w-10 h-10 mx-auto mb-3 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <p class="text-sm">No agents yet. Add your first agent using the form.</p>
            </div>
            @endforelse
        </div>

        @if($agents->hasPages())
            <div class="mt-4">{{ $agents->links() }}</div>
        @endif
    </div>

</div>

@push('scripts')
<script>
    document.getElementById('avatar-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(ev) {
            const container = document.getElementById('avatar-preview-container');
            container.innerHTML = `<img src="${ev.target.result}" class="w-full h-full object-cover rounded-xl">`;
        };
        reader.readAsDataURL(file);
    });
</script>
@endpush

@endsection
