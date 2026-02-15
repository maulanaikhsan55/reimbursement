@extends('layouts.app')

@section('title', 'Pusat Laporan Keuangan')

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Pusat Laporan" subtitle="Akses semua laporan operasional dan akuntansi" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content">
            <div class="reports-grid">
                <a href="{{ route('report.jurnal_umum') }}" class="report-link">
                    <div class="report-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                    </div>
                    <div class="report-info">
                        <h3>Jurnal Umum</h3>
                        <p>Catatan transaksi harian dan jurnal entries</p>
                    </div>
                </a>

                <a href="{{ route('report.buku_besar') }}" class="report-link">
                    <div class="report-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                        </svg>
                    </div>
                    <div class="report-info">
                        <h3>Buku Besar</h3>
                        <p>Rincian per akun COA dan saldo terkini</p>
                    </div>
                </a>

                <a href="{{ route('report.laporan_arus_kas') }}" class="report-link">
                    <div class="report-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <div class="report-info">
                        <h3>Laporan Arus Kas</h3>
                        <p>Aliran kas masuk dan keluar per periode</p>
                    </div>
                </a>

                <a href="{{ route('report.reconciliation') }}" class="report-link">
                    <div class="report-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <div class="report-info">
                        <h3>Reconciliation</h3>
                        <p>Rekonsiliasi data Accurate dengan sistem</p>
                    </div>
                </a>

                <a href="{{ route('report.budget_audit') }}" class="report-link" style="border-left: 4px solid #425d87;">
                    <div class="report-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                            <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                        </svg>
                    </div>
                    <div class="report-info">
                        <h3>Audit Budget</h3>
                        <p>Evaluasi penggunaan anggaran tiap bulan</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles)
<style>
    .reports-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        padding: 20px 0;
    }
    
    .report-link {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 20px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        text-decoration: none;
        color: inherit;
        transition: all 0.2s ease;
    }
    
    .report-link:hover {
        border-color: #3b82f6;
        background: #f8fafc;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    
    .report-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f5f9;
        border-radius: 10px;
        color: #475569;
    }
    
    .report-link:hover .report-icon {
        background: #dbeafe;
        color: #3b82f6;
    }
    
    .report-icon svg {
        width: 24px;
        height: 24px;
    }
    
    .report-info h3 {
        margin: 0 0 4px 0;
        font-size: 16px;
        font-weight: 600;
        color: #1e293b;
    }
    
    .report-info p {
        margin: 0;
        font-size: 13px;
        color: #64748b;
    }
</style>
@endpush
