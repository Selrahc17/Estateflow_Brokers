@extends('layouts.broker')
@section('title', 'Edit Agent')
@section('page-title', 'Edit Agent')
@section('page-subtitle', 'Update ' . $agent->name)

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- Agent Card Preview --}}
    <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-6 text-center">
        <div class="w-20 h-20 overflow-hidden bg-teal-100 dark:bg-teal-900 rounded-2xl flex items-center justify-center text-teal-800 dark:text-teal-200 font-bold text-3xl mx-auto mb-4">
            @if($agent->avatar)
                <img src="{{ str_starts_with($agent->avatar, 'http') ? $agent->avatar : asset('storage/' . $agent->avatar) }}" class="w-full h-full object-cover">
            @else
                {{ strtoupper(substr($agent->name, 0, 1)) }}
            @endif
        </div>
        <p class="font-bold text-stone-800 dark:text-stone-100 text-lg">{{ $agent->name }}</p>
        <p class="text-sm text-stone-400">{{ $agent->email }}</p>
        @if($agent->phone)
            <p class="text-sm text-stone-400">{{ $agent->phone }}</p>
        @endif
        <span class="mt-2 inline-block px-3 py-1 rounded-full text-xs font-medium {{ $agent->is_active ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300' : 'bg-red-100 dark:bg-red-950 text-red-600 dark:text-red-300' }}">
            {{ $agent->is_active ? 'Active' : 'Suspended' }}
        </span>
        <div class="mt-4 pt-4 border-t border-stone-100 dark:border-stone-800 text-xs text-stone-400">
            <p class="mb-1.5">PRC accreditation</p>
            @include('pages.broker._renewal-badge', ['expiresAt' => $agent->prc_id_expires_at])
            <p class="mt-3">Member since {{ $agent->created_at->format('M d, Y') }}</p>
        </div>
    </div>

    {{-- Edit Form --}}
    <div class="xl:col-span-2 bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-6">
        <h2 class="font-semibold text-stone-800 dark:text-stone-100 mb-5">Update Agent Information</h2>

        @if($errors->any())
        <div class="mb-4 p-3 bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-900 text-red-700 dark:text-red-300 rounded-lg text-sm">
            <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
        @endif

        <form action="{{ route('broker.agents.update', $agent) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="text-xs font-semibold text-stone-500 dark:text-stone-400 uppercase tracking-widest mb-1.5 block">Profile Photo</label>
                <input type="file" name="avatar" accept="image/*" class="w-full border border-stone-200 dark:border-stone-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                <p class="text-xs text-stone-400 mt-1">Leave empty to keep current photo</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-stone-500 dark:text-stone-400 uppercase tracking-widest mb-1.5 block">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $agent->name) }}" class="w-full border border-stone-200 dark:border-stone-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-500 dark:text-stone-400 uppercase tracking-widest mb-1.5 block">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $agent->phone) }}" placeholder="+63 912 345 6789" class="w-full border border-stone-200 dark:border-stone-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
            </div>
            <div>
                <label class="text-xs font-semibold text-stone-500 dark:text-stone-400 uppercase tracking-widest mb-1.5 block">Email</label>
                <input type="email" name="email" value="{{ old('email', $agent->email) }}" class="w-full border border-stone-200 dark:border-stone-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="license_number" class="text-xs font-semibold text-stone-500 dark:text-stone-400 uppercase tracking-widest mb-1.5 block">PRC Accreditation Number</label>
                    <input id="license_number" type="text" name="license_number" value="{{ old('license_number', $agent->license_number) }}" class="w-full border border-stone-200 dark:border-stone-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <div>
                    <label for="prc_id_expires_at" class="text-xs font-semibold text-stone-500 dark:text-stone-400 uppercase tracking-widest mb-1.5 block">Accreditation Expiry</label>
                    <input id="prc_id_expires_at" type="date" name="prc_id_expires_at" value="{{ old('prc_id_expires_at', $agent->prc_id_expires_at?->format('Y-m-d')) }}" class="w-full border border-stone-200 dark:border-stone-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-xs font-semibold text-stone-500 dark:text-stone-400 uppercase tracking-widest mb-1.5 block">New Password <span class="normal-case text-stone-300">(optional)</span></label>
                    <input type="password" name="password" placeholder="••••••••" class="w-full border border-stone-200 dark:border-stone-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <div>
                    <label class="text-xs font-semibold text-stone-500 dark:text-stone-400 uppercase tracking-widest mb-1.5 block">Confirm Password</label>
                    <input type="password" name="password_confirmation" placeholder="••••••••" class="w-full border border-stone-200 dark:border-stone-700 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white px-6 py-2.5 rounded-lg text-sm font-semibold transition">Save Changes</button>
                <a href="{{ route('broker.agents.index') }}" class="border border-stone-200 dark:border-stone-700 hover:bg-stone-50 dark:hover:bg-stone-800 text-stone-600 dark:text-stone-300 px-6 py-2.5 rounded-lg text-sm font-medium transition">Cancel</a>
            </div>
        </form>

        <div class="mt-8 border-t border-stone-100 dark:border-stone-800 pt-6">
            <h2 class="font-semibold text-stone-800 dark:text-stone-100 mb-1">Supporting Identification</h2>
            <p class="text-xs text-stone-400 mb-4">Upload the ID submitted by this agent.</p>
            @if(session('success'))
                <p class="mb-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</p>
            @endif
            <form action="{{ route('broker.agents.documents.store', $agent) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <select name="type" required class="border border-stone-200 dark:border-stone-700 rounded-lg px-3 py-2.5 text-sm">
                        <option value="">Select ID type</option>
                        <option value="drivers_license">Driver's License</option>
                        <option value="physical_id">Physical ID</option>
                        <option value="postal_id">Postal ID</option>
                        <option value="national_id">National ID</option>
                        <option value="other">Other ID</option>
                    </select>
                    <input type="file" name="file" required accept=".jpg,.jpeg,.png,.pdf" class="border border-stone-200 dark:border-stone-700 rounded-lg px-3 py-2 text-sm">
                </div>
                <button type="submit" class="bg-stone-800 hover:bg-stone-900 text-white px-4 py-2 rounded-lg text-sm font-medium">Upload ID</button>
            </form>
            <div class="mt-4 space-y-2">
                @forelse($agent->agentDocuments as $document)
                    <div class="flex items-center justify-between gap-3 border border-stone-100 dark:border-stone-800 rounded-lg px-3 py-2 text-sm">
                        <div>
                            <p class="font-medium text-stone-700 dark:text-stone-200">{{ ucfirst(str_replace('_', ' ', $document->type)) }}</p>
                            <p class="text-xs text-stone-400">{{ number_format($document->file_size / 1024, 1) }} KB · {{ ucfirst($document->status) }}</p>
                        </div>
                        <a href="{{ route('broker.agents.documents.download', [$agent, $document]) }}" class="text-xs text-teal-700 dark:text-teal-300 hover:underline">Download</a>
                    </div>
                @empty
                    <p class="text-sm text-stone-400">No supporting ID uploaded yet.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
