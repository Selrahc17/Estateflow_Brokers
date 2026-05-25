@extends('layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Alerts, reminders, and system notifications')

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    <div class="xl:col-span-2 space-y-3">
        @foreach([
            ['Payment Overdue','Ana Lim has an overdue payment for Lot 8-D. Last due: June 30, 2025.','red','2 hours ago'],
            ['Missing Documents','Maria Santos is missing 2 required documents for her reservation.','yellow','5 hours ago'],
            ['New Reservation','Pedro Reyes submitted a new reservation for Lot 3-C, Sunrise Homes.','green','Yesterday'],
            ['Payment Received','Juan dela Cruz paid ₱50,000 for July installment.','blue','Yesterday'],
            ['Document Approved','Birth Certificate of Pedro Reyes has been approved.','green','2 days ago'],
            ['Reservation Cancelled','Ben Cruz cancelled his reservation for Lot 11-A.','stone','3 days ago'],
        ] as $n)
        <div class="bg-white rounded-xl border border-stone-200 p-4 flex items-start gap-4 hover:shadow-sm transition">
            <div class="w-2.5 h-2.5 rounded-full mt-1.5 shrink-0
                {{ $n[2]==='red' ? 'bg-red-500' : '' }}
                {{ $n[2]==='yellow' ? 'bg-yellow-500' : '' }}
                {{ $n[2]==='green' ? 'bg-green-500' : '' }}
                {{ $n[2]==='blue' ? 'bg-blue-500' : '' }}
                {{ $n[2]==='stone' ? 'bg-stone-400' : '' }}">
            </div>
            <div class="flex-1">
                <p class="font-medium text-stone-800 text-sm">{{ $n[0] }}</p>
                <p class="text-xs text-stone-500 mt-0.5">{{ $n[1] }}</p>
            </div>
            <span class="text-xs text-stone-400 shrink-0">{{ $n[3] }}</span>
        </div>
        @endforeach
    </div>

    {{-- Send Notification --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <h2 class="font-semibold text-stone-800 mb-4">Send Notification</h2>
        <div class="space-y-3">
            <div>
                <label class="text-xs text-stone-500 mb-1 block">Recipient</label>
                <select class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option>All Clients</option>
                    <option>Juan dela Cruz</option>
                    <option>Maria Santos</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-stone-500 mb-1 block">Type</label>
                <select class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option>Payment Reminder</option>
                    <option>Document Request</option>
                    <option>General Message</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-stone-500 mb-1 block">Message</label>
                <textarea rows="4" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none" placeholder="Type your message..."></textarea>
            </div>
            <div class="flex gap-2">
                <button class="flex-1 bg-amber-600 hover:bg-amber-700 text-white py-2 rounded-lg text-sm font-medium transition">Send Email</button>
                <button class="flex-1 bg-stone-100 hover:bg-stone-200 text-stone-700 py-2 rounded-lg text-sm font-medium transition">Send SMS</button>
            </div>
        </div>
    </div>

</div>

@endsection
