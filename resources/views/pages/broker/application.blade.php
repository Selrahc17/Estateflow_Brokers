<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script>
        if (localStorage.getItem('darkMode') === 'true') document.documentElement.classList.add('dark');
    </script>
    <style>
        .dark { color-scheme: dark; }
        .dark body { color: #e7e5e4; }
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EstateFlow — Broker Application</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-100 dark:bg-stone-950 font-sans min-h-screen p-6">

    @php
        $inputClass = fn (string $field) => 'w-full border rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 '.($errors->has($field) ? 'border-red-300' : 'border-stone-200 dark:border-stone-700');
        $labelClass = 'text-sm text-stone-600 dark:text-stone-300 font-medium mb-1 block';
        $statusLabels = [
            'pending' => ['In review', 'bg-yellow-100 dark:bg-yellow-950 text-yellow-700 dark:text-yellow-300'],
            'verified' => ['Verified', 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300'],
            'rejected' => ['Needs a new copy', 'bg-red-100 dark:bg-red-950 text-red-600 dark:text-red-300'],
            'needs_more' => ['Needs a new copy', 'bg-red-100 dark:bg-red-950 text-red-600 dark:text-red-300'],
        ];
    @endphp

    <div class="w-full max-w-2xl mx-auto space-y-5">

        <div class="flex items-center justify-between gap-2">
            <span class="text-xl font-bold text-teal-700 dark:text-teal-300">EstateFlow</span>
            @include('pages.auth._sign-out-confirm', ['triggerClass' => 'text-sm font-medium text-stone-500 dark:text-stone-400 hover:text-teal-700 dark:hover:text-teal-300 transition', 'showIcon' => false])
        </div>

        <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-700 p-6 shadow-sm">
            <h1 class="text-2xl font-bold text-stone-800 dark:text-stone-100 mb-1">Complete your broker application</h1>
            <p class="text-stone-500 dark:text-stone-400 text-sm">
                Upload the documents below. An admin reviews each one, and your account is activated once all of them are verified.
            </p>
            <p class="text-sm font-medium text-teal-700 dark:text-teal-300 mt-3">{{ $verifiedCount }} of {{ count($checklist) }} documents verified</p>
        </div>

        @if($rejection)
        <div role="alert" class="rounded-xl border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 p-3 text-sm text-red-700 dark:text-red-300">
            <p class="font-medium">An admin asked for changes to your application</p>
            <p class="mt-0.5">{{ $rejection->message }}</p>
        </div>
        @endif

        @if(session('success'))
        <div class="bg-green-50 dark:bg-green-950 border border-green-200 dark:border-green-900 rounded-xl p-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
        @endif

        @error('file')
        <div role="alert" class="bg-red-50 dark:bg-red-950 border border-red-200 dark:border-red-900 rounded-xl p-3 text-sm text-red-600 dark:text-red-300">{{ $message }}</div>
        @enderror

        {{-- Documents --}}
        <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-700 shadow-sm divide-y divide-stone-100 dark:divide-stone-800">
            @foreach($checklist as $type => $item)
            @php($document = $item['document'])
            <div class="p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="font-semibold text-stone-800 dark:text-stone-100 text-sm">{{ $item['label'] }}</h2>
                        <p class="text-xs text-stone-500 dark:text-stone-400 mt-0.5">{{ $item['hint'] }}</p>
                    </div>
                    @if($document)
                    <span class="shrink-0 px-2 py-0.5 rounded-full text-xs font-medium {{ $statusLabels[$document->status][1] ?? 'bg-stone-100 dark:bg-stone-800 text-stone-500 dark:text-stone-400' }}">
                        {{ $statusLabels[$document->status][0] ?? ucfirst($document->status) }}
                    </span>
                    @else
                    <span class="shrink-0 px-2 py-0.5 rounded-full text-xs font-medium bg-stone-100 dark:bg-stone-800 text-stone-500 dark:text-stone-400">Not uploaded</span>
                    @endif
                </div>

                @if($document?->notes)
                <p class="text-xs text-red-600 dark:text-red-300 mt-2">Admin note: {{ $document->notes }}</p>
                @endif

                @if($document)
                <a href="{{ route('broker.application.documents.download', $document) }}" class="inline-block text-xs text-teal-700 dark:text-teal-300 hover:underline font-medium mt-2">View uploaded file</a>
                @endif

                @if($document?->status !== 'verified')
                <form action="{{ route('broker.application.documents.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-2 mt-3">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="file" name="file" accept=".jpg,.jpeg,.png,.pdf" required aria-label="{{ $item['label'] }} file"
                        class="min-w-0 flex-1 border border-stone-200 dark:border-stone-700 rounded-xl px-3 py-2 text-xs file:mr-3 file:border-0 file:bg-teal-50 file:text-teal-700 file:rounded-lg file:px-3 file:py-1 file:text-xs file:font-medium">
                    <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
                        {{ $document ? 'Replace' : 'Upload' }}
                    </button>
                </form>
                @endif
            </div>
            @endforeach
        </div>
        <p class="text-xs text-stone-500 dark:text-stone-400 px-1">JPG, PNG, or PDF up to 10 MB each. Your documents are stored privately and shown only to you and the admin reviewing your application.</p>

        {{-- Details --}}
        <form action="{{ route('broker.application.details') }}" method="POST" class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-700 p-6 shadow-sm space-y-4">
            @csrf
            @method('PUT')
            <h2 class="font-semibold text-stone-800 dark:text-stone-100">Your details</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="phone" class="{{ $labelClass }}">Phone Number</label>
                    <input id="phone" type="tel" name="phone" value="{{ old('phone', $broker->phone) }}" required class="{{ $inputClass('phone') }}">
                    @error('phone')<p class="text-xs text-red-600 dark:text-red-300 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="tin" class="{{ $labelClass }}">TIN</label>
                    <input id="tin" type="text" name="tin" placeholder="123-456-789-000" value="{{ old('tin', $broker->tin) }}" required class="{{ $inputClass('tin') }}">
                    @error('tin')<p class="text-xs text-red-600 dark:text-red-300 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="license_number" class="{{ $labelClass }}">PRC License Number</label>
                    <input id="license_number" type="text" name="license_number" value="{{ old('license_number', $broker->license_number) }}" required class="{{ $inputClass('license_number') }}">
                    @error('license_number')<p class="text-xs text-red-600 dark:text-red-300 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="prc_id_expires_at" class="{{ $labelClass }}">PRC ID Expiry</label>
                    <input id="prc_id_expires_at" type="date" name="prc_id_expires_at" value="{{ old('prc_id_expires_at', $broker->prc_id_expires_at?->format('Y-m-d')) }}" required class="{{ $inputClass('prc_id_expires_at') }}">
                    @error('prc_id_expires_at')<p class="text-xs text-red-600 dark:text-red-300 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="dhsud_registration_number" class="{{ $labelClass }}">DHSUD Registration Number</label>
                    <input id="dhsud_registration_number" type="text" name="dhsud_registration_number" value="{{ old('dhsud_registration_number', $broker->dhsud_registration_number) }}" required class="{{ $inputClass('dhsud_registration_number') }}">
                    @error('dhsud_registration_number')<p class="text-xs text-red-600 dark:text-red-300 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="dhsud_registration_expires_at" class="{{ $labelClass }}">DHSUD Registration Expiry</label>
                    <input id="dhsud_registration_expires_at" type="date" name="dhsud_registration_expires_at" value="{{ old('dhsud_registration_expires_at', $broker->dhsud_registration_expires_at?->format('Y-m-d')) }}" required class="{{ $inputClass('dhsud_registration_expires_at') }}">
                    @error('dhsud_registration_expires_at')<p class="text-xs text-red-600 dark:text-red-300 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white px-4 py-2 rounded-xl text-sm font-medium transition">Save details</button>
        </form>

    </div>

</body>
</html>
