@extends('layouts.app')

@section('title', 'Create User')
@section('page-title', 'Create User')

@section('breadcrumb')
    <a href="{{ route('admin.users.index') }}" class="hover:text-violet-600 transition-colors">User Profiles</a>
    <span class="mx-1 opacity-40">/</span>
    <span class="text-slate-700 font-semibold">Create</span>
@endsection

@section('content')
<form action="{{ route('admin.users.store') }}" method="POST" id="user-form">
@csrf

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── LEFT: Form ───────────────────────────────────────────────────── --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- 1. Profile --}}
        <div class="card">
            <div class="p-6">
                <h3 class="form-section-title">1 — Profile</h3>
                <div class="space-y-5">
                    <div>
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                               class="form-input {{ $errors->has('name') ? 'error' : '' }}" required
                               placeholder="e.g. Maria Santos">
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                               class="form-input {{ $errors->has('email') ? 'error' : '' }}" required
                               placeholder="user@citygovernment.gov.ph">
                        @error('email') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label for="role" class="form-label">System Role</label>
                            <select name="role" id="role" class="form-input {{ $errors->has('role') ? 'error' : '' }}" required>
                                <option value="staff" {{ old('role', 'staff') === 'staff' ? 'selected' : '' }}>Staff</option>
                                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator</option>
                            </select>
                            @error('role') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="position" class="form-label">
                                Position / Title
                                <span class="text-slate-400 font-normal ml-1">(optional)</span>
                            </label>
                            <input type="text" name="position" id="position" value="{{ old('position') }}"
                                   class="form-input {{ $errors->has('position') ? 'error' : '' }}"
                                   placeholder="e.g. HR Manager">
                            @error('position') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Password --}}
        <div class="card">
            <div class="p-6">
                <h3 class="form-section-title">2 — Set Password</h3>
                <div class="space-y-5">
                    <div>
                        <label for="password" class="form-label">Password</label>
                        <div class="relative">
                            <input type="password" name="password" id="password"
                                   class="form-input pr-10 {{ $errors->has('password') ? 'error' : '' }}" required
                                   placeholder="Minimum 8 characters">
                            <button type="button"
                                    onclick="togglePassword('password', this)"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition-colors"
                                    tabindex="-1" aria-label="Toggle password visibility">
                                <svg class="eye-on w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg class="eye-off w-4 h-4 hidden" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        @error('password') <p class="form-error">{{ $message }}</p> @enderror
                        <p class="form-hint mt-2 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                            Must be at least 8 characters and contain uppercase, lowercase, numbers, and symbols.
                        </p>
                    </div>
                    <div>
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="password_confirmation"
                                   class="form-input pr-10" required placeholder="Repeat the password">
                            <button type="button"
                                    onclick="togglePassword('password_confirmation', this)"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 transition-colors"
                                    tabindex="-1" aria-label="Toggle password visibility">
                                <svg class="eye-on w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg class="eye-off w-4 h-4 hidden" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3 pb-2">
            <a href="{{ route('admin.users.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" id="submit-btn" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.66-1.548c0 .526-.099 1.039-.286 1.504" />
                </svg>
                <span id="submit-label">Create User</span>
            </button>
        </div>
    </div>

    {{-- ── RIGHT: Role guide ────────────────────────────────────────────── --}}
    <div class="sticky top-20 self-start space-y-5">

        {{-- Preview --}}
        <div class="card">
            <div class="p-5 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Preview</h3>
                <p class="text-xs text-slate-400 mt-0.5">Updates as you fill in the form</p>
            </div>
            <div class="p-5 space-y-4">
                <div class="flex items-center gap-3">
                    <div id="prev-avatar" class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0"
                         style="background: linear-gradient(135deg, #6d28d9, #7c3aed);">
                        <svg class="w-5 h-5 opacity-60" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    </div>
                    <div>
                        <p id="prev-name" class="text-sm font-bold text-slate-800">—</p>
                        <p id="prev-email" class="text-xs text-slate-400">—</p>
                    </div>
                </div>
                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <span class="text-xs text-slate-400 font-medium">Role</span>
                    <span id="prev-role" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Staff
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-400 font-medium">Position</span>
                    <span id="prev-position" class="text-xs font-semibold text-slate-700">—</span>
                </div>
            </div>
        </div>

        {{-- Role guide --}}
        <div class="bg-violet-50 rounded-2xl border border-violet-100 p-5 space-y-4">
            <h4 class="text-xs font-bold text-violet-900 uppercase tracking-widest">Role Permissions</h4>
            <div>
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-violet-600 text-white">Admin</span>
                </div>
                <ul class="space-y-1 text-xs text-violet-700">
                    <li class="flex gap-2"><svg class="w-3 h-3 shrink-0 mt-0.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>Full document management (create, edit, delete)</li>
                    <li class="flex gap-2"><svg class="w-3 h-3 shrink-0 mt-0.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>User management</li>
                    <li class="flex gap-2"><svg class="w-3 h-3 shrink-0 mt-0.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>View system audit logs</li>
                </ul>
            </div>
            <div class="border-t border-violet-100 pt-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-500 text-white">Staff</span>
                </div>
                <ul class="space-y-1 text-xs text-violet-700">
                    <li class="flex gap-2"><svg class="w-3 h-3 shrink-0 mt-0.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>View, create, and edit documents</li>
                    <li class="flex gap-2"><svg class="w-3 h-3 shrink-0 mt-0.5 text-violet-500" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>View and download PDFs</li>
                    <li class="flex gap-2 text-violet-400"><svg class="w-3 h-3 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>Cannot delete documents or manage users</li>
                </ul>
            </div>
        </div>
    </div>

</div>
</form>

@push('scripts')
<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    btn.querySelector('.eye-on').classList.toggle('hidden', isHidden);
    btn.querySelector('.eye-off').classList.toggle('hidden', !isHidden);
}

(function () {
    const nameInput  = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const roleSelect = document.getElementById('role');
    const posInput   = document.getElementById('position');
    const submitBtn  = document.getElementById('submit-btn');
    const submitLbl  = document.getElementById('submit-label');

    function updatePreview() {
        const name = nameInput.value.trim();
        const initials = name ? name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase() : null;

        const avatar = document.getElementById('prev-avatar');
        avatar.innerHTML = initials
            ? `<span class="text-sm font-bold">${initials}</span>`
            : `<svg class="w-5 h-5 opacity-60" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>`;

        document.getElementById('prev-name').textContent    = name || '—';
        document.getElementById('prev-email').textContent   = emailInput.value || '—';
        document.getElementById('prev-position').textContent = posInput.value || '—';

        const isAdmin = roleSelect.value === 'admin';
        document.getElementById('prev-role').innerHTML = isAdmin
            ? `<span class="w-1.5 h-1.5 rounded-full bg-violet-500"></span>Administrator`
            : `<span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Staff`;
        document.getElementById('prev-role').className = isAdmin
            ? 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-violet-50 text-violet-700 border border-violet-100'
            : 'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200';
    }

    nameInput.addEventListener('input',  updatePreview);
    emailInput.addEventListener('input', updatePreview);
    roleSelect.addEventListener('change', updatePreview);
    posInput.addEventListener('input',   updatePreview);
    updatePreview();

    document.getElementById('user-form').addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitLbl.textContent = 'Creating…';
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
    });
})();
</script>
@endpush
@endsection
