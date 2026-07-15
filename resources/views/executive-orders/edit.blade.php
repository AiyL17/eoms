@extends('layouts.app')

@section('title', 'Edit — ' . $eo->eo_number)
@section('page-title', 'Edit Executive Order')

@section('breadcrumb')
    <a href="{{ route('executive-orders.index') }}" class="hover:text-violet-600 transition-colors">Executive Orders</a>
    <span class="mx-1 opacity-40">/</span>
    <a href="{{ route('executive-orders.show', $eo) }}" class="hover:text-violet-600 transition-colors">{{ $eo->eo_number }}</a>
    <span class="mx-1 opacity-40">/</span>
    <span class="text-slate-700 font-semibold">Edit</span>
@endsection

@section('content')
<form action="{{ route('executive-orders.update', $eo) }}" method="POST" enctype="multipart/form-data" id="eo-form">
@csrf
@method('PUT')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

    {{-- ── LEFT: Main form ──────────────────────────────────────────────── --}}
    <div class="xl:col-span-2 space-y-5">

        {{-- 1. Status --}}
        <div class="card" id="tour-eo-form-status">
            <div class="p-6">
                <h3 class="form-section-title">1 — Status</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="status" class="form-label">Current Status</label>
                        <select name="status" id="status" class="form-input {{ $errors->has('status') ? 'error' : '' }}" required>
                            @foreach($statuses as $value => $label)
                                <option value="{{ $value }}" {{ old('status', $eo->status) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div id="status-notes-wrap" class="{{ old('status', $eo->status) === 'active' ? 'hidden' : '' }}">
                        <label for="status_notes" class="form-label">
                            Status Notes
                            <span class="text-slate-400 font-normal ml-1">(optional)</span>
                        </label>
                        <input type="text" name="status_notes" id="status_notes"
                               value="{{ old('status_notes', $eo->status_notes) }}"
                               class="form-input {{ $errors->has('status_notes') ? 'error' : '' }}"
                               placeholder="e.g. Pending final review">
                        @error('status_notes') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Document Details --}}
        <div class="card" id="tour-eo-form-basic">
            <div class="p-6">
                <h3 class="form-section-title">2 — Document Details</h3>
                <div class="space-y-5">
                    <div id="tour-eo-title-subject">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" name="title" id="title"
                               value="{{ old('title', $eo->title) }}"
                               class="form-input {{ $errors->has('title') ? 'error' : '' }}" required>
                        @error('title') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="subject" class="form-label">Subject</label>
                        <textarea name="subject" id="subject" rows="2"
                                  class="form-input {{ $errors->has('subject') ? 'error' : '' }}" required>{{ old('subject', $eo->subject) }}</textarea>
                        @error('subject') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="content_summary" class="form-label">
                            Content Summary
                            <span class="text-slate-400 font-normal ml-1">(optional)</span>
                        </label>
                        <textarea name="content_summary" id="content_summary" rows="3"
                                  class="form-input {{ $errors->has('content_summary') ? 'error' : '' }}">{{ old('content_summary', $eo->content_summary) }}</textarea>
                        @error('content_summary') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5" id="tour-eo-dates">
                        <div>
                            <label for="date_issued" class="form-label">Date Issued</label>
                            <input type="date" name="date_issued" id="date_issued"
                                   value="{{ old('date_issued', $eo->date_issued?->format('Y-m-d')) }}"
                                   class="form-input {{ $errors->has('date_issued') ? 'error' : '' }}" required>
                            @error('date_issued') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="date_effective" class="form-label">
                                Date Effective
                                <span class="text-slate-400 font-normal ml-1">(optional)</span>
                            </label>
                            <input type="date" name="date_effective" id="date_effective"
                                   value="{{ old('date_effective', $eo->date_effective?->format('Y-m-d')) }}"
                                   class="form-input {{ $errors->has('date_effective') ? 'error' : '' }}">
                            @error('date_effective') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5" id="tour-eo-signatory">
                        <div>
                            <label for="signed_by" class="form-label">Signed By</label>
                            <input type="text" name="signed_by" id="signed_by"
                                   value="{{ old('signed_by', $eo->signed_by) }}"
                                   class="form-input {{ $errors->has('signed_by') ? 'error' : '' }}" required>
                            @error('signed_by') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="tags" class="form-label">
                                Tags
                                <span class="text-slate-400 font-normal ml-1">(optional)</span>
                            </label>
                            <input type="text" name="tags" id="tags"
                                   value="{{ old('tags', implode(', ', $eo->tags ?? [])) }}"
                                   class="form-input {{ $errors->has('tags') ? 'error' : '' }}"
                                   placeholder="Comma-separated">
                            <p class="form-hint">Comma-separated values.</p>
                            @error('tags') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- E-Signature --}}
                    <div id="tour-eo-signature">
                        <label class="form-label">
                            E-Signature
                            <span class="text-slate-400 font-normal ml-1">(optional)</span>
                        </label>

                        @php $profileSig = auth()->user()->signature_data; @endphp

                        @if($profileSig)
                        {{-- User has a profile signature — checkbox + update button --}}
                        <div class="flex items-center gap-2">
                            <label class="flex-1 flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-slate-50 cursor-pointer hover:bg-violet-50/50 hover:border-violet-200 transition-colors">
                                <input type="checkbox" id="use-profile-sig"
                                       class="w-4 h-4 rounded text-violet-600 accent-violet-600"
                                       {{ old('signature_data', $eo->signature_data) === $profileSig ? 'checked' : '' }}>
                                <img id="sig-preview-thumb" src="{{ $profileSig }}" alt="Your profile signature"
                                     class="h-8 object-contain bg-white rounded border border-slate-100 px-2 shrink-0">
                                <span class="text-sm font-medium text-slate-700">Use my saved profile signature</span>
                            </label>
                            <button type="button" id="open-update-sig"
                                    class="btn-secondary shrink-0 px-3 py-2 text-xs"
                                    title="Update your signature">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                </svg>
                                Update
                            </button>
                        </div>
                        <input type="hidden" name="signature_data" id="signature-data"
                               value="{{ old('signature_data', $eo->signature_data) }}">

                        @else
                        {{-- No profile signature — show draw pad; drawn sig also saves to profile --}}
                        @if($eo->signature_data)
                        <div class="mb-3 p-3 bg-slate-50 rounded-xl border border-slate-200 flex items-center gap-3">
                            <img src="{{ $eo->signature_data }}" alt="Current signature"
                                 class="h-10 object-contain bg-white rounded border border-slate-100 px-2">
                            <p class="text-xs text-slate-500">Current signature on file — draw below to replace.</p>
                        </div>
                        @endif

                        <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden">
                            <canvas id="signature-pad"
                                    class="w-full touch-none block"
                                    style="height: 160px; cursor: crosshair;"></canvas>
                        </div>
                        <input type="hidden" name="signature_data" id="signature-data"
                               value="{{ old('signature_data', $eo->signature_data) }}">
                        <div class="flex items-center justify-between mt-2">
                            <p class="form-hint flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                                Draw a new signature — it will also be saved to your profile.
                            </p>
                            <button type="button" id="clear-signature"
                                    class="text-xs font-semibold text-slate-400 hover:text-red-500 transition-colors flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                Clear
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Replace PDF --}}
        <div class="card" id="tour-eo-form-file">
            <div class="p-6">
                <h3 class="form-section-title">3 — Replace PDF <span class="text-slate-400 font-normal normal-case tracking-normal text-xs ml-1">(optional)</span></h3>

                {{-- Current file display --}}
                <div class="flex items-center gap-3 p-3 mb-4 bg-slate-50 rounded-xl border border-slate-100">
                    <div class="w-8 h-8 bg-red-50 text-red-400 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-700 truncate">{{ $eo->original_filename }}</p>
                        <p class="text-xs text-slate-400">{{ $eo->file_size_formatted }} — current file</p>
                    </div>
                </div>

                {{-- Drop zone --}}
                <div id="drop-zone"
                     class="relative rounded-2xl border-2 border-dashed border-slate-200 p-6 text-center cursor-pointer transition-all hover:border-violet-400 hover:bg-violet-50/30">
                    <input type="file" name="pdf_file" id="pdf_file" accept="application/pdf"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    <div id="upload-idle" class="pointer-events-none">
                        <div class="w-11 h-11 bg-slate-100 text-slate-400 rounded-xl flex items-center justify-center mx-auto mb-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-slate-600">Click to upload a replacement PDF</p>
                        <p class="text-xs text-slate-400 mt-0.5">Leave empty to keep the current file</p>
                    </div>
                    <div id="upload-selected" class="hidden pointer-events-none">
                        <div class="w-11 h-11 bg-emerald-50 text-emerald-500 rounded-xl flex items-center justify-center mx-auto mb-2">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <p id="selected-filename" class="text-sm font-bold text-slate-800 truncate max-w-xs mx-auto"></p>
                        <p id="selected-filesize" class="text-xs text-slate-400 mt-0.5"></p>
                        <p class="text-xs text-violet-600 font-medium mt-1.5">Click to change selection</p>
                    </div>
                </div>

                @error('pdf_file') <p class="form-error mt-2">{{ $message }}</p> @enderror
                <p class="form-hint mt-3 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                    Only PDF files are accepted. Maximum file size is <strong class="text-slate-600">20 MB</strong>.
                </p>
            </div>
        </div>

        {{-- 4. Audit reason --}}
        <div class="card" id="tour-eo-form-reason">
            <div class="p-6">
                <h3 class="form-section-title">4 — Reason for Edit</h3>
                <input type="text" name="log_notes" id="log_notes"
                       class="form-input"
                       placeholder="Briefly describe what was changed and why">
                <p class="form-hint mt-2 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
                    This note is optional but will be saved to the audit log for this record.
                </p>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex justify-end gap-3 pb-2">
            <a href="{{ route('executive-orders.show', $eo) }}" class="btn-secondary">Cancel</a>
            <button type="submit" id="submit-btn" class="btn-primary">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                <span id="submit-label">Save Changes</span>
            </button>
        </div>
    </div>

    {{-- ── RIGHT: Live preview panel ────────────────────────────────────── --}}
    <div class="sticky top-6 self-start space-y-5" id="tour-eo-form-preview">
        <div class="card">
            <div class="p-5 border-b border-slate-100">
                <h3 class="text-sm font-bold text-slate-800">Preview</h3>
                <p class="text-xs text-slate-400 mt-0.5">Updates as you edit</p>
            </div>
            <div class="p-5 space-y-4 overflow-y-auto" style="max-height: calc(100vh - 160px);">

                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">EO Number</p>
                    <p class="text-base font-bold text-slate-800 font-mono">{{ $eo->eo_number }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Title</p>
                    <p id="prev-title" class="text-sm text-slate-700 leading-snug break-words">{{ $eo->title }}</p>
                </div>

                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Subject</p>
                    <p id="prev-subject" class="text-xs text-slate-500 leading-relaxed break-words">{{ $eo->subject }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Date Issued</p>
                        <p id="prev-date" class="text-xs font-semibold text-slate-700">{{ $eo->date_issued->format('M d, Y') }}</p>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Status</p>
                        <p id="prev-status" class="text-xs font-semibold text-slate-700">{{ $eo->status_label }}</p>
                    </div>
                </div>

                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Signed By</p>
                    <div class="relative pt-6">
                        <p id="prev-signed" class="text-xs font-semibold text-slate-700 break-words pt-0.5">{{ $eo->signed_by }}</p>
                        <img id="prev-signature-img"
                             src="{{ $eo->signature_data ?? '' }}"
                             alt="E-Signature"
                             class="absolute left-0 w-full h-6 object-contain object-left {{ $eo->signature_data ? '' : 'hidden' }}"
                             style="bottom: 4px;">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-2">Attached File</p>
                    <div id="file-display" class="flex items-center gap-2">
                        <div class="w-7 h-7 bg-red-50 text-red-400 rounded-lg flex items-center justify-center shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                        </div>
                        <div class="min-w-0">
                            <p id="prev-filename" class="text-xs font-semibold text-slate-700 truncate">{{ $eo->original_filename }}</p>
                            <p id="prev-filesize" class="text-[11px] text-slate-400">{{ $eo->file_size_formatted }}</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Last edited info --}}
        <div class="bg-slate-50 rounded-2xl border border-slate-100 p-4 space-y-2 text-xs text-slate-500">
            <div class="flex items-center justify-between">
                <span class="font-medium">Uploaded by</span>
                <span class="font-semibold text-slate-700">{{ $eo->uploader->name ?? 'System' }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="font-medium">Created</span>
                <span class="font-semibold text-slate-700">{{ $eo->created_at->format('M d, Y') }}</span>
            </div>
            @if($eo->updater)
            <div class="flex items-center justify-between">
                <span class="font-medium">Last edited by</span>
                <span class="font-semibold text-slate-700">{{ $eo->updater->name }}</span>
            </div>
            @endif
            <div class="flex items-center justify-between">
                <span class="font-medium">Last updated</span>
                <span class="font-semibold text-slate-700">{{ $eo->updated_at->diffForHumans() }}</span>
            </div>
        </div>
    </div>

</div>
</form>

@if(auth()->user()->signature_data)
{{-- ── Update Signature Modal ── --}}
<div id="update-sig-modal"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 hidden"
     role="dialog" aria-modal="true">
    <div id="update-sig-backdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <div>
                <h3 class="text-sm font-bold text-slate-800">Update Your Signature</h3>
                <p class="text-xs text-slate-400 mt-0.5">This will replace your saved profile signature.</p>
            </div>
            <button type="button" id="close-update-sig"
                    class="w-8 h-8 flex items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-6">
            <div class="rounded-2xl border-2 border-dashed border-slate-200 bg-white overflow-hidden">
                <canvas id="update-sig-canvas" class="w-full touch-none block" style="height: 200px; cursor: crosshair;"></canvas>
            </div>
            <div class="flex items-center justify-between mt-3">
                <p class="form-hint flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    Draw using mouse or touch.
                </p>
                <button type="button" id="clear-update-sig"
                        class="text-xs font-semibold text-slate-400 hover:text-red-500 transition-colors flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Clear
                </button>
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/60">
            <button type="button" id="cancel-update-sig" class="btn-secondary">Cancel</button>
            <button type="button" id="save-update-sig" class="btn-primary" disabled>
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                </svg>
                Save & Use
            </button>
        </div>
    </div>
</div>
@endif

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
(function () {
    const sigData       = document.getElementById('signature-data');
    const prevSigImg    = document.getElementById('prev-signature-img');
    const useProfileChk = document.getElementById('use-profile-sig');
    const submitBtn     = document.getElementById('submit-btn');
    const submitLabel   = document.getElementById('submit-label');

    @if(auth()->user()->signature_data)
    // ── HAS profile signature: checkbox + update modal ────────────────────
    let profileSigData = @json(auth()->user()->signature_data);

    const thumb      = document.getElementById('sig-preview-thumb');
    const openBtn    = document.getElementById('open-update-sig');
    const modal      = document.getElementById('update-sig-modal');
    const backdrop   = document.getElementById('update-sig-backdrop');
    const closeBtn   = document.getElementById('close-update-sig');
    const cancelBtn  = document.getElementById('cancel-update-sig');
    const saveBtn    = document.getElementById('save-update-sig');
    const clearBtn   = document.getElementById('clear-update-sig');
    const canvas     = document.getElementById('update-sig-canvas');

    const updatePad = new SignaturePad(canvas, {
        minWidth: 0.8, maxWidth: 2.5,
        penColor: '#1e293b', backgroundColor: 'rgba(0,0,0,0)',
    });

    function resizeUpdateCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width  = canvas.offsetWidth  * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        updatePad.clear();
        saveBtn.disabled = true;
    }

    updatePad.addEventListener('endStroke', () => {
        saveBtn.disabled = updatePad.isEmpty();
    });

    function openModal() {
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => resizeUpdateCanvas());
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        updatePad.clear();
        saveBtn.disabled = true;
    }

    openBtn.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    backdrop.addEventListener('click', closeModal);
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });
    window.addEventListener('resize', () => {
        if (!modal.classList.contains('hidden')) resizeUpdateCanvas();
    });

    clearBtn.addEventListener('click', () => {
        updatePad.clear();
        saveBtn.disabled = true;
    });

    saveBtn.addEventListener('click', () => {
        if (updatePad.isEmpty()) return;
        const newSig = updatePad.toDataURL('image/png');

        // Persist to profile via AJAX
        fetch('{{ route('profile.update-signature') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-HTTP-Method-Override': 'PATCH',
            },
            body: JSON.stringify({ signature_data: newSig, _method: 'PATCH' }),
        });

        // Update local state
        profileSigData = newSig;
        if (thumb) thumb.src = newSig;

        // If checkbox is checked, also apply to this EO
        if (useProfileChk && useProfileChk.checked) {
            sigData.value = newSig;
            prevSigImg.src = newSig;
            prevSigImg.classList.remove('hidden');
        }

        closeModal();
    });

    // Sync preview on page load if checkbox starts checked
    if (useProfileChk && useProfileChk.checked) {
        sigData.value = profileSigData;
        prevSigImg.src = profileSigData;
        prevSigImg.classList.remove('hidden');
    }

    if (useProfileChk) {
        useProfileChk.addEventListener('change', function () {
            if (this.checked) {
                sigData.value = profileSigData;
                prevSigImg.src = profileSigData;
                prevSigImg.classList.remove('hidden');
            } else {
                sigData.value = sigData.defaultValue;
                const orig = sigData.defaultValue;
                if (orig) {
                    prevSigImg.src = orig;
                    prevSigImg.classList.remove('hidden');
                } else {
                    prevSigImg.classList.add('hidden');
                    prevSigImg.src = '';
                }
            }
        });
    }

    @else
    // ── NO profile signature: draw pad ────────────────────────────────────
    const canvas   = document.getElementById('signature-pad');
    const clearBtn = document.getElementById('clear-signature');

    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width  = canvas.offsetWidth  * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        signaturePad.clear();
    }

    const signaturePad = new SignaturePad(canvas, {
        minWidth: 0.8, maxWidth: 2.5,
        penColor: '#1e293b', backgroundColor: 'rgba(0,0,0,0)',
    });

    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    signaturePad.addEventListener('endStroke', () => {
        const url = signaturePad.toDataURL('image/png');
        prevSigImg.src = url;
        prevSigImg.classList.remove('hidden');
    });

    clearBtn.addEventListener('click', () => {
        signaturePad.clear();
        sigData.value = 'CLEAR';
        prevSigImg.classList.add('hidden');
        prevSigImg.src = '';
    });

    document.getElementById('eo-form').addEventListener('submit', function () {
        if (!signaturePad.isEmpty()) {
            sigData.value = signaturePad.toDataURL('image/png');
        }
    });
    @endif

    // ── File replacement ──────────────────────────────────────────────────
    const fileInput    = document.getElementById('pdf_file');
    const dropZone     = document.getElementById('drop-zone');
    const uploadIdle   = document.getElementById('upload-idle');
    const uploadSel    = document.getElementById('upload-selected');
    const selFilename  = document.getElementById('selected-filename');
    const selFilesize  = document.getElementById('selected-filesize');

    const statusSel    = document.getElementById('status');
    const statusNotes  = document.getElementById('status-notes-wrap');
    const titleInput   = document.getElementById('title');
    const subjectInput = document.getElementById('subject');
    const dateInput    = document.getElementById('date_issued');
    const signedInput  = document.getElementById('signed_by');

    function formatBytes(bytes) {
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
        return (bytes / 1024).toFixed(1) + ' KB';
    }

    function formatDatePreview(val) {
        if (!val) return '—';
        const d = new Date(val + 'T00:00:00');
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    fileInput.addEventListener('change', function () {
        if (this.files && this.files.length > 0) {
            const f = this.files[0];
            selFilename.textContent = f.name;
            selFilesize.textContent = formatBytes(f.size);
            uploadIdle.classList.add('hidden');
            uploadSel.classList.remove('hidden');
            dropZone.classList.add('border-violet-400', 'bg-violet-50/30');
            document.getElementById('prev-filename').textContent = f.name;
            document.getElementById('prev-filesize').textContent = formatBytes(f.size);
        }
    });

    statusSel.addEventListener('change', function () {
        statusNotes.classList.toggle('hidden', this.value === 'active');
        document.getElementById('prev-status').textContent = this.options[this.selectedIndex].text;
    });

    titleInput.addEventListener('input',   () => document.getElementById('prev-title').textContent   = titleInput.value   || '—');
    subjectInput.addEventListener('input', () => document.getElementById('prev-subject').textContent = subjectInput.value || '—');
    dateInput.addEventListener('change',   () => document.getElementById('prev-date').textContent    = formatDatePreview(dateInput.value));
    signedInput.addEventListener('input',  () => document.getElementById('prev-signed').textContent  = signedInput.value  || '—');

    // ── Submit loading state ──────────────────────────────────────────────
    document.getElementById('eo-form').addEventListener('submit', function () {
        submitBtn.disabled = true;
        submitLabel.textContent = 'Saving…';
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');
    });
})();
</script>
@endpush
@endsection
