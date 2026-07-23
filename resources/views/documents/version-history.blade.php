@extends('layouts.app')

@section('title', 'Version History — ' . $doc->title)
@section('page-title', $doc->reference_number)

@section('breadcrumb')
    <a href="{{ route('documents.index') }}" class="hover:text-violet-600 transition-colors shrink-0">Documents</a>
    <span class="mx-1 opacity-40 shrink-0">/</span>
    <a href="{{ route('documents.show', $doc) }}" class="hover:text-violet-600 transition-colors truncate min-w-0">{{ $doc->title }}</a>
    <span class="mx-1 opacity-40 shrink-0">/</span>
    <span class="text-slate-700 font-semibold shrink-0">Version History</span>
@endsection

@section('header-actions')
    <a href="{{ route('documents.show', $doc) }}" class="btn-secondary btn-sm" id="tour-vh-back">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15m0 0l6.75 6.75M4.5 12l6.75-6.75" /></svg>
        Back to Document
    </a>
@endsection

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    {{-- ── PDF Version Archive ─────────────────────────────────────────────── --}}
    <div class="card" id="tour-vh-pdf-archive">
        <div class="card-header">
            <div class="min-w-0">
                <h2 class="text-sm font-bold text-slate-800">PDF Version Archive</h2>
                <p class="text-xs text-slate-400 mt-0.5">Previous PDF files kept when the document was replaced</p>
            </div>
            <span class="text-xs font-semibold text-slate-400 bg-slate-50 px-2 py-0.5 rounded-full border border-slate-100 whitespace-nowrap shrink-0">
                {{ count($archivedFiles) }} archived
            </span>
        </div>

        {{-- Current version --}}
        <div class="px-5 py-4 border-b border-slate-100 bg-violet-50/30" id="tour-vh-current">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 bg-violet-100 text-violet-600 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $doc->original_filename }}</p>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">Current version · {{ $doc->file_size_formatted }} · {{ $doc->updated_at->format('M d, Y g:i A') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('documents.pdf', $doc) }}" target="_blank" id="tour-vh-open" class="btn-secondary btn-sm">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                        Open
                    </a>
                    <a href="{{ route('documents.download', $doc) }}" id="tour-vh-download" class="btn-primary btn-sm">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        Download
                    </a>
                </div>
            </div>
        </div>

        {{-- Archived versions --}}
        <div id="tour-vh-archived-versions">
        @forelse($archivedFiles as $i => $file)
        <div class="px-5 py-4 border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-8 h-8 bg-slate-100 text-slate-500 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-700 truncate">{{ $file['original_name'] }}</p>
                        <p class="text-xs text-slate-400 mt-0.5 truncate">
                            {{ $file['timestamp'] ? $file['timestamp']->format('M d, Y g:i A') : 'Unknown date' }}
                            · {{ number_format($file['size'] / 1024, 1) }} KB
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('documents.version-history.open', ['document' => $doc->id, 'file' => $file['path']]) }}"
                       target="_blank"
                       class="btn-secondary btn-sm">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                        Open
                    </a>
                    <a href="{{ route('documents.version-history.download', ['document' => $doc->id, 'file' => $file['path']]) }}"
                       class="btn-secondary btn-sm">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        Download
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="py-10 text-center text-slate-400">
            <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center mx-auto mb-2 text-slate-400">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
            </div>
            <p class="text-sm font-semibold text-slate-600">No archived versions</p>
            <p class="text-xs text-slate-400 mt-1">Previous PDF versions appear here when the document is replaced during an edit.</p>
        </div>
        @endforelse
        </div>
    </div>

    {{-- ── Metadata Change History ──────────────────────────────────────────── --}}
    <div class="card" id="tour-vh-meta-history">
        <div class="card-header">
            <div>
                <h2 class="text-sm font-bold text-slate-800">Metadata Change History</h2>
                <p class="text-xs text-slate-400 mt-0.5">Side-by-side diff of every recorded field change</p>
            </div>
            <span class="text-xs font-semibold text-slate-400 bg-slate-50 px-2 py-0.5 rounded-full border border-slate-100">
                {{ count($metaDiffs) }} {{ Str::plural('change', count($metaDiffs)) }}
            </span>
        </div>

        <div class="divide-y divide-slate-50 overflow-y-auto" style="max-height: 640px;">
            @forelse($metaDiffs as $diff)
            <div class="px-5 py-4">
                <div class="flex items-center gap-2 mb-3">
                    <span class="action-badge-{{ $diff['action'] }}">{{ $diff['label'] }}</span>
                    <span class="text-xs text-slate-500">by <span class="font-semibold">{{ $diff['user'] }}</span></span>
                    <span class="ml-auto text-[11px] text-slate-400">{{ $diff['created_at']->format('M d, Y g:i A') }}</span>
                </div>
                @if($diff['notes'])
                <p class="text-xs text-slate-400 mb-3"><span class="font-semibold text-slate-500">Reason for edit:</span> <span class="italic">"{{ $diff['notes'] }}"</span></p>
                @endif
                @if(! empty($diff['old']) || ! empty($diff['new']))
                <div class="space-y-2">
                    @php
                        $allKeys = array_unique(array_merge(array_keys($diff['old']), array_keys($diff['new'])));
                        $fieldLabels = [
                            'title'             => 'Document Name',
                            'document_type'     => 'Document Type',
                            'received_from'     => 'Office / Origin',
                            'recipient'         => 'Recipient',
                            'date_issued'       => 'Date Received',
                            'expiration_date'   => 'Deadline',
                            'reference_number'  => 'Reference Number',
                            'original_filename' => 'PDF File',
                        ];
                    @endphp
                    @foreach($allKeys as $key)
                    @php
                        $oldVal = $diff['old'][$key] ?? null;
                        $newVal = $diff['new'][$key] ?? null;
                        $changed = $oldVal !== $newVal;
                        $label   = $fieldLabels[$key] ?? ucwords(str_replace('_', ' ', $key));
                    @endphp
                    @if($changed)
                    <div class="rounded-lg overflow-hidden border border-slate-100 text-xs">
                        <div class="px-3 py-1.5 bg-slate-50 border-b border-slate-100">
                            <span class="font-semibold text-slate-600">{{ $label }}</span>
                        </div>
                        <div class="grid grid-cols-2 divide-x divide-slate-100">
                            <div class="px-3 py-2 bg-red-50/50">
                                <p class="text-[10px] font-bold text-red-400 uppercase tracking-wide mb-1">Before</p>
                                <p class="text-slate-600 wrap-break-word">{{ $oldVal ?? '—' }}</p>
                            </div>
                            <div class="px-3 py-2 bg-emerald-50/50">
                                <p class="text-[10px] font-bold text-emerald-500 uppercase tracking-wide mb-1">After</p>
                                <p class="text-slate-700 font-medium wrap-break-word">{{ $newVal ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
                @else
                <p class="text-xs text-slate-400 italic">No field-level details recorded for this change.</p>
                @endif
            </div>
            @empty
            <div class="py-10 text-center text-slate-400">
                <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center mx-auto mb-2 text-slate-400">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
                </div>
                <p class="text-sm font-semibold text-slate-600">No changes recorded</p>
                <p class="text-xs text-slate-400 mt-1">Metadata diffs appear here each time this document is edited.</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

@endsection
