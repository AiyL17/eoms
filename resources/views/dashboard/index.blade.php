@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('header-actions')
    <a href="{{ route('executive-orders.create') }}" class="btn-primary btn-sm">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Upload EO
    </a>
@endsection

@section('content')

{{-- ── KPI Stats ─────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- Total EOs --}}
    <div class="stat-card">
        <div class="stat-icon bg-violet-100 text-violet-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total EOs</p>
            <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ number_format($totalEos) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">all time</p>
        </div>
    </div>

    {{-- Total Users (admin only, shows placeholder for staff) --}}
    <div class="stat-card">
        <div class="stat-icon bg-indigo-100 text-indigo-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Users</p>
            <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ $totalUsers }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ $adminCount }} admin · {{ $staffCount }} staff</p>
        </div>
    </div>

    {{-- Downloads --}}
    <div class="stat-card">
        <div class="stat-icon bg-violet-100 text-violet-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Downloads</p>
            <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ number_format($thisMonthDownloads) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">this month · {{ number_format($totalDownloads) }} all-time</p>
        </div>
    </div>

    {{-- Storage Used --}}
    <div class="stat-card">
        <div class="stat-icon bg-emerald-100 text-emerald-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 2.625c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125m0 2.625c0 2.278 3.694 4.125 8.25 4.125s8.25-1.847 8.25-4.125" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Storage Used</p>
            @php
                $mb = $totalStorageBytes / 1048576;
                $storageDisplay = $mb >= 1 ? round($mb, 1) . ' MB' : round($totalStorageBytes / 1024, 1) . ' KB';
            @endphp
            <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ $storageDisplay }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">across {{ $totalEos }} PDF files</p>
        </div>
    </div>

</div>

