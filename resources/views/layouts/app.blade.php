<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — EOMS</title>
    <meta name="description" content="Executive Order Management System — City Government">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f5f4ff] font-sans antialiased text-slate-900">
<div class="flex h-screen overflow-hidden">

    {{-- ══════════════════════════════════════════════════════════════ SIDEBAR ══ --}}
    <aside class="w-64 flex flex-col shrink-0 fixed inset-y-0 left-0 z-50 sidebar-scroll overflow-y-auto"
           style="background: linear-gradient(160deg, #3d1f8a 0%, #5b21b6 60%, #6d28d9 100%);">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-6 border-b border-white/10">
            <div class="w-9 h-9 bg-white/15 rounded-xl flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                </svg>
            </div>
            <div>
                <p class="text-white font-bold text-sm leading-tight">EOMS</p>
                <p class="text-violet-300 text-[11px] font-medium">City Government</p>
            </div>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-5 space-y-0.5">

            <p class="px-3 pt-1 pb-2 text-[10px] font-bold text-violet-300/60 uppercase tracking-widest">Overview</p>

            <a href="{{ route('dashboard') }}"
               class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <span>Dashboard</span>
            </a>

            <p class="px-3 pt-5 pb-2 text-[10px] font-bold text-violet-300/60 uppercase tracking-widest">Management</p>

            <a href="{{ route('executive-orders.index') }}"
               class="nav-link {{ request()->routeIs('executive-orders.index') ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                </svg>
                <span>Executive Orders</span>
            </a>

            @if(auth()->user()->isAdmin())
            <p class="px-3 pt-5 pb-2 text-[10px] font-bold text-violet-300/60 uppercase tracking-widest">Administration</p>

            <a href="{{ route('admin.users.index') }}"
               class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                <span>User Profiles</span>
            </a>

            <a href="{{ route('admin.logs.index') }}"
               class="nav-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <span>System Logs</span>
            </a>
            @endif
        </nav>

        {{-- User Profile Block --}}
        <div class="p-4 border-t border-white/10">
            <div class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-white/10 transition-colors">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center shrink-0 text-white text-xs font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-semibold truncate">{{ auth()->user()->name }}</p>
                    <p class="text-violet-300 text-[11px] truncate">{{ auth()->user()->position ?? ucfirst(auth()->user()->role) }}</p>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-2.5 px-4 py-2 rounded-xl text-violet-300/80 text-xs font-medium
                               hover:bg-white/10 hover:text-white transition-all duration-200">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    {{-- ══════════════════════════════════════════════════ MAIN CONTENT ══ --}}
    <div class="flex-1 ml-64 flex flex-col min-h-screen overflow-hidden">

        {{-- Top Bar --}}
        <header class="bg-white border-b border-slate-100 px-8 py-0 flex items-center justify-between shrink-0 h-16 sticky top-0 z-40">
            <div class="flex items-center gap-2 text-sm">
                <h1 class="font-bold text-slate-800">@yield('page-title', 'Dashboard')</h1>
                @hasSection('breadcrumb')
                <span class="text-slate-300 mx-1">/</span>
                <nav class="flex items-center gap-1.5 text-sm font-medium text-slate-400">
                    @yield('breadcrumb')
                </nav>
                @endif
            </div>
            <div class="flex items-center gap-3">
                @yield('header-actions')
            </div>
        </header>

        {{-- Flash Messages --}}
        @if(session('success') || session('error'))
        <div class="px-8 pt-5">
            @if(session('success'))
            <div class="alert-success">
                <svg class="w-4.5 h-4.5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
            @endif
            @if(session('error'))
            <div class="alert-error">
                <svg class="w-4.5 h-4.5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                </svg>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
            @endif
        </div>
        @endif

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto px-8 py-7">
            @yield('content')
        </main>
    </div>
</div>

@stack('scripts')
</body>
</html>
