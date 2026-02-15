<aside class="finance-sidebar" wire:persist="sidebar">
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
        <a href="{{ route('finance.dashboard') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('finance.dashboard') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                <polyline points="9 22 9 12 15 12 15 22"></polyline>
            </svg>
            <span class="menu-text">Dashboard</span>
        </a>

        <!-- REIMBURSEMENT Section -->
        <div class="menu-section-label">REIMBURSEMENT</div>

        <!-- Persetujuan -->
        @php
            $pendingFinanceCount = \App\Models\Pengajuan::where('status', 'menunggu_finance')->count();
        @endphp
        <a href="{{ route('finance.approval.index') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('finance.approval.*') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 11l3 3L22 4"></path>
                <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="menu-text">Persetujuan</span>
            <span class="badge-sidebar badge-approval-finance" 
                x-data="{ count: {{ $pendingFinanceCount }} }" 
                x-init="
                    window.addEventListener('refresh-notif-badges', () => {
                        fetch('{{ route('finance.approval.count') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(r => r.json())
                            .then(data => { count = data.pending_count; });
                    });
                    setInterval(() => {
                        fetch('{{ route('finance.approval.count') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(r => r.json())
                            .then(data => { count = data.pending_count; });
                    }, 10000);
                " 
                x-show="count > 0"
                x-cloak
                x-text="count > 99 ? '99+' : count">
            </span>
        </a>

        <!-- Pencairan -->
        @php
            $pendingDisbursementCount = \App\Models\Pengajuan::where('status', 'terkirim_accurate')
                ->whereNull('tanggal_pencairan')
                ->count();
        @endphp
        <a href="{{ route('finance.disbursement.index') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('finance.disbursement.*') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2v20m10-10H2"></path>
                <path d="M4 8l8-6 8 6"></path>
                <path d="M4 16l8 6 8-6"></path>
            </svg>
            <span class="menu-text">Pencairan</span>
            <span class="badge-sidebar badge-disbursement-finance" 
                x-data="{ count: {{ $pendingDisbursementCount }} }" 
                x-init="
                    window.addEventListener('refresh-notif-badges', () => {
                        fetch('{{ route('finance.disbursement.count') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(r => r.json())
                            .then(data => { count = data.pending_count; });
                    });
                    setInterval(() => {
                        fetch('{{ route('finance.disbursement.count') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(r => r.json())
                            .then(data => { count = data.pending_count; });
                    }, 10000);
                " 
                x-show="count > 0"
                x-cloak
                x-text="count > 99 ? '99+' : count">
            </span>
        </a>

         <!-- Notifikasi -->
        <a href="{{ route('finance.notifikasi') }}" wire:navigate.hover class="menu-item notifikasi-menu {{ request()->routeIs('finance.notifikasi*') ? 'active' : '' }}" x-data="{
            unreadCount: {{ \App\Models\Notifikasi::where('user_id', auth()->id())->where('is_read', false)->count() }}
        }" x-init="
            // Listen for notification events
            window.addEventListener('refresh-notif-badges', () => {
                fetch('{{ route('finance.notifikasi.count') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json())
                    .then(data => {
                        unreadCount = data.unread_count;
                    });
            });
            
            // Also listen for Livewire event
            if (typeof Livewire !== 'undefined') {
                Livewire.on('notifikasi-baru', () => {
                    setTimeout(() => {
                        fetch('{{ route('finance.notifikasi.count') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(r => r.json())
                            .then(data => {
                                unreadCount = data.unread_count;
                            });
                    }, 500);
                });
            }
            
            // Fallback polling every 30 seconds
            setInterval(() => {
                fetch('{{ route('finance.notifikasi.count') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json())
                    .then(data => {
                        if (data.unread_count !== unreadCount) {
                            unreadCount = data.unread_count;
                        }
                    });
            }, 30000);
        ">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <span class="menu-text">Notifikasi</span>
            <span class="notif-badge-sidebar" x-show="unreadCount > 0" x-text="unreadCount > 99 ? '99+' : unreadCount"></span>
        </a>

        <!-- MASTER DATA Section -->
        <div class="menu-section-label">MASTER DATA</div>

        <!-- User -->
        <a href="{{ route('finance.masterdata.users.index') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('finance.masterdata.users.*') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
            </svg>
            <span class="menu-text">User</span>
        </a>

        <!-- Departemen -->
        <a href="{{ route('finance.masterdata.departemen.index') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('finance.masterdata.departemen.*') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
            </svg>
            <span class="menu-text">Departemen</span>
        </a>

        <!-- Kategori Biaya -->
        <a href="{{ route('finance.masterdata.kategori_biaya.index') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('finance.masterdata.kategori_biaya.*') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                <line x1="7" y1="7" x2="7.01" y2="7"></line>
            </svg>
            <span class="menu-text">Kategori Biaya</span>
        </a>

        <!-- COA -->
        <a href="{{ route('finance.masterdata.coa.index') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('finance.masterdata.coa.*') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="8" y1="6" x2="21" y2="6"></line>
                <line x1="8" y1="12" x2="21" y2="12"></line>
                <line x1="8" y1="18" x2="21" y2="18"></line>
                <line x1="3" y1="6" x2="3.01" y2="6"></line>
                <line x1="3" y1="12" x2="3.01" y2="12"></line>
                <line x1="3" y1="18" x2="3.01" y2="18"></line>
            </svg>
            <span class="menu-text">COA</span>
        </a>

        <!-- Kas/Bank -->
        <a href="{{ route('finance.masterdata.kas_bank.index') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('finance.masterdata.kas_bank.*') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                <line x1="1" y1="10" x2="23" y2="10"></line>
            </svg>
            <span class="menu-text">Kas/Bank</span>
        </a>

        <!-- LAPORAN Section -->
        <div class="menu-section-label">LAPORAN</div>

        <!-- Jurnal Umum -->
        <a href="{{ route('finance.report.jurnal_umum') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('finance.report.jurnal_umum') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="12" y1="11" x2="12" y2="17"></line>
                <line x1="9" y1="14" x2="15" y2="14"></line>
            </svg>
            <span class="menu-text">Jurnal Umum</span>
        </a>

        <!-- Buku Besar -->
        <a href="{{ route('finance.report.buku_besar') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('finance.report.buku_besar') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
            <span class="menu-text">Buku Besar</span>
        </a>

        <!-- Rekonsiliasi -->
        <a href="{{ route('finance.report.reconciliation') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('finance.report.reconciliation') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 6L9 17l-5-5"></path>
                <path d="M12 2v4"></path>
                <path d="M12 18v4"></path>
                <path d="M4 12H2"></path>
                <path d="M22 12h-2"></path>
            </svg>
            <span class="menu-text">Rekonsiliasi</span>
        </a>

        <!-- Arus Kas -->
        <a href="{{ route('finance.report.laporan_arus_kas') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('finance.report.laporan_arus_kas') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="21 8 21 21 3 21 3 8"></polyline>
                <line x1="1" y1="3" x2="23" y2="3"></line>
                <path d="M10 12v4"></path>
                <path d="M14 12v4"></path>
            </svg>
            <span class="menu-text">Arus Kas</span>
        </a>

        <!-- Audit Budget -->
        <a href="{{ route('finance.report.budget_audit') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('finance.report.budget_audit') ? 'active' : '' }}">
            <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
            </svg>
            <span class="menu-text">Audit Budget</span>
        </a>

        <!-- AKUN Section -->
        <div class="menu-section-label">AKUN</div>

        <!-- Profil Saya -->
        <a href="{{ route('finance.profile.index') }}" wire:navigate.hover class="menu-item {{ request()->routeIs('finance.profile.*') ? 'active' : '' }}">
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
    /* Finance Sidebar */
    .finance-sidebar {
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
        padding-bottom: 0;
    }

    /* Modern thin scrollbar - Chrome/Safari */
    .finance-sidebar::-webkit-scrollbar {
        width: 2px;
    }

    .finance-sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    .finance-sidebar::-webkit-scrollbar-thumb {
        background: transparent;
        border-radius: 1px;
    }

    .finance-sidebar:hover::-webkit-scrollbar-thumb {
        background: #d0d9e7;
    }

    .finance-sidebar::-webkit-scrollbar-thumb:hover {
        background: #a6b8d3;
    }

    /* Firefox */
    .finance-sidebar {
        scrollbar-width: thin;
        scrollbar-color: transparent transparent;
    }

    .finance-sidebar:hover {
        scrollbar-color: #d0d9e7 transparent;
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
        background: #f4f7fa;
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
        padding: 0.5rem 0 5rem 0;
        overflow-y: auto;
        overflow-x: hidden;
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        padding-right: -1.5rem;
        position: relative;
    }

    /* Sliding Indicator - INSTANT (No Animation) */
    .menu-sliding-bg {
        position: absolute;
        left: 0;
        width: 100%;
        background: linear-gradient(135deg, #425d87 0%, #5575a2 100%);
        border-radius: 1.5rem;
        transition: none !important;
        z-index: 0;
        box-shadow: 0 4px 16px rgba(66, 93, 135, 0.4);
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
        transition: background-color 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.35s ease, transform 0.35s ease, box-shadow 0.35s ease;
        position: relative;
        background: transparent;
        border: none;
        cursor: pointer;
        z-index: 1;
    }

    .menu-item:hover {
        background: linear-gradient(135deg, rgba(66, 93, 135, 0.08) 0%, rgba(60, 83, 121, 0.08) 100%);
        color: #425d87;
        transform: translateX(4px);
    }

    .menu-item:active {
        transform: scale(0.92) translateX(4px);
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

    /* Main Menu Section Labels */
    .menu-section-label {
        padding: 1.25rem 1rem 0.5rem;
        font-size: 0.7rem;
        font-weight: 800;
        color: #c0cbd8;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-top: 0.75rem;
    }

    /* Logout Section - Fixed at bottom */
    .logout-section {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        padding: 1rem 1.5rem 1.5rem;
        border-top: 0.1px solid #f0f3f8;
        background: linear-gradient(to top, #ffffff, #fafbfc);
        border-radius: 0 0 2.5rem 0;
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
        .finance-sidebar {
            width: 100% !important;
            height: auto !important;
            position: relative !important;
            border-radius: 0 !important;
            padding: 1rem !important;
        }
    }
</style>

<script data-navigate-once>
function confirmLogout(event) {
    event.preventDefault();
    openConfirmModal(
        () => document.getElementById('logout-form').submit(),
        'Konfirmasi Logout',
        'Apakah Anda yakin ingin keluar dari sistem?'
    );
}
</script>