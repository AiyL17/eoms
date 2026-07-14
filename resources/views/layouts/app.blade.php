<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — EOMS</title>
    <meta name="description" content="Executive Order Management System — City Government">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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

            <a href="{{ route('executive-orders.archive') }}"
               class="nav-link {{ request()->routeIs('executive-orders.archive') ? 'active' : '' }}">
                <svg class="w-4.5 h-4.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                <span>Archive</span>
            </a>

            <a href="{{ route('admin.settings.index') }}"
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
        <div class="p-4 border-t border-white/10">
            <a href="{{ route('profile.edit') }}"
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

                {{-- Page-specific action buttons --}}
                @yield('header-actions')

                {{-- Divider --}}
                <div class="w-px h-5 bg-slate-200"></div>

                {{-- Notification Bell — always pinned at the end --}}
                @php $unreadCount = auth()->user()->unreadNotifications->count(); @endphp
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">

                    <button @click="open = !open"
                            class="relative w-9 h-9 flex items-center justify-center rounded-xl text-slate-500 hover:bg-slate-100 hover:text-slate-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        @if($unreadCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center leading-none">
                            {{ $unreadCount > 9 ? '9+' : $unreadCount }}
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
                         class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden"
                         style="display: none;">

                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                            <p class="text-sm font-bold text-slate-800">Notifications
                                @if($unreadCount > 0)
                                <span class="ml-1.5 text-[11px] font-bold bg-red-100 text-red-600 px-1.5 py-0.5 rounded-full">{{ $unreadCount }} new</span>
                                @endif
                            </p>
                            @if($unreadCount > 0)
                            <form action="{{ route('notifications.read-all') }}" method="POST">
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
                                    'eo_uploaded'       => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'path' => 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5'],
                                    'eo_status_changed' => ['bg' => 'bg-amber-100',   'text' => 'text-amber-600',   'path' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99'],
                                    'eo_updated'        => ['bg' => 'bg-blue-100',    'text' => 'text-blue-600',    'path' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z'],
                                    'eo_deleted'        => ['bg' => 'bg-red-100',     'text' => 'text-red-600',     'path' => 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0'],
                                    default             => ['bg' => 'bg-slate-100',   'text' => 'text-slate-500',   'path' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0'],
                                };
                            @endphp
                            <form action="{{ route('notifications.read', $n->id) }}" method="POST">
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
        <main class="flex-1 overflow-y-auto px-8 py-7">
            @yield('content')
        </main>
    </div>
</div>

{{-- ══════════════════════════════════════════════════ TOAST NOTIFICATIONS ══ --}}
<div id="toast-container"
     class="fixed top-6 right-6 z-[9999] flex flex-col gap-3 pointer-events-none"
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
        <div class="toast pointer-events-auto w-96 rounded-2xl border shadow-xl overflow-hidden
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
                <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </div>
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

    // Icon paths for known action types
    const icons = {
        archive: 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z',
        delete:  'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0',
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
        };
        openConfirm(form.dataset.confirm, form, opts);
    });
})();
</script>

@stack('scripts')
</body>
</html>
