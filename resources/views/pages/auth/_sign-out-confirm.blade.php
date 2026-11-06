{{-- Expects $triggerClass; set $showIcon to false for a text-only button. --}}
<div x-data>
    <button type="button" @click="$refs.signOut.showModal()" class="{{ $triggerClass }}">
        @if($showIcon ?? true)
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        @endif
        Sign Out
    </button>

    <dialog x-ref="signOut" aria-labelledby="sign-out-heading" @click="if ($event.target === $el) $el.close()"
        class="m-auto w-[calc(100%-2rem)] max-w-sm rounded-2xl bg-white dark:bg-stone-900 p-6 text-left text-stone-800 dark:text-stone-100 shadow-xl backdrop:bg-black/50">
        <h2 id="sign-out-heading" class="text-lg font-semibold text-stone-800 dark:text-white">Sign out?</h2>
        <p class="mt-1 text-sm text-stone-600 dark:text-stone-300">You will need to sign in again to use your account.</p>
        <form action="{{ route('auth.logout') }}" method="POST" class="mt-5 flex justify-end gap-3">
            @csrf
            <button type="button" @click="$refs.signOut.close()" class="rounded-lg border border-stone-200 dark:border-stone-700 px-4 py-2 text-sm font-medium text-stone-600 dark:text-stone-300 transition hover:bg-stone-50 dark:hover:bg-stone-800">Stay Signed In</button>
            <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-800">Sign Out</button>
        </form>
    </dialog>
</div>
