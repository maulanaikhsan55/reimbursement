@extends('layouts.app')

@section('title', 'Kelola Kas/Bank')

@push('styles')
<style>
    .filter-form-finance {
        display: grid;
        grid-template-columns: 1fr 180px auto;
        gap: 1rem;
        align-items: flex-end;
    }
    
    .modern-section {
        padding: 1.25rem !important;
    }

    .data-table {
        table-layout: fixed !important;
        width: 100% !important;
    }

    .data-table th {
        padding: 0.75rem 0.5rem !important;
        white-space: nowrap;
        background: #f8fafc;
        text-transform: uppercase;
        font-size: 0.65rem;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: #64748b;
        border-bottom: 2px solid #f1f5f9;
    }
    
    .data-table td {
        padding: 0.75rem 0.5rem !important;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
        word-wrap: break-word;
    }

    .col-kode { width: 100px; }
    .col-nama { width: 220px; }
    .col-catatan { width: auto; min-width: 150px; }
    .col-status { width: 90px; text-align: center !important; }
    .col-tipe { width: 120px; text-align: center !important; }
    .col-saldo { width: 160px; text-align: right !important; }
    .col-as-of { width: 110px; text-align: center !important; }
    .col-sync { width: 140px; text-align: center !important; }

    .data-table th.col-tipe, .data-table th.col-as-of, .data-table th.col-status, .data-table th.col-sync {
        text-align: center !important;
    }
    .data-table th.col-saldo {
        text-align: right !important;
    }

    .badge-status-active {
        background: rgba(16, 185, 129, 0.1) !important;
        color: #059669 !important;
        border: 1px solid rgba(16, 185, 129, 0.2) !important;
        font-size: 0.7rem !important;
        font-weight: 600 !important;
        padding: 4px 10px !important;
        border-radius: 50px !important;
    }

    .badge-status-inactive {
        background: rgba(239, 68, 68, 0.1) !important;
        color: #dc2626 !important;
        border: 1px solid rgba(239, 68, 68, 0.2) !important;
        font-size: 0.7rem !important;
        font-weight: 600 !important;
        padding: 4px 10px !important;
        border-radius: 50px !important;
    }

    .code-badge {
        font-family: 'JetBrains Mono', 'Fira Code', monospace;
        background: #f8fafc;
        color: #475569;
        padding: 4px 10px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .amount-text {
        font-family: 'Inter', sans-serif;
        font-weight: 700;
        color: #1e293b;
        font-size: 0.95rem;
    }

    @media (max-width: 768px) {
        .filter-form-finance {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Kelola Kas/Bank" subtitle="Daftar rekening kas dan bank yang disinkronkan dari Accurate" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content">
            <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">Total Rekening</div>
                </div>
                <div class="stat-icon primary-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                        <path d="M1 10h22"></path>
                    </svg>
                </div>
            </div>
            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value">{{ $stats['aktif'] }}</div>
                    <div class="stat-label">Aktif</div>
                </div>
                <div class="stat-icon success-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
            </div>
            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value">{{ $stats['nonaktif'] }}</div>
                    <div class="stat-label">Nonaktif</div>
                </div>
                <div class="stat-icon warning-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                </div>
            </div>
        </div>

        <section class="modern-section">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Daftar Kas/Bank</h2>
                    <p class="section-subtitle">Data disinkronkan secara otomatis dari Accurate</p>
                </div>
                <div class="header-actions">
                    <form action="{{ route('finance.masterdata.kas_bank.sync') }}" method="POST" class="d-inline" id="syncForm">
                        @csrf
                        <input type="hidden" name="force_full_sync" id="forceFullSync" value="0">
                        <button type="button" class="btn-modern btn-modern-primary" id="syncButton" style="margin-right: 8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                                <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                <path d="M3 3v5h5"></path>
                                <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"></path>
                                <path d="M16 21h5v-5"></path>
                            </svg>
                            <span id="syncButtonText">Sync Accurate Sekarang</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="filter-container">
                <form id="filterForm" action="{{ route('finance.masterdata.kas_bank.index') }}" method="GET" class="filter-form-finance">
                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Pencarian</label>
                        <div class="search-group">
                            <div class="search-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <input type="text" name="search" id="searchInput" value="{{ request('search') }}" class="filter-input-pegawai search-input" placeholder="Nama atau kode kas/bank...">
                        </div>
                    </div>

                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Status</label>
                        <select name="status" id="statusInput" class="filter-input-pegawai">
                            <option value="">Semua Status</option>
                            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>

                    <div class="filter-actions-pegawai">
                        <button type="submit" class="btn-modern btn-modern-secondary btn-modern-sm">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            Filter
                        </button>
                        <a href="{{ route('finance.masterdata.kas_bank.index') }}" class="btn-reset-pegawai" title="Reset Filter">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </div>
                </form>
            </div>

            <div id="tableContainer">
                @if ($kasBanks->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                <path d="M1 10h22"></path>
                            </svg>
                        </div>
                        <div class="empty-state-title">Data Kas/Bank Kosong</div>
                        <p>Silakan lakukan sinkronisasi untuk mengambil data dari Accurate.</p>
                    </div>
                @else
                    <div class="data-table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="col-kode">Kode Perkiraan</th>
                                    <th class="col-nama">Nama Kas / Bank</th>
                                    <th class="col-catatan">Deskripsi</th>
                                    <th class="col-status">Status</th>
                                    <th class="col-tipe">Tipe Akun</th>
                                    <th class="col-saldo">Saldo</th>
                                    <th class="col-as-of">Per Tanggal</th>
                                    <th class="col-sync">Sinkron</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($kasBanks as $item)
                                    <tr>
                                        <td data-label="Kode Perkiraan" class="col-kode">
                                            <code class="code-badge">{{ $item->kode_kas_bank }}</code>
                                        </td>
                                        <td data-label="Nama Kas / Bank" class="col-nama">
                                            <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">{{ $item->nama_kas_bank }}</div>
                                        </td>
                                        <td data-label="Deskripsi" class="col-catatan">
                                            <div style="font-size: 0.85rem; color: #64748b; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $item->deskripsi }}">
                                                {{ $item->deskripsi ?? '-' }}
                                            </div>
                                        </td>
                                        <td data-label="Status" class="col-status">
                                            @if($item->is_active)
                                                <span class="badge-status-active">Aktif</span>
                                            @else
                                                <span class="badge-status-inactive">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td data-label="Tipe Akun" class="col-tipe">
                                            <span class="meta-badge" style="text-transform: uppercase; background: rgba(66, 93, 135, 0.08); color: #425d87; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">{{ $item->tipe_akun ?? 'CASH_BANK' }}</span>
                                        </td>
                                        <td data-label="Saldo" class="col-saldo">
                                            <span class="amount-text">{{ $item->currency_code ?? 'IDR' }} {{ number_format($item->saldo ?? 0, 0, ',', '.') }}</span>
                                        </td>
                                        <td data-label="Per Tanggal" class="col-as-of">
                                            <span style="font-size: 0.85rem; color: #64748b; font-weight: 500;">
                                                {{ $item->as_of_date ? $item->as_of_date->format('d/m/Y') : '-' }}
                                            </span>
                                        </td>
                                        <td data-label="Sinkron" class="col-sync">
                                            <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                                    <path d="M3 21v-5h5"></path>
                                                    <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                                    <path d="M16 3h5v5"></path>
                                                </svg>
                                                <span style="font-size: 0.75rem; color: #64748b; font-weight: 500;">{{ $item->last_sync_at ? $item->last_sync_at->diffForHumans() : '-' }}</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-wrapper">
                        {{ $kasBanks->links('components.pagination') }}
                    </div>
                @endif
            </div>
        </section>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/finance-master.js') }}"></script>
<script>
</script>
@endpush
