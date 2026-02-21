@extends('layouts.app')

@section('title', 'Profil Saya - Atasan')

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Profil Saya" subtitle="Kelola informasi profil dan pengaturan akun Anda" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content profile-page-content">
        <div class="tabs-container profile-tabs-container">
            <div class="tabs-header profile-tabs-header">
                <div class="tabs-nav profile-tabs-nav">
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

            <div id="informasi" class="tab-content active profile-tab-content">
            <div class="modern-section profile-modern-section">
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
                                   value="{{ $user->name }}" required autocomplete="name">
                            @error('name')
                                <small class="form-error">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="{{ $user->email }}" readonly 
                                   style="background-color: #f8fafc; cursor: not-allowed;"
                                   title="Email tidak dapat diubah untuk keamanan identitas" autocomplete="email">
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
                                   placeholder="Contoh: 081234567890" autocomplete="tel">
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

        <div id="keamanan" class="tab-content profile-tab-content">
            <div class="modern-section profile-modern-section">
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
                                   class="form-control" placeholder="Masukkan password saat ini" required autocomplete="current-password">
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
                                   class="form-control" placeholder="Masukkan password baru" required autocomplete="new-password">
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
                                   class="form-control" placeholder="Konfirmasi password baru" required autocomplete="new-password">
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
    :root {
        --profile-primary: #425d87;
        --profile-primary-700: #344d74;
        --profile-ink: #1d2d48;
        --profile-muted: #64748b;
        --profile-border: rgba(66, 93, 135, 0.15);
    }

    .dashboard-wrapper {
        padding: 1rem;
        background: linear-gradient(135deg, #f5f7fa 0%, #eef1f8 100%);
        min-height: 100vh;
    }

    .dashboard-container {
        max-width: 100%;
        margin: 0;
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
        background: #fff;
        border: 1px solid var(--profile-border);
        border-radius: 1.5rem;
        box-shadow: 0 14px 28px rgba(22, 37, 62, 0.08);
        overflow: hidden;
    }

    .alert {
        padding: 0.75rem 0.95rem;
        border-radius: 0.75rem;
        margin-bottom: 1rem;
        border: 1px solid;
    }

    .alert-success {
        background: #ecfdf3;
        color: #166534;
        border-color: #bbf7d0;
    }

    .alert-danger {
        background: #fef2f2;
        color: #991b1b;
        border-color: #fecaca;
    }

    .alert ul {
        margin: 0;
        padding-left: 1.5rem;
    }

    .alert li {
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }

    .tabs-header {
        display: block;
        gap: 0;
        background: #fff;
        padding: 0;
    }

    .tabs-nav {
        display: flex;
        gap: 0.5rem;
        padding: 0.95rem 1.25rem 0.85rem;
        border-bottom: 1px solid rgba(66, 93, 135, 0.1);
    }

    .tab-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        padding: 0.6rem 0.95rem;
        background: #fff;
        border: 1px solid rgba(66, 93, 135, 0.2);
        border-radius: 999px;
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--profile-muted);
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .tab-btn:hover {
        color: var(--profile-primary);
        border-color: rgba(66, 93, 135, 0.35);
    }

    .tab-btn.active {
        color: #fff;
        background: linear-gradient(135deg, var(--profile-primary), var(--profile-primary-700));
        border-color: transparent;
        box-shadow: 0 10px 18px rgba(66, 93, 135, 0.22);
    }

    .tab-icon {
        width: 16px;
        height: 16px;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .modern-section {
        background: transparent;
        border-radius: 0;
        padding: 1rem 1.25rem 1.25rem;
        border: none;
        box-shadow: none;
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
        font-size: 1rem;
        font-weight: 700;
        color: var(--profile-ink);
        margin: 0;
    }

    .section-subtitle {
        font-size: 0.8rem;
        color: var(--profile-muted);
        margin: 0;
        font-weight: 500;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.85rem;
        margin-bottom: 0.85rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group label {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--profile-ink);
    }

    .form-control {
        width: 100%;
        padding: 0.68rem 0.85rem;
        border: 1px solid rgba(66, 93, 135, 0.2);
        border-radius: 0.72rem;
        font-size: 0.86rem;
        font-family: inherit;
        transition: all 0.2s ease;
        background: white;
        color: #1e293b;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--profile-primary);
        box-shadow: 0 0 0 3px rgba(66, 93, 135, 0.12);
        background: #f8faff;
    }

    .form-display {
        width: 100%;
        padding: 0.68rem 0.85rem;
        border: 1px solid rgba(66, 93, 135, 0.15);
        border-radius: 0.72rem;
        font-size: 0.86rem;
        background: #f8fbff;
        color: #475569;
        font-weight: 500;
    }

    .form-section {
        margin-top: 1.1rem;
        padding-top: 0.95rem;
        border-top: 1px solid rgba(66, 93, 135, 0.12);
    }

    .form-section-title {
        font-size: 0.76rem;
        font-weight: 700;
        color: var(--profile-muted);
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .form-actions {
        margin-top: 1rem;
        display: flex;
        justify-content: flex-end;
    }

    .btn-modern {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.7rem 1.2rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.84rem;
        cursor: pointer;
        transition: all 0.3s ease;
        border: none;
        gap: 0.5rem;
    }

    .btn-modern-primary {
        background: linear-gradient(135deg, var(--profile-primary), var(--profile-primary-700));
        color: white;
        box-shadow: 0 10px 18px rgba(66, 93, 135, 0.22);
    }

    .btn-modern-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 22px rgba(66, 93, 135, 0.27);
    }

    .password-input-wrapper {
        position: relative;
    }

    .password-toggle {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #94a3b8;
        cursor: pointer;
        padding: 0.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .password-toggle svg {
        width: 1.25rem;
        height: 1.25rem;
    }

    .password-toggle:hover {
        color: #64748b;
    }

    .password-note {
        display: flex;
        gap: 1rem;
        background: #f3f7fd;
        border: 1px solid rgba(66, 93, 135, 0.16);
        border-radius: 0.8rem;
        padding: 0.8rem 0.9rem;
        margin-bottom: 1rem;
    }

    .note-icon {
        width: 1.1rem;
        height: 1.1rem;
        color: var(--profile-primary);
        flex-shrink: 0;
    }

    .password-note p {
        margin: 0 0 0.35rem 0;
        font-size: 0.82rem;
        color: #334155;
    }

    .password-note ul {
        margin: 0;
        padding-left: 1.25rem;
        font-size: 0.78rem;
        color: var(--profile-muted);
    }

    .password-note li {
        margin-bottom: 0.25rem;
    }

    .form-error {
        color: #dc2626;
        font-size: 0.75rem;
        font-weight: 500;
        margin-top: 0.25rem;
    }

    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
        }

        .tabs-nav {
            padding: 0.7rem 1rem;
        }

        .tab-btn {
            padding: 0.58rem 0.85rem;
            flex: 1;
        }

        .modern-section {
            padding: 0.9rem 1rem 1.05rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    function initAtasanProfileTabs() {
        document.querySelectorAll('.tab-btn').forEach(button => {
            if (button.dataset.bound === '1') return;
            button.dataset.bound = '1';
            button.addEventListener('click', () => {
                const tabId = button.getAttribute('data-tab');
                document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
                button.classList.add('active');
                const target = document.getElementById(tabId);
                if (target) target.classList.add('active');
            });
        });
    }

    function togglePasswordVisibility(inputId) {
        const input = document.getElementById(inputId);
        if (!input || !input.nextElementSibling) return;
        const icon = input.nextElementSibling.querySelector('svg');
        if (!icon) return;
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        }
    }

    document.addEventListener('DOMContentLoaded', initAtasanProfileTabs);
    document.addEventListener('livewire:navigated', initAtasanProfileTabs);
</script>
@endpush
@endsection

