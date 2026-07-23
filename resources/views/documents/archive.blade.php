@extends('layouts.app')

@section('title', 'Archived Documents')
@section('page-title', 'Archived Documents')

@section('breadcrumb')
    <a href="{{ route('documents.index') }}" class="hover:text-violet-600 transition-colors">Documents</a>
    <span class="mx-1 opacity-40">/</span>
    <span class="text-slate-700 font-semibold">Archive</span>
@endsection

@section('header-actions')
    <a href="{{ route('documents.index') }}" id="tour-header-btn" class="btn-secondary btn-sm">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Back to Documents
    </a>
@endsection

@section('content')

{{-- Filters --}}
<div class="card mb-5" data-tour="archive-filters">
    <div class="px-5 py-4">
        <form action="{{ route('documents.archive') }}" method="GET">
            <div class="flex flex-col lg:flex-row gap-3 items-center">

                {{-- Search --}}
                <div class="flex-1 w-full relative">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Search by document number, title…"
                           class="form-input form-input-icon">
                </div>

                {{-- Actions --}}
                <div class="flex gap-2 w-full lg:w-auto shrink-0">
                    <button type="submit" class="btn-primary h-[42px] px-5 w-full lg:w-auto">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search']))
                        <a href="{{ route('documents.archive') }}"
                           class="btn-secondary h-[42px] px-4 w-full lg:w-auto text-slate-400 hover:text-slate-600"
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

