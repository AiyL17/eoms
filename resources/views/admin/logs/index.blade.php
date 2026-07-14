@extends('layouts.app')

@section('title', 'System Logs')
@section('page-title', 'System Logs')

@section('content')

{{-- Filters --}}
<div class="card mb-5">
    <div class="px-5 py-4">
        <form action="{{ route('admin.logs.index') }}" method="GET">
            <div class="flex flex-col lg:flex-row gap-3 items-center">

                {{-- Search --}}
                <div class="flex-1 w-full relative">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search by EO number or title…"
                           class="form-input form-input-icon">
                </div>

                {{-- Action type --}}
                <div class="w-full lg:w-48 shrink-0">
                    <select name="action" class="form-input">
                        <option value="">All Actions</option>
                        @foreach($actions as $value => $label)
                            <option value="{{ $value }}" {{ request('action') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- User --}}
                <div class="w-full lg:w-48 shrink-0">
                    <select name="user_id" class="form-input">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Actions --}}
                <div class="flex gap-2 shrink-0 w-full lg:w-auto">
                    <button type="submit" class="btn-primary h-[42px] px-5 w-full lg:w-auto">Filter</button>
                    @if(request()->anyFilled(['action', 'search', 'user_id']))
                        <a href="{{ route('admin.logs.index') }}"
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

{{-- Logs Table --}}
<div class="card">

    {{-- Result count + active filter chips --}}
    <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-4 flex-wrap">
        <p class="text-xs font-semibold text-slate-500">
            {{ number_format($logs->total()) }} {{ Str::plural('record', $logs->total()) }} found
        </p>
        @if(request()->anyFilled(['search', 'action', 'user_id']))
        <div class="flex items-center gap-2 flex-wrap">
            @if(request('search'))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-violet-50 text-violet-700 text-xs font-medium rounded-full border border-violet-100">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    "{{ Str::limit(request('search'), 30) }}"
                </span>
            @endif
            @if(request('action'))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-violet-50 text-violet-700 text-xs font-medium rounded-full border border-violet-100">
                    Action: {{ $actions[request('action')] ?? request('action') }}
                </span>
            @endif
            @if(request('user_id'))
                @php $selectedUser = $users->firstWhere('id', request('user_id')); @endphp
                @if($selectedUser)
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-violet-50 text-violet-700 text-xs font-medium rounded-full border border-violet-100">
                    User: {{ $selectedUser->name }}
                </span>
                @endif
            @endif
        </div>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full table-auto table-wide">
            <thead>
                @php
                    $sortUrl = function (string $col) use ($sort, $dir) {
                        $newDir = ($sort === $col && $dir === 'asc') ? 'desc' : 'asc';
                        return request()->fullUrlWithQuery(['sort' => $col, 'dir' => $newDir, 'page' => 1]);
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
                    <th class="w-44">
                        <a href="{{ $sortUrl('created_at') }}" class="inline-flex items-center gap-1 group hover:text-violet-700 transition-colors">
                            Timestamp {!! $sortIcon('created_at') !!}
                        </a>
                    </th>
                    <th class="w-44">User</th>
                    <th class="w-36">
                        <a href="{{ $sortUrl('action') }}" class="inline-flex items-center gap-1 group hover:text-violet-700 transition-colors">
                            Action {!! $sortIcon('action') !!}
                        </a>
                    </th>
                    <th>Target Record</th>
                    <th class="w-32">IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr class="group">
                    <td class="whitespace-nowrap">
                        <p class="text-[13px] font-semibold text-slate-800">{{ $log->created_at->format('M d, Y') }}</p>
                        <p class="text-xs text-slate-400">{{ $log->created_at->format('h:i:s A') }}</p>
                    </td>
                    <td>
                        <div class="flex items-center gap-2.5">
                            <x-user-avatar :user="$log->user ?? new \App\Models\User(['name' => 'System'])" :size="7" />
                            <div>
                                <p class="text-[13px] font-semibold text-slate-800">{{ $log->user->name ?? 'System' }}</p>
                                <p class="text-[11px] text-slate-400">{{ $log->user->email ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="action-badge-{{ $log->action }}">{{ $log->action_label }}</span>
                    </td>
                    <td>
                        @if($log->executiveOrder)
                            @if($log->executiveOrder->trashed())
                                <a href="{{ route('executive-orders.archive') }}"
                                   class="text-[13px] font-bold text-violet-600 hover:text-violet-800 transition-colors">
                                    {{ $log->executiveOrder->eo_number }}
                                </a>
                                <p class="text-xs text-violet-400 mt-0.5">Archived</p>
                            @else
                                <a href="{{ route('executive-orders.show', $log->executiveOrder) }}"
                                   class="text-[13px] font-bold text-violet-600 hover:text-violet-800 transition-colors">
                                    {{ $log->executiveOrder->eo_number }}
                                </a>
                                <p class="text-xs text-slate-400 truncate max-w-sm mt-0.5">{{ $log->executiveOrder->title }}</p>
                            @endif
                        @else
                            <span class="text-[13px] text-slate-400 italic">Record Deleted</span>
                        @endif
                        @if($log->notes)
                            <p class="text-xs text-slate-400 italic mt-0.5">"{{ $log->notes }}"</p>
                        @endif
                    </td>
                    <td class="font-mono text-xs text-slate-400">{{ $log->ip_address }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-16 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-14 h-14 bg-violet-50 rounded-2xl flex items-center justify-center mb-4 text-violet-400">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-800 mb-1">No logs found</p>
                            <p class="text-sm text-slate-500 mb-4">No activity logs match your current filters.</p>
                            @if(request()->anyFilled(['action', 'search', 'user_id']))
                                <a href="{{ route('admin.logs.index') }}" class="btn-secondary btn-sm">Clear Filters</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
    <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/50 flex items-center justify-between gap-4 flex-wrap">
        <p class="text-xs text-slate-400">
            Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ number_format($logs->total()) }}
        </p>
        {{ $logs->links() }}
    </div>
    @endif
</div>

@endsection
