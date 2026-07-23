@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'My Dashboard')

@section('header-actions')
    <a href="{{ route('documents.create') }}" id="tour-header-btn" class="btn-primary btn-sm" title="Upload Document">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        <span class="hidden sm:inline">Upload Document</span>
    </a>
@endsection

@section('content')

{{-- Welcome Banner --}}
<div class="bg-gradient-to-r from-violet-600 to-indigo-600 rounded-2xl px-6 py-5 mb-6 flex items-center justify-between" data-tour="welcome-banner">
    <div>
        <p class="text-white/70 text-xs font-semibold uppercase tracking-wider mb-1">Welcome back</p>
        <h2 class="text-white text-xl font-bold">
            <span id="welcome-name-text"></span><span id="welcome-cursor" class="inline-block w-0.5 h-5 bg-white/80 align-middle ml-0.5 animate-pulse"></span>
        </h2>
        <p class="text-white/60 text-sm mt-0.5">{{ auth()->user()->position ?? ucfirst(auth()->user()->role) }}</p>
    </div>
    <div class="text-right hidden sm:block">
        <p id="dashboard-clock" class="text-white text-2xl font-bold tabular-nums tracking-tight"></p>
        <p id="dashboard-date" class="text-white/60 text-xs font-medium mt-0.5"></p>
    </div>
</div>

{{-- Personal KPI Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6" data-tour="kpi-stats">

    <a href="{{ route('documents.index') }}" class="stat-card hover:ring-2 hover:ring-violet-200 hover:shadow-md transition-all">
        <div class="stat-icon bg-violet-100 text-violet-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">My Uploads</p>
            <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ number_format($myTotalUploads) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">{{ $myThisMonth }} this month</p>
        </div>
    </a>

    <div class="stat-card">
        <div class="stat-icon bg-indigo-100 text-indigo-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">My Downloads</p>
            <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ number_format($myDownloads) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">PDFs downloaded</p>
        </div>
    </div>

    <a href="{{ route('documents.index') }}" class="stat-card hover:ring-2 hover:ring-emerald-200 hover:shadow-md transition-all">
        <div class="stat-icon bg-emerald-100 text-emerald-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Documents</p>
            <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ number_format($totalDocs) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">system-wide</p>
        </div>
    </a>

    <a href="{{ route('documents.index', ['year' => date('Y')]) }}" class="stat-card hover:ring-2 hover:ring-amber-200 hover:shadow-md transition-all">
        <div class="stat-icon bg-amber-100 text-amber-600">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
        </div>
        <div>
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">This Year</p>
            <p class="text-2xl font-bold text-slate-900 mt-0.5">{{ number_format($thisYearDocs) }}</p>
            <p class="text-[11px] text-slate-400 mt-0.5">registered in {{ date('Y') }}</p>
        </div>
    </a>

</div>

{{-- My Recent Uploads + Type Overview --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    <div class="lg:col-span-2 card" data-tour="recent-eos">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-bold text-slate-800">My Recent Uploads</h2>
                <p class="text-xs text-slate-400 mt-0.5">Documents you have uploaded</p>
            </div>
            <a href="{{ route('documents.index') }}" class="text-xs font-semibold text-violet-600 hover:text-violet-800 transition-colors flex items-center gap-1">
                Browse All <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
            </a>
        </div>
        <div class="block md:hidden divide-y divide-slate-100">
            @forelse($myRecentDocs as $doc)
            <a href="{{ route('documents.show', $doc) }}" class="flex items-start gap-3 px-4 py-3.5 hover:bg-violet-50/40 transition-colors">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-0.5">
                        <span class="text-xs font-bold text-violet-700 font-mono">{{ $doc->reference_number }}</span>
                        <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded {{ $doc->document_type === 'incoming' ? 'bg-blue-50 text-blue-600' : 'bg-emerald-50 text-emerald-600' }}">{{ $doc->document_type_label }}</span>
                    </div>
                    <p class="text-xs text-slate-500 truncate">{{ $doc->title }}</p>
                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $doc->date_issued->format('M d, Y') }} · {{ $doc->created_at->format('h:i A') }}</p>
                </div>
                <svg class="w-4 h-4 text-slate-300 shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            </a>
            @empty
            <div class="py-12 text-center px-4">
                <p class="text-sm font-semibold text-slate-700 mb-1">You haven't uploaded any documents yet</p>
                <a href="{{ route('documents.create') }}" class="text-violet-600 text-sm font-semibold hover:underline">Upload your first one →</a>
            </div>
            @endforelse
        </div>
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full table-auto">
                <thead>
                    <tr>
                        <th>Document No.</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Date Received</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($myRecentDocs as $doc)
                    <tr>
                        <td class="font-bold text-slate-800 whitespace-nowrap text-[13px]">{{ $doc->reference_number }}</td>
                        <td><div class="truncate max-w-[180px] text-slate-600 text-[13px]" title="{{ $doc->title }}">{{ $doc->title }}</div></td>
                        <td>
                            <span class="text-[11px] font-semibold px-1.5 py-0.5 rounded {{ $doc->document_type === 'incoming' ? 'bg-blue-50 text-blue-600' : 'bg-emerald-50 text-emerald-600' }}">
                                {{ $doc->document_type_label }}
                            </span>
                        </td>
                        <td class="text-slate-500 text-[13px] whitespace-nowrap">
                            <div>{{ $doc->date_issued->format('M d, Y') }}</div>
                            <div class="text-[11px] text-slate-400">{{ $doc->created_at->format('h:i A') }}</div>
                        </td>
                        <td class="text-right">
                            <a href="{{ route('documents.show', $doc) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-violet-600 hover:text-violet-800 transition-colors">
                                View <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center">
                            <p class="text-sm font-semibold text-slate-700 mb-1">No documents yet</p>
                            <a href="{{ route('documents.create') }}" class="text-violet-600 text-sm font-semibold hover:underline">Upload your first one →</a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Document Type Overview --}}
    <div class="card" data-tour="type-distribution">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Document Types</h2>
                <p class="text-xs text-slate-400 mt-0.5">System-wide breakdown</p>
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
                <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full transition-all duration-500" style="width: {{ $pct }}%; background-color: {{ $cfg['color'] }};"></div>
                </div>
                <span class="w-8 text-xs font-bold text-right text-slate-700">{{ $count }}</span>
            </div>
            @endforeach
        </div>
    </div>

