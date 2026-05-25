<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EstateFlow — Page Not Found</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-stone-50 font-sans min-h-screen flex items-center justify-center px-6">
    <div class="text-center max-w-md">
        <div class="w-24 h-24 bg-amber-100 rounded-3xl flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
        </div>
        <p class="text-amber-600 font-bold text-6xl mb-2">404</p>
        <h1 class="text-2xl font-bold text-stone-800 mb-3">Page Not Found</h1>
        <p class="text-stone-400 text-sm leading-relaxed mb-8">The page you're looking for doesn't exist or has been moved. Let's get you back on track.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ url('/properties') }}" class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-xl font-semibold text-sm transition">
                Browse Properties
            </a>
            <a href="javascript:history.back()" class="border border-stone-200 text-stone-600 hover:bg-stone-100 px-6 py-3 rounded-xl font-semibold text-sm transition">
                Go Back
            </a>
        </div>
        <div class="mt-8 flex items-center justify-center gap-2">
            <div class="w-6 h-6 bg-amber-600 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </div>
            <span class="text-sm font-bold text-stone-700">Estate<span class="text-amber-600">Flow</span></span>
        </div>
    </div>
</body>
</html>
