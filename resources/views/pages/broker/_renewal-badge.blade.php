{{-- Expects $expiresAt (a date or null). --}}
@inject('compliance', 'App\Services\BrokerComplianceService')
@php
    $renewalStyles = [
        'valid' => 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300',
        'expiring' => 'bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300',
        'expired' => 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300',
        'missing' => 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300',
    ];
@endphp
<span class="inline-block rounded-full px-2 py-0.5 text-xs font-medium {{ $renewalStyles[$compliance->renewalState($expiresAt)] }}">{{ $compliance->renewalLabel($expiresAt) }}</span>
