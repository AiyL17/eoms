@extends('layouts.app')

@section('title', $eo->eo_number)
@section('page-title', $eo->eo_number)

@section('breadcrumb')
    <a href="{{ route('executive-orders.index') }}" class="hover:text-violet-600 transition-colors">Executive Orders</a>
    <span class="mx-1 opacity-40">/</span>
    <span class="text-slate-700 font-semibold">{{ $eo->eo_number }}</span>
@endsection

@section('header-actions')
    <div class="flex items-center gap-2" id="tour-header-btn">
        <a href="{{ route('executive-orders.export-single', $eo) }}" class="btn-secondary btn-sm" title="Export EO details as CSV">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            Export
        </a>
        <a href="{{ route('executive-orders.version-history', $eo) }}" class="btn-secondary btn-sm" title="Version History">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            History
        </a>
        <a href="{{ route('executive-orders.edit', $eo) }}" class="btn-secondary btn-sm">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
            Edit
        </a>
        @if(auth()->user()->isAdmin())
        <form action="{{ route('executive-orders.destroy', $eo) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-archive btn-sm"
                    data-confirm="Archive this Executive Order? It can be restored from the Archive."
                    data-confirm-title="Confirm Archive"
                    data-confirm-subtitle="The EO will be moved to the archive and can be restored later."
                    data-confirm-action="Archive">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                Archive
            </button>
        </form>
        @endif
    </div>
@endsection

@section('content')

{{-- Amendment Warning --}}
@if($eo->status === 'amended' && $eo->amendedBy)
<div class="alert-warning mb-5">
    <svg class="w-4.5 h-4.5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
    <div>
        <p class="font-semibold text-sm">This Executive Order has been amended.</p>
        <p class="text-sm mt-0.5">Superseded by <a href="{{ route('executive-orders.show', $eo->amendedBy) }}" class="underline font-semibold hover:text-amber-900">{{ $eo->amendedBy->eo_number }}</a>.</p>
    </div>
</div>
@endif

{{-- Review Anniversary Notice --}}
@php
    $yearsOld = $eo->date_issued ? (int) $eo->date_issued->diffInYears(now()) : 0;
    $isReviewDue = $eo->status === 'active' && $yearsOld >= 1;
@endphp
@if($isReviewDue)
<div class="alert-info mb-5">
    <svg class="w-4.5 h-4.5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    <div>
        <p class="font-semibold text-sm">Periodic Review Reminder</p>
        <p class="text-sm mt-0.5">This EO was issued {{ $yearsOld }} {{ Str::plural('year', $yearsOld) }} ago ({{ $eo->date_issued->format('F d, Y') }}) and is still active. Consider reviewing it for continued relevance.</p>
    </div>
