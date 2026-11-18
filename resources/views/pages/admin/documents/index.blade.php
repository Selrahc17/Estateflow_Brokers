@extends('layouts.admin')
@section('title', 'Documents')
@section('page-title', 'Documents')
@section('page-subtitle', 'Review the documents brokers submit')

@section('content')
@php
    $fieldClass = 'rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400';
    $badge = [
        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
        'needs_more' => 'bg-orange-100 text-orange-800 dark:bg-orange-950 dark:text-orange-300',
        'rejected' => 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
        'verified' => 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300',
        'missing' => 'bg-stone-100 text-stone-600 dark:bg-stone-800 dark:text-stone-400',
    ];
    $dot = [
        'pending' => 'bg-amber-500', 'needs_more' => 'bg-orange-500', 'rejected' => 'bg-red-500',
        'verified' => 'bg-green-500', 'missing' => 'bg-stone-300 dark:bg-stone-600',
    ];
    $tabAccent = [
        'pending' => 'text-amber-700 dark:text-amber-300', 'needs_more' => 'text-orange-700 dark:text-orange-300',
        'rejected' => 'text-red-700 dark:text-red-300', 'verified' => 'text-green-700 dark:text-green-300',
        'all' => 'text-stone-700 dark:text-stone-200',
    ];
    $keep = array_filter(['q' => $search, 'type' => $type]);
@endphp

@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300" role="status">{{ session('success') }}</div>
@endif

@if($errors->any())
<div class="mb-5 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 px-4 py-3 text-sm text-red-700 dark:text-red-300" role="alert">
    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
</div>
@endif

{{-- Status tabs with counts --}}
<nav aria-label="Document status" class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
    @foreach($tabs as $value => $label)
    @php $active = $status === $value; @endphp
    <a href="{{ route('admin.documents', $keep + ['status' => $value]) }}" @if($active) aria-current="page" @endif
       class="{{ $value === 'all' ? 'col-span-2 sm:col-span-1' : '' }} rounded-xl border px-4 py-3 transition {{ $active ? 'border-teal-600 bg-white ring-2 ring-teal-600/20 dark:bg-stone-900' : 'border-stone-200 bg-white hover:border-stone-300 dark:border-stone-700 dark:bg-stone-900 dark:hover:border-stone-600' }}">
        <span class="block text-xs font-medium text-stone-500 dark:text-stone-400">{{ $label }}</span>
        <span class="mt-1 block text-2xl font-bold {{ $tabAccent[$value] }}">{{ $counts[$value] ?? 0 }}</span>
    </a>
    @endforeach
</nav>

{{-- Search and filter --}}
<form method="GET" class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-[1fr_220px_auto]">
    <input type="hidden" name="status" value="{{ $status }}">
    <input type="search" name="q" value="{{ $search }}" placeholder="Search broker or agent name or email..." aria-label="Search documents" class="{{ $fieldClass }}">
    <select name="type" aria-label="Filter by document type" class="{{ $fieldClass }}">
        <option value="">All document types</option>
        <optgroup label="Broker compliance">
            @foreach(\App\Models\Document::BROKER_COMPLIANCE_TYPES as $value => $details)
            <option value="{{ $value }}" @selected($type === $value)>{{ $details['label'] }}</option>
            @endforeach
        </optgroup>
        <optgroup label="Agent IDs">
            @foreach(\App\Models\Document::AGENT_ID_TYPES as $value => $label)
            <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
            @endforeach
        </optgroup>
    </select>
    <div class="flex gap-2">
        <button type="submit" class="flex-1 rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-800">Filter</button>
        @if($search !== '' || $type)
        <a href="{{ route('admin.documents', ['status' => $status]) }}" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-4 py-2 text-center text-sm font-medium text-stone-600 dark:text-stone-300 transition hover:bg-stone-50 dark:hover:bg-stone-800">Clear</a>
        @endif
    </div>
</form>

<p class="mb-4 text-xs text-stone-500 dark:text-stone-400">Brokers upload their own documents from their application page or Settings. Verify each one, or ask for a new copy with a reason the broker can act on.</p>

