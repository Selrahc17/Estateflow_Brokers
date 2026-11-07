@extends('layouts.admin')
@section('title', 'Contact Messages')
@section('page-title', 'Contact Messages')
@section('page-subtitle', 'Review messages submitted through the public contact form')

@section('content')
@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
@endif

<div class="mb-4 flex gap-2">
    @foreach(['' => 'All', 'new' => 'New', 'handled' => 'Handled'] as $value => $label)
    <a href="{{ route('admin.contact-messages', array_filter(['status' => $value])) }}" class="rounded-lg px-3 py-1.5 text-sm font-medium transition {{ request('status', '') === $value ? 'bg-teal-700 text-white' : 'border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 dark:bg-stone-800/60 border-b border-stone-200 dark:border-stone-700">
                <tr class="text-left text-stone-500 dark:text-stone-400">
                    <th class="px-5 py-3 font-medium">Submitted</th>
                    <th class="px-5 py-3 font-medium">Sender</th>
                    <th class="px-5 py-3 font-medium">Subject</th>
                    <th class="px-5 py-3 font-medium">Message</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                @forelse($messages as $message)
                <tr class="hover:bg-stone-50 dark:hover:bg-stone-800 transition align-top">
                    <td class="px-5 py-4 text-xs text-stone-500 dark:text-stone-400 whitespace-nowrap">{{ $message->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-5 py-4">
                        <p class="font-medium text-stone-700 dark:text-stone-200">{{ $message->first_name }} {{ $message->last_name }}</p>
                        <a href="mailto:{{ $message->email }}" class="text-xs text-teal-700 dark:text-teal-300 hover:underline">{{ $message->email }}</a>
                        @if($message->phone)<p class="text-xs text-stone-500 dark:text-stone-400">{{ $message->phone }}</p>@endif
                    </td>
                    <td class="px-5 py-4 text-xs text-stone-600 dark:text-stone-300">{{ $message->subject }}</td>
                    <td class="px-5 py-4 text-xs text-stone-600 dark:text-stone-300 max-w-md">{{ $message->message }}</td>
                    <td class="px-5 py-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $message->status === 'handled' ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300' : 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300' }}">{{ ucfirst($message->status) }}</span>
                    </td>
                    <td class="px-5 py-4">
                        @if($message->status !== 'handled')
                        <form action="{{ route('admin.contact-messages.handle', $message) }}" method="POST">
                            @csrf
                            <button type="submit" class="whitespace-nowrap rounded-lg bg-stone-100 dark:bg-stone-800 px-2 py-1 text-xs font-medium text-stone-700 dark:text-stone-200 transition hover:bg-stone-200 dark:hover:bg-stone-700">Mark Handled</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-stone-500 dark:text-stone-400">No contact messages found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($messages->hasPages())
    <div class="px-5 py-4 border-t border-stone-100 dark:border-stone-800">{{ $messages->links() }}</div>
    @endif
</div>
@endsection
