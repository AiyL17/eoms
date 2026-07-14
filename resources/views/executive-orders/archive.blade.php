@extends('layouts.app')

@section('title', 'Archived Executive Orders')
@section('page-title', 'Archived Executive Orders')

@section('breadcrumb')
    <a href="{{ route('executive-orders.index') }}" class="hover:text-violet-600 transition-colors">Executive Orders</a>
    <span class="mx-1 opacity-40">/</span>
    <span class="text-slate-700 font-semibold">Archive</span>
@endsection

@section('header-actions')
    <a href="{{ route('executive-orders.index') }}" class="btn-secondary btn-sm">
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
        </svg>
        Back to EOs
    </a>
@endsection

@section('content')

<div class="card">

    {{-- Header --}}
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-4 flex-wrap">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center shrink-0">
                <svg class="w-4.5 h-4.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z" />
                </svg>
            </div>
            <div>
                <p class="text-sm font-bold text-slate-800">Archive</p>
                <p class="text-xs text-slate-400">
                    {{ number_format($orders->total()) }} {{ Str::plural('record', $orders->total()) }} — permanently removed after 30 days
                </p>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full table-auto table-wide">
            <thead>
                <tr>
                    <th class="w-36">EO Number</th>
                    <th>Title</th>
                    <th class="w-40">Archived On</th>
                    <th class="w-40">Uploaded By</th>
                    <th class="w-48 text-right pr-6">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $eo)
                <tr class="group">
                    <td class="font-bold text-slate-800 whitespace-nowrap">{{ $eo->eo_number }}</td>
                    <td>
                        <div class="text-[13px] font-semibold text-slate-800 mb-0.5 truncate max-w-xs" title="{{ $eo->title }}">
                            {{ Str::limit($eo->title, 65) }}
                        </div>
                        <div class="text-xs text-slate-400 truncate max-w-xs">{{ Str::limit($eo->subject, 80) }}</div>
                    </td>
                    <td class="whitespace-nowrap">
                        <p class="text-[13px] text-slate-600 font-semibold">{{ $eo->deleted_at->format('M d, Y') }}</p>
                        <p class="text-xs text-slate-400">{{ $eo->deleted_at->format('h:i A') }}</p>
                    </td>
                    <td class="text-slate-500 text-[13px]">{{ $eo->uploader->name ?? '—' }}</td>
                    <td class="text-right pr-5 whitespace-nowrap">
                        <div class="flex items-center justify-end gap-2">
                            {{-- Restore --}}
                            <form action="{{ route('executive-orders.restore', $eo->id) }}" method="POST">
                                @csrf
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50 px-3 py-1.5 rounded-lg transition-colors"
                                        title="Restore this EO">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                    </svg>
                                    Restore
                                </button>
                            </form>

                            {{-- Permanent delete --}}
                            <form action="{{ route('executive-orders.force-destroy', $eo->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-500 hover:text-red-700 hover:bg-red-50 px-3 py-1.5 rounded-lg transition-colors"
                                        data-confirm="Permanently delete {{ $eo->eo_number }}? The PDF and all previous versions will also be removed. This cannot be undone."
                                        title="Permanently delete">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                    Delete Forever
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-16 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center mb-4 text-amber-400">
                                <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z" />
                                </svg>
                            </div>
                            <p class="text-sm font-bold text-slate-800 mb-1">Archive is empty</p>
                            <p class="text-sm text-slate-500">No archived executive orders found.</p>
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