<div class="space-y-5">
    @forelse($owners as $owner)
    @php
        $checklist = $checklists[$owner->id] ?? null;
        $verified = $checklist ? collect($checklist)->filter(fn ($item) => $item['document']?->status === 'verified')->count() : 0;
        $total = $checklist ? count($checklist) : 0;
    @endphp
    <section class="overflow-hidden rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900" aria-labelledby="owner-{{ $owner->id }}">
        {{-- Broker header --}}
        <div class="flex flex-col gap-3 border-b border-stone-100 dark:border-stone-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-teal-700 text-sm font-semibold text-white" aria-hidden="true">{{ strtoupper(substr($owner->name, 0, 1)) }}</span>
                <div class="min-w-0">
                    <h2 id="owner-{{ $owner->id }}" class="truncate font-semibold text-stone-800 dark:text-stone-100">{{ $owner->name }}</h2>
                    <p class="truncate text-xs text-stone-500 dark:text-stone-400">
                        {{ $owner->email }}
                        @if($owner->role === 'broker')
                        · <span class="{{ $owner->is_approved ? 'text-green-700 dark:text-green-400' : 'text-amber-700 dark:text-amber-400' }}">{{ $owner->is_approved ? 'Approved broker' : 'Awaiting approval' }}</span>
                        @endif
                    </p>
                </div>
            </div>
            @if($checklist)
            <div class="flex items-center gap-4 sm:justify-end">
                <div class="w-40">
                    <p class="text-xs font-medium text-stone-600 dark:text-stone-300">{{ $verified }} of {{ $total }} verified</p>
                    <div class="mt-1 h-1.5 overflow-hidden rounded-full bg-stone-100 dark:bg-stone-800" role="progressbar" aria-valuemin="0" aria-valuemax="{{ $total }}" aria-valuenow="{{ $verified }}" aria-label="Compliance documents verified">
                        <div class="h-full rounded-full bg-green-500" style="width: {{ $total ? round($verified / $total * 100) : 0 }}%"></div>
                    </div>
                </div>
                <a href="{{ route('admin.brokers.show', $owner) }}" class="shrink-0 text-xs font-semibold text-teal-700 hover:underline dark:text-teal-400">Open application</a>
            </div>
            @endif
        </div>

        {{-- Compliance checklist --}}
        @if($checklist)
        <ul class="flex flex-wrap gap-2 border-b border-stone-100 dark:border-stone-800 bg-stone-50/60 dark:bg-stone-800/30 px-5 py-3" aria-label="Compliance checklist for {{ $owner->name }}">
            @foreach($checklist as $item)
            @php $state = $item['document']?->status ?? 'missing'; @endphp
            <li class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium {{ $badge[$state] ?? $badge['missing'] }}">
                <span class="h-1.5 w-1.5 rounded-full {{ $dot[$state] ?? $dot['missing'] }}" aria-hidden="true"></span>
                {{ $item['label'] }}<span class="sr-only">:</span>
                <span class="font-normal opacity-80">{{ $state === 'missing' ? 'Not uploaded' : (\App\Models\Document::STATUSES[$state] ?? $state) }}</span>
            </li>
            @endforeach
        </ul>
        @endif

        {{-- Documents --}}
        <ul class="divide-y divide-stone-100 dark:divide-stone-800">
            @foreach($documents[$owner->id] ?? [] as $doc)
            @php $title = $doc->agent ? $doc->typeLabel() : $doc->name; @endphp
            <li class="px-5 py-4" x-data="{ mode: null }">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-teal-50 text-teal-700 dark:bg-teal-950 dark:text-teal-300" aria-hidden="true">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="font-medium text-stone-800 dark:text-stone-100">{{ $title }}</p>
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $badge[$doc->status] ?? $badge['missing'] }}">{{ $doc->statusLabel() }}</span>
                            </div>
                            <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">
                                @if($doc->agent)For agent {{ $doc->agent->name }} · @elseif($doc->typeLabel() !== $doc->name){{ $doc->typeLabel() }} · @endif
                                Uploaded {{ $doc->created_at->format('M d, Y') }}
                                @if($doc->readableSize()) · {{ $doc->readableSize() }}@endif
                                @if($doc->status === 'verified' && $doc->verified_at) · Verified {{ $doc->verified_at->format('M d, Y') }}@endif
                            </p>
                            @if($doc->notes && $doc->status !== 'verified')
                            <p class="mt-2 rounded-lg bg-stone-50 dark:bg-stone-800 px-3 py-2 text-xs text-stone-600 dark:text-stone-300"><span class="font-semibold">Reason sent:</span> {{ $doc->notes }}</p>
                            @endif
                            @if(in_array($doc->status, ['rejected', 'needs_more'], true))
                            <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">Waiting for the broker to upload a new copy.</p>
                            @endif
                        </div>
                    </div>

                    <div class="flex shrink-0 flex-wrap items-center gap-2 lg:justify-end">
                        <a href="{{ route('admin.documents.download', $doc) }}" target="_blank" rel="noopener"
                           class="rounded-lg border border-stone-200 dark:border-stone-700 px-3 py-1.5 text-xs font-semibold text-stone-700 dark:text-stone-200 transition hover:bg-stone-50 dark:hover:bg-stone-800">View file</a>
                        @if($doc->status === 'pending')
                        <form action="{{ route('admin.documents.verify', $doc) }}" method="POST">
                            @csrf
                            <button type="submit" class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-green-700">Verify</button>
                        </form>
                        <button type="button" @click="mode = mode === 'more' ? null : 'more'" :aria-expanded="mode === 'more'"
                                class="rounded-lg border border-orange-200 dark:border-orange-900 px-3 py-1.5 text-xs font-semibold text-orange-700 dark:text-orange-300 transition hover:bg-orange-50 dark:hover:bg-orange-950">Request new copy</button>
                        <button type="button" @click="mode = mode === 'reject' ? null : 'reject'" :aria-expanded="mode === 'reject'"
                                class="rounded-lg border border-red-200 dark:border-red-900 px-3 py-1.5 text-xs font-semibold text-red-700 dark:text-red-300 transition hover:bg-red-50 dark:hover:bg-red-950">Reject</button>
                        @endif
                        <form action="{{ route('admin.documents.destroy', $doc) }}" method="POST" onsubmit="return confirm('Delete {{ addslashes($title) }}? The broker will need to upload it again.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="rounded-lg p-1.5 text-stone-400 transition hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950 dark:hover:text-red-400" aria-label="Delete {{ $title }}" title="Delete">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>

                @if($doc->status === 'pending')
                {{-- Reason panel for "Request new copy" and "Reject" --}}
                @foreach([
                    'more' => [route('admin.documents.request-more', $doc), 'What should the broker fix or add?', 'e.g. The photo is blurry. Please upload a clearer copy.', 'Send request', 'bg-orange-600 hover:bg-orange-700'],
                    'reject' => [route('admin.documents.reject', $doc), 'Why is this document rejected?', 'e.g. The PRC ID has expired.', 'Reject document', 'bg-red-600 hover:bg-red-700'],
                ] as $panel => [$action, $question, $example, $submitLabel, $submitClass])
                <form x-show="mode === '{{ $panel }}'" style="display: none" action="{{ $action }}" method="POST"
                      class="mt-3 rounded-lg border border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-800/50 p-3 lg:ml-12">
                    @csrf
                    <label for="reason-{{ $panel }}-{{ $doc->id }}" class="block text-xs font-semibold text-stone-700 dark:text-stone-200">{{ $question }}</label>
                    <textarea id="reason-{{ $panel }}-{{ $doc->id }}" name="notes" rows="2" required maxlength="1000" placeholder="{{ $example }}"
                              class="mt-1.5 w-full rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400"></textarea>
                    <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">The broker sees this reason in their notifications.</p>
                    <div class="mt-2 flex gap-2">
                        <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-white transition {{ $submitClass }}">{{ $submitLabel }}</button>
                        <button type="button" @click="mode = null" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-stone-600 dark:text-stone-300 hover:bg-stone-100 dark:hover:bg-stone-800">Cancel</button>
                    </div>
                </form>
                @endforeach
                @endif
            </li>
            @endforeach
        </ul>
    </section>
    @empty
    <div class="rounded-xl border border-dashed border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 px-6 py-14 text-center">
        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-50 text-green-600 dark:bg-green-950 dark:text-green-400" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        </span>
        @if($search !== '' || $type)
        <p class="mt-3 font-semibold text-stone-800 dark:text-stone-100">No documents match your search</p>
        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Try another name or document type.</p>
        @elseif($status === 'pending')
        <p class="mt-3 font-semibold text-stone-800 dark:text-stone-100">Nothing to review</p>
        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">New uploads from brokers will appear here.</p>
        @else
        <p class="mt-3 font-semibold text-stone-800 dark:text-stone-100">No documents here</p>
        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Documents with this status will appear here.</p>
        @endif
    </div>
    @endforelse
</div>

@if($owners->hasPages())
<div class="mt-5">{{ $owners->links() }}</div>
@endif

@endsection
