@extends('layouts.app')

@section('title', 'Riwayat Pencairan Dana')

@push('styles')
<style>
    .filter-form-history {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1.2fr auto;
        gap: 1rem;
        align-items: flex-end;
    }
    
    .data-table th {
        padding: 1.25rem 1.25rem !important;
        white-space: nowrap;
        background: #f8fafc;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: #64748b;
        border-bottom: 2px solid #f1f5f9;
    }
    
    .data-table td {
        padding: 1.15rem 1.25rem !important;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Column Widths & Alignment */
    .col-staff-no { width: 220px; }
    .col-vendor-dept { min-width: 180px; }
    .col-rekening { min-width: 150px; }
    .col-nominal { width: 150px; text-align: right !important; }
    .col-status { width: 160px; text-align: center !important; }
    .col-tanggal { width: 130px; text-align: center !important; }
    .col-aksi { width: 100px; text-align: center !important; }

    .data-table th.col-status, .data-table th.col-tanggal, .data-table th.col-aksi {
        text-align: center !important;
    }
    .data-table th.col-nominal {
        text-align: right !important;
    }

    .code-badge {
        font-family: 'JetBrains Mono', 'Fira Code', monospace;
        background: #f8fafc;
        color: #475569;
        padding: 2px 8px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-transaction-id {
        display: inline-block;
        font-size: 0.65rem !important;
        color: #94a3b8 !important;
        font-weight: 600;
        margin-top: 2px;
        letter-spacing: 0.02em;
    }

    @media (max-width: 1200px) {
        .filter-form-history {
            grid-template-columns: 1fr 1fr;
        }
        .filter-actions-pegawai {
            grid-column: span 2;
            justify-content: flex-end;
        }
    }
    .action-buttons-centered {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
    }
    .btn-action-modern {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none !important;
        border: 1px solid transparent;
    }
    .btn-action-modern:hover {
        transform: translateY(-2px) scale(1.05);
    }
    .btn-action-modern svg {
        stroke-width: 2.8;
        width: 18px;
        height: 18px;
    }
    
    .btn-detail-modern {
        background: rgba(66, 93, 135, 0.08) !important;
        color: #425d87 !important;
        border-color: rgba(66, 93, 135, 0.1) !important;
    }
    .btn-detail-modern:hover {
        background: #425d87 !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(66, 93, 135, 0.2);
    }

    /* Smart Stack for Info */
    .info-stack {
        display: flex;
        flex-direction: column;
        gap: 2px;
        line-height: 1.2;
    }
    .info-main {
        font-weight: 700;
        color: #334155;
        font-size: 0.875rem;
    }
    .info-sub {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 500;
    }
    .text-truncate-smart {
        max-width: 150px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
    }
    @media (max-width: 1200px) {
        .text-truncate-smart {
            max-width: 100px;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Riwayat Pencairan" subtitle="Daftar pengajuan yang telah dicairkan ke karyawan" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content">
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value">{{ $totalCount }}</div>
                    <div class="stat-label">Total Pengajuan</div>
                </div>
                <div class="stat-icon success-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
            </div>

            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value">{{ format_rupiah($totalNominal) }}</div>
                    <div class="stat-label">Total Nominal Cair</div>
                </div>
                <div class="stat-icon primary-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                        <line x1="9" y1="9" x2="9.01" y2="9"></line>
                        <line x1="15" y1="9" x2="15.01" y2="9"></line>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Requests Table Section -->
        <section class="modern-section">
            <div class="section-header" style="justify-content: flex-start !important; align-items: flex-start !important; gap: 2rem;">
                <div style="text-align: left !important; flex: 1;">
                    <h2 class="section-title" style="text-align: left !important; margin: 0;">Riwayat Pencairan</h2>
                    <p class="section-subtitle" style="text-align: left !important; margin-top: 4px;">Total: {{ $pengajuan->total() }} pengajuan</p>
                </div>
                <div class="header-actions">
                    <div class="export-actions">
                        <a href="#" onclick="exportCsv(event)" data-url="{{ route('finance.disbursement.history-export-csv') }}" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke CSV">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            CSV
                        </a>

                        <a href="#" onclick="exportPdf(event)" data-url="{{ route('finance.disbursement.history-export-pdf') }}" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke PDF">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="12" y1="18" x2="12" y2="12"></line>
                                <line x1="9" y1="15" x2="15" y2="15"></line>
                            </svg>
                            PDF
                        </a>
                    </div>
                    <a href="{{ route('finance.disbursement.index') }}" class="btn-modern btn-modern-secondary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; margin-right: 0.5rem;">
                            <path d="M19 12H5"></path>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>

            <div class="filter-container">
                <form id="filterForm" action="{{ route('finance.disbursement.history') }}" method="GET" class="filter-form-history">
                    <!-- Search -->
                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Pencarian</label>
                        <div class="search-group">
                            <div class="search-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <input type="text" name="search" id="searchInput" value="{{ request('search') }}" class="filter-input-pegawai search-input" placeholder="No. pengajuan, nama staff...">
                        </div>
                    </div>

                    <!-- Department -->
                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Departemen</label>
                        <select name="departemen_id" id="departemenInput" class="filter-input-pegawai">
                            <option value="">Semua Departemen</option>
                            @foreach($departemen as $dept)
                                <option value="{{ $dept->departemen_id }}" {{ request('departemen_id') == $dept->departemen_id ? 'selected' : '' }}>
                                    {{ $dept->nama_departemen }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date -->
                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Tanggal</label>
                        <div class="date-group-pegawai">
                            <input type="date" name="start_date" id="startDateInput" value="{{ $startDate->format('Y-m-d') }}" class="filter-input-pegawai">
                            <span class="date-separator">-</span>
                            <input type="date" name="end_date" id="endDateInput" value="{{ $endDate->format('Y-m-d') }}" class="filter-input-pegawai">
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="filter-actions-pegawai">
                        <button type="submit" class="btn-modern btn-modern-secondary btn-modern-sm">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            Filter
                        </button>
                        <a href="{{ route('finance.disbursement.history') }}" class="btn-reset-pegawai" title="Reset Filter">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </div>
                </form>
            </div>

            <div id="tableContainer">
                <div class="data-table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th class="col-staff-no">Staff / No. Pengajuan</th>
                                <th class="col-vendor-dept">Vendor / Dept</th>
                                <th class="col-rekening">Rekening Bank</th>
                                <th class="col-nominal">Nominal</th>
                                <th class="col-status">Status</th>
                                <th class="col-tanggal">Tanggal Cair</th>
                                <th class="col-aksi">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pengajuan as $item)
                                <tr>
                                    <td data-label="Staff / No. Pengajuan">
                                        <div class="info-stack">
                                            <span class="info-main text-truncate-smart" title="{{ $item->user->name }}">{{ $item->user->name }}</span>
                                            <span class="code-badge" style="width: fit-content; margin-top: 4px;">{{ $item->nomor_pengajuan }}</span>
                                        </div>
                                    </td>
                                    <td data-label="Vendor / Dept">
                                        <div class="info-stack">
                                            <span class="info-main text-truncate-smart" title="{{ $item->nama_vendor ?? '-' }}">{{ $item->nama_vendor ?? '-' }}</span>
                                            <span class="info-sub text-truncate-smart" title="{{ $item->departemen->nama_departemen }}">{{ $item->departemen->nama_departemen }}</span>
                                        </div>
                                    </td>
                                    <td data-label="Rekening Bank" class="col-rekening">
                                        <div class="info-stack">
                                            <span class="info-main text-mono" style="font-size: 0.8rem; letter-spacing: 0.02em;">{{ $item->user->nomor_rekening ?? '-' }}</span>
                                            <span class="info-sub" style="font-size: 0.7rem;">{{ $item->user->nama_bank ?? 'Bank -' }}</span>
                                        </div>
                                    </td>
                                    <td data-label="Nominal" class="col-nominal">
                                        <span class="amount-text" style="font-weight: 700; color: #0f172a;">{{ format_rupiah($item->nominal) }}</span>
                                    </td>
                                    <td data-label="Status" class="col-status">
                                        <x-status-badge :status="$item->status" :transactionId="$item->accurate_transaction_id" />
                                    </td>
                                    <td data-label="Tanggal Cair" class="col-tanggal">
                                        <span class="text-secondary" style="white-space: nowrap; font-size: 0.85rem;">{{ $item->tanggal_pencairan ? $item->tanggal_pencairan->format('d M Y') : '-' }}</span>
                                    </td>
                                    <td data-label="Aksi" class="col-aksi">
                                        <div class="action-buttons-centered">
                                            <a href="{{ route('finance.disbursement.show', $item->pengajuan_id) }}" class="btn-action-modern btn-detail-modern" title="Lihat Detail">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                    <circle cx="12" cy="12" r="3"></circle>
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 3rem 1rem;">
                                        <x-empty-state title="Tidak Ada Riwayat" description="Tidak ditemukan data pencairan untuk periode ini" />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pagination-wrapper" style="margin-top: 2rem;">
                    {{ $pengajuan->appends(request()->query())->links('components.pagination') }}
                </div>
            </div>
        </section>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/finance-master.js') }}"></script>
<script>
    // Export functions
    function getExportParams(btn) {
        const search = document.getElementById('searchInput').value;
        const departemen = document.getElementById('departemenInput').value;
        const startDate = document.getElementById('startDateInput').value;
        const endDate = document.getElementById('endDateInput').value;
        
        let url = btn.dataset.url;
        const params = new URLSearchParams();
        
        if (search) params.append('search', search);
        if (departemen) params.append('departemen_id', departemen);
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
        
        return url + (url.includes('?') ? '&' : '?') + params.toString();
    }

    function exportCsv(e) {
        e.preventDefault();
        window.location.href = getExportParams(e.currentTarget);
    }

    function exportPdf(e) {
        e.preventDefault();
        window.location.href = getExportParams(e.currentTarget);
    }
</script>
@endpush
@endsection