{{-- Table --}}
<div class="card" data-tour="archive-table">

    {{-- Result count + active filter chips --}}
    <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z" />
                </svg>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500">
                    {{ number_format($orders->total()) }} {{ Str::plural('record', $orders->total()) }} found
                </p>
                <p class="text-[11px] text-slate-400">Permanently removed after 30 days of archiving</p>
            </div>
        </div>
        @if(request()->anyFilled(['search']))
        <div class="flex items-center gap-2 flex-wrap">
            @if(request('search'))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 text-amber-700 text-xs font-medium rounded-full border border-amber-100">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    "{{ Str::limit(request('search'), 30) }}"
                </span>
            @endif
        </div>
        @endif
    </div>

    {{-- ── Mobile card list (< md) ──────────────────────────────────────────── --}}
    <div class="block md:hidden divide-y divide-slate-100">
        @forelse($orders as $doc)
        <div class="px-4 py-4">
            <div class="flex items-start justify-between gap-3 mb-2">
                <span class="text-xs text-slate-400">{{ $doc->deleted_at->format('M d, Y') }}</span>
            </div>
            <p class="text-sm font-semibold text-slate-800 leading-snug mb-0.5">{{ Str::limit($doc->title, 70) }}</p>
            <p class="text-[11px] font-mono font-bold text-violet-700 mb-1">{{ $doc->reference_number }}</p>
            <p class="text-xs text-slate-400 mb-3">Uploaded by: <span class="font-medium text-slate-600">{{ $doc->uploader->name ?? '—' }}</span></p>
            <div class="flex items-center gap-2">
                <form action="{{ route('documents.restore', $doc->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                        Restore
                    </button>
                </form>
                <form action="{{ route('documents.force-destroy', $doc->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-500 hover:text-red-700 hover:bg-red-50 px-3 py-1.5 rounded-lg border border-red-200 transition-colors"
                            data-confirm="Permanently delete {{ $doc->reference_number }}? The PDF and all previous versions will also be removed. This cannot be undone.">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                        Delete Forever
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="py-16 text-center px-4">
            <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center mb-4 text-amber-400 mx-auto">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z" /></svg>
            </div>
            <p class="text-sm font-bold text-slate-800 mb-1">{{ request()->anyFilled(['search']) ? 'No records match your search' : 'Archive is empty' }}</p>
            <p class="text-sm text-slate-500 mb-5">{{ request()->anyFilled(['search']) ? 'Try a different search term.' : 'No Archived Documents found.' }}</p>
            @if(request()->anyFilled(['search']))<a href="{{ route('documents.archive') }}" class="btn-secondary btn-sm">Clear Search</a>@endif
        </div>
        @endforelse
    </div>

    {{-- ── Desktop table (md+) ───────────────────────────────────────────── --}}
    <div class="hidden md:block overflow-x-auto">
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
                    <th class="w-36"><a href="{{ $sortUrl('reference_number') }}" class="inline-flex items-center gap-1 group hover:text-violet-700 transition-colors">Reference No. {!! $sortIcon('reference_number') !!}</a></th>
                    <th><a href="{{ $sortUrl('title') }}" class="inline-flex items-center gap-1 group hover:text-violet-700 transition-colors">Document Name {!! $sortIcon('title') !!}</a></th>
                    <th class="w-40"><a href="{{ $sortUrl('deleted_at') }}" class="inline-flex items-center gap-1 group hover:text-violet-700 transition-colors">Archived On {!! $sortIcon('deleted_at') !!}</a></th>
                    <th class="w-40"><a href="{{ $sortUrl('uploaded_by') }}" class="inline-flex items-center gap-1 group hover:text-violet-700 transition-colors">Uploaded By {!! $sortIcon('uploaded_by') !!}</a></th>
                    <th class="w-20 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $doc)
                <tr class="group">
                    <td class="whitespace-nowrap">
                        <span class="text-[13px] font-mono font-bold text-violet-700">{{ $doc->reference_number }}</span>
                    </td>
                    <td>
                        <div class="text-[13px] font-semibold text-slate-800 mb-0.5 truncate max-w-xs" title="{{ $doc->title }}">{{ Str::limit($doc->title, 65) }}</div>
                    </td>
                    <td class="whitespace-nowrap">
                        <p class="text-[13px] text-slate-600 font-semibold">{{ $doc->deleted_at->format('M d, Y') }}</p>
                        <p class="text-xs text-slate-400">{{ $doc->deleted_at->format('h:i A') }}</p>
                    </td>
                    <td class="text-slate-500 text-[13px]">{{ $doc->uploader->name ?? '—' }}</td>
                    <td class="text-center whitespace-nowrap">
                        <div class="inline-flex items-center justify-center gap-1">
                            <form action="{{ route('documents.restore', $doc->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        @if($loop->first) id="tour-archive-restore" @endif
                                        class="inline-flex items-center justify-center text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 p-1.5 rounded-lg transition-colors" title="Restore this document">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                                </button>
                            </form>
                            <form action="{{ route('documents.force-destroy', $doc->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        @if($loop->first) id="tour-archive-delete" @endif
                                        class="inline-flex items-center justify-center text-red-500 hover:text-red-700 hover:bg-red-50 p-1.5 rounded-lg transition-colors"
                                        data-confirm="Permanently delete {{ $doc->reference_number }}? The PDF and all previous versions will also be removed. This cannot be undone." title="Permanently delete">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-16 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center mb-4 text-amber-400">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z" /></svg>
                            </div>
                            <p class="text-sm font-bold text-slate-800 mb-1">{{ request()->anyFilled(['search']) ? 'No records match your search' : 'Archive is empty' }}</p>
                            <p class="text-sm text-slate-500 mb-5">{{ request()->anyFilled(['search']) ? 'Try a different search term.' : 'No Archived Documents found.' }}</p>
                            @if(request()->anyFilled(['search']))<a href="{{ route('documents.archive') }}" class="btn-secondary btn-sm">Clear Search</a>@endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
    <div class="border-t border-slate-100 px-6 py-4 bg-slate-50/50 flex items-center justify-between gap-4 flex-wrap">
        <p class="text-xs text-slate-400">
            Showing {{ $orders->firstItem() }}–{{ $orders->lastItem() }} of {{ number_format($orders->total()) }}
        </p>
        {{ $orders->links() }}
    </div>
    @endif

</div>

@endsection
