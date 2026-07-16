<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Executive Orders') — Public Portal</title>
    <meta name="description" content="Official Executive Order Registry — City Government. Browse and download all publicly available executive orders.">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Public portal hero gradient animation */
        @keyframes shimmer {
            0%   { background-position: -200% center; }
            100% { background-position:  200% center; }
        }
        .hero-shimmer {
            background: linear-gradient(105deg, #3b0764 0%, #4c1d95 25%, #5b21b6 45%, #6d28d9 60%, #7c3aed 75%, #4c1d95 90%, #3b0764 100%);
            background-size: 200% auto;
            animation: shimmer 8s linear infinite;
        }
        .portal-card-hover {
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }
        .portal-card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 32px -4px rgba(109,40,217,0.13), 0 2px 8px -2px rgba(0,0,0,0.07);
            border-color: #ddd6fe;
        }
        .eo-number-pill {
            font-variant-numeric: tabular-nums;
            letter-spacing: 0.02em;
        }
    </style>
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-900 min-h-screen flex flex-col">

{{-- Top accent bar --}}
<div class="h-1 w-full" style="background: linear-gradient(90deg, #4c1d95, #7c3aed, #a78bfa, #7c3aed, #4c1d95); background-size: 200% auto;"></div>

{{-- Header --}}
<header class="bg-white border-b border-slate-100 sticky top-0 z-40 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-3.5 flex items-center justify-between gap-4">
        <a href="{{ route('public.index') }}" class="flex items-center gap-3 group">
            {{-- Logo mark --}}
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 shadow-md shadow-violet-200"
                 style="background: linear-gradient(145deg, #4c1d95 0%, #7c3aed 100%);">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-slate-800 group-hover:text-violet-700 transition-colors leading-tight">Executive Order Registry</p>
                <p class="text-[11px] text-slate-400 font-medium tracking-wide">City Government · Public Portal</p>
            </div>
        </a>
        <div class="flex items-center gap-3">
            {{-- Live status indicator --}}
            <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-semibold rounded-full border border-emerald-100">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                Public Access
            </span>
            <a href="{{ route('login') }}" class="btn-secondary btn-sm gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" /></svg>
                 Login
            </a>
        </div>
    </div>
</header>

{{-- Main --}}
<main class="flex-1 max-w-7xl w-full mx-auto px-4 lg:px-8 py-8">
    @yield('content')
</main>

{{-- Footer --}}
<footer class="bg-white border-t border-slate-100 mt-12">
    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-8">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                     style="background: linear-gradient(145deg, #4c1d95 0%, #7c3aed 100%);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-700">Executive Order Registry</p>
                    <p class="text-xs text-slate-400">City Government · Public Read-Only Portal</p>
                </div>
            </div>
            <div class="flex items-center gap-6 text-xs text-slate-400">
                <p>© {{ date('Y') }} City Government. All rights reserved.</p>
                <span class="hidden sm:inline text-slate-200">|</span>
                <a href="{{ route('login') }}" class="hidden sm:inline text-violet-600 hover:text-violet-800 font-semibold transition-colors">Login →</a>
            </div>
        </div>
    </div>
</footer>

</body>
</html>
