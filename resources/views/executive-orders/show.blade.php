@extends('layouts.app')

@section('title', $eo->eo_number)
@section('page-title', $eo->eo_number)

@section('breadcrumb')
    <a href="{{ route('executive-orders.index') }}" class="hover:text-violet-600 transition-colors">Executive Orders</a>
    <span class="mx-1 opacity-40">/</span>
    <span class="text-slate-700 font-semibold">{{ $eo->eo_number }}</span>
@endsection

@section('header-actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('executive-orders.edit', $eo) }}" class="btn-secondary btn-sm">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
            Edit
        </a>
        @if(auth()->user()->isAdmin())
        <form action="{{ route('executive-orders.destroy', $eo) }}" method="POST"
              onsubmit="return confirm('Delete this Executive Order? This cannot be undone.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger btn-sm">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                Delete
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

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- PDF Viewer --}}
    <div class="xl:col-span-2" style="height: calc(100vh - 140px);">
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
                <a href="{{ route('executive-orders.download', $eo) }}" class="btn-primary btn-sm">
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
    <div class="space-y-5 overflow-y-auto" style="max-height: calc(100vh - 140px);">

        {{-- Status & Title --}}
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

        {{-- Details grid --}}
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
                        <span class="text-xs text-slate-400 font-medium">Date Effective</span>
                        <span class="text-sm font-semibold text-slate-800 text-right">{{ $eo->date_effective->format('F d, Y') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-400 font-medium">Signed By</span>
                        <span class="text-sm font-semibold text-slate-800 text-right max-w-[60%]">{{ $eo->signed_by }}</span>
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
            </div>
        </div>

        {{-- Content summary --}}
        @if($eo->content_summary)
        <div class="card">
            <div class="p-6">
                <h3 class="form-section-title">Content Summary</h3>
                <p class="text-sm text-slate-600 leading-relaxed">{{ $eo->content_summary }}</p>
            </div>
        </div>
        @endif

        {{-- Tags --}}
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

        {{-- Amendment chain --}}
        @if(($eo->amends_id && $eo->amends) || ($eo->amended_by_id && $eo->amendedBy))
        <div class="card">
            <div class="p-6">
                <h3 class="form-section-title">Amendment Chain</h3>
                @if($eo->amends_id && $eo->amends)
                <div class="mb-3">
                    <p class="text-xs text-slate-400 mb-1.5">This EO amends:</p>
                    <a href="{{ route('executive-orders.show', $eo->amends) }}"
                       class="flex items-center gap-3 p-3 rounded-xl bg-blue-50 hover:bg-blue-100 border border-blue-100 transition-colors group">
                        <div class="w-7 h-7 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center shrink-0 group-hover:bg-blue-200">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15m0 0l6.75 6.75M4.5 12l6.75-6.75" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-blue-900 truncate">{{ $eo->amends->eo_number }}</p>
                            <p class="text-xs text-blue-600 truncate">{{ $eo->amends->title }}</p>
                        </div>
                    </a>
                </div>
                @endif
                @if($eo->amended_by_id && $eo->amendedBy)
                <div>
                    <p class="text-xs text-slate-400 mb-1.5">Amended by:</p>
                    <a href="{{ route('executive-orders.show', $eo->amendedBy) }}"
                       class="flex items-center gap-3 p-3 rounded-xl bg-amber-50 hover:bg-amber-100 border border-amber-100 transition-colors group">
                        <div class="w-7 h-7 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-amber-900 truncate">{{ $eo->amendedBy->eo_number }}</p>
                            <p class="text-xs text-amber-600 truncate">{{ $eo->amendedBy->title }}</p>
                        </div>
                    </a>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Activity log --}}
        @if($eo->activityLogs->count())
        <div class="card">
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
