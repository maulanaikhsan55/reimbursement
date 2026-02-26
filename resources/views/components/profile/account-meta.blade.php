@props([
    'user',
])

@php
    $createdAt = $user->created_at;
    $updatedAt = $user->updated_at;
    $verifiedAt = $user->email_verified_at;
    $accountAgeDays = $createdAt ? $createdAt->diffInDays(now()) : 0;
    $accountAgeLabel = $accountAgeDays >= 365
        ? floor($accountAgeDays / 365).' thn '.($accountAgeDays % 365).' hr'
        : $accountAgeDays.' hari';
@endphp

<div class="profile-account-meta">
    <h3 class="form-section-title">Informasi Akun</h3>
    <div class="profile-meta-grid">
        <div class="profile-meta-card">
            <span class="profile-meta-label">Akun Dibuat</span>
            <strong class="profile-meta-value">{{ $createdAt ? $createdAt->format('d M Y, H:i') : '-' }}</strong>
        </div>
        <div class="profile-meta-card">
            <span class="profile-meta-label">Terakhir Diperbarui</span>
            <strong class="profile-meta-value">{{ $updatedAt ? $updatedAt->format('d M Y, H:i') : '-' }}</strong>
        </div>
        <div class="profile-meta-card">
            <span class="profile-meta-label">Usia Akun</span>
            <strong class="profile-meta-value">{{ $accountAgeLabel }}</strong>
        </div>
        <div class="profile-meta-card">
            <span class="profile-meta-label">Status Verifikasi</span>
            <strong class="profile-meta-value">{{ $verifiedAt ? 'Terverifikasi' : 'Belum Verifikasi' }}</strong>
        </div>
    </div>
</div>
