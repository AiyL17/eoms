@extends('layouts.app')

@section('title', $doc->title)
@section('page-title', $doc->reference_number)

@section('breadcrumb')
    <a href="{{ route('documents.index') }}" class="hover:text-violet-600 transition-colors shrink-0 whitespace-nowrap">Documents</a>
    <span class="opacity-40 shrink-0">/</span>
    <span class="text-slate-700 font-semibold truncate min-w-0">{{ $doc->title }}</span>
@endsection

@section('header-actions')
    <div class="flex items-center gap-2" id="tour-header-btn">
        <a href="{{ route('documents.export-single', $doc) }}" class="btn-secondary btn-sm" title="Export document details as ZIP (Excel + PDF)">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            <span class="hidden sm:inline">Export</span>
        </a>
        <a href="{{ route('documents.version-history', $doc) }}" class="btn-secondary btn-sm" title="Version History">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span class="hidden sm:inline">History</span>
        </a>
        <a href="{{ route('documents.edit', $doc) }}" class="btn-secondary btn-sm" title="Edit document">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
            <span class="hidden sm:inline">Edit</span>
        </a>
        @if(auth()->user()->isAdmin())
        <form action="{{ route('documents.destroy', $doc) }}" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-archive btn-sm"
                    title="Archive document"
                    data-confirm="Archive this document? It can be restored from the Archive."
                    data-confirm-title="Confirm Archive"
                    data-confirm-subtitle="The document will be moved to the archive and can be restored later."
                    data-confirm-action="Archive"
                    data-confirm-variant="archive">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z" />
                </svg>
                <span class="hidden sm:inline">Archive</span>
            </button>
        </form>
        @endif
    </div>
@endsection

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- PDF Viewer --}}
    <div class="xl:col-span-2 min-h-[400px]" id="tour-doc-pdf" style="height: clamp(400px, calc(100vh - 140px), 900px);">
        <div class="card h-full flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between shrink-0 gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-red-50 text-red-500 flex items-center justify-center shrink-0">
                        <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $doc->original_filename }}</p>
                        <p class="text-xs text-slate-400">{{ $doc->file_size_formatted }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('documents.pdf', $doc) }}" target="_blank" id="tour-doc-open" class="btn-secondary btn-sm" title="Open PDF">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                        <span class="hidden sm:inline">Open</span>
                    </a>
                    <a href="{{ route('documents.download', $doc) }}" id="tour-doc-download" class="btn-primary btn-sm" title="Download PDF">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        <span class="hidden sm:inline">Download</span>
                    </a>
                </div>
            </div>
            <div class="flex-1 bg-slate-50 overflow-hidden">
                <iframe src="{{ route('documents.pdf', $doc) }}" class="w-full h-full border-0"></iframe>
            </div>
        </div>
    </div>

    {{-- Metadata Panel --}}
    <div class="space-y-5 xl:overflow-y-auto" style="max-height: clamp(400px, calc(100vh - 140px), 900px);">

        {{-- Document Info Card --}}
        <div class="card" id="tour-doc-meta">
            <div class="p-6">

                {{-- Reference Number --}}
                <div class="flex items-center gap-2 mb-4 px-3 py-2 rounded-xl bg-violet-50 border border-violet-100">
                    <svg class="w-3.5 h-3.5 text-violet-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 8.25h15m-16.5 7.5h15m-1.8-13.5l-3.9 19.5m-2.1-19.5l-3.9 19.5" />
                    </svg>
                    <span class="text-[11px] font-bold text-violet-400 uppercase tracking-widest">Reference No.</span>
                    <span class="ml-auto text-sm font-bold text-violet-700 tracking-wide font-mono">{{ $doc->reference_number }}</span>
                </div>

                <h1 class="text-base font-bold text-slate-900 leading-snug mb-5">{{ $doc->title }}</h1>

                {{-- Document Type --}}
                <div class="flex items-center gap-3 p-3 mb-5 rounded-xl border border-slate-100 bg-slate-50">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                        {{ $doc->document_type === 'incoming' ? 'bg-blue-50 text-blue-500' : 'bg-emerald-50 text-emerald-500' }}">
                        @if($doc->document_type === 'incoming')
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3v8.25m0 0l-3-3m3 3l3-3" />
                        </svg>
                        @else
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                        </svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-800">{{ $doc->document_type_label }}</p>
                        <p class="text-[11px] text-slate-400">
                            {{ $doc->document_type === 'incoming' ? 'Received from an office' : 'Sent to a recipient' }}
                        </p>
                    </div>
                </div>

                {{-- Key metadata --}}
                <div class="space-y-4">
                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-400 font-medium">Reference No.</span>
                        <span class="text-sm font-bold text-violet-700 text-right tracking-wide font-mono">{{ $doc->reference_number }}</span>
                    </div>

                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-400 font-medium">Date Received</span>
                        <span class="text-sm font-semibold text-slate-800 text-right">{{ $doc->date_issued->format('F d, Y') }}</span>
                    </div>

                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-400 font-medium">Office / Origin</span>
                        <span class="text-sm font-semibold text-slate-800 text-right max-w-[60%]">{{ $doc->received_from ?? '—' }}</span>
                    </div>

                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-400 font-medium">Recipient</span>
                        <span class="text-sm font-semibold text-slate-800 text-right max-w-[60%]">{{ $doc->recipient ?? '—' }}</span>
                    </div>

                    @if($doc->expiration_date)
                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-400 font-medium">Deadline</span>
                        <span class="text-sm font-semibold text-right
                            {{ $doc->expiration_date->isPast() ? 'text-red-600' : (now()->diffInDays($doc->expiration_date) <= 7 ? 'text-amber-600' : 'text-slate-800') }}">
                            {{ $doc->expiration_date->format('F d, Y') }}
                            @if($doc->expiration_date->isPast())
                                <span class="block text-[11px] font-normal text-red-400">Past deadline</span>
                            @elseif(now()->diffInDays($doc->expiration_date) <= 7)
                                <span class="block text-[11px] font-normal text-amber-400">Deadline soon</span>
                            @endif
                        </span>
                    </div>
                    @endif

                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-400 font-medium">Registered By</span>
                        <span class="text-sm font-semibold text-slate-800 text-right">{{ $doc->uploader->name ?? 'System' }}</span>
                    </div>

                    @if($doc->updater)
                    <div class="flex justify-between items-start">
                        <span class="text-xs text-slate-400 font-medium">Last Edited By</span>
                        <span class="text-sm font-semibold text-slate-800 text-right">{{ $doc->updater->name }}</span>
                    </div>
                    @endif


                </div>

            </div>
        </div>

        {{-- Activity log --}}
        @if($doc->activityLogs->count())
        <div class="card" id="tour-doc-activity-log">
            <div class="p-6">
                <h3 class="form-section-title">Activity Log</h3>
                <div class="space-y-3 overflow-y-auto" style="max-height: 280px;">
                    @foreach($doc->activityLogs as $log)
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
