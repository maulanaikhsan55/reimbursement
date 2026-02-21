@extends('layouts.guest')

@section('title', 'Lupa Password - Smart Reimbursement')

@section('content')
<div class="auth-container">
    <div class="auth-shell auth-shell-compact auth-shell-forgot">
        <aside class="auth-showcase auth-showcase-compact auth-showcase-forgot">
            <div class="auth-showcase-badge">Account Recovery</div>
            <h1>Secure Recovery Process</h1>
            <p>Reset dikelola Finance Admin untuk menjaga keamanan akun dan validasi identitas pengguna.</p>
            <ul class="auth-showcase-points">
                <li>Verifikasi identitas sebelum reset</li>
                <li>Password sementara dengan kontrol admin</li>
                <li>Wajib ganti password saat login pertama</li>
            </ul>
            <div class="auth-showcase-visual">
                <img src="{{ asset('images/finance.png') }}" alt="Finance dashboard preview" loading="lazy" decoding="async">
            </div>
        </aside>

        <div class="auth-card auth-card-forgot auth-card-compact">
            <div class="auth-header">
                <div class="auth-header-simple">
                    <img src="/images/logo.png" alt="Smart Reimbursement Logo" class="auth-logo-simple">
                </div>
                <h2 class="auth-title">Lupa Password?</h2>
                <p class="auth-subtitle">Ikuti alur singkat di bawah untuk mendapatkan akses kembali dengan aman.</p>
            </div>

            <div class="support-card support-card-info">
                <div class="support-card-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <div>
                    <h4>Reset Password oleh Finance Admin</h4>
                    <p>
                        Admin verifikasi akun Anda terlebih dahulu, lalu memberikan password sementara.
                    </p>
                </div>
            </div>

            <div class="support-steps">
                <h5>Langkah-langkah:</h5>
                <div class="support-steps-grid">
                    <div class="support-step-item">
                        <span class="support-step-index">1</span>
                        <span>Hubungi Finance Admin</span>
                    </div>
                    <div class="support-step-item">
                        <span class="support-step-index">2</span>
                        <span>Sebutkan nama + email akun</span>
                    </div>
                    <div class="support-step-item">
                        <span class="support-step-index">3</span>
                        <span>Terima password sementara</span>
                    </div>
                    <div class="support-step-item">
                        <span class="support-step-index">4</span>
                        <span>Login & ganti password</span>
                    </div>
                </div>
            </div>

            <div class="support-card support-card-warning">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <p>
                    <strong>Catatan:</strong> Lebih aman karena tidak berbagi password lewat email terbuka.
                </p>
            </div>

            <a href="{{ route('login') }}" class="auth-btn auth-btn-link">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                Kembali ke Login
            </a>

            <div class="auth-footer">
                <p class="auth-footer-note">Butuh bantuan segera? Hubungi IT Support</p>
            </div>
        </div>
    </div>
</div>
@endsection
