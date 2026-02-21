@extends('layouts.guest')

@section('title', 'Login - Smart Reimbursement')

@section('content')
<div class="auth-container">
    <div class="auth-shell auth-shell-compact">
        <aside class="auth-showcase auth-showcase-compact">
            <div class="auth-showcase-badge">Smart Reimbursement Platform</div>
            <h1>Approve Faster with a Cleaner Expense Flow</h1>
            <p>OCR validation, duplicate guard, and controlled approval routing in one modern workspace.</p>
            <ul class="auth-showcase-points">
                <li>AI OCR for clean submission data</li>
                <li>Policy-safe approvals from submission to close</li>
                <li>Audit-ready records with accurate sync</li>
            </ul>
            <div class="auth-showcase-visual">
                <img src="{{ asset('images/mockup.png') }}" alt="Reimbursement dashboard preview" loading="lazy" decoding="async">
            </div>
        </aside>

        <div class="auth-card auth-card-compact">
            <div class="auth-header">
                <div class="auth-header-simple">
                    <img src="/images/logo.png" alt="Smart Reimbursement Logo" class="auth-logo-simple">
                </div>
                <h2 class="auth-title">Welcome Back</h2>
                <p class="auth-subtitle">Masuk untuk melanjutkan proses reimbursement Anda.</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-errors">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        class="form-input"
                        placeholder="nama@email.com"
                        autocomplete="email"
                        autofocus
                    >
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div class="password-input-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            class="form-input"
                            placeholder="Masukkan kata sandi Anda"
                            autocomplete="current-password"
                        >
                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword()"
                        tabindex="-1"
                        aria-label="Tampilkan kata sandi"
                        aria-pressed="false"
                        title="Tampilkan/Sembunyikan kata sandi"
                    >
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="auth-btn">
                    Masuk
                </button>

                <div class="auth-footer kiri-kanan">
                    <a href="/" class="auth-link">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M19 12H5M12 19l-7-7 7-7"/>
                        </svg>
                        <span>Beranda</span>
                    </a>
                    <a href="{{ route('password.request') }}" class="auth-link">
                        <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                        <span>Lupa Password?</span>
                    </a>
                </div>
            </form>
            
            <div class="auth-footer-info">
                <p>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    Sistem dilindungi dengan enkripsi aman
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inline toggle password function
    window.togglePassword = function() {
        const passwordInput = document.getElementById('password');
        const toggleBtn = document.querySelector('.password-toggle');
        const eyeIcon = toggleBtn.querySelector('.eye-icon');
        if (!passwordInput || !toggleBtn || !eyeIcon) return;
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.add('visible');
            toggleBtn.setAttribute('aria-pressed', 'true');
            toggleBtn.setAttribute('aria-label', 'Sembunyikan kata sandi');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('visible');
            toggleBtn.setAttribute('aria-pressed', 'false');
            toggleBtn.setAttribute('aria-label', 'Tampilkan kata sandi');
        }
    };
});
</script>
@endsection
