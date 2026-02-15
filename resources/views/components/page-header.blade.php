<div class="page-header">
    <div class="page-header-left">
        <h1 class="page-title">{{ $title }}</h1>
        @if($subtitle ?? false)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="page-header-right">
        @if($showNotification ?? false)
            <livewire:notification-bell />
        @endif

        @if($showProfile ?? false)
            @php
                $profileRoute = match(Auth::user()->role) {
                    'finance' => route('finance.profile.index'),
                    'pegawai' => route('pegawai.profile.index'),
                    'atasan' => route('atasan.profile.index'),
                    default => '#'
                };
            @endphp
            <a href="{{ $profileRoute }}" class="header-profile" title="Profil">
                <div class="profile-avatar">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</div>
                <div class="profile-info">
                    <p class="profile-name">{{ Auth::user()->name }}</p>
                    <p class="profile-role">{{ Auth::user()->role ?? 'User' }}</p>
                </div>
            </a>
        @endif
    </div>
</div>



<style>
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.5rem 1rem 0.5rem 0.5rem;
        background: linear-gradient(135deg, #f5f7fa 0%, #f0f3f8 100%);
        border-bottom: 1px solid #e8ecf1;
        margin: 0;
        margin-top: -1rem;
        margin-bottom: 1rem;
        border-radius: 0;
        width: 100% !important;
        box-sizing: border-box !important;
    }

    .page-header-left {
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
    }

    .page-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
        letter-spacing: 0.3px;
        line-height: 1.1;
    }

    .page-subtitle {
        font-size: 0.85rem;
        color: #2c3e50;
        margin: 0;
        font-weight: 500;
        letter-spacing: 0.2px;
    }

    .page-header-right {
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .header-icon-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border: none;
        border-radius: 50%;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.3s ease;
        color: #5575a2;
        text-decoration: none;
    }

    .header-icon-btn:hover {
        background: #f0f3f8;
        box-shadow: 0 2px 8px rgba(85, 117, 162, 0.15);
    }

    .header-icon-btn svg {
        width: 20px;
        height: 20px;
    }

    .header-profile {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
        padding: 0.5rem 1rem;
        border-radius: 1rem;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        text-decoration: none;
    }

    .header-profile:hover {
        background: rgba(85, 117, 162, 0.08);
        transform: translateY(-2px);
    }

    .profile-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: linear-gradient(135deg, #5575a2 0%, #4a6a95 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        box-shadow: 0 4px 12px rgba(85, 117, 162, 0.25);
        flex-shrink: 0;
    }

    .profile-info {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        min-width: 0;
    }

    .profile-name {
        font-size: 0.85rem;
        font-weight: 700;
        color: #1a1a1a;
        margin: 0;
        line-height: 1.2;
    }

    .profile-role {
        font-size: 0.7rem;
        color: #9ca3af;
        margin: 0;
        font-weight: 500;
        text-transform: lowercase;
    }

    @media (max-width: 1200px) {
        .profile-info {
            display: none;
        }
    }

    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .page-title {
            font-size: 1.5rem;
        }

        .page-header-right {
            width: 100%;
            justify-content: space-between;
        }
    }
</style>
