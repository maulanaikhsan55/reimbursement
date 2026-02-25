@extends('layouts.app')

@section('title', 'Dashboard Atasan')

@push('styles')
<style>
    .dashboard-wrapper {
        --atasan-accent: #425d87;
        --atasan-ink: #1e293b;
        --atasan-muted: #64748b;
        --atasan-stroke: rgba(66, 93, 135, 0.16);
        --atasan-surface: #ffffff;
        --atasan-surface-alt: #f8fbff;
        --kpi-card-radius: 1.35rem;
        background:
            radial-gradient(860px 360px at 9% -16%, rgba(66, 93, 135, 0.13), transparent 72%),
            radial-gradient(840px 300px at 90% -18%, rgba(14, 116, 144, 0.1), transparent 70%),
            linear-gradient(150deg, #f4f7fc 0%, #edf2f9 100%);
    }

    .dashboard-content {
        gap: 0.82rem;
    }

    .atasan-premium-card {
        border: 1px solid #e2e8f0 !important;
        background: linear-gradient(156deg, var(--atasan-surface) 0%, var(--atasan-surface-alt) 100%) !important;
        box-shadow: 0 6px 18px rgba(22, 37, 62, 0.07) !important;
        border-radius: var(--kpi-card-radius) !important;
    }

    .atasan-team-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .atasan-self-actions-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        gap: 1rem;
        align-items: start;
    }

    .self-requests-card {
        padding: 1rem;
    }

    .dashboard-kpi-grid {
        margin-top: 0.85rem;
        gap: 0.8rem;
    }

    .dashboard-kpi-grid .stat-card.modern {
        min-height: 96px;
        padding: 0.82rem 0.95rem;
    }

    .dashboard-kpi-grid .stat-label {
        font-size: 0.82rem;
        font-weight: 700;
        color: #64748b;
        line-height: 1.3;
        margin-bottom: 0.2rem;
    }

    .dashboard-kpi-grid .stat-value {
        font-size: clamp(1.12rem, 2vw, 1.55rem);
        font-weight: 800;
        color: #1f2f47;
        line-height: 1.2;
        margin-bottom: 0.12rem;
    }

    .chart-switch {
        display: flex;
        background: #eef2ff;
        padding: 0.2rem;
        border-radius: 10px;
        border: 1px solid #dbeafe;
    }

    .chart-switch-btn {
        border: none;
        background: transparent;
        padding: 0.3rem 0.6rem;
        border-radius: 8px;
        font-size: 0.65rem;
        font-weight: 700;
        color: #64748b;
        cursor: pointer;
        transition: all 0.2s;
    }

    .chart-switch-btn.is-active {
        background: white;
        color: #425d87;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .stat-sub-label {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 4px;
        opacity: 0.8;
    }
    .stat-value {
        line-height: 1.2;
    }
    
    @keyframes pulse {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }
    
    @keyframes pulse-red {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
        70% { transform: scale(1.02); box-shadow: 0 0 0 10px rgba(239, 68, 68, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
    }
    
    .sla-alert {
        animation: pulse-red 2s infinite;
        border-color: #fca5a5 !important;
        background: #fef2f2 !important;
    }

    .sla-alert-cta {
        margin-left: auto;
        white-space: nowrap;
    }

    @media (max-width: 1200px) {
        .atasan-team-grid,
        .atasan-self-actions-grid,
        .dashboard-kpi-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-kpi-grid .stat-card.modern {
            height: auto;
            min-height: 96px;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    var statusChartInstance = null;
    var trendChartInstance = null;
    var categoryChartInstance = null;

    function initAtasanDashboard() {
        if (typeof Chart === 'undefined') {
            if (typeof window.ensureChartJsLoaded === 'function') {
                window.ensureChartJsLoaded()
                    .then(() => initAtasanDashboard())
                    .catch((error) => console.error('[Atasan Dashboard] Chart.js failed to load:', error));
            } else {
                console.error('[Atasan Dashboard] Chart.js is not available.');
            }
            return;
        }

        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            if (statusChartInstance) statusChartInstance.destroy();
            
            const statusData = {
                'menunggu_atasan': {{ $statusData['menunggu_atasan'] ?? 0 }},
                'menunggu_finance': {{ $statusData['menunggu_finance'] ?? 0 }},
                'dicairkan': {{ $statusData['dicairkan'] ?? 0 }},
                'ditolak_atasan': {{ $statusData['ditolak_atasan'] ?? 0 }},
                'ditolak_finance': {{ $statusData['ditolak_finance'] ?? 0 }}
            };

            const data = Object.values(statusData);
            const total = data.reduce((a, b) => a + b, 0);

            if (total === 0) {
                const ctx = statusCtx.getContext('2d');
                ctx.clearRect(0, 0, statusCtx.width, statusCtx.height);
                ctx.font = '14px Poppins';
                ctx.fillStyle = '#94a3b8';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText('Belum ada data', statusCtx.offsetWidth / 2, statusCtx.offsetHeight / 2);
            } else {
                statusChartInstance = new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Atasan', 'Finance', 'Cair', 'Tolak'],
                        datasets: [{
                            data: [
                                {{ $statusData['menunggu_atasan'] ?? 0 }},
                                {{ $statusData['menunggu_finance'] ?? 0 }},
                                {{ $statusData['dicairkan'] ?? 0 }},
                                {{ ($statusData['ditolak_atasan'] ?? 0) + ($statusData['ditolak_finance'] ?? 0) }}
                            ],
                            backgroundColor: ['#425d87', '#7693ba', '#10b981', '#ef4444'],
                            borderColor: 'rgba(255,255,255,0.86)',
                            borderWidth: 2.5,
                            borderRadius: 4,
                            spacing: 3,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1d2534',
                                padding: 10,
                                cornerRadius: 8,
                                titleFont: { size: 12, weight: '700', family: 'Poppins' },
                                bodyFont: { size: 12, family: 'Poppins' }
                            }
                        }
                    }
                });
            }
        }

        const categoryCtx = document.getElementById('categoryChart');
        if (categoryCtx) {
            if (categoryChartInstance) categoryChartInstance.destroy();

            const categoryData = {!! json_encode($categoryDist ?? []) !!};
            const labels = categoryData.map(d => d.nama_kategori);
            const values = categoryData.map(d => d.total);
            const colors = ['#425d87', '#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'];

            if (values.length === 0) {
                const ctx = categoryCtx.getContext('2d');
                ctx.font = '14px Poppins';
                ctx.fillStyle = '#94a3b8';
                ctx.textAlign = 'center';
                ctx.fillText('Belum ada data', categoryCtx.offsetWidth / 2, categoryCtx.offsetHeight / 2);
            } else {
                categoryChartInstance = new Chart(categoryCtx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: colors.slice(0, values.length),
                            borderColor: 'rgba(255,255,255,0.86)',
                            borderWidth: 2.5,
                            borderRadius: 4,
                            spacing: 3,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#1d2534',
                                padding: 10,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        return ' Total: Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }

        const trendCtx = document.getElementById('trendChart');
        if (trendCtx) {
            if (trendChartInstance) trendChartInstance.destroy();

            const trendData = {!! json_encode($monthlyTrend) !!};
            const labels = trendData.map(d => {
                const date = new Date(d.tanggal);
                return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
            });
            const values = trendData.map(d => d.total);

            const gradient = trendCtx.getContext('2d').createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(66, 93, 135, 0.15)');
            gradient.addColorStop(1, 'rgba(66, 93, 135, 0)');

            trendChartInstance = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: labels.length ? labels : ['No Data'],
                    datasets: [{
                        label: 'Nominal',
                        data: values.length ? values : [0],
                        borderColor: '#425d87',
                        borderWidth: 3.5,
                        backgroundColor: gradient,
                        fill: true,
                        tension: 0.45,
                        pointRadius: 0,
                        pointHitRadius: 20,
                        pointHoverRadius: 6,
                        pointHoverBackgroundColor: '#ffffff',
                        pointHoverBorderColor: '#425d87',
                        pointHoverBorderWidth: 3
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
                                    return 'Total: Rp ' + new Intl.NumberFormat('id-ID').format(context.raw);
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
                                color: '#94a3b8',
                                callback: value => 'Rp ' + (value >= 1000 ? (value / 1000) + 'k' : value),
                                maxTicksLimit: 5
                            }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                font: { size: 10, weight: '600', family: 'Poppins' },
                                color: '#94a3b8',
                                maxTicksLimit: 7
                            }
                        }
                    }
                }
            });
        }
    }

    if (document.readyState === 'complete') {
        initAtasanDashboard();
    } else {
        document.addEventListener('DOMContentLoaded', initAtasanDashboard);
    }
    document.addEventListener('livewire:navigated', initAtasanDashboard);

    const refreshAtasanDashboardSections = async () => {
        if (window.__atasanDashboardPartialRefreshBusy) return;
        window.__atasanDashboardPartialRefreshBusy = true;

        const selectors = [
            '.welcome-card',
            '.team-overview-card',
            '.recent-section',
            '.self-requests-card',
        ];
        const refreshUrl = "{{ route('atasan.dashboard.widgets') }}";

        try {
            const response = await fetch(refreshUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                credentials: 'same-origin',
            });
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }
            const payload = await response.json();
            const sections = payload.sections || {};

            selectors.forEach((selector) => {
                const currentEl = document.querySelector(selector);
                const nextHtml = sections[selector];
                if (currentEl && typeof nextHtml === 'string' && nextHtml.length > 0) {
                    currentEl.outerHTML = nextHtml;
                }
            });
        } catch (error) {
            console.error('[Atasan Dashboard] Partial refresh failed:', error);
        } finally {
            window.__atasanDashboardPartialRefreshBusy = false;
        }
    };

    window.removeEventListener('refresh-approval-table', window.__atasanDashboardPartialRefreshHandler);
    window.removeEventListener('refresh-pengajuan-table', window.__atasanDashboardPartialRefreshHandler);
    window.__atasanDashboardPartialRefreshHandler = refreshAtasanDashboardSections;
    window.addEventListener('refresh-approval-table', window.__atasanDashboardPartialRefreshHandler);
    window.addEventListener('refresh-pengajuan-table', window.__atasanDashboardPartialRefreshHandler);

    function toggleDistChart(type) {
        const statusContainer = document.getElementById('statusChartContainer');
        const categoryContainer = document.getElementById('categoryChartContainer');
        const btnStatus = document.getElementById('btnStatusChart');
        const btnCategory = document.getElementById('btnCategoryChart');
        const subtitle = document.getElementById('chartSubtitle');

        if (type === 'status') {
            statusContainer.style.display = 'flex';
            categoryContainer.style.display = 'none';
            btnStatus.classList.add('is-active');
            btnCategory.classList.remove('is-active');
            subtitle.innerText = 'Berdasarkan status';
        } else {
            statusContainer.style.display = 'none';
            categoryContainer.style.display = 'flex';
            btnStatus.classList.remove('is-active');
            btnCategory.classList.add('is-active');
            subtitle.innerText = 'Berdasarkan nominal kategori';
        }
    }
