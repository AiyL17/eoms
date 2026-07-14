@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications')

@section('header-actions')
    @if(auth()->user()->unreadNotifications->count() > 0)
    <form action="{{ route('notifications.read-all') }}" method="POST">
        @csrf
        <button type="submit" class="btn-secondary text-xs">
            Mark all as read
        </button>
    </form>
    @endif
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-3">

    @if($notifications->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm py-16 text-center">
        <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-400">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
            </svg>
        </div>
        <p class="text-sm font-semibold text-slate-700">No notifications yet</p>
        <p class="text-xs text-slate-400 mt-1">You're all caught up.</p>
    </div>
    @else

    @foreach($notifications as $n)
    @php
        $data = $n->data;
        $icon = match($data['type'] ?? '') {
            'eo_uploaded'       => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'path' => 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5'],
            'eo_status_changed' => ['bg' => 'bg-amber-100',   'text' => 'text-amber-600',   'path' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99'],
            'eo_updated'        => ['bg' => 'bg-blue-100',    'text' => 'text-blue-600',    'path' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z'],
            'eo_deleted'        => ['bg' => 'bg-red-100',     'text' => 'text-red-600',     'path' => 'M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0'],
            default             => ['bg' => 'bg-slate-100',   'text' => 'text-slate-500',   'path' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0'],
        };
    @endphp

    <form action="{{ route('notifications.read', $n->id) }}" method="POST">
        @csrf
        <button type="submit"
                class="w-full bg-white rounded-2xl border shadow-sm text-left flex items-start gap-4 px-5 py-4
                       hover:border-violet-200 hover:shadow-md transition-all duration-150
                       {{ $n->read_at ? 'border-slate-100 opacity-70' : 'border-violet-100' }}">

            {{-- Icon --}}
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 mt-0.5
                        {{ $icon['bg'] }} {{ $icon['text'] }}">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon['path'] }}" />
                </svg>
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm leading-snug {{ $n->read_at ? 'text-slate-500' : 'font-semibold text-slate-800' }}">
                    {{ $data['message'] ?? 'Notification' }}
                </p>
                @if(!empty($data['title']))
                <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $data['title'] }}</p>
                @endif
                <p class="text-xs text-slate-400 mt-1">{{ $n->created_at->diffForHumans() }} &mdash; {{ $n->created_at->format('M d, Y h:i A') }}</p>
            </div>

            {{-- Unread dot --}}
            @if(!$n->read_at)
            <span class="w-2.5 h-2.5 bg-violet-500 rounded-full shrink-0 mt-2"></span>
            @endif
        </button>
    </form>
    @endforeach

    {{-- Pagination --}}
    @if($notifications->hasPages())
    <div class="pt-2">
        {{ $notifications->links() }}
    </div>
    @endif

    @endif
</div>
@endsection
