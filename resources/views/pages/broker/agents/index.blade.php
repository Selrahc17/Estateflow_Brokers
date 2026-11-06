@extends('layouts.broker')
@section('title', 'Agent Management')
@section('page-title', 'Agent Management')
@section('page-subtitle', 'Invite agents and keep your team up to date')

@section('content')
@inject('compliance', 'App\Services\BrokerComplianceService')
@php
    $usedPercent = $agentLimit > 0 ? min(100, round($agentCount / $agentLimit * 100)) : 100;
    $inputClass = 'w-full rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 px-3 py-2.5 text-sm text-stone-800 dark:text-stone-100 placeholder:text-stone-400 dark:placeholder:text-stone-500 focus:outline-none focus:ring-2 focus:ring-teal-400';
    $labelClass = 'mb-1.5 block text-sm font-medium text-stone-600 dark:text-stone-300';
    $menuItemClass = 'block w-full px-4 py-2 text-left text-sm transition hover:bg-stone-50 dark:hover:bg-stone-700';
    $tiles = [
        'active' => ['Active', $summary['active'], 'text-green-700 dark:text-green-300'],
        'invited' => ['Invited', $summary['invited'], 'text-amber-800 dark:text-amber-300'],
        'suspended' => ['Suspended', $summary['suspended'], 'text-stone-700 dark:text-stone-200'],
        'renewal' => ['Needs Renewal', $summary['renewal'], 'text-red-700 dark:text-red-300'],
    ];
@endphp

