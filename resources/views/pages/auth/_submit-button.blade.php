{{-- Expects $label and $busyLabel. The surrounding Alpine scope must provide `submitting`. --}}
<button type="submit" :disabled="submitting"
    class="w-full flex items-center justify-center gap-2 bg-teal-700 hover:bg-teal-800 disabled:opacity-70 disabled:cursor-wait text-white py-3 rounded-xl font-medium transition active:scale-[0.98]">
    <svg x-show="submitting" style="display: none" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span x-text="submitting ? @js($busyLabel) : @js($label)">{{ $label }}</span>
</button>
