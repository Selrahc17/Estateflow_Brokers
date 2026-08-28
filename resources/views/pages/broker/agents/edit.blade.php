@extends('layouts.broker')
@section('title', 'Edit Agent')
@section('page-title', 'Edit Agent')
@section('page-subtitle', 'Update {{ $agent->name }}')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Agent Card Preview --}}
    <div class="bg-white rounded-xl border border-stone-200 p-6 text-center">
        <div class="w-20 h-20 overflow-hidden bg-amber-100 rounded-2xl flex items-center justify-center text-amber-700 font-bold text-3xl mx-auto mb-4">
            @if($agent->avatar)
                <img src="{{ str_starts_with($agent->avatar, 'http') ? $agent->avatar : asset('storage/' . $agent->avatar) }}" class="w-full h-full object-cover">
            @else
                {{ strtoupper(substr($agent->name, 0, 1)) }}
            @endif
        </div>
        <p class="font-bold text-stone-800 text-lg">{{ $agent->name }}</p>
        <p class="text-sm text-stone-400">{{ $agent->email }}</p>
        @if($agent->phone)
            <p class="text-sm text-stone-400">{{ $agent->phone }}</p>
        @endif
        <span class="mt-2 inline-block px-3 py-1 rounded-full text-xs font-medium {{ $agent->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">
            {{ $agent->is_active ? 'Active' : 'Suspended' }}
        </span>
        <div class="mt-4 pt-4 border-t border-stone-100 text-xs text-stone-400">
            Member since {{ $agent->created_at->format('M d, Y') }}
        </div>
    </div>

    {{-- Edit Form --}}
    <div class="xl:col-span-2 bg-white rounded-xl border border-stone-200 p-6">
        <h2 class="font-semibold text-stone-800 mb-5">Update Agent Information</h2>

        @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form action="{{ route('broker.agents.update', $agent) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Profile Photo</label>
                <input type="file" name="avatar" accept="image/*" class="w-full border border-stone-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-red-50 file:text-red-600 hover:file:bg-red-100">
                <p class="text-xs text-stone-400 mt-1">Leave empty to keep current photo</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $agent->name) }}" class="w-full border border-stone-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $agent->phone) }}" placeholder="+63 912 345 6789" class="w-full border border-stone-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Email</label>
                <input type="email" name="email" value="{{ old('email', $agent->email) }}" class="w-full border border-stone-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">New Password <span class="normal-case text-stone-300">(optional)</span></label>
                    <input type="password" name="password" placeholder="••••••••" class="w-full border border-stone-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-1.5 block">Confirm Password</label>
                    <input type="password" name="password_confirmation" placeholder="••••••••" class="w-full border border-stone-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition">Save Changes</button>
                <a href="{{ route('broker.agents.index') }}" class="border border-stone-200 hover:bg-stone-50 text-stone-600 px-6 py-2.5 rounded-lg text-sm font-medium transition">Cancel</a>
            </div>
        </form>
    </div>

</div>
@endsection
