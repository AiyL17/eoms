<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — DTMS</title>
    <meta name="description" content="Document Management System — City Government">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Alpine.js is bundled via Vite (resources/js/app.js) --}}
    {{-- x-cloak hides Alpine-controlled elements until Alpine has initialised,
         preventing the sidebar flash/slide-away on mobile page load.
         Only applies below lg breakpoint — desktop sidebar is always visible. --}}
    <style>
        @media (max-width: 1023px) {
            [x-cloak] { display: none !important; }
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css"/>
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
</head>
<body class="bg-[#f5f4ff] font-sans antialiased text-slate-900">

{{-- ══════════════════════════════════════════════════ PAGE LOADING OVERLAY ══ --}}
<div id="page-loader"
     aria-live="polite" aria-label="Loading page"
     style="
         display:none;
         opacity:0;
         transition:opacity 0.2s ease;
         position:fixed;
         inset:0;
         z-index:2147483647;
         isolation:isolate;
         background:rgba(15,10,40,0.6);
         backdrop-filter:blur(6px);
         -webkit-backdrop-filter:blur(6px);
         align-items:center;
         justify-content:center;
     ">

    {{-- Centre card --}}
    <div style="
        position:relative;
        background:linear-gradient(160deg,#3d1f8a 0%,#5b21b6 60%,#6d28d9 100%);
        border-radius:1.5rem;
        padding:2rem 2.5rem;
        display:flex;
        flex-direction:column;
        align-items:center;
        gap:1.25rem;
        box-shadow:0 25px 60px rgba(109,40,217,0.45), 0 8px 24px rgba(0,0,0,0.3);
        min-width:200px;
    ">
        {{-- Logo mark --}}
        <div style="width:48px;height:48px;background:rgba(255,255,255,0.15);border-radius:0.875rem;display:flex;align-items:center;justify-content:center;">
            <svg xmlns="http://www.w3.org/2000/svg" style="width:26px;height:26px;color:white;" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
            </svg>
        </div>

        {{-- App name --}}
        <div style="text-align:center;line-height:1.2;">
            <p style="color:white;font-weight:700;font-size:0.9rem;letter-spacing:0.05em;">DTMS</p>
            <p style="color:rgba(196,181,253,0.8);font-size:0.7rem;font-weight:500;margin-top:1px;">City Government</p>
        </div>

        {{-- Animated dot trail --}}
        <div style="display:flex;align-items:center;gap:6px;" id="loader-dots">
            <span class="loader-dot" style="width:7px;height:7px;border-radius:50%;background:rgba(255,255,255,0.9);animation:loaderDot 1.2s ease-in-out infinite;animation-delay:0s;"></span>
            <span class="loader-dot" style="width:7px;height:7px;border-radius:50%;background:rgba(255,255,255,0.9);animation:loaderDot 1.2s ease-in-out infinite;animation-delay:0.2s;"></span>
            <span class="loader-dot" style="width:7px;height:7px;border-radius:50%;background:rgba(255,255,255,0.9);animation:loaderDot 1.2s ease-in-out infinite;animation-delay:0.4s;"></span>
        </div>
    </div>
</div>

<style>
@keyframes loaderDot {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
    40%            { transform: scale(1.15); opacity: 1; }
}
</style>

<script>
(function () {
    var loader = document.getElementById('page-loader');

    function show() {
        loader.style.display = 'flex';
        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                loader.style.opacity = '1';
            });
        });
    }

    function hide() {
        loader.style.opacity = '0';
        loader.style.display = 'none';
    }

    // Paths that trigger a file download (no page navigation occurs, so loader must not show)
    var downloadPaths = ['/documents/export', '/export'];

    function isDownloadLink(anchor) {
        if (anchor.hasAttribute('download')) return true;
        try {
            var url = new URL(anchor.getAttribute('href'), window.location.origin);
            for (var i = 0; i < downloadPaths.length; i++) {
                if (url.pathname === downloadPaths[i] || url.pathname.endsWith('/export')) {
                    return true;
                }
            }
            // Match any download or archived-download route segment
            if (/\/download$/.test(url.pathname) || /\/archived-download$/.test(url.pathname)) {
                return true;
            }
        } catch (_) {}
        return false;
    }

    document.addEventListener('click', function (e) {
        var anchor = e.target.closest('a[href]');
        if (!anchor) return;

        var href = anchor.getAttribute('href');
        if (
            !href ||
            href.startsWith('#') ||
            href.startsWith('mailto:') ||
            href.startsWith('tel:') ||
            anchor.target === '_blank' ||
            isDownloadLink(anchor) ||
            e.ctrlKey || e.metaKey || e.shiftKey
        ) return;

        try {
            var url = new URL(href, window.location.origin);
            if (url.origin !== window.location.origin) return;
            if (url.pathname === window.location.pathname && url.search === window.location.search) return;
        } catch (_) { return; }

        show();
    });

    document.addEventListener('submit', function (e) {
        if (e.target.target === '_blank') return;
        if (e.target.dataset.noLoader) return;
        if (e.target.dataset.confirm && !e.target._confirmed) return;
        show();
    });

    window.addEventListener('pageshow', hide);
})();
</script>

