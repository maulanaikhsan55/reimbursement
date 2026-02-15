@extends('layouts.guest')

@section('title', 'Lupa Password - Smart Reimbursement')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-header-simple">
                <img src="/images/logo.png" alt="Smart Reimbursement Logo" class="auth-logo-simple">
            </div>
            <h2 class="auth-title">Lupa Password?</h2>
            <p class="auth-subtitle">Tenang, kami akan bantu Anda</p>
        </div>

        <!-- Info Card -->
        <div style="background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%); border: 1px solid #bfdbfe; padding: 20px; border-radius: 12px; margin-bottom: 24px;">
            <div style="display: flex; align-items: flex-start; gap: 12px;">
                <div style="background: #425d87; color: white; padding: 8px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                </div>
                <div>
                    <h4 style="margin: 0 0 8px 0; color: #1e40af; font-size: 14px;">Reset Password oleh Finance Admin</h4>
                    <p style="margin: 0; font-size: 13px; color: #475569; line-height: 1.6;">
                        Untuk keamanan, reset password dilakukan oleh <strong>Administrator Finance</strong>.
                        Silakan hubungi mereka secara langsung untuk mendapatkan password sementara.
                    </p>
                </div>
            </div>
        </div>

        <!-- Steps -->
        <div style="margin-bottom: 24px;">
            <h5 style="margin: 0 0 12px 0; color: #374151; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">Langkah-langkah:</h5>
            
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc; border-radius: 8px;">
                    <span style="background: #425d87; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0;">1</span>
                    <span style="font-size: 13px; color: #475569;">Hubungi Finance Admin via WA atau telepon</span>
                </div>
                
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc; border-radius: 8px;">
                    <span style="background: #425d87; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0;">2</span>
                    <span style="font-size: 13px; color: #475569;">Sebutkan nama dan email akun Anda</span>
                </div>
                
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc; border-radius: 8px;">
                    <span style="background: #425d87; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0;">3</span>
                    <span style="font-size: 13px; color: #475569;">Finance akan reset dan berikan password baru</span>
                </div>
                
                <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: #f8fafc; border-radius: 8px;">
                    <span style="background: #425d87; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; flex-shrink: 0;">4</span>
                    <span style="font-size: 13px; color: #475569;">Login dan wajib ganti password pertama kali</span>
                </div>
            </div>
        </div>

        <!-- Security Info -->
        <div style="background: #fef3c7; border: 1px solid #fcd34d; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <p style="margin: 0; font-size: 12px; color: #92400e;">
                <strong>Catatan:</strong> Proses ini lebih aman karena Anda tidak perlu berbagi password via email yang tidak terenkripsi.
            </p>
        </div>

        <a href="{{ route('login') }}" class="auth-btn" style="text-align: center; text-decoration: none; display: flex; align-items: center; justify-content: center;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
            Kembali ke Login
        </a>

        <div class="auth-footer">
            <p style="font-size: 12px; color: #9ca3af; margin: 0; text-align: center;">
                Butuh bantuan segera? Hubungi IT Support
            </p>
        </div>
    </div>
</div>
@endsection
