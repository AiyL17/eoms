@extends('layouts.app')

@section('title', 'Settings')
@section('page-title', 'Settings')

@section('content')

<form action="{{ route('admin.settings.update') }}" method="POST">
    @csrf
    @method('PATCH')

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">

        {{-- ── Left col: settings card + save ─────────────────────────── --}}
        <div class="xl:col-span-2 space-y-5" data-tour="settings-form">

            <div class="card divide-y divide-slate-100">

                {{-- Document Management ------------------------------------------------}}
                <div class="px-6 py-5" id="tour-setting-retention">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Document Management</p>
                            <p class="text-xs text-slate-400">Rules governing how documents are handled</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-6">
                        <div class="flex-1">
                            <label for="archive_retention_days" class="form-label">Archive Retention Period</label>
                            <p class="text-xs text-slate-400 mt-0.5 mb-3">Archived documents older than this are automatically purged by the nightly scheduler.</p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <input type="number" id="archive_retention_days" name="archive_retention_days"
                                   min="1" max="365"
                                   value="{{ old('archive_retention_days', $settings['archive_retention_days'] ?? 30) }}"
                                   class="form-input w-20 text-center @error('archive_retention_days') border-red-300 @enderror">
                            <span class="text-sm text-slate-500 whitespace-nowrap">days</span>
                        </div>
                    </div>
                    @error('archive_retention_days')
                        <p class="form-error mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Access Control -----------------------------------------------}}
                <div class="px-6 py-5" id="tour-setting-staff-upload">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-violet-50 text-violet-600 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25-2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800">Access Control</p>
                            <p class="text-xs text-slate-400">Permissions for non-administrator roles</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-6">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-slate-700">Allow Staff to Upload Documents</p>
                            <p class="text-xs text-slate-400 mt-0.5">When disabled, only administrators can upload new documents. Staff can still view and download.</p>
                        </div>
                        <div class="relative shrink-0">
                            <input type="checkbox" name="staff_can_upload" value="1" id="staff_can_upload"
                                   class="sr-only peer"
                                   {{ old('staff_can_upload', $settings['staff_can_upload'] ?? '1') == '1' ? 'checked' : '' }}>
                            <label for="staff_can_upload" class="block w-10 h-6 bg-slate-200 peer-checked:bg-violet-600 rounded-full cursor-pointer transition-colors peer-focus:ring-2 peer-focus:ring-violet-500/30"></label>
                            <span class="pointer-events-none absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></span>
                        </div>
                    </div>
                </div>

                {{-- Maintenance --------------------------------------------------}}
                <div class="px-6 py-5 {{ ($settings['maintenance_mode'] ?? '0') === '1' ? 'bg-amber-50/40' : '' }} rounded-b-2xl transition-colors" id="tour-setting-maintenance">
                    <div class="flex items-center gap-2.5 mb-4">
                        <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-slate-800">Maintenance Mode</p>
                            <p class="text-xs text-slate-400">Temporarily restrict system access for non-administrators</p>
                        </div>
                        @if(($settings['maintenance_mode'] ?? '0') === '1')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-700 border border-amber-200">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Active
                        </span>
                        @endif
                    </div>

                    <div class="flex items-center justify-between gap-6">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-slate-700">Enable Maintenance Mode</p>
                            <p class="text-xs text-slate-400 mt-0.5">Only administrators can log in. Staff will be logged out and see a maintenance notice on the login page.</p>
                        </div>
                        <div class="relative shrink-0">
                            <input type="checkbox" name="maintenance_mode" value="1" id="maintenance_mode"
                                   class="sr-only peer"
                                   {{ old('maintenance_mode', $settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : '' }}>
                            <label for="maintenance_mode" class="block w-10 h-6 bg-slate-200 peer-checked:bg-amber-500 rounded-full cursor-pointer transition-colors peer-focus:ring-2 peer-focus:ring-amber-500/30"></label>
                            <span class="pointer-events-none absolute top-1 left-1 w-4 h-4 bg-white rounded-full shadow transition-transform peer-checked:translate-x-4"></span>
                        </div>
                    </div>
                </div>

            </div>{{-- /card --}}

            {{-- Validation errors --}}
            @if($errors->any())
            <div class="flex items-start gap-2.5 px-4 py-3 rounded-xl bg-red-50 border border-red-100 text-red-700 text-sm font-medium">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div>
                    <p class="font-semibold">Please fix the following:</p>
                    <ul class="mt-1 space-y-0.5 list-disc list-inside text-xs font-normal">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif

            {{-- Save button --}}
            <div class="flex items-center gap-4">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Save Settings
                </button>
                <p class="text-xs text-slate-400">Changes take effect immediately.</p>
            </div>

        </div>{{-- /left col --}}

        {{-- ── Right col: system health ────────────────────────────────── --}}
        <div class="xl:sticky xl:top-20">
            <div class="card overflow-hidden" data-tour="health-panel">

                {{-- Panel header --}}
                <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-slate-800">System Health</p>
                        <p class="text-xs text-slate-400">Live sanity checks</p>
                    </div>
                    {{-- Overall status badge --}}
                    @php
                        $hasFailure = collect($health)->contains('status', 'fail');
                        $hasWarning = collect($health)->contains('status', 'warn');
                        $overallStatus = $hasFailure ? 'fail' : ($hasWarning ? 'warn' : 'ok');
                    @endphp
                    <span id="health-badge" @class([
                        'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold',
                        'bg-emerald-100 text-emerald-700' => $overallStatus === 'ok',
                        'bg-amber-100 text-amber-700'     => $overallStatus === 'warn',
                        'bg-red-100 text-red-700'         => $overallStatus === 'fail',
                    ])>
                        <span class="w-1.5 h-1.5 rounded-full {{ $overallStatus === 'ok' ? 'bg-emerald-500' : ($overallStatus === 'warn' ? 'bg-amber-500 animate-pulse' : 'bg-red-500 animate-pulse') }}"></span>
                        <span>{{ $overallStatus === 'ok' ? 'Healthy' : ($overallStatus === 'warn' ? 'Warnings' : 'Issues') }}</span>
                    </span>

                    {{-- Re-check button --}}
                    <button type="button" id="health-recheck-btn"
                            onclick="recheckHealth()"
                            title="Re-check now"
                            class="w-7 h-7 flex items-center justify-center rounded-lg text-violet-500 hover:bg-violet-50 hover:text-violet-700 transition-colors">
                        <svg id="health-recheck-icon" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </button>
                </div>{{-- /header --}}

                {{-- Check rows --}}
                <ul id="health-rows" class="divide-y divide-slate-100">
                    @foreach($health as $check)
                    @php
                        $cfg = match($check['status']) {
                            'ok'   => ['dot' => 'bg-emerald-500', 'icon_bg' => 'bg-emerald-50', 'icon_text' => 'text-emerald-600', 'detail' => 'text-slate-500',
                                       'path' => 'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                            'warn' => ['dot' => 'bg-amber-500',   'icon_bg' => 'bg-amber-50',   'icon_text' => 'text-amber-600',   'detail' => 'text-amber-700',
                                       'path' => 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z'],
                            default => ['dot' => 'bg-red-500',    'icon_bg' => 'bg-red-50',     'icon_text' => 'text-red-600',     'detail' => 'text-red-700',
                                       'path' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z'],
                        };
                        $hasHint = $check['status'] !== 'ok' && !empty($check['hint']);
                    @endphp
                    <li class="px-5 py-3.5">
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5 {{ $cfg['icon_bg'] }} {{ $cfg['icon_text'] }}">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $cfg['path'] }}" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold text-slate-700">{{ $check['label'] }}</p>
                                <p class="text-xs {{ $cfg['detail'] }} mt-0.5 leading-snug">{{ $check['detail'] }}</p>
                                @if($hasHint)
                                <button type="button"
                                        onclick="toggleHint(this)"
                                        class="inline-flex items-center gap-1 mt-1.5 text-[11px] font-semibold
                                               {{ $check['status'] === 'fail' ? 'text-red-500 hover:text-red-700' : 'text-amber-500 hover:text-amber-700' }}
                                               transition-colors select-none">
                                    <svg class="hint-chevron w-3 h-3 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                    </svg>
                                    <span class="hint-label">How to fix</span>
                                </button>
                                @endif
                            </div>
                            <span class="w-2 h-2 rounded-full shrink-0 mt-2 {{ $cfg['dot'] }}"></span>
                        </div>
                        @if($hasHint)
                        <div class="hint-box hidden ml-10 mt-2">
                            <div class="flex items-start gap-2 px-3 py-2.5 rounded-lg
                                        {{ $check['status'] === 'fail' ? 'bg-red-50 border border-red-100' : 'bg-amber-50 border border-amber-100' }}">
                                <svg class="w-3.5 h-3.5 shrink-0 mt-0.5 {{ $check['status'] === 'fail' ? 'text-red-400' : 'text-amber-400' }}"
                                     fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                                </svg>
                                <p class="text-[11px] leading-relaxed {{ $check['status'] === 'fail' ? 'text-red-700' : 'text-amber-700' }}">
                                    {{ $check['hint'] }}
                                </p>
                            </div>
                        </div>
                        @endif
                    </li>
                    @endforeach
                </ul>

                {{-- Footer timestamp --}}
                <div class="px-5 py-3 border-t border-slate-100 bg-slate-50/60">
                    <p id="health-timestamp" class="text-[11px] text-slate-400 text-center">
                        Checked at {{ now()->format('g:i A') }} · {{ now()->format('M j, Y') }}
                    </p>
                </div>

            </div>
        </div>{{-- /right col --}}

    </div>

</form>

@push('scripts')
<script>
const HEALTH_URL = '{{ route('admin.settings.health') }}';

const statusCfg = {
    ok:   {
        dot:      'bg-emerald-500',
        iconBg:   'bg-emerald-50',
        iconText: 'text-emerald-600',
        detail:   'text-slate-500',
        path:     'M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    warn: {
        dot:      'bg-amber-500',
        iconBg:   'bg-amber-50',
        iconText: 'text-amber-600',
        detail:   'text-amber-700',
        path:     'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z',
    },
    fail: {
        dot:      'bg-red-500',
        iconBg:   'bg-red-50',
        iconText: 'text-red-600',
        detail:   'text-red-700',
        path:     'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z',
    },
};

const badgeCfg = {
    ok:   { cls: 'bg-emerald-100 text-emerald-700', dot: 'bg-emerald-500',              label: 'Healthy'  },
    warn: { cls: 'bg-amber-100 text-amber-700',     dot: 'bg-amber-500 animate-pulse',  label: 'Warnings' },
    fail: { cls: 'bg-red-100 text-red-700',         dot: 'bg-red-500 animate-pulse',    label: 'Issues'   },
};

async function recheckHealth() {
    const btn       = document.getElementById('health-recheck-btn');
    const icon      = document.getElementById('health-recheck-icon');
    const rowsList  = document.getElementById('health-rows');
    const badge     = document.getElementById('health-badge');
    const timestamp = document.getElementById('health-timestamp');

    // Spin the icon and disable the button while loading
    btn.disabled = true;
    icon.classList.add('animate-spin');

    try {
        const res  = await fetch(HEALTH_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        const data = await res.json();

        // Re-render check rows
        rowsList.innerHTML = data.health.map(check => {
            const cfg = statusCfg[check.status] ?? statusCfg.fail;
            const isBad = check.status !== 'ok';
            const toggleColor = check.status === 'fail'
                ? 'text-red-500 hover:text-red-700'
                : 'text-amber-500 hover:text-amber-700';
            const hintBg = check.status === 'fail'
                ? 'bg-red-50 border border-red-100'
                : 'bg-amber-50 border border-amber-100';
            const hintText = check.status === 'fail' ? 'text-red-700' : 'text-amber-700';
            const hintIcon = check.status === 'fail' ? 'text-red-400' : 'text-amber-400';

            const toggleBtn = (isBad && check.hint) ? `
                <button type="button" onclick="toggleHint(this)"
                        class="inline-flex items-center gap-1 mt-1.5 text-[11px] font-semibold ${toggleColor} transition-colors select-none">
                    <svg class="hint-chevron w-3 h-3 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                    <span class="hint-label">How to fix</span>
                </button>` : '';

            const hintBox = (isBad && check.hint) ? `
                <div class="hint-box hidden ml-10 mt-2">
                    <div class="flex items-start gap-2 px-3 py-2.5 rounded-lg ${hintBg}">
                        <svg class="w-3.5 h-3.5 shrink-0 mt-0.5 ${hintIcon}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <p class="text-[11px] leading-relaxed ${hintText}">${escHtml(check.hint)}</p>
                    </div>
                </div>` : '';

            return `
            <li class="px-5 py-3.5">
                <div class="flex items-start gap-3">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0 mt-0.5 ${cfg.iconBg} ${cfg.iconText}">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="${cfg.path}" />
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-slate-700">${escHtml(check.label)}</p>
                        <p class="text-xs ${cfg.detail} mt-0.5 leading-snug">${escHtml(check.detail)}</p>
                        ${toggleBtn}
                    </div>
                    <span class="w-2 h-2 rounded-full shrink-0 mt-2 ${cfg.dot}"></span>
                </div>
                ${hintBox}
            </li>`;
        }).join('');

        // Update badge
        const bc = badgeCfg[data.overall] ?? badgeCfg.fail;
        badge.className = `inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-semibold ${bc.cls}`;
        badge.innerHTML = `<span class="w-1.5 h-1.5 rounded-full ${bc.dot}"></span><span>${bc.label}</span>`;

        // Update timestamp
        timestamp.textContent = 'Checked at ' + data.checkedAt;

    } catch (e) {
        timestamp.textContent = 'Re-check failed — please try again.';
    } finally {
        icon.classList.remove('animate-spin');
        btn.disabled = false;
    }
}

function toggleHint(btn) {
    const li       = btn.closest('li');
    const hintBox  = li.querySelector('.hint-box');
    const chevron  = btn.querySelector('.hint-chevron');
    const label    = btn.querySelector('.hint-label');
    const isOpen   = !hintBox.classList.contains('hidden');

    hintBox.classList.toggle('hidden', isOpen);
    chevron.classList.toggle('rotate-180', !isOpen);
    label.textContent = isOpen ? 'How to fix' : 'Hide';
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}
</script>
@endpush

@endsection
