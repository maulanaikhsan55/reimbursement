@extends('layouts.app')

@section('title', 'Tambah Pengguna - Humplus Reimbursement')
@section('page-title', 'Tambah Pengguna Baru')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .form-help-text {
        display: block;
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 0.35rem;
        line-height: 1.4;
    }

    .role-description {
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 0.5rem;
        padding: 0.75rem 1rem;
        background: #f1f5f9;
        border-radius: 1rem;
        border-left: 4px solid #425d87;
        line-height: 1.5;
    }
    
    .alert-warning-simple {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #92400e;
        background: #fffbeb;
        padding: 0.75rem;
        border-radius: 0.5rem;
        margin-top: 0.5rem;
        font-size: 0.85rem;
        border: 1px solid #fef3c7;
    }

    /* Hierarchy Preview Styles */
    .hierarchy-container {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: white;
        padding: 1.25rem;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        margin-top: 0.5rem;
    }

    .hierarchy-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.35rem;
        flex: 1;
    }

    .step-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }

    .step-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: #64748b;
        text-align: center;
        white-space: nowrap;
    }

    .hierarchy-arrow {
        color: #cbd5e1;
    }

    .current-user .step-icon {
        background: #425d87;
        color: white;
        border-color: #425d87;
    }

    .current-user .step-label {
        color: #425d87;
    }

    .next-approver.active .step-icon {
        background: #10b981;
        color: white;
        border-color: #10b981;
    }

    .next-approver.active .step-label {
        color: #059669;
    }

    /* TomSelect Modern Theme Overrides */
    .ts-wrapper {
        width: 100%;
    }
    .ts-control {
        border-radius: 0.82rem !important;
        padding: 0.625rem 1.25rem !important;
        border-color: #e2e8f0 !important;
        font-family: inherit !important;
        font-size: 0.875rem !important;
        transition: all 0.2s ease !important;
        box-shadow: none !important;
    }
    .ts-wrapper.focus .ts-control {
        border-color: #425d87 !important;
        box-shadow: 0 0 0 3px rgba(66, 93, 135, 0.1) !important;
    }
    .ts-dropdown {
        border-radius: 1rem !important;
        margin-top: 0.5rem !important;
        border-color: #e2e8f0 !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        padding: 0.5rem !important;
    }
    .ts-dropdown .option {
        padding: 0.5rem 0.75rem !important;
        border-radius: 0.5rem !important;
    }
    .ts-dropdown .active {
        background-color: #f1f5f9 !important;
        color: #1e293b !important;
    }

    /* Input & Form Control Overrides */
    .form-control {
        border-radius: 0.82rem !important;
        padding: 0.625rem 1.25rem !important;
        border-color: #e2e8f0 !important;
        font-size: 0.875rem !important;
    }

    .form-control:focus {
        border-color: #425d87 !important;
        box-shadow: 0 0 0 3px rgba(66, 93, 135, 0.1) !important;
    }

    .form-label {
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.5rem;
        font-size: 0.85rem;
        margin-left: 0.75rem;
    }

    .btn-modern {
        border-radius: 0.82rem !important;
    }

    .icon-leading {
        margin-right: 8px;
    }

    .is-hidden {
        display: none;
    }

    .form-group-span-all {
        grid-column: 1 / -1;
    }

    .optional-note {
        color: #6b7280;
        font-size: 0.85rem;
        display: none;
    }

    .copy-btn-inline {
        display: none;
        position: absolute;
        right: 3rem;
        top: 50%;
        transform: translateY(-50%);
    }

    .side-caption {
        font-size: 0.8rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.75rem;
    }

    .icon-shrink {
        flex-shrink: 0;
    }

    .password-wrapper {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        position: relative;
        width: 100%;
    }

    .password-wrapper .form-control {
        flex: 1;
        padding-right: 3rem;
    }

    .password-wrapper .password-toggle {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
        padding: 0.35rem;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s ease;
    }

    .password-wrapper .password-toggle:hover {
        color: #425d87;
    }

    .password-wrapper .eye-icon {
        width: 18px;
        height: 18px;
    }

    .copy-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        font-size: 0.75rem;
        border: 1px solid #e2e8f0;
        background: white;
        color: #64748b;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .copy-btn:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
    }

    .copy-btn.copied {
        background: #10b981;
        color: white;
        border-color: #10b981;
    }

    .password-strength {
        display: flex;
        gap: 0.25rem;
        margin-top: 0.5rem;
        height: 4px;
        padding: 0 1rem;
    }

    .strength-bar {
        flex: 1;
        height: 4px;
        border-radius: 2px;
        background: #e2e8f0;
    }

    .strength-bar.weak { background: #ef4444; }
    .strength-bar.medium { background: #f59e0b; }
    .strength-bar.strong { background: #10b981; }

    .password-info {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 0.35rem;
    }

    /* Split Container Layout */
    .unified-form-card {
        background: white;
        border-radius: 1.24rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .unified-form-body {
        padding: 2rem;
    }

    .form-split-grid {
        display: grid;
        grid-template-columns: 1.65fr 0.95fr;
        gap: 2rem;
        align-items: start;
    }

    @media (max-width: 1024px) {
        .form-split-grid {
            grid-template-columns: 1fr;
            gap: 2.5rem;
        }
    }

    .form-side-card {
        background: linear-gradient(165deg, #ffffff 0%, #f7fbff 100%);
        border-radius: 1.1rem;
        padding: 1.25rem;
        border: 1px solid #dce7f6;
        position: sticky;
        top: 2rem;
    }

    .form-section-header {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        margin-bottom: 1.2rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid #e8eef7;
    }

    .form-section-header svg {
        color: #425d87;
        background: #eef2f7;
        padding: 0.6rem;
        border-radius: 0.85rem;
        width: 38px;
        height: 38px;
    }

    .form-section-header h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .form-grid-double {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 768px) {
        .form-grid-double {
            grid-template-columns: 1fr;
        }
        .unified-form-body {
            padding: 1.5rem;
        }
    }

    .side-section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: #475569;
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 1rem;
    }

    .hierarchy-depth-warning {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        color: #b45309;
        background: #fffbeb;
        padding: 1rem;
        border-radius: 1rem;
        border: 1px solid #fef3c7;
        font-size: 0.85rem;
        line-height: 1.5;
    }

    .btn-modern-primary {
        background: #425d87 !important;
        box-shadow: 0 4px 12px rgba(66, 93, 135, 0.25) !important;
    }

    .btn-modern-primary:hover {
        background: #364d70 !important;
        box-shadow: 0 8px 20px rgba(66, 93, 135, 0.35) !important;
    }

    .user-form-shell {
        overflow: visible;
    }

    .user-form-shell > .section-header {
        align-items: flex-start;
        margin-bottom: 1.35rem;
    }

    .user-shell-header-copy {
        display: grid;
        gap: 0.25rem;
    }

    .user-shell-subtitle {
        margin: 0;
        font-size: 0.92rem;
        color: #64748b;
    }

    .user-editor-form {
        margin: 0;
    }

    .user-form-shell .link-back {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.62rem 1rem;
        border-radius: 0.95rem;
        border: 1px solid #d6e0ef;
        background: #f8fbff;
        color: #425d87;
        text-decoration: none;
        font-weight: 700;
        transition: all 0.2s ease;
    }

    .user-form-shell .link-back:hover {
        background: #eef4ff;
        border-color: #bfd0eb;
        color: #2f4a72;
    }

    .user-form-shell .link-back svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }

    /* New clean split layout */
    .user-editor-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(320px, 0.95fr);
        gap: 1.25rem;
        align-items: start;
    }

    .user-main-column {
        background: #ffffff;
        border: 1px solid #dbe3ee;
        border-radius: 1rem;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.04);
        padding: 1.35rem;
    }

    .user-form-card {
        background: transparent;
        border: 0;
        border-radius: 0;
        box-shadow: none;
        padding: 0;
    }

    .user-form-card + .user-form-card {
        margin-top: 1.2rem;
        padding-top: 1.2rem;
        border-top: 1px solid #e5edf7;
    }

    .user-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 1.2rem;
        padding-top: 1.2rem;
        border-top: 1px solid #e2e8f0;
    }

    .smart-preview-card {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px dashed #cbd5e1;
    }

    .smart-preview-list {
        display: grid;
        gap: 0.55rem;
        margin: 0;
    }

    .smart-preview-item {
        display: flex;
        justify-content: space-between;
        gap: 0.75rem;
        font-size: 0.82rem;
    }

    .smart-preview-label {
        color: #64748b;
        font-weight: 600;
    }

    .smart-preview-value {
        color: #1e293b;
        font-weight: 700;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .completeness-track {
        margin-top: 0.9rem;
        background: #e2e8f0;
        border-radius: 999px;
        height: 7px;
        overflow: hidden;
    }

    .completeness-fill {
        height: 100%;
        width: 0;
        background: linear-gradient(90deg, #3b82f6, #10b981);
        transition: width 0.2s ease;
    }

    .completeness-text {
        margin-top: 0.45rem;
        font-size: 0.76rem;
        color: #64748b;
    }

    .inline-field-helper {
        display: block;
    }

    .email-availability {
        display: block;
        margin-top: 0.35rem;
        font-size: 0.78rem;
        font-weight: 600;
    }

    .email-availability.is-checking {
        color: #0c4a6e;
    }

    .email-availability.is-available {
        color: #166534;
    }

    .email-availability.is-unavailable {
        color: #b91c1c;
    }

    .change-state {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #475569;
        padding: 0.6rem 0.75rem;
        font-size: 0.82rem;
        font-weight: 600;
    }

    .change-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #94a3b8;
        flex-shrink: 0;
    }

    .change-state.is-clean .change-dot {
        background: #22c55e;
    }

    .change-state.is-dirty {
        border-color: #fed7aa;
        background: #fff7ed;
        color: #9a3412;
    }

    .change-state.is-dirty .change-dot {
        background: #f97316;
    }

    .change-state.is-saving {
        border-color: #bfdbfe;
        background: #eff6ff;
        color: #1d4ed8;
    }

    .change-state.is-saving .change-dot {
        background: #3b82f6;
    }

    .tips-list {
        margin: 0.8rem 0 0;
        padding-left: 1rem;
        font-size: 0.8rem;
        color: #475569;
    }

    .tips-list li + li {
        margin-top: 0.4rem;
    }

    @media (max-width: 1200px) {
        .user-editor-grid {
            grid-template-columns: 1fr;
        }

        .form-side-card {
            position: static;
        }
    }

    @media (max-width: 768px) {
        .user-form-shell > .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.9rem;
        }

        .user-main-column {
            padding: 1rem;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Tambah Pengguna Baru" subtitle="Kelola akses dan informasi pengguna dalam satu langkah" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content">
            <section class="modern-section user-form-shell">
                <div class="section-header">
                    <div class="user-shell-header-copy">
                        <h2 class="section-title">Formulir Data Pengguna</h2>
                        <p class="user-shell-subtitle">Lengkapi data akun, organisasi, dan informasi tambahan dalam satu container utama.</p>
                    </div>
                    <a href="{{ route('finance.masterdata.users.index') }}" class="link-back">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Kembali
                    </a>
                </div>

                <form method="POST" action="{{ route('finance.masterdata.users.store') }}" id="userForm" class="user-editor-form">
                @csrf
                
                <div class="user-editor-grid">
                    <div class="user-main-column">
                        <!-- Account Data Section -->
                        <div class="user-form-card">
                                    <div class="form-section-header">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        <h3>Data Akun</h3>
                                    </div>
                                    
                                    <div class="form-grid-double">
                                        <div class="form-group">
                                            <label for="name" class="form-label">Nama Lengkap <span class="required">*</span></label>
                                            <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Budi Santoso" required autocomplete="name">
                                            @error('name') <small class="form-error ml-3">{{ $message }}</small> @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="email" class="form-label">Email <span class="required">*</span></label>
                                            <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="contoh@humplus.id" required autocomplete="email">
                                            @error('email') <small class="form-error ml-3">{{ $message }}</small> @enderror
                                            <small id="emailAvailabilityHint" class="form-help-text ml-3">Gunakan email resmi perusahaan.</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="password" class="form-label">Password <span class="required">*</span></label>
                                            <div class="password-wrapper">
                                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 8 karakter" required autocomplete="new-password">
                                                <button type="button" class="password-toggle" onclick="togglePassword('password', this)">
                                                    <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="password-strength mt-2 is-hidden" id="passwordStrength">
                                                <div class="strength-bar"></div>
                                                <div class="strength-bar"></div>
                                                <div class="strength-bar"></div>
                                            </div>
                                            <div class="password-info ml-3" id="passwordInfo"></div>
                                        </div>

                                        <div class="form-group">
                                            <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="required">*</span></label>
                                            <div class="password-wrapper">
                                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Ulangi password" required autocomplete="new-password">
                                                <button type="button" class="password-toggle" onclick="togglePassword('password_confirmation', this)">
                                                    <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                </button>
                                                <button type="button" class="copy-btn copy-btn-inline" id="copyBtn" onclick="copyPassword()">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/>
                                                    </svg>
                                                    <span>Salin</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <button type="button" class="btn-modern btn-modern-secondary w-100" onclick="generatePassword()" id="generateBtn">
                                            <svg class="icon-leading" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 2.2"/>
                                            </svg>
                                            Buat Password Kuat Otomatis
                                        </button>
                                    </div>
                        </div>

                        <!-- Organization Data Section -->
                        <div class="user-form-card">
                                    <div class="form-section-header">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        </svg>
                                        <h3>Data Organisasi</h3>
                                    </div>

                                    <div class="form-grid-double">
                                        <div class="form-group">
                                            <label for="departemen_id" class="form-label">Departemen <span class="required">*</span></label>
                                            <select id="departemen_id" name="departemen_id" class="form-control @error('departemen_id') is-invalid @enderror" required>
                                                <option value="">-- Pilih Departemen --</option>
                                                @foreach ($departemen->sortBy('nama_departemen') as $dept)
                                                    <option value="{{ $dept->departemen_id }}" {{ old('departemen_id') == $dept->departemen_id ? 'selected' : '' }}>
                                                        {{ $dept->nama_departemen }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('departemen_id') <small class="form-error ml-3">{{ $message }}</small> @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="role" class="form-label">Peran (Role) <span class="required">*</span></label>
                                            <select id="role" name="role" class="form-control @error('role') is-invalid @enderror" required>
                                                <option value="pegawai" {{ old('role') === 'pegawai' ? 'selected' : '' }}>Pegawai</option>
                                                <option value="atasan" {{ old('role') === 'atasan' ? 'selected' : '' }}>Atasan (Supervisor)</option>
                                                <option value="finance" {{ old('role') === 'finance' ? 'selected' : '' }}>Finance</option>
                                            </select>
                                            @error('role') <small class="form-error ml-3">{{ $message }}</small> @enderror
                                            <small id="roleFieldHelper" class="form-help-text ml-3 inline-field-helper">Pilih peran agar sistem menyesuaikan alur approval.</small>
                                        </div>

                                        <div class="form-group">
                                            <label for="jabatan" class="form-label">Jabatan <span class="required">*</span></label>
                                            <input type="text" id="jabatan" name="jabatan" class="form-control @error('jabatan') is-invalid @enderror" value="{{ old('jabatan') }}" placeholder="Contoh: Staff Admin, Manager IT" required>
                                            @error('jabatan') <small class="form-error ml-3">{{ $message }}</small> @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="is_active" class="form-label">Status Akun</label>
                                            <select id="is_active" name="is_active" class="form-control">
                                                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Aktif</option>
                                                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Nonaktif</option>
                                            </select>
                                        </div>

                                        <div class="form-group form-group-span-all" id="atasan_group">
                                            <label for="atasan_id" class="form-label">
                                                Atasan Langsung <span id="atasan_required" class="required">*</span>
                                                <span id="atasan_optional" class="optional-note">(Opsional)</span>
                                            </label>
                                            <select id="atasan_id" name="atasan_id" class="form-control">
                                                <option value="">-- Pilih Atasan --</option>
                                                @foreach ($supervisors as $supervisor)
                                                    <option value="{{ $supervisor->id }}" 
                                                            data-dept="{{ $supervisor->departemen_id }}"
                                                            data-atasan-id="{{ $supervisor->atasan_id }}"
                                                            data-supervisor-name="{{ $supervisor->name }}"
                                                            {{ old('atasan_id') == $supervisor->id ? 'selected' : '' }}>
                                                        {{ $supervisor->name }} ({{ $supervisor->departemen->nama_departemen ?? '-' }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div id="atasanWarning" class="alert-warning-simple mt-2 is-hidden">
                                                <x-icon name="alert-circle" class="w-4 h-4" />
                                                <span>Departemen ini belum memiliki user dengan role Atasan.</span>
                                            </div>
                                            <small id="atasanFieldHelper" class="form-help-text ml-3 inline-field-helper">Wajib untuk role Pegawai.</small>
                                            @error('atasan_id') <small class="form-error ml-3">{{ $message }}</small> @enderror
                                        </div>
                                    </div>
                        </div>

                        <div class="user-form-card">
                            <div class="form-section-header">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                    <line x1="2" y1="10" x2="22" y2="10"></line>
                                </svg>
                                <h3>Data Tambahan</h3>
                            </div>

                            <div class="form-grid-double">
                                <div class="form-group">
                                    <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                                    <input type="tel" id="nomor_telepon" name="nomor_telepon" class="form-control @error('nomor_telepon') is-invalid @enderror" value="{{ old('nomor_telepon') }}" placeholder="08123456789" autocomplete="tel">
                                    @error('nomor_telepon') <small class="form-error ml-3">{{ $message }}</small> @enderror
                                </div>

                                <div class="form-group">
                                    <label for="nama_bank" class="form-label">Nama Bank</label>
                                    <input type="text" id="nama_bank" name="nama_bank" class="form-control @error('nama_bank') is-invalid @enderror" value="{{ old('nama_bank') }}" placeholder="Contoh: BCA, Mandiri">
                                    @error('nama_bank') <small class="form-error ml-3">{{ $message }}</small> @enderror
                                </div>

                                <div class="form-group form-group-span-all">
                                    <label for="nomor_rekening" class="form-label">Nomor Rekening</label>
                                    <input type="text" id="nomor_rekening" name="nomor_rekening" class="form-control @error('nomor_rekening') is-invalid @enderror" value="{{ old('nomor_rekening') }}" placeholder="Nomor rekening bank">
                                    <small class="form-help-text ml-3">Opsional saat create, bisa dilengkapi lagi di halaman edit.</small>
                                    @error('nomor_rekening') <small class="form-error ml-3">{{ $message }}</small> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="user-form-actions">
                            <a href="{{ route('finance.masterdata.users.index') }}" class="btn-modern btn-modern-secondary px-5">
                                Batal
                            </a>
                            <button type="submit" class="btn-modern btn-modern-primary px-5">
                                <svg class="icon-leading" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                Simpan Pengguna
                            </button>
                        </div>
                    </div>

                            <!-- Right Side: Preview & Info -->
                            <div class="form-sidebar-content">
                                <div class="form-side-card">
                                    <div class="side-section-title">
                                        <svg class="icon-leading" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                        </svg>
                                        Bantuan & Preview
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div class="side-caption">Deskripsi Peran</div>
                                        <div id="roleDescription" class="role-description">Pilih peran untuk melihat deskripsi...</div>
                                    </div>
                                    
                                    <div id="hierarchyPreview" class="is-hidden">
                                        <div class="side-caption">Alur Persetujuan</div>
                                        <div class="hierarchy-container">
                                            <div class="hierarchy-step current-user">
                                                <div class="step-icon">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                                </div>
                                                <div class="step-label">Pengaju</div>
                                            </div>
                                            <div class="hierarchy-arrow">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                            </div>
                                            <div class="hierarchy-step next-approver" id="approverStep">
                                                <div class="step-icon">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle></svg>
                                                </div>
                                                <div class="step-label" id="approverLabel">Atasan</div>
                                            </div>
                                            <div class="hierarchy-arrow">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                            </div>
                                            <div class="hierarchy-step">
                                                <div class="step-icon">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                                </div>
                                                <div class="step-label">Finance</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="hierarchyDepthWarning" class="hierarchy-depth-warning mt-4 is-hidden">
                                        <svg class="icon-shrink" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                        <div id="hierarchyDepthMessage"></div>
                                    </div>

                                    <div class="smart-preview-card">
                                        <div class="side-caption">Status Perubahan</div>
                                        <div id="changeState" class="change-state is-clean">
                                            <span class="change-dot"></span>
                                            <span id="changeStateText">Belum ada perubahan.</span>
                                        </div>
                                    </div>

                                    <div class="smart-preview-card">
                                        <div class="side-caption">Ringkasan Input</div>
                                        <div class="smart-preview-list">
                                            <div class="smart-preview-item"><span class="smart-preview-label">Nama</span><span class="smart-preview-value" id="summaryName">-</span></div>
                                            <div class="smart-preview-item"><span class="smart-preview-label">Role</span><span class="smart-preview-value" id="summaryRole">-</span></div>
                                            <div class="smart-preview-item"><span class="smart-preview-label">Departemen</span><span class="smart-preview-value" id="summaryDept">-</span></div>
                                            <div class="smart-preview-item"><span class="smart-preview-label">Atasan</span><span class="smart-preview-value" id="summaryAtasan">-</span></div>
                                            <div class="smart-preview-item"><span class="smart-preview-label">Status Akun</span><span class="smart-preview-value" id="summaryStatus">Aktif</span></div>
                                        </div>
                                        <div class="completeness-track">
                                            <div id="completenessFill" class="completeness-fill"></div>
                                        </div>
                                        <p id="completenessText" class="completeness-text">Lengkapi field wajib untuk menyimpan pengguna.</p>
                                    </div>

                                    <div class="smart-preview-card">
                                        <div class="side-caption">Rekomendasi Smart</div>
                                        <ul class="tips-list">
                                            <li>Gunakan role paling minimal sesuai kebutuhan akses user.</li>
                                            <li>Isi atasan langsung untuk menjaga alur approval tetap jelas.</li>
                                            <li>Pastikan email resmi aktif agar notifikasi tidak hilang.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
            </form>
            </section>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
(function() {
    window.togglePassword = function(inputId, btn) {
        const input = document.getElementById(inputId);
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        
        btn.innerHTML = isPassword ? `
            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
        ` : `
            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        `;
    }

    window.generatePassword = function() {
        const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        const lowercase = 'abcdefghijklmnopqrstuvwxyz';
        const numbers = '0123456789';
        const symbols = '!@#$%^&*';
        const allChars = uppercase + lowercase + numbers + symbols;
        
        let password = '';
        password += uppercase[Math.floor(Math.random() * uppercase.length)];
        password += lowercase[Math.floor(Math.random() * lowercase.length)];
        password += numbers[Math.floor(Math.random() * numbers.length)];
        password += symbols[Math.floor(Math.random() * symbols.length)];
        
        for (let i = 4; i < 12; i++) {
            password += allChars[Math.floor(Math.random() * allChars.length)];
        }
        
        password = password.split('').sort(() => Math.random() - 0.5).join('');
        
        document.getElementById('password').value = password;
        document.getElementById('password_confirmation').value = password;
        window.updatePasswordStrength(password);
        document.getElementById('copyBtn').style.display = 'flex';
        if (typeof window.__updateUserFormPreview === 'function') {
            window.__updateUserFormPreview();
        }
    }

    window.copyPassword = function() {
        const password = document.getElementById('password').value;
        if (!password) return;
        
        navigator.clipboard.writeText(password).then(() => {
            const copyBtn = document.getElementById('copyBtn');
            const originalHtml = copyBtn.innerHTML;
            copyBtn.classList.add('copied');
            copyBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg> <span>Tersalin!</span>';
            setTimeout(() => {
                copyBtn.classList.remove('copied');
                copyBtn.innerHTML = originalHtml;
            }, 2000);
        });
    }

    window.updatePasswordStrength = function(password) {
        const strengthDiv = document.getElementById('passwordStrength');
        const infoDiv = document.getElementById('passwordInfo');
        if (!strengthDiv) return;
        const bars = strengthDiv.querySelectorAll('.strength-bar');
        
        if (!password) {
            strengthDiv.style.display = 'none';
            if (infoDiv) infoDiv.textContent = '';
            return;
        }
        
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[!@#$%^&*]/.test(password)) strength++;
        
        bars.forEach((bar, index) => {
            bar.classList.remove('weak', 'medium', 'strong');
            if (index < Math.min(strength, 3)) {
                if (strength <= 2) bar.classList.add('weak');
                else if (strength <= 3) bar.classList.add('medium');
                else bar.classList.add('strong');
            }
        });
        
        if (infoDiv) infoDiv.textContent = strength <= 2 ? 'Lemah' : (strength <= 3 ? 'Sedang' : 'Kuat');
        strengthDiv.style.display = 'flex';
    }

    const initPage = function() {
        const formEl = document.getElementById('userForm');
        const roleSelect = document.getElementById('role');
        const deptSelect = document.getElementById('departemen_id');
        const atasanSelect = document.getElementById('atasan_id');
        const atasanGroup = document.getElementById('atasan_group');
        const atasanWarning = document.getElementById('atasanWarning');
        const atasanFieldHelper = document.getElementById('atasanFieldHelper');
        const roleDescription = document.getElementById('roleDescription');
        const roleFieldHelper = document.getElementById('roleFieldHelper');
        const emailAvailabilityHint = document.getElementById('emailAvailabilityHint');
        const hierarchyPreview = document.getElementById('hierarchyPreview');
        const approverLabel = document.getElementById('approverLabel');
        const approverStep = document.getElementById('approverStep');
        const changeState = document.getElementById('changeState');
        const changeStateText = document.getElementById('changeStateText');
        const summaryName = document.getElementById('summaryName');
        const summaryRole = document.getElementById('summaryRole');
        const summaryDept = document.getElementById('summaryDept');
        const summaryAtasan = document.getElementById('summaryAtasan');
        const summaryStatus = document.getElementById('summaryStatus');
        const completenessFill = document.getElementById('completenessFill');
        const completenessText = document.getElementById('completenessText');
        const nameInput = document.getElementById('name');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const passwordConfirmInput = document.getElementById('password_confirmation');
        const jabatanInput = document.getElementById('jabatan');
        const statusInput = document.getElementById('is_active');
        const phoneInput = document.getElementById('nomor_telepon');
        const bankInput = document.getElementById('nama_bank');
        const rekeningInput = document.getElementById('nomor_rekening');
        const emailCheckEndpoint = "{{ route('finance.masterdata.users.check-email') }}";

        if (!formEl || !roleSelect || !deptSelect) return;

        const formState = window.__financeUserFormState || {
            beforeUnloadBound: false,
            isDirty: () => false,
            isSubmitting: false,
        };
        window.__financeUserFormState = formState;

        if (!formState.beforeUnloadBound) {
            window.addEventListener('beforeunload', (event) => {
                if (formState.isSubmitting || !formState.isDirty()) {
                    return;
                }
                event.preventDefault();
                event.returnValue = '';
            });
            formState.beforeUnloadBound = true;
        }

        // Cleanup TomSelect instances if already existing
        if (roleSelect.tomselect) roleSelect.tomselect.destroy();
        if (deptSelect.tomselect) deptSelect.tomselect.destroy();
        if (atasanSelect && atasanSelect.tomselect) atasanSelect.tomselect.destroy();

        const tsDept = new TomSelect('#departemen_id', { create: false, controlInput: null });
        const tsAtasan = new TomSelect('#atasan_id', { create: false, allowEmptyOption: true, placeholder: '-- Pilih Atasan --' });
        const allAtasanOptions = atasanSelect ? Array.from(atasanSelect.options).slice(1) : [];

        const roleData = {
            'pegawai': { desc: 'Karyawan yang mengajukan reimbursement. Memerlukan atasan untuk persetujuan awal.', hierarchy: true },
            'atasan': { desc: 'Manager/Supervisor yang menyetujui pengajuan tim. Pengajuan pribadi langsung ke Finance.', hierarchy: true },
            'finance': { desc: 'Tim keuangan yang memverifikasi dokumen dan mencairkan dana.', hierarchy: false }
        };
        let initialSnapshot = '';
        let emailCheckTimer = null;
        let emailRequestId = 0;

        function setChangeState(mode, text) {
            if (!changeState || !changeStateText) return;
            changeState.classList.remove('is-clean', 'is-dirty', 'is-saving');
            changeState.classList.add(mode);
            changeStateText.textContent = text;
        }

        function snapshotForm() {
            return JSON.stringify({
                name: nameInput?.value?.trim() || '',
                email: emailInput?.value?.trim().toLowerCase() || '',
                password: passwordInput?.value || '',
                password_confirmation: passwordConfirmInput?.value || '',
                role: roleSelect?.value || '',
                departemen: deptSelect?.value || '',
                jabatan: jabatanInput?.value?.trim() || '',
                atasan: atasanSelect?.value || '',
                status: statusInput?.value || '',
                nomor_telepon: phoneInput?.value?.trim() || '',
                nama_bank: bankInput?.value?.trim() || '',
                nomor_rekening: rekeningInput?.value?.trim() || '',
            });
        }

        function updateDirtyState() {
            const dirty = initialSnapshot !== '' && snapshotForm() !== initialSnapshot;
            formState.isDirty = () => dirty;
            if (formState.isSubmitting) {
                setChangeState('is-saving', 'Menyimpan perubahan...');
                return;
            }
            setChangeState(
                dirty ? 'is-dirty' : 'is-clean',
                dirty ? 'Ada perubahan yang belum disimpan.' : 'Belum ada perubahan.'
            );
        }

        function setEmailAvailability(state, message) {
            if (!emailAvailabilityHint) return;
            emailAvailabilityHint.classList.add('email-availability');
            emailAvailabilityHint.classList.remove('is-checking', 'is-available', 'is-unavailable');
            if (state === 'checking') emailAvailabilityHint.classList.add('is-checking');
            if (state === 'available') emailAvailabilityHint.classList.add('is-available');
            if (state === 'unavailable') emailAvailabilityHint.classList.add('is-unavailable');
            emailAvailabilityHint.textContent = message;
        }

        async function checkEmailAvailability(value) {
            const email = (value || '').trim().toLowerCase();
            if (!email) {
                setEmailAvailability('neutral', 'Gunakan email resmi perusahaan.');
                return;
            }

            const formatValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            if (!formatValid) {
                setEmailAvailability('unavailable', 'Format email belum valid.');
                return;
            }

            const requestId = ++emailRequestId;
            setEmailAvailability('checking', 'Memeriksa ketersediaan email...');

            try {
                const response = await fetch(`${emailCheckEndpoint}?email=${encodeURIComponent(email)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                const payload = await response.json().catch(() => ({}));
                if (requestId !== emailRequestId) return;

                if (response.ok && payload.available) {
                    setEmailAvailability('available', payload.message || 'Email tersedia.');
                } else {
                    setEmailAvailability('unavailable', payload.message || 'Email tidak dapat digunakan.');
                }
            } catch (error) {
                if (requestId !== emailRequestId) return;
                setEmailAvailability('neutral', 'Pemeriksaan email gagal. Coba lagi beberapa saat.');
            }
        }

        function scheduleEmailCheck() {
            if (!emailInput) return;
            clearTimeout(emailCheckTimer);
            emailCheckTimer = setTimeout(() => {
                checkEmailAvailability(emailInput.value);
            }, 450);
        }

        function updateRoleFieldHelper() {
            const role = roleSelect?.value || '';
            if (roleFieldHelper) {
                if (role === 'pegawai') {
                    roleFieldHelper.textContent = 'Pegawai wajib memiliki atasan langsung dalam departemen yang sama.';
                } else if (role === 'atasan') {
                    roleFieldHelper.textContent = 'Atasan menyetujui pengajuan tim; pengajuan pribadi langsung ke Finance.';
                } else if (role === 'finance') {
                    roleFieldHelper.textContent = 'Finance memiliki akses approval, pencairan, laporan, dan master data.';
                } else {
                    roleFieldHelper.textContent = 'Pilih peran agar sistem menyesuaikan alur approval.';
                }
            }

            if (atasanFieldHelper) {
                atasanFieldHelper.textContent = role === 'pegawai'
                    ? 'Wajib untuk role Pegawai. Pilih atasan langsung dari departemen yang sama.'
                    : 'Tidak diperlukan untuk role Atasan/Finance.';
            }
        }

        function optionLabel(selectEl) {
            if (!selectEl) return '-';
            const option = selectEl.options[selectEl.selectedIndex];
            return option && option.value !== '' ? option.text.trim() : '-';
        }

        function updateSmartPreview() {
            const role = roleSelect?.value || '';
            const isPegawai = role === 'pegawai';
            const fields = [
                !!nameInput?.value?.trim(),
                !!emailInput?.value?.trim(),
                !!passwordInput?.value?.trim(),
                !!passwordConfirmInput?.value?.trim(),
                !!jabatanInput?.value?.trim(),
                !!deptSelect?.value,
                !!role,
            ];

            if (isPegawai) {
                fields.push(!!atasanSelect?.value);
            }

            const filledCount = fields.filter(Boolean).length;
            const percent = Math.round((filledCount / fields.length) * 100);
            const passwordMatch = passwordInput?.value && passwordConfirmInput?.value && passwordInput.value === passwordConfirmInput.value;

            if (summaryName) summaryName.textContent = nameInput?.value?.trim() || '-';
            if (summaryRole) summaryRole.textContent = optionLabel(roleSelect);
            if (summaryDept) summaryDept.textContent = optionLabel(deptSelect);
            if (summaryAtasan) summaryAtasan.textContent = isPegawai ? optionLabel(atasanSelect) : 'Tidak diperlukan';
            if (summaryStatus) summaryStatus.textContent = optionLabel(statusInput);

            if (completenessFill) completenessFill.style.width = `${percent}%`;
            if (completenessText) {
                if (!passwordInput?.value && !passwordConfirmInput?.value) {
                    completenessText.textContent = `Kelengkapan form ${percent}%`;
                } else if (!passwordMatch) {
                    completenessText.textContent = 'Password dan konfirmasi password belum sama.';
                } else {
                    completenessText.textContent = `Kelengkapan form ${percent}%`;
                }
            }
            updateDirtyState();
        }

        window.__updateUserFormPreview = updateSmartPreview;

        function updateRoleInfo() {
            const role = roleSelect.value;
            updateRoleFieldHelper();
            if (roleDescription) {
                roleDescription.innerHTML = roleData[role] ? roleData[role].desc : 'Pilih peran untuk melihat deskripsi...';
            }
            
            if (hierarchyPreview) {
                if (roleData[role]?.hierarchy) {
                    hierarchyPreview.style.display = 'block';
                    if (approverLabel) approverLabel.innerText = role === 'pegawai' ? 'Atasan' : 'Finance (Direct)';
                    if (approverStep) approverStep.style.display = role === 'atasan' ? 'none' : 'flex';
                    const arrows = hierarchyPreview.querySelectorAll('.hierarchy-arrow');
                    if (arrows.length > 0) arrows[0].style.display = role === 'atasan' ? 'none' : 'block';
                } else {
                    hierarchyPreview.style.display = 'none';
                }
            }
            
            if (atasanGroup) atasanGroup.style.display = role === 'pegawai' ? 'block' : 'none';
            if (atasanSelect) atasanSelect.required = role === 'pegawai';
            if (role !== 'pegawai') tsAtasan.setValue("");
            updateSmartPreview();
        }

        function filterAtasan() {
            const selectedDept = deptSelect.value;
            tsAtasan.clear(true);
            tsAtasan.clearOptions();
            if (atasanWarning) atasanWarning.style.display = 'none';
            
            if (!selectedDept) {
                updateSmartPreview();
                return;
            }

            const filtered = allAtasanOptions.filter(opt => opt.dataset.dept == selectedDept);
            if (filtered.length > 0) {
                filtered.forEach(opt => tsAtasan.addOption({ value: opt.value, text: opt.text }));
                if (filtered.length === 1 && roleSelect.value === 'pegawai') {
                    tsAtasan.setValue(filtered[0].value);
                }
            } else if (roleSelect.value === 'pegawai') {
                if (atasanWarning) atasanWarning.style.display = 'flex';
            }

            updateSmartPreview();
        }

        roleSelect.addEventListener('change', () => { updateRoleInfo(); filterAtasan(); });
        deptSelect.addEventListener('change', filterAtasan);
        if (atasanSelect) atasanSelect.addEventListener('change', updateSmartPreview);
        if (statusInput) statusInput.addEventListener('change', updateSmartPreview);
        [nameInput, jabatanInput, passwordInput, passwordConfirmInput, phoneInput, bankInput, rekeningInput].forEach((el) => {
            if (!el) return;
            el.addEventListener('input', updateSmartPreview);
            el.addEventListener('change', updateSmartPreview);
        });
        if (emailInput) {
            emailInput.addEventListener('input', () => {
                updateSmartPreview();
                scheduleEmailCheck();
            });
            emailInput.addEventListener('blur', () => checkEmailAvailability(emailInput.value));
            emailInput.addEventListener('change', () => checkEmailAvailability(emailInput.value));
        }
        const pwInput = document.getElementById('password');
        if (pwInput) pwInput.addEventListener('input', (e) => window.updatePasswordStrength(e.target.value));
        if (!formEl.dataset.submitGuardBound) {
            formEl.addEventListener('submit', (event) => {
                if (emailAvailabilityHint?.classList.contains('is-unavailable')) {
                    event.preventDefault();
                    formState.isSubmitting = false;
                    setChangeState('is-dirty', 'Perbaiki email sebelum menyimpan.');
                    if (emailInput) emailInput.focus();
                    return;
                }
                formState.isSubmitting = true;
                setChangeState('is-saving', 'Menyimpan perubahan...');
            });
            formEl.dataset.submitGuardBound = '1';
        }

        updateRoleInfo();
        if (deptSelect.value) filterAtasan();
        updateSmartPreview();
        scheduleEmailCheck();
        initialSnapshot = snapshotForm();
        formState.isSubmitting = false;
        updateDirtyState();
    };

    if (document.readyState === 'complete') {
        initPage();
    } else {
        document.addEventListener('DOMContentLoaded', initPage);
    }
    
    // Also handle Livewire navigation
    document.addEventListener('livewire:navigated', initPage);
})();
</script>
@endpush
@endsection

