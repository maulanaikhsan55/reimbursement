@extends('layouts.app')

@section('title', 'Ringkasan Laporan - Humplus Reimbursement')
@section('page-title', 'Ringkasan Laporan')

@push('styles')
<style>
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
    }

    .summary-table-wrap {
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        overflow: auto;
        background: #fff;
    }

    .summary-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 520px;
    }

    .summary-table th,
    .summary-table td {
        padding: 0.8rem 0.9rem;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
        color: #334155;
    }

    .summary-table th {
        background: #f8fafc;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #64748b;
        font-weight: 700;
    }

    .summary-table td.text-right,
    .summary-table th.text-right {
        text-align: right;
    }

    @media (max-width: 1024px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Ringkasan Laporan" subtitle="Ikhtisar pencairan untuk pemantauan operasional finance" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content reports-clean-content">
            <div class="stats-grid">
                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-value">{{ number_format($totalCairkan) }}</div>
                        <div class="stat-label">Total Pencairan</div>
                    </div>
                    <div class="stat-icon primary-icon">
                        <x-icon name="check-circle" class="w-6 h-6" />
                    </div>
                </div>

                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-value">Rp {{ number_format($totalNominal, 0, ',', '.') }}</div>
                        <div class="stat-label">Total Nominal Dicairkan</div>
                    </div>
                    <div class="stat-icon success-icon">
                        <x-icon name="credit-card" class="w-6 h-6" />
                    </div>
                </div>
            </div>

            <section class="modern-section" style="margin-top: 1rem;">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Tren & Distribusi</h2>
                        <p class="section-subtitle">Ringkasan bulanan dan kontribusi per departemen</p>
                    </div>
                    <a href="{{ route('finance.report.index') }}" class="btn-modern btn-modern-secondary btn-modern-sm">
                        Kembali ke Report Center
                    </a>
                </div>

                <div class="summary-grid">
                    <div>
                        <h3 style="font-size: 0.95rem; font-weight: 700; color: #334155; margin: 0 0 0.75rem;">Pencairan per Bulan (12 bulan)</h3>
                        <div class="summary-table-wrap">
                            <table class="summary-table">
                                <thead>
                                    <tr>
                                        <th>Bulan</th>
                                        <th class="text-right">Jumlah</th>
                                        <th class="text-right">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cairkanByMonth as $row)
                                        <tr>
                                            <td>{{ $row->month ? \Carbon\Carbon::createFromFormat('Y-m', $row->month)->translatedFormat('M Y') : '-' }}</td>
                                            <td class="text-right">{{ number_format($row->count) }}</td>
                                            <td class="text-right">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3">Belum ada data pencairan.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div>
                        <h3 style="font-size: 0.95rem; font-weight: 700; color: #334155; margin: 0 0 0.75rem;">Kontribusi per Departemen</h3>
                        <div class="summary-table-wrap">
                            <table class="summary-table">
                                <thead>
                                    <tr>
                                        <th>Departemen</th>
                                        <th class="text-right">Jumlah</th>
                                        <th class="text-right">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cairkanByDept as $row)
                                        <tr>
                                            <td>{{ $row->departemen->nama_departemen ?? 'Tanpa Departemen' }}</td>
                                            <td class="text-right">{{ number_format($row->count) }}</td>
                                            <td class="text-right">Rp {{ number_format($row->total, 0, ',', '.') }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3">Belum ada data departemen.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection
