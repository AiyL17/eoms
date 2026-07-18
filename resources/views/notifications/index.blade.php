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
            'eo_updated'            => ['bg' => 'bg-blue-100',    'text' => 'text-blue-600',    'path' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z'],
            'eo_type_changed'       => ['bg' => 'bg-sky-100',     'text' => 'text-sky-600',     'path' => 'M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5'],
            'eo_archived'       => ['bg' => 'bg-orange-100',  'text' => 'text-orange-600',  'path' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z'],
            // legacy fallback for old notifications stored with eo_deleted type
            'eo_deleted'        => ['bg' => 'bg-orange-100',  'text' => 'text-orange-600',  'path' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-.375c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v.375c0 .621.504 1.125 1.125 1.125z'],
            'eo_expiring'           => ['bg' => 'bg-orange-100',  'text' => 'text-orange-600',  'path' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'],
            'eo_expiration_warning' => ['bg' => 'bg-red-100',     'text' => 'text-red-600',     'path' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'],
            'eo_review_due'         => ['bg' => 'bg-violet-100',  'text' => 'text-violet-600',  'path' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
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
