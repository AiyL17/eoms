@extends('public.layout')

@section('title', $eo->eo_number . ' · ' . Str::limit($eo->title, 50) . ' — Public Portal')

@section('content')

{{-- ═══════════════════════════════════════════════════ Breadcrumb --}}
<nav aria-label="Breadcrumb" class="flex items-center gap-2 text-sm text-slate-400 mb-5">
    <a href="{{ route('public.index') }}" class="hover:text-violet-600 font-medium transition-colors flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
        Registry
    </a>
    <svg class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
    <span class="text-slate-600 font-semibold" aria-current="page">{{ $eo->eo_number }}</span>
</nav>

{{-- ══════════════════════════════════════════════════ Alert banners --}}

{{-- #7: under_review — internal status that shouldn't normally appear publicly --}}
@if($eo->status === 'under_review')
<div class="alert-info mb-5 rounded-xl" role="status">
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
    <div>
        <p class="font-semibold text-sm">This Executive Order is currently under review.</p>
        <p class="text-sm mt-0.5">It has not yet been formally enacted. Details may change before it takes effect.</p>
    </div>
</div>
@endif

@if($eo->status === 'amended' && $eo->amendedBy)
<div class="alert-warning mb-5 rounded-xl" role="status">
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
    <div>
        <p class="font-semibold text-sm">This Executive Order has been amended.</p>
        <p class="text-sm mt-0.5">It has been superseded by <a href="{{ route('public.show', $eo->amendedBy) }}" class="underline font-semibold hover:text-amber-900 transition-colors">{{ $eo->amendedBy->eo_number }}</a>. Please refer to the newer order for the current version.</p>
    </div>
</div>
@endif

{{-- #6: superseded banner --}}
@if($eo->status === 'superseded')
<div class="alert-warning mb-5 rounded-xl" role="status">
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
    <div>
        <p class="font-semibold text-sm">This Executive Order has been superseded and is no longer the current version.</p>
        @if($eo->status_notes)<p class="text-sm mt-0.5">{{ $eo->status_notes }}</p>@endif
    </div>
</div>
@endif

@if($eo->status === 'repealed')
<div class="alert-error mb-5 rounded-xl" role="status">
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
    <div>
        <p class="font-semibold text-sm">This Executive Order has been repealed and is no longer in effect.</p>
        @if($eo->status_notes)<p class="text-sm mt-0.5">{{ $eo->status_notes }}</p>@endif
    </div>
</div>
@endif

@if($eo->status === 'suspended')
<div class="flex items-start gap-3 p-4 rounded-xl border mb-5 bg-orange-50 border-orange-100 text-orange-800" role="status">
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9v6m-4.5 0V9M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
    <div>
        <p class="font-semibold text-sm">This Executive Order is currently suspended.</p>
        @if($eo->status_notes)<p class="text-sm mt-0.5">{{ $eo->status_notes }}</p>@endif
    </div>
</div>
@endif

{{-- ══════════════════════════════════════ Main two-column layout --}}

{{-- Back button: mobile — sits above the content so it's immediately accessible --}}
<div class="block md:hidden mb-4">
    <a href="{{ route('public.index') }}" class="btn-secondary btn-sm gap-1.5 text-slate-500">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
        Back to Registry
    </a>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── PDF Viewer ─────────────────────────────────────────────── --}}
    <div class="xl:col-span-2">

        {{-- #2: On small screens skip the iframe entirely — show a download CTA instead --}}
        <div class="block md:hidden card p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-50 text-red-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-slate-800">{{ $eo->original_filename }}</p>
                    <p class="text-xs text-slate-400">PDF Document · {{ $eo->file_size_formatted }}</p>
                </div>
            </div>
            <p class="text-sm text-slate-500 mb-4 leading-relaxed">
                PDF preview is not available on small screens. Open or download the document below.
            </p>
            <div class="flex gap-3">
                <a href="{{ route('public.pdf', $eo) }}" target="_blank" rel="noopener" class="btn-secondary flex-1 gap-2 justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                    Open PDF
                </a>
                <a href="{{ route('public.download', $eo) }}" class="btn-primary flex-1 gap-2 justify-center">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Download
                </a>
            </div>
        </div>

        {{-- Desktop iframe viewer --}}
        {{--
            Deliberately NOT using the .card class here — .card has overflow-hidden which
            clips the iframe. We replicate card's visual styles via inline style + Tailwind
            utilities, then manage overflow explicitly at each layer.
        --}}
        <div class="hidden md:flex flex-col bg-white border border-slate-100 shadow-sm rounded-2xl"
             style="height: clamp(520px, calc(100vh - 180px), 900px); overflow: hidden;">

            {{-- Toolbar — fixed height, never shrinks --}}
            <div class="shrink-0 flex items-center justify-between gap-3 px-5 py-3.5 bg-white border-b border-slate-100">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-red-50 text-red-500 flex items-center justify-center shrink-0" aria-hidden="true">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $eo->original_filename }}</p>
                        <p class="text-xs text-slate-400">PDF Document · {{ $eo->file_size_formatted }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('public.pdf', $eo) }}" target="_blank" rel="noopener"
                       class="btn-secondary btn-sm gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                        Open
                    </a>
                    <a href="{{ route('public.download', $eo) }}" class="btn-primary btn-sm gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        Download
                    </a>
                </div>
            </div>

            {{-- iframe area — flex-1 + min-h-0 lets it fill all remaining space without overflowing --}}
            <div class="flex-1 min-h-0" style="background: #f1f5f9;">
                <iframe src="{{ route('public.pdf', $eo) }}"
                        style="display: block; width: 100%; height: 100%; border: none;"
                        title="PDF document: {{ $eo->title }}"
                        aria-label="PDF preview of {{ $eo->eo_number }}: {{ $eo->title }}">
                    {{-- Fallback for browsers that do not support inline PDF --}}
                    <div style="padding: 2rem; text-align: center;">
                        <p style="font-size: 0.875rem; color: #475569; margin-bottom: 0.75rem;">
                            Your browser does not support inline PDF viewing.
                        </p>
                        <a href="{{ route('public.download', $eo) }}" class="btn-primary gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            Download PDF
                        </a>
                    </div>
                </iframe>
            </div>

        </div>
    </div>

    {{-- ── Metadata Sidebar ────────────────────────────────────────── --}}
    {{-- eo-sidebar: no constraints on mobile, independent scroll on xl desktop --}}
    <div class="eo-sidebar space-y-4">
        {{-- Title & status card --}}
        <div class="card">
            <div class="p-5">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <span class="badge-{{ $eo->status }} shrink-0">{{ $eo->status_label }}</span>
                    <span class="eo-number-pill text-xs font-bold text-violet-700 bg-violet-50 border border-violet-100 px-2.5 py-1 rounded-lg shrink-0">{{ $eo->eo_number }}</span>
                </div>
                <h1 class="text-base font-bold text-slate-900 leading-snug mb-2">{{ $eo->title }}</h1>
                @if($eo->subject)
                    <p class="text-sm text-slate-500 leading-relaxed">{{ $eo->subject }}</p>
                @endif
            </div>
        </div>

        {{-- #13: Proper <dl> structure with consistent dt/dd pairing --}}
        <div class="card">
            <div class="p-5">
                <h2 class="form-section-title">Details</h2>
                <dl class="space-y-3.5">

                    <div class="flex justify-between items-start gap-3">
                        <dt class="flex items-center gap-1.5 text-xs text-slate-400 font-medium shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5" /></svg>
                            Date Issued
                        </dt>
                        <dd class="text-sm font-semibold text-slate-800 text-right">
                            <time datetime="{{ $eo->date_issued->toDateString() }}">{{ $eo->date_issued->format('F d, Y') }}</time>
                        </dd>
                    </div>

                    @if($eo->date_effective)
                    <div class="flex justify-between items-start gap-3">
                        <dt class="flex items-center gap-1.5 text-xs text-slate-400 font-medium shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Effective Date
                        </dt>
                        <dd class="text-sm font-semibold text-slate-800 text-right">
                            <time datetime="{{ $eo->date_effective->toDateString() }}">{{ $eo->date_effective->format('F d, Y') }}</time>
                        </dd>
                    </div>
                    @endif

                    <div class="flex justify-between items-start gap-3">
                        <dt class="flex items-center gap-1.5 text-xs text-slate-400 font-medium shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                            Signed By
                        </dt>
                        {{-- Matches the internal EO view exactly:
                             signature image is absolutely positioned over the name --}}
                        <dd class="relative pt-6 max-w-[60%]">
                            <span class="text-sm font-semibold text-slate-800 pt-0.5 block text-right">{{ $eo->signed_by }}</span>
                            @if($eo->signature_data)
                                <img src="{{ $eo->signature_data }}"
                                     alt="E-Signature of {{ $eo->signed_by }}"
                                     class="absolute right-0 w-full h-6 object-contain object-right pointer-events-none select-none"
                                     style="bottom: 4px;">
                            @endif
                        </dd>
                    </div>

                    <div class="flex justify-between items-start gap-3">
                        <dt class="flex items-center gap-1.5 text-xs text-slate-400 font-medium shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                            Document
                        </dt>
                        <dd class="text-sm text-slate-600 text-right">{{ $eo->file_size_formatted }}</dd>
                    </div>

                    {{-- #15: last updated timestamp --}}
                    <div class="flex justify-between items-start gap-3">
                        <dt class="flex items-center gap-1.5 text-xs text-slate-400 font-medium shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                            Last Updated
                        </dt>
                        <dd class="text-sm text-slate-600 text-right">
                            <time datetime="{{ $eo->updated_at->toIso8601String() }}"
                                  title="{{ $eo->updated_at->format('F d, Y g:i A') }}">
                                {{ $eo->updated_at->diffForHumans() }}
                            </time>
                        </dd>
                    </div>

                    @if($eo->status_notes)
                    <div class="pt-3.5 border-t border-slate-100">
                        <dt class="text-xs text-slate-400 font-medium mb-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" /></svg>
                            Status Notes
                        </dt>
                        <dd class="text-sm text-slate-600 leading-relaxed">{{ $eo->status_notes }}</dd>
                    </div>
                    @endif

                </dl>
            </div>
        </div>

        {{-- Summary --}}
        @if($eo->content_summary)
        <div class="card">
            <div class="p-5">
                <h2 class="form-section-title">Summary</h2>
                <p class="text-sm text-slate-600 leading-relaxed">{{ $eo->content_summary }}</p>
            </div>
        </div>
        @endif

        {{-- Tags --}}
        @if($eo->tags && count($eo->tags) > 0)
        <div class="card">
            <div class="p-5">
                <h2 class="form-section-title">Tags</h2>
                <div class="flex flex-wrap gap-2" role="list" aria-label="Tags">
                    @foreach($eo->tags as $tag)
                        <a href="{{ route('public.index', ['tag' => $tag]) }}"
                           role="listitem"
                           class="px-3 py-1.5 bg-violet-50 text-violet-700 text-xs rounded-full font-semibold border border-violet-100 hover:bg-violet-100 hover:border-violet-200 transition-colors focus:outline-none focus:ring-2 focus:ring-violet-400">
                            # {{ $tag }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Amendment chain --}}
        @if(($eo->amends_id && $eo->amends) || ($eo->amended_by_id && $eo->amendedBy))
        <div class="card">
            <div class="p-5">
                <h2 class="form-section-title">Amendment Chain</h2>
                <div class="space-y-3">
                    @if($eo->amends_id && $eo->amends)
                    <div>
                        <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wider mb-1.5">Amends:</p>
                        <a href="{{ route('public.show', $eo->amends) }}"
                           class="flex items-center gap-3 p-3 rounded-xl bg-blue-50 border border-blue-100 hover:bg-blue-100 hover:border-blue-200 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center shrink-0" aria-hidden="true">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15m0 0l6.75 6.75M4.5 12l6.75-6.75" /></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-blue-900">{{ $eo->amends->eo_number }}</p>
                                <p class="text-xs text-blue-600 line-clamp-1">{{ Str::limit($eo->amends->title, 40) }}</p>
                            </div>
                        </a>
                    </div>
                    @endif
                    @if($eo->amended_by_id && $eo->amendedBy)
                    <div>
                        <p class="text-[11px] text-slate-400 font-semibold uppercase tracking-wider mb-1.5">Amended by:</p>
                        <a href="{{ route('public.show', $eo->amendedBy) }}"
                           class="flex items-center gap-3 p-3 rounded-xl bg-amber-50 border border-amber-100 hover:bg-amber-100 hover:border-amber-200 transition-colors focus:outline-none focus:ring-2 focus:ring-amber-400">
                            <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0" aria-hidden="true">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" /></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-amber-900">{{ $eo->amendedBy->eo_number }}</p>
                                <p class="text-xs text-amber-600 line-clamp-1">{{ Str::limit($eo->amendedBy->title, 40) }}</p>
                            </div>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Download CTA — hidden on mobile (covered by the top mobile card above) --}}
        <div class="hidden md:block rounded-2xl border border-violet-100 p-5"
             style="background: linear-gradient(135deg, #faf5ff 0%, #ede9fe 100%);">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-8 h-8 rounded-xl bg-violet-600 text-white flex items-center justify-center shrink-0" aria-hidden="true">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                </div>
                <p class="text-sm font-semibold text-violet-900">Download Document</p>
            </div>
            <p class="text-xs text-violet-700/80 mb-3 leading-relaxed">Download the official PDF document of this executive order for offline reference.</p>
            <a href="{{ route('public.download', $eo) }}"
               class="w-full btn-primary gap-2 justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                Download PDF
            </a>
        </div>

        {{-- #16: Copy link / share --}}
        <div class="card">
            <div class="p-5">
                <h2 class="form-section-title">Share this Record</h2>
                <div class="flex items-center gap-2">
                    <input id="share-url"
                           type="text"
                           readonly
                           value="{{ url()->current() }}"
                           aria-label="Direct link to this executive order"
                           class="form-input text-xs text-slate-500 bg-slate-50 cursor-text select-all flex-1">
                    <button id="copy-link-btn"
                            type="button"
                            aria-label="Copy link to clipboard"
                            onclick="
                                navigator.clipboard.writeText(document.getElementById('share-url').value).then(function() {
                                    var btn = document.getElementById('copy-link-btn');
                                    var icon = document.getElementById('copy-icon');
                                    var label = document.getElementById('copy-label');
                                    icon.innerHTML = '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M4.5 12.75l6 6 9-13.5\' />';
                                    label.textContent = 'Copied!';
                                    btn.classList.add('text-emerald-600', 'border-emerald-200', 'bg-emerald-50');
                                    btn.classList.remove('text-slate-500');
                                    setTimeout(function() {
                                        icon.innerHTML = '<path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184\' />';
                                        label.textContent = 'Copy link';
                                        btn.classList.remove('text-emerald-600', 'border-emerald-200', 'bg-emerald-50');
                                        btn.classList.add('text-slate-500');
                                    }, 2000);
                                });
                            "
                            class="btn-secondary btn-sm gap-1.5 shrink-0 text-slate-500 transition-colors">
                        <svg id="copy-icon" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                        </svg>
                        <span id="copy-label">Copy link</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Official notice — hidden on mobile to keep the single-column flow clean --}}
        <div class="hidden md:flex rounded-xl border border-slate-100 bg-slate-50 p-4 items-start gap-3">
            <svg class="w-4 h-4 text-slate-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" /></svg>
            <p class="text-[11px] text-slate-400 leading-relaxed">
                Official public record · City Government Executive Order Management System. All documents are verified and authentic.
            </p>
        </div>

    </div>{{-- /sidebar --}}
</div>

{{-- Back navigation — desktop only (mobile version sits above the grid) --}}
<div class="hidden md:block mt-6">
    <a href="{{ route('public.index') }}" class="btn-secondary btn-sm gap-1.5 text-slate-500">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
        Back to Registry
    </a>
</div>

@endsection
