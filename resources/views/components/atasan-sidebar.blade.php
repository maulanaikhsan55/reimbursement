<aside class="atasan-sidebar" id="atasanSidebar" wire:persist="sidebar">
    <!-- Logo Section -->
    <div class="sidebar-header">
        <div class="logo-wrapper">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="sidebar-logo" loading="eager">
        </div>
    </div>

    <!-- Menu Section -->
    <nav class="sidebar-menu" style="position: relative;">
        <!-- Sliding Indicator -->
        <div class="menu-sliding-bg" id="sidebarIndicator"></div>

        <!-- OVERVIEW Section -->
        <div class="menu-section-label">OVERVIEW</div>
        
        <!-- Dashboard -->
        <a href="{{ route('atasan.dashboard') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('atasan.dashboard') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span class="menu-text">Dashboard</span>
        </a>

        <!-- PENGAJUAN & PERSETUJUAN Section -->
        <div class="menu-section-label">REIMBURSEMENT</div>

        <!-- Pengajuan Pribadi -->
        <a href="{{ route('atasan.pengajuan.index') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('atasan.pengajuan.*') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
            <span class="menu-text">Pengajuan</span>
        </a>

        <!-- Persetujuan Pengajuan -->
        <a href="{{ route('atasan.approval.index') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('atasan.approval.*') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 11l3 3L22 4"></path>
                <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="menu-text">Persetujuan</span>
            <span class="badge-sidebar badge-approval-atasan" data-badge-key="approval" style="display: none;">0</span>
        </a>

        <!-- Notifikasi -->
        <a href="{{ route('atasan.notifikasi') }}" wire:navigate.hover class="menu-item notifikasi-menu {{ request()->routeIs('atasan.notifikasi*') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <span class="menu-text">Notifikasi</span>
            <span class="notif-badge-sidebar" data-badge-key="notif" style="display: none;">0</span>
        </a>

        <!-- AKUN Section -->
        <div class="menu-section-label">AKUN</div>

        <!-- Profile -->
        <a href="{{ route('atasan.profile.index') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('atasan.profile*') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span class="menu-text">Profil Saya</span>
        </a>
    </nav>

    <!-- Logout - Fixed at bottom -->
    <div class="logout-section">
        <form method="POST" action="{{ route('logout') }}" id="logout-form">
            @csrf
            <button type="button" class="menu-item logout-item-menu" onclick="confirmLogout(event)">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                <span class="menu-text">Logout</span>
            </button>
        </form>
    </div>
</aside>