{{-- ── Row 3: Status Breakdown + Year Volume ──────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- Status Distribution --}}
    <div class="lg:col-span-2 card">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Status Distribution</h2>
                <p class="text-xs text-slate-400 mt-0.5">Breakdown of all executive orders by current status</p>
            </div>
        </div>
        <div class="p-6 space-y-3">
            @php
            $statusConfig = [
                'active'       => ['label' => 'Active',       'color' => '#10b981'],
                'amended'      => ['label' => 'Amended',      'color' => '#f59e0b'],
                'repealed'     => ['label' => 'Repealed',     'color' => '#ef4444'],
                'suspended'    => ['label' => 'Suspended',    'color' => '#f97316'],
                'superseded'   => ['label' => 'Superseded',   'color' => '#8b5cf6'],
                'under_review' => ['label' => 'Under Review', 'color' => '#0ea5e9'],
            ];
        @endphp
        @foreach($statusConfig as $key => $cfg)
        @php $count = $statusCounts[$key] ?? 0; $pct = $totalEos > 0 ? round(($count / $totalEos) * 100) : 0; @endphp
        <div class="flex items-center gap-3">
            <span class="w-24 text-xs font-semibold text-slate-600 shrink-0">{{ $cfg['label'] }}</span>
            <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                <div class="h-2 rounded-full transition-all duration-500"
                     style="width: {{ $pct }}%; background-color: {{ $cfg['color'] }};"></div>
            </div>
            <span class="w-8 text-xs font-bold text-right text-slate-700">{{ $count }}</span>
            <span class="w-10 text-xs text-slate-400 text-right">{{ $pct }}%</span>
        </div>
        @endforeach
        </div>
    </div>

    {{-- EOs by Year --}}
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-bold text-slate-800">By Year</h2>
                <p class="text-xs text-slate-400 mt-0.5">Volume per issuance year</p>
            </div>
        </div>
        <div class="overflow-x-auto overflow-y-auto" style="max-height: 260px;">
            <table class="w-full table-auto">
                <thead class="sticky top-0 z-10">
                    <tr>
                        <th>Year</th>
                        <th class="text-right">Count</th>
                        <th class="text-right pr-6">Share</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($yearList as $row)
                    @php $pct = $totalEos > 0 ? round(($row->count / $totalEos) * 100) : 0; @endphp
                    <tr>
                        <td class="font-bold text-slate-800">{{ $row->year }}</td>
                        <td class="text-right font-semibold text-slate-700">{{ $row->count }}</td>
                        <td class="text-right pr-5">
                            <span class="text-xs font-medium text-slate-400">{{ $pct }}%</span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="py-8 text-center text-slate-400 text-sm">No data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ── Row 4: Recent EOs + Needs Attention + Top Users ───────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- Recently Uploaded --}}
    <div class="lg:col-span-2 card">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Recently Uploaded</h2>
                <p class="text-xs text-slate-400 mt-0.5">Latest 5 executive orders added</p>
            </div>
            <a href="{{ route('executive-orders.index') }}"
               class="text-xs font-semibold text-violet-600 hover:text-violet-800 transition-colors flex items-center gap-1">
                View All
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr>
                        <th>EO Number</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Issued</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentEos as $eo)
                    <tr>
                        <td class="font-bold text-slate-800 whitespace-nowrap text-[13px]">{{ $eo->eo_number }}</td>
                        <td><div class="truncate max-w-[180px] text-slate-600 text-[13px]" title="{{ $eo->subject }}">{{ $eo->subject }}</div></td>
                        <td><span class="badge-{{ $eo->status }}">{{ $eo->status_label }}</span></td>
                        <td class="text-slate-500 text-[13px] whitespace-nowrap">{{ $eo->date_issued->format('M d, Y') }}</td>
                        <td class="text-right">
                            <a href="{{ route('executive-orders.show', $eo) }}"
                               class="inline-flex items-center gap-1 text-xs font-semibold text-violet-600 hover:text-violet-800 transition-colors">
                                View <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center">
                            <p class="text-sm font-semibold text-slate-700 mb-1">No executive orders yet</p>
                            <a href="{{ route('executive-orders.create') }}" class="text-violet-600 text-sm font-semibold hover:underline">Upload the first one →</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Right column: Needs Attention + Top Users (admin only) --}}
    <div class="space-y-6">

        {{-- Needs Attention --}}
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Needs Attention</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Under review or suspended EOs</p>
                </div>
            </div>
            <div class="p-4 space-y-2 overflow-y-auto" style="max-height: 260px;">
                @forelse($needsAttention as $eo)
                <a href="{{ route('executive-orders.show', $eo) }}"
                   class="flex items-start gap-3 p-3 rounded-xl hover:bg-slate-50 transition-colors group">
                    <span class="w-2 h-2 rounded-full mt-1.5 shrink-0 {{ $eo->status === 'under_review' ? 'bg-sky-400' : 'bg-orange-400' }}"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold text-slate-800 group-hover:text-violet-700 transition-colors">{{ $eo->eo_number }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ $eo->title }}</p>
                        <span class="badge-{{ $eo->status }} mt-1 inline-block">{{ $eo->status_label }}</span>
                    </div>
                </a>
                @empty
                <div class="py-6 text-center">
                    <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center mx-auto mb-2 text-emerald-500">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <p class="text-xs font-semibold text-slate-600">All clear</p>
                    <p class="text-xs text-slate-400">No EOs need attention</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Top Active Users (admin only) --}}
        @if(auth()->user()->isAdmin())
        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="text-sm font-bold text-slate-800">Most Active Users</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Last 30 days by actions</p>
                </div>
                <a href="{{ route('admin.users.index') }}"
                   class="text-xs font-semibold text-violet-600 hover:text-violet-800 transition-colors flex items-center gap-1">
                    All Users <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </a>
            </div>
            <div class="p-4 space-y-2 overflow-y-auto" style="max-height: 240px;">
                @forelse($topUsers as $entry)
                <div class="flex items-center gap-3 px-2 py-1.5 rounded-xl hover:bg-slate-50 transition-colors">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-[11px] font-bold shrink-0"
                         style="background: linear-gradient(135deg,#6d28d9,#7c3aed);">
                        {{ strtoupper(substr($entry->user->name ?? '?', 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-semibold text-slate-800 truncate">{{ $entry->user->name ?? 'Unknown' }}</p>
                        <p class="text-[11px] text-slate-400">{{ ucfirst($entry->user->role ?? '') }}</p>
                    </div>
                    <span class="text-xs font-bold text-violet-700 bg-violet-50 px-2 py-0.5 rounded-full border border-violet-100">
                        {{ $entry->action_count }}
                    </span>
                </div>
                @empty
                <p class="py-6 text-center text-sm text-slate-400">No activity in the last 30 days.</p>
                @endforelse
            </div>
        </div>
        @endif

    </div>
</div>

{{-- ── Row 5: Activity Feed + 7-day upload sparkline ─────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Recent Activity --}}
    <div class="lg:col-span-2 card">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Recent Activity</h2>
                <p class="text-xs text-slate-400 mt-0.5">Latest system actions across all users</p>
            </div>
            @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.logs.index') }}"
               class="text-xs font-semibold text-violet-600 hover:text-violet-800 transition-colors flex items-center gap-1">
                Full Log <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
            </a>
            @endif
        </div>
        <div class="divide-y divide-slate-50 overflow-y-auto" style="max-height: 420px;">
            @forelse($recentLogs as $log)
            <div class="flex items-start gap-3 px-5 py-3 hover:bg-slate-50/70 transition-colors">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full shrink-0 mt-0.5 action-{{ $log->action }}">
                    @if($log->action === 'created')
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    @elseif($log->action === 'updated')
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                    @elseif($log->action === 'status_changed')
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                    @elseif($log->action === 'deleted')
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
                        @if($log->executiveOrder)
                            <a href="{{ route('executive-orders.show', $log->executiveOrder) }}" class="ml-1 font-semibold text-violet-600 hover:text-violet-800 transition-colors">{{ $log->executiveOrder->eo_number }}</a>
                        @else
                            <span class="ml-1 text-slate-400 italic">Deleted EO</span>
                        @endif
                    </p>
                </div>
            </div>
            @empty
            <div class="py-12 text-center text-slate-400 text-sm">No recent activity.</div>
            @endforelse
        </div>
    </div>

    {{-- 7-Day Upload Activity --}}
    <div class="card">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Uploads — Last 7 Days</h2>
                <p class="text-xs text-slate-400 mt-0.5">Daily EO upload count</p>
            </div>
        </div>
        <div class="p-6">
            @php $maxCount = max($last7Days->pluck('count')->toArray() ?: [1]); @endphp
            <div class="flex items-end justify-between gap-2 h-28">
                @foreach($last7Days as $day)
                @php $barHeight = $maxCount > 0 ? max(4, round(($day['count'] / $maxCount) * 100)) : 4; @endphp
                <div class="flex flex-col items-center gap-1.5 flex-1">
                    <span class="text-[11px] font-bold text-slate-600">{{ $day['count'] > 0 ? $day['count'] : '' }}</span>
                    <div class="w-full rounded-t-lg transition-all duration-500 {{ $day['count'] > 0 ? 'bg-violet-500' : 'bg-slate-100' }}"
                         style="height: {{ $barHeight }}%"></div>
                    <span class="text-[10px] text-slate-400 font-medium">{{ $day['label'] }}</span>
                </div>
                @endforeach
            </div>
            <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-500">Total this week</span>
                <span class="text-sm font-bold text-slate-900">{{ $last7Days->sum('count') }} uploads</span>
            </div>
        </div>
    </div>

</div>

@endsection
