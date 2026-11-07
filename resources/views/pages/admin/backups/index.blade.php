@extends('layouts.admin')
@section('title', 'Backups')
@section('page-title', 'Backups')
@section('page-subtitle', 'Save a full copy of the system data')

@section('content')
<div x-data="{
    busy: false,
    action: '',
    method: 'POST',
    heading: '',
    buttonLabel: '',
    confirm(url, method, heading, buttonLabel) {
        this.action = url;
        this.method = method;
        this.heading = heading;
        this.buttonLabel = buttonLabel;
        this.$refs.confirm.showModal();
    },
}">

    @if(session('success'))
    <div class="mb-4 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 p-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
    @endif

    @if($errors->any())
    <div role="alert" class="mb-4 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 p-3 text-sm text-red-700 dark:text-red-300">{{ $errors->first() }}</div>
    @endif

    <div class="mb-5 grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-5 lg:col-span-2">
            <h2 class="font-semibold text-stone-800 dark:text-stone-100">What a backup contains</h2>
            <ul class="mt-3 list-inside list-disc space-y-1 text-sm text-stone-600 dark:text-stone-300">
                <li>All system records: users, properties, clients, reservations, commissions, documents, messages, and audit logs, as a <span class="font-mono text-xs">database.sql</span> file and as readable JSON files</li>
                <li>Uploaded files, including client documents, broker compliance files, and payment proofs</li>
                <li>Property photos saved in Supabase storage</li>
            </ul>
            <p class="mt-3 text-xs text-stone-500 dark:text-stone-400">Temporary data such as login sessions and password reset links is left out.</p>
        </div>
        <div class="flex flex-col justify-between gap-4 rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-5">
            <div>
                <p class="text-xs text-stone-500 dark:text-stone-400">Last backup</p>
                <p class="mt-1 font-semibold text-stone-800 dark:text-stone-100">{{ $backups->isNotEmpty() ? \Illuminate\Support\Carbon::createFromTimestamp($backups->first()['created_at'])->timezone(config('app.timezone'))->format('M d, Y g:i A') : 'No backups yet' }}</p>
            </div>
            <form action="{{ route('admin.backups.store') }}" method="POST" @submit="busy = true" @pageshow.window="busy = false">
                @csrf
                <button type="submit" :disabled="busy" class="w-full rounded-lg bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800 disabled:cursor-wait disabled:opacity-70" x-text="busy ? 'Creating backup...' : 'Create Backup'">Create Backup</button>
            </form>
        </div>
    </div>

    <div class="mb-5 rounded-xl border border-amber-200 dark:border-amber-900 bg-amber-50 dark:bg-amber-950 p-4 text-sm text-amber-800 dark:text-amber-300">
        <p class="font-semibold">Keep backups private</p>
        <p class="mt-1">A backup holds personal data, IDs, TINs, and password hashes, and is not encrypted. It also sits on this same server until you download it, so save a copy somewhere secure.</p>
    </div>

    <div class="overflow-hidden rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="border-b border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/60">
                    <tr class="text-left text-stone-500 dark:text-stone-400">
                        <th class="px-5 py-3 font-medium">Backup</th>
                        <th class="px-5 py-3 font-medium">Created</th>
                        <th class="px-5 py-3 font-medium">Size</th>
                        <th class="px-5 py-3 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                    @forelse($backups as $backup)
                    <tr class="transition hover:bg-stone-50 dark:hover:bg-stone-800">
                        <td class="px-5 py-3 font-mono text-xs text-stone-700 dark:text-stone-200">{{ $backup['name'] }}</td>
                        <td class="whitespace-nowrap px-5 py-3 text-xs text-stone-500 dark:text-stone-400">{{ \Illuminate\Support\Carbon::createFromTimestamp($backup['created_at'])->timezone(config('app.timezone'))->format('M d, Y g:i A') }}</td>
                        <td class="whitespace-nowrap px-5 py-3 text-xs text-stone-500 dark:text-stone-400">{{ $backup['size'] >= 1048576 ? round($backup['size'] / 1048576, 1).' MB' : ceil($backup['size'] / 1024).' KB' }}</td>
                        <td class="whitespace-nowrap px-5 py-3 text-right">
                            <button type="button" @click="confirm(@js(route('admin.backups.download', $backup['name'])), 'POST', 'Download this backup?', 'Download')" class="rounded-lg bg-stone-100 dark:bg-stone-800 px-2 py-1 text-xs font-medium text-stone-700 dark:text-stone-200 transition hover:bg-stone-200 dark:hover:bg-stone-700">Download</button>
                            <button type="button" @click="confirm(@js(route('admin.backups.destroy', $backup['name'])), 'DELETE', 'Delete this backup?', 'Delete')" class="ml-1 rounded-lg bg-red-100 dark:bg-red-950 px-2 py-1 text-xs font-medium text-red-700 dark:text-red-300 transition hover:bg-red-200 dark:hover:bg-red-900">Delete</button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-5 py-10 text-center text-stone-500 dark:text-stone-400">No backups yet. Create your first one above.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <dialog x-ref="confirm" aria-labelledby="backup-confirm-heading" @click="if ($event.target === $el) $el.close()"
        class="m-auto w-[calc(100%-2rem)] max-w-sm rounded-2xl bg-white dark:bg-stone-900 p-6 text-stone-800 dark:text-stone-100 shadow-xl backdrop:bg-black/50">
        <h2 id="backup-confirm-heading" class="text-lg font-semibold" x-text="heading">Confirm</h2>
        <p class="mt-1 text-sm text-stone-600 dark:text-stone-300">Enter your password to continue.</p>
        <form :action="action" method="POST" class="mt-4 space-y-4" @submit="$refs.confirm.close(); setTimeout(() => $el.reset(), 500)">
            @csrf
            <input type="hidden" name="_method" :value="method">
            <div>
                <label for="backup-password" class="mb-1 block text-sm font-medium text-stone-600 dark:text-stone-300">Password</label>
                <input id="backup-password" type="password" name="password" required autocomplete="current-password" class="w-full rounded-lg border border-stone-200 dark:border-stone-700 dark:bg-stone-800 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" @click="$refs.confirm.close()" class="rounded-lg border border-stone-200 dark:border-stone-700 px-4 py-2 text-sm font-medium text-stone-600 dark:text-stone-300 transition hover:bg-stone-50 dark:hover:bg-stone-800">Cancel</button>
                <button type="submit" :class="method === 'DELETE' ? 'bg-teal-700 hover:bg-teal-800' : 'bg-teal-700 hover:bg-teal-800'" class="rounded-lg px-4 py-2 text-sm font-semibold text-white transition" x-text="buttonLabel">Continue</button>
            </div>
        </form>
    </dialog>
</div>
@endsection
