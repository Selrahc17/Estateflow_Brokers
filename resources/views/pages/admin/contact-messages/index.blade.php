@extends('layouts.admin')
@section('title', 'Contact Messages')
@section('page-title', 'Contact Messages')
@section('page-subtitle', 'Review messages submitted through the public contact form')

@section('content')
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
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse($messages as $message)
                <tr class="hover:bg-stone-50 transition align-top">
                    <td class="px-5 py-4 text-xs text-stone-400 whitespace-nowrap">{{ $message->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-5 py-4">
                        <p class="font-medium text-stone-700">{{ $message->first_name }} {{ $message->last_name }}</p>
                        <p class="text-xs text-stone-400">{{ $message->email }}</p>
                        @if($message->phone)<p class="text-xs text-stone-400">{{ $message->phone }}</p>@endif
                    </td>
                    <td class="px-5 py-4 text-xs text-stone-600">{{ $message->subject }}</td>
                    <td class="px-5 py-4 text-xs text-stone-600 max-w-md">{{ $message->message }}</td>
                    <td class="px-5 py-4">
                        <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ ucfirst($message->status) }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-stone-400">No contact messages submitted yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-5 py-4 border-t border-stone-100">{{ $messages->links() }}</div>
</div>
@endsection
