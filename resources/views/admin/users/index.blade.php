@extends('layouts.app')

@section('title', 'User Profiles')
@section('page-title', 'User Profiles')

@section('header-actions')
    <a href="{{ route('admin.users.create') }}" id="tour-header-btn" class="btn-primary btn-sm">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.66-1.548c0 .526-.099 1.039-.286 1.504" />
        </svg>
        Create User
    </a>
@endsection

@section('content')

{{-- Summary stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6" data-tour="user-stats">
    <div class="stat-card">
        <div class="stat-icon bg-violet-100 text-violet-600">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Users</p>
            <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ $totalUsers }}</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-indigo-100 text-indigo-600">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Administrators</p>
            <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ $adminCount }}</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon bg-slate-100 text-slate-500">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Staff</p>
            <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ $staffCount }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-5" data-tour="user-filters">
    <div class="px-5 py-4">
        <form action="{{ route('admin.users.index') }}" method="GET">
            <div class="flex flex-col sm:flex-row gap-3 items-center">
                <div class="flex-1 w-full relative">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by name or email…"
                           class="form-input form-input-icon">
                </div>
                <div class="w-full sm:w-44 shrink-0">
                    <select name="role" class="form-input">
                        <option value="">All Roles</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                        <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Staff</option>
                    </select>
                </div>
                <div class="flex gap-2 shrink-0 w-full sm:w-auto">
                    <button type="submit" class="btn-primary h-[42px] px-5 w-full sm:w-auto">Filter</button>
                    @if(request()->anyFilled(['search', 'role']))
                        <a href="{{ route('admin.users.index') }}"
                           class="btn-secondary h-[42px] px-4 text-slate-400 hover:text-slate-600"
                           title="Clear filters">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- User table --}}
<div class="card" data-tour="user-table">
    {{-- Result count + active filter chips --}}
    <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-4 flex-wrap">
        <p class="text-xs font-semibold text-slate-500">
            {{ $users->count() }} {{ Str::plural('user', $users->count()) }} found
        </p>
        @if(request()->anyFilled(['search', 'role']))
        <div class="flex items-center gap-2">
            @if(request('search'))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-violet-50 text-violet-700 text-xs font-medium rounded-full border border-violet-100">
                    "{{ Str::limit(request('search'), 25) }}"
                </span>
            @endif
            @if(request('role'))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-violet-50 text-violet-700 text-xs font-medium rounded-full border border-violet-100">
                    {{ request('role') === 'admin' ? 'Administrator' : 'Staff' }}
                </span>
            @endif
        </div>
        @endif
    </div>
    {{-- ── Mobile card list (< md) ──────────────────────────────────────────── --}}
    <div class="block md:hidden divide-y divide-slate-100">
        @foreach($users as $user)
        <div class="px-4 py-4">
            <div class="flex items-center gap-3 mb-2">
                <x-user-avatar :user="$user" :size="10" />
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-semibold text-slate-800">{{ $user->name }}</span>
                        @if(auth()->id() === $user->id)
                            <span class="text-[10px] font-bold text-violet-600 bg-violet-50 border border-violet-100 px-1.5 py-0.5 rounded-full uppercase tracking-wider">You</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 truncate">{{ $user->email }}</p>
                </div>
                <div class="flex items-center gap-1 shrink-0">
                    <a href="{{ route('admin.users.edit', $user) }}"
                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-violet-500 hover:text-violet-700 hover:bg-violet-50 transition-all" title="Edit">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
                    </a>
                    @if(auth()->id() !== $user->id)
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 transition-all" title="Delete"
                                data-confirm="Delete {{ $user->name }}? This cannot be undone.">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-3 flex-wrap text-xs text-slate-500">
                @if($user->role === 'admin')
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-violet-50 text-violet-700 border border-violet-100"><span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>Administrator</span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Staff</span>
                @endif
                @if($user->isOnline())
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="relative flex w-1.5 h-1.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full w-1.5 h-1.5 bg-emerald-500"></span>
                        </span>
                        Online
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-400 border border-slate-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                        Offline
                    </span>
                @endif
                @if($user->position)<span>{{ $user->position }}</span>@endif
                <span>Since {{ $user->created_at->format('M d, Y') }}</span>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── Desktop table (md+) ───────────────────────────────────────────── --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="w-full table-auto table-wide">
            <thead>
                @php
                    $sortUrl = function (string $col) use ($sort, $dir) {
                        $newDir = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
                        return request()->fullUrlWithQuery(['sort' => $col, 'dir' => $newDir]);
                    };
                    $sortIcon = function (string $col) use ($sort, $dir) {
                        if ($sort !== $col) {
                            return '<svg class="w-3.5 h-3.5 text-slate-300 group-hover:text-slate-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" /></svg>';
                        }
                        if ($dir === 'asc') {
                            return '<svg class="w-3.5 h-3.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" /></svg>';
                        }
                        return '<svg class="w-3.5 h-3.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>';
                    };
                @endphp
                <tr>
                    <th><a href="{{ $sortUrl('name') }}" class="inline-flex items-center gap-1 group hover:text-violet-700 transition-colors">Name {!! $sortIcon('name') !!}</a></th>
                    <th><a href="{{ $sortUrl('email') }}" class="inline-flex items-center gap-1 group hover:text-violet-700 transition-colors">Email {!! $sortIcon('email') !!}</a></th>
                    <th><a href="{{ $sortUrl('role') }}" class="inline-flex items-center gap-1 group hover:text-violet-700 transition-colors">Role {!! $sortIcon('role') !!}</a></th>
                    <th>Position</th>
                    <th><a href="{{ $sortUrl('created_at') }}" class="inline-flex items-center gap-1 group hover:text-violet-700 transition-colors">Member Since {!! $sortIcon('created_at') !!}</a></th>
                    <th>Status</th>
                    <th class="text-right pr-6">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="group">
                    <td>
                        <div class="flex items-center gap-3">
                            <x-user-avatar :user="$user" :size="8" />
                            <div>
                                <span class="font-semibold text-slate-800">{{ $user->name }}</span>
                                @if(auth()->id() === $user->id)
                                    <span class="ml-2 text-[10px] font-bold text-violet-600 bg-violet-50 border border-violet-100 px-1.5 py-0.5 rounded-full uppercase tracking-wider">You</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-slate-500 text-[13px]">{{ $user->email }}</td>
                    <td>
                        @if($user->role === 'admin')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-violet-50 text-violet-700 border border-violet-100"><span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>Administrator</span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Staff</span>
                        @endif
                    </td>
                    <td class="text-slate-500 text-[13px]">{{ $user->position ?: '—' }}</td>
                    <td class="text-slate-400 text-[13px] whitespace-nowrap">{{ $user->created_at->format('M d, Y') }}</td>
                    <td>
                        @if($user->isOnline())
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="relative flex w-2 h-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full w-2 h-2 bg-emerald-500"></span>
                                </span>
                                Online
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-400 border border-slate-200">
                                <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                                Offline
                            </span>
                        @endif
                    </td>
                    <td class="text-right pr-5 whitespace-nowrap">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('admin.users.edit', $user) }}"
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-violet-500 hover:text-violet-700 hover:bg-violet-50 transition-all" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
                            </a>
                            @if(auth()->id() !== $user->id)
                            <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 transition-all" title="Delete"
                                        data-confirm="Delete {{ $user->name }}? This cannot be undone.">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                </button>
                            </form>
                            @else
                            <span class="w-8 h-8"></span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