</div>

{{-- My Activity + Sparkline --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 card" data-tour="activity-feed">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-bold text-slate-800">My Recent Activity</h2>
                <p class="text-xs text-slate-400 mt-0.5">Your latest actions in the system</p>
            </div>
        </div>
        <div class="divide-y divide-slate-50 overflow-y-auto" style="max-height: 380px;">
            @forelse($myRecentLogs as $log)
            <div class="flex items-start gap-3 px-5 py-3 hover:bg-slate-50/70 transition-colors">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full shrink-0 mt-0.5 action-{{ $log->action }}">
                    @if($log->action === 'created')
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    @elseif($log->action === 'updated')
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" /></svg>
                    @elseif($log->action === 'deleted')
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z" /></svg>
                    @elseif($log->action === 'downloaded')
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    @else
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    @endif
                </span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-baseline justify-between gap-2">
                        <p class="text-[13px] font-semibold text-slate-800">You</p>
                        <p class="text-[11px] text-slate-400 shrink-0">{{ $log->created_at->diffForHumans() }}</p>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        <span class="action-badge-{{ $log->action }}">{{ $log->action_label }}</span>
                        @if($log->document)
                            @if($log->document->trashed())
                                <a href="{{ route('documents.archive') }}" class="ml-1 font-semibold text-violet-600 hover:text-violet-800 transition-colors">{{ $log->document->reference_number }}</a>
                            @else
                                <a href="{{ route('documents.show', $log->document) }}" class="ml-1 font-semibold text-violet-600 hover:text-violet-800 transition-colors">{{ $log->document->reference_number }}</a>
                            @endif
                        @else
                            <span class="ml-1 text-slate-400 italic">Deleted document</span>
                        @endif
                    </p>
                </div>
            </div>
            @empty
            <div class="py-12 text-center text-slate-400 text-sm">No activity recorded yet.</div>
            @endforelse
        </div>
    </div>

    <div class="card" data-tour="upload-sparkline">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-bold text-slate-800">My Uploads — Last 7 Days</h2>
                <p class="text-xs text-slate-400 mt-0.5">Your daily upload count</p>
            </div>
        </div>
        <div class="p-6">
            @php $maxCount = max($last7Days->pluck('count')->toArray() ?: [1]); @endphp
            <div class="flex items-end justify-between gap-2 h-24">
                @foreach($last7Days as $day)
                @php $barHeight = $maxCount > 0 ? max(4, round(($day['count'] / $maxCount) * 100)) : 4; @endphp
                <div class="flex flex-col items-center gap-1.5 flex-1">
                    <span class="text-[11px] font-bold text-slate-600">{{ $day['count'] > 0 ? $day['count'] : '' }}</span>
                    <div class="w-full rounded-t-lg transition-all duration-500 {{ $day['count'] > 0 ? 'bg-violet-500' : 'bg-slate-100' }}" style="height: {{ $barHeight }}%"></div>
                    <span class="text-[10px] text-slate-400 font-medium">{{ $day['label'] }}</span>
                </div>
                @endforeach
            </div>
            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                <span class="text-xs text-slate-500">Your uploads this week</span>
                <span class="text-sm font-bold text-slate-900">{{ $last7Days->sum('count') }}</span>
            </div>
        </div>
        <div class="px-6 pb-6 pt-2 border-t border-slate-100 mt-2">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Quick Actions</p>
            <div class="space-y-2">
                <a href="{{ route('documents.create') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-violet-50 text-slate-700 hover:text-violet-700 transition-colors group">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                    <span class="text-sm font-medium">Upload New Document</span>
                </a>
                <a href="{{ route('documents.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-violet-50 text-slate-700 hover:text-violet-700 transition-colors group">
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                    <span class="text-sm font-medium">Browse All Documents</span>
                </a>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script>
(function () {
    // Clock
    const clockEl = document.getElementById('dashboard-clock');
    const dateEl  = document.getElementById('dashboard-date');
    if (clockEl && dateEl) {
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
    }

    // Typewriter for welcome name
    const nameEl   = document.getElementById('welcome-name-text');
    const cursorEl = document.getElementById('welcome-cursor');
    if (nameEl && cursorEl) {
        const fullName = @json(auth()->user()->name);
        let i = 0;
        const speed = 60; // ms per character
        function typeChar() {
            if (i < fullName.length) {
                nameEl.textContent += fullName.charAt(i);
                i++;
                setTimeout(typeChar, speed);
            } else {
                // Remove Tailwind pulse class so inline transition works, then fade out
                setTimeout(() => {
                    cursorEl.classList.remove('animate-pulse');
                    cursorEl.style.transition = 'opacity 0.5s';
                    cursorEl.style.opacity = '0';
                }, 800);
            }
        }
        setTimeout(typeChar, 300);
    }
})();
</script>
@endpush
