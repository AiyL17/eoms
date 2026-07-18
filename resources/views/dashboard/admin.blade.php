@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Administrator Dashboard')

@section('header-actions')
    <div id="tour-header-btn" class="flex items-center gap-2">
        <a href="{{ route('admin.users.index') }}" class="btn-secondary btn-sm">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
            Manage Users
        </a>
        <a href="{{ route('documents.create') }}" class="btn-primary btn-sm">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Register Document
        </a>
    </div>
@endsection

@section('content')

{{-- Welcome Banner --}}
<div class="bg-gradient-to-r from-violet-600 to-indigo-600 rounded-2xl px-6 py-5 mb-6 flex items-center justify-between" data-tour="welcome-banner">
    <div>
        <p class="text-white/70 text-xs font-semibold uppercase tracking-wider mb-1">Welcome back</p>
        <h2 class="text-white text-xl font-bold">{{ auth()->user()->name }}</h2>
        <p class="text-white/60 text-sm mt-0.5">{{ auth()->user()->position ?? ucfirst(auth()->user()->role) }}</p>
    </div>
    <div class="text-right hidden sm:block">
        <p id="dashboard-clock" class="text-white text-2xl font-bold tabular-nums tracking-tight"></p>
        <p id="dashboard-date" class="text-white/60 text-xs font-medium mt-0.5"></p>
    </div>
</div>

{{-- KPI Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6" data-tour="kpi-stats">

    <a href="{{ route('documents.index') }}" class="stat-card hover:ring-2 hover:ring-violet-200 hover:shadow-md transition-all">
        <div class="stat-icon bg-violet-100 text-violet-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Documents</p>
            <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ number_format($totalDocs) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ $thisYearDocs }} this year</p>
        </div>
    </a>

    <a href="{{ route('admin.users.index') }}" class="stat-card hover:ring-2 hover:ring-indigo-200 hover:shadow-md transition-all">
        <div class="stat-icon bg-indigo-100 text-indigo-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Users</p>
            <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ $totalUsers }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ $adminCount }} admin · {{ $staffCount }} staff · +{{ $newUsersThisMonth }} this month</p>
        </div>
    </a>

    <a href="{{ route('documents.index', ['document_type' => 'incoming']) }}" class="stat-card hover:ring-2 hover:ring-blue-200 hover:shadow-md transition-all">
        <div class="stat-icon bg-blue-100 text-blue-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3v8.25m0 0l-3-3m3 3l3-3" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Incoming</p>
            <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ number_format($typeCounts['incoming'] ?? 0) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">received documents</p>
        </div>
    </a>

    <a href="{{ route('documents.index', ['document_type' => 'outgoing']) }}" class="stat-card hover:ring-2 hover:ring-emerald-200 hover:shadow-md transition-all">
        <div class="stat-icon bg-emerald-100 text-emerald-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Outgoing</p>
            <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ number_format($typeCounts['outgoing'] ?? 0) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">sent documents</p>
        </div>
    </a>

</div>