{{-- The dialog sits further down the page, so it only exists after the first tick. --}}
<div x-data @if($errors->any() && ! $atLimit) x-init="$nextTick(() => $refs.invite.showModal())" @endif>

    @if(session('success'))
    <div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
    @endif

    @if(session('invite_link'))
    <div class="mb-5 rounded-lg border border-teal-200 dark:border-teal-800 bg-teal-50 dark:bg-teal-950 px-4 py-3" x-data="{
        copyState: 'idle',
        async copy() {
            this.$refs.link.select();
            try {
                await navigator.clipboard.writeText(this.$refs.link.value);
                this.copyState = 'copied';
            } catch {
                {{-- The clipboard API needs HTTPS or localhost; the selected text can still be copied this way. --}}
                this.copyState = document.execCommand('copy') ? 'copied' : 'failed';
            }
        },
    }">
        <p class="text-sm font-medium text-teal-800 dark:text-teal-200">Share this link with your agent</p>
        <p class="mt-0.5 text-xs text-teal-700 dark:text-teal-300">It is only shown once. If you lose it, use Resend Invite to create a new one.</p>
        <div class="mt-2 flex gap-2">
            <input x-ref="link" type="text" readonly value="{{ session('invite_link') }}" aria-label="Invitation link" @focus="$event.target.select()" class="min-w-0 flex-1 rounded-lg border border-teal-200 dark:border-teal-800 bg-white dark:bg-stone-900 px-3 py-2 text-xs text-stone-700 dark:text-stone-200">
            <button type="button" @click="copy()" class="shrink-0 rounded-lg bg-teal-700 px-4 py-2 text-xs font-medium text-white transition hover:bg-teal-800" x-text="copyState === 'copied' ? 'Copied' : 'Copy'">Copy</button>
        </div>
        <p x-show="copyState === 'failed'" style="display: none" class="mt-2 text-xs text-teal-800 dark:text-teal-200">Your browser blocked copying. The link is selected, so press Ctrl+C.</p>
    </div>
    @endif

    {{-- Team capacity --}}
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="w-full sm:max-w-xs">
            <div class="mb-1.5 flex items-baseline justify-between gap-3">
                <p class="text-sm font-semibold text-stone-800 dark:text-stone-100">{{ $agentCount }} of {{ $agentLimit }} agents</p>
                <p class="text-xs text-stone-600 dark:text-stone-400">{{ max(0, $agentLimit - $agentCount) }} {{ Str::plural('slot', max(0, $agentLimit - $agentCount)) }} left</p>
            </div>
            <div class="h-1.5 overflow-hidden rounded-full bg-stone-200 dark:bg-stone-700" role="progressbar" aria-label="Team capacity" aria-valuenow="{{ $agentCount }}" aria-valuemin="0" aria-valuemax="{{ $agentLimit }}">
                <div class="h-full rounded-full {{ $atLimit ? 'bg-amber-500' : 'bg-teal-600 dark:bg-teal-400' }}" style="width: {{ $usedPercent }}%"></div>
            </div>
        </div>
        @if($atLimit)
        <p class="rounded-lg border border-amber-200 dark:border-amber-900 bg-amber-50 dark:bg-amber-950 px-3 py-2 text-sm text-amber-800 dark:text-amber-300">You have reached the limit of {{ $agentLimit }} agents per broker.</p>
        @else
        <button type="button" @click="$refs.invite.showModal()" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Invite Agent
        </button>
        @endif
    </div>

    {{-- Status filters --}}
    <nav aria-label="Filter agents by status" class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach($tiles as $key => [$label, $count, $color])
        @php($isCurrent = $status === $key)
        <a href="{{ $isCurrent ? route('broker.agents.index', array_filter(['search' => request('search')])) : route('broker.agents.index', array_filter(['status' => $key, 'search' => request('search')])) }}"
            @if($isCurrent) aria-current="true" @endif
            class="rounded-xl border bg-white dark:bg-stone-900 px-4 py-3 transition {{ $isCurrent ? 'border-teal-500 ring-1 ring-teal-500' : 'border-stone-200 dark:border-stone-700 hover:border-stone-300 dark:hover:border-stone-600' }}">
            <p class="text-2xl font-bold {{ $color }}">{{ $count }}</p>
            <p class="text-xs text-stone-500 dark:text-stone-400">{{ $label }}</p>
        </a>
        @endforeach
    </nav>

    {{-- Search --}}
    <form method="GET" class="mb-4 flex gap-2">
        @if($status)
            <input type="hidden" name="status" value="{{ $status }}">
        @endif
        <div class="relative min-w-0 flex-1 sm:max-w-sm">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name or email..." aria-label="Search agents" class="w-full rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 py-2 pl-9 pr-3 text-sm text-stone-800 dark:text-stone-100 placeholder:text-stone-400 dark:placeholder:text-stone-500 focus:outline-none focus:ring-2 focus:ring-teal-400">
        </div>
        <button type="submit" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-4 py-2 text-sm font-medium text-stone-700 dark:text-stone-200 transition hover:bg-stone-50 dark:hover:bg-stone-800">Search</button>
        @if($status || request('search'))
            <a href="{{ route('broker.agents.index') }}" class="rounded-lg px-3 py-2 text-sm font-medium text-teal-700 dark:text-teal-300 hover:underline">Clear</a>
        @endif
    </form>

    {{-- Agent list. Not overflow-hidden, so the action menus can extend past the last row. --}}
    <div class="rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900">
        <div class="hidden grid-cols-[minmax(0,2.4fr)_100px_minmax(0,1.6fr)_72px_72px_160px] gap-4 rounded-t-xl border-b border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/60 px-5 py-3 text-xs font-medium text-stone-500 dark:text-stone-400 lg:grid" aria-hidden="true">
            <span>Agent</span>
            <span>Status</span>
            <span>Accreditation</span>
            <span>Listings</span>
            <span>Clients</span>
            <span class="text-right">Actions</span>
        </div>

        <ul class="divide-y divide-stone-100 dark:divide-stone-800">
            @forelse($agents as $agent)
            @php($invited = $agent->isInvited())
            <li class="grid grid-cols-1 gap-3 px-5 py-4 lg:grid-cols-[minmax(0,2.4fr)_100px_minmax(0,1.6fr)_72px_72px_160px] lg:items-center lg:gap-4">

                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-teal-100 dark:bg-teal-900 text-sm font-bold text-teal-800 dark:text-teal-200">
                        @if($agent->avatar)
                            <img src="{{ str_starts_with($agent->avatar, 'http') ? $agent->avatar : asset('storage/' . $agent->avatar) }}" alt="" class="h-full w-full object-cover">
                        @else
                            {{ strtoupper(substr($agent->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-stone-800 dark:text-stone-100">{{ $agent->name }}</p>
                        <p class="truncate text-xs text-stone-500 dark:text-stone-400">{{ $agent->email }}</p>
                        @if($agent->phone)
                            <p class="truncate text-xs text-stone-500 dark:text-stone-400">{{ $agent->phone }}</p>
                        @endif
                    </div>
                </div>

                <div>
                    @if($invited)
                        <span class="rounded-full bg-amber-100 dark:bg-amber-950 px-2 py-0.5 text-xs font-medium text-amber-800 dark:text-amber-300">Invited</span>
                    @elseif($agent->is_active)
                        <span class="rounded-full bg-green-100 dark:bg-green-950 px-2 py-0.5 text-xs font-medium text-green-700 dark:text-green-300">Active</span>
                    @else
                        <span class="rounded-full bg-red-100 dark:bg-red-950 px-2 py-0.5 text-xs font-medium text-red-700 dark:text-red-300">Suspended</span>
                    @endif
                </div>

                <div class="flex items-center justify-between gap-3 text-xs text-stone-500 dark:text-stone-400 lg:block">
                    <span class="lg:hidden">Accreditation</span>
                    @if($invited)
                        @if(! $agent->invitation_expires_at)
                            <span>No link yet</span>
                        @elseif($agent->invitation_expires_at->isPast())
                            <span class="text-red-700 dark:text-red-300">Link expired</span>
                        @else
                            <span>Link expires {{ $agent->invitation_expires_at->format('M j') }}</span>
                        @endif
                    @else
                        @include('pages.broker._renewal-badge', ['expiresAt' => $agent->prc_id_expires_at])
                    @endif
                </div>

                <div class="flex justify-between text-sm text-stone-600 dark:text-stone-300 lg:block">
                    <span class="text-xs text-stone-500 dark:text-stone-400 lg:hidden">Listings</span>
                    {{ $agent->properties_count }}
                </div>

                <div class="flex justify-between text-sm text-stone-600 dark:text-stone-300 lg:block">
                    <span class="text-xs text-stone-500 dark:text-stone-400 lg:hidden">Clients</span>
                    {{ $agent->clients_count }}
                </div>

                <div class="flex items-center gap-2 lg:justify-end">
                    @if($invited)
                    <form action="{{ route('broker.agents.invitation', $agent) }}" method="POST" class="flex-1 lg:flex-none">
                        @csrf
                        <button type="submit" class="w-full whitespace-nowrap rounded-lg border border-teal-200 dark:border-teal-800 px-3 py-1.5 text-xs font-medium text-teal-700 dark:text-teal-300 transition hover:bg-teal-50 dark:hover:bg-teal-900">Resend Invite</button>
                    </form>
                    @else
                    <a href="{{ route('broker.agents.edit', $agent) }}" class="flex-1 rounded-lg border border-teal-200 dark:border-teal-800 px-3 py-1.5 text-center text-xs font-medium text-teal-700 dark:text-teal-300 transition hover:bg-teal-50 dark:hover:bg-teal-900 lg:flex-none">Edit</a>
                    @endif

                    <div class="relative" x-data="{ open: false }" @click.outside="open = false" @keydown.escape="open = false">
                        <button type="button" @click="open = !open" :aria-expanded="open" aria-haspopup="true" aria-label="More actions for {{ $agent->name }}"
                            class="flex h-8 w-8 items-center justify-center rounded-lg border border-stone-200 dark:border-stone-700 text-stone-500 dark:text-stone-400 transition hover:bg-stone-50 dark:hover:bg-stone-800">
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4zm0 6a2 2 0 110-4 2 2 0 010 4z"/></svg>
                        </button>
                        <div x-show="open" x-transition.opacity.duration.100ms style="display: none"
                            class="absolute right-0 top-full z-20 mt-1 w-44 overflow-hidden rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 py-1 shadow-lg">
                            @if($invited)
                            <form action="{{ route('broker.agents.destroy', $agent) }}" method="POST" onsubmit="return confirm('Cancel this invitation?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="{{ $menuItemClass }} text-red-700 dark:text-red-300">Cancel Invite</button>
                            </form>
                            @else
                            <form action="{{ route('broker.agents.toggle-status', $agent) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="{{ $menuItemClass }} text-stone-700 dark:text-stone-200">{{ $agent->is_active ? 'Suspend' : 'Activate' }}</button>
                            </form>
                            <form action="{{ route('broker.agents.destroy', $agent) }}" method="POST" onsubmit="return confirm('Delete this agent? This permanently removes their commission history and inquiries. Suspend them instead to keep their records.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="{{ $menuItemClass }} text-red-700 dark:text-red-300">Delete</button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </li>
            @empty
            <li class="px-5 py-14 text-center">
                <svg class="mx-auto mb-3 h-10 w-10 text-stone-300 dark:text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                @if($status || request('search'))
                    <p class="text-sm font-medium text-stone-700 dark:text-stone-200">No agents match this view</p>
                    <a href="{{ route('broker.agents.index') }}" class="mt-1 inline-block text-sm font-medium text-teal-700 dark:text-teal-300 hover:underline">Show all agents</a>
                @else
                    <p class="text-sm font-medium text-stone-700 dark:text-stone-200">No agents yet</p>
                    <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Invite your first agent to start building your team.</p>
                @endif
            </li>
            @endforelse
        </ul>

        @if($agents->hasPages())
            <div class="border-t border-stone-100 dark:border-stone-800 px-5 py-4">{{ $agents->links() }}</div>
        @endif
    </div>

    {{-- Invite dialog --}}
    @unless($atLimit)
    <dialog x-ref="invite" aria-labelledby="invite-heading" @click="if ($event.target === $el) $el.close()"
        class="m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl bg-white dark:bg-stone-900 p-0 text-stone-800 dark:text-stone-100 shadow-xl backdrop:bg-black/50">
        <form action="{{ route('broker.agents.store') }}" method="POST" class="space-y-4 p-6">
            @csrf
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h2 id="invite-heading" class="text-lg font-semibold text-stone-800 dark:text-white">Invite an Agent</h2>
                    <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">You will get a link to send them. They use it to set their own password and enter their PRC accreditation. It lasts 7 days.</p>
                </div>
                <button type="button" @click="$refs.invite.close()" aria-label="Close" class="shrink-0 text-stone-400 transition hover:text-stone-600 dark:hover:text-stone-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div>
                <label for="name" class="{{ $labelClass }}">Full Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="e.g. Juan dela Cruz" class="{{ $inputClass }}">
                @error('name')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="{{ $labelClass }}">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required placeholder="agent@email.com" class="{{ $inputClass }}">
                @error('email')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone" class="{{ $labelClass }}">Phone <span class="font-normal text-stone-500 dark:text-stone-400">(optional)</span></label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" placeholder="+63 912 345 6789" class="{{ $inputClass }}">
                @error('phone')<p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" @click="$refs.invite.close()" class="rounded-lg border border-stone-200 dark:border-stone-700 px-4 py-2.5 text-sm font-medium text-stone-600 dark:text-stone-300 transition hover:bg-stone-50 dark:hover:bg-stone-800">Cancel</button>
                <button type="submit" class="flex-1 rounded-lg bg-teal-700 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">Send Invitation</button>
            </div>
        </form>
    </dialog>
    @endunless

</div>

@endsection