<div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

    {{-- Mobile backdrop --}}
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-slate-900/50 backdrop-blur-sm lg:hidden"
         style="display:none;"></div>

    {{-- ══════════════════════════════════════════════════════════════ SIDEBAR ══ --}}
    <aside x-cloak id="tour-sidebar" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="w-64 flex flex-col shrink-0 fixed inset-y-0 left-0 z-50 sidebar-scroll overflow-y-auto
                  transform transition-transform duration-300 ease-in-out
                  lg:translate-x-0 lg:static lg:z-auto lg:h-screen"
           style="background: linear-gradient(160deg, #3d1f8a 0%, #5b21b6 60%, #6d28d9 100%);">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-6 py-6 border-b border-white/10">
            <div class="w-9 h-9 bg-white/15 rounded-xl flex items-center justify-center shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-bold text-sm leading-tight">DTMS</p>
                <p class="text-violet-300 text-[11px] font-medium">City Government</p>
            </div>
            {{-- Close button — mobile only --}}
            <button @click="sidebarOpen = false"
                    class="lg:hidden w-7 h-7 flex items-center justify-center rounded-lg text-white/60 hover:text-white hover:bg-white/10 transition-colors shrink-0"
                    aria-label="Close menu">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 px-3 py-5 space-y-0.5">

            <p class="px-3 pt-1 pb-2 text-[10px] font-bold text-violet-300/60 uppercase tracking-widest">Overview</p>

            <a href="{{ route('dashboard') }}"
               id="tour-nav-dashboard"
               class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                </svg>
                <span>Dashboard</span>
            </a>

            <p class="px-3 pt-5 pb-2 text-[10px] font-bold text-violet-300/60 uppercase tracking-widest">Management</p>

            <a href="{{ route('documents.index') }}"
               id="tour-nav-docs"
               class="nav-link {{ request()->routeIs('documents.index') ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                </svg>
                <span>Documents</span>
            </a>

            @if(auth()->user()->isAdmin())
            <p class="px-3 pt-5 pb-2 text-[10px] font-bold text-violet-300/60 uppercase tracking-widest">Administration</p>

            <a href="{{ route('admin.users.index') }}"
               id="tour-nav-users"
               class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                <span>User Profiles</span>
            </a>

            <a href="{{ route('admin.logs.index') }}"
               id="tour-nav-logs"
               class="nav-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <span>System Logs</span>
            </a>

            <a href="{{ route('documents.archive') }}"
               id="tour-nav-archive"
               class="nav-link {{ request()->routeIs('documents.archive') ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                <span>Archive</span>
            </a>

            <a href="{{ route('admin.settings.index') }}"
               id="tour-nav-settings"
               class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Settings</span>
            </a>
            @endif
        </nav>

        {{-- User Profile Block --}}
        <div class="p-4 border-t border-white/10" id="tour-sidebar-profile">
            <a href="{{ route('profile.edit') }}"
               id="tour-sidebar-profile-link"
               class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-white/10 transition-colors group">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center shrink-0 text-white text-xs font-bold overflow-hidden">
                    @if(auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}"
                             alt="{{ auth()->user()->name }}"
                             class="w-full h-full object-cover">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-semibold truncate group-hover:text-violet-200 transition-colors">{{ auth()->user()->name }}</p>
                    <p class="text-violet-300 text-[11px] truncate">{{ auth()->user()->position ?? ucfirst(auth()->user()->role) }}</p>
                </div>
                <svg class="w-3.5 h-3.5 text-violet-400 group-hover:text-violet-200 shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="mt-2">
                @csrf
                <button type="submit"
                        id="tour-sidebar-signout"
                        class="w-full flex items-center gap-2.5 px-4 py-2 rounded-xl text-red-400 text-xs font-medium
                               hover:bg-red-500/20 hover:text-red-300 transition-all duration-200">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                    Sign Out
                </button>
            </form>
        </div>
    </aside>

    {{-- ══════════════════════════════════════════════════ MAIN CONTENT ══ --}}
    <div class="flex-1 min-w-0 flex flex-col min-h-screen overflow-hidden lg:ml-0">

        {{-- Top Bar --}}
        <header class="bg-white border-b border-slate-100 px-4 lg:px-8 py-0 flex items-center justify-between shrink-0 h-16 sticky top-0 z-40">
            <div class="flex items-center gap-3 min-w-0 flex-1 overflow-hidden">
                {{-- Hamburger — mobile only --}}
                <button @click="sidebarOpen = true"
                        class="lg:hidden w-9 h-9 flex items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors shrink-0"
                        aria-label="Open menu">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>
                <div class="flex items-center gap-2 text-sm min-w-0 overflow-hidden">
                <h1 class="font-bold text-slate-800 truncate shrink-0 max-w-[80px] sm:max-w-[160px] lg:max-w-xs">@yield('page-title', 'Dashboard')</h1>
                @hasSection('breadcrumb')
                <span class="text-slate-300 shrink-0">/</span>
                <nav class="flex items-center gap-1 text-sm font-medium text-slate-400 min-w-0 overflow-hidden whitespace-nowrap">
                    @yield('breadcrumb')
                </nav>
                @endif
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0 ml-4" id="tour-header-actions">

                {{-- Page-specific action buttons --}}
                @yield('header-actions')

                {{-- Divider --}}
                <div class="w-px h-5 bg-slate-200"></div>

                {{-- Notification Bell — always pinned at the end --}}
                @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                <div class="relative" id="tour-notifications" x-data="{ open: false }" @click.outside="open = false">

                    <button @click="open = !open"
                            class="relative w-9 h-9 flex items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        @if($unreadCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 flex items-center justify-center">
                            <span class="absolute w-4 h-4 bg-red-400 rounded-full animate-ping opacity-60"></span>
                            <span class="relative w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        </span>
                        @endif
                    </button>

                    {{-- Dropdown Panel --}}
                    <div x-show="open"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden"
                         style="display: none;">

                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                            <p class="text-sm font-bold text-slate-800">Notifications
                                @if($unreadCount > 0)
                                <span class="ml-1.5 text-[11px] font-bold bg-red-100 text-red-600 px-1.5 py-0.5 rounded-full">{{ $unreadCount }} new</span>
                                @endif
                            </p>
                            @if($unreadCount > 0)
                            <form action="{{ route('notifications.read-all') }}" method="POST" data-no-loader>
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-violet-600 hover:text-violet-800 transition-colors">
                                    Mark all read
                                </button>
                            </form>
                            @endif
                        </div>

                        <div class="overflow-y-auto" style="max-height: 360px;">
                            @php $notifications = auth()->user()->notifications()->latest()->take(15)->get(); @endphp
                            @forelse($notifications as $n)
                            @php
                                $data = $n->data;
                                $icon = match($data['type'] ?? '') {
                                                    'doc_uploaded'           => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'path' => 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5'],
                                    'doc_updated'            => ['bg' => 'bg-blue-100',    'text' => 'text-blue-600',    'path' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z'],
                                    'doc_type_changed'       => ['bg' => 'bg-sky-100',     'text' => 'text-sky-600',     'path' => 'M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5'],
                                    'doc_archived'           => ['bg' => 'bg-red-100',     'text' => 'text-red-600',     'path' => 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0'],
                                    'doc_expiring'           => ['bg' => 'bg-orange-100',  'text' => 'text-orange-600',  'path' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'],
                                    'doc_expiration_warning' => ['bg' => 'bg-red-100',     'text' => 'text-red-600',     'path' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'],
                                    default                  => ['bg' => 'bg-slate-100',   'text' => 'text-slate-500',   'path' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0'],
                                };
                            @endphp
                            <form action="{{ route('notifications.read', $n->id) }}" method="POST" data-no-loader>
                                @csrf
                                <button type="submit"
                                        class="w-full flex items-start gap-3 px-4 py-3 text-left hover:bg-slate-50 transition-colors {{ $n->read_at ? 'opacity-60' : '' }}">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5 {{ $icon['bg'] }} {{ $icon['text'] }}">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon['path'] }}" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[13px] {{ $n->read_at ? 'text-slate-500' : 'font-semibold text-slate-800' }} leading-snug">
                                            {{ $data['message'] ?? 'Notification' }}
                                        </p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $n->created_at->diffForHumans() }}</p>
                                    </div>
                                    @if(!$n->read_at)
                                    <span class="w-2 h-2 bg-violet-500 rounded-full shrink-0 mt-1.5"></span>
                                    @endif
                                </button>
                            </form>
                            @empty
                            <div class="py-10 text-center">
                                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center mx-auto mb-2 text-slate-400">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                    </svg>
                                </div>
                                <p class="text-xs font-semibold text-slate-600">No notifications</p>
                                <p class="text-xs text-slate-400 mt-0.5">You're all caught up</p>
                            </div>
                            @endforelse
                        </div>

                        {{-- Footer: link to full notifications page --}}
                        <div class="border-t border-slate-100 px-4 py-2.5 text-center">
                            <a href="{{ route('notifications.index') }}"
                               class="text-xs font-semibold text-violet-600 hover:text-violet-800 transition-colors">
                                View all notifications
                            </a>
                        </div>

                    </div>
                </div>

            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 overflow-y-auto px-4 py-5 lg:px-8 lg:py-7">
            @yield('content')
        </main>
    </div>
</div>

{{-- ══════════════════════════════════════════════════ TOAST NOTIFICATIONS ══ --}}
<div id="toast-container"
     class="fixed top-4 right-4 left-4 sm:left-auto sm:top-6 sm:right-6 z-[9999] flex flex-col gap-3 pointer-events-none"
     aria-live="polite" aria-label="Notifications">

    @foreach([
        'success' => [
            'bg'         => 'bg-emerald-50',
            'border'     => 'border-emerald-100',
            'icon_bg'    => 'bg-emerald-100',
            'icon_text'  => 'text-emerald-600',
            'bar'        => 'bg-emerald-500',
            'title'      => 'Success',
            'title_color'=> 'text-emerald-800',
            'msg_color'  => 'text-emerald-700',
            'close_color'=> 'text-emerald-400 hover:text-emerald-600',
            'icon_path'  => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
        'error' => [
            'bg'         => 'bg-red-50',
            'border'     => 'border-red-100',
            'icon_bg'    => 'bg-red-100',
            'icon_text'  => 'text-red-600',
            'bar'        => 'bg-red-500',
            'title'      => 'Error',
            'title_color'=> 'text-red-800',
            'msg_color'  => 'text-red-700',
            'close_color'=> 'text-red-400 hover:text-red-600',
            'icon_path'  => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z',
        ],
        'warning' => [
            'bg'         => 'bg-amber-50',
            'border'     => 'border-amber-100',
            'icon_bg'    => 'bg-amber-100',
            'icon_text'  => 'text-amber-600',
            'bar'        => 'bg-amber-500',
            'title'      => 'Warning',
            'title_color'=> 'text-amber-800',
            'msg_color'  => 'text-amber-700',
            'close_color'=> 'text-amber-400 hover:text-amber-600',
            'icon_path'  => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
        ],
        'info' => [
            'bg'         => 'bg-violet-50',
            'border'     => 'border-violet-100',
            'icon_bg'    => 'bg-violet-100',
            'icon_text'  => 'text-violet-600',
            'bar'        => 'bg-violet-500',
            'title'      => 'Info',
            'title_color'=> 'text-violet-800',
            'msg_color'  => 'text-violet-700',
            'close_color'=> 'text-violet-400 hover:text-violet-600',
            'icon_path'  => 'M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z',
        ],
    ] as $type => $cfg)
        @if(session($type))
        <div class="toast pointer-events-auto w-full sm:w-96 rounded-2xl border shadow-xl overflow-hidden
                    {{ $cfg['bg'] }} {{ $cfg['border'] }}"
             role="alert"
             data-toast>

            <div class="flex items-center gap-3 px-4 py-3.5">
                {{-- Icon --}}
                <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 {{ $cfg['icon_bg'] }} {{ $cfg['icon_text'] }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $cfg['icon_path'] }}" />
                    </svg>
                </div>

                {{-- Text --}}
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-bold uppercase tracking-widest {{ $cfg['title_color'] }}">{{ $cfg['title'] }}</p>
                    <p class="text-sm {{ $cfg['msg_color'] }} mt-0.5 leading-snug">{{ session($type) }}</p>
                </div>

                {{-- Close button --}}
                <button type="button"
                        class="shrink-0 transition-colors {{ $cfg['close_color'] }}"
                        aria-label="Dismiss"
                        onclick="dismissToast(this.closest('[data-toast]'))">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Progress bar at the bottom --}}
            <div class="toast-bar h-0.5 {{ $cfg['bar'] }} origin-left" style="animation: toast-shrink 5s linear forwards;"></div>
        </div>
        @endif
    @endforeach
</div>

<style>
@keyframes toast-shrink {
    from { transform: scaleX(1); }
    to   { transform: scaleX(0); }
}
.toast {
    animation: toast-slide-in 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
}
.toast.toast-hide {
    animation: toast-slide-out 0.25s ease-in forwards;
}
@keyframes toast-slide-in {
    from { opacity: 0; transform: translateY(-12px) scale(0.95); }
    to   { opacity: 1; transform: translateY(0)     scale(1); }
}
@keyframes toast-slide-out {
    from { opacity: 1; transform: translateY(0)     scale(1);    max-height: 120px; margin-bottom: 0; }
    to   { opacity: 0; transform: translateY(-8px)  scale(0.95); max-height: 0;     margin-bottom: -12px; }
}
</style>

<script>
function dismissToast(el) {
    if (!el) return;
    el.classList.add('toast-hide');
    el.addEventListener('animationend', () => el.remove(), { once: true });
}

// Auto-dismiss each toast after its progress bar finishes (5 s)
document.querySelectorAll('[data-toast]').forEach(function (toast) {
    setTimeout(function () { dismissToast(toast); }, 5000);
});
</script>

{{-- ══════════════════════════════════════════════ CONFIRM DIALOG MODAL ══ --}}
<div id="confirm-modal"
     class="fixed inset-0 z-[10000] flex items-center justify-center p-4 hidden"
     role="dialog" aria-modal="true" aria-labelledby="confirm-modal-title">

    {{-- Backdrop --}}
    <div id="confirm-backdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

    {{-- Panel — matches the signature modal structure exactly --}}
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm flex flex-col overflow-hidden"
         id="confirm-panel">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div>
                    <h3 id="confirm-modal-title" class="text-sm font-bold text-slate-800">Confirm Deletion</h3>
                    <p id="confirm-modal-subtitle" class="text-xs text-slate-400 mt-0.5">This action cannot be undone.</p>
                </div>
            </div>
            <button type="button" id="confirm-close-btn"
                    class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5">
            <p id="confirm-modal-message" class="text-sm text-slate-600 leading-relaxed"></p>
        </div>

        {{-- Footer --}}
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/60">
            <button type="button" id="confirm-cancel-btn" class="btn-secondary">
                Cancel
            </button>
            <button type="button" id="confirm-ok-btn" class="btn-danger">
                <svg id="confirm-ok-icon" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                </svg>
                <span id="confirm-ok-label">Delete</span>
            </button>
        </div>
    </div>
</div>

<style>
@keyframes modal-pop {
    from { opacity: 0; transform: scale(0.95) translateY(8px); }
    to   { opacity: 1; transform: scale(1)    translateY(0); }
}
</style>

<script>
(function () {
    const modal      = document.getElementById('confirm-modal');
    const backdrop   = document.getElementById('confirm-backdrop');
    const msgEl      = document.getElementById('confirm-modal-message');
    const titleEl    = document.getElementById('confirm-modal-title');
    const subtitleEl = document.getElementById('confirm-modal-subtitle');
    const cancelBtn  = document.getElementById('confirm-cancel-btn');
    const closeBtn   = document.getElementById('confirm-close-btn');
    const okBtn      = document.getElementById('confirm-ok-btn');
    const okIcon     = document.getElementById('confirm-ok-icon');
    const okLabel    = document.getElementById('confirm-ok-label');
    const panel      = document.getElementById('confirm-panel');

    // Variant color schemes for the OK button
    const variantStyles = {
        danger: {
            okClass: 'btn-danger',
        },
        archive: {
            okClass: 'btn-primary',
        },
        outgoing: {
            okClass: 'btn-success',
        },
        incoming: {
            okClass: 'btn-info',
        },
    };

    // Icon paths for known action types
    const icons = {
        archive: 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z',
        delete:  'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0',
        update:  'M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5',
    };

    let pendingForm = null;

    function openConfirm(message, form, opts) {
        opts = opts || {};
        pendingForm = form;
        msgEl.textContent = message;
        titleEl.textContent    = opts.title    || 'Confirm Deletion';
        subtitleEl.textContent = opts.subtitle || 'This action cannot be undone.';
        okLabel.textContent    = opts.action   || 'Delete';

        // Swap icon if a known action type is provided
        const iconPath = icons[opts.action ? opts.action.toLowerCase() : 'delete'] || icons.delete;
        okIcon.querySelector('path').setAttribute('d', iconPath);

        // Apply variant color scheme (outgoing=green, incoming=blue, archive=violet, default=danger/red)
        const allOkClasses = ['btn-danger', 'btn-success', 'btn-info', 'btn-primary'];
        const style = variantStyles[opts.variant] || variantStyles.danger;

        // OK button variant
        allOkClasses.forEach(c => okBtn.classList.remove(c));
        okBtn.classList.add(style.okClass);

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        // Re-trigger animation on each open
        panel.style.animation = 'none';
        panel.offsetHeight; // reflow
        panel.style.animation = 'modal-pop 0.2s cubic-bezier(0.34, 1.56, 0.64, 1) both';
    }

    function closeConfirm() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        pendingForm = null;
    }

    cancelBtn.addEventListener('click', closeConfirm);
    closeBtn.addEventListener('click', closeConfirm);
    backdrop.addEventListener('click', closeConfirm);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeConfirm();
    });

    okBtn.addEventListener('click', () => {
        if (pendingForm) {
            const form = pendingForm;
            closeConfirm();
            form._confirmed = true;
            form.submit();
        }
    });

    // Intercept any element with data-confirm="..."
    document.addEventListener('click', (e) => {
        const trigger = e.target.closest('[data-confirm]');
        if (!trigger) return;

        e.preventDefault();
        e.stopPropagation();

        const message = trigger.dataset.confirm;
        const formId  = trigger.dataset.confirmForm;
        const form    = formId
            ? document.getElementById(formId)
            : (trigger.tagName === 'FORM' ? trigger : trigger.closest('form'));

        const opts = {
            title:    trigger.dataset.confirmTitle    || null,
            subtitle: trigger.dataset.confirmSubtitle || null,
            action:   trigger.dataset.confirmAction   || null,
            variant:  trigger.dataset.confirmVariant  || null,
        };

        if (form) openConfirm(message, form, opts);
    });

    // Intercept form submits that carry data-confirm on the form element
    document.addEventListener('submit', (e) => {
        const form = e.target;
        if (!form.dataset.confirm) return;
        if (form._confirmed) { form._confirmed = false; return; }
        e.preventDefault();
        const opts = {
            title:    form.dataset.confirmTitle    || null,
            subtitle: form.dataset.confirmSubtitle || null,
            action:   form.dataset.confirmAction   || null,
            variant:  form.dataset.confirmVariant  || null,
        };
        openConfirm(form.dataset.confirm, form, opts);
    });
})();
</script>

