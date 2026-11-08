{{-- Optional developer name and logo. Expects $property on the edit form. --}}
@php
    $property = $property ?? null;
    $inputClass = 'w-full rounded-lg border border-stone-200 px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400';
@endphp
<div class="grid grid-cols-1 gap-4 sm:grid-cols-[1fr_auto] sm:items-start">
    <div>
        <label for="developer" class="mb-1 block text-sm font-medium text-stone-700">Developer <span class="text-xs font-normal text-stone-500">(optional)</span></label>
        <input id="developer" type="text" name="developer" maxlength="255" value="{{ old('developer', $property?->developer) }}" placeholder="e.g. Sunrise Land Corp." class="{{ $inputClass }} @error('developer') border-red-400 @enderror">
        <p class="mt-1 text-xs text-stone-500">Leave blank for an individually owned property.</p>
        @error('developer')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div x-data="imagePicker({ max: 1, replace: true })" class="sm:w-64">
        <label for="developer-logo" class="mb-1 block text-sm font-medium text-stone-700">Developer Logo <span class="text-xs font-normal text-stone-500">(JPG, PNG, or WebP, up to 2 MB)</span></label>
        <div class="flex items-center gap-3">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-stone-200 bg-white">
                <template x-if="files.length">
                    <img :src="files[0].url" alt="New developer logo" class="h-full w-full object-contain p-1">
                </template>
                @if($property?->developer_logo)
                <img x-show="!files.length" src="{{ $property->developer_logo }}" alt="Current developer logo" class="h-full w-full object-contain p-1">
                @else
                <svg x-show="!files.length" class="h-6 w-6 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                @endif
            </div>
            <input id="developer-logo" type="file" name="developer_logo" accept="image/png,image/jpeg,image/webp" x-ref="input" @change="add($event)" class="min-w-0 flex-1 text-xs text-stone-600 file:mr-2 file:rounded-lg file:border-0 file:bg-teal-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-teal-700 hover:file:bg-teal-100">
        </div>
        <p x-show="error" x-text="error" role="alert" style="display: none" class="mt-1 text-xs text-red-500"></p>
        @if($property?->developer_logo)
        <label class="mt-2 flex items-center gap-2 text-xs text-stone-600">
            <input type="checkbox" name="remove_developer_logo" value="1" class="rounded">
            Remove current logo
        </label>
        @endif
        @error('developer_logo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