{{-- Document Type Breakdown + Year Volume --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    <div class="lg:col-span-2 card" data-tour="type-distribution">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Document Type Breakdown</h2>
                <p class="text-xs text-slate-400 mt-0.5">Incoming vs outgoing across all registered documents</p>
            </div>
        </div>
        <div class="p-6 space-y-4">
            @php
            $typeConfig = [
                'incoming' => ['label' => 'Incoming', 'color' => '#3b82f6'],
                'outgoing' => ['label' => 'Outgoing', 'color' => '#10b981'],
            ];
            @endphp
            @foreach($typeConfig as $key => $cfg)
            @php $count = $typeCounts[$key] ?? 0; $pct = $totalDocs > 0 ? round(($count / $totalDocs) * 100) : 0; @endphp
            <div class="flex items-center gap-3">
                <span class="w-20 text-xs font-semibold text-slate-600 shrink-0">{{ $cfg['label'] }}</span>
                <div class="flex-1 bg-slate-100 rounded-full h-2.5 overflow-hidden">
                    <div class="h-2.5 rounded-full transition-all duration-500" style="width: {{ $pct }}%; background-color: {{ $cfg['color'] }};"></div>
                </div>
                <span class="w-10 text-xs font-bold text-right text-slate-700">{{ $count }}</span>
                <span class="w-10 text-xs text-slate-400 text-right">{{ $pct }}%</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card" data-tour="by-year">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-bold text-slate-800">By Year</h2>
                <p class="text-xs text-slate-400 mt-0.5">Volume per year</p>
            </div>
        </div>
        <div class="overflow-x-auto overflow-y-auto" style="max-height: 260px;">
            <table class="w-full table-auto">
                <thead class="sticky top-0 z-10">
                    <tr><th>Year</th><th class="text-right">Count</th><th class="text-right pr-6">Share</th></tr>
                </thead>
                <tbody>
                    @forelse($yearList as $row)
                    @php $pct = $totalDocs > 0 ? round(($row->count / $totalDocs) * 100) : 0; @endphp
                    <tr>
                        <td class="font-bold text-slate-800">{{ $row->year }}</td>
                        <td class="text-right font-semibold text-slate-700">{{ $row->count }}</td>
                        <td class="text-right pr-5"><span class="text-xs font-medium text-slate-400">{{ $pct }}%</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="py-8 text-center text-slate-400 text-sm">No data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Recent Documents + Top Users --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    <div class="lg:col-span-2 card" data-tour="recent-eos">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Recently Registered</h2>
                <p class="text-xs text-slate-400 mt-0.5">Latest 5 documents added to the system</p>
            </div>
            <a href="{{ route('documents.index') }}" class="text-xs font-semibold text-violet-600 hover:text-violet-800 transition-colors flex items-center gap-1">
                View All <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
            </a>
        </div>
        <div class="block md:hidden divide-y divide-slate-100">
            @forelse($recentDocs as $doc)
            <a href="{{ route('documents.show', $doc) }}" class="flex items-start gap-3 px-4 py-3.5 hover:bg-violet-50/40 transition-colors">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-0.5">
                        <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded {{ $doc->document_type === 'incoming' ? 'bg-blue-50 text-blue-600' : 'bg-emerald-50 text-emerald-600' }}">{{ $doc->document_type_label }}</span>
                    </div>
                    <p class="text-xs text-slate-500 truncate">{{ $doc->title }}</p>
                </div>
                <svg class="w-4 h-4 text-slate-300 shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            </a>
            @empty
            <div class="py-12 text-center px-4">
                <p class="text-sm font-semibold text-slate-700 mb-1">No documents yet</p>
                <a href="{{ route('documents.create') }}" class="text-violet-600 text-sm font-semibold hover:underline">Register the first one →</a>
            </div>
            @endforelse
        </div>
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr>
                        <th>Document Name</th>
                        <th>Type</th>
                        <th>Registered By</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentDocs as $doc)
                    <tr>
                        <td><div class="truncate max-w-[200px] text-slate-600 text-[13px]" title="{{ $doc->title }}">{{ $doc->title }}</div></td>
                        <td>
                            <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded {{ $doc->document_type === 'incoming' ? 'bg-blue-50 text-blue-600' : 'bg-emerald-50 text-emerald-600' }}">
                                {{ $doc->document_type_label }}
                            </span>
                        </td>
                        <td class="text-slate-500 text-[13px]">{{ $doc->uploader->name ?? '—' }}</td>
                        <td class="text-right">
                            <a href="{{ route('documents.show', $doc) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-violet-600 hover:text-violet-800 transition-colors">
                                View <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-12 text-center">
                            <p class="text-sm font-semibold text-slate-700 mb-1">No documents yet</p>
                            <a href="{{ route('documents.create') }}" class="text-violet-600 text-sm font-semibold hover:underline">Register the first one →</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Most Active Users --}}
    <div class="card" data-tour="top-users">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Most Active Users</h2>
                <p class="text-xs text-slate-400 mt-0.5">Last 30 days by actions</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-violet-600 hover:text-violet-800 transition-colors flex items-center gap-1">
                All Users <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
            </a>
        </div>
        <div class="p-4 space-y-2 overflow-y-auto" style="max-height: 260px;">
            @forelse($topUsers as $entry)
            <div class="flex items-center gap-3 px-2 py-1.5 rounded-xl hover:bg-slate-50 transition-colors">
                <x-user-avatar :user="$entry->user ?? new \App\Models\User(['name' => '?'])" :size="7" />
                <div class="flex-1 min-w-0">
                    <p class="text-[13px] font-semibold text-slate-800 truncate">{{ $entry->user->name ?? 'Unknown' }}</p>
                    <p class="text-[11px] text-slate-400">{{ ucfirst($entry->user->role ?? '') }}</p>
                </div>
                <span class="text-xs font-bold text-violet-700 bg-violet-50 px-2 py-0.5 rounded-full border border-violet-100">{{ $entry->action_count }}</span>
            </div>
            @empty
            <p class="py-6 text-center text-sm text-slate-400">No activity in the last 30 days.</p>
            @endforelse
        </div>
    </div>
</div>

