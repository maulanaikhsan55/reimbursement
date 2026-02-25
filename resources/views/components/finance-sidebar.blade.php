@php
    $isReimbursementActive = request()->routeIs('finance.approval.*')
        || request()->routeIs('finance.disbursement.*')
        || request()->routeIs('finance.notifikasi*');
    $isMasterDataActive = request()->routeIs('finance.masterdata.*');
    $isReportActive = request()->routeIs('finance.report.*');
@endphp

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
        <div class="menu-group {{ $isReimbursementActive ? 'is-open' : '' }}" data-menu-group="reimbursement">
            <button type="button" class="menu-item menu-group-toggle" data-menu-toggle aria-expanded="{{ $isReimbursementActive ? 'true' : 'false' }}" aria-controls="financeGroupReimbursement">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4"></path>
                    <path d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="menu-text">Proses</span>
                <svg class="menu-group-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 6 15 12 9 18"></polyline>
                </svg>
            </button>

            <div id="financeGroupReimbursement" class="menu-submenu" role="group" aria-label="Workflow reimbursement">
                <a href="{{ route('finance.approval.index') }}" wire:navigate.hover class="menu-item menu-sub-item {{ request()->routeIs('finance.approval.index') || request()->routeIs('finance.approval.show') ? 'active' : '' }}">
                    <span class="menu-sub-dot" aria-hidden="true"></span>
                    <span class="menu-text">Persetujuan</span>
                    <span class="badge-sidebar badge-approval-finance" data-badge-key="approval" style="display: none;">0</span>
                </a>

                <a href="{{ route('finance.approval.history') }}" wire:navigate.hover class="menu-item menu-sub-item {{ request()->routeIs('finance.approval.history*') ? 'active' : '' }}">
                    <span class="menu-sub-dot" aria-hidden="true"></span>
                    <span class="menu-text">Riwayat Persetujuan</span>
                </a>

                <a href="{{ route('finance.disbursement.index') }}" wire:navigate.hover class="menu-item menu-sub-item {{ request()->routeIs('finance.disbursement.index') || request()->routeIs('finance.disbursement.show') ? 'active' : '' }}">
                    <span class="menu-sub-dot" aria-hidden="true"></span>
                    <span class="menu-text">Pencairan</span>
                    <span class="badge-sidebar badge-disbursement-finance" data-badge-key="disbursement" style="display: none;">0</span>
                </a>

                <a href="{{ route('finance.disbursement.history') }}" wire:navigate.hover class="menu-item menu-sub-item {{ request()->routeIs('finance.disbursement.history*') ? 'active' : '' }}">
                    <span class="menu-sub-dot" aria-hidden="true"></span>
                    <span class="menu-text">Riwayat Pencairan</span>
                </a>

                <a href="{{ route('finance.notifikasi') }}" wire:navigate.hover class="menu-item menu-sub-item notifikasi-menu {{ request()->routeIs('finance.notifikasi*') ? 'active' : '' }}">
                    <span class="menu-sub-dot" aria-hidden="true"></span>
                    <span class="menu-text">Notifikasi</span>
                    <span class="notif-badge-sidebar" data-badge-key="notif" style="display: none;">0</span>
                </a>
            </div>
        </div>

        <!-- MASTER DATA Section -->
        <div class="menu-section-label">MASTER DATA</div>
        <div class="menu-group {{ $isMasterDataActive ? 'is-open' : '' }}" data-menu-group="masterdata">
            <button type="button" class="menu-item menu-group-toggle" data-menu-toggle aria-expanded="{{ $isMasterDataActive ? 'true' : 'false' }}" aria-controls="financeGroupMasterData">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 7h18"></path>
                    <path d="M3 12h18"></path>
                    <path d="M3 17h18"></path>
                </svg>
                <span class="menu-text">Master Data</span>
                <svg class="menu-group-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 6 15 12 9 18"></polyline>
                </svg>
            </button>

            <div id="financeGroupMasterData" class="menu-submenu" role="group" aria-label="Master data">
                <a href="{{ route('finance.masterdata.users.index') }}" wire:navigate.hover class="menu-item menu-sub-item {{ request()->routeIs('finance.masterdata.users.*') ? 'active' : '' }}">
                    <span class="menu-sub-dot" aria-hidden="true"></span>
                    <span class="menu-text">User</span>
                </a>

                <a href="{{ route('finance.masterdata.departemen.index') }}" wire:navigate.hover class="menu-item menu-sub-item {{ request()->routeIs('finance.masterdata.departemen.*') ? 'active' : '' }}">
                    <span class="menu-sub-dot" aria-hidden="true"></span>
                    <span class="menu-text">Departemen</span>
                </a>

                <a href="{{ route('finance.masterdata.kategori_biaya.index') }}" wire:navigate.hover class="menu-item menu-sub-item {{ request()->routeIs('finance.masterdata.kategori_biaya.*') ? 'active' : '' }}">
                    <span class="menu-sub-dot" aria-hidden="true"></span>
                    <span class="menu-text">Kategori Biaya</span>
                </a>

                <a href="{{ route('finance.masterdata.coa.index') }}" wire:navigate.hover class="menu-item menu-sub-item {{ request()->routeIs('finance.masterdata.coa.*') ? 'active' : '' }}">
                    <span class="menu-sub-dot" aria-hidden="true"></span>
                    <span class="menu-text">COA</span>
                </a>

                <a href="{{ route('finance.masterdata.kas_bank.index') }}" wire:navigate.hover class="menu-item menu-sub-item {{ request()->routeIs('finance.masterdata.kas_bank.*') ? 'active' : '' }}">
                    <span class="menu-sub-dot" aria-hidden="true"></span>
                    <span class="menu-text">Kas/Bank</span>
                </a>
            </div>
        </div>

        <!-- LAPORAN Section -->
        <div class="menu-section-label">LAPORAN</div>
        <div class="menu-group {{ $isReportActive ? 'is-open' : '' }}" data-menu-group="reports">
            <button type="button" class="menu-item menu-group-toggle" data-menu-toggle aria-expanded="{{ $isReportActive ? 'true' : 'false' }}" aria-controls="financeGroupReports">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                <span class="menu-text">Laporan Keuangan</span>
                <svg class="menu-group-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="9 6 15 12 9 18"></polyline>
                </svg>
            </button>

            <div id="financeGroupReports" class="menu-submenu" role="group" aria-label="Laporan keuangan">
                <a href="{{ route('finance.report.index') }}" wire:navigate.hover class="menu-item menu-sub-item {{ request()->routeIs('finance.report.index') ? 'active' : '' }}">
                    <span class="menu-sub-dot" aria-hidden="true"></span>
                    <span class="menu-text">Report Center</span>
                </a>

                <a href="{{ route('finance.report.jurnal_umum') }}" wire:navigate.hover class="menu-item menu-sub-item {{ request()->routeIs('finance.report.jurnal_umum') ? 'active' : '' }}">
                    <span class="menu-sub-dot" aria-hidden="true"></span>
                    <span class="menu-text">Jurnal Umum</span>
                </a>

                <a href="{{ route('finance.report.buku_besar') }}" wire:navigate.hover class="menu-item menu-sub-item {{ request()->routeIs('finance.report.buku_besar') ? 'active' : '' }}">
                    <span class="menu-sub-dot" aria-hidden="true"></span>
                    <span class="menu-text">Buku Besar</span>
                </a>

                <a href="{{ route('finance.report.reconciliation') }}" wire:navigate.hover class="menu-item menu-sub-item {{ request()->routeIs('finance.report.reconciliation') ? 'active' : '' }}">
                    <span class="menu-sub-dot" aria-hidden="true"></span>
                    <span class="menu-text">Rekonsiliasi</span>
                </a>

                <a href="{{ route('finance.report.laporan_arus_kas') }}" wire:navigate.hover class="menu-item menu-sub-item {{ request()->routeIs('finance.report.laporan_arus_kas') ? 'active' : '' }}">
                    <span class="menu-sub-dot" aria-hidden="true"></span>
                    <span class="menu-text">Arus Kas</span>
                </a>

                <a href="{{ route('finance.report.budget_audit') }}" wire:navigate.hover class="menu-item menu-sub-item {{ request()->routeIs('finance.report.budget_audit') ? 'active' : '' }}">
                    <span class="menu-sub-dot" aria-hidden="true"></span>
                    <span class="menu-text">Audit Budget</span>
                </a>
            </div>
        </div>

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
    :root {
        --finance-sidebar-primary: #425d87;
        --finance-sidebar-primary-700: #344d74;
        --finance-sidebar-ink: #17233b;
        --finance-sidebar-muted: #5f7393;
        --finance-sidebar-label: #9badc8;
        --finance-sidebar-danger: #e05555;
    }

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
        box-shadow: 8px 0 30px rgba(22, 37, 62, 0.12);
        border-radius: 0 2.5rem 2.5rem 0;
        padding: 0.5rem 1.25rem 0 1.25rem;
        scroll-behavior: smooth;
        padding-bottom: 0;
        transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
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
        padding-right: 0.35rem;
        position: relative;
    }

    /* Sliding Indicator */
    .menu-sliding-bg {
        position: absolute;
        left: 0;
        width: 100%;
        background: linear-gradient(135deg, var(--finance-sidebar-primary) 0%, var(--finance-sidebar-primary-700) 100%);
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
        color: var(--finance-sidebar-ink);
        text-decoration: none;
        transition: background-color 0.26s ease, color 0.26s ease, transform 0.22s ease, box-shadow 0.26s ease;
        position: relative;
        background: transparent;
        border: none;
        cursor: pointer;
        z-index: 1;
    }

    .menu-item:hover {
        background: linear-gradient(135deg, rgba(66, 93, 135, 0.08) 0%, rgba(66, 93, 135, 0.05) 100%);
        color: var(--finance-sidebar-primary);
        transform: translateX(2px);
    }

    .menu-item:active {
        transform: scale(0.98) translateX(2px);
    }

    .menu-group {
        display: grid;
        gap: 0.2rem;
    }

    .menu-group-toggle {
        width: 100%;
        text-align: left;
    }

    .menu-group-toggle .menu-icon,
    .menu-group-toggle .menu-text {
        color: var(--finance-sidebar-primary);
        font-weight: 600;
    }

    .menu-group-chevron {
        width: 16px;
        height: 16px;
        color: var(--finance-sidebar-muted);
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }

    .menu-group.is-open .menu-group-chevron {
        transform: rotate(90deg);
    }

    .menu-submenu {
        display: grid;
        gap: 0.2rem;
        max-height: 0;
        opacity: 0;
        overflow: hidden;
        transform: translateY(-4px);
        transition: max-height 0.3s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.24s ease, transform 0.24s ease, margin-top 0.24s ease;
        margin-top: 0;
        padding: 0 0.15rem 0 0.25rem;
        position: relative;
    }

    .menu-submenu::before {
        content: '';
        position: absolute;
        left: 1rem;
        top: 0.35rem;
        bottom: 0.35rem;
        width: 1px;
        background: linear-gradient(180deg, rgba(167, 184, 209, 0.18) 0%, rgba(136, 157, 189, 0.42) 50%, rgba(167, 184, 209, 0.18) 100%);
        border-radius: 999px;
        pointer-events: none;
    }

    .menu-group.is-open .menu-submenu {
        max-height: 520px;
        opacity: 1;
        transform: translateY(0);
        margin-top: 0.2rem;
    }

    .menu-sub-item {
        border-radius: 1rem;
        padding: 0.36rem 0.8rem 0.36rem 1.45rem;
        min-height: 32px;
        font-size: 0.82rem;
    }

    .menu-sub-dot {
        width: 5px;
        height: 5px;
        border-radius: 999px;
        background: #9eb3d2;
        flex-shrink: 0;
        margin-left: -0.22rem;
        box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.95);
    }

    .menu-sub-item .menu-text {
        color: #3f587f;
        font-weight: 600;
    }

    .menu-sub-item.active .menu-sub-dot {
        background: #ffffff;
    }

    .menu-sub-item.active .menu-text {
        color: #ffffff;
        font-weight: 600;
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
        color: var(--finance-sidebar-muted);
        transition: color 0.26s ease;
    }

    .menu-item.active .menu-icon {
        color: #ffffff;
    }

    .menu-text {
        flex: 1;
        color: var(--finance-sidebar-ink);
    }

    .menu-item.active .menu-text {
        color: #ffffff;
    }

    .badge-sidebar,
    .notif-badge-sidebar {
        background: var(--finance-sidebar-danger);
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
        color: var(--finance-sidebar-label);
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-top: 0.75rem;
    }

    /* Logout Section - sits below scrollable menu */
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
        color: var(--finance-sidebar-danger) !important;
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
            width: 280px !important;
            height: 100vh !important;
            position: fixed !important;
            left: -280px !important;
            top: 0 !important;
            border-radius: 0 2rem 2rem 0 !important;
            padding: 1rem !important;
            transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            visibility: visible !important;
            transform: none !important;
        }

        .finance-sidebar.show {
            left: 0 !important;
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

(function() {
    function setGroupOpen(group, open) {
        if (!group) return;
        group.classList.toggle('is-open', open);
        const toggle = group.querySelector('[data-menu-toggle]');
        if (toggle) toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function normalizePath(path) {
        const normalized = (path || '/').replace(/\/+$/, '');
        return normalized === '' ? '/' : normalized;
    }

    function syncFinanceActiveLink(sidebar) {
        if (!sidebar) return null;
        const links = Array.from(sidebar.querySelectorAll('a.menu-item[href]'));
        if (!links.length) return null;

        const currentPath = normalizePath(window.location.pathname);
        let bestLink = null;
        let bestScore = -1;

        links.forEach((link) => {
            let linkPath = '/';
            try {
                linkPath = normalizePath(new URL(link.href, window.location.origin).pathname);
            } catch (error) {
                linkPath = normalizePath(link.getAttribute('href') || '/');
            }

            let score = -1;
            if (currentPath === linkPath) {
                score = 1000 + linkPath.length;
            } else if (currentPath.startsWith(`${linkPath}/`)) {
                score = 500 + linkPath.length;
            }

            if (score > bestScore) {
                bestScore = score;
                bestLink = link;
            }
        });

        links.forEach((link) => link.classList.remove('active'));
        if (bestLink && bestScore >= 0) {
            bestLink.classList.add('active');
        }

        return bestLink && bestScore >= 0 ? bestLink : null;
    }

    function updateFinanceSidebarIndicator(sidebar) {
        if (!sidebar) return;
        const menuContainer = sidebar.querySelector('.sidebar-menu');
        const indicator = sidebar.querySelector('#sidebarIndicator');
        const activeMenu = sidebar.querySelector('a.menu-item.active');

        if (!menuContainer || !indicator || !activeMenu) {
            if (indicator) indicator.style.opacity = '0';
            return;
        }

        const menuRect = menuContainer.getBoundingClientRect();
        const activeRect = activeMenu.getBoundingClientRect();
        // Include scrollTop so indicator stays aligned while sidebar content scrolls.
        const relativeTop = activeRect.top - menuRect.top + menuContainer.scrollTop;
        const maxTop = Math.max(0, menuContainer.scrollHeight - activeRect.height);
        const safeTop = Math.max(0, Math.min(relativeTop, maxTop));

        indicator.style.opacity = '1';
        indicator.style.height = `${activeRect.height}px`;
        indicator.style.top = `${safeTop}px`;
    }

    function scheduleFinanceIndicatorRefresh(sidebar, durationMs = 420) {
        if (!sidebar) return;
        const startedAt = performance.now();

        const frame = (now) => {
            updateFinanceSidebarIndicator(sidebar);
            if (now - startedAt < durationMs) {
                requestAnimationFrame(frame);
            }
        };

        requestAnimationFrame(frame);
    }

    function scrollActiveMenuIntoView(sidebar, smooth = true) {
        if (!sidebar) return;
        const menuContainer = sidebar.querySelector('.sidebar-menu');
        const activeMenu = sidebar.querySelector('.menu-item.active');
        if (!menuContainer || !activeMenu) return;

        const padding = 18;
        const containerTop = menuContainer.scrollTop;
        const containerBottom = containerTop + menuContainer.clientHeight;
        const itemTop = activeMenu.offsetTop - padding;
        const itemBottom = activeMenu.offsetTop + activeMenu.offsetHeight + padding;

        if (itemTop < containerTop) {
            menuContainer.scrollTo({ top: Math.max(0, itemTop), behavior: smooth ? 'smooth' : 'auto' });
            return;
        }

        if (itemBottom > containerBottom) {
            const nextTop = Math.max(0, itemBottom - menuContainer.clientHeight);
            menuContainer.scrollTo({ top: nextTop, behavior: smooth ? 'smooth' : 'auto' });
        }
    }

    function initFinanceSidebarDropdown() {
        const sidebar = document.querySelector('.finance-sidebar');
        if (!sidebar) return;

        const groups = Array.from(sidebar.querySelectorAll('.menu-group[data-menu-group]'));
        if (!groups.length) return;

        const activeLink = syncFinanceActiveLink(sidebar);

        groups.forEach((group) => {
            const toggle = group.querySelector('[data-menu-toggle]');
            const activeChild = activeLink && group.contains(activeLink);
            const shouldOpen = Boolean(activeChild);

            setGroupOpen(group, shouldOpen);

            const submenu = group.querySelector('.menu-submenu');
            if (submenu && submenu.dataset.indicatorBound !== 'true') {
                submenu.dataset.indicatorBound = 'true';
                submenu.addEventListener('transitionend', (event) => {
                    if (event.propertyName === 'max-height' || event.propertyName === 'transform') {
                        scheduleFinanceIndicatorRefresh(sidebar, 260);
                    }
                });
            }

            if (!toggle || toggle.dataset.bound === 'true') return;
            toggle.dataset.bound = 'true';
            toggle.addEventListener('click', () => {
                const nextOpen = !group.classList.contains('is-open');
                groups.forEach((otherGroup) => {
                    if (otherGroup !== group) setGroupOpen(otherGroup, false);
                });
                setGroupOpen(group, nextOpen);
                setTimeout(() => scrollActiveMenuIntoView(sidebar, true), 40);
                scheduleFinanceIndicatorRefresh(sidebar);
            });
        });

        const menuContainer = sidebar.querySelector('.sidebar-menu');
        if (menuContainer && menuContainer.dataset.indicatorBound !== 'true') {
            menuContainer.dataset.indicatorBound = 'true';
            menuContainer.addEventListener('scroll', () => updateFinanceSidebarIndicator(sidebar), { passive: true });
        }

        if (!sidebar.dataset.resizeBound) {
            sidebar.dataset.resizeBound = 'true';
            window.addEventListener('resize', () => updateFinanceSidebarIndicator(sidebar));
        }

        requestAnimationFrame(() => {
            scheduleFinanceIndicatorRefresh(sidebar, 460);
            setTimeout(() => {
                scrollActiveMenuIntoView(sidebar, true);
                scheduleFinanceIndicatorRefresh(sidebar, 460);
            }, 120);
        });
    }

    if (document.readyState === 'complete') {
        initFinanceSidebarDropdown();
    } else {
        document.addEventListener('DOMContentLoaded', initFinanceSidebarDropdown);
    }
    document.addEventListener('livewire:navigated', initFinanceSidebarDropdown);
})();

</script>