{{-- ══════════════════════════════════════════════════ HELP TOUR ══ --}}

{{-- Floating Help Button --}}
<button id="tour-help-btn"
        onclick="startPageTour()"
        class="fixed right-4 sm:right-6 z-[9990] w-12 h-12 rounded-full shadow-lg
               bg-violet-600 hover:bg-violet-700 text-white
               flex items-center justify-center
               transition-all duration-200 hover:scale-110 hover:shadow-violet-600/40 hover:shadow-xl
               focus:outline-none focus:ring-4 focus:ring-violet-500/30"
        style="bottom: calc(1.5rem + env(safe-area-inset-bottom, 0px));"
        aria-label="Start page tour"
        title="Help &amp; Tour">
    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
    </svg>
</button>

{{-- Driver.js custom theme overrides --}}
<style>
/* Popover shell */
.driver-popover {
    background: #ffffff !important;
    border-radius: 16px !important;
    box-shadow: 0 25px 60px -12px rgba(109,40,217,.18), 0 8px 24px -4px rgba(0,0,0,.12) !important;
    padding: 0 !important;
    max-width: 340px !important;
    border: 1px solid #ede9fe !important;
    overflow: hidden !important;
}

/* On mobile, use near-full width */
@media (max-width: 639px) {
    .driver-popover {
        max-width: calc(100vw - 24px) !important;
        width: calc(100vw - 24px) !important;
    }
    /* Always position popover at bottom of screen on mobile for sidebar steps */
    .driver-popover-bottom,
    .driver-popover-top,
    .driver-popover-left,
    .driver-popover-right {
        position: fixed !important;
        bottom: 80px !important;
        left: 12px !important;
        right: 12px !important;
        top: auto !important;
        transform: none !important;
    }
    /* Hide the directional arrow on mobile — it points nowhere useful */
    .driver-popover-arrow {
        display: none !important;
    }
}

