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
        transform: translateX(5px);
        background: #f1f5f9;
    }

    .dashboard-card {
        animation: slideIn 0.5s ease-out;
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

        // Smart Real-time Refresh
        window.addEventListener('refresh-approval-table', () => {
            location.reload();
        });

        window.addEventListener('refresh-pengajuan-table', () => {
            location.reload();
        });
    })();
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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
            <!-- ===== WELCOME & BUDGET CARDS ===== -->
            <div style="display: grid; grid-template-columns: 1.6fr 1fr; gap: 1rem; margin-bottom: 1rem;">
                <!-- Welcome Card -->
                <div class="welcome-card dashboard-card" style="animation-delay: 0.1s;">
                    <div class="welcome-content">
                        <div class="welcome-avatar">
                            <span class="avatar-initial">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        </div>
                        <div class="welcome-text">
                            <h2 class="welcome-title">Halo, {{ explode(' ', Auth::user()->name)[0] }}! 👋</h2>
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

            <!-- ===== CHARTS GRID ===== -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem; margin-bottom: 0;">
                <!-- Activity Trend Chart -->
                <div class="chart-card dashboard-card" style="animation-delay: 0.3s;">
                    <div class="card-header">
                        <div class="header-title">
                            <h3 class="card-title">Aktivitas Pengajuan</h3>
                            <span class="card-subtitle">Tren 7 hari terakhir</span>
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
                    <div class="chart-container" style="min-height: 280px;">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>

                <!-- Department Distribution Pie Chart -->
                <div class="chart-card dashboard-card" style="animation-delay: 0.4s;">
                    <div class="card-header">
                        <div class="header-title">
                            <h3 class="card-title">Distribusi Departemen</h3>
                            <span class="card-subtitle">Berdasarkan volume</span>
                        </div>
                    </div>
                    <div class="chart-container" style="min-height: 280px; display: flex; align-items: center; justify-content: center;">
                        <div style="width: 200px; height: 200px; position: relative;">
                            <canvas id="statusChart"></canvas>
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; pointer-events: none;">
                                <div style="font-size: 1.5rem; font-weight: 800; color: #425d87;">{{ count($departementDistribution ?? []) }}</div>
                                <div style="font-size: 0.7rem; font-weight: 600; color: #94a3b8;">Dept</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== MANAGEMENT STATS ===== -->
            <div class="stats-grid dashboard-card" style="animation-delay: 0.5s;">
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
            <div class="info-grid">
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
            <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 1rem;">
                <!-- Pending by Category -->
                <section class="modern-section dashboard-card" style="animation-delay: 0.6s;">
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
                                <tr>
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
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 2rem; color: #94a3b8;">
                                        Tidak ada pengajuan pending
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- Rejection Rate & Quality Indicator -->
                <section class="modern-section dashboard-card" style="animation-delay: 0.7s;">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title" style="margin: 0;">Indikator Kualitas</h2>
                            <p class="section-subtitle" style="margin: 0.25rem 0 0 0;">Rejection Rate per Kategori</p>
                        </div>
                    </div>
                    <div class="rejection-list">
                        @foreach($rejectionRateByCategory ?? [] as $rate)
                        <div style="margin-bottom: 1.25rem;">
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
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                <!-- Recent Activity Table -->
                <section class="modern-section dashboard-card" style="animation-delay: 0.8s;">
                    <div class="section-header">
                        <div>
                            <h2 class="section-title" style="margin: 0;">Aktivitas Terkini</h2>
                            <p class="section-subtitle" style="margin: 0.25rem 0 0 0;">Transaksi terbaru yang perlu perhatian</p>
                        </div>
                        <a href="{{ route('finance.approval.index') }}" class="btn-text">Lihat Semua →</a>
                    </div>
                    <div class="data-table-wrapper" style="max-height: 400px; overflow-y: auto;">
                        @if($recentRequests->isEmpty())
                        <div style="padding: 3rem; text-align: center; color: #94a3b8;">
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
                                <tr>
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
                        @endif
                    </div>
                </section>

                <!-- Sidebar Column -->
                <div class="sidebar-column" style="display: flex; flex-direction: column; gap: 1rem;">
                    <!-- Top Requesters -->
                    <div class="widget-card dashboard-card" style="animation-delay: 0.9s;">
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
                            <div style="padding: 1.5rem; text-align: center; color: #94a3b8; font-size: 0.85rem;">
                                Belum ada data
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Quick Actions & Master Data -->
                    <div class="widget-card dashboard-card" style="animation-delay: 1.0s;">
                        <div class="card-header">
                            <h3 class="card-title">Aksi Cepat & Master Data</h3>
                            <p class="section-subtitle">Akses cepat ke fitur utama</p>
                        </div>
                        <div class="quick-actions-list">
                            <a href="{{ route('finance.approval.index') }}" class="quick-action-item">
                                <div class="action-icon warning">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                </div>
                                <div class="action-info">
                                    <span class="action-title">Persetujuan</span>
                                    <span class="action-desc">{{ $total_pending_count ?? 0 }} pending verifikasi</span>
                                </div>
                            </a>
                            <a href="{{ route('finance.disbursement.index') }}" class="quick-action-item">
                                <div class="action-icon success">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                </div>
                                <div class="action-info">
                                    <span class="action-title">Pencairan Dana</span>
                                    <span class="action-desc">Proses transfer ke pegawai</span>
                                </div>
                            </a>
                            <a href="{{ route('finance.report.index') }}" class="quick-action-item">
                                <div class="action-icon info">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                </div>
                                <div class="action-info">
                                    <span class="action-title">Laporan</span>
                                    <span class="action-desc">Buku besar, jurnal, arus kas</span>
                                </div>
                            </a>
                            <div style="margin: 0.5rem 0; border-top: 1px solid #f1f5f9;"></div>
                            <a href="{{ route('finance.masterdata.users.index') }}" class="quick-action-item">
                                <div class="action-icon" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe); color: #4f46e5;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                                <div class="action-info">
                                    <span class="action-title">Kelola User</span>
                                    <span class="action-desc">Pegawai & Atasan</span>
                                </div>
                            </a>
                            <a href="{{ route('finance.masterdata.departemen.index') }}" class="quick-action-item">
                                <div class="action-icon" style="background: linear-gradient(135deg, #fae8ff, #f5d0fe); color: #a21caf;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2L2 7l10 5 10-5-10-5z"></path>
                                    </svg>
                                </div>
                                <div class="action-info">
                                    <span class="action-title">Departemen</span>
                                    <span class="action-desc">Budget & Struktur</span>
                                </div>
                            </a>
                        </div>
                    </div>

                    <!-- Monthly Overview Mini Chart -->
                    <div class="widget-card dashboard-card" style="animation-delay: 1.1s;">
                        <div class="card-header">
                            <h3 class="card-title">Overview Bulanan</h3>
                            <p class="section-subtitle">Status & distribusi bulan ini</p>
                        </div>
                        <div style="position: relative; height: 180px;">
                            <canvas id="monthlyOverviewChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ===== CHART.JS CONFIGURATION =====
