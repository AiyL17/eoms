<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Executive Orders') — Public Portal</title>
    <meta name="description" content="Public Executive Order Registry — City Government">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f5f4ff] font-sans antialiased text-slate-900 min-h-screen flex flex-col">

{{-- Header --}}
<header class="bg-white border-b border-slate-100 sticky top-0 z-40 shadow-sm">
    <div class="max-w-6xl mx-auto px-4 lg:px-8 py-4 flex items-center justify-between gap-4">
        <a href="{{ route('public.index') }}" class="flex items-center gap-3 group">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                 style="background: linear-gradient(160deg, #3d1f8a 0%, #6d28d9 100%);">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-slate-800 group-hover:text-violet-700 transition-colors leading-tight">Executive Order Registry</p>
                <p class="text-[11px] text-slate-400 font-medium">City Government · Public Portal</p>
            </div>
        </a>
        <div class="flex items-center gap-3">
            <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-full border border-emerald-100">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                Public Access
            </span>
            <a href="{{ route('login') }}" class="btn-secondary btn-sm">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                Log In
            </a>
        </div>
    </div>
</header>

{{-- Main --}}
<main class="flex-1 max-w-6xl w-full mx-auto px-4 lg:px-8 py-7">
    @yield('content')
</main>

{{-- Footer --}}
<footer class="bg-white border-t border-slate-100 mt-10">
    <div class="max-w-6xl mx-auto px-4 lg:px-8 py-5 flex items-center justify-between gap-4 text-xs text-slate-400">
        <p>© {{ date('Y') }} City Government · Executive Order Management System</p>
        <p>Public Read-Only Portal · <a href="{{ route('login') }}" class="text-violet-600 hover:text-violet-800 font-semibold transition-colors">Log In</a></p>
    </div>
</footer>

</body>
</html>
