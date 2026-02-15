@extends('layouts.guest')

@section('title', 'Verifikasi Email - Smart Reimbursement')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-header-simple">
                <img src="/images/logo.png" alt="Smart Reimbursement Logo" class="auth-logo-simple">
            </div>
            <h2 class="auth-title">Verifikasi Email</h2>
            <p class="auth-subtitle">Silakan verifikasi alamat email Anda untuk melanjutkan</p>
        </div>

        <div class="alert alert-info" style="background: rgba(59, 130, 246, 0.1); color: #1e40af; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.9rem;">
            Sebelum melanjutkan, silakan periksa email Anda untuk tautan verifikasi. 
            Jika Anda tidak menerima email tersebut, kami akan mengirimkan yang baru.
        </div>

        @if (session('message'))
            <div class="alert alert-success" style="background: rgba(34, 197, 94, 0.1); color: #166534; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem; font-size: 0.9rem;">
                {{ session('message') }}
            </div>
        @endif

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="auth-btn">
                    Kirim Ulang Email Verifikasi
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="auth-link" style="background:none; border:none; cursor:pointer; width: 100%; text-align: center; color: #6b7280;">
                    Log Out
                </button>
            </form>
        </div>

        <div class="auth-footer kiri-kanan" style="margin-top: 2rem;">
            <a href="/" class="auth-link">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                <span>Beranda</span>
            </a>
        </div>
    </div>
</div>
@endsection
