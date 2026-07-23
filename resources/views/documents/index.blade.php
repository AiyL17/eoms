@extends('layouts.app')

@section('title', 'Documents')
@section('page-title', 'Documents')

@section('header-actions')
    @if(auth()->user()->isAdmin() || \App\Models\Setting::get('staff_can_upload', '1') === '1')
    <a href="{{ route('documents.create') }}" id="tour-header-btn" class="btn-primary btn-sm" title="Register Document">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        <span class="hidden sm:inline">Register Document</span>
    </a>
    @endif
    <a href="{{ route('documents.export', request()->query()) }}"
       id="tour-export-csv"
       class="btn-secondary btn-sm"
       title="Export current filter results to CSV">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
        </svg>
        <span class="hidden sm:inline">Export CSV</span>
    </a>
@endsection

@section('content')

{{-- Filters --}}
<div class="card mb-5" id="tour-doc-filters">
    <div class="px-5 py-4">
        <form action="{{ route('documents.index') }}" method="GET">
            <div class="flex flex-col lg:flex-row gap-3 items-center">

                {{-- Search --}}
                <div class="flex-1 w-full relative">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                    </div>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                           placeholder="Search by document name, office, recipient…"
                           class="form-input form-input-icon">
                </div>

                {{-- Document Type --}}
                <div class="w-full lg:w-40 shrink-0">
                    <select name="document_type" id="document_type" class="form-input">
                        <option value="">All Types</option>
                        @foreach($documentTypes as $value => $label)
                            <option value="{{ $value }}" {{ request('document_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Actions --}}
                <div class="flex gap-2 w-full lg:w-auto shrink-0">
                    <button type="submit" class="btn-primary h-[42px] px-5 w-full lg:w-auto">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'document_type']))
                        <a href="{{ route('documents.index') }}"
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
<div class="card" id="tour-doc-table">

    {{-- Result count + active filter chips --}}
    <div class="px-5 py-3 border-b border-slate-100 flex items-center justify-between gap-4 flex-wrap">
        <p class="text-xs font-semibold text-slate-500">
            {{ number_format($orders->total()) }} {{ Str::plural('record', $orders->total()) }} found
        </p>
        @if(request()->anyFilled(['search', 'document_type']))
        <div class="flex items-center gap-2 flex-wrap">
            @if(request('search'))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-violet-50 text-violet-700 text-xs font-medium rounded-full border border-violet-100">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" /></svg>
                    "{{ Str::limit(request('search'), 30) }}"
                </span>
            @endif
            @if(request('document_type'))
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-violet-50 text-violet-700 text-xs font-medium rounded-full border border-violet-100">
                    Type: {{ $documentTypes[request('document_type')] ?? request('document_type') }}
                </span>
            @endif
        </div>
        @endif
    </div>

    {{-- ── Mobile card list ─────────────────────────────────────────────────── --}}
    <div class="block md:hidden divide-y divide-slate-100">
        @forelse($orders as $doc)
        <a href="{{ route('documents.show', $doc) }}" class="flex items-start gap-3 px-4 py-4 hover:bg-violet-50/40 transition-colors">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap mb-1">
                    @if($doc->document_type === 'incoming')
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-blue-50 text-blue-600 border border-blue-100">Incoming</span>
                    @else
                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-600 border border-emerald-100">Outgoing</span>
                    @endif
                </div>
                <p class="text-sm font-semibold text-slate-800 leading-snug">{{ Str::limit($doc->title, 70) }}</p>
                <div class="flex items-center gap-3 mt-2 text-xs text-slate-400">
                    <span>{{ $doc->date_issued->format('M d, Y') }}</span>
                    @if($doc->received_from)
                    <span class="text-slate-200">·</span>
                    <span class="truncate">{{ Str::limit($doc->received_from, 28) }}</span>
                    @endif
                    @if($doc->expiration_date)
                    <span class="text-slate-200">·</span>
                    <span class="{{ $doc->expiration_date->isPast() ? 'text-red-500 font-semibold' : (now()->diffInDays($doc->expiration_date) <= 7 ? 'text-amber-500' : 'text-slate-500') }}">
                        Deadline {{ $doc->expiration_date->format('M d, Y') }}
                    </span>
                    @endif
                </div>
            </div>
            <svg class="w-4 h-4 text-slate-300 shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
            </svg>
        </a>
        @empty
        <div class="py-16 text-center px-4">
            <div class="w-14 h-14 bg-violet-50 rounded-2xl flex items-center justify-center mb-4 text-violet-400 mx-auto">
                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
            </div>
            <p class="text-sm font-bold text-slate-800 mb-1">No records found</p>
            <p class="text-sm text-slate-500 mb-5">No documents match your current filters.</p>
            @if(request()->anyFilled(['search', 'document_type']))
                <a href="{{ route('documents.index') }}" class="btn-secondary btn-sm">Clear Filters</a>
            @else
                <a href="{{ route('documents.create') }}" class="btn-primary btn-sm">Register Document</a>
            @endif
        </div>
        @endforelse
    </div>

    {{-- ── Desktop table ────────────────────────────────────────────────────── --}}
    <div class="hidden md:block">
        <table class="w-full table-auto">
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
                    <th class="w-24">Type</th>
                    <th>Document Name</th>
                    <th class="w-32">
                        <a href="{{ $sortUrl('date_issued') }}" class="inline-flex items-center gap-1 group hover:text-violet-700 transition-colors">
                            Date Received {!! $sortIcon('date_issued') !!}
                        </a>
                    </th>
                    <th class="w-36">Office / Origin</th>
                    <th class="w-28 hidden xl:table-cell">Recipient</th>
                    <th class="w-28">
                        <a href="{{ $sortUrl('expiration_date') }}" class="inline-flex items-center gap-1 group hover:text-violet-700 transition-colors">
                            Deadline {!! $sortIcon('expiration_date') !!}
                        </a>
                    </th>
                    <th class="w-16 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $doc)
                <tr class="group cursor-pointer" onclick="window.location='{{ route('documents.show', $doc) }}'">
                    <td class="whitespace-nowrap">
                        @if($doc->document_type === 'incoming')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-blue-50 text-blue-600 text-[11px] font-semibold border border-blue-100">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75H6.912a2.25 2.25 0 00-2.15 1.588L2.35 13.177a2.25 2.25 0 00-.1.661V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H15M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859M12 3v8.25m0 0l-3-3m3 3l3-3" /></svg>
                                Incoming
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-600 text-[11px] font-semibold border border-emerald-100">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" /></svg>
                                Outgoing
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="text-[13px] font-semibold text-slate-800 group-hover:text-violet-700 transition-colors truncate max-w-xs" title="{{ $doc->title }}">
                            {{ Str::limit($doc->title, 65) }}
                        </div>
                    </td>
                    <td class="text-slate-500 whitespace-nowrap text-[13px]">{{ $doc->date_issued->format('M d, Y') }}</td>
                    <td class="text-slate-600 text-[13px] truncate" title="{{ $doc->received_from }}">
                        {{ Str::limit($doc->received_from ?? '—', 26) }}
                    </td>
                    <td class="text-slate-600 text-[13px] truncate hidden xl:table-cell" title="{{ $doc->recipient }}">
                        {{ Str::limit($doc->recipient ?? '—', 22) }}
                    </td>
                    <td class="text-[13px] whitespace-nowrap">
                        @if($doc->expiration_date)
                            <span class="{{ $doc->expiration_date->isPast() ? 'text-red-500 font-semibold' : (now()->diffInDays($doc->expiration_date) <= 7 ? 'text-amber-600' : 'text-slate-700') }}">
                                {{ $doc->expiration_date->format('M d, Y') }}
                            </span>
                        @else
                            <span class="text-slate-300">—</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <div class="inline-flex items-center justify-center gap-1" onclick="event.stopPropagation()">
                            {{-- Toggle type button --}}
                            <form action="{{ route('documents.toggle-type', $doc) }}" method="POST"
                                  data-confirm="Change &quot;{{ Str::limit($doc->title, 40) }}&quot; from {{ ucfirst($doc->document_type) }} to {{ $doc->document_type === 'incoming' ? 'Outgoing' : 'Incoming' }}?"
                                  data-confirm-title="Change Document Type"
                                  data-confirm-subtitle="This will update the type and notify relevant users."
                                  data-confirm-action="Update"
                                  data-confirm-variant="{{ $doc->document_type === 'incoming' ? 'outgoing' : 'incoming' }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        title="Switch to {{ $doc->document_type === 'incoming' ? 'Outgoing' : 'Incoming' }}"
                                        class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-lg transition-colors
                                               {{ $doc->document_type === 'incoming'
                                                  ? 'text-blue-500 hover:text-blue-700 hover:bg-blue-50'
                                                  : 'text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50' }}">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                    </svg>
                                </button>
                            </form>
                            {{-- View button --}}
                            <a href="{{ route('documents.show', $doc) }}"
                               title="View document"
                               class="inline-flex items-center text-violet-600 hover:text-violet-800 transition-colors p-1.5 rounded-lg hover:bg-violet-50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="py-16 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-14 h-14 bg-violet-50 rounded-2xl flex items-center justify-center mb-4 text-violet-400">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-800 mb-1">No records found</p>
                            <p class="text-sm text-slate-500 mb-5">No documents match your current filters.</p>
                            @if(request()->anyFilled(['search', 'document_type']))
                                <a href="{{ route('documents.index') }}" class="btn-secondary btn-sm">Clear Filters</a>
                            @else
                                <a href="{{ route('documents.create') }}" class="btn-primary btn-sm">Register Document</a>
                            @endif
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
