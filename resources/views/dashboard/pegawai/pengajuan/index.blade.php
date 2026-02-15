@extends('layouts.app')

@section('title', 'Pengajuan Reimbursement')

@push('styles')
<style>
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
    .col-no-pengajuan { width: 180px; }
    .col-vendor { min-width: 200px; }
    .col-tanggal { width: 130px; text-align: center !important; }
    .col-nominal { width: 150px; text-align: right !important; }
    .col-status { width: 160px; text-align: center !important; }
    .col-ai { width: 120px; text-align: center !important; }
    .col-aksi { width: 100px; text-align: center !important; }

    .data-table th.col-tanggal, .data-table th.col-status, .data-table th.col-ai, .data-table th.col-aksi {
        text-align: center !important;
    }
    .data-table th.col-nominal {
        text-align: right !important;
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

    .action-buttons-centered {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
    }

    .status-transaction-id {
        display: inline-block;
        font-size: 0.65rem !important;
        color: #94a3b8 !important;
        font-weight: 600;
        margin-top: 2px;
        letter-spacing: 0.02em;
    }

    .stat-sub-label {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 2px;
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header 
            title="Pengajuan Reimbursement" 
            subtitle="Kelola dan pantau pengajuan reimbursement Anda" 
            :showNotification="true" 
            :showProfile="true" 
        />

        <div class="dashboard-content">
        <!-- Stats Section -->
        <div id="statsContainer">
            @include('dashboard.pegawai.pengajuan.partials._stats', ['stats' => $stats])
        </div>

        <!-- Pengajuan List Section -->
        <section class="modern-section">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Daftar Pengajuan</h2>
                    <p class="section-subtitle">Total: {{ $pengajuanList->total() }} pengajuan pribadi</p>
                </div>
                <div class="header-actions">
                    <div class="export-actions">
                        <a href="#" onclick="exportCsv(event)" data-url="{{ route('pegawai.pengajuan.export-csv') }}" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke CSV">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            CSV
                        </a>

                        <a href="#" onclick="exportPdf(event)" data-url="{{ route('pegawai.pengajuan.export-pdf') }}" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke PDF">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="12" y1="18" x2="12" y2="12"></line>
                                <line x1="9" y1="15" x2="15" y2="15"></line>
                            </svg>
                            PDF
                        </a>
                    </div>

                    <a href="{{ route('pegawai.pengajuan.create') }}" class="btn-modern btn-modern-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Pengajuan Baru
                    </a>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-container">
                <form id="filterForm" action="{{ route('pegawai.pengajuan.index') }}" method="GET" class="filter-form-pegawai">
                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Pencarian</label>
                        <div class="search-group">
                            <div class="search-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <input type="text" name="search" id="searchInput" value="{{ request('search') }}" class="filter-input-pegawai search-input" placeholder="No. pengajuan, vendor, deskripsi...">
                        </div>
                    </div>

                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Status</label>
                        <select name="status" id="statusInput" class="filter-input-pegawai">
                            <option value="">Semua Status</option>
                            <option value="validasi_ai" {{ request('status') == 'validasi_ai' ? 'selected' : '' }}>Validasi AI</option>
                            <option value="menunggu_atasan" {{ request('status') == 'menunggu_atasan' ? 'selected' : '' }}>Menunggu Atasan</option>
                            <option value="ditolak_atasan" {{ request('status') == 'ditolak_atasan' ? 'selected' : '' }}>Ditolak Atasan</option>
                            <option value="menunggu_finance" {{ request('status') == 'menunggu_finance' ? 'selected' : '' }}>Menunggu Finance</option>
                            <option value="ditolak_finance" {{ request('status') == 'ditolak_finance' ? 'selected' : '' }}>Ditolak Finance</option>
                            <option value="terkirim_accurate" {{ request('status') == 'terkirim_accurate' ? 'selected' : '' }}>Disetujui Finance</option>
                            <option value="dicairkan" {{ request('status') == 'dicairkan' ? 'selected' : '' }}>Dicairkan</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                        </select>
                    </div>

                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Tanggal</label>
                        <div class="date-group-pegawai">
                            <input type="date" name="tanggal_from" id="tanggalFrom" value="{{ request('tanggal_from') }}" class="filter-input-pegawai">
                            <span class="date-separator">-</span>
                            <input type="date" name="tanggal_to" id="tanggalTo" value="{{ request('tanggal_to') }}" class="filter-input-pegawai">
                        </div>
                    </div>

                    <div class="filter-actions-pegawai">
                        <button type="submit" class="btn-modern btn-modern-secondary btn-modern-sm">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            Filter
                        </button>
                        <a href="{{ route('pegawai.pengajuan.index') }}" class="btn-reset-pegawai" title="Reset Filter">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div id="tableContainer">
                @include('dashboard.pegawai.pengajuan.partials._table')
            </div>
        </section>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Listener for real-time refresh
        window.addEventListener('refresh-pengajuan-table', function() {
            refreshTable();
        });

        function refreshTable() {
            const form = document.getElementById('filterForm');
            const url = new URL(form.action);
            const params = new URLSearchParams(new FormData(form));
            
            fetch(`${url.pathname}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('tableContainer').innerHTML = data.table;
                document.getElementById('statsContainer').innerHTML = data.stats;
            })
            .catch(error => console.error('Error refreshing table:', error));
        }
    });
</script>
<script src="{{ asset('js/pages/pegawai/pengajuan.js') }}"></script>
@endpush

@endsection
