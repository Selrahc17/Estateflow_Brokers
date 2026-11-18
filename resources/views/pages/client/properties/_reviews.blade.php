{{-- Client reviews: summary, the viewer's own review or the form, then everyone's reviews. --}}
@php
    $viewer = auth()->user();
    $isClient = $viewer?->role === 'client';
    $total = (int) $reviewStats->total;
    $average = (float) $reviewStats->average;
    $displayName = function ($name) {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];
        $first = array_shift($parts) ?? 'Client';
        $last = array_pop($parts);

        return $last ? $first.' '.mb_strtoupper(mb_substr($last, 0, 1)).'.' : $first;
    };
    $startOpen = $errors->has('rating') || $errors->has('comment') || ($isClient && ! $myReview);
@endphp
<section id="reviews" class="bg-white rounded-2xl border border-stone-200 p-6 scroll-mt-24" aria-labelledby="reviews-heading">
    <h2 id="reviews-heading" class="font-semibold text-stone-800">Reviews</h2>

    {{-- Summary --}}
    <div class="mt-4 flex flex-col gap-5 sm:flex-row sm:items-center">
        <div class="shrink-0 text-center sm:w-36">
            <p class="text-4xl font-bold text-stone-800">{{ $total ? number_format($average, 1) : '—' }}</p>
            <div class="mt-1 flex justify-center">@include('pages.client.properties._stars', ['rating' => $average, 'size' => 'h-4 w-4'])</div>
            <p class="mt-1 text-xs text-stone-500">{{ $total }} {{ Str::plural('review', $total) }}</p>
        </div>
        <ul class="flex-1 space-y-1.5" aria-label="Rating breakdown">
            @for($star = 5; $star >= 1; $star--)
            @php $count = (int) ($ratingBreakdown[$star] ?? 0); @endphp
            <li class="flex items-center gap-2 text-xs text-stone-600">
                <span class="w-10 shrink-0">{{ $star }} star</span>
                <span class="h-2 flex-1 overflow-hidden rounded-full bg-stone-100"><span class="block h-full rounded-full bg-amber-400" style="width: {{ $total ? round($count / $total * 100) : 0 }}%"></span></span>
                <span class="w-6 shrink-0 text-right">{{ $count }}</span>
            </li>
            @endfor
        </ul>
    </div>

    {{-- The viewer's review --}}
    <div class="mt-6 border-t border-stone-100 pt-5">
        @guest
        <p class="text-sm text-stone-600"><a href="{{ route('auth.login') }}" class="font-semibold text-teal-700 hover:underline">Log in</a> or <a href="{{ route('auth.register') }}" class="font-semibold text-teal-700 hover:underline">create an account</a> to leave a review.</p>
        @endguest

        @auth
        @if(! $isClient)
        <p class="text-sm text-stone-500">Only clients can leave reviews.</p>
        @else
        <div x-data="{ editing: @js($startOpen), rating: {{ (int) old('rating', $myReview?->rating ?? 0) }}, hover: 0 }">
            @if($myReview)
            <div x-show="! editing" class="rounded-xl border border-teal-100 bg-teal-50/50 p-4">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-sm font-semibold text-stone-800">Your review</p>
                    <div class="flex items-center gap-3 text-xs font-semibold">
                        <button type="button" @click="editing = true" class="text-teal-700 hover:underline">Edit</button>
                        <form action="{{ route('client.account.reviews.destroy', $property) }}" method="POST" onsubmit="return confirm('Delete your review?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>
                <div class="mt-1 flex items-center gap-2">
                    @include('pages.client.properties._stars', ['rating' => $myReview->rating, 'size' => 'h-4 w-4'])
                    <span class="sr-only">{{ $myReview->rating }} out of 5</span>
                </div>
                @if($myReview->comment)<p class="mt-2 whitespace-pre-line text-sm text-stone-700">{{ $myReview->comment }}</p>@endif
                @if($myReview->isHidden())
                <p class="mt-2 text-xs text-amber-800">An admin has hidden this review, so other visitors can't see it.</p>
                @endif
            </div>
            @endif

            <form x-show="editing" @if(! $startOpen) style="display: none" @endif action="{{ route('client.account.reviews.store', $property) }}" method="POST" class="space-y-3">
                @csrf
                <fieldset>
                    <legend class="text-sm font-semibold text-stone-800">{{ $myReview ? 'Edit your review' : 'Rate this property' }}</legend>
                    <div class="mt-2 flex items-center gap-1" @mouseleave="hover = 0">
                        @for($star = 1; $star <= 5; $star++)
                        <label class="cursor-pointer rounded focus-within:ring-2 focus-within:ring-teal-400" @mouseenter="hover = {{ $star }}">
                            <input type="radio" name="rating" value="{{ $star }}" x-model.number="rating" class="sr-only" @checked((int) old('rating', $myReview?->rating) === $star) required>
                            <span class="sr-only">{{ $star }} {{ Str::plural('star', $star) }}</span>
                            <svg class="h-8 w-8 transition" :class="(hover || rating) >= {{ $star }} ? 'text-amber-400' : 'text-stone-300'" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </label>
                        @endfor
                        <span class="ml-2 text-sm text-stone-500" x-text="['', 'Poor', 'Fair', 'Good', 'Very good', 'Excellent'][hover || rating]"></span>
                    </div>
                    @error('rating')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </fieldset>
                <div>
                    <label for="review-comment" class="text-sm font-medium text-stone-700">Comment <span class="font-normal text-stone-500">(optional)</span></label>
                    <textarea id="review-comment" name="comment" rows="3" maxlength="1000" placeholder="What did you like? How was the location, the unit, or the agent?"
                              class="mt-1 w-full rounded-xl border border-stone-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">{{ old('comment', $myReview?->comment) }}</textarea>
                    @error('comment')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="rounded-xl bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800">{{ $myReview ? 'Update review' : 'Post review' }}</button>
                    @if($myReview)
                    <button type="button" @click="editing = false" class="rounded-xl px-4 py-2 text-sm font-semibold text-stone-600 hover:bg-stone-100">Cancel</button>
                    @endif
                </div>
                <p class="text-xs text-stone-500">Your first name and last initial are shown with your review.</p>
            </form>
        </div>
        @endif
        @endauth
    </div>

    {{-- Everyone's reviews --}}
    <div class="mt-6 border-t border-stone-100 pt-5">
        @forelse($reviews as $review)
        <article class="py-4 first:pt-0 [&:not(:last-child)]:border-b [&:not(:last-child)]:border-stone-100">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-teal-700 text-sm font-semibold text-white" aria-hidden="true">{{ mb_strtoupper(mb_substr($review->user?->name ?? 'C', 0, 1)) }}</span>
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1">
                        <p class="text-sm font-semibold text-stone-800">{{ $displayName($review->user?->name) }}</p>
                        <time datetime="{{ $review->created_at->toDateString() }}" class="text-xs text-stone-500">{{ $review->created_at->format('M d, Y') }}</time>
                    </div>
                    <div class="mt-0.5 flex items-center gap-2">
                        @include('pages.client.properties._stars', ['rating' => $review->rating, 'size' => 'h-3.5 w-3.5'])
                        <span class="sr-only">{{ $review->rating }} out of 5</span>
                    </div>
                    @if($review->comment)<p class="mt-2 whitespace-pre-line break-words text-sm text-stone-700">{{ $review->comment }}</p>@endif
                </div>
            </div>
        </article>
        @empty
        <p class="text-sm text-stone-500">No reviews yet. {{ $isClient ? 'Be the first to share your experience.' : '' }}</p>
        @endforelse
    </div>
</section>
