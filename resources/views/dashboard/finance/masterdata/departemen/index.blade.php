@extends('layouts.app')

@section('title', 'Kelola Departemen')

@push('styles')
<style>
    .filter-form-finance {
        display: grid;
        grid-template-columns: 1fr auto auto auto;
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
        min-width: 1180px;
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

   
    .col-kode { width: 80px; }
    .col-nama { width: 220px; }
    .col-deskripsi { width: auto; min-width: 200px; }
    .col-status { width: 90px; text-align: center !important; }
    .col-users { width: 80px; text-align: center !important; }
    .col-budget { width: 150px; text-align: right !important; }
    .col-usage { width: 150px; text-align: right !important; }
    .col-sync { width: 140px; text-align: center !important; }
    .col-actions { width: 70px; text-align: center !important; }

    .data-table th.col-status, 
    .data-table th.col-actions, 
    .data-table th.col-users, 
    .data-table th.col-sync {
        text-align: center !important;
    }

    .data-table th.col-budget, 
    .data-table th.col-usage {
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

    .budget-info-banner {
        margin: 1rem 0;
        padding: 0.9rem 1rem;
        border: 1px solid #dbeafe;
        border-left: 4px solid #3b82f6;
        border-radius: 0.85rem;
        background: #f8fbff;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
    }

    .budget-info-icon {
        color: #3b82f6;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .budget-info-text {
        flex: 1;
        font-size: 0.9rem;
        line-height: 1.5;
        color: #334155;
    }

    .budget-info-close {
        border: 0;
        background: #eef2ff;
        color: #475569;
        width: 30px;
        height: 30px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .budget-info-close:hover {
        background: #dbeafe;
        color: #1e3a8a;
    }

    .sync-readonly-note {
        margin-top: 0.45rem;
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.4rem 0.7rem;
        border-radius: 999px;
        border: 1px solid #dbeafe;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.01em;
    }

    .sync-readonly-note svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }

    @media (max-width: 768px) {
        .filter-form-finance {
            grid-template-columns: 1fr;
        }

        .budget-info-banner {
            flex-direction: column;
            align-items: stretch;
        }

        .budget-info-close {
            align-self: flex-end;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Kelola Departemen" subtitle="Daftar departemen organisasi yang disinkronkan dari Accurate" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content">
            <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value">{{ $stats['total'] }}</div>
                    <div class="stat-label">Total Departemen</div>
                </div>
                <div class="stat-icon primary-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                </div>
            </div>
            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value">{{ $stats['with_users'] }}</div>
                    <div class="stat-label">Dengan Pengguna</div>
                </div>
                <div class="stat-icon success-icon">
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
                    <div class="stat-value">{{ $stats['without_users'] }}</div>
                    <div class="stat-label">Tanpa Pengguna</div>
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
                    <h2 class="section-title">Daftar Departemen</h2>
                    <p class="section-subtitle">Data disinkronkan secara otomatis dari Accurate</p>
                    <div class="sync-readonly-note">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="10" rx="2"></rect>
                            <path d="M7 11V8a5 5 0 0 1 10 0v3"></path>
                        </svg>
                        Referensi Accurate (Read-only)
                    </div>
                </div>
                <div class="header-actions">
                    <form id="syncForm" action="{{ route('finance.masterdata.departemen.sync') }}" method="POST" class="d-inline">
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
                <form id="filterForm" action="{{ route('finance.masterdata.departemen.index') }}" method="GET" class="filter-form-finance">
                    <!-- Search -->
                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Pencarian Departemen</label>
                        <div class="search-group">
                            <div class="search-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <input type="text" name="search" id="searchInput" value="{{ request('search') }}" class="filter-input-pegawai search-input" placeholder="Ketik nama atau kode departemen...">
                        </div>
                    </div>

                    <!-- Month Filter -->
                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Bulan</label>
                        <select name="month" id="monthInput" class="filter-input-pegawai">
                            <option value="" {{ empty($selectedMonth) ? 'selected' : '' }}>Semua Bulan</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->isoFormat('MMMM') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Year Filter -->
                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Tahun</label>
                        <select name="year" id="yearInput" class="filter-input-pegawai">
                            <option value="" {{ empty($selectedYear) ? 'selected' : '' }}>Semua Tahun</option>
                            @foreach(range(now()->year - 2, now()->year + 1) as $y)
                                <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                            @endforeach
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
                        <a href="{{ route('finance.masterdata.departemen.index') }}" class="btn-reset-pegawai" title="Reset Filter">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </div>
                </form>
            </div>

            @php
                $selectedMonthName = $selectedMonth ? \Carbon\Carbon::create()->month($selectedMonth)->isoFormat('MMMM') : null;
                if ($selectedMonthName && $selectedYear) {
                    $budgetPeriodLabel = $selectedMonthName.' '.$selectedYear;
                } elseif ($selectedMonthName) {
                    $budgetPeriodLabel = $selectedMonthName.' (semua tahun)';
                } elseif ($selectedYear) {
                    $budgetPeriodLabel = 'Semua bulan '.$selectedYear;
                } else {
                    $budgetPeriodLabel = 'semua periode';
                }
            @endphp
            <div class="budget-info-banner" id="budgetInfoBanner">
                <svg class="budget-info-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <div class="budget-info-text">
                    <strong>Info:</strong> Budget limit berlaku per bulan. Realisasi dan Sisa Budget dihitung otomatis berdasarkan transaksi pada periode <strong>{{ $budgetPeriodLabel }}</strong>.
                </div>
                <button type="button" class="budget-info-close" id="budgetInfoClose" aria-label="Tutup informasi budget" title="Tutup">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div id="tableContainer">
                @if ($departemen->isEmpty())
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                        </div>
                        <div class="empty-state-title">Belum ada departemen</div>
                        <p>Silakan lakukan sinkronisasi untuk mengambil data dari Accurate</p>
                    </div>
                @else
                    <div class="data-table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="col-kode">Kode</th>
                                    <th class="col-nama">Nama Departemen</th>
                                    <th class="col-deskripsi">Deskripsi</th>
                                    <th class="col-status">Status</th>
                                    <th class="col-users">Users</th>
                                    <th class="col-budget">Budget/Bln</th>
                                    <th class="col-usage">Realisasi & Sisa</th>
                                    <th class="col-sync">Sinkron</th>
                                    <th class="col-actions">Aksi</th>
                                </tr>
                            </thead>
                                <tbody>
                                    @foreach ($departemen as $dept)
                                        @php
                                            $percentage = $dept->budget_limit > 0 ? ($dept->current_usage / $dept->budget_limit) * 100 : 0;
                                            $usageColor = $percentage > 100 ? '#e11d48' : ($percentage > 80 ? '#f59e0b' : '#10b981');
                                        @endphp
                                        <tr>
                                            <td data-label="Kode" class="col-kode">
                                                <code class="code-badge">{{ $dept->kode_departemen }}</code>
                                            </td>
                                            <td data-label="Nama Departemen" class="col-nama">
                                                <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;">{{ $dept->nama_departemen }}</div>
                                            </td>
                                            <td data-label="Deskripsi" class="col-deskripsi">
                                                <div style="font-size: 0.8rem; color: #64748b;">{{ $dept->deskripsi ?? '-' }}</div>
                                            </td>
                                            <td data-label="Status" class="col-status">
                                                @if($dept->is_active)
                                                    <span class="badge-status-active">Aktif</span>
                                                @else
                                                    <span class="badge-status-inactive">Nonaktif</span>
                                                @endif
                                            </td>
                                            <td data-label="Users" class="col-users">
                                                <div style="display: flex; justify-content: center;">
                                                    <span class="badge-status-active" style="background: rgba(59, 130, 246, 0.1) !important; color: #2563eb !important; border: 1px solid rgba(59, 130, 246, 0.2) !important; min-width: 32px; display: inline-flex; justify-content: center;">
                                                        {{ $dept->users_count }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td data-label="Budget/Bln" class="col-budget">
                                                <div style="font-weight: 700; color: #1e293b;">
                                                    Rp {{ number_format($dept->budget_limit, 0, ',', '.') }}
                                                </div>
                                            </td>
                                            <td data-label="Realisasi & Sisa" class="col-usage">
                                                @php
                                                    $sisa = $dept->budget_limit - $dept->current_usage;
                                                    $sisaColor = $sisa < 0 ? '#e11d48' : '#64748b';
                                                @endphp
                                                <div style="font-weight: 700; color: {{ $usageColor }};">
                                                    Rp {{ number_format($dept->current_usage, 0, ',', '.') }}
                                                </div>
                                                <div style="font-size: 0.75rem; color: {{ $sisaColor }}; font-weight: 600;">
                                                    Sisa: Rp {{ number_format($sisa, 0, ',', '.') }}
                                                </div>
                                            </td>
                                            <td data-label="Sinkron" class="col-sync">
                                                <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                                        <path d="M3 21v-5h5"></path>
                                                        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                                        <path d="M16 3h5v5"></path>
                                                    </svg>
                                                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 500;">
                                                        {{ $dept->last_sync_at ? $dept->last_sync_at->diffForHumans() : '-' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="col-actions">
                                                <button type="button" class="btn-modern btn-modern-secondary btn-modern-sm" onclick="openEditBudgetModal('{{ $dept->departemen_id }}', '{{ $dept->nama_departemen }}', '{{ $dept->budget_limit }}', '{{ $dept->deskripsi }}')" style="padding: 6px;">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="pagination-wrapper">
                            {{ $departemen->links('components.pagination') }}
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Edit Budget Modal -->
<div id="editBudgetModal" class="modal" data-app-modal="1" style="display: none; position: fixed; inset: 0; z-index: 10000; background: rgba(15,23,42,0.45); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div style="background: white; width: 90%; max-width: 500px; border-radius: 1.5rem; padding: 2rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 id="modalTitle" style="font-size: 1.25rem; font-weight: 700; color: #1e293b;">Set Budget Departemen</h2>
            <button onclick="closeEditBudgetModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>
        
        <form id="editBudgetForm" method="POST">
            @csrf
            @method('PUT')
            
            <div style="margin-bottom: 1.5rem;">
                <label for="budgetLimitInput" style="display: block; font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Anggaran Bulanan (Rp)</label>
                <input type="hidden" name="budget_limit" id="budgetLimitInput" required>
                <input type="text" id="budgetLimitDisplay" inputmode="numeric" autocomplete="off" placeholder="Rp 0" style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 0.75rem; font-size: 1rem; font-weight: 700; color: #1e293b;">
                <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.5rem;">Format otomatis Rupiah. Nilai tersimpan sebagai angka murni.</p>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="deskripsiInput" style="display: block; font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsiInput" rows="3" style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.9rem; resize: none;"></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="button" onclick="closeEditBudgetModal()" class="btn-modern btn-modern-secondary" style="flex: 1;">Batal</button>
                <button type="submit" class="btn-modern btn-modern-primary" style="flex: 2;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/finance-master.js') }}"></script>
<script>
    window.__editBudgetModalState = window.__editBudgetModalState || {
        prevOverflow: '',
    };

    function ensureEditBudgetModalMounted() {
        const modal = document.getElementById('editBudgetModal');
        if (!modal) return null;
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        return modal;
    }

    function openEditBudgetModal(id, name, budget, desc) {
        const modal = ensureEditBudgetModalMounted();
        const form = document.getElementById('editBudgetForm');
        const title = document.getElementById('modalTitle');
        const budgetInput = document.getElementById('budgetLimitInput');
        const budgetDisplay = document.getElementById('budgetLimitDisplay');
        const descInput = document.getElementById('deskripsiInput');

        if (!modal || !form || !title || !budgetInput || !budgetDisplay || !descInput) return;
        
        title.innerText = `Set Budget: ${name}`;
        form.action = `{{ url('/finance/masterdata/departemen') }}/${id}`;
        const numericBudget = parseRupiahValue(budget);
        budgetInput.value = numericBudget;
        budgetDisplay.value = formatRupiahDisplay(numericBudget);
        descInput.value = desc === 'null' ? '' : desc;
        
        modal.style.display = 'flex';
        window.__editBudgetModalState.prevOverflow = document.body.style.overflow || '';
        document.body.style.overflow = 'hidden';
    }

    function closeEditBudgetModal() {
        const modal = document.getElementById('editBudgetModal');
        if (!modal) return;

        modal.style.display = 'none';
        document.body.style.overflow = window.__editBudgetModalState.prevOverflow || '';
        window.__editBudgetModalState.prevOverflow = '';
    }

    function bindEditBudgetModalBehavior() {
        if (window.__editBudgetModalBound) {
            return;
        }
        window.__editBudgetModalBound = true;

        document.addEventListener('click', function(event) {
            const modal = document.getElementById('editBudgetModal');
            if (!modal) return;
            if (event.target === modal) {
                closeEditBudgetModal();
            }
        });

        document.addEventListener('keydown', function(event) {
            const modal = document.getElementById('editBudgetModal');
            if (!modal) return;
            if (event.key === 'Escape' && modal.style.display === 'flex') {
                closeEditBudgetModal();
            }
        });
    }

    function initEditBudgetModalMount() {
        ensureEditBudgetModalMounted();
        bindEditBudgetModalBehavior();
        bindBudgetInputFormatter();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEditBudgetModalMount);
    } else {
        initEditBudgetModalMount();
    }
    document.addEventListener('livewire:navigated', initEditBudgetModalMount);

    window.addEventListener('beforeunload', function () {
        const modal = document.getElementById('editBudgetModal');
        if (modal && modal.style.display === 'flex') {
            closeEditBudgetModal();
        }
    });

    function initBudgetInfoBannerClose() {
        const infoBanner = document.getElementById('budgetInfoBanner');
        const closeButton = document.getElementById('budgetInfoClose');

        if (!infoBanner || !closeButton) {
            return;
        }

        if (closeButton.dataset.closeBound === '1') {
            return;
        }
        closeButton.dataset.closeBound = '1';

        closeButton.addEventListener('click', function () {
            infoBanner.style.display = 'none';
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBudgetInfoBannerClose);
    } else {
        initBudgetInfoBannerClose();
    }

    document.addEventListener('livewire:navigated', initBudgetInfoBannerClose);

    function parseRupiahValue(value) {
        const numeric = String(value ?? '').replace(/[^0-9]/g, '');
        return numeric === '' ? 0 : parseInt(numeric, 10);
    }

    function formatRupiahDisplay(value) {
        return `Rp ${new Intl.NumberFormat('id-ID').format(value || 0)}`;
    }

    function bindBudgetInputFormatter() {
        const budgetDisplay = document.getElementById('budgetLimitDisplay');
        const budgetInput = document.getElementById('budgetLimitInput');
        const form = document.getElementById('editBudgetForm');

        if (!budgetDisplay || !budgetInput || !form) {
            return;
        }

        if (budgetDisplay.dataset.formatBound !== '1') {
            budgetDisplay.dataset.formatBound = '1';
            budgetDisplay.addEventListener('input', function () {
                const numeric = parseRupiahValue(this.value);
                budgetInput.value = numeric;
                this.value = formatRupiahDisplay(numeric);
            });

            budgetDisplay.addEventListener('blur', function () {
                const numeric = parseRupiahValue(this.value);
                budgetInput.value = numeric;
                this.value = formatRupiahDisplay(numeric);
            });
        }

        if (form.dataset.submitBound !== '1') {
            form.dataset.submitBound = '1';
            form.addEventListener('submit', function () {
                budgetInput.value = parseRupiahValue(budgetDisplay.value);
            });
        }
    }
</script>
@endpush