/* Header bar */
.driver-popover-title {
    font-family: 'Inter', sans-serif !important;
    font-size: 13px !important;
    font-weight: 700 !important;
    color: #ffffff !important;
    background: linear-gradient(135deg, #5b21b6 0%, #6d28d9 100%) !important;
    margin: 0 !important;
    padding: 14px 16px 12px !important;
    border-radius: 0 !important;
    display: flex !important;
    align-items: center !important;
    gap: 8px !important;
}
.driver-popover-title::before {
    content: '';
    display: inline-block;
    width: 7px; height: 7px;
    border-radius: 50%;
    background: rgba(255,255,255,0.6);
    flex-shrink: 0;
}

/* Description */
.driver-popover-description {
    font-family: 'Inter', sans-serif !important;
    font-size: 13px !important;
    color: #475569 !important;
    line-height: 1.6 !important;
    padding: 14px 16px !important;
    margin: 0 !important;
    border-bottom: 1px solid #f1f5f9 !important;
}
.driver-popover-description strong { color: #4c1d95; }
.driver-popover-description .tour-tip {
    display: block;
    background: #f8f7ff;
    border: 1px solid #ede9fe;
    border-radius: 10px;
    padding: 8px 12px 8px 30px;
    margin-top: 8px;
    font-size: 12px;
    color: #4c1d95;
    position: relative;
}
.driver-popover-description .tour-tip::before {
    content: '→';
    font-weight: 700;
    color: #7c3aed;
    position: absolute;
    left: 12px;
    top: 8px;
}

/* Footer / navigation */
.driver-popover-footer {
    padding: 10px 16px !important;
    background: #faf9ff !important;
    border-radius: 0 !important;
    gap: 8px !important;
}
.driver-popover-progress-text {
    font-family: 'Inter', sans-serif !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    color: #94a3b8 !important;
}

/* Buttons */
.driver-popover-prev-btn,
.driver-popover-next-btn,
.driver-popover-close-btn {
    font-family: 'Inter', sans-serif !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    border-radius: 10px !important;
    padding: 6px 14px !important;
    border: none !important;
    cursor: pointer !important;
    transition: all .15s !important;
    line-height: 1.4 !important;
}
.driver-popover-prev-btn {
    background: #f1f5f9 !important;
    color: #64748b !important;
    border: 1px solid #e2e8f0 !important;
}
.driver-popover-prev-btn:hover {
    background: #e2e8f0 !important;
    color: #334155 !important;
}
.driver-popover-next-btn {
    background: linear-gradient(135deg, #6d28d9, #5b21b6) !important;
    color: #ffffff !important;
    box-shadow: 0 2px 8px rgba(109,40,217,.3) !important;
    text-shadow: none !important;
}
.driver-popover-next-btn:hover {
    background: linear-gradient(135deg, #5b21b6, #4c1d95) !important;
    box-shadow: 0 4px 12px rgba(109,40,217,.4) !important;
}
.driver-popover-close-btn {
    background: transparent !important;
    color: rgba(255,255,255,0.7) !important;
    padding: 4px 8px !important;
    position: absolute !important;
    top: 8px !important;
    right: 8px !important;
    font-size: 16px !important;
    line-height: 1 !important;
    border-radius: 6px !important;
}
.driver-popover-close-btn:hover {
    background: rgba(255,255,255,0.15) !important;
    color: #ffffff !important;
}

/* Arrow */
.driver-popover-arrow { filter: drop-shadow(0 1px 2px rgba(109,40,217,.15)); }

/* Highlight ring */
.driver-highlighted-element {
    border-radius: 10px !important;
}

/* Tour title icon */
.tour-title-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.1em;
    height: 1.1em;
    vertical-align: middle;
    margin-right: 6px;
    position: relative;
    top: -1px;
}
.tour-title-icon svg {
    width: 1.1em;
    height: 1.1em;
}
</style>

<script>
(function () {
    /* ── Page-aware tour steps ────────────────────────────────────── */
    const isAdmin   = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};
    const routeName = '{{ Route::currentRouteName() }}';
    const hasArchivedVersions = {{ isset($archivedFiles) && count($archivedFiles) > 0 ? 'true' : 'false' }};

    /* Shared helper: wrap a tip line */
    function tip(text) {
        return `<div class="tour-tip">${text}</div>`;
    }

    /* Shared helper: inline SVG icon for tour titles */
    function ico(path) {
        return '<span class="tour-title-icon"><svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="' + path + '"/></svg></span> ';
    }

    /* ── Tours keyed by Laravel route name ───────────────────────── */
    const tours = {

        /* ── Dashboard (admin) ─────────────────────────────────────── */
        'dashboard': isAdmin ? [
            {
                element: '#tour-sidebar',
                popover: {
                    title: ico('M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776') + 'Navigation Sidebar',
                    description: 'All sections of the system are accessible here — Dashboard, Documents, Users, Logs, Archive, and Settings.'
                        + tip('Click any link to navigate. On mobile, tap the menu icon to open it.'),
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-nav-dashboard',
                popover: {
                    title: ico('M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25') + 'Dashboard',
                    description: 'Your command center. See KPI stats, recent uploads, activity feed, and system-wide charts at a glance.',
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-nav-docs',
                popover: {
                    title: ico('M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z') + 'Documents',
                    description: 'Browse, search, filter and manage all documents in the system.',
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-nav-users',
                popover: {
                    title: ico('M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z') + 'User Profiles',
                    description: 'Create and manage user accounts. Assign roles (Admin / Staff) to control access levels.',
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-nav-logs',
                popover: {
                    title: ico('M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z') + 'System Logs',
                    description: 'A full audit trail of every action — uploads, edits, status changes, downloads, and deletions.',
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-nav-archive',
                popover: {
                    title: ico('M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z') + 'Archive',
                    description: 'Soft-deleted documents live here. You can restore them or permanently remove them.',
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-nav-settings',
                popover: {
                    title: ico('M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28zM15 12a3 3 0 11-6 0 3 3 0 016 0z') + 'Settings',
                    description: 'Configure system-wide preferences — maintenance mode, staff upload permissions, and more.',
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-notifications',
                popover: {
                    title: ico('M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0') + 'Notifications',
                    description: 'Real-time alerts for document events. A red badge appears when you have unread notifications.'
                        + tip('Click the bell to expand the dropdown. Click any notification to mark it read.'),
                    side: 'bottom', align: 'end',
                }
            },
            {
                element: '#tour-header-manage-users',
                popover: {
                    title: ico('M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z') + 'Manage Users',
                    description: 'Takes you to the <strong>User Profiles</strong> page where you can create, edit, or remove user accounts and assign roles.',
                    side: 'bottom', align: 'end',
                }
            },
            {
                element: '#tour-header-upload',
                popover: {
                    title: ico('M12 4.5v15m7.5-7.5h-15') + 'Upload Document',
                    description: 'Takes you directly to the <strong>Upload Document</strong> form to register a new document into the system.',
                    side: 'bottom', align: 'end',
                }
            },
            {
                element: '[data-tour="welcome-banner"]',
                popover: {
                    title: ico('M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25') + 'Welcome Banner',
                    description: 'Shows your name, role, and a live clock. A quick snapshot of who\'s logged in.',
                    side: 'bottom', align: 'start',
                }
            },
            {
                element: '[data-tour="kpi-stats"]',
                popover: {
                    title: ico('M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z') + 'KPI Statistics',
                    description: 'At-a-glance numbers: total documents, total users, incoming documents, and outgoing documents.'
                        + tip('Each card is clickable and takes you directly to the related section.'),
                    side: 'bottom', align: 'start',
                }
            },
            {
                element: '[data-tour="type-distribution"]',
                popover: {
                    title: ico('M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z') + 'Document Type Breakdown',
                    description: 'Visual breakdown of all documents by type — Incoming vs. Outgoing — shown as progress bars with counts and percentages.',
                    side: 'right', align: 'start',
                }
            },
            {
                element: '[data-tour="by-year"]',
                popover: {
                    title: ico('M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5') + 'By Year',
                    description: 'Document volume per issuance year. Scroll the list to see older years.',
                    side: 'left', align: 'start',
                }
            },
            {
                element: '[data-tour="recent-eos"]',
                popover: {
                    title: ico('M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z') + 'Recently Registered',
                    description: 'The five most recently registered documents. Click any row to open the full document detail page.',
                    side: 'top', align: 'start',
                }
            },
            {
                element: '[data-tour="top-users"]',
                popover: {
                    title: ico('M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z') + 'Most Active Users',
                    description: 'Top users ranked by actions taken in the last 30 days.',
                    side: 'left', align: 'start',
                }
            },
            {
                element: '[data-tour="activity-feed"]',
                popover: {
                    title: ico('M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z') + 'System Activity',
                    description: 'A live feed of every action taken across all users — who did what and when.',
                    side: 'top', align: 'start',
                }
            },
            {
                element: '[data-tour="upload-sparkline"]',
                popover: {
                    title: ico('M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z') + 'Registrations — Last 7 Days',
                    description: 'Daily document registration count for the past 7 days, plus quick links to common admin tasks like adding users, viewing audit logs, and exporting records.',
                    side: 'left', align: 'start',
                }
            },
            {
                element: '#tour-sidebar-profile-link',
                popover: {
                    title: ico('M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z') + 'Your Profile',
                    description: 'Click here to edit your name, position, avatar, or password.',
                    side: 'top', align: 'start',
                }
            },
            {
                element: '#tour-sidebar-signout',
                popover: {
                    title: ico('M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75') + 'Sign Out',
                    description: 'Ends your session and returns you to the login page.',
                    side: 'top', align: 'start',
                }
            },
            {
                element: '#tour-help-btn',
                popover: {
                    title: ico('M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z') + 'This Help Button',
                    description: 'Click anytime to restart this page tour. Each page has its own tour tailored to that section.',
                    side: 'top', align: 'end',
                }
            },
        /* ── end admin dashboard steps ── */
        ] : [
            /* ── Dashboard (staff) ─────────────────────────────────── */
            {
                element: '#tour-sidebar',
                popover: {
                    title: ico('M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776') + 'Navigation Sidebar',
                    description: 'All sections you have access to are here — Dashboard and Documents.',
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-nav-dashboard',
                popover: {
                    title: ico('M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25') + 'Dashboard',
                    description: 'Your personal dashboard showing your upload stats, recent activity, and document type overview.',
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-nav-docs',
                popover: {
                    title: ico('M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z') + 'Documents',
                    description: 'Browse and search all documents in the system. Click any row to view the full document.',
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-notifications',
                popover: {
                    title: ico('M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0') + 'Notifications',
                    description: 'Alerts for document uploads, updates, and status changes. The red badge shows unread count.',
                    side: 'bottom', align: 'end',
                }
            },
            {
                element: '#tour-header-btn',
                popover: {
                    title: ico('M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z') + 'Quick Actions',
                    description: 'The <strong>Upload Document</strong> button here takes you directly to the upload form.',
                    side: 'bottom', align: 'end',
                }
            },
            {
                element: '[data-tour="welcome-banner"]',
                popover: {
                    title: ico('M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25') + 'Welcome Banner',
                    description: 'Shows your name, role, and a live clock.',
                    side: 'bottom', align: 'start',
                }
            },
            {
                element: '[data-tour="kpi-stats"]',
                popover: {
                    title: ico('M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z') + 'Your Stats',
                    description: 'Your personal upload count, download activity, total documents in the system, and documents issued this year.',
                    side: 'bottom', align: 'start',
                }
            },
            {
                element: '[data-tour="recent-eos"]',
                popover: {
                    title: ico('M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3') + 'My Recent Uploads',
                    description: 'Documents you recently submitted. Click any row to view or edit them.',
                    side: 'top', align: 'start',
                }
            },
            {
                element: '[data-tour="type-distribution"]',
                popover: {
                    title: ico('M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z') + 'Document Types',
                    description: 'System-wide breakdown of all documents by type — Incoming vs. Outgoing.',
                    side: 'left', align: 'start',
                }
            },
            {
                element: '[data-tour="activity-feed"]',
                popover: {
                    title: ico('M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z') + 'My Recent Activity',
                    description: 'A log of your own actions in the system — uploads, edits, downloads, and more.',
                    side: 'top', align: 'start',
                }
            },
            {
                element: '[data-tour="upload-sparkline"]',
                popover: {
                    title: ico('M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z') + 'My Uploads — Last 7 Days',
                    description: 'Your daily registration count for the past 7 days, plus quick links to register a new document or browse all documents.',
                    side: 'left', align: 'start',
                }
            },
            {
                element: '#tour-sidebar-profile-link',
                popover: {
                    title: ico('M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z') + 'Your Profile',
                    description: 'Click here to update your name, position, and avatar.',
                    side: 'top', align: 'start',
                }
            },
            {
                element: '#tour-sidebar-signout',
                popover: {
                    title: ico('M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75') + 'Sign Out',
                    description: 'Ends your session and returns you to the login page.',
                    side: 'top', align: 'start',
                }
            },
            {
                element: '#tour-help-btn',
                popover: {
                    title: ico('M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z') + 'This Help Button',
                    description: 'Click anytime to restart this page tour. Each page has its own tour tailored to that section.',
                    side: 'top', align: 'end',
                }
            },
        ],

        /* ── Documents list ─────────────────────────────────── */
        'documents.index': [
            {
                element: '#tour-doc-filters',
                popover: {
                    title: ico('M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z') + 'Search & Filters',
                    description: 'Search by document name, office, or recipient. Narrow results by <strong>Document Type</strong> (Incoming or Outgoing).'
                        + tip('Active filters appear as chips below the bar. Click the × to clear them.'),
                    side: 'bottom', align: 'start',
                }
            },
            {
                element: '#tour-doc-table',
                popover: {
                    title: ico('M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z') + 'Documents Table',
                    description: 'All documents are listed here. Click any row to open the full detail page.'
                        + tip('The <strong>Reference No.</strong> and <strong>Date Received</strong> column headers are clickable to sort ascending or descending.'),
                    side: 'top', align: 'start',
                }
            },
            {
                element: '#tour-doc-toggle-type',
                popover: {
                    title: ico('M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5') + 'Toggle Document Type',
                    description: 'Click the <strong>swap icon</strong> to switch a document between <strong>Incoming</strong> and <strong>Outgoing</strong>. A confirmation dialog will appear before the change is saved.'
                        + tip('This action is logged in the document\'s activity history and notifies relevant users.'),
                    side: 'top', align: 'center',
                }
            },
            {
                element: '#tour-doc-view-btn',
                popover: {
                    title: ico('M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z') + 'View Document',
                    description: 'Click the <strong>eye icon</strong> to open the full document detail page — view the PDF, all metadata, and the complete activity log.',
                    side: 'top', align: 'center',
                }
            },
            {
                element: '#tour-header-btn',
                popover: {
                    title: ico('M12 4.5v15m7.5-7.5h-15') + 'Upload Document',
                    description: 'Click <strong>Upload Document</strong> to add a new document — fill in metadata and attach the PDF.',
                    side: 'bottom', align: 'end',
                }
            },
            {
                element: '#tour-export-csv',
                popover: {
                    title: ico('M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3') + 'Export ZIP',
                    description: 'Downloads all currently filtered documents as a ZIP archive — one folder per year, each containing an Excel file and the associated PDFs.'
                        + tip('Apply filters first, then click Export ZIP to download only the matching records.'),
                    side: 'bottom', align: 'end',
                }
            },
            {
                element: '#tour-notifications',
                popover: {
                    title: ico('M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0') + 'Notifications',
                    description: 'You\'ll be notified here whenever a document is uploaded, updated, or its status changes.',
                    side: 'bottom', align: 'end',
                }
            },
        ],

        /* ── Document detail / show ──────────────────────────────────────── */
        'documents.show': [
            {
                element: '#tour-doc-pdf',
                popover: {
                    title: ico('M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z') + 'PDF Viewer',
                    description: 'The official document is displayed here. Scroll within the viewer to read all pages.',
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-doc-open',
                popover: {
                    title: ico('M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25') + 'Open',
                    description: 'Opens the PDF in a new browser tab — useful for reading without downloading.',
                    side: 'bottom', align: 'end',
                }
            },
            {
                element: '#tour-doc-download',
                popover: {
                    title: ico('M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3') + 'Download',
                    description: 'Downloads the original PDF file to your device. Every download is logged in the activity history.',
                    side: 'bottom', align: 'end',
                }
            },
            {
                element: '#tour-doc-meta',
                popover: {
                    title: ico('M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z') + 'Document Metadata',
                    description: 'Everything about this document in one panel — the unique <strong>Reference Number</strong>, document type (Incoming or Outgoing), date received, office/origin, recipient, deadline, and who registered or last edited it.',
                    side: 'left', align: 'start',
                }
            },
            {
                element: '#tour-doc-activity-log',
                onHighlightStarted: (el) => {
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                },
                popover: {
                    title: ico('M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z') + 'Activity Log',
                    description: 'A complete audit trail of every action taken on this document — uploads, edits, status changes, downloads, and more.'
                        + '<br><br>Each entry shows the action type, who performed it, any notes attached, and the exact date and time.'
                        + tip('This section only appears when the document has recorded activity. It is automatically updated — no manual logging needed.'),
                    side: 'left', align: 'start',
                }
            },
            {
                element: '#tour-header-btn',
                popover: {
                    title: ico('M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5') + 'Action Buttons',
                    description: '<strong>Export</strong> downloads the full document record as a ZIP archive containing the Excel detail sheet and the original PDF.'
                        + '<br><br><strong>History</strong> opens the version history — every edit and status change with who made it.'
                        + '<br><br><strong>Edit</strong> opens the edit form to update details or change the document type.'
                        + '<br><br><strong>Archive</strong> soft-deletes the document (restorable from the Archive page).',
                    side: 'bottom', align: 'end',
                }
            },
        ],

        /* ── Version History ───────────────────────────────────────── */
        'documents.version-history': [
            {
                element: '#tour-vh-pdf-archive',
                onHighlightStarted: (el) => {
                    if (el) {
                        const main = el.closest('main') || document.querySelector('main');
                        if (main) main.scrollTop = 0;
                    }
                },
                popover: {
                    title: ico('M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z') + 'PDF Version Archive',
                    description: 'Every PDF ever attached to this document is preserved here. When an edit replaces the file, the old one is moved to this archive — nothing is lost.'
                        + tip('Each archived version shows its filename, upload date, and file size.'),
                    side: 'right', align: 'start',
                },
            },
            {
                element: '#tour-vh-current',
                onHighlightStarted: (el) => {
                    if (el) {
                        const main = el.closest('main') || document.querySelector('main');
                        if (main) main.scrollTop = 0;
                    }
                },
                popover: {
                    title: ico('M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z') + 'Current Version',
                    description: 'The highlighted row is the <strong>active document</strong> — the file currently shown in the PDF viewer on the document detail page.',
                    side: 'bottom', align: 'start',
                },
            },
            {
                element: '#tour-vh-open',
                popover: {
                    title: ico('M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25') + 'Open',
                    description: 'Opens the current PDF in a new browser tab — useful for reading or printing without downloading the file.',
                    side: 'bottom', align: 'end',
                },
            },
            {
                element: '#tour-vh-download',
                popover: {
                    title: ico('M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3') + 'Download',
                    description: 'Downloads the current version of the PDF directly to your device. Every download is recorded in the document\'s activity log.',
                    side: 'bottom', align: 'end',
                },
            },
            ...(hasArchivedVersions ? [{
                element: '#tour-vh-archived-versions',
                popover: {
                    title: ico('M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z') + 'Earlier Versions',
                    description: 'These are the previous PDF files that were replaced during edits. Each row shows the original filename, the date it was archived, and its file size.'
                        + tip('Use <strong>Open</strong> to preview an older version in the browser, or <strong>Download</strong> to save it — without affecting the current active file.'),
                    side: 'right', align: 'start',
                },
            }] : []),
            {
                element: '#tour-vh-meta-history',
                onHighlightStarted: (el) => {
                    if (el) {
                        const main = el.closest('main') || document.querySelector('main');
                        if (main) main.scrollTop = 0;
                    }
                },
                popover: {
                    title: ico('M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z') + 'Metadata Change History',
                    description: 'A chronological log of every field-level edit made to this document. Each entry shows <strong>who</strong> made the change, <strong>when</strong>, and a side-by-side <strong>before / after</strong> diff for every modified field.'
                        + tip('Status changes, title edits, date corrections, and signatory updates are all captured here automatically.'),
                    side: 'left', align: 'start',
                },
            },
        ],

        /* ── Upload / create ───────────────────────────────────────── */
        'documents.create': [
            {
                element: '#tour-doc-form-file',
                popover: {
                    title: ico('M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z') + 'Document File',
                    description: 'Click or drag-and-drop to attach the official document. Only <strong>PDF files</strong> up to 20 MB are accepted. This field is required.',
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-doc-form-details',
                popover: {
                    title: ico('M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z') + 'Document Information',
                    description: 'Fill in the core details — <strong>Reference Number</strong> (your office\'s official numbering, must be unique), <strong>Document Type</strong> (Incoming or Outgoing), <strong>Document Name</strong>, <strong>Office / Origin</strong>, <strong>Date Received</strong>, <strong>Recipient</strong>, and the optional <strong>Deadline</strong>.'
                        + tip('The preview panel on the right updates in real time as you type.'),
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-doc-form-preview',
                onHighlightStarted: () => {
                    const main = document.querySelector('main');
                    if (main) main.scrollTop = 0;
                },
                popover: {
                    title: ico('M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z') + 'Live Preview',
                    description: 'This panel updates in real time as you fill in the form — reference number, document type, name, office, dates, recipient, and attached file all reflect instantly before you submit.',
                    side: 'left', align: 'start',
                }
            },
        ],

        /* ── Document edit ───────────────────────────────────────────────── */
        'documents.edit': [
            {
                element: '#tour-doc-form-basic',
                popover: {
                    title: ico('M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z') + 'Document Information',
                    description: 'Update the core details of this document — <strong>Document Type</strong> (Incoming or Outgoing), <strong>Document Name</strong>, <strong>Office / Origin</strong>, <strong>Date Received</strong>, <strong>Recipient</strong>, and the optional <strong>Deadline</strong>.'
                        + tip('The <strong>Reference Number</strong> is locked and cannot be changed after the document is created.'),
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-doc-form-file',
                popover: {
                    title: ico('M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z') + 'Replace File',
                    description: 'Upload a new PDF to replace the current file. Leave this empty to keep the existing one. Any replacement is logged in the audit trail.'
                        + tip('Only PDF files are accepted. Maximum size is 20 MB.'),
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-doc-form-reason',
                popover: {
                    title: ico('M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z') + 'Reason for Edit',
                    description: 'Briefly describe what changed and why. This note is optional but is saved to the <strong>audit log</strong> for this record — useful for accountability and future reference.',
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-doc-preview-card',
                onHighlightStarted: () => {
                    const main = document.querySelector('main');
                    if (main) main.scrollTop = 0;
                },
                popover: {
                    title: ico('M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z') + 'Live Preview',
                    description: 'The preview panel updates in real time as you type — reference number, document type, name, office, dates, recipient, and the attached file all reflect your changes before you save.'
                        + tip('The <strong>Reference Number</strong> is fixed and shown here as a reminder — it cannot be changed after creation.'),
                    side: 'left', align: 'start',
                }
            },
        ],

        /* ── Admin: User Profiles ──────────────────────────────────── */
        'admin.users.index': [
            {
                element: '[data-tour="user-stats"]',
                popover: {
                    title: ico('M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z') + 'User Summary',
                    description: 'Total users, number of Administrators, and number of Staff members at a glance.',
                    side: 'bottom', align: 'start',
                }
            },
            {
                element: '[data-tour="user-filters"]',
                popover: {
                    title: ico('M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z') + 'Search & Filter',
                    description: 'Search users by name or email. Filter by role (Administrator / Staff).',
                    side: 'bottom', align: 'start',
                }
            },
            {
                element: '[data-tour="user-table"]',
                popover: {
                    title: ico('M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z') + 'Users Table',
                    description: 'Lists all accounts with their name, email, role, position, join date, and online status. Column headers are sortable by clicking them.',
                    side: 'top', align: 'start',
                }
            },
            {
                element: '#tour-user-edit-btn',
                popover: {
                    title: ico('M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125') + 'Edit User',
                    description: 'Click the <strong>pencil icon</strong> to open the edit form for that user — update their name, email, role, position, or password.',
                    side: 'left', align: 'center',
                }
            },
            {
                element: '#tour-user-delete-btn',
                popover: {
                    title: ico('M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0') + 'Delete User',
                    description: 'Click the <strong>trash icon</strong> to permanently delete a user account. A confirmation dialog will appear before anything is removed.'
                        + tip('You cannot delete your own account — the trash icon is hidden on your own row for safety.'),
                    side: 'left', align: 'center',
                }
            },
            {
                element: '#tour-header-btn',
                popover: {
                    title: ico('M12 4.5v15m7.5-7.5h-15') + 'Create User',
                    description: 'Click <strong>Create User</strong> to add a new account. Assign their role and set a temporary password.',
                    side: 'bottom', align: 'end',
                }
            },
        ],

        /* ── Admin: Create User ───────────────────────────────────── */
        'admin.users.create': [
            {
                element: '#tour-user-create-profile',
                popover: {
                    title: ico('M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z') + 'Profile',
                    description: 'Enter the user\'s <strong>Full Name</strong>, <strong>Email Address</strong>, <strong>System Role</strong> (Admin or Staff), and optional <strong>Position / Title</strong>.',
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#role-info-btn',
                popover: {
                    title: ico('M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z') + 'Role Permissions',
                    description: 'Click or hover this <strong>ⓘ info icon</strong> beside the System Role label to see a full breakdown of what each role can do.'
                        + tip('Admin — full document management, user management, and audit log access. Staff — view, create, and edit documents only.'),
                    side: 'bottom', align: 'start',
                }
            },
            {
                element: '#tour-user-create-password',
                popover: {
                    title: ico('M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z') + 'Set Password',
                    description: 'Set a temporary password for the new account. The user can change it later from their profile.'
                        + tip('Passwords must be at least 8 characters and contain uppercase, lowercase, numbers, and symbols.'),
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-user-create-preview',
                onHighlightStarted: () => {
                    const main = document.querySelector('main');
                    if (main) main.scrollTop = 0;
                },
                popover: {
                    title: ico('M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z') + 'Live Preview',
                    description: 'The <strong>Preview</strong> card updates in real time as you fill in the form — the avatar initials, name, email, role badge, and position all reflect instantly before you submit.',
                    side: 'left', align: 'start',
                }
            },
        ],

        /* ── Admin: Edit User ─────────────────────────────────────── */
        'admin.users.edit': [
            {
                element: '#tour-user-profile',
                popover: {
                    title: ico('M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z') + 'Profile',
                    description: 'Update the user\'s <strong>Full Name</strong>, <strong>Email Address</strong>, <strong>System Role</strong>, and optional <strong>Position / Title</strong>.'
                        + tip('You cannot change your own role to prevent accidental lockout.'),
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-user-password',
                popover: {
                    title: ico('M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z') + 'Change Password',
                    description: 'Set a new password for this user. Leave both fields blank to keep the existing password unchanged.'
                        + tip('Passwords must be at least 8 characters. Use the eye icon to toggle visibility while typing.'),
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-user-preview-card',
                onHighlightStarted: () => {
                    const main = document.querySelector('main');
                    if (main) main.scrollTop = 0;
                },
                popover: {
                    title: ico('M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z') + 'Live Preview',
                    description: 'This panel reflects the user\'s name, email, role badge, and position in real time as you make changes — before anything is saved.',
                    side: 'left', align: 'start',
                }
            },
        ],

        /* ── Admin: System Logs ────────────────────────────────────── */
        'admin.logs.index': [
            {
                element: '[data-tour="logs-filters"]',
                popover: {
                    title: ico('M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z') + 'Search & Filters',
                    description: 'Narrow down the audit trail with three filters:'
                        + tip('<strong>Search</strong> — find entries by document number or title keyword.')
                        + tip('<strong>All Types</strong> — filter by document type: Incoming or Outgoing.')
                        + tip('<strong>All Users</strong> — show activity from a specific staff member or admin only.')
                        + '<br>Active filters appear as chips above the table. Click the <strong>✕</strong> button to clear all at once.',
                    side: 'bottom', align: 'start',
                }
            },
            {
                element: '[data-tour="logs-table"]',
                popover: {
                    title: ico('M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z') + 'Activity Log Table',
                    description: 'A full audit trail of every action taken in the system — who did it, when, on which document, and from what IP address. The <strong>Timestamp</strong> and <strong>Action</strong> columns are sortable. Click any document title in the <strong>Target Record</strong> column to jump straight to it.'
                        + tip('Records are paginated. Apply filters above to narrow results by keyword, document type, or user.'),
                    side: 'top', align: 'start',
                }
            },
        ],

        /* ── Admin: Settings ───────────────────────────────────────── */
        'admin.settings.index': [
            {
                element: '[data-tour="settings-form"]',
                popover: {
                    title: ico('M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28zM15 12a3 3 0 11-6 0 3 3 0 016 0z') + 'System Settings',
                    description: 'All system-wide configuration lives here. Click <strong>Save Settings</strong> at the bottom to apply any changes immediately.',
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-setting-retention',
                popover: {
                    title: ico('M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z') + 'Archive Retention Period',
                    description: 'Sets how many days an archived document is kept before the nightly scheduler permanently deletes it. The default is <strong>30 days</strong>.'
                        + tip('Increase this number if you need a longer safety window before permanent deletion.'),
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-setting-staff-upload',
                popover: {
                    title: ico('M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z') + 'Allow Staff to Upload',
                    description: 'When this toggle is <strong>on</strong>, staff members can upload new documents. When <strong>off</strong>, only administrators can upload — staff can still view and download existing documents.',
                    side: 'right', align: 'start',
                }
            },
            {
                element: '#tour-setting-maintenance',
                popover: {
                    title: ico('M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z') + 'Maintenance Mode',
                    description: 'When enabled, only administrators can log in. All active staff sessions are terminated and they see a maintenance notice on the login page.'
                        + tip('Use this before performing major updates or data migrations. Remember to turn it off when done.'),
                    side: 'right', align: 'start',
                }
            },
            {
                element: '[data-tour="health-panel"]',
                popover: {
                    title: ico('M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z') + 'System Health',
                    description: 'Live sanity checks for critical system components. Each row shows a check name, its current status, and a colour-coded dot — green for healthy, amber for warnings, red for issues.'
                        + tip('Expand <strong>How to fix</strong> on any failing or warning check for resolution steps.'),
                    side: 'left', align: 'start',
                }
            },
            {
                element: '#health-recheck-btn',
                popover: {
                    title: ico('M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99') + 'Re-check Now',
                    description: 'Click this button to instantly re-run all system health checks without reloading the page. The badge and all check rows update in real time.',
                    side: 'left', align: 'start',
                }
            },
        ],

        /* ── Archive ───────────────────────────────────────────────── */
        'documents.archive': [
            {
                element: '[data-tour="archive-filters"]',
                popover: {
                    title: ico('M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z') + 'Search Archive',
                    description: 'Search archived documents by <strong>document number</strong> or <strong>title</strong>. The active search term appears as a chip above the results. Use the <strong>✕ clear</strong> button to reset.',
                    side: 'bottom', align: 'start',
                }
            },
            {
                element: '[data-tour="archive-table"]',
                popover: {
                    title: ico('M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z') + 'Archive Table',
                    description: 'Lists all soft-deleted documents with their <strong>reference number</strong>, <strong>title</strong>, <strong>archived date</strong>, and <strong>who uploaded</strong> them. All column headers are sortable. The record count is shown above the table.',
                    side: 'top', align: 'start',
                }
            },
            {
                element: '#tour-archive-restore',
                popover: {
                    title: ico('M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99') + 'Restore',
                    description: 'Moves the document back to the active document list, fully intact with all its metadata and PDF history preserved.',
                    side: 'left', align: 'center',
                }
            },
            {
                element: '#tour-archive-delete',
                popover: {
                    title: ico('M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0') + 'Delete Forever',
                    description: 'Permanently removes the document, its PDF, and all previous file versions from the system. A confirmation dialog will appear first.'
                        + tip('This action is irreversible. Use Restore if you are unsure.'),
                    side: 'left', align: 'center',
                }
            },
        ],

        /* ── Profile edit ──────────────────────────────────────────── */
        'profile.edit': [
            {
                element: '[data-tour="profile-avatar"]',
                popover: {
                    title: ico('M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z') + 'Profile Picture',
                    description: 'Click the avatar to open the photo picker. Upload a JPG, PNG, GIF, or WebP image (max 2 MB) that appears in the sidebar and across the system.'
                        + tip('Use the camera icon badge as a hint that the avatar is clickable. You can also remove an existing photo from the same modal.'),
                    side: 'bottom', align: 'start',
                },
                onHighlightStarted: () => {
                    const main = document.querySelector('main');
                    if (main) main.scrollTop = 0;
                },
            },
            {
                element: '[data-tour="profile-account"]',
                popover: {
                    title: ico('M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z') + 'Account Details',
                    description: 'A read-only summary of your account — your <strong>system role</strong>, the date you joined (<strong>Member Since</strong>), and your <strong>total document uploads</strong>. These are managed by your administrator.',
                    side: 'right', align: 'start',
                },
                onHighlightStarted: () => {
                    const main = document.querySelector('main');
                    if (main) main.scrollTop = 0;
                },
            },
            {
                element: '[data-tour="profile-info"]',
                popover: {
                    title: ico('M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z') + 'Personal Information',
                    description: 'Update your <strong>Full Name</strong>, <strong>Email Address</strong>, and optional <strong>Position / Title</strong>. Changes reflect immediately across the system — in the header, sidebar, and document logs.',
                    side: 'left', align: 'start',
                },
                onHighlightStarted: () => {
                    const btn = document.getElementById('tab-btn-info');
                    if (btn) btn.click();
                    const main = document.querySelector('main');
                    if (main) main.scrollTop = 0;
                },
            },
            {
                element: '[data-tour="profile-info"]',
                popover: {
                    title: ico('M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z') + 'Password & Security',
                    description: 'Change your password by entering your <strong>current password</strong>, then a new one. The new password must be at least 8 characters and include letters and numbers.'
                        + tip('If you forget your current password, ask your administrator to reset it for you from the User Profiles page.'),
                    side: 'left', align: 'start',
                },
                onHighlightStarted: () => {
                    const btn = document.getElementById('tab-btn-password');
                    if (btn) btn.click();
                    const main = document.querySelector('main');
                    if (main) main.scrollTop = 0;
                },
            },
        ],

    };

    /* ── Launch tour ──────────────────────────────────────────────── */
    window.startPageTour = function () {
        const steps = tours[routeName];
        const isMobile = window.innerWidth < 1024; // lg breakpoint — sidebar is overlay on mobile

        if (!steps || steps.length === 0) {
            /* No tour for this page — show a friendly toast */
            const msg = document.createElement('div');
            msg.className = 'fixed bottom-28 right-4 sm:bottom-24 sm:right-6 z-[9999] bg-white border border-violet-100 shadow-xl rounded-2xl px-5 py-4 flex items-center gap-3 text-sm font-medium text-slate-700 pointer-events-none';
            msg.style.animation = 'toast-slide-in .3s cubic-bezier(.34,1.56,.64,1) both';
            msg.innerHTML = `
                <div class="w-8 h-8 rounded-xl bg-violet-100 text-violet-600 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
                    </svg>
                </div>
                No tour available for this page yet.`;
            document.body.appendChild(msg);
            setTimeout(() => msg.remove(), 3000);
            return;
        }

        const sidebarElementIds = [
            '#tour-sidebar', '#tour-nav-dashboard', '#tour-nav-docs',
            '#tour-nav-users', '#tour-nav-logs', '#tour-nav-archive',
            '#tour-nav-settings', '#tour-sidebar-profile',
            '#tour-sidebar-profile-link', '#tour-sidebar-signout',
        ];

        /* Helper: is a step targeting a sidebar element? */
        function isSidebarStep(step) {
            return step.element && sidebarElementIds.includes(step.element);
        }

        /* Alpine.js sidebar state helpers */
        function openSidebar() {
            const alpineRoot = document.querySelector('[x-data]');
            if (alpineRoot && alpineRoot._x_dataStack) {
                alpineRoot._x_dataStack[0].sidebarOpen = true;
            }
        }
        function closeSidebar() {
            const alpineRoot = document.querySelector('[x-data]');
            if (alpineRoot && alpineRoot._x_dataStack) {
                alpineRoot._x_dataStack[0].sidebarOpen = false;
            }
        }

        /* Filter out steps whose element doesn't exist on this page */
        const validSteps = steps.filter(s => !s.element || document.querySelector(s.element));

        if (validSteps.length === 0) return;

        /* On mobile: wrap every step to handle sidebar open/close + adapt popover side */
        const processedSteps = validSteps.map(step => {
            const processed = Object.assign({}, step);

            if (isMobile) {
                /* Force all popovers to appear below the element on mobile
                   (sidebar items sit at top-left, so 'bottom' keeps popover visible) */
                if (processed.popover) {
                    processed.popover = Object.assign({}, processed.popover, { side: 'bottom', align: 'start' });
                }

                const originalOnHighlight = step.onHighlightStarted;

                if (isSidebarStep(step)) {
                    /* For sidebar steps: open the sidebar before highlighting */
                    processed.onHighlightStarted = (element, step) => {
                        openSidebar();
                        /* Wait for the sidebar transition (300ms) then let driver proceed */
                        if (originalOnHighlight) originalOnHighlight(element, step);
                    };
                } else {
                    /* For non-sidebar steps: close the sidebar so it doesn't cover page content */
                    processed.onHighlightStarted = (element, step) => {
                        closeSidebar();
                        if (originalOnHighlight) originalOnHighlight(element, step);
                    };
                }
            }

            return processed;
        });

        /* On mobile: close sidebar after tour ends or is dismissed */
        const onTourEnd = isMobile ? () => {
            /* Small delay so the user sees the final step before sidebar snaps shut */
            setTimeout(closeSidebar, 300);
        } : undefined;

        const driverObj = window.driver.js.driver({
            showProgress: true,
            progressText: 'Step @{{current}} of @{{total}}',
            nextBtnText: 'Next →',
            prevBtnText: '← Back',
            doneBtnText: 'Done ✓',
            allowClose: true,
            overlayOpacity: 0.55,
            stagePadding: isMobile ? 4 : 6,
            stageRadius: 10,
            popoverClass: 'eoms-tour-popover',
            smoothScroll: false,
            steps: processedSteps,
            onDestroyed: onTourEnd,
        });

        /* On mobile: open sidebar first if the very first step targets it */
        if (isMobile && processedSteps.length > 0 && isSidebarStep(validSteps[0])) {
            openSidebar();
            /* Give the CSS transition time to finish before driver.js measures positions */
            setTimeout(() => driverObj.drive(), 320);
        } else {
            driverObj.drive();
        }
    };
})();
</script>

<div id="help-modal"
     class="fixed inset-0 z-[10001] flex items-center justify-center p-4 hidden"
     role="dialog" aria-modal="true" aria-labelledby="help-modal-title">

    {{-- Backdrop --}}
    <div id="help-backdrop"
         class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
         onclick="closeHelpGuide()"></div>

    {{-- Panel --}}
    <div id="help-panel"
         class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg flex flex-col overflow-hidden"
         style="max-height: 90vh;">

        {{-- Header --}}
        <div class="shrink-0 px-6 py-5 border-b border-slate-100 flex items-center justify-between"
             style="background: linear-gradient(135deg, #5b21b6 0%, #6d28d9 100%);">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
                <div>
                    <h2 id="help-modal-title" class="text-white font-bold text-sm">System Guide</h2>
                    <p class="text-violet-200 text-xs mt-0.5">Learn how to navigate DTMS</p>
                </div>
            </div>
            <button onclick="closeHelpGuide()"
                    class="w-8 h-8 flex items-center justify-center rounded-xl text-white/60
                           hover:text-white hover:bg-white/15 transition-colors"
                    aria-label="Close guide">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Progress bar --}}
        <div class="shrink-0 h-1 bg-slate-100">
            <div id="help-progress"
                 class="h-1 bg-violet-500 transition-all duration-300 ease-out"
                 style="width: 0%"></div>
        </div>

        {{-- Step content --}}
        <div class="flex-1 overflow-y-auto px-6 py-5">
            <div id="help-steps"></div>
        </div>

        {{-- Footer --}}
        <div class="shrink-0 flex items-center justify-between gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/60">
            <div class="flex items-center gap-1.5">
                <span id="help-step-label" class="text-xs font-semibold text-slate-400"></span>
            </div>
            <div class="flex items-center gap-2">
                <button id="help-prev-btn"
                        onclick="helpPrev()"
                        class="btn-secondary btn-sm hidden">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                    Back
                </button>
                <button id="help-next-btn"
                        onclick="helpNext()"
                        class="btn-primary btn-sm">
                    Next
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </div>
        </div>

    </div>
</div>

<style>
@keyframes help-panel-pop {
    from { opacity: 0; transform: scale(0.95) translateY(12px); }
    to   { opacity: 1; transform: scale(1)    translateY(0); }
}
#help-panel { animation: none; }
#help-panel.is-open { animation: help-panel-pop 0.25s cubic-bezier(0.34, 1.56, 0.64, 1) both; }

/* Step transition */
.help-step-enter {
    animation: help-step-in 0.2s ease-out both;
}
@keyframes help-step-in {
    from { opacity: 0; transform: translateX(16px); }
    to   { opacity: 1; transform: translateX(0); }
}
.help-step-back {
    animation: help-step-back 0.2s ease-out both;
}
@keyframes help-step-back {
    from { opacity: 0; transform: translateX(-16px); }
    to   { opacity: 1; transform: translateX(0); }
}
</style>

<script>
(function () {

    /* ── Step definitions ─────────────────────────────────────────── */
    const isAdmin = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};

    const allSteps = [
        {
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />',
            iconBg: 'bg-violet-100', iconColor: 'text-violet-600',
            title: 'Welcome to DTMS',
            description: 'The <strong>Document Management System</strong> helps you store, track, and manage all documents issued by the City Government. Use this guide to learn the key features.',
            tips: [
                'Navigate between sections using the <strong>left sidebar</strong>.',
                'The <strong>top bar</strong> shows the current page title and quick actions.',
                'The <strong>notification bell</strong> alerts you to important events.',
            ],
        },
        {
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />',
            iconBg: 'bg-indigo-100', iconColor: 'text-indigo-600',
            title: 'Browsing Documents',
            description: 'The <strong>Documents</strong> page lists all documents in the system. You can search, filter, and sort to quickly find what you need.',
            tips: [
                'Use the <strong>search bar</strong> to find documents by number, title, subject, or signatory.',
                'Filter by <strong>Status</strong>, <strong>Year</strong>, or <strong>Tag</strong> using the dropdowns.',
                'Click any row or the <strong>View</strong> button to open a document\'s detail page.',
                'Column headers are <strong>clickable</strong> to sort ascending or descending.',
            ],
        },
        {
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />',
            iconBg: 'bg-emerald-100', iconColor: 'text-emerald-600',
            title: 'Viewing a Document',
            description: 'The document detail page shows the full <strong>PDF document</strong> alongside all metadata — title, signatory, dates, tags, amendment chain, and activity log.',
            tips: [
                'Use the <strong>Download</strong> button to save a copy of the PDF.',
                'The <strong>status badge</strong> (Active, Amended, Repealed, etc.) shows the document\'s current state.',
                'An <strong>Amendment Chain</strong> section links related documents if one amends another.',
                'The <strong>Activity Log</strong> at the bottom tracks every change made to the document.',
            ],
        },
        {
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />',
            iconBg: 'bg-sky-100', iconColor: 'text-sky-600',
            title: 'Uploading a New Document',
            description: 'Click the <strong>"Upload Document"</strong> button on the Documents page or dashboard to add a new document to the system.',
            tips: [
                'Fill in the <strong>Document Name</strong>, <strong>Office / Origin</strong>, date received, and recipient.',
                'Attach the official <strong>PDF file</strong> — only PDF format is accepted.',
                'Optionally add <strong>Tags</strong> and a <strong>Content Summary</strong> for easier searching.',
                'If this document amends an existing one, use the <strong>"Amends"</strong> field to link them.',
            ],
            adminOnly: false,
        },
        {
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />',
            iconBg: 'bg-amber-100', iconColor: 'text-amber-600',
            title: 'Editing & Changing Status',
            description: 'Open any document and click <strong>Edit</strong> to update its details or change its status.',
            tips: [
                'Change the <strong>Status</strong> field to reflect the document\'s current standing (Active, Amended, Repealed, Suspended, Under Review).',
                'Add <strong>Status Notes</strong> to document the reason for a status change.',
                'Uploading a <strong>new PDF</strong> replaces the existing file and is logged in the activity history.',
                'All edits are recorded in the <strong>Activity Log</strong> with the editor\'s name and timestamp.',
            ],
        },
        {
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />',
            iconBg: 'bg-rose-100', iconColor: 'text-rose-600',
            title: 'Notifications',
            description: 'The <strong>bell icon</strong> in the top-right shows real-time alerts whenever a document is uploaded, updated, status-changed, or deleted.',
            tips: [
                'Unread notifications are shown with a <strong>red badge</strong> count.',
                'Click a notification to <strong>mark it as read</strong> and jump to the related document.',
                'Use <strong>"Mark all read"</strong> to clear all unread alerts at once.',
                'Visit the <strong>Notifications</strong> page for the full history.',
            ],
        },
        {
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />',
            iconBg: 'bg-teal-100', iconColor: 'text-teal-600',
            title: 'Your Profile',
            description: 'Click your <strong>name in the bottom of the sidebar</strong> to open your Profile page.',
            tips: [
                'Update your <strong>name</strong>, <strong>position</strong>, and <strong>avatar</strong> photo.',
                'Draw or upload your <strong>e-signature</strong> which will appear on documents you sign.',
                'Change your <strong>password</strong> from the profile page at any time.',
            ],
        },
        {
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />',
            iconBg: 'bg-violet-100', iconColor: 'text-violet-600',
            title: 'Managing Users',
            description: 'As an administrator, go to <strong>User Profiles</strong> in the sidebar to manage all system accounts.',
            tips: [
                'Create new accounts by clicking <strong>"Add New User"</strong>.',
                'Assign the <strong>Admin</strong> or <strong>Staff</strong> role to control access levels.',
                'Edit or deactivate user accounts as needed.',
                'View each user\'s activity and uploaded documents from their profile.',
            ],
            adminOnly: true,
        },
        {
            icon: '<path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z" />',
            iconBg: 'bg-slate-100', iconColor: 'text-slate-500',
            title: 'Archive & System Logs',
            description: 'Deleted documents are soft-deleted and moved to the <strong>Archive</strong>. The <strong>System Logs</strong> page gives a complete audit trail of every action.',
            tips: [
                'Go to <strong>Archive</strong> to view, restore, or permanently delete soft-deleted documents.',
                '<strong>System Logs</strong> shows every upload, edit, status change, download, and deletion.',
                'Filter logs by user, action type, or date range for targeted auditing.',
            ],
            adminOnly: true,
        },
    ];

    /* Filter steps based on role */
    const steps = allSteps.filter(s => isAdmin || !s.adminOnly);
    let current = 0;
    let direction = 'forward';

    /* ── Render helpers ───────────────────────────────────────────── */
    function renderStep(idx, dir) {
        const step = steps[idx];
        const container = document.getElementById('help-steps');
        const animClass = dir === 'back' ? 'help-step-back' : 'help-step-enter';

        container.innerHTML = `
            <div class="${animClass}">
                <div class="flex items-center gap-4 mb-5">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 ${step.iconBg} ${step.iconColor}">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            ${step.icon}
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 leading-snug">${step.title}</h3>
                </div>
                <p class="text-sm text-slate-600 leading-relaxed mb-5">${step.description}</p>
                <div class="space-y-2.5">
                    ${step.tips.map(tip => `
                        <div class="flex items-start gap-3 px-4 py-3 rounded-xl bg-slate-50 border border-slate-100">
                            <div class="w-5 h-5 rounded-full bg-violet-100 text-violet-600 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </div>
                            <p class="text-sm text-slate-700 leading-relaxed">${tip}</p>
                        </div>
                    `).join('')}
                </div>
                ${idx === steps.length - 1 ? `
                    <div class="mt-5 p-4 rounded-2xl bg-gradient-to-r from-violet-50 to-indigo-50 border border-violet-100 text-center">
                        <p class="text-sm font-semibold text-violet-800">You&rsquo;re all set! 🎉</p>
                        <p class="text-xs text-violet-600 mt-1">You can re-open this guide anytime using the <strong>?</strong> button.</p>
                    </div>
                ` : ''}
            </div>
        `;

        /* Progress bar */
        const pct = steps.length > 1 ? Math.round(((idx + 1) / steps.length) * 100) : 100;
        document.getElementById('help-progress').style.width = pct + '%';

        /* Step label */
        document.getElementById('help-step-label').textContent = `Step ${idx + 1} of ${steps.length}`;

        /* Navigation buttons */
        const prevBtn = document.getElementById('help-prev-btn');
        const nextBtn = document.getElementById('help-next-btn');

        prevBtn.classList.toggle('hidden', idx === 0);

        if (idx === steps.length - 1) {
            nextBtn.innerHTML = `
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                Done
            `;
        } else {
            nextBtn.innerHTML = `
                Next
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            `;
        }
    }

    /* ── Public API ───────────────────────────────────────────────── */
    window.openHelpGuide = function () {
        current = 0;
        const modal = document.getElementById('help-modal');
        const panel = document.getElementById('help-panel');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        panel.classList.remove('is-open');
        panel.offsetHeight;
        panel.classList.add('is-open');
        renderStep(current, 'forward');
    };

    window.closeHelpGuide = function () {
        document.getElementById('help-modal').classList.add('hidden');
        document.body.style.overflow = '';
    };

    window.helpNext = function () {
        if (current === steps.length - 1) {
            closeHelpGuide();
            return;
        }
        current++;
        renderStep(current, 'forward');
    };

    window.helpPrev = function () {
        if (current === 0) return;
        current--;
        renderStep(current, 'back');
    };

    /* Keyboard navigation */
    document.addEventListener('keydown', function (e) {
        if (document.getElementById('help-modal').classList.contains('hidden')) return;
        if (e.key === 'Escape')      closeHelpGuide();
        if (e.key === 'ArrowRight')  helpNext();
        if (e.key === 'ArrowLeft')   helpPrev();
    });

})();
</script>

@stack('scripts')
</body>
</html>
