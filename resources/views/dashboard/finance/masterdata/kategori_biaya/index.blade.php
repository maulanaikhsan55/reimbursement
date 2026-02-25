@extends('layouts.app')

@section('title', 'Kelola Kategori Biaya')

@push('styles')
<style>
    .filter-form-finance {
        display: grid;
        grid-template-columns: 1fr 220px auto;
        gap: 1rem;
        align-items: flex-end;
    }
    
    .modern-section {
        padding: 1.25rem !important;
    }

    .data-table-wrapper {
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }

    .data-table {
        table-layout: fixed !important;
        width: 100% !important;
        min-width: 1080px;
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

    /* Column Widths & Alignment */
    .col-kode { width: 80px; }
    .col-nama { width: 220px; }
    .col-desc { width: auto; min-width: 200px; }
    .col-status { width: 100px; text-align: center !important; }
    .col-coa { width: 180px; }
    .col-date { width: 130px; text-align: center !important; }
    .col-aksi { width: 90px; text-align: center !important; }

    .data-table th.col-status, .data-table th.col-date, .data-table th.col-aksi {
        text-align: center !important;
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

    .action-buttons-centered {
        display: flex;
        justify-content: center;
        gap: 0.75rem;
    }

    @media (max-width: 768px) {
        .filter-form-finance {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }

        .data-table {
            min-width: 920px;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Kelola Kategori Biaya" subtitle="Tambah, edit, atau hapus kategori pengeluaran" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content">
            <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">Total Kategori</div>
                </div>
                <div class="stat-icon primary-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
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
                    <h2 class="section-title">Daftar Kategori Biaya</h2>
                    <p class="section-subtitle">Kelola kategori pengeluaran</p>
                </div>
                <div class="header-actions">
                    <a href="{{ route('finance.masterdata.kategori_biaya.create') }}" class="btn-modern btn-modern-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Tambah Kategori
                    </a>
                </div>
            </div>

            <div class="filter-container">
                <form id="filterForm" action="{{ route('finance.masterdata.kategori_biaya.index') }}" method="GET" class="filter-form-finance">
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
                            <input type="text" name="search" id="searchInput" value="{{ request('search') }}" class="filter-input-pegawai search-input" placeholder="Nama kategori...">
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Status</label>
                        <select name="status" id="statusInput" class="filter-input-pegawai">
                            <option value="">Semua Status</option>
                            <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="filter-actions-pegawai">
                        <button type="submit" class="btn-modern btn-modern-secondary btn-modern-sm">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            Filter
                        </button>
                        <a href="{{ route('finance.masterdata.kategori_biaya.index') }}" class="btn-reset-pegawai" title="Reset Filter">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </div>
                </form>
            </div>

            <div id="tableContainer">
                @if ($kategoriBiaya->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                        </div>
                        <div class="empty-state-title">Belum ada kategori biaya</div>
                        <p>Mulai dengan menambahkan kategori biaya baru</p>
                        <div style="margin-top: 20px;">
                            <a href="{{ route('finance.masterdata.kategori_biaya.create') }}" class="btn-modern btn-modern-primary">
                                Tambah Kategori
                            </a>
                        </div>
                    </div>
                @else
                    <div class="data-table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="col-kode">Kode</th>
                                    <th class="col-nama">Nama Kategori</th>
                                    <th class="col-desc">Deskripsi</th>
                                    <th class="col-status">Status</th>
                                    <th class="col-coa">Default COA</th>
                                    <th class="col-date">Dibuat</th>
                                    <th class="col-aksi">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($kategoriBiaya as $kategori)
                                    <tr>
                                        <td data-label="Kode" class="col-kode">
                                            <code class="code-badge">{{ $kategori->kode_kategori }}</code>
                                        </td>
                                        <td data-label="Nama Kategori" class="col-nama">
                                            <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">{{ $kategori->nama_kategori }}</div>
                                        </td>
                                        <td data-label="Deskripsi" class="col-desc">
                                            <span style="color: #64748b; font-size: 0.875rem;">{{ $kategori->deskripsi ?? '-' }}</span>
                                        </td>
                                        <td data-label="Status" class="col-status">
                                            @if ($kategori->is_active)
                                                <span class="badge-status-active">Aktif</span>
                                            @else
                                                <span class="badge-status-inactive">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td data-label="Default COA" class="col-coa">
                                            @if($kategori->defaultCoa)
                                                <div style="font-size: 0.85rem; color: #1e293b; font-weight: 600;">
                                                    {{ $kategori->defaultCoa->kode_coa }}
                                                    <div style="font-size: 0.75rem; color: #64748b; font-weight: 500; margin-top: 2px;">{{ $kategori->defaultCoa->nama_coa }}</div>
                                                </div>
                                            @else
                                                <span style="color: #94a3b8; font-style: italic; font-size: 0.8rem;">- Belum diatur -</span>
                                            @endif
                                        </td>
                                        <td data-label="Dibuat" class="col-date">
                                            <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#425d87" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                                </svg>
                                                <span style="font-size: 0.75rem; color: #64748b; font-weight: 500;">{{ $kategori->created_at->format('d/m/Y') }}</span>
                                            </div>
                                        </td>
                                        <td data-label="Aksi" class="col-aksi">
                                            <div class="action-buttons-centered">
                                                <a href="{{ route('finance.masterdata.kategori_biaya.edit', $kategori->kategori_id) }}" class="btn-action-icon" title="Edit">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                    </svg>
                                                </a>
                                                @if (!$kategori->pengajuan()->exists())
                                                    <button type="button" class="btn-action-icon btn-action-delete" onclick="handleSingleDelete('{{ route('finance.masterdata.kategori_biaya.destroy', $kategori->kategori_id) }}', 'Hapus Kategori', 'Yakin ingin menghapus kategori ini?')" title="Hapus">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <polyline points="3 6 5 6 21 6"></polyline>
                                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                        </svg>
                                                    </button>
                                                @else
                                                    <button type="button" class="btn-action-icon" style="opacity: 0.5; cursor: not-allowed;" disabled title="Tidak dapat dihapus karena sedang digunakan dalam pengajuan">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-wrapper">
                        {{ $kategoriBiaya->links('components.pagination') }}
                    </div>
                @endif
            </div>
        </section>
    </div>
    <form id="singleDeleteForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
</div>
</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/finance-master.js') }}"></script>
@endpush
