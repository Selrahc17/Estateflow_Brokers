{{-- Expects $user, $checklist and $outstanding. --}}
<div class="bg-white rounded-xl border border-stone-200 p-5 mb-5">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <h2 class="font-semibold text-stone-800">Licensing and Compliance</h2>
        <div class="flex flex-wrap items-center gap-2">
            @unless($user->is_approved)
            <form action="{{ route('admin.brokers.approve', $user) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" @disabled($outstanding) class="bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-white px-4 py-2 rounded-lg text-sm font-medium transition">Approve Broker</button>
            </form>
            @endunless
            <form action="{{ route('admin.brokers.reject', $user) }}" method="POST" class="flex items-center gap-2">
                @csrf
                @method('PATCH')
                <input type="text" name="reason" required maxlength="1000" placeholder="Reason" aria-label="Reason for rejecting {{ $user->name }}" class="w-40 border border-stone-200 rounded-lg px-2 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">{{ $user->is_approved ? 'Revoke Approval' : 'Reject Application' }}</button>
            </form>
        </div>
    </div>

    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm mb-5">
        <div class="flex justify-between gap-3"><dt class="text-stone-400">Phone</dt><dd class="text-stone-700">{{ $user->phone ?? 'Not provided' }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-stone-400">TIN</dt><dd class="text-stone-700">{{ $user->tin ?? 'Not provided' }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-stone-400">PRC License</dt><dd class="text-stone-700">{{ $user->license_number ?? 'Not provided' }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-stone-400">PRC ID Expiry</dt><dd class="text-stone-700">{{ $user->prc_id_expires_at?->format('M d, Y') ?? 'Not provided' }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-stone-400">DHSUD Registration</dt><dd class="text-stone-700">{{ $user->dhsud_registration_number ?? 'Not provided' }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-stone-400">DHSUD Expiry</dt><dd class="text-stone-700">{{ $user->dhsud_registration_expires_at?->format('M d, Y') ?? 'Not provided' }}</dd></div>
        <div class="flex justify-between gap-3"><dt class="text-stone-400">Privacy Consent</dt><dd class="text-stone-700">{{ $user->privacy_consented_at?->format('M d, Y') ?? 'Not recorded' }}</dd></div>
    </dl>

    <div class="divide-y divide-stone-100 border-t border-stone-100">
        @foreach($checklist as $item)
        @php($document = $item['document'])
        <div class="py-3 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-stone-700">{{ $item['label'] }}</p>
                <p class="text-xs text-stone-400">
                    {{ $document ? ucfirst(str_replace('_', ' ', $document->status)) : 'Not uploaded' }}
                    @if($document?->notes) · {{ $document->notes }} @endif
                </p>
            </div>
            @if($document)
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.documents.download', $document) }}" target="_blank" class="text-xs bg-stone-100 text-stone-600 hover:bg-stone-200 px-2 py-1 rounded-lg font-medium transition">View</a>
                @if($document->status === 'pending')
                <form action="{{ route('admin.documents.verify', $document) }}" method="POST">
                    @csrf
                    <button type="submit" class="text-xs bg-green-100 text-green-700 hover:bg-green-200 px-2 py-1 rounded-lg font-medium transition">Verify</button>
                </form>
                <form action="{{ route('admin.documents.reject', $document) }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="notes" required maxlength="500" placeholder="Reason for rejecting" aria-label="Reason for rejecting {{ $item['label'] }}" class="border border-stone-200 rounded-lg px-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-red-400">
                    <button type="submit" class="text-xs bg-red-100 text-red-600 hover:bg-red-200 px-2 py-1 rounded-lg font-medium transition">Reject</button>
                </form>
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>

    @if(!$user->is_approved && $outstanding)
    <div class="mt-4 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
        <p class="font-medium mb-1">Needed before approval:</p>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($outstanding as $reason)<li>{{ $reason }}</li>@endforeach
        </ul>
    </div>
    @endif
</div>
