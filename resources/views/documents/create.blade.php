@extends('layouts.app')

@section('title', 'Register Incoming Document')
@section('page-title', 'Register Incoming Document')

@section('breadcrumb')
    <a href="{{ route('documents.index') }}" class="hover:text-violet-600 transition-colors">Incoming Documents</a>
    <span class="mx-1 opacity-40">/</span>
    <span class="text-slate-700 font-semibold">Register New</span>
@endsection

@section('content')
<form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" id="doc-form">
@csrf

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── LEFT: Main form ──────────────────────────────────────────────── --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- 1. Document File --}}
        <div class="card" id="tour-doc-form-file">
            <div class="p-6">
                <h3 class="form-section-title">1 — Document File</h3>
                <div id="drop-zone"
                     class="relative rounded-2xl border-2 border-dashed border-slate-200 p-8 text-center cursor-pointer transition-all hover:border-violet-400 hover:bg-violet-50/30">
                    <input type="file" name="pdf_file" id="pdf_file" accept="application/pdf"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" required>
                    <div id="upload-idle" class="pointer-events-none">
                        <div class="w-14 h-14 bg-violet-50 text-violet-500 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-700 mb-1">Click to upload or drag & drop</p>
                        <p class="text-xs text-slate-400">PDF files only — max 20 MB</p>
                    </div>
                    <div id="upload-selected" class="hidden pointer-events-none">
                        <div class="w-14 h-14 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <p id="selected-filename" class="text-sm font-bold text-slate-800 truncate max-w-xs mx-auto"></p>
                        <p id="selected-filesize" class="text-xs text-slate-400 mt-1"></p>
                        <p class="text-xs text-violet-600 font-medium mt-2">Click to change file</p>
                    </div>
                </div>
                @error('pdf_file') <p class="form-error mt-2">{{ $message }}</p> @enderror
                <p class="form-hint mt-3 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                    Only PDF files are accepted. Maximum file size is <strong class="text-slate-600">20 MB</strong>.
                </p>
            </div>
        </div>

        {{-- 2. Document Information --}}
        <div class="card" id="tour-doc-form-details">
            <div class="p-6">
                <h3 class="form-section-title">2 — Document Information</h3>
                <div class="space-y-5">

                    {{-- Document Type --}}
                    <div>
                        <label class="form-label">Document Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            @foreach($documentTypes as $value => $label)
                            <label class="relative flex items-center gap-3 p-3.5 rounded-xl border-2 cursor-pointer transition-all
                                {{ old('document_type', 'incoming') === $value
                                    ? 'border-violet-500 bg-violet-50'
                                    : 'border-slate-200 hover:border-violet-300 hover:bg-slate-50' }}">
                                <input type="radio" name="document_type" value="{{ $value }}"
                                       class="sr-only peer"
                                       {{ old('document_type', 'incoming') === $value ? 'checked' : '' }}>
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0
                                    {{ $value === 'incoming' ? 'bg-blue-50 text-blue-500' : 'bg-emerald-50 text-emerald-500' }}">
                                    @if($value === 'incoming')
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
                                    <p class="text-sm font-semibold text-slate-800">{{ $label }}</p>
                                    <p class="text-xs text-slate-400">
                                        {{ $value === 'incoming' ? 'Received from an office' : 'Sent to a recipient' }}
                                    </p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                        @error('document_type') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    {{-- Document Name --}}
                    <div>
                        <label for="title" class="form-label">Document Name</label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}"
                               class="form-input {{ $errors->has('title') ? 'error' : '' }}" required
                               placeholder="e.g. Memorandum on Budget Allocation for Q3...">
                        @error('title') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    {{-- Office / Origin --}}
                    <div>
                        <label for="received_from" class="form-label">Office / Origin</label>
                        <input type="text" name="received_from" id="received_from" value="{{ old('received_from') }}"
                               class="form-input {{ $errors->has('received_from') ? 'error' : '' }}" required
                               placeholder="e.g. Office of the City Mayor">
                        @error('received_from') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        {{-- Date Received --}}
                        <div>
                            <label for="date_issued" class="form-label">Date Received</label>
                            <input type="date" name="date_issued" id="date_issued"
                                   value="{{ old('date_issued', date('Y-m-d')) }}"
                                   class="form-input {{ $errors->has('date_issued') ? 'error' : '' }}" required>
                            @error('date_issued') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        {{-- Recipient --}}
                        <div>
                            <label for="recipient" class="form-label">Recipient</label>
                            <input type="text" name="recipient" id="recipient" value="{{ old('recipient') }}"
                                   class="form-input {{ $errors->has('recipient') ? 'error' : '' }}" required
                                   placeholder="e.g. City Administrator">
                            @error('recipient') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Deadline --}}
                    <div>
                        <label for="expiration_date" class="form-label">
                            Deadline
                            <span class="text-slate-400 font-normal ml-1">(optional)</span>
                        </label>
                        <input type="date" name="expiration_date" id="expiration_date"
                               value="{{ old('expiration_date') }}"
                               class="form-input {{ $errors->has('expiration_date') ? 'error' : '' }}">
                        @error('expiration_date') <p class="form-error">{{ $message }}</p> @enderror
                        <p class="form-hint mt-1.5 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                            Leave blank if the document has no fixed deadline.
                        </p>
                    </div>

                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3 pb-2">
            <a href="{{ route('documents.index') }}" class="btn-secondary">Cancel</a>
            <button type="submit" id="submit-btn" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                <span id="submit-label">Register Document</span>
            </button>
        </div>
    </div>

    {{-- ── RIGHT: Live summary panel ────────────────────────────────────── --}}
    <div class="sticky top-6 self-start" id="tour-doc-form-preview">
        <div class="card">
            <div class="p-5 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Preview</h3>
                <p class="text-xs text-slate-400 mt-0.5">Updates as you fill in the form</p>
            </div>
            <div class="p-5 space-y-4">

                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Document Type</p>
                    <p id="prev-doc-type" class="text-xs font-semibold text-slate-700">Incoming</p>
                </div>

                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Document Name</p>
                    <p id="prev-title" class="text-sm text-slate-700 leading-snug">—</p>
                </div>

                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Office / Origin</p>
                    <p id="prev-received-from" class="text-xs font-semibold text-slate-700">—</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Date Received</p>
                        <p id="prev-date" class="text-xs font-semibold text-slate-700">—</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Recipient</p>
                        <p id="prev-recipient" class="text-xs font-semibold text-slate-700">—</p>
                    </div>
                </div>

                <div id="prev-expiry-wrap" class="hidden">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Deadline</p>
                    <p id="prev-expiry" class="text-xs font-semibold text-amber-600">—</p>
                </div>

                <div id="prev-file-wrap" class="hidden pt-3 border-t border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Attached File</p>
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 bg-red-50 text-red-400 rounded-lg flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p id="prev-filename" class="text-xs font-semibold text-slate-700 truncate"></p>
                            <p id="prev-filesize" class="text-[11px] text-slate-400"></p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>