let activityChartInstance = null;
let statusChartInstance = null;
let monthlyOverviewChartInstance = null;

function initFinanceCharts() {
    // Activity Trend Chart
    const activityCtx = document.getElementById('activityChart');
    if (activityCtx && window.financeChartData?.activity) {
        if (activityChartInstance) activityChartInstance.destroy();
        
        const activityData = window.financeChartData.activity;
        const labels = activityData.map(d => {
            const date = new Date(d.tanggal);
            return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        });
        const volumes = activityData.map(d => d.count ?? 0);
        const nominals = activityData.map(d => d.total ?? 0);

        const gradient = activityCtx.getContext('2d').createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(66, 93, 135, 0.2)');
        gradient.addColorStop(1, 'rgba(66, 93, 135, 0)');

        activityChartInstance = new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: labels.length ? labels : ['No Data'],
                datasets: [{
                    label: 'Volume',
                    data: volumes.length ? volumes : [0],
                    borderColor: '#425d87',
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 0,
                    pointHitRadius: 20,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#ffffff',
                    pointHoverBorderColor: '#425d87',
                    pointHoverBorderWidth: 3,
                    yAxisID: 'y'
                }, {
                    label: 'Nominal',
                    data: nominals.length ? nominals : [0],
                    borderColor: '#667eea',
                    backgroundColor: 'transparent',
                    tension: 0.4,
                    borderWidth: 3,
                    pointRadius: 0,
                    pointHitRadius: 20,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#ffffff',
                    pointHoverBorderColor: '#667eea',
                    pointHoverBorderWidth: 3,
                    yAxisID: 'y1',
                    hidden: true
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
                        backgroundColor: '#1d2534',
                        padding: 12,
                        cornerRadius: 10,
                        titleFont: { size: 13, weight: '700', family: 'Poppins' },
                        bodyFont: { size: 13, family: 'Poppins' },
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
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        grid: { color: '#f8fafc', drawBorder: false },
                        ticks: {
                            font: { size: 10, weight: '600', family: 'Poppins' },
                            color: '#94a3b8'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: false,
                        position: 'right'
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 10, weight: '600', family: 'Poppins' },
                            color: '#94a3b8'
                        }
                    }
                }
            }
        });
    }

    // Department Distribution Pie Chart
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx && window.financeChartData?.distribution) {
        if (statusChartInstance) statusChartInstance.destroy();
        
        const deptData = window.financeChartData.distribution;
        const labels = deptData.map(d => d.nama_departemen || d.nama);
        const values = deptData.map(d => d.count || d.total);
        const colors = ['#425d87', '#667eea', '#7693ba', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

        const total = values.reduce((a, b) => a + b, 0);

        if (total === 0) {
            const ctx = statusCtx.getContext('2d');
            ctx.clearRect(0, 0, statusCtx.width, statusCtx.height);
            ctx.font = '600 12px Poppins';
            ctx.fillStyle = '#94a3b8';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText('Belum ada data', statusCtx.width / 2, statusCtx.height / 2);
        } else {
            statusChartInstance = new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: values,
                        backgroundColor: colors.slice(0, labels.length),
                        borderColor: '#ffffff',
                        borderWidth: 4,
                        borderRadius: 4,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1d2534',
                            padding: 12,
                            cornerRadius: 10,
                            titleFont: { size: 13, weight: '700', family: 'Poppins' },
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
    }

    // Monthly Overview Mini Chart
    const monthlyCtx = document.getElementById('monthlyOverviewChart');
    if (monthlyCtx && window.financeChartData?.status) {
        if (monthlyOverviewChartInstance) monthlyOverviewChartInstance.destroy();
        
        const statusData = window.financeChartData.status || {};
        const data = [
            statusData.menunggu_atasan || 0,
            statusData.menunggu_finance || 0,
            statusData.dicairkan || 0,
            (statusData.ditolak_atasan || 0) + (statusData.ditolak_finance || 0) + (statusData.ditolak_ai || 0)
        ];

        monthlyOverviewChartInstance = new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: ['Menunggu', 'Verifikasi', 'Disetujui', 'Ditolak'],
                datasets: [{
                    data: data,
                    backgroundColor: ['#425d87', '#7693ba', '#10b981', '#ef4444'],
                    borderRadius: 6,
                    barThickness: 30
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1d2534',
                        padding: 10,
                        cornerRadius: 8,
                        titleFont: { size: 12, weight: '700', family: 'Poppins' },
                        bodyFont: { size: 12, family: 'Poppins' },
                        callbacks: {
                            label: function(context) {
                                return ` ${context.raw} pengajuan`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f8fafc', drawBorder: false },
                        ticks: {
                            font: { size: 10, weight: '600', family: 'Poppins' },
                            color: '#94a3b8'
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 10, weight: '600', family: 'Poppins' },
                            color: '#94a3b8'
                        }
                    }
                }
            }
        });
    }
}

// Toggle Activity Chart (Volume/Nominal)
function toggleActivityChart(type) {
    const btnVolume = document.getElementById('btnActivityVolume');
    const btnNominal = document.getElementById('btnActivityNominal');
    
    if (type === 'volume') {
        btnVolume.classList.add('active');
        btnNominal.classList.remove('active');
        if (activityChartInstance) {
            activityChartInstance.data.datasets[0].hidden = false;
            activityChartInstance.data.datasets[1].hidden = true;
            activityChartInstance.update();
        }
    } else {
        btnVolume.classList.remove('active');
        btnNominal.classList.add('active');
        if (activityChartInstance) {
            activityChartInstance.data.datasets[0].hidden = true;
            activityChartInstance.data.datasets[1].hidden = false;
            activityChartInstance.update();
        }
    }
}

// Initialize charts when DOM is ready
if (document.readyState === 'complete') {
    initFinanceCharts();
} else {
    document.addEventListener('DOMContentLoaded', initFinanceCharts);
}

document.addEventListener('livewire:navigated', initFinanceCharts);
</script>
@endsection
