@extends('layouts.admin')
@section('title', 'Contact Messages')
@section('page-title', 'Contact Messages')
@section('page-subtitle', 'Messages sent through the public contact form')

@section('content')
@php
    $fieldClass = 'rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400';
    $tabAccent = [
        'new' => 'text-teal-700 dark:text-teal-300',
        'handled' => 'text-green-700 dark:text-green-300',
        'all' => 'text-stone-700 dark:text-stone-200',
    ];
    $subjectStyle = [
        'Property Inquiry' => 'bg-teal-50 text-teal-800 dark:bg-teal-950 dark:text-teal-300',
        'Reservation Question' => 'bg-sky-50 text-sky-800 dark:bg-sky-950 dark:text-sky-300',
        'Document Submission' => 'bg-violet-50 text-violet-800 dark:bg-violet-950 dark:text-violet-300',
        'General Inquiry' => 'bg-stone-100 text-stone-700 dark:bg-stone-800 dark:text-stone-300',
        'Complaint / Feedback' => 'bg-rose-50 text-rose-800 dark:bg-rose-950 dark:text-rose-300',
    ];
    $keep = array_filter(['q' => $search, 'subject' => $subject]);
@endphp

@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300" role="status">{{ session('success') }}</div>
@endif

{{-- Status tabs with counts --}}
<nav aria-label="Message status" class="mb-5 grid grid-cols-3 gap-3 sm:max-w-xl">
    @foreach($tabs as $value => $label)
    @php $active = $status === $value; @endphp
    <a href="{{ route('admin.contact-messages', $keep + ['status' => $value]) }}" @if($active) aria-current="page" @endif
       class="rounded-xl border px-4 py-3 transition {{ $active ? 'border-teal-600 bg-white ring-2 ring-teal-600/20 dark:bg-stone-900' : 'border-stone-200 bg-white hover:border-stone-300 dark:border-stone-700 dark:bg-stone-900 dark:hover:border-stone-600' }}">
        <span class="block text-xs font-medium text-stone-500 dark:text-stone-400">{{ $label }}</span>
        <span class="mt-1 block text-2xl font-bold {{ $tabAccent[$value] }}">{{ $counts[$value] ?? 0 }}</span>
    </a>
    @endforeach
</nav>

{{-- Search and filter --}}
<form method="GET" class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-[1fr_220px_auto]">
    <input type="hidden" name="status" value="{{ $status }}">
    <input type="search" name="q" value="{{ $search }}" placeholder="Search name, email, phone, or message..." aria-label="Search messages" class="{{ $fieldClass }}">
    <select name="subject" aria-label="Filter by subject" class="{{ $fieldClass }}">
        <option value="">All subjects</option>
        @foreach($subjects as $option)
        <option value="{{ $option }}" @selected($subject === $option)>{{ $option }}</option>
        @endforeach
    </select>
    <div class="flex gap-2">
        <button type="submit" class="flex-1 rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-800">Filter</button>
        @if($search !== '' || $subject)
        <a href="{{ route('admin.contact-messages', ['status' => $status]) }}" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-4 py-2 text-center text-sm font-medium text-stone-600 dark:text-stone-300 transition hover:bg-stone-50 dark:hover:bg-stone-800">Clear</a>
        @endif
    </div>
</form>