<style>
    /* Atasan Sidebar */
    .atasan-sidebar {
        width: 260px;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        z-index: 1000;
        box-shadow: 8px 0 32px rgba(66, 93, 135, 0.12);
        border-radius: 0 2.5rem 2.5rem 0;
        padding: 0.5rem 1.25rem 0 1.25rem;
        scroll-behavior: smooth;
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .atasan-sidebar::-webkit-scrollbar {
        width: 2px;
    }

    .atasan-sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    .atasan-sidebar::-webkit-scrollbar-thumb {
        background: transparent;
        border-radius: 1px;
    }

    .atasan-sidebar:hover::-webkit-scrollbar-thumb {
        background: #d0d9e7;
    }

    /* Logo Section */
    .sidebar-header {
        padding: 1.5rem 1rem 0.5rem;
        margin: 0;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .logo-wrapper {
        background: linear-gradient(180deg, #f7f9fd 0%, #f1f5fb 100%);
        border: 1px solid rgba(66, 93, 135, 0.1);
        padding: 0.75rem;
        border-radius: 1rem;
        display: flex;
        justify-content: center;
        align-items: center;
        width: 100%;
        min-height: 64px;
    }

    .sidebar-logo {
        width: auto;
        height: 40px;
        max-width: 100%;
        object-fit: contain;
    }

    /* Menu Section */
    .sidebar-menu {
        flex: 1;
        padding: 0.5rem 0 0.75rem 0;
        overflow-y: auto;
        overflow-x: hidden;
        scrollbar-gutter: stable both-edges;
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        position: relative;
    }

    /* Sliding Indicator */
    .menu-sliding-bg {
        position: absolute;
        left: 0;
        width: 100%;
        background: linear-gradient(135deg, #425d87 0%, #344d74 100%);
        border-radius: 1.5rem;
        transition: top 0.28s cubic-bezier(0.22, 1, 0.36, 1), height 0.24s ease, opacity 0.2s ease;
        z-index: 0;
        box-shadow: 0 10px 22px rgba(66, 93, 135, 0.32);
        opacity: 0;
        pointer-events: none;
        will-change: top, height, opacity;
    }

    .menu-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 1rem;
        border-radius: 1.5rem;
        font-size: 0.9rem;
        font-weight: 500;
        color: #1a1a1a;
        text-decoration: none;
        transition: background-color 0.26s ease, color 0.26s ease, transform 0.22s ease, box-shadow 0.26s ease;
        position: relative;
        background: transparent;
        border: none;
        cursor: pointer;
        z-index: 1;
    }

    .menu-item:hover {
        background: linear-gradient(135deg, rgba(85, 117, 162, 0.08) 0%, rgba(60, 83, 121, 0.08) 100%);
        color: #5575a2;
        transform: translateX(2px);
    }

    .menu-item:active {
        transform: scale(0.98) translateX(2px);
    }

    .menu-item:focus-visible {
        outline: 2px solid rgba(66, 93, 135, 0.35);
        outline-offset: 2px;
    }

    .menu-item.active {
        background: transparent !important;
        color: #ffffff;
        font-weight: 600;
        border-radius: 1.5rem;
        box-shadow: none !important;
    }

    .menu-icon {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        color: #5575a2;
        transition: color 0.35s ease;
    }

    .menu-item.active .menu-icon {
        color: #ffffff;
    }

    .menu-text {
        flex: 1;
        color: #1a1a1a;
    }

    .menu-item.active .menu-text {
        color: #ffffff;
    }

    .badge-sidebar,
    .notif-badge-sidebar {
        background: #ff5757;
        color: white;
        padding: 0.2rem 0.5rem;
        border-radius: 0.5rem;
        font-size: 0.65rem;
        font-weight: 700;
        white-space: nowrap;
        flex-shrink: 0;
        margin-left: auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
    }

    .menu-item.active .badge-sidebar,
    .menu-item.active .notif-badge-sidebar {
        background: rgba(255, 255, 255, 0.3);
    }

    /* Menu Section Labels */
    .menu-section-label {
        padding: 1.25rem 1.25rem 0.5rem;
        font-size: 0.7rem;
        font-weight: 800;
        color: #c0cbd8;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-top: 0.75rem;
    }

    /* Logout Section - Fixed at bottom */
    .logout-section {
        margin-top: auto;
        position: relative;
        padding: 1rem 1.5rem 1.5rem;
        border-top: 1px solid rgba(66, 93, 135, 0.1);
        background: #ffffff;
        border-radius: 0 0 2.5rem 0;
        z-index: 2;
    }

    .logout-section form {
        margin: 0;
        width: 100%;
    }

    .logout-item-menu {
        width: 100%;
        text-align: left;
        font-family: inherit;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 1rem;
        border-radius: 1.5rem;
        font-size: 0.9rem;
        font-weight: 500;
        color: #1a1a1a;
        text-decoration: none;
        transition: background-color 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.35s ease, transform 0.35s ease;
        position: relative;
        background: transparent;
        border: none;
        cursor: pointer;
    }
    
    .logout-item-menu .menu-icon,
    .logout-item-menu .menu-text {
        color: #ff5757 !important;
        transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    
    .logout-item-menu:hover {
        background: rgba(255, 87, 87, 0.08) !important;
        transform: translateX(4px);
    }
    
    .logout-item-menu:active {
        transform: scale(0.92) translateX(4px);
    }

    @media (max-width: 768px) {
        .atasan-sidebar {
            left: -280px;
        }
        
        .atasan-sidebar.show {
            left: 0;
        }
    }
</style>

<script data-navigate-once>
    function confirmLogout(event) {
        event.preventDefault();
        if (typeof openConfirmModal === 'function') {
            openConfirmModal(
                () => document.getElementById('logout-form').submit(),
                'Konfirmasi Logout',
                'Apakah Anda yakin ingin keluar dari sistem?'
            );
        } else {
            if (confirm('Apakah Anda yakin ingin keluar?')) {
                document.getElementById('logout-form').submit();
            }
        }
    }
</script>
