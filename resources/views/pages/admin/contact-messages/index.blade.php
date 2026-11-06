@extends('layouts.admin')
@section('title', 'Contact Messages')
@section('page-title', 'Contact Messages')
@section('page-subtitle', 'Review messages submitted through the public contact form')

@section('content')
@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="mb-4 flex gap-2">
    @foreach(['' => 'All', 'new' => 'New', 'handled' => 'Handled'] as $value => $label)
    <a href="{{ route('admin.contact-messages', array_filter(['status' => $value])) }}" class="rounded-lg px-3 py-1.5 text-sm font-medium transition {{ request('status', '') === $value ? 'bg-red-600 text-white' : 'border border-stone-200 bg-white text-stone-600 hover:bg-stone-50' }}">{{ $label }}</a>
    @endforeach
</div>

<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-stone-200">
                <tr class="text-left text-stone-500">
                    <th class="px-5 py-3 font-medium">Submitted</th>
                    <th class="px-5 py-3 font-medium">Sender</th>
                    <th class="px-5 py-3 font-medium">Subject</th>
                    <th class="px-5 py-3 font-medium">Message</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse($messages as $message)
                <tr class="hover:bg-stone-50 transition align-top">
                    <td class="px-5 py-4 text-xs text-stone-500 whitespace-nowrap">{{ $message->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-5 py-4">
                        <p class="font-medium text-stone-700">{{ $message->first_name }} {{ $message->last_name }}</p>
                        <a href="mailto:{{ $message->email }}" class="text-xs text-teal-700 hover:underline">{{ $message->email }}</a>
                        @if($message->phone)<p class="text-xs text-stone-500">{{ $message->phone }}</p>@endif
                    </td>
                    <td class="px-5 py-4 text-xs text-stone-600">{{ $message->subject }}</td>
                    <td class="px-5 py-4 text-xs text-stone-600 max-w-md">{{ $message->message }}</td>
                    <td class="px-5 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $message->status === 'handled' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">{{ ucfirst($message->status) }}</span>
                    </td>
                    <td class="px-5 py-4">
                        @if($message->status !== 'handled')
                        <form action="{{ route('admin.contact-messages.handle', $message) }}" method="POST">
                            @csrf
                            <button type="submit" class="whitespace-nowrap rounded-lg bg-stone-100 px-2 py-1 text-xs font-medium text-stone-700 transition hover:bg-stone-200">Mark Handled</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-stone-500">No contact messages found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($messages->hasPages())
    <div class="px-5 py-4 border-t border-stone-100">{{ $messages->links() }}</div>
    @endif
</div>
@endsection