</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- PDF Viewer --}}
    <div class="xl:col-span-2 min-h-[400px]" id="tour-eo-pdf" style="height: clamp(400px, calc(100vh - 140px), 900px);">
        <div class="card h-full flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-red-50 text-red-500 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-slate-800">{{ $eo->original_filename }}</p>
                        <p class="text-xs text-slate-400">{{ $eo->file_size_formatted }}</p>
                    </div>
                </div>
                <a href="{{ route('executive-orders.download', $eo) }}" id="tour-eo-download" class="btn-primary btn-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Download
                </a>
            </div>
            <div class="flex-1 bg-slate-50 overflow-hidden">
                <iframe src="{{ route('executive-orders.pdf', $eo) }}" class="w-full h-full border-0"></iframe>
            </div>
        </div>
    </div>

    {{-- Metadata Panel --}}
    <div class="space-y-5 xl:overflow-y-auto" style="max-height: clamp(400px, calc(100vh - 140px), 900px);">

        {{-- Unified EO Info Card --}}
        <div class="card" id="tour-eo-meta">
            <div class="p-6">

                {{-- Status & Identity --}}
                <div class="flex items-center justify-between mb-4">
                    <span class="badge-{{ $eo->status }}">{{ $eo->status_label }}</span>
                    <span class="text-xs text-slate-400 font-medium">{{ $eo->eo_number }}</span>
                </div>
                <h1 class="text-base font-bold text-slate-900 leading-snug mb-2">{{ $eo->title }}</h1>
                <p class="text-sm text-slate-500 leading-relaxed">{{ $eo->subject }}</p>

                {{-- Details --}}
                <div class="mt-5 pt-5 border-t border-slate-100 space-y-4">
                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-400 font-medium">Date Issued</span>
                        <span class="text-sm font-semibold text-slate-800 text-right">{{ $eo->date_issued->format('F d, Y') }}</span>
                    </div>
                    @if($eo->date_effective)
                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-400 font-medium">Date Effective</span>
                        <span class="text-sm font-semibold text-slate-800 text-right">{{ $eo->date_effective->format('F d, Y') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-400 font-medium">Signed By</span>
                        <div class="relative pt-6 max-w-[60%]">
                            <span class="text-sm font-semibold text-slate-800 pt-0.5 block text-right">{{ $eo->signed_by }}</span>
                            @if($eo->signature_data)
                            <img src="{{ $eo->signature_data }}"
                                 alt="E-Signature of {{ $eo->signed_by }}"
                                 class="absolute right-0 w-full h-6 object-contain object-right pointer-events-none"
                                 style="bottom: 4px;">
                            @endif
                        </div>
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-400 font-medium">Uploaded By</span>
                        <span class="text-sm font-semibold text-slate-800 text-right">{{ $eo->uploader->name ?? 'System' }}</span>
                    </div>
                    @if($eo->updater)
                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-400 font-medium">Last Edited By</span>
                        <span class="text-sm font-semibold text-slate-800 text-right">{{ $eo->updater->name }}</span>
                    </div>
                    @endif
                    @if($eo->status_notes)
                    <div class="pt-3 border-t border-slate-100">
                        <p class="text-xs text-slate-400 font-medium mb-1.5">Status Notes</p>
                        <p class="text-sm text-slate-600">{{ $eo->status_notes }}</p>
                    </div>
                    @endif
                </div>

                {{-- Content Summary --}}
                @if($eo->content_summary)
                <div class="mt-5 pt-5 border-t border-slate-100">
                    <p class="text-xs text-slate-400 font-medium mb-1.5">Content Summary</p>
                    <p class="text-sm text-slate-600 leading-relaxed">{{ $eo->content_summary }}</p>
                </div>
                @endif

                {{-- Tags --}}
                @if($eo->tags)
                <div class="mt-5 pt-5 border-t border-slate-100">
                    <p class="text-xs text-slate-400 font-medium mb-2">Tags</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($eo->tags as $tag)
                            <span class="px-3 py-1 bg-violet-50 text-violet-700 text-xs rounded-full font-medium border border-violet-100">{{ $tag }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

            </div>
        </div>

        {{-- Amendment Chain Visualizer --}}
        @php
            $hasChain = count($chainTree) > 0 && ($eo->amends_id || $eo->amended_by_id);
        @endphp
        @if($hasChain)
        <div class="card" id="tour-eo-amendment-chain">
            <div class="p-6">
                <div class="flex items-center justify-between mb-1">
                    <h3 class="form-section-title mb-0 pb-0 border-0">Amendment Chain</h3>
                    <span class="text-[11px] font-semibold text-slate-400 bg-slate-50 px-2 py-0.5 rounded-full border border-slate-100">{{ count($chainTree) }} {{ Str::plural('order', count($chainTree)) }}</span>
                </div>
                <p class="text-xs text-slate-400 mb-4">Full lineage from the original order to the latest amendment.</p>

                {{-- Summary breadcrumb: "EO 5-24 → amended by EO 3-25 → superseded by EO 1-26" --}}
                @if(count($chainTree) > 1)
                <div class="flex flex-wrap items-center gap-1 mb-4 p-2.5 bg-slate-50 rounded-xl border border-slate-100">
                    @foreach($chainTree as $i => $node)
                        @if($i > 0)
                        @php
                            $prevNode = $chainTree[$i - 1];
                            $verb = match($prevNode['status']) {
                                'amended'    => 'amended by',
                                'superseded' => 'superseded by',
                                'repealed'   => 'replaced by',
                                default      => 'followed by',
                            };
                        @endphp
                        <span class="text-[10px] text-slate-400 font-medium italic px-0.5">{{ $verb }}</span>
                        @endif
                        <span class="text-[10px] font-bold {{ $node['is_current'] ? 'text-violet-700 bg-violet-100 px-1.5 py-0.5 rounded-md' : 'text-slate-600' }} font-mono whitespace-nowrap">{{ $node['eo_number'] }}</span>
                    @endforeach
                </div>
                @endif

                <div class="relative">
                    {{-- Vertical connector line --}}
                    @if(count($chainTree) > 1)
                    <div class="absolute left-[13px] top-5 bottom-5 w-px bg-slate-200 z-0"></div>
                    @endif
                    <div class="space-y-1.5 relative z-10">
                        @foreach($chainTree as $i => $node)
                        @php
                            $isFirst   = $i === 0;
                            $isLast    = $i === count($chainTree) - 1;
                            $isCurrent = $node['is_current'];
                            $dotColor  = match($node['status']) {
                                'active'       => 'bg-emerald-500',
                                'amended'      => 'bg-amber-400',
                                'repealed'     => 'bg-red-400',
                                'suspended'    => 'bg-orange-400',
                                'superseded'   => 'bg-violet-400',
                                'under_review' => 'bg-sky-400',
                                default        => 'bg-slate-300',
                            };
                            $cardBg = $isCurrent
                                ? 'bg-violet-50 border-violet-200 ring-1 ring-violet-200'
                                : 'bg-white border-slate-100 hover:bg-slate-50';
                        @endphp

                        {{-- Connector label between nodes --}}
                        @if(! $isFirst)
                        @php
                            $prevNode = $chainTree[$i - 1];
                            $connLabel = match($prevNode['status']) {
                                'amended'    => 'amended by',
                                'superseded' => 'superseded by',
                                'repealed'   => 'replaced by',
                                default      => 'followed by',
                            };
                        @endphp
                        <div class="flex items-center gap-3 py-0.5">
                            <div class="shrink-0 w-7 flex justify-center">
                                <svg class="w-3 h-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5 12 21m0 0-7.5-7.5M12 21V3" />
                                </svg>
                            </div>
                            <span class="text-[10px] text-slate-400 font-medium italic">{{ $connLabel }}</span>
                        </div>
                        @endif

                        <div class="flex items-start gap-3">
                            {{-- Timeline dot --}}
                            <div class="shrink-0 w-7 flex flex-col items-center pt-2">
                                <span class="w-3.5 h-3.5 rounded-full border-2 border-white shadow-sm {{ $dotColor }} {{ $isCurrent ? 'ring-2 ring-violet-400' : '' }}"></span>
                            </div>
                            {{-- Node card --}}
                            <a href="{{ $node['url'] }}"
                               class="flex-1 flex items-start justify-between gap-2 p-3 rounded-xl border transition-all {{ $cardBg }} {{ $node['is_trashed'] ? 'opacity-60' : '' }}">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                        <span class="text-xs font-bold {{ $isCurrent ? 'text-violet-800' : 'text-slate-800' }} font-mono">{{ $node['eo_number'] }}</span>
                                        <span class="badge-{{ $node['status'] }} !py-0 !px-2 !text-[10px]">{{ $node['status_label'] }}</span>
                                        @if($isCurrent)
                                        <span class="text-[10px] font-bold bg-violet-600 text-white px-1.5 py-0.5 rounded-full leading-none">Viewing</span>
                                        @endif
                                        @if($isFirst && count($chainTree) > 1)
                                        <span class="text-[10px] font-semibold bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded-full leading-none">Original</span>
                                        @endif
                                        @if($isLast && count($chainTree) > 1)
                                        <span class="text-[10px] font-semibold bg-emerald-100 text-emerald-700 px-1.5 py-0.5 rounded-full leading-none">Latest</span>
                                        @endif
                                        @if($node['is_trashed'])
                                        <span class="text-[10px] font-bold bg-slate-200 text-slate-500 px-1.5 py-0.5 rounded-full leading-none">Archived</span>
                                        @endif
                                    </div>
                                    <p class="text-xs {{ $isCurrent ? 'text-violet-700' : 'text-slate-500' }} truncate max-w-[180px] mb-0.5">{{ $node['title'] }}</p>
                                    <div class="flex items-center gap-2 text-[10px] text-slate-400">
                                        <span>{{ $node['date_issued'] }}</span>
                                        @if(! empty($node['signed_by']))
                                        <span class="opacity-50">·</span>
                                        <span class="truncate max-w-[120px]">{{ $node['signed_by'] }}</span>
                                        @endif
                                    </div>
                                </div>
                                @if(! $node['is_trashed'])
                                <svg class="w-3.5 h-3.5 text-slate-300 shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                                @endif
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Activity log --}}
        @if($eo->activityLogs->count())
        <div class="card" id="tour-eo-activity-log">
            <div class="p-6">
                <h3 class="form-section-title">Activity Log</h3>
                <div class="space-y-3 overflow-y-auto" style="max-height: 280px;">
                    @foreach($eo->activityLogs as $log)
                    <div class="flex items-start gap-3">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full shrink-0 action-{{ $log->action }} text-[10px] font-bold">
                            {{ strtoupper(substr($log->action, 0, 1)) }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-slate-700">{{ $log->action_label }} <span class="font-normal text-slate-400">by {{ $log->user->name ?? 'System' }}</span></p>
                            @if($log->notes)
                            <p class="text-xs text-slate-500 mt-0.5 italic">"{{ $log->notes }}"</p>
                            @endif
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ $log->created_at->format('M d, Y · h:i A') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

    </div>
</div>

@endsection