{{-- Activity Feed + 7-day Sparkline --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 card" data-tour="activity-feed">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-bold text-slate-800">System Activity</h2>
                <p class="text-xs text-slate-400 mt-0.5">Latest actions across all users</p>
            </div>
            <a href="{{ route('admin.logs.index') }}" class="text-xs font-semibold text-violet-600 hover:text-violet-800 transition-colors flex items-center gap-1">
                Full Log <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
            </a>
        </div>
        <div class="divide-y divide-slate-50 overflow-y-auto" style="max-height: 420px;">
            @forelse($recentLogs as $log)
            <div class="flex items-start gap-3 px-5 py-3 hover:bg-slate-50/70 transition-colors">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full shrink-0 mt-0.5 action-{{ $log->action }}">
                    @if($log->action === 'created')
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    @elseif($log->action === 'updated')
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                    @elseif($log->action === 'deleted')
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z" /></svg>
                    @elseif($log->action === 'force_deleted')
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                    @elseif($log->action === 'downloaded')
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    @else
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    @endif
                </span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-baseline justify-between gap-2">
                        <p class="text-[13px] font-semibold text-slate-800">{{ $log->user->name ?? 'System' }}</p>
                        <p class="text-[11px] text-slate-400 shrink-0">{{ $log->created_at->diffForHumans() }}</p>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        <span class="action-badge-{{ $log->action }}">{{ $log->action_label }}</span>
                        @if($log->document)
                            @if($log->document->trashed())
                                <a href="{{ route('documents.archive') }}" class="ml-1 font-semibold text-violet-600 hover:text-violet-800 transition-colors">{{ $log->document->doc_number }}</a>
                            @else
                                <a href="{{ route('documents.show', $log->document) }}" class="ml-1 font-semibold text-violet-600 hover:text-violet-800 transition-colors">{{ $log->document->doc_number }}</a>
                            @endif
                        @else
                            <span class="ml-1 text-slate-400 italic">Deleted document</span>
                        @endif
                    </p>
                </div>
            </div>
            @empty
            <div class="py-12 text-center text-slate-400 text-sm">No recent activity.</div>
            @endforelse
        </div>
    </div>

    <div class="card" data-tour="upload-sparkline">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Registrations — Last 7 Days</h2>
                <p class="text-xs text-slate-400 mt-0.5">Daily document registration count</p>
            </div>
        </div>
        <div class="p-6">
            @php $maxCount = max($last7Days->pluck('count')->toArray() ?: [1]); @endphp
            <div class="flex items-end justify-between gap-2 h-28">
                @foreach($last7Days as $day)
                @php $barHeight = $maxCount > 0 ? max(4, round(($day['count'] / $maxCount) * 100)) : 4; @endphp
                <div class="flex flex-col items-center gap-1.5 flex-1">
                    <span class="text-[11px] font-bold text-slate-600">{{ $day['count'] > 0 ? $day['count'] : '' }}</span>
                    <div class="w-full rounded-t-lg transition-all duration-500 {{ $day['count'] > 0 ? 'bg-violet-500' : 'bg-slate-100' }}" style="height: {{ $barHeight }}%"></div>
                    <span class="text-[10px] text-slate-400 font-medium">{{ $day['label'] }}</span>
                </div>
                @endforeach
            </div>
            <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-500">Total this week</span>
                <span class="text-sm font-bold text-slate-900">{{ $last7Days->sum('count') }} documents</span>
            </div>
        </div>
        <div class="px-6 pb-6 pt-2 border-t border-slate-100 mt-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Quick Actions</p>
            <div class="space-y-2">
                <a href="{{ route('admin.users.create') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-violet-50 text-slate-700 hover:text-violet-700 transition-colors group">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" /></svg>
                    <span class="text-sm font-medium">Add New User</span>
                </a>
                <a href="{{ route('admin.logs.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-violet-50 text-slate-700 hover:text-violet-700 transition-colors group">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" /></svg>
                    <span class="text-sm font-medium">View Audit Logs</span>
                </a>
                <a href="{{ route('documents.export') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-violet-50 text-slate-700 hover:text-violet-700 transition-colors group">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    <span class="text-sm font-medium">Export All (CSV)</span>
                </a>

            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function () {
    const clockEl = document.getElementById('dashboard-clock');
    const dateEl  = document.getElementById('dashboard-date');
    if (!clockEl || !dateEl) return;
    const days   = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    function tick() {
        const now = new Date();
        let h = now.getHours();
        const m = String(now.getMinutes()).padStart(2,'0');
        const s = String(now.getSeconds()).padStart(2,'0');
        const ampm = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        clockEl.textContent = `${h}:${m}:${s} ${ampm}`;
        dateEl.textContent  = `${days[now.getDay()]}, ${months[now.getMonth()]} ${now.getDate()}, ${now.getFullYear()}`;
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
@endpush