</form>

@push('scripts')
<script>
(function () {
    const fileInput     = document.getElementById('pdf_file');
    const dropZone      = document.getElementById('drop-zone');
    const uploadIdle    = document.getElementById('upload-idle');
    const uploadSel     = document.getElementById('upload-selected');
    const selFilename   = document.getElementById('selected-filename');
    const selFilesize   = document.getElementById('selected-filesize');
    const titleInput    = document.getElementById('title');
    const fromInput     = document.getElementById('received_from');
    const dateInput     = document.getElementById('date_issued');
    const recipInput    = document.getElementById('recipient');
    const expiryInput   = document.getElementById('expiration_date');
    const submitBtn     = document.getElementById('submit-btn');
    const submitLabel   = document.getElementById('submit-label');
    const docTypeRadios = document.querySelectorAll('input[name="document_type"]');

    function formatBytes(bytes) {
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
        return (bytes / 1024).toFixed(1) + ' KB';
    }
    function formatDatePreview(val) {
        if (!val) return '—';
        const d = new Date(val + 'T00:00:00');
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    // ── Document type radio cards ─────────────────────────────────────────
    docTypeRadios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            // Update card highlight styles
            docTypeRadios.forEach(function (r) {
                const card = r.closest('label');
                card.classList.toggle('border-violet-500', r.checked);
                card.classList.toggle('bg-violet-50',      r.checked);
                card.classList.toggle('border-slate-200',  !r.checked);
            });
            document.getElementById('prev-doc-type').textContent = this.value.charAt(0).toUpperCase() + this.value.slice(1);
        });
    });

    // ── File selection ────────────────────────────────────────────────────
    fileInput.addEventListener('change', function () {
        if (this.files && this.files.length > 0) {
            const f = this.files[0];
            selFilename.textContent = f.name;
            selFilesize.textContent = formatBytes(f.size);
            uploadIdle.classList.add('hidden');
            uploadSel.classList.remove('hidden');
            dropZone.classList.add('border-violet-400', 'bg-violet-50/30');
            document.getElementById('prev-file-wrap').classList.remove('hidden');
            document.getElementById('prev-filename').textContent = f.name;
            document.getElementById('prev-filesize').textContent = formatBytes(f.size);
        }
    });

    // ── Live preview ──────────────────────────────────────────────────────
    titleInput.addEventListener('input',  () => document.getElementById('prev-title').textContent         = titleInput.value  || '—');
    fromInput.addEventListener('input',   () => document.getElementById('prev-received-from').textContent = fromInput.value   || '—');
    dateInput.addEventListener('change',  () => document.getElementById('prev-date').textContent          = formatDatePreview(dateInput.value));
    recipInput.addEventListener('input',  () => document.getElementById('prev-recipient').textContent     = recipInput.value  || '—');
    expiryInput.addEventListener('change', function () {
        const wrap = document.getElementById('prev-expiry-wrap');
        if (this.value) {
            document.getElementById('prev-expiry').textContent = formatDatePreview(this.value);
            wrap.classList.remove('hidden');
        } else {
            wrap.classList.add('hidden');
        }
    });

    // Initialise preview from old() values (on validation failure redirect)
    document.getElementById('prev-title').textContent         = titleInput.value  || '—';
    document.getElementById('prev-received-from').textContent = fromInput.value   || '—';
    document.getElementById('prev-date').textContent          = formatDatePreview(dateInput.value);
    document.getElementById('prev-recipient').textContent     = recipInput.value  || '—';
    if (expiryInput.value) {
        document.getElementById('prev-expiry').textContent = formatDatePreview(expiryInput.value);
        document.getElementById('prev-expiry-wrap').classList.remove('hidden');
    }

    // ── Submit loading state ──────────────────────────────────────────────
    document.getElementById('doc-form').addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitLabel.textContent = 'Registering…';
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
    });
})();
</script>
@endpush
@endsection
