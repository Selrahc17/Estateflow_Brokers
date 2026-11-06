@php
    $currentFeatured = $currentFeatured ?? null;
    $currentImages = $currentImages ?? [];
    $maxImages = \App\Models\Property::MAX_GALLERY_IMAGES;
    $fileInputClass = 'w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-teal-50 file:text-teal-800 hover:file:bg-teal-100';
    $removeButtonClass = 'absolute -top-2 -right-2 w-6 h-6 flex items-center justify-center rounded-full bg-stone-800 hover:bg-red-600 text-white text-sm leading-none transition';
@endphp

<div x-data="imagePicker({ max: 1, replace: true })">
    <label for="featured-image" class="block text-sm font-medium text-stone-700 mb-1">
        Featured Image <span class="text-stone-400 font-normal text-xs">(up to 4 MB)</span>
    </label>
    @if($currentFeatured)
        <img src="{{ $currentFeatured }}" alt="Current featured image" x-show="!files.length" class="mb-2 h-24 w-36 rounded-lg object-cover border border-stone-200">
    @endif
    <template x-for="(item, index) in files" :key="item.url">
        <div class="relative mb-2 h-24 w-36">
            <img :src="item.url" :alt="item.file.name" class="h-24 w-36 rounded-lg object-cover border border-stone-200">
            <button type="button" @click="remove(index)" :aria-label="'Remove ' + item.file.name" class="{{ $removeButtonClass }}">&times;</button>
        </div>
    </template>
    <input id="featured-image" type="file" name="featured_image" accept="image/*" x-ref="input" @change="add($event)" class="{{ $fileInputClass }}">
    <p x-show="error" x-text="error" role="alert" style="display: none" class="text-xs text-red-500 mt-1"></p>
    @error('featured_image')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
</div>

<div x-data="imagePicker({ max: {{ $maxImages }}, existing: {{ count($currentImages) }} })">
    <div class="flex items-center justify-between gap-3 mb-1">
        <label for="gallery-images" class="block text-sm font-medium text-stone-700">
            Additional Images <span class="text-stone-400 font-normal text-xs">(up to {{ $maxImages }} photos, 4 MB each)</span>
        </label>
        <span class="shrink-0 text-xs font-medium text-stone-500" x-text="used + ' of ' + max + ' photos'">{{ count($currentImages) }} of {{ $maxImages }} photos</span>
    </div>

    @if($currentImages)
        <div class="flex flex-wrap gap-3 mb-3">
            @foreach($currentImages as $image)
                <label class="block w-28 cursor-pointer">
                    <img src="{{ $image }}" alt="" :class="removed.includes(@js($image)) && 'opacity-40'" class="h-20 w-28 rounded-lg object-cover border border-stone-200 transition">
                    <span class="mt-1 flex items-center gap-1.5 text-xs text-stone-600">
                        <input type="checkbox" name="remove_images[]" value="{{ $image }}" x-model="removed" @change="error = ''" class="rounded border-stone-300 text-red-500 focus:ring-red-400">
                        Remove
                    </span>
                </label>
            @endforeach
        </div>
    @endif

    <input id="gallery-images" type="file" name="images[]" accept="image/*" multiple x-ref="input" @change="add($event)" class="{{ $fileInputClass }}">
    <p class="text-xs text-stone-400 mt-1">You can pick photos more than once. Each new selection is added to the list.</p>

    <div x-show="files.length" style="display: none" class="mt-3 flex flex-wrap gap-3">
        <template x-for="(item, index) in files" :key="item.url">
            <div class="relative h-20 w-28">
                <img :src="item.url" :alt="item.file.name" class="h-20 w-28 rounded-lg object-cover border border-stone-200">
                <button type="button" @click="remove(index)" :aria-label="'Remove ' + item.file.name" class="{{ $removeButtonClass }}">&times;</button>
            </div>
        </template>
    </div>

    <p x-show="error" x-text="error" role="alert" style="display: none" class="text-xs text-red-500 mt-1"></p>
    <p x-show="used > max" role="alert" style="display: none" class="text-xs text-red-500 mt-1">This is over the limit. Remove some photos before saving.</p>
    @error('images')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
    @error('images.*')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
</div>

@if($errors->any())
    <p class="text-xs text-amber-700">Browsers do not keep chosen files after a form error. If you picked new photos, please choose them again.</p>
@endif
