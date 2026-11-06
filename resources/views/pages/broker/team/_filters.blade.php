{{-- Expects $agents, $statuses (value => label), and $searchPlaceholder. --}}
<form method="GET" class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_200px_200px_auto]">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="{{ $searchPlaceholder }}" aria-label="Search" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
    <select name="agent" aria-label="Filter by agent" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
        <option value="">All agents</option>
        @foreach($agents as $agent)
            <option value="{{ $agent->id }}" @selected((int) request('agent') === $agent->id)>{{ $agent->name }}</option>
        @endforeach
    </select>
    <select name="status" aria-label="Filter by status" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
        <option value="">All statuses</option>
        @foreach($statuses as $value => $label)
            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <div class="flex gap-2">
        <button type="submit" class="flex-1 rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-800">Filter</button>
        @if(request()->hasAny(['search', 'agent', 'status']))
            <a href="{{ url()->current() }}" class="rounded-lg border border-stone-200 dark:border-stone-700 px-4 py-2 text-center text-sm font-medium text-stone-600 dark:text-stone-300 transition hover:bg-stone-50 dark:hover:bg-stone-800">Clear</a>
        @endif
    </div>
</form>