</script>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header 
            title="Dashboard Atasan" 
            subtitle="Kelola dan pantau pengajuan reimbursement Anda & tim" 
            :showNotification="true" 
            :showProfile="true" 
        />

        <div class="dashboard-content">
            <div class="welcome-card dashboard-welcome-card atasan-premium-card">
                <div class="welcome-content">
                    <div class="welcome-avatar">
                        <span class="avatar-initial">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                    </div>
                    <div class="welcome-text">
                        <h2 class="welcome-title">Halo, {{ explode(' ', Auth::user()->name)[0] }}!</h2>
                        <p class="welcome-subtitle">{{ Auth::user()->departemen->nama_departemen ?? 'Departemen' }}</p>
                        @php
                            $dashboardGeneratedAt = isset($generatedAt) ? \Carbon\Carbon::parse($generatedAt) : now();
                        @endphp
                        <p class="welcome-desc">Ringkasan realtime pengajuan tim, progres approval, dan proteksi anggaran.</p>
                        <div class="dashboard-live-meta">
                            <span class="dashboard-live-pill">
                                <span class="dashboard-live-dot"></span>
                                Realtime aktif
                            </span>
                            <span class="dashboard-last-updated">Update terakhir: {{ $dashboardGeneratedAt->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
                <div class="welcome-stats">
                    <div class="welcome-stat-item">
                        <div class="stat-value">Rp {{ number_format($nominalPending ?? 0, 0, ',', '.') }}</div>
                        <div class="stat-label">Sedang Diproses</div>
                    </div>
                    <div class="stat-divider"></div>
                    <div class="welcome-stat-item">
                        <div class="stat-value">Rp {{ number_format($nominalDisbursedMonth ?? 0, 0, ',', '.') }}</div>
                        <div class="stat-label">Dicairkan (Bulan Ini)</div>
                        @if(isset($disbursedGrowth))
                            <div class="welcome-growth {{ $disbursedGrowth >= 0 ? 'is-up' : 'is-down' }}">
                                {{ $disbursedGrowth >= 0 ? '+' : '-' }} {{ abs(round($disbursedGrowth, 1)) }}% <span class="welcome-growth-note">vs bln lalu</span>
                            </div>
                        @endif
                    </div>
                    <div class="stat-divider"></div>
                    <div class="welcome-stat-item is-protection" title="Total nominal pengajuan pribadi yang tidak dicairkan bulan ini (Ditolak oleh AI/Atasan/Finance)">
                        <div class="stat-value">Rp {{ number_format($nominalRejected ?? 0, 0, ',', '.') }}</div>
                        <div class="stat-label">Proteksi Anggaran</div>
                    </div>
                </div>
            </div>

            @if(isset($activeRequest) && $activeRequest)
                <div class="live-status-tracker atasan-premium-card" style="background: white; border: 1px solid #f1f5f9; border-radius: 1rem; padding: 1rem; box-shadow: 0 4px 20px -5px rgba(0,0,0,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                        <h4 style="margin: 0; color: #425d87; font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem;">
                            <span style="display: inline-block; width: 8px; height: 8px; background: #10b981; border-radius: 50%; animation: pulse 2s infinite;"></span>
                            Live Tracker: {{ $activeRequest->nomor_pengajuan }}
                        </h4>
                        <span style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; background: #f8fafc; padding: 0.3rem 0.75rem; border-radius: 20px; border: 1px solid #f1f5f9;">
                            {{ $activeRequest->status->label() }}
                        </span>
                    </div>
                    
                    <div class="stepper-visual" style="display: flex; justify-content: space-between; position: relative;">
                        <div style="position: absolute; top: 14px; left: 5%; right: 5%; height: 3px; background: #f1f5f9; z-index: 1;"></div>
                        
                        @php
                            $currentStatus = $activeRequest->status->value;
                            $steps = [
                                ['id' => 'validasi_ai', 'label' => 'Validasi'],
                                ['id' => 'menunggu_atasan', 'label' => 'Approval Atasan'],
                                ['id' => 'menunggu_finance', 'label' => 'Finance'],
                                ['id' => 'dicairkan', 'label' => 'Selesai']
                            ];
                            
                            $activeIndex = 0;
                            if ($currentStatus == 'menunggu_atasan') $activeIndex = 1;
                            elseif ($currentStatus == 'menunggu_finance') $activeIndex = 2;
                            elseif (in_array($currentStatus, ['terkirim_accurate', 'dicairkan'])) $activeIndex = 3;
                            
                            $progressWidth = ($activeIndex / 3) * 90;
                        @endphp

                        <div style="position: absolute; top: 14px; left: 5%; width: {{ $progressWidth }}%; height: 3px; background: #425d87; z-index: 2; transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);"></div>

                        @foreach($steps as $index => $step)
                            @php
                                $isCompleted = $index < $activeIndex;
                                $isActive = $index == $activeIndex;
                            @endphp
                            <div style="position: relative; z-index: 3; display: flex; flex-direction: column; align-items: center; width: 60px;">
                                <div style="width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; 
                                    background: {{ $isCompleted || $isActive ? '#425d87' : '#f8fafc' }}; 
                                    color: white; border: 3px solid {{ $isActive ? '#cbd5e1' : ($isCompleted ? '#425d87' : '#f1f5f9') }};
                                    transition: all 0.3s ease;">
                                    @if($isCompleted)
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    @else
                                        <span style="font-size: 0.75rem; font-weight: 800; color: {{ $isActive ? 'white' : '#94a3b8' }}">{{ $index + 1 }}</span>
                                    @endif
                                </div>
                                <span style="font-size: 0.65rem; font-weight: {{ $isActive ? '800' : '600' }}; color: {{ $isActive ? '#425d87' : '#94a3b8' }}; margin-top: 0.6rem; text-align: center; line-height: 1.2;">
                                    {{ $step['label'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(($approvalQueueStats->overdue_count ?? 0) > 0)
                <div class="sla-alert" style="background: white; border: 1px solid #fecaca; border-radius: 1rem; padding: 1.25rem; box-shadow: 0 4px 20px -5px rgba(239, 68, 68, 0.1); display: flex; align-items: center; gap: 1.25rem;">
                    <div style="background: #fee2e2; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #ef4444; flex-shrink: 0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="24" height="24">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <div>
                        <h4 style="margin: 0; color: #991b1b; font-weight: 800; font-size: 0.95rem;">Perhatian: Antrean SLA Terlampaui</h4>
                        <p style="margin: 0.2rem 0 0 0; color: #b91c1c; font-size: 0.8rem; opacity: 0.8;">Ada {{ $approvalQueueStats->overdue_count }} pengajuan tim yang belum diproses lebih dari 3 hari.</p>
                    </div>
                    <a href="{{ route('atasan.approval.index') }}" class="btn-modern btn-modern-danger btn-modern-sm sla-alert-cta">Lihat Antrean</a>
                </div>
            @endif

            <div class="amount-grid" style="display: grid; grid-template-columns: 2fr 1fr; gap: 1rem;">
                <div class="status-chart-card atasan-premium-card" style="min-height: 280px; display: flex; flex-direction: column; padding: 1rem;">
                    <div class="chart-header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0;">
                        <div>
                            <h3 class="chart-title" style="color: #1e293b;">Tren Pengeluaran Pribadi</h3>
                            <p class="chart-subtitle">Analisis 30 hari terakhir</p>
                        </div>
                        <div style="display: flex; gap: 0.75rem; align-items: center;">
                            <div style="background: #f0f7ff; color: #425d87; padding: 0.6rem 1rem; border-radius: 12px; font-size: 0.75rem; font-weight: 700; border: 1px solid #e0ebf7;">
                                Avg: Rp {{ number_format($avgDailySpending ?? 0, 0, ',', '.') }}
                            </div>
                            <div style="background: #f0fdf4; color: #10b981; padding: 0.6rem 1rem; border-radius: 12px; font-size: 0.75rem; font-weight: 700; border: 1px solid #dcfce7;">
                                Top: {{ $topCategory ?? 'Belum ada' }}
                            </div>
                        </div>
                    </div>
                    
                    <div style="flex: 1; min-height: 200px; margin-top: 0.5rem; position: relative;">
                        <canvas id="trendChart"></canvas>
                    </div>

                    <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px dashed #e2e8f0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem;">
                            <span style="font-size: 0.8rem; font-weight: 700; color: #64748b; display: flex; align-items: center; gap: 0.4rem;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20M17 5H9.5a4.5 4.5 0 0 0 0 9H12m0 0a4.5 4.5 0 1 1 0 9H4"></path></svg>
                                Kesehatan Anggaran Departemen
                            </span>
                            @php
                                $usagePercent = ($budgetLimit ?? 0) > 0 ? (($monthlySpending ?? 0) / ($budgetLimit ?? 1)) * 100 : 0;
                                $barColor = $usagePercent > 90 ? '#ef4444' : ($usagePercent > 70 ? '#f59e0b' : '#10b981');
                            @endphp
                            <span style="font-size: 0.8rem; font-weight: 800; color: {{ $barColor }};">
                                {{ number_format($usagePercent, 1) }}% Terpakai
                            </span>
                        </div>
                        <div style="height: 8px; background: #f1f5f9; border-radius: 10px; overflow: hidden; display: flex;">
                            <div style="width: {{ min($usagePercent, 100) }}%; background: {{ $barColor }}; border-radius: 10px; transition: width 1.5s cubic-bezier(0.34, 1.56, 0.64, 1);"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-top: 0.5rem;">
                            <span style="font-size: 0.7rem; color: #94a3b8; font-weight: 600;">Terpakai: Rp {{ number_format($monthlySpending ?? 0, 0, ',', '.') }}</span>
                            <span style="font-size: 0.7rem; color: #94a3b8; font-weight: 600;">Limit: Rp {{ number_format($budgetLimit ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                <div class="status-chart-card atasan-premium-card" style="display: flex; flex-direction: column; position: relative; padding: 1rem;">
                    <div class="chart-header" style="width: 100%; display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                        <div>
                            <h3 class="chart-title" style="color: #1e293b; margin-bottom: 0;">Distribusi</h3>
                            <p class="chart-subtitle" id="chartSubtitle">Berdasarkan status</p>
                        </div>
                        <div class="chart-switch">
                            <button onclick="toggleDistChart('status')" id="btnStatusChart" class="chart-switch-btn is-active">Status</button>
                            <button onclick="toggleDistChart('category')" id="btnCategoryChart" class="chart-switch-btn">Kategori</button>
                        </div>
                    </div>
                    
                    <div id="statusChartContainer" style="width: 100%; flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                        <div style="width: 160px; height: 160px; position: relative; margin: 0.5rem 0;">
                            <canvas id="statusChart"></canvas>
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; pointer-events: none;">
                                <div style="font-size: 1.25rem; font-weight: 800; color: #425d87; line-height: 1;">{{ $totalRequests ?? 0 }}</div>
                                <div style="font-size: 0.6rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px;">Total</div>
                            </div>
                        </div>

                        <div style="width: 100%; margin-top: auto; display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; padding-top: 0.75rem; border-top: 1px solid #f1f5f9;">
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.25rem;">
                                <div style="display: flex; align-items: center; gap: 0.4rem;">
                                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #425d87;"></div>
                                    <span style="font-size: 0.65rem; font-weight: 600; color: #64748b;">Atasan</span>
                                </div>
                                <span style="font-size: 0.65rem; font-weight: 700; color: #425d87;">{{ $statusData['menunggu_atasan'] ?? 0 }}</span>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.25rem;">
                                <div style="display: flex; align-items: center; gap: 0.4rem;">
                                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #7693ba;"></div>
                                    <span style="font-size: 0.65rem; font-weight: 600; color: #64748b;">Finance</span>
                                </div>
                                <span style="font-size: 0.65rem; font-weight: 700; color: #425d87;">{{ $statusData['menunggu_finance'] ?? 0 }}</span>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.25rem;">
                                <div style="display: flex; align-items: center; gap: 0.4rem;">
                                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></div>
                                    <span style="font-size: 0.65rem; font-weight: 600; color: #64748b;">Cair</span>
                                </div>
                                <span style="font-size: 0.65rem; font-weight: 700; color: #425d87;">{{ $statusData['dicairkan'] ?? 0 }}</span>
                            </div>
                            <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.25rem;">
                                <div style="display: flex; align-items: center; gap: 0.4rem;">
                                    <div style="width: 8px; height: 8px; border-radius: 50%; background: #ef4444;"></div>
                                    <span style="font-size: 0.65rem; font-weight: 600; color: #64748b;">Tolak</span>
                                </div>
                                <span style="font-size: 0.65rem; font-weight: 700; color: #425d87;">{{ ($statusData['ditolak_atasan'] ?? 0) + ($statusData['ditolak_finance'] ?? 0) }}</span>
                            </div>
                        </div>
                    </div>

                    <div id="categoryChartContainer" style="width: 100%; flex: 1; display: none; flex-direction: column; align-items: center; justify-content: center;">
                        <div style="width: 160px; height: 160px; position: relative; margin: 0.5rem 0;">
                            <canvas id="categoryChart"></canvas>
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center; pointer-events: none;">
                                <div style="font-size: 0.8rem; font-weight: 800; color: #425d87; line-height: 1;">Rp</div>
                                <div style="font-size: 0.55rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px;">Kategori</div>
                            </div>
                        </div>

                        <div style="width: 100%; margin-top: auto; max-height: 80px; overflow-y: auto; padding-right: 5px; display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem; padding-top: 0.5rem; border-top: 1px solid #f1f5f9;">
                            @foreach($categoryDist ?? [] as $index => $cat)
                                @php
                                    $catName = data_get($cat, 'nama_kategori', '-');
                                    $catTotal = (float) data_get($cat, 'total', 0);
                                @endphp
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.15rem 0.25rem; border-radius: 4px; background: #f8fafc;">
                                    <div style="display: flex; align-items: center; gap: 0.35rem; min-width: 0;">
                                        <div style="width: 6px; height: 6px; border-radius: 50%; background: {{ ['#425d87', '#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'][$index % 7] }}; flex-shrink: 0;"></div>
                                        <span style="font-size: 0.6rem; font-weight: 600; color: #64748b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $catName }}</span>
                                    </div>
                                    <span style="font-size: 0.6rem; font-weight: 700; color: #425d87; flex-shrink: 0;">{{ number_format($catTotal / 1000, 0) }}k</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            @php
                $atasanTotalStatus = (int) ($statusData['menunggu_atasan'] ?? 0)
                    + (int) ($statusData['menunggu_finance'] ?? 0)
                    + (int) ($statusData['dicairkan'] ?? 0)
                    + (int) ($statusData['ditolak_atasan'] ?? 0)
                    + (int) ($statusData['ditolak_finance'] ?? 0);
                $atasanApprovedRate = $atasanTotalStatus > 0
                    ? (((int) ($statusData['dicairkan'] ?? 0) / $atasanTotalStatus) * 100)
                    : 0;
                $atasanSlaOverdue = (int) ($approvalQueueStats->overdue_count ?? 0);
                $atasanQueueCount = (int) ($approvalQueueStats->total_count ?? 0);
            @endphp
            <div class="stats-grid dashboard-kpi-grid">
                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-label">Anggota Tim</div>
                        <div class="stat-value">{{ number_format($teamMembers->count(), 0, ',', '.') }}</div>
                        <div class="stat-sub-label">User yang Anda monitor</div>
                    </div>
                    <span class="stat-icon primary-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </span>
                </div>
                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-label">Approval Rate</div>
                        <div class="stat-value">{{ number_format($atasanApprovedRate, 1, ',', '.') }}%</div>
                        <div class="stat-sub-label">Persentase pengajuan dicairkan</div>
                    </div>
                    <span class="stat-icon success-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="9"></circle><path d="m8 12 2.8 2.8L16.5 9"></path></svg>
                    </span>
                </div>
                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-label">Antrian Aktif</div>
                        <div class="stat-value">{{ number_format($atasanQueueCount, 0, ',', '.') }}</div>
                        <div class="stat-sub-label">{{ $atasanSlaOverdue }} item melewati SLA</div>
                    </div>
                    <span class="stat-icon warning-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path></svg>
                    </span>
                </div>
            </div>

            <div class="atasan-team-grid">
                <div class="team-overview-card atasan-premium-card" style="background: white; border-radius: 1.25rem; border: 1px solid #f1f5f9; padding: 1rem; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);">
                    <div style="margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid #f1f5f9;">
                        <h3 style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin: 0;">Tim Anda</h3>
                        <p style="font-size: 0.75rem; color: #64748b; margin: 0.2rem 0 0 0;">{{ $teamMembers->count() }} anggota tim</p>
                    </div>
                    
                    @if($teamMembers->isEmpty())
                        <div style="text-align: center; padding: 2rem 1rem; color: #94a3b8;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="display: inline-block; opacity: 0.5;"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            <p style="font-size: 0.8rem; margin-top: 0.5rem;">Belum ada anggota tim</p>
                        </div>
                    @else
                        <div style="display: flex; flex-direction: column; gap: 0.5rem; max-height: 280px; overflow-y: auto;">
                            @foreach($teamMembers as $member)
                                <div style="display: grid; grid-template-columns: 1fr 80px 80px 80px; gap: 0.5rem; padding: 0.6rem; background: #f8fafc; border-radius: 8px; align-items: center; font-size: 0.75rem;">
                                    <div style="min-width: 0;">
                                        <div style="font-weight: 600; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $member->name }}</div>
                                        <div style="color: #94a3b8; font-size: 0.7rem;">{{ $member->total_count }} pengajuan</div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-weight: 700; color: #425d87;">{{ $member->pending_nominal > 0 ? 'Rp ' . number_format($member->pending_nominal / 1000, 0) . 'k' : '-' }}</div>
                                        <div style="color: #94a3b8; font-size: 0.65rem;">Proses</div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-weight: 700; color: #10b981;">{{ $member->approved_nominal > 0 ? 'Rp ' . number_format($member->approved_nominal / 1000, 0) . 'k' : '-' }}</div>
                                        <div style="color: #94a3b8; font-size: 0.65rem;">Cair</div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-weight: 700; color: #64748b;">{{ $member->total_count }}</div>
                                        <div style="color: #94a3b8; font-size: 0.65rem;">Total</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="recent-section atasan-premium-card" style="background: white; border-radius: 1.25rem; border: 1px solid #f1f5f9; padding: 1rem; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid #f1f5f9;">
                        <div>
                            <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0;">Perlu Persetujuan</h3>
                            <p style="font-size: 0.8rem; color: #64748b; margin: 0.2rem 0 0 0;">Dari tim Anda (5 terbaru)</p>
                        </div>
                        <a href="{{ route('atasan.approval.index') }}" class="btn-link-right">
                            Lihat Semua
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 12h14"></path><path d="M12 5l7 7-7 7"></path></svg>
                        </a>
                    </div>

                    @if($recentRequests->isEmpty())
                        <div style="text-align: center; padding: 3rem 1rem; color: #64748b;">
                            <div style="background: #f8fafc; width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" color="#94a3b8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            </div>
                            <p style="margin: 0; font-weight: 600; font-size: 0.9rem;">Belum ada pengajuan</p>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.8rem;">Semua pengajuan sudah disetujui</p>
                        </div>
                    @else
                        <div class="data-table-wrapper" style="box-shadow: none; border: none; border-radius: 0; overflow: visible; margin-top: 0;">
                            <table class="data-table" style="font-size: 0.85rem;">
                                <thead>
                                    <tr>
                                        <th style="width: 15%; padding: 0.6rem 0.5rem;">No. Pengajuan</th>
                                        <th style="width: 20%; padding: 0.6rem 0.5rem;">Pemohon</th>
                                        <th style="width: 12%; padding: 0.6rem 0.5rem;">Tanggal</th>
                                        <th style="width: 18%; padding: 0.6rem 0.5rem;">Nominal</th>
                                        <th style="width: 12%; text-align: center; padding: 0.6rem 0.5rem;">Status</th>
                                        <th style="width: 10%; text-align: center; padding: 0.6rem 0.5rem;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentRequests as $pengajuan)
                                        @php
                                            $approvalStatus = $pengajuan->status instanceof \BackedEnum ? $pengajuan->status->value : (string) $pengajuan->status;
                                        @endphp
                                        <tr>
                                            <td data-label="No. Pengajuan" style="padding: 0.6rem 0.5rem;">
                                                <span style="font-weight: 700; color: #334155;">{{ $pengajuan->nomor_pengajuan }}</span>
                                            </td>
                                            <td data-label="Pemohon" style="padding: 0.6rem 0.5rem;">
                                                <span style="font-weight: 600; color: #1e293b;">{{ $pengajuan->user->name }}</span>
                                            </td>
                                            <td data-label="Tanggal" style="padding: 0.6rem 0.5rem;">
                                                <span class="text-secondary">{{ $pengajuan->tanggal_pengajuan->format('d M Y') }}</span>
                                            </td>
                                            <td data-label="Nominal" style="padding: 0.6rem 0.5rem;">
                                                <span style="font-weight: 700; color: #1e293b;">{{ format_rupiah($pengajuan->nominal) }}</span>
                                            </td>
                                            <td data-label="Status" style="text-align: center; padding: 0.6rem 0.5rem;">
                                                <x-status-badge :status="$pengajuan->status" :transactionId="$pengajuan->accurate_transaction_id" />
                                            </td>
                                            <td data-label="Aksi" style="text-align: center; padding: 0.6rem 0.5rem;">
                                                <div style="display: flex; justify-content: center;">
                                                    <x-action-icon
                                                        :href="route('atasan.approval.show', $pengajuan->pengajuan_id)"
                                                        :variant="$approvalStatus === 'menunggu_atasan' ? 'approve' : 'view'"
                                                        :title="$approvalStatus === 'menunggu_atasan' ? 'Persetujuan' : 'Lihat detail'"
                                                        style="width: 28px; height: 28px;"
                                                    />
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="atasan-self-actions-grid">
                <div class="self-requests-card atasan-premium-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem; padding-bottom: 0.5rem; border-bottom: 1px solid #f1f5f9;">
                        <div>
                            <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0;">Pengajuan Pribadi Terbaru</h3>
                            <p style="font-size: 0.8rem; color: #64748b; margin: 0.2rem 0 0 0;">Pantau 5 pengajuan terakhir Anda</p>
                        </div>
                        <a href="{{ route('atasan.pengajuan.index') }}" class="btn-link-right">
                            Lihat Semua
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 12h14"></path><path d="M12 5l7 7-7 7"></path></svg>
                        </a>
                    </div>
                    @php
                        $myRequests = $myRecentRequests ?? collect();
                    @endphp
                    @if($myRequests->isEmpty())
                        <div style="text-align: center; padding: 3rem 1rem; color: #64748b;">
                            <div style="background: #f8fafc; width: 56px; height: 56px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" color="#94a3b8"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            </div>
                            <p style="margin: 0; font-weight: 600; font-size: 0.9rem;">Belum ada pengajuan pribadi</p>
                            <p style="margin: 0.25rem 0 0 0; font-size: 0.8rem;">Klik Buat Pengajuan untuk memulai</p>
                        </div>
                    @else
                        <div class="data-table-wrapper" style="box-shadow: none; border: none; border-radius: 0; overflow: visible; margin-top: 0;">
                            <table class="data-table" style="font-size: 0.85rem;">
                                <thead>
                                    <tr>
                                        <th style="width: 20%; padding: 0.6rem 0.5rem;">No. Pengajuan</th>
                                        <th style="width: 20%; padding: 0.6rem 0.5rem;">Vendor</th>
                                        <th style="width: 15%; padding: 0.6rem 0.5rem;">Tanggal</th>
                                        <th style="width: 15%; padding: 0.6rem 0.5rem;">Nominal</th>
                                        <th style="width: 15%; text-align: center; padding: 0.6rem 0.5rem;">Status</th>
                                        <th style="width: 10%; text-align: center; padding: 0.6rem 0.5rem;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($myRequests as $myRequest)
                                        <tr>
                                            <td data-label="No. Pengajuan" style="padding: 0.6rem 0.5rem;">
                                                <span style="font-weight: 700; color: #334155;">{{ $myRequest->nomor_pengajuan }}</span>
                                            </td>
                                            <td data-label="Vendor" style="padding: 0.6rem 0.5rem;">
                                                <span style="font-weight: 600; color: #1e293b;">{{ $myRequest->nama_vendor ?? '-' }}</span>
                                            </td>
                                            <td data-label="Tanggal" style="padding: 0.6rem 0.5rem;">
                                                <span class="text-secondary">{{ optional($myRequest->tanggal_pengajuan)->format('d M Y') }}</span>
                                            </td>
                                            <td data-label="Nominal" style="padding: 0.6rem 0.5rem;">
                                                <span style="font-weight: 700; color: #1e293b;">{{ format_rupiah($myRequest->nominal) }}</span>
                                            </td>
                                            <td data-label="Status" style="text-align: center; padding: 0.6rem 0.5rem;">
                                                <x-status-badge :status="$myRequest->status" :transactionId="$myRequest->accurate_transaction_id" />
                                            </td>
                                            <td data-label="Aksi" style="text-align: center; padding: 0.6rem 0.5rem;">
                                                <div style="display: flex; gap: 0.4rem; justify-content: center;">
                                                    <x-action-icon
                                                        :href="route('atasan.pengajuan.show', $myRequest->pengajuan_id)"
                                                        title="Lihat detail"
                                                        style="width: 28px; height: 28px;"
                                                    />
                                                    <x-action-icon
                                                        :href="route('atasan.pengajuan.create', ['duplicate_id' => $myRequest->pengajuan_id])"
                                                        variant="duplicate"
                                                        title="Ajukan lagi (Duplikat)"
                                                        style="width: 28px; height: 28px; background: #f0f7ff; color: #3b82f6;"
                                                    />
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="quick-actions-minimal">
                    <h3 class="quick-actions-title">
                        Aksi Cepat
                        <div class="quick-actions-title-line"></div>
                    </h3>

                    <div class="quick-actions-vertical">
                        <a href="{{ route('atasan.pengajuan.create') }}" class="modern-action-card quick-card quick-card-primary">
                            <div class="quick-card-icon quick-card-icon-primary">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                            </div>
                            <div class="quick-card-content">
                                <div class="quick-card-title">Buat pengajuan</div>
                                <div class="quick-card-subtitle">Pengajuan pribadi</div>
                            </div>
                        </a>

                        <a href="{{ route('atasan.pengajuan.index') }}" class="modern-action-card quick-card quick-card-warning">
                            <div class="quick-card-icon quick-card-icon-warning">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            </div>
                            <div class="quick-card-content">
                                <div class="quick-card-title">Riwayat pribadi</div>
                                <div class="quick-card-subtitle">Pantau semua pengajuan Anda</div>
                            </div>
                        </a>

                        <a href="{{ route('atasan.approval.index') }}" class="modern-action-card quick-card quick-card-warning">
                            <div class="quick-card-icon quick-card-icon-warning">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                            </div>
                            <div class="quick-card-content">
                                <div class="quick-card-title">Perlu persetujuan</div>
                                <div class="quick-card-subtitle">Approval dari tim</div>
                            </div>
                        </a>

                        <a href="{{ route('atasan.profile.index') }}" class="modern-action-card quick-card quick-card-success">
                            <div class="quick-card-icon quick-card-icon-success">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </div>
                            <div class="quick-card-content">
                                <div class="quick-card-title">Profil akun</div>
                                <div class="quick-card-subtitle">Pengaturan & info</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
