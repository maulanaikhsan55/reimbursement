@extends('layouts.app')

@section('title', 'Audit Budget Bulanan')

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Audit Budget Bulanan" subtitle="Evaluasi plafon, realisasi, dan kontrol anggaran per departemen" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content reports-clean-content">
            <div class="stats-grid report-stats-grid">
                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-value">Rp {{ number_format($summary['total_annual_budget'] ?? 0, 0, ',', '.') }}</div>
                        <div class="stat-label">Total Plafon Tahunan</div>
                    </div>
                    <div class="stat-icon primary-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                </div>

                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-value">Rp {{ number_format($summary['total_year_usage'] ?? 0, 0, ',', '.') }}</div>
                        <div class="stat-label">Total {{ ($basis ?? 'komitmen') === 'realisasi' ? 'Realisasi' : 'Komitmen' }} Tahun Berjalan</div>
                    </div>
                    <div class="stat-icon info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 3v18h18"></path>
                            <path d="M7 14l4-4 3 3 4-6"></path>
                        </svg>
                    </div>
                </div>

                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-value">{{ number_format($summary['overall_utilization'] ?? 0, 1) }}%</div>
                        <div class="stat-label">Utilisasi Budget Tahunan</div>
                    </div>
                    <div class="stat-icon success-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 12a9 9 0 1 1-9-9"></path>
                            <path d="M21 3v9h-9"></path>
                        </svg>
                    </div>
                </div>

                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-value">{{ number_format($summary['departemen_over_limit'] ?? 0) }}</div>
                        <div class="stat-label">Departemen Melebihi Limit</div>
                        <div class="stat-note">Overrun: Rp {{ number_format($summary['total_overrun'] ?? 0, 0, ',', '.') }}</div>
                    </div>
                    <div class="stat-icon warning-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M10.29 3.86L1.82 18A2 2 0 0 0 3.53 21h16.94a2 2 0 0 0 1.71-3l-8.47-14.14a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                </div>
            </div>

            <section class="modern-section report-section budget-audit-section">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Matriks Audit Budget Per Bulan</h2>
                        <p class="section-subtitle">
                            Basis saat ini: <strong>{{ ($basis ?? 'komitmen') === 'realisasi' ? 'Realisasi (status dicairkan)' : 'Komitmen (semua non-ditolak)' }}</strong>.
                            Limit departemen dibaca sebagai plafon <strong>per bulan</strong>.
                        </p>
                    </div>
                </div>

                <div class="filter-container">
                    <form action="{{ route('finance.report.budget_audit') }}" method="GET" class="filter-form-finance budget-audit-filter">
                        <div class="filter-group-pegawai">
                            <label class="filter-label-pegawai">Tahun</label>
                            <select name="year" class="filter-input-pegawai">
                                @for($y = date('Y'); $y >= 2023; $y--)
                                    <option value="{{ $y }}" {{ (int) $year === (int) $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>

                        <div class="filter-group-pegawai">
                            <label class="filter-label-pegawai">Departemen</label>
                            <select name="departemen_id" class="filter-input-pegawai">
                                <option value="">Semua Departemen</option>
                                @foreach($allDepartemens as $dept)
                                    <option value="{{ $dept->departemen_id }}" {{ (string) $departemenId === (string) $dept->departemen_id ? 'selected' : '' }}>
                                        {{ $dept->nama_departemen }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group-pegawai">
                            <label class="filter-label-pegawai">Basis Perhitungan</label>
                            <select name="basis" class="filter-input-pegawai">
                                <option value="komitmen" {{ ($basis ?? 'komitmen') === 'komitmen' ? 'selected' : '' }}>Komitmen Budget</option>
                                <option value="realisasi" {{ ($basis ?? 'komitmen') === 'realisasi' ? 'selected' : '' }}>Realisasi Pencairan</option>
                            </select>
                        </div>

                        <div class="filter-actions-report">
                            <button type="submit" class="btn-modern btn-modern-secondary btn-modern-sm">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                </svg>
                                Filter
                            </button>
                            <a href="{{ route('finance.report.budget_audit') }}" class="btn-reset-pegawai" title="Reset Filter">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </a>
                        </div>
                    </form>
                </div>

                <div class="data-table-wrapper budget-audit-table-wrap">
                    <table class="data-table budget-audit-table">
                        <thead>
                            <tr>
                                <th class="sticky-col">Departemen</th>
                                <th class="num-col">Limit / Bln</th>
                                @for($m = 1; $m <= 12; $m++)
                                    <th class="num-col">{{ Carbon\Carbon::create(2000, $m, 1)->format('M') }}</th>
                                @endfor
                                <th class="num-col">Total Tahun</th>
                                <th class="num-col">Rata2 / Bln</th>
                                <th class="num-col">Utilisasi</th>
                                <th class="status-col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($auditData as $data)
                                @php
                                    $statusClass = $data['status_label'] === 'Melebihi'
                                        ? 'danger'
                                        : ($data['status_label'] === 'Waspada' ? 'warning' : 'success');
                                @endphp
                                <tr>
                                    <td class="sticky-col dept-col">
                                        <div class="dept-name">{{ $data['departemen']->nama_departemen }}</div>
                                        <div class="dept-meta">Over month: {{ $data['over_budget_months'] }} | Overrun: Rp {{ number_format($data['overrun_total'], 0, ',', '.') }}</div>
                                    </td>
                                    <td class="num-col">Rp {{ number_format($data['budget_limit'], 0, ',', '.') }}</td>
                                    @foreach($data['monthly_usage'] as $usage)
                                        @php
                                            $isOver = $data['budget_limit'] > 0 ? $usage > $data['budget_limit'] : $usage > 0;
                                        @endphp
                                        <td class="num-col month-cell {{ $isOver ? 'is-over' : ($usage > 0 ? 'is-used' : '') }}">
                                            {{ $usage > 0 ? number_format($usage, 0, ',', '.') : '-' }}
                                        </td>
                                    @endforeach
                                    <td class="num-col strong">Rp {{ number_format($data['total_year'], 0, ',', '.') }}</td>
                                    <td class="num-col">Rp {{ number_format($data['avg_monthly'], 0, ',', '.') }}</td>
                                    <td class="num-col">
                                        <span class="util-pill {{ $statusClass }}">{{ number_format($data['utilization_percent'], 1) }}%</span>
                                    </td>
                                    <td class="status-col">
                                        <span class="status-pill {{ $statusClass }}">{{ $data['status_label'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="18" class="empty-cell">Belum ada data audit budget untuk filter yang dipilih.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="budget-policy-note">
                    <p class="note-title">Aturan Budget yang diterapkan di sistem</p>
                    <p>1. Budget limit dibaca sebagai plafon bulanan per departemen (recurring), jadi setiap awal bulan limit kembali ke nilai awal.</p>
                    <p>2. Saldo sisa bulan sebelumnya tidak di-carry otomatis ke bulan berikutnya, kecuali perusahaan menetapkan kebijakan rollover tersendiri.</p>
                    <p>3. Untuk kontrol ketat, gunakan basis <strong>Komitmen</strong>; untuk laporan kas aktual gunakan basis <strong>Realisasi</strong>.</p>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .budget-audit-filter {
        display: grid;
        grid-template-columns: 1fr 1.4fr 1.2fr auto;
        gap: 0.85rem;
        align-items: flex-end;
    }

    .budget-audit-section .section-header {
        margin-bottom: 0.75rem !important;
    }

    .budget-audit-table-wrap {
        margin-top: 0.5rem;
        overflow-x: auto;
        overflow-y: hidden;
        scroll-behavior: smooth;
    }

    .budget-audit-table {
        width: max(100%, 2140px);
        min-width: 2140px;
        table-layout: fixed;
    }

    .budget-audit-table thead th {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .budget-audit-table .sticky-col {
        position: sticky;
        left: 0;
        z-index: 2;
        min-width: 250px;
        width: 250px;
    }

    .budget-audit-table thead .sticky-col {
        background: #edf3fc;
    }

    .budget-audit-table tbody .sticky-col {
        background: #ffffff;
        border-right: 1px solid rgba(62, 87, 130, 0.12);
    }

    .budget-audit-table .dept-col {
        min-width: 250px;
    }

    .budget-audit-table .dept-name {
        font-weight: 700;
        color: #1f324d;
        font-size: 0.83rem;
    }

    .budget-audit-table .dept-meta {
        font-size: 0.7rem;
        color: #7587a2;
        margin-top: 0.15rem;
    }

    .budget-audit-table .num-col {
        text-align: right;
        white-space: nowrap;
        min-width: 108px;
        width: 108px;
    }

    .budget-audit-table .status-col {
        text-align: center;
        min-width: 128px;
        width: 128px;
    }

    .budget-audit-table .strong {
        font-weight: 700;
        color: #38567f;
    }

    .budget-audit-table .month-cell.is-used {
        color: #334a6c;
    }

    .budget-audit-table .month-cell.is-over {
        color: #b91c1c;
        font-weight: 700;
        background: rgba(239, 68, 68, 0.08);
        border-radius: 10px;
    }

    .status-pill,
    .util-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.18rem 0.58rem;
        border-radius: 999px;
        font-size: 0.69rem;
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .status-pill.success,
    .util-pill.success {
        background: #dcfce7;
        color: #166534;
    }

    .status-pill.warning,
    .util-pill.warning {
        background: #fef3c7;
        color: #92400e;
    }

    .status-pill.danger,
    .util-pill.danger {
        background: #fee2e2;
        color: #b91c1c;
    }

    .empty-cell {
        text-align: center !important;
        color: #64748b;
        padding: 1.5rem !important;
    }

    .budget-policy-note {
        margin-top: 0.85rem;
        padding: 0.85rem 1rem;
        border-radius: 0.95rem;
        background: #f8fbff;
        border: 1px solid #dbe6f4;
    }

    .budget-policy-note .note-title {
        margin: 0 0 0.35rem;
        font-size: 0.78rem;
        font-weight: 700;
        color: #304d75;
        letter-spacing: 0.02em;
        text-transform: uppercase;
    }

    .budget-policy-note p {
        margin: 0.18rem 0;
        font-size: 0.79rem;
        color: #506482;
        line-height: 1.45;
    }

    @media (max-width: 1400px) {
        .budget-audit-filter {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .budget-audit-filter .filter-actions-report {
            grid-column: 1 / -1;
            justify-content: flex-end;
        }
    }

@media (max-width: 768px) {
        .budget-audit-filter {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/finance-master.js') }}"></script>
@endpush
