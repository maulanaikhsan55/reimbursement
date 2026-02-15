@extends('layouts.app')

@section('title', 'Profil Saya - Atasan')

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Profil Saya" subtitle="Kelola informasi profil dan pengaturan akun Anda" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="tabs-container">
            <div class="tabs-header">
                <div class="tabs-nav">
                    <button type="button" class="tab-btn active" data-tab="informasi">
                        <svg class="tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>Informasi Profil</span>
                    </button>
                    <button type="button" class="tab-btn" data-tab="keamanan">
                        <svg class="tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        <span>Keamanan</span>
                    </button>
                </div>
            </div>

            <div id="informasi" class="tab-content active">
            <div class="modern-section">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Informasi Pribadi</h2>
                        <p class="section-subtitle">Perbarui data pribadi Anda</p>
                    </div>
                </div>

                <form action="{{ route('atasan.profile.update') }}" method="POST">
                    @csrf

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Nama Lengkap</label>
                            <input type="text" id="name" name="name" class="form-control" 
                                   value="{{ $user->name }}" required>
                            @error('name')
                                <small class="form-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="{{ $user->email }}" readonly 
                                   style="background-color: #f8fafc; cursor: not-allowed;"
                                   title="Email tidak dapat diubah untuk keamanan identitas">
                            <small class="form-help-text" style="color: #64748b; font-size: 0.75rem;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:inline; margin-bottom: 2px;">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                </svg>
                                Email adalah identitas akun dan tidak dapat diubah.
                            </small>
                        </div>

                        <div class="form-group">
                            <label for="nomor_telepon">Nomor Telepon</label>
                            <input type="tel" id="nomor_telepon" name="nomor_telepon" class="form-control" 
                                   value="{{ $user->nomor_telepon ?? '' }}" 
                                   placeholder="Contoh: 081234567890">
                            @error('nomor_telepon')
                                <small class="form-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="nama_bank">Nama Bank</label>
                            <input type="text" id="nama_bank" name="nama_bank" class="form-control" 
                                   value="{{ $user->nama_bank ?? '' }}" 
                                   placeholder="Contoh: BCA, Mandiri, BNI">
                            @error('nama_bank')
                                <small class="form-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="nomor_rekening">Nomor Rekening</label>
                            <input type="text" id="nomor_rekening" name="nomor_rekening" class="form-control" 
                                   value="{{ $user->nomor_rekening ?? '' }}" 
                                   placeholder="Nomor rekening bank Anda">
                            @error('nomor_rekening')
                                <small class="form-error">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 class="form-section-title">Informasi Departemen</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>Departemen</label>
                                <div class="form-display">{{ $user->departemen->nama_departemen ?? '-' }}</div>
                            </div>

                            <div class="form-group">
                                <label>Role</label>
                                <div class="form-display">{{ ucfirst($user->role) }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-modern btn-modern-primary" onclick="openConfirmModal(() => this.closest('form').submit(), 'Simpan Perubahan', 'Apakah Anda yakin ingin menyimpan perubahan profil ini?')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" style="margin-right: 8px;">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="keamanan" class="tab-content">
            <div class="modern-section">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Ubah Password</h2>
                        <p class="section-subtitle">Perbarui password untuk menjaga keamanan akun Anda</p>
                    </div>
                </div>

                <form action="{{ route('atasan.profile.password') }}" method="POST">
                    @csrf

                    <div class="password-note">
                        <svg class="note-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <div>
                            <p><strong>Persyaratan Password:</strong></p>
                            <ul>
                                <li>Minimal 8 karakter</li>
                                <li>Gunakan kombinasi huruf, angka, dan simbol untuk keamanan maksimal</li>
                            </ul>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="current_password">Password Saat Ini</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="current_password" name="current_password" 
                                   class="form-control" placeholder="Masukkan password saat ini" required>
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('current_password')" tabindex="-1">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        @error('current_password')
                            <small class="form-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password Baru</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="password" name="password" 
                                   class="form-control" placeholder="Masukkan password baru" required>
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password')" tabindex="-1">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <small class="form-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Konfirmasi Password Baru</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="password_confirmation" name="password_confirmation" 
                                   class="form-control" placeholder="Konfirmasi password baru" required>
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password_confirmation')" tabindex="-1">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <small class="form-error">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-modern btn-modern-primary" onclick="openConfirmModal(() => this.closest('form').submit(), 'Ubah Password', 'Apakah Anda yakin ingin mengubah password akun Anda?')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20" style="margin-right: 8px;">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
        </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .dashboard-wrapper {
        padding: 1rem;
        background: linear-gradient(135deg, #f5f7fa 0%, #eef1f8 100%);
        min-height: 100vh;
    }

    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
        width: 100%;
    }

    .dashboard-content {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .tabs-container {
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .alert {
        padding: 1rem 1.25rem;
        border-radius: 1rem;
        margin-bottom: 1rem;
        border-left: 4px solid;
        animation: slideDown 0.3s ease;
    }

    .alert-success {
        background: rgba(34, 197, 94, 0.1);
        color: #166534;
        border-left-color: #22c55e;
    }

    .alert-danger {
        background: rgba(239, 68, 68, 0.1);
        color: #991b1b;
        border-left-color: #ef4444;
    }

    @keyframes slideDown {
        from { transform: translateY(-10px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .tabs-header {
        display: flex;
        gap: 0;
        background: white;
        border-radius: 1.75rem 1.75rem 0 0;
        padding: 0;
        border: 1px solid #e5eaf2;
        border-bottom: 2px solid #e5eaf2;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }

    .tabs-nav {
        display: flex;
        gap: 0;
    }

    .tab-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        padding: 1.25rem 2rem;
        background: none;
        border: none;
        border-bottom: 3px solid transparent;
        font-size: 0.9rem;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
    }

    .tab-btn:hover {
        color: #425d87;
        background: rgba(66, 93, 135, 0.02);
    }

    .tab-btn.active {
        color: #425d87;
        background: white;
        border-bottom: 3px solid #425d87;
        margin-bottom: -1px; /* Menutupi border bawah header agar menyatu */
    }

    .tab-icon {
        width: 18px;
        height: 18px;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .modern-section {
        background: white;
        border-radius: 0 0 1.75rem 1.75rem;
        padding: 2rem;
        border: 1px solid #e5eaf2;
        border-top: none;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        transition: all 0.3s ease;
    }

    .section-header {
        margin-bottom: 1rem;
    }

    .section-header > div {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2c394e;
        margin: 0;
    }

    .section-subtitle {
        font-size: 0.85rem;
        color: #64748b;
        margin: 0;
        font-weight: 500;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group label {
        font-size: 0.9rem;
        font-weight: 600;
        color: #2c394e;
    }

    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid #d9e0ed;
        border-radius: 0.75rem;
        font-size: 0.95rem;
        font-family: inherit;
        transition: all 0.2s ease;
        background: white;
        color: #1e293b;
    }

    .form-control:focus {
        outline: none;
        border-color: #425d87;
        box-shadow: 0 0 0 3px rgba(66, 93, 135, 0.08);
        background: #f8faff;
    }

    .form-display {
        width: 100%;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border: 1px solid #d9e0ed;
        border-radius: 0.75rem;
        font-size: 0.95rem;
        color: #64748b;
        display: flex;
        align-items: center;
        font-weight: 500;
    }

    .form-section {
        margin-top: 1.75rem;
        padding-top: 1.75rem;
        border-top: 1px solid #e5eaf2;
    }

    .form-section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #2c394e;
        margin: 0 0 1.5rem;
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 1rem;
        margin-top: 1rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e5eaf2;
    }

    .password-note {
        display: flex;
        gap: 1rem;
        padding: 1rem 1.25rem;
        background: rgba(66, 93, 135, 0.08);
        border: 1px solid rgba(66, 93, 135, 0.2);
        border-radius: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .note-icon {
        width: 20px;
        height: 20px;
        color: #425d87;
        flex-shrink: 0;
        margin-top: 0.125rem;
    }

    .password-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-input-wrapper .form-control {
        padding-right: 2.75rem;
    }

    .password-toggle {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        padding: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        transition: color 0.2s ease;
    }

    .password-toggle:hover {
        color: #425d87;
    }

    .form-error {
        font-size: 0.8rem;
        color: #dc2626;
        display: block;
        margin-top: 0.25rem;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        const button = event.target.closest('.password-toggle');
        
        if (input.type === 'password') {
            input.type = 'text';
            button.innerHTML = `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                    <line x1="1" y1="1" x2="23" y2="23"></line>
                </svg>
            `;
        } else {
            input.type = 'password';
            button.innerHTML = `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            `;
        }
    }

    function initProfileScripts() {
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');

        tabButtons.forEach(button => {
            button.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');

                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));

                this.classList.add('active');
                const targetTab = document.getElementById(tabName);
                if (targetTab) {
                    targetTab.classList.add('active');
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', initProfileScripts);
    document.addEventListener('livewire:navigated', initProfileScripts);
</script>
@endpush

@endsection