<div class="space-y-3">
    @forelse($messages as $message)
    @php
        $isNew = $message->status === 'new';
        $long = mb_strlen($message->message) > 280 || substr_count($message->message, "\n") > 3;
        $mailto = 'mailto:' . $message->email
            . '?subject=' . rawurlencode('Re: ' . $message->subject . ' - EstateFlow')
            . '&body=' . rawurlencode("Hi {$message->first_name},\n\n");
    @endphp
    <article x-data="{ open: false }" aria-labelledby="message-{{ $message->id }}"
             class="rounded-xl border bg-white dark:bg-stone-900 p-5 transition {{ $isNew ? 'border-stone-200 dark:border-stone-700 border-l-4 border-l-teal-600' : 'border-stone-200 dark:border-stone-700' }}">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex min-w-0 items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold {{ $isNew ? 'bg-teal-700 text-white' : 'bg-stone-100 text-stone-600 dark:bg-stone-800 dark:text-stone-300' }}" aria-hidden="true">{{ strtoupper(substr($message->first_name, 0, 1)) }}</span>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 id="message-{{ $message->id }}" class="{{ $isNew ? 'font-semibold' : 'font-medium' }} text-stone-800 dark:text-stone-100">{{ $message->fullName() }}</h2>
                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $subjectStyle[$message->subject] ?? $subjectStyle['General Inquiry'] }}">{{ $message->subject }}</span>
                        @if($isNew)
                        <span class="rounded-full bg-teal-700 px-2 py-0.5 text-xs font-semibold text-white">New</span>
                        @endif
                    </div>
                    <p class="mt-0.5 flex flex-wrap gap-x-3 gap-y-0.5 text-xs text-stone-500 dark:text-stone-400">
                        <a href="mailto:{{ $message->email }}" class="text-teal-700 hover:underline dark:text-teal-400">{{ $message->email }}</a>
                        @if($message->phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $message->phone) }}" class="hover:underline">{{ $message->phone }}</a>@endif
                    </p>
                </div>
            </div>
            <time datetime="{{ $message->created_at->toIso8601String() }}" title="{{ $message->created_at->format('M d, Y g:i A') }}"
                  class="shrink-0 text-xs text-stone-500 dark:text-stone-400 sm:text-right">
                {{ $message->created_at->diffForHumans() }}
                <span class="block">{{ $message->created_at->format('M d, Y g:i A') }}</span>
            </time>
        </div>

        <div class="mt-3 sm:ml-[3.25rem]">
            <p class="whitespace-pre-line break-words text-sm leading-relaxed text-stone-700 dark:text-stone-200" @if($long) :class="open ? '' : 'line-clamp-4'" @endif>{{ $message->message }}</p>
            @if($long)
            <button type="button" @click="open = !open" :aria-expanded="open" class="mt-1 text-xs font-semibold text-teal-700 hover:underline dark:text-teal-400"
                    x-text="open ? 'Show less' : 'Show full message'">Show full message</button>
            @endif

            <div class="mt-4 flex flex-wrap items-center gap-2">
                <a href="{{ $mailto }}" class="inline-flex items-center gap-1.5 rounded-lg bg-teal-700 px-3 py-1.5 text-xs font-semibold text-white transition hover:bg-teal-800">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                    Reply by email
                </a>
                @if($message->phone)
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $message->phone) }}" class="inline-flex items-center gap-1.5 rounded-lg border border-stone-200 dark:border-stone-700 px-3 py-1.5 text-xs font-semibold text-stone-700 dark:text-stone-200 transition hover:bg-stone-50 dark:hover:bg-stone-800">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    Call
                </a>
                @endif
                @if($isNew)
                <form action="{{ route('admin.contact-messages.handle', $message) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg border border-green-200 dark:border-green-900 px-3 py-1.5 text-xs font-semibold text-green-700 dark:text-green-300 transition hover:bg-green-50 dark:hover:bg-green-950">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        Mark as handled
                    </button>
                </form>
                @else
                <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 dark:text-green-400">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Handled {{ $message->updated_at->format('M d, Y') }}
                </span>
                <form action="{{ route('admin.contact-messages.reopen', $message) }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-lg px-2 py-1.5 text-xs font-semibold text-stone-500 transition hover:bg-stone-100 hover:text-stone-700 dark:text-stone-400 dark:hover:bg-stone-800 dark:hover:text-stone-200">Reopen</button>
                </form>
                @endif
            </div>
        </div>
    </article>
    @empty
    <div class="rounded-xl border border-dashed border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 px-6 py-14 text-center">
        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-teal-50 text-teal-700 dark:bg-teal-950 dark:text-teal-300" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </span>
        @if($search !== '' || $subject)
        <p class="mt-3 font-semibold text-stone-800 dark:text-stone-100">No messages match your search</p>
        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Try another name or subject.</p>
        @elseif($status === 'new')
        <p class="mt-3 font-semibold text-stone-800 dark:text-stone-100">You're all caught up</p>
        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">New messages from the contact form will appear here.</p>
        @else
        <p class="mt-3 font-semibold text-stone-800 dark:text-stone-100">No messages here</p>
        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Messages with this status will appear here.</p>
        @endif
    </div>
    @endforelse
</div>

@if($messages->hasPages())
<div class="mt-5">{{ $messages->links() }}</div>
@endif
@endsection
