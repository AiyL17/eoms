@extends('public.layout')

@section('title', $eo->eo_number . ' — Public Portal')

@section('content')

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-slate-400 mb-5">
    <a href="{{ route('public.index') }}" class="hover:text-violet-600 font-medium transition-colors">Registry</a>
    <span class="opacity-40">/</span>
    <span class="text-slate-700 font-semibold">{{ $eo->eo_number }}</span>
</div>

{{-- Amendment warning --}}
@if($eo->status === 'amended' && $eo->amendedBy)
<div class="alert-warning mb-5">
    <svg class="w-4.5 h-4.5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
    <div>
        <p class="font-semibold text-sm">This Executive Order has been amended.</p>
        <p class="text-sm mt-0.5">Superseded by <a href="{{ route('public.show', $eo->amendedBy) }}" class="underline font-semibold hover:text-amber-900">{{ $eo->amendedBy->eo_number }}</a>.</p>
    </div>
</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- PDF Viewer --}}
    <div class="xl:col-span-2" style="height: clamp(400px, calc(100vh - 180px), 900px);">
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
                <a href="{{ route('public.download', $eo) }}" class="btn-primary btn-sm">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Download
                </a>
            </div>
            <div class="flex-1 bg-slate-50 overflow-hidden">
                <iframe src="{{ route('public.pdf', $eo) }}" class="w-full h-full border-0"></iframe>
            </div>
        </div>
    </div>

    {{-- Metadata --}}
    <div class="space-y-5 xl:overflow-y-auto" style="max-height: clamp(400px, calc(100vh - 180px), 900px);">

        <div class="card">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <span class="badge-{{ $eo->status }}">{{ $eo->status_label }}</span>
                    <span class="text-xs text-slate-400 font-medium">{{ $eo->eo_number }}</span>
                </div>
                <h1 class="text-base font-bold text-slate-900 leading-snug mb-3">{{ $eo->title }}</h1>
                <p class="text-sm text-slate-500 leading-relaxed">{{ $eo->subject }}</p>
            </div>
        </div>

        <div class="card">
            <div class="p-6">
                <h3 class="form-section-title">Details</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-400 font-medium">Date Issued</span>
                        <span class="text-sm font-semibold text-slate-800 text-right">{{ $eo->date_issued->format('F d, Y') }}</span>
                    </div>
                    @if($eo->date_effective)
                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-400 font-medium">Effective Date</span>
                        <span class="text-sm font-semibold text-slate-800 text-right">{{ $eo->date_effective->format('F d, Y') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-400 font-medium">Signed By</span>
                        <span class="text-sm font-semibold text-slate-800 text-right">{{ $eo->signed_by }}</span>
                    </div>
                    @if($eo->status_notes)
                    <div class="pt-3 border-t border-slate-100">
                        <p class="text-xs text-slate-400 font-medium mb-1.5">Status Notes</p>
                        <p class="text-sm text-slate-600">{{ $eo->status_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($eo->content_summary)
        <div class="card">
            <div class="p-6">
                <h3 class="form-section-title">Summary</h3>
                <p class="text-sm text-slate-600 leading-relaxed">{{ $eo->content_summary }}</p>
            </div>
        </div>
        @endif

        @if($eo->tags)
        <div class="card">
            <div class="p-6">
                <h3 class="form-section-title">Tags</h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($eo->tags as $tag)
                        <span class="px-3 py-1 bg-violet-50 text-violet-700 text-xs rounded-full font-medium border border-violet-100">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Amendment links --}}
        @if(($eo->amends_id && $eo->amends) || ($eo->amended_by_id && $eo->amendedBy))
        <div class="card">
            <div class="p-6">
                <h3 class="form-section-title">Related Orders</h3>
                @if($eo->amends_id && $eo->amends)
                <div class="mb-3">
                    <p class="text-xs text-slate-400 mb-1.5">Amends:</p>
                    <a href="{{ route('public.show', $eo->amends) }}" class="flex items-center gap-2 p-3 rounded-xl bg-blue-50 border border-blue-100 hover:bg-blue-100 transition-colors">
                        <span class="text-sm font-bold text-blue-900">{{ $eo->amends->eo_number }}</span>
                    </a>
                </div>
                @endif
                @if($eo->amended_by_id && $eo->amendedBy)
                <div>
                    <p class="text-xs text-slate-400 mb-1.5">Amended by:</p>
                    <a href="{{ route('public.show', $eo->amendedBy) }}" class="flex items-center gap-2 p-3 rounded-xl bg-amber-50 border border-amber-100 hover:bg-amber-100 transition-colors">
                        <span class="text-sm font-bold text-amber-900">{{ $eo->amendedBy->eo_number }}</span>
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

        <div class="card">
            <div class="p-6">
                <p class="text-xs text-slate-400 text-center">This is an official public record of the City Government Executive Order Management System.</p>
            </div>
        </div>

    </div>
</div>

@endsection
