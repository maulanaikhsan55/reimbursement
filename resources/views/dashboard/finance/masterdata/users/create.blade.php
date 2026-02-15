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
        border-radius: 50px !important;
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
        border-radius: 50px !important;
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
        border-radius: 50px !important;
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
        border-radius: 1.5rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .unified-form-body {
        padding: 2.5rem;
    }

    .form-split-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 3.5rem;
        align-items: start;
    }

    @media (max-width: 1024px) {
        .form-split-grid {
            grid-template-columns: 1fr;
            gap: 2.5rem;
        }
    }

    .form-side-card {
        background: #f8fafc;
        border-radius: 1.5rem;
        padding: 2rem;
        border: 1px solid #eef2f7;
        position: sticky;
        top: 2rem;
    }

    .form-section-header {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        margin-bottom: 2rem;
        padding-bottom: 1.25rem;
        border-bottom: 2px solid #f1f5f9;
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
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Tambah Pengguna Baru" subtitle="Kelola akses dan informasi pengguna dalam satu langkah" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content">
            <form method="POST" action="{{ route('finance.masterdata.users.store') }}" id="userForm">
                @csrf
                
                <div class="unified-form-card">
                    <div class="unified-form-body">
                        <div class="form-split-grid">
                            <!-- Left Side: Form Fields -->
                            <div class="form-main-content">
                                <!-- Account Data Section -->
                                <div class="form-section mb-5">
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
                                            <div class="password-strength mt-2" id="passwordStrength" style="display: none;">
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
                                                <button type="button" class="copy-btn" id="copyBtn" onclick="copyPassword()" style="display: none; position: absolute; right: 3rem; top: 50%; transform: translateY(-50%);">
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
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                                                <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 2.2"/>
                                            </svg>
                                            Buat Password Kuat Otomatis
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Organization Data Section -->
                                <div class="form-section">
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

                                        <div class="form-group" id="atasan_group" style="grid-column: 1 / -1;">
                                            <label for="atasan_id" class="form-label">
                                                Atasan Langsung <span id="atasan_required" class="required">*</span>
                                                <span id="atasan_optional" style="color: #6b7280; font-size: 0.85rem; display: none;">(Opsional)</span>
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
                                            <div id="atasanWarning" class="alert-warning-simple mt-2" style="display: none;">
                                                <x-icon name="alert-circle" class="w-4 h-4" />
                                                <span>Departemen ini belum memiliki user dengan role Atasan.</span>
                                            </div>
                                            @error('atasan_id') <small class="form-error ml-3">{{ $message }}</small> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Side: Preview & Info -->
                            <div class="form-sidebar-content">
                                <div class="form-side-card">
                                    <div class="side-section-title">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                        </svg>
                                        Bantuan & Preview
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Deskripsi Peran</div>
                                        <div id="roleDescription" class="role-description">Pilih peran untuk melihat deskripsi...</div>
                                    </div>
                                    
                                    <div id="hierarchyPreview" style="display: none;">
                                        <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Alur Persetujuan</div>
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

                                    <div id="hierarchyDepthWarning" class="hierarchy-depth-warning mt-4" style="display: none;">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                        <div id="hierarchyDepthMessage"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-5 pt-4 border-top d-flex justify-content-end gap-3">
                            <a href="{{ route('finance.masterdata.users.index') }}" class="btn-modern btn-modern-secondary px-5">
                                Batal
                            </a>
                            <button type="submit" class="btn-modern btn-modern-primary px-5">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                Simpan Pengguna
                            </button>
                        </div>
                    </div>
                </div>
            </form>
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
            if (infoDiv) infoDiv.innerHTML = '';
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
        
        if (infoDiv) infoDiv.innerHTML = strength <= 2 ? '⚠️ Lemah' : (strength <= 3 ? '⚡ Sedang' : '✓ Kuat');
        strengthDiv.style.display = 'flex';
    }

    const initPage = function() {
        const roleSelect = document.getElementById('role');
        const deptSelect = document.getElementById('departemen_id');
        const atasanSelect = document.getElementById('atasan_id');
        const atasanGroup = document.getElementById('atasan_group');
        const atasanWarning = document.getElementById('atasanWarning');
        const roleDescription = document.getElementById('roleDescription');
        const hierarchyPreview = document.getElementById('hierarchyPreview');
        const approverLabel = document.getElementById('approverLabel');
        const approverStep = document.getElementById('approverStep');

        if (!roleSelect || !deptSelect) return;

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

        function updateRoleInfo() {
            const role = roleSelect.value;
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
        }

        function filterAtasan() {
            const selectedDept = deptSelect.value;
            tsAtasan.clearOptions();
            if (atasanWarning) atasanWarning.style.display = 'none';
            
            if (!selectedDept) return;

            const filtered = allAtasanOptions.filter(opt => opt.dataset.dept == selectedDept);
            if (filtered.length > 0) {
                filtered.forEach(opt => tsAtasan.addOption({ value: opt.value, text: opt.text }));
                if (filtered.length === 1 && roleSelect.value === 'pegawai') {
                    tsAtasan.setValue(filtered[0].value);
                }
            } else if (roleSelect.value === 'pegawai') {
                if (atasanWarning) atasanWarning.style.display = 'flex';
            }
        }

        roleSelect.addEventListener('change', () => { updateRoleInfo(); filterAtasan(); });
        deptSelect.addEventListener('change', filterAtasan);
        const pwInput = document.getElementById('password');
        if (pwInput) pwInput.addEventListener('input', (e) => window.updatePasswordStrength(e.target.value));

        updateRoleInfo();
        if (deptSelect.value) filterAtasan();
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
