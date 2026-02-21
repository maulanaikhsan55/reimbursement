@extends('layouts.app')

@section('title', 'Dashboard Finance - Pusat Kontrol Keuangan')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/finance-dashboard.css') }}">
<style>
    @keyframes pulse-red {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        70% { transform: scale(1.02); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
    
    @keyframes pulse-green {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
        70% { transform: scale(1.02); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    @keyframes slideIn {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .sla-alert {
        animation: pulse-red 2s infinite;
        border-color: #fca5a5 !important;
        background: #fef2f2 !important;
    }

    .urgent-alert {
        animation: pulse-red 1.5s infinite;
    }

    .success-alert {
        animation: pulse-green 2s infinite;
    }

    .chart-toggle-btn {
        transition: all 0.2s ease;
    }

    .chart-toggle-btn.active {
        background: #425d87;
        color: white;
        box-shadow: 0 4px 12px rgba(66, 93, 135, 0.3);
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .info-card:hover {
        transform: translateY(-3px);
    }

    .quick-action-item:hover {
        transform: translateY(-2px);
        background: #f1f5f9;
    }

    .dashboard-wrapper {
        --fd-accent: #3f5f94;
        --fd-accent-700: #2e4b79;
        --fd-ink: #1b2a42;
        --fd-muted: #5f6f89;
        --fd-surface: #f9fbff;
        --fd-surface-alt: #f3f6fb;
        --fd-stroke: rgba(76, 101, 138, 0.17);
        --fd-shadow: 0 16px 30px rgba(22, 36, 63, 0.08);
        background:
            radial-gradient(920px 360px at 8% -18%, rgba(62, 95, 146, 0.12), transparent 70%),
            radial-gradient(860px 320px at 92% -22%, rgba(19, 95, 160, 0.09), transparent 72%),
            linear-gradient(140deg, #f4f7fc 0%, #edf2f9 100%);
    }

    .dashboard-container {
        max-width: 1420px;
    }

    .dashboard-card {
        animation: slideIn 0.5s ease-out;
    }

    .dashboard-content {
        gap: 0.68rem;
    }

    .dashboard-content .section-title {
        font-size: 1.02rem !important;
    }

    .dashboard-content .section-subtitle {
        font-size: 0.76rem !important;
    }

    .dashboard-content .card-title {
        font-size: 0.9rem !important;
    }

    .dashboard-wrapper .top-overview-grid,
    .dashboard-wrapper .finance-grid-2-1,
    .dashboard-wrapper .finance-grid-3-2 {
        gap: 0.72rem;
    }

    .dashboard-wrapper .priority-actions,
    .dashboard-wrapper .chart-card,
    .dashboard-wrapper .modern-section,
    .dashboard-wrapper .widget-card,
    .dashboard-wrapper .info-card,
    .dashboard-wrapper .stat-card.modern {
        border: 1px solid var(--fd-stroke) !important;
        background: linear-gradient(165deg, #ffffff 0%, var(--fd-surface) 100%) !important;
        box-shadow: var(--fd-shadow) !important;
        border-radius: 1.2rem !important;
    }

    .dashboard-wrapper .priority-actions {
        padding: 0.72rem 0.75rem;
    }

    .dashboard-wrapper .priority-head h3 {
        font-size: 0.9rem;
        color: var(--fd-ink);
    }

    .dashboard-wrapper .priority-head p {
        font-size: 0.72rem;
        color: var(--fd-muted);
    }

    .dashboard-wrapper .priority-actions-grid {
        gap: 0.45rem;
    }

    .dashboard-wrapper .priority-action-item {
        min-height: 58px;
        padding: 0.6rem 0.65rem;
        border-radius: 0.85rem;
    }

    .dashboard-wrapper .priority-action-content strong {
        font-size: 0.78rem;
    }

    .dashboard-wrapper .priority-action-content small {
        font-size: 0.67rem;
    }

    .welcome-title {
        font-size: 1.18rem;
    }

    .welcome-subtitle {
        font-size: 0.78rem;
    }

    .dashboard-wrapper .welcome-card,
    .dashboard-wrapper .budget-card {
        border-radius: 1.22rem;
        min-height: 170px;
        box-shadow: 0 18px 28px rgba(33, 56, 95, 0.2);
    }

    .dashboard-wrapper .mini-stat .value {
        font-size: 1.3rem;
    }

    .dashboard-wrapper .card-balance .balance-amount {
        font-size: 1.55rem;
    }

    .dashboard-wrapper .chart-card {
        padding: 1.08rem;
    }

    .dashboard-wrapper .card-subtitle {
        color: #6f80a0;
    }

    .dashboard-wrapper .compact-chart {
        min-height: 215px;
    }

    .dashboard-wrapper .donut-wrapper {
        width: 172px;
        height: 172px;
    }

    .dashboard-wrapper .stats-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.62rem;
    }

    .dashboard-wrapper .stat-card.modern {
        min-height: 96px;
        padding: 0.8rem 0.85rem;
    }

    .dashboard-wrapper .stat-label {
        font-size: 0.78rem;
    }

    .dashboard-wrapper .stat-value {
        font-size: 1.46rem;
        margin-bottom: 0.22rem;
    }

    .dashboard-wrapper .info-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.62rem;
    }

    .dashboard-wrapper .info-card {
        padding: 1rem;
    }

    .dashboard-wrapper .info-main-value {
        font-size: 1.5rem;
    }

    .dashboard-wrapper .compact-scroll {
        max-height: 286px;
    }

    .finance-grid-3-2,
    .finance-grid-2-1 {
        gap: 0.8rem;
    }

    .smart-analytics-grid {
        grid-template-columns: minmax(0, 1.65fr) minmax(300px, 1fr);
        align-items: start;
    }

    .smart-side-stack {
        display: grid;
        grid-template-rows: auto auto;
        gap: 0.75rem;
        height: auto;
        align-self: start;
    }

    .finance-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.72rem;
    }

    .finance-kpi-grid .stat-card.modern {
        min-height: 92px;
        padding: 0.74rem 0.84rem;
    }

    .finance-kpi-grid .stat-value {
        font-size: 1.22rem;
        margin-bottom: 0.15rem;
        line-height: 1.15;
    }

    .finance-kpi-grid .stat-value.currency {
        font-size: 1.04rem;
    }

    .finance-kpi-grid .stat-note {
        display: block;
        font-size: 0.68rem;
        color: #8b9cb5;
        font-weight: 600;
    }

    .smart-rail-widget {
        padding-top: 0.95rem;
        padding-bottom: 0.95rem;
    }

    .smart-rail-widget .top-requesters-list {
        gap: 0.55rem;
    }

    .ops-split-grid {
        grid-template-columns: minmax(0, 1.65fr) minmax(300px, 1fr);
        align-items: start;
    }

    .ops-split-grid .activity-compact-card {
        min-height: 0;
    }

    .ops-top-requesters .top-requesters-list {
        max-height: 430px;
        overflow: auto;
        padding-right: 0.2rem;
    }

    .finance-grid-3-2 > .modern-section {
        height: 100%;
        display: flex;
        flex-direction: column;
    }

    .finance-grid-3-2 .data-table-wrapper {
        margin-top: 0.3rem;
    }

    .finance-grid-3-2 .rejection-list > div {
        margin-bottom: 0.8rem !important;
    }

    .compact-workspace {
        margin-top: 0;
    }

    .compact-workspace .modern-section {
        margin-bottom: 0 !important;
    }

    .activity-compact-card .data-table-wrapper,
    .compact-insight-card .data-table-wrapper {
        margin-top: 0.35rem;
    }

    .activity-compact-card .section-header {
        align-items: flex-start;
    }

    .activity-compact-card .section-header > div {
        text-align: left;
    }

    .activity-compact-card .section-title {
        font-size: 0.9rem !important;
        text-align: left;
        line-height: 1.2;
    }

    .activity-compact-card .section-subtitle {
        font-size: 0.76rem !important;
        text-align: left;
    }

    .activity-compact-card .btn-text {
        font-size: 0.86rem;
        align-self: flex-start;
    }

    .rejection-item {
        margin-bottom: 0.85rem;
    }

    .rejection-item:last-child {
        margin-bottom: 0;
    }

    .is-focus-hidden {
        display: none !important;
    }

    .dashboard-filter-strip {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 0.9rem;
        padding: 0 0.2rem 0.58rem;
        border-bottom: 1px solid rgba(76, 101, 138, 0.2);
    }

    .filter-strip-kicker {
        margin: 0 0 0.08rem;
        font-size: 0.64rem;
        font-weight: 700;
        color: #6d7f9f;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }

    .filter-strip-title {
        margin: 0;
        font-size: 1.02rem;
        line-height: 1.2;
        font-weight: 700;
        color: #1d2f4b;
    }

    .filter-date-form {
        margin-left: auto;
        display: flex;
        align-items: center;
        gap: 0.44rem;
        flex-wrap: wrap;
    }

    .filter-date-input {
        min-width: 148px;
        border: 1px solid #d3deec;
        background: #f8fbff;
        border-radius: 0.72rem;
        color: #364861;
        font-size: 0.74rem;
        font-weight: 600;
        font-family: inherit;
        padding: 0.42rem 0.56rem;
        outline: none;
        transition: all 0.2s ease;
    }

    .filter-date-input:focus {
        border-color: #9fb5d6;
        background: #ffffff;
        box-shadow: 0 0 0 2px rgba(66, 93, 135, 0.14);
    }

    .filter-date-separator {
        font-size: 0.84rem;
        color: #7b8ca8;
        font-weight: 700;
    }

    .filter-date-btn {
        border: 1px solid #425d87;
        background: #425d87;
        color: #ffffff;
        border-radius: 0.72rem;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 0.42rem 0.7rem;
        cursor: pointer;
        transition: all 0.2s ease;
        font-family: inherit;
    }

    .filter-date-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 14px rgba(66, 93, 135, 0.22);
    }

    .filter-date-btn.secondary {
        background: #ffffff;
        border-color: #d3deec;
        color: #5d7192;
        box-shadow: none;
    }

    .rejection-item {
        border: 1px solid #e8eef7;
        border-radius: 0.9rem;
        padding: 0.62rem 0.72rem;
        background: linear-gradient(145deg, #ffffff, #f8fbff);
    }

    .rejection-list .progress-track {
        background: linear-gradient(90deg, #eef2ff 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
    }

    @media (max-width: 1024px) {
        .dashboard-wrapper .stats-grid,
        .dashboard-wrapper .info-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .finance-kpi-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-filter-strip {
            flex-direction: column;
            align-items: flex-start;
        }

        .filter-date-form {
            width: 100%;
            margin-left: 0;
        }
    }

    @media (max-width: 900px) {
        .dashboard-wrapper .top-overview-grid,
        .dashboard-wrapper .finance-grid-2-1,
        .dashboard-wrapper .finance-grid-3-2 {
            grid-template-columns: 1fr;
        }

        .smart-side-stack {
            grid-template-rows: auto;
        }

        .dashboard-wrapper .priority-actions-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 640px) {
        .dashboard-wrapper .stats-grid,
        .dashboard-wrapper .info-grid,
        .dashboard-wrapper .priority-actions-grid {
            grid-template-columns: 1fr;
        }

        .finance-kpi-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-wrapper .compact-chart {
            min-height: 198px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (function() {
        window.financeChartData = {
            activity: @json($dailyTrend ?? []),
            distribution: @json($departementDistribution ?? []),
            status: @json($statusDistribution ?? []),
            category: @json($categoryDistribution ?? []),
            monthly: @json($monthlyTrend ?? [])
        };

        const refreshFinanceDashboardSections = async () => {
            if (window.__financeDashboardPartialRefreshBusy) return;
            window.__financeDashboardPartialRefreshBusy = true;

            const selectors = [
                '.finance-kpi-grid',
                '.top-overview-grid',
                '.compact-workspace',
            ];

            try {
                const response = await fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');

                selectors.forEach((selector) => {
                    const currentEl = document.querySelector(selector);
                    const nextEl = doc.querySelector(selector);
                    if (currentEl && nextEl) {
                        currentEl.outerHTML = nextEl.outerHTML;
                    }
                });
            } catch (error) {
                console.error('[Finance Dashboard] Partial refresh failed:', error);
            } finally {
                window.__financeDashboardPartialRefreshBusy = false;
            }
        };

        window.removeEventListener('refresh-approval-table', window.__financeDashboardPartialRefreshHandler);
        window.removeEventListener('refresh-pengajuan-table', window.__financeDashboardPartialRefreshHandler);
        window.__financeDashboardPartialRefreshHandler = refreshFinanceDashboardSections;
        window.addEventListener('refresh-approval-table', window.__financeDashboardPartialRefreshHandler);
        window.addEventListener('refresh-pengajuan-table', window.__financeDashboardPartialRefreshHandler);
    })();
</script>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header 
            title="Dashboard Finance" 
            subtitle="Pusat Kontrol Keuangan Reimbursement" 
            :showNotification="true" 
            :showProfile="true" 
        />

        <div class="dashboard-content">
            @php
                $remainingBudget = max(($monthly_budget ?? 0) - ($this_month_processed_amount ?? 0), 0);
                $budgetUsage = ($monthly_budget ?? 0) > 0 ? (($this_month_processed_amount ?? 0) / $monthly_budget) * 100 : 0;
                $financeAlerts = ($oversla_count ?? 0) + ($waiting_finance_count ?? 0);
            @endphp

            <section class="dashboard-filter-strip">
                <div>
                    <p class="filter-strip-kicker">Filter Dashboard</p>
                    <h3 class="filter-strip-title">Rentang Tanggal Aktivitas Finance</h3>
                </div>
                <form id="dashboardDateFilterForm" class="filter-date-form" onsubmit="return false;">
                    <input id="filterDateStart" type="date" class="filter-date-input" aria-label="Tanggal mulai">
                    <span class="filter-date-separator">-</span>
                    <input id="filterDateEnd" type="date" class="filter-date-input" aria-label="Tanggal akhir">
                    <button type="button" id="applyDateFilterBtn" class="filter-date-btn">Terapkan</button>
                    <button type="button" id="resetDateFilterBtn" class="filter-date-btn secondary">Reset</button>
                </form>
            </section>

            <div class="finance-kpi-grid" data-focus="essential">
                <div class="stat-card modern dashboard-card" style="animation-delay: 0.04s;">
                    <div class="stat-left">
                        <div class="stat-label">Antrian Finance</div>
                        <div class="stat-value">{{ number_format($waiting_finance_count ?? 0) }}</div>
                        <span class="stat-note">Menunggu aksi tim finance</span>
                    </div>
                    <div class="stat-icon warning-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="stat-card modern dashboard-card" style="animation-delay: 0.08s;">
                    <div class="stat-left">
                        <div class="stat-label">Alert SLA</div>
                        <div class="stat-value">{{ number_format($oversla_count ?? 0) }}</div>
                        <span class="stat-note">{{ number_format($financeAlerts) }} total alert proses</span>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%); color: #dc2626;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                </div>
                <div class="stat-card modern dashboard-card" style="animation-delay: 0.12s;">
                    <div class="stat-left">
                        <div class="stat-label">Pencairan Bulan Ini</div>
                        <div class="stat-value currency">{{ format_rupiah($this_month_disbursed ?? 0) }}</div>
                        <span class="stat-note">Arus keluar terverifikasi</span>
                    </div>
                    <div class="stat-icon success-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                </div>
                <div class="stat-card modern dashboard-card" style="animation-delay: 0.16s;">
                    <div class="stat-left">
                        <div class="stat-label">Sisa Anggaran</div>
                        <div class="stat-value currency">{{ format_rupiah($remainingBudget) }}</div>
                        <span class="stat-note">{{ number_format(min($budgetUsage, 100), 1) }}% budget terpakai</span>
                    </div>
                    <div class="stat-icon primary-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                            <line x1="2" y1="10" x2="22" y2="10"></line>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- ===== WELCOME & BUDGET CARDS ===== -->
            <div class="top-overview-grid" data-focus="essential">
                <!-- Welcome Card -->
                <div class="welcome-card dashboard-card" style="animation-delay: 0.1s;">
                    <div class="welcome-content">
                        <div class="welcome-avatar">
                            <span class="avatar-initial">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        </div>
                        <div class="welcome-text">
                            <h2 class="welcome-title">Halo, {{ explode(' ', Auth::user()->name)[0] }}!</h2>
                            <p class="welcome-subtitle">Finance Control Center</p>
                            <div style="margin-top: 1rem; display: flex; gap: 1.5rem;">
                                <div class="mini-stat">
                                    <span class="label">Persetujuan</span>
                                    <span class="value">{{ $total_pending_count ?? 0 }}</span>
                                </div>
                                <div class="stat-divider"></div>
                                <div class="mini-stat">
                                    <span class="label">Pengajuan Baru</span>
                                    <span class="value">{{ $requests_today ?? 0 }}</span>
                                </div>
                                <div class="stat-divider"></div>
                                <div class="mini-stat">
                                    <span class="label">SLA Alert</span>
                                    <span class="value" style="color: {{ $oversla_count > 0 ? '#fca5a5' : '#bbf7d0' }}">{{ $oversla_count ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Virtual Budget Card -->
                <div class="budget-card dashboard-card" style="animation-delay: 0.2s;">
                    <div class="card-chip">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="2" y="5" width="20" height="14" rx="2" />
                            <line x1="2" y1="10" x2="22" y2="10" />
                        </svg>
                    </div>
                    <div class="card-balance">
                        <span class="balance-label">Total Realisasi Anggaran</span>
                        <h3 class="balance-amount">{{ format_rupiah($this_month_processed_amount ?? 0) }}</h3>
                    </div>
                    <div class="card-footer">
                        <div class="card-holder">
                            <span class="holder-label">Limit Anggaran Perusahaan</span>
                            <span class="holder-name">Rp {{ number_format($monthly_budget ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="card-logo">
                            <div class="circle c1"></div>
                            <div class="circle c2"></div>
                        </div>
                    </div>
                    <div class="card-pattern"></div>
                </div>
            </div>

            <!-- ===== PRIORITY QUICK ACTIONS ===== -->
            <div class="priority-actions dashboard-card" style="animation-delay: 0.25s;" data-focus="essential">
                <div class="priority-head">
                    <h3>Aksi Prioritas Hari Ini</h3>
                    <p>Mulai dari proses yang paling berdampak ke cashflow.</p>
                </div>
                <div class="priority-actions-grid">
                    <a href="{{ route('finance.approval.index') }}" class="priority-action-item">
                        <span class="priority-action-icon warning">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </span>
                        <span class="priority-action-content">
                            <strong>Persetujuan</strong>
                            <small>{{ $total_pending_count ?? 0 }} menunggu verifikasi</small>
                        </span>
                    </a>
                    <a href="{{ route('finance.disbursement.index') }}" class="priority-action-item">
                        <span class="priority-action-icon success">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                        </span>
                        <span class="priority-action-content">
                            <strong>Pencairan Dana</strong>
                            <small>Proses transfer ke pegawai</small>
                        </span>
                    </a>
                    <a href="{{ route('finance.report.index') }}" class="priority-action-item">
                        <span class="priority-action-icon info">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                            </svg>
                        </span>
                        <span class="priority-action-content">
                            <strong>Laporan Keuangan</strong>
                            <small>Buku besar, jurnal, arus kas</small>
                        </span>
                    </a>
                    <a href="{{ route('finance.masterdata.users.index') }}" class="priority-action-item">
                        <span class="priority-action-icon primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </span>
                        <span class="priority-action-content">
                            <strong>Kelola User</strong>
                            <small>Pegawai & Atasan</small>
                        </span>
                    </a>
                </div>
            </div>

            <!-- ===== CHARTS GRID ===== -->
            <div class="finance-grid-2-1 compact-top smart-analytics-grid" data-focus="essential">
                <!-- Activity Trend Chart -->
                <div class="chart-card dashboard-card" style="animation-delay: 0.3s;">
                    <div class="card-header">
                        <div class="header-title">
                            <h3 class="card-title">Aktivitas Pengajuan</h3>
                            <span class="card-subtitle" id="activityPeriodLabel">Tren aktivitas sesuai rentang tanggal</span>
                        </div>
                        <div class="chart-toggle-group">
                            <button onclick="toggleActivityChart('volume')" id="btnActivityVolume" class="chart-toggle-btn active">
                                Volume
                            </button>
                            <button onclick="toggleActivityChart('nominal')" id="btnActivityNominal" class="chart-toggle-btn">
                                Nominal
                            </button>
                        </div>
                    </div>
                    <div class="chart-container compact-chart">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>

                <!-- Smart Right Rail -->
                <div class="smart-side-stack">
                    <!-- Department Distribution Pie Chart -->
                    <div class="chart-card dashboard-card" style="animation-delay: 0.4s;">
                        <div class="card-header">
                            <div class="header-title">
                                <h3 class="card-title">Distribusi Departemen</h3>
                                <span class="card-subtitle">Berdasarkan volume</span>
                            </div>
                        </div>
                        <div class="chart-container compact-chart compact-center">
                            <div class="donut-wrapper">
                                <canvas id="statusChart"></canvas>
                                <div class="donut-center">
                                    <div class="donut-count">{{ count($departementDistribution ?? []) }}</div>
                                    <div class="donut-label">Dept</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ===== MANAGEMENT STATS ===== -->
            <div class="stats-grid dashboard-card" style="animation-delay: 0.5s;" data-focus="support">
                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-label">Total Anggota/Pegawai</div>
                        <div class="stat-value">{{ $totalUsers ?? 0 }} <span style="font-size: 0.8rem; color: #94a3b8; font-weight: 500;">User Aktif</span></div>
                    </div>
                    <div class="stat-icon primary-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                </div>

                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-label">Struktur Departemen</div>
                        <div class="stat-value">{{ $totalDepartemen ?? 0 }} <span style="font-size: 0.8rem; color: #94a3b8; font-weight: 500;">Unit Kerja</span></div>
                    </div>
                    <div class="stat-icon info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                        </svg>
                    </div>
                </div>

                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-label">Kategori Biaya</div>
                        <div class="stat-value">{{ $totalCategories ?? 0 }} <span style="font-size: 0.8rem; color: #94a3b8; font-weight: 500;">COA Terpetakan</span></div>
                    </div>
                    <div class="stat-icon success-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- ===== FINANCIAL INFO CARDS ===== -->
            <div class="info-grid" data-focus="support">
                <!-- Pengajuan Masuk -->
                <div class="info-card dashboard-card" style="animation-delay: 0.4s;">
                    <div class="info-header">
                        <div>
                            <div class="info-title">Pengajuan Masuk (Bulan Ini)</div>
                            <div class="info-main-value">{{ format_rupiah($this_month_amount ?? 0) }}</div>
                        </div>
                        <div class="info-icon-wrapper icon-blue">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <div class="info-target-section">
                        <div class="target-label">
                            <span>Penggunaan Budget</span>
                            <span>{{ round(($budget_usage_percent ?? 0), 1) }}%</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ min(($budget_usage_percent ?? 0), 100) }}%; background: #3b82f6;"></div>
                        </div>
                    </div>

                    <div class="info-footer">
                        <div class="footer-stat">
                            <span class="footer-label">Rata-rata</span>
                            <span class="footer-value">{{ format_rupiah($avg_request_amount ?? 0) }}</span>
                        </div>
                        <div class="footer-stat" style="align-items: flex-end;">
                            <span class="footer-label">Frekuensi</span>
                            <span class="footer-value">{{ $this_month_count ?? 0 }}x</span>
                        </div>
                    </div>
                </div>

                <!-- Dana Dicairkan -->
                <div class="info-card dashboard-card" style="animation-delay: 0.5s;">
                    <div class="info-header">
                        <div>
                            <div class="info-title">Dana Dicairkan (Bulan Ini)</div>
                            <div class="info-main-value">{{ format_rupiah($this_month_disbursed ?? 0) }}</div>
                        </div>
                        <div class="info-icon-wrapper icon-green">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                                <line x1="12" y1="1" x2="12" y2="23"></line>
                                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                            </svg>
                        </div>
                    </div>

                    <div class="info-target-section">
                        <div class="trend-indicator {{ ($growth_percentage ?? 0) >= 0 ? 'trend-up' : 'trend-down' }}">
                            @if(($growth_percentage ?? 0) >= 0)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                    <polyline points="17 6 23 6 23 12"></polyline>
                                </svg>
                                +{{ round(($growth_percentage ?? 0), 1) }}%
                            @else
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                    <polyline points="23 18 13.5 8.5 8.5 13.5 1 6"></polyline>
                                    <polyline points="17 18 23 18 23 12"></polyline>
                                </svg>
                                {{ round(($growth_percentage ?? 0), 1) }}%
                            @endif
                            <span>vs bulan lalu</span>
                        </div>
                    </div>

                    <div class="info-footer">
                        <div class="footer-stat">
                            <span class="footer-label">Total Lifetime</span>
                            <span class="footer-value">{{ format_rupiah($lifetime_disbursed ?? 0) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Menunggu Verifikasi -->
                <div class="info-card dashboard-card" style="animation-delay: 0.6s;">
                    <div class="info-header">
                        <div>
                            <div class="info-title">Menunggu Verifikasi</div>
                            <div class="info-main-value">{{ $waiting_finance_count ?? 0 }}</div>
                        </div>
                        <div class="info-icon-wrapper icon-orange">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                    </div>

                    <div class="info-target-section">
                         <div class="target-label">
                            <span>Nominal Pending</span>
                            <span>{{ format_rupiah($waiting_finance_amount ?? 0) }}</span>
                        </div>
                        <div class="progress-track">
                            @php 
                                $pendingPercent = ($this_month_count ?? 0) > 0 ? (($waiting_finance_count ?? 0) / ($this_month_count ?? 1)) * 100 : 0;
                                $visualPercent = min($pendingPercent * 2, 100); 
                            @endphp
                            <div class="progress-fill" style="width: {{ $visualPercent }}%; background: #f59e0b;"></div>
                        </div>
                    </div>

                    <div class="info-footer">
                        <div class="footer-stat">
                            <span class="footer-label">Pending di Atasan</span>
                            <span class="footer-value">{{ format_rupiah($waiting_atasan_amount ?? 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== SLA ALERT ===== -->
            @if(($oversla_count ?? 0) > 0)
            <div class="modern-section sla-alert dashboard-card" style="margin-bottom: 0; animation-delay: 0.7s;">
                <div style="display: flex; align-items: start; gap: 1rem;">
                    <div style="background: #fee2e2; width: 56px; height: 56px; border-radius: 1rem; display: flex; align-items: center; justify-content: center; color: #ef4444; flex-shrink: 0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="28" height="28">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                    <div style="flex: 1;">
                        <h2 style="margin: 0 0 0.5rem 0; color: #991b1b; font-size: 1.1rem; font-weight: 800;">
                            Perhatian: {{ $oversla_count }} Pengajuan Melebihi SLA ({{ $slaDays ?? 3 }} Hari)
                        </h2>
                        <p style="margin: 0; color: #b91c1c; font-size: 0.9rem; opacity: 0.85;">
                            Tindakan segera diperlukan. Pengajuan yang overdue terkumpul dengan nominal tertahan {{ format_rupiah($oversla_nominal ?? 0) }}.
                        </p>
                    </div>
                    <a href="{{ route('finance.approval.index', ['sla_overdue' => 1]) }}" style="background: #ef4444; color: white; padding: 0.75rem 1.5rem; border-radius: 0.875rem; font-size: 0.875rem; font-weight: 700; text-decoration: none; transition: all 0.2s; align-self: center; white-space: nowrap;">
                        Lihat Detail SLA
                    </a>
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 1.5rem;">
                    <div class="stat-card modern" style="box-shadow: none; border: 1px solid #fee2e2; background: white; min-height: 90px; padding: 1rem;">
                        <div class="stat-left">
                            <div class="stat-value" style="color: #dc2626; font-size: 1.5rem;">{{ $oversla_count ?? 0 }}</div>
                            <div class="stat-label">Total Outstanding</div>
                        </div>
                    </div>
                    <div class="stat-card modern" style="box-shadow: none; border: 1px solid #fee2e2; background: white; min-height: 90px; padding: 1rem;">
                        <div class="stat-left">
                            <div class="stat-value" style="color: #dc2626; font-size: 1.5rem;">{{ format_rupiah($oversla_nominal ?? 0) }}</div>
                            <div class="stat-label">Nominal Tertahan</div>
                        </div>
                    </div>
                    <div class="stat-card modern" style="box-shadow: none; border: 1px solid #fee2e2; background: white; min-height: 90px; padding: 1rem;">
                        <div class="stat-left">
                            <div class="stat-value" style="color: #dc2626; font-size: 1.5rem;">{{ round(($oversla_avg_days ?? 0), 1) }} Hari</div>
                            <div class="stat-label">Rata-rata Keterlambatan</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- ===== DETAILED ANALYSIS GRID ===== -->
            <div class="finance-grid-3-2" data-focus="risk">
                <!-- Pending by Category -->
                <section class="modern-section dashboard-card compact-insight-card" style="animation-delay: 0.6s;">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title" style="margin: 0;">Pending Berdasarkan Kategori</h2>
                            <p class="section-subtitle" style="margin: 0.25rem 0 0 0;">Analisis kategori perlu perhatian</p>
                        </div>
                    </div>
                    <div class="data-table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 40%;">Kategori</th>
                                    <th style="width: 20%; text-align: center;">Jumlah</th>
                                    <th style="width: 20%;">Total Nominal</th>
                                    <th style="width: 20%;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pendingByCategory ?? [] as $item)
                                <tr class="pending-row" data-category="{{ strtolower($item?->nama_kategori ?? '') }}">
                                    <td style="font-weight: 600; color: #1e293b;">
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b;"></div>
                                            <span>{{ $item?->nama_kategori }}</span>
                                        </div>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge-status-menunggu_verifikasi">{{ $item?->count ?? 0 }}</span>
                                    </td>
                                    <td style="font-weight: 700; color: #475569;">{{ format_rupiah($item?->total_nominal ?? 0) }}</td>
                                    <td>
                                        <span style="font-size: 0.75rem; font-weight: 600; color: #f59e0b;">
                                            Perlu Review
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr class="pending-empty-row">
                                    <td colspan="4" style="text-align: center; padding: 1.25rem; color: #94a3b8;">
                                        Tidak ada pengajuan pending
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Rejection Rate & Quality Indicator -->
                <section class="modern-section dashboard-card compact-insight-card" style="animation-delay: 0.7s;">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title" style="margin: 0;">Indikator Kualitas</h2>
                            <p class="section-subtitle" style="margin: 0.25rem 0 0 0;">Rejection Rate per Kategori</p>
                        </div>
                    </div>
                    <div class="rejection-list">
                        @foreach($rejectionRateByCategory ?? [] as $rate)
                        <div class="rejection-item" data-category="{{ strtolower($rate?->nama_kategori ?? '') }}">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; align-items: center;">
                                <span style="font-size: 0.8rem; font-weight: 700; color: #64748b;">{{ $rate?->nama_kategori ?? '-' }}</span>
                                <span style="font-size: 0.8rem; font-weight: 800; color: {{ ($rate?->rejection_rate ?? 0) > 20 ? '#ef4444' : '#64748b' }};">
                                    {{ round($rate?->rejection_rate ?? 0, 1) }}%
                                </span>
                            </div>
                            <div style="margin-bottom: 0.35rem;">
                                <div class="progress-track" style="height: 8px;">
                                    <div class="progress-fill" style="width: {{ $rate?->rejection_rate ?? 0 }}%; background: {{ ($rate?->rejection_rate ?? 0) > 20 ? '#ef4444' : '#94a3b8' }};"></div>
                                </div>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.7rem; color: #94a3b8;">
                                <span>{{ $rate?->approved_count ?? 0 }} disetujui</span>
                                <span>{{ $rate?->rejected_count ?? 0 }} ditolak</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </section>
            </div>


            <!-- ===== MAIN GRID ===== -->
            <div class="finance-grid-2-1 align-start compact-workspace ops-split-grid" data-focus="ops">
                <!-- Recent Activity Table -->
                <section class="modern-section dashboard-card activity-compact-card" style="animation-delay: 0.8s;">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title" style="margin: 0;">Aktivitas Terkini</h2>
                            <p class="section-subtitle" style="margin: 0.25rem 0 0 0;">Transaksi terbaru yang perlu perhatian</p>
                        </div>
                        <a href="{{ route('finance.approval.index') }}" class="btn-text">Lihat Semua &rarr;</a>
                    </div>
                    <div class="data-table-wrapper">
                        @if($recentRequests->isEmpty())
                        <div class="empty-state-compact">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M12 6v6l4 2"></path>
                            </svg>
                            <p style="font-weight: 600; font-size: 0.875rem;">Belum ada aktivitas baru</p>
                        </div>
                        @else
                        <table class="data-table">
                            <thead style="position: sticky; top: 0; z-index: 10;">
                                <tr>
                                    <th style="width: 25%;">Pengaju</th>
                                    <th style="width: 18%;">Nominal</th>
                                    <th style="width: 18%;">Kategori</th>
                                    <th style="width: 18%;">Status</th>
                                    <th style="width: 21%; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentRequests as $pengajuan)
                                @php
                                    $recentDate = $pengajuan->tanggal ?? $pengajuan->created_at ?? null;
                                @endphp
                                <tr class="recent-row" data-date="{{ $recentDate ? \Illuminate\Support\Carbon::parse($recentDate)->format('Y-m-d') : '' }}">
                                    <td>
                                        <div style="font-weight: 600; color: #1e293b;">{{ $pengajuan->user->name ?? '-' }}</div>
                                        <div style="font-size: 0.75rem; color: #64748b;">{{ $pengajuan->departemen->nama_departemen ?? '-' }}</div>
                                    </td>
                                    <td style="font-weight: 700; color: #1e293b;">{{ format_rupiah($pengajuan->nominal ?? 0) }}</td>
                                    <td>
                                        <span style="font-size: 0.75rem; font-weight: 600; color: #425d87; background: #e8ecf8; padding: 0.25rem 0.5rem; border-radius: 6px;">
                                            {{ $pengajuan->kategori->nama_kategori ?? '-' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-status-{{ $pengajuan->status->value ?? '' }}">
                                            {{ $pengajuan->status->label() ?? '-' }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <a href="{{ route('finance.approval.show', $pengajuan) }}" style="display: inline-flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: #f8fafc; border: 1.5px solid #e5eaf2; border-radius: 8px; color: #64748b; text-decoration: none; transition: all 0.2s;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                                                <path d="M9 18l6-6-6-6"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div id="recentFilterEmptyState" class="empty-state-compact" style="display: none; padding: 1rem;">
                            Tidak ada aktivitas pada rentang tanggal ini.
                        </div>
                        @endif
                    </div>
                </section>

                <section class="widget-card dashboard-card smart-rail-widget ops-top-requesters" style="animation-delay: 0.84s;">
                    <div class="card-header">
                        <h3 class="card-title">Top Karyawan</h3>
                        <p class="section-subtitle">Bulan ini (Nominal Tertinggi)</p>
                    </div>
                    <div class="top-requesters-list">
                        @forelse($topRequesters ?? [] as $requester)
                        <div class="requester-item">
                            <div class="requester-avatar">
                                <span>{{ substr($requester?->name ?? '', 0, 1) }}</span>
                            </div>
                            <div class="requester-info">
                                <span class="requester-name">{{ $requester?->name ?? '-' }}</span>
                                <span class="requester-requests">{{ $requester?->total_requests ?? 0 }} Pengajuan</span>
                            </div>
                            <div class="requester-amount">{{ format_rupiah($requester?->total_nominal ?? 0) }}</div>
                        </div>
                        @empty
                        <div class="empty-state-compact" style="padding: 1rem;">
                            Belum ada data
                        </div>
                        @endforelse
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>

<script>
let activityChartInstance = null;
let statusChartInstance = null;

const dashboardControlState = {
    metric: 'volume',
    dateStart: '',
    dateEnd: '',
    focus: 'ops',
};

const financeCenterTextPlugin = {
    id: 'financeCenterTextPlugin',
    afterDraw(chart, args, opts) {
        if (!opts || !opts.text) return;
        const { ctx, chartArea } = chart;
        if (!chartArea) return;

        const x = (chartArea.left + chartArea.right) / 2;
        const y = (chartArea.top + chartArea.bottom) / 2;

        ctx.save();
        ctx.textAlign = 'center';
        ctx.fillStyle = opts.color || '#1f3357';
        ctx.font = '700 20px Poppins';
        ctx.fillText(opts.text, x, y - 4);
        ctx.fillStyle = opts.subColor || '#64748b';
        ctx.font = '600 11px Poppins';
        ctx.fillText(opts.subtext || '', x, y + 14);
        ctx.restore();
    }
};

if (window.Chart && !window.__financeCenterTextPluginRegistered) {
    Chart.register(financeCenterTextPlugin);
    window.__financeCenterTextPluginRegistered = true;
}

function syncChipState(selector, attr, value) {
    document.querySelectorAll(selector).forEach(btn => {
        const active = btn.getAttribute(attr) === value;
        btn.classList.toggle('active', active);
    });
}

function toInputDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function parseDateValue(value, endOfDay = false) {
    if (!value) return null;
    const parsed = new Date(`${value}T00:00:00`);
    if (Number.isNaN(parsed.getTime())) return null;
    if (endOfDay) parsed.setHours(23, 59, 59, 999);
    return parsed;
}

function getActivityBaseSeries() {
    const daily = Array.isArray(window.financeChartData?.activity) ? window.financeChartData.activity : [];
    const monthly = Array.isArray(window.financeChartData?.monthly) ? window.financeChartData.monthly : [];
    const source = monthly.length > daily.length ? monthly : daily;

    return source
        .filter(item => item && item.tanggal)
        .sort((a, b) => new Date(a.tanggal) - new Date(b.tanggal));
}

function getFilteredActivitySeries() {
    const baseSeries = getActivityBaseSeries();
    if (!baseSeries.length) return [];

    const start = parseDateValue(dashboardControlState.dateStart, false);
    const end = parseDateValue(dashboardControlState.dateEnd, true);

    return baseSeries.filter(item => {
        const itemDate = new Date(item.tanggal);
        if (Number.isNaN(itemDate.getTime())) return false;
        if (start && itemDate < start) return false;
        if (end && itemDate > end) return false;
        return true;
    });
}

function updateActivityPeriodLabel(series = []) {
    const labelNode = document.getElementById('activityPeriodLabel');
    if (!labelNode) return;

    const start = parseDateValue(dashboardControlState.dateStart, false);
    const end = parseDateValue(dashboardControlState.dateEnd, false);

    if (start && end) {
        const startText = start.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        const endText = end.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        labelNode.textContent = series.length
            ? `Periode ${startText} - ${endText}`
            : `Periode ${startText} - ${endText} (tidak ada data)`;
        return;
    }

    labelNode.textContent = `Menampilkan ${series.length} titik aktivitas terbaru`;
}

function renderActivityChart() {
    const activityCtx = document.getElementById('activityChart');
    if (!activityCtx) return;

    const activityData = getFilteredActivitySeries();
    if (!activityData.length) {
        if (activityChartInstance) {
            activityChartInstance.destroy();
            activityChartInstance = null;
        }
        const ctx = activityCtx.getContext('2d');
        ctx.clearRect(0, 0, activityCtx.width, activityCtx.height);
        ctx.font = '600 12px Poppins';
        ctx.fillStyle = '#94a3b8';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('Tidak ada data pada rentang tanggal ini', activityCtx.width / 2, activityCtx.height / 2);
        updateActivityPeriodLabel(activityData);
        return;
    }

    if (activityChartInstance) activityChartInstance.destroy();

    const labels = activityData.map(d => {
        const date = new Date(d.tanggal);
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    });
    const volumes = activityData.map(d => d.count ?? 0);
    const nominals = activityData.map(d => d.total ?? 0);

    const ctx = activityCtx.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 320);
    gradient.addColorStop(0, 'rgba(66, 93, 135, 0.28)');
    gradient.addColorStop(1, 'rgba(66, 93, 135, 0.02)');

    activityChartInstance = new Chart(activityCtx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Volume',
                data: volumes,
                borderColor: '#425d87',
                backgroundColor: gradient,
                fill: true,
                tension: 0.42,
                borderWidth: 3.2,
                pointRadius: 0,
                pointHitRadius: 16,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#ffffff',
                pointHoverBorderColor: '#425d87',
                pointHoverBorderWidth: 3,
                yAxisID: 'y',
                hidden: dashboardControlState.metric !== 'volume',
            }, {
                label: 'Nominal',
                data: nominals,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.08)',
                fill: true,
                tension: 0.35,
                borderWidth: 3,
                pointRadius: 0,
                pointHitRadius: 16,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#ffffff',
                pointHoverBorderColor: '#10b981',
                pointHoverBorderWidth: 3,
                yAxisID: 'y1',
                hidden: dashboardControlState.metric !== 'nominal',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12,
                    cornerRadius: 10,
                    titleFont: { size: 12, weight: '700', family: 'Poppins' },
                    bodyFont: { size: 12, family: 'Poppins' },
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            if (context.dataset.label === 'Volume') {
                                return ` ${context.dataset.label}: ${context.raw} pengajuan`;
                            }
                            return ` ${context.dataset.label}: Rp ${new Intl.NumberFormat('id-ID').format(context.raw)}`;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: dashboardControlState.metric === 'volume',
                    beginAtZero: true,
                    grid: { color: '#f1f5f9', drawBorder: false },
                    ticks: {
                        font: { size: 10, weight: '600', family: 'Poppins' },
                        color: '#94a3b8',
                    },
                },
                y1: {
                    type: 'linear',
                    display: dashboardControlState.metric === 'nominal',
                    beginAtZero: true,
                    grid: { display: false },
                    ticks: {
                        callback: (value) => `Rp ${new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value)}`,
                        font: { size: 10, weight: '600', family: 'Poppins' },
                        color: '#94a3b8',
                    },
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        maxTicksLimit: labels.length > 14 ? 10 : 7,
                        font: { size: 10, weight: '600', family: 'Poppins' },
                        color: '#94a3b8',
                    },
                },
            },
        },
    });

    updateActivityPeriodLabel(activityData);
}

function renderDistributionChart() {
    const statusCtx = document.getElementById('statusChart');
    if (!statusCtx) return;

    const deptData = window.financeChartData?.distribution ?? [];
    if (statusChartInstance) statusChartInstance.destroy();

    const labels = deptData.map(d => d.nama_departemen || d.nama);
    const values = deptData.map(d => d.count || d.total || 0);
    const total = values.reduce((a, b) => a + b, 0);
    const colors = ['#425d87', '#2f6dff', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#0ea5e9'];

    if (total === 0) {
        const ctx = statusCtx.getContext('2d');
        ctx.clearRect(0, 0, statusCtx.width, statusCtx.height);
        ctx.font = '600 12px Poppins';
        ctx.fillStyle = '#94a3b8';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText('Belum ada data', statusCtx.width / 2, statusCtx.height / 2);
        return;
    }

    statusChartInstance = new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: colors.slice(0, labels.length),
                borderColor: 'rgba(255,255,255,0.86)',
                borderWidth: 2.5,
                spacing: 4,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { display: false },
                financeCenterTextPlugin: {
                    text: String(total),
                    subtext: 'Dept aktif',
                    color: '#425d87',
                    subColor: '#64748b',
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    padding: 12,
                    cornerRadius: 10,
                    titleFont: { size: 12, weight: '700', family: 'Poppins' },
                    bodyFont: { size: 12, family: 'Poppins' },
                    callbacks: {
                        label: function(context) {
                            const percentage = ((context.raw / total) * 100).toFixed(1);
                            return ` ${context.label}: ${context.raw} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

function toggleActivityChart(type) {
    dashboardControlState.metric = type === 'nominal' ? 'nominal' : 'volume';
    syncChipState('.chart-toggle-btn', 'id', dashboardControlState.metric === 'volume' ? 'btnActivityVolume' : 'btnActivityNominal');
    renderActivityChart();
}

function applyFocusMode(mode) {
    dashboardControlState.focus = mode;

    const blocks = document.querySelectorAll('[data-focus]');
    blocks.forEach(block => {
        const role = block.getAttribute('data-focus');
        const visible = mode === 'all' || role === mode || role === 'essential';
        block.classList.toggle('is-focus-hidden', !visible);
    });
}

function applyRecentActivityDateFilter() {
    const start = parseDateValue(dashboardControlState.dateStart, false);
    const end = parseDateValue(dashboardControlState.dateEnd, true);
    const rows = document.querySelectorAll('.recent-row[data-date]');
    let visibleCount = 0;

    rows.forEach(row => {
        const rawDate = row.getAttribute('data-date') || '';
        const rowDate = parseDateValue(rawDate, false);

        if (!rowDate) {
            row.style.display = '';
            visibleCount += 1;
            return;
        }

        let visible = true;
        if (start && rowDate < start) visible = false;
        if (end && rowDate > end) visible = false;

        row.style.display = visible ? '' : 'none';
        if (visible) visibleCount += 1;
    });

    const emptyState = document.getElementById('recentFilterEmptyState');
    if (emptyState) {
        emptyState.style.display = visibleCount === 0 ? '' : 'none';
    }
}

function setDefaultDateRange() {
    const series = getActivityBaseSeries();
    const startInput = document.getElementById('filterDateStart');
    const endInput = document.getElementById('filterDateEnd');
    if (!startInput || !endInput) return;

    if (!series.length) {
        startInput.value = '';
        endInput.value = '';
        dashboardControlState.dateStart = '';
        dashboardControlState.dateEnd = '';
        return;
    }

    const endDate = new Date(series[series.length - 1].tanggal);
    const startDate = new Date(endDate);
    startDate.setDate(endDate.getDate() - 6);

    const startValue = toInputDate(startDate);
    const endValue = toInputDate(endDate);
    startInput.value = startValue;
    endInput.value = endValue;
    dashboardControlState.dateStart = startValue;
    dashboardControlState.dateEnd = endValue;
}

function applyDateFilterFromInputs() {
    const startInput = document.getElementById('filterDateStart');
    const endInput = document.getElementById('filterDateEnd');
    if (!startInput || !endInput) return;

    let startValue = startInput.value || '';
    let endValue = endInput.value || '';

    if (startValue && endValue && startValue > endValue) {
        const temp = startValue;
        startValue = endValue;
        endValue = temp;
        startInput.value = startValue;
        endInput.value = endValue;
    }

    dashboardControlState.dateStart = startValue;
    dashboardControlState.dateEnd = endValue;
    renderActivityChart();
    applyRecentActivityDateFilter();
}

function initDashboardFilters() {
    const applyBtn = document.getElementById('applyDateFilterBtn');
    const resetBtn = document.getElementById('resetDateFilterBtn');
    const startInput = document.getElementById('filterDateStart');
    const endInput = document.getElementById('filterDateEnd');

    if (applyBtn) {
        applyBtn.onclick = applyDateFilterFromInputs;
    }
    if (startInput) {
        startInput.onchange = applyDateFilterFromInputs;
    }
    if (endInput) {
        endInput.onchange = applyDateFilterFromInputs;
    }
    if (resetBtn) {
        resetBtn.onclick = () => {
            setDefaultDateRange();
            applyDateFilterFromInputs();
        };
    }

    setDefaultDateRange();
    applyDateFilterFromInputs();
}

function initFinanceCharts() {
    if (typeof Chart === 'undefined') {
        if (typeof window.ensureChartJsLoaded === 'function') {
            window.ensureChartJsLoaded()
                .then(() => initFinanceCharts())
                .catch((error) => console.error('[Finance Dashboard] Chart.js failed to load:', error));
        } else {
            console.error('[Finance Dashboard] Chart.js is not available.');
        }
        return;
    }

    renderDistributionChart();
    initDashboardFilters();
    applyFocusMode(dashboardControlState.focus);
}

window.toggleActivityChart = toggleActivityChart;

if (document.readyState === 'complete') {
    initFinanceCharts();
} else {
    document.addEventListener('DOMContentLoaded', initFinanceCharts);
}

document.addEventListener('livewire:navigated', initFinanceCharts);
</script>
@endsection
