@extends('layouts.app')

@section('title', 'Jurnal Umum Report')

@push('styles')
<style>
    .filter-form-finance {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1fr 1.2fr auto;
        gap: 1rem;
        align-items: flex-end;
    }

    .report-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }

    .data-table-wrapper {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        background: white;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        margin-top: 1rem;
    }

    .data-table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
    }

    .data-table th {
        padding: 1rem 0.75rem !important;
        background: #f8fafc;
        text-transform: uppercase;
        font-size: 0.65rem;
        letter-spacing: 0.075em;
        font-weight: 700;
        color: #64748b;
        border-bottom: 2px solid #f1f5f9;
    }
    
    .data-table td {
        padding: 1rem 0.75rem !important;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
        transition: all 0.2s ease;
    }

    .data-table tbody tr:hover td {
        background-color: #f8fafc;
    }

    .badge-unbalanced {
        padding: 4px 10px;
        background: #fff1f2;
        color: #e11d48;
        border: 1px solid #ffe4e6;
        border-radius: 50px;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .btn-sync-mini {
        padding: 4px 10px;
        background: #4f46e5;
        color: white;
        border: none;
        border-radius: 50px;
        font-size: 0.65rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        transition: all 0.2s;
    }

    .btn-sync-mini:hover {
        background: #4338ca;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
    }
    
    @media (max-width: 1400px) {
        .filter-form-finance {
            grid-template-columns: repeat(2, 1fr);
        }
        .filter-actions-report {
            grid-column: 1 / -1;
            justify-content: flex-end;
        }
    }
    
    @media (max-width: 768px) {
        .filter-form-finance {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Jurnal Umum" subtitle="Pencatatan detail setiap transaksi dari pengajuan yang dikirim ke Accurate" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content">
        <div class="stats-grid">
            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value">Rp {{ number_format($totalDebit, 0, ',', '.') }}</div>
                    <div class="stat-label">Total Debit</div>
                </div>
                <div class="stat-icon primary-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                </div>
            </div>

            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value">Rp {{ number_format($totalCredit, 0, ',', '.') }}</div>
                    <div class="stat-label">Total Kredit</div>
                </div>
                <div class="stat-icon success-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14"></path>
                    </svg>
                </div>
            </div>

            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value">Rp {{ number_format($totalDebit - $totalCredit, 0, ',', '.') }}</div>
                    <div class="stat-label">Balance</div>
                </div>
                <div class="stat-icon warning-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 12h18M12 3v18"></path>
                    </svg>
                </div>
            </div>

            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value">{{ $paginatedJournal->total() }}</div>
                    <div class="stat-label">Total Entry</div>
                </div>
                <div class="stat-icon info-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 11l3 3L22 4"></path>
                        <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                    </svg>
                </div>
            </div>
        </div>

        <section class="modern-section report-section">
            <div class="section-header" style="justify-content: flex-start !important; align-items: flex-start !important; gap: 2rem;">
                <div style="text-align: left !important; flex: 1;">
                    <h2 class="section-title" style="text-align: left !important; margin: 0;">Jurnal Entry Details</h2>
                    <p class="section-subtitle" style="text-align: left !important; margin-top: 4px;">Menampilkan {{ $paginatedJournal->total() }} baris pencatatan jurnal</p>
                </div>
                <div class="header-actions">
                    <div class="export-actions">
                        <a href="{{ route('finance.report.jurnal_umum.export_csv', request()->query()) }}" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke CSV">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            CSV
                        </a>
                        <a href="{{ route('finance.report.jurnal_umum.export_pdf', request()->query()) }}" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke PDF">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="12" y1="18" x2="12" y2="12"></line>
                                <line x1="9" y1="15" x2="15" y2="15"></line>
                            </svg>
                            PDF
                        </a>
                    </div>
                </div>
            </div>

            <div class="filter-container">
                <form id="filterForm" method="GET" action="{{ route('finance.report.jurnal_umum') }}" class="filter-form-finance">
                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Cari No. Ref</label>
                        <div class="search-group">
                            <div class="search-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <input type="text" name="search" id="searchInput" value="{{ request('search') }}" class="filter-input-pegawai search-input" placeholder="No. Ref...">
                        </div>
                    </div>

                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date" class="filter-input-pegawai" value="{{ $startDate->format('Y-m-d') }}">
                    </div>

                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Tanggal Akhir</label>
                        <input type="date" id="end_date" name="end_date" class="filter-input-pegawai" value="{{ $endDate->format('Y-m-d') }}">
                    </div>

                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">COA</label>
                        <select id="coa_id" name="coa_id" class="filter-input-pegawai">
                            <option value="">-- Semua COA --</option>
                            @foreach($coas as $coa)
                                <option value="{{ $coa->coa_id }}" {{ $coaId == $coa->coa_id ? 'selected' : '' }}>
                                    {{ $coa->kode_coa }} - {{ $coa->nama_coa }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-actions-report">
                        <button type="submit" class="btn-modern btn-modern-secondary btn-modern-sm">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            Filter
                        </button>
                        <a href="{{ route('finance.report.jurnal_umum') }}" class="btn-reset-pegawai" title="Reset Filter">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </div>
                </form>
            </div>

            <div id="tableContainer">
                @if($paginatedJournal->count() > 0)
                    <div class="data-table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 85px;">Tanggal</th>
                                    <th style="width: 140px;">No. Referensi</th>
                                    <th style="width: 110px;">Pengajuan</th>
                                    <th style="width: 80px;">Kode COA</th>
                                    <th>Nama Akun</th>
                                    <th>Deskripsi</th>
                                    <th style="text-align: right; width: 100px;">Debit</th>
                                    <th style="text-align: right; width: 100px;">Kredit</th>
                                    <th style="text-align: center; width: 60px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($paginatedJournal as $group)
                                    @foreach($group['entries'] as $index => $entry)
                                        <tr style="{{ $index === 0 ? 'border-top: 2px solid #f1f5f9; background: #f8fafc;' : '' }}">
                                            @if($index === 0)
                                                <td style="color: #64748b; font-size: 0.85rem;">{{ $entry->tanggal_posting->format('d/m/Y') }}</td>
                                                <td style="font-size: 0.85rem;">
                                                    <div style="display: flex; align-items: center; gap: 6px;">
                                                        @if($entry->pengajuan && $entry->pengajuan->accurate_transaction_id)
                                                            <div title="Synced to Accurate" style="width: 8px; height: 8px; border-radius: 50%; background: #059669; flex-shrink: 0;"></div>
                                                        @else
                                                            <div title="Not Synced / Local Only" style="width: 8px; height: 8px; border-radius: 50%; background: #94a3b8; flex-shrink: 0;"></div>
                                                        @endif
                                                        <div style="font-weight: 600; color: #1e293b;">{{ $entry->nomor_ref }}</div>
                                                    </div>
                                                    @if(!$group['is_balanced'])
                                                        <div style="margin-top: 6px; display: flex; flex-direction: column; gap: 6px;">
                                                            <span class="badge-unbalanced">
                                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                                                Selisih: Rp {{ number_format(abs($group['total_debit'] - $group['total_credit']), 0, ',', '.') }}
                                                            </span>
                                                            <button type="button" onclick="syncByRef('{{ $entry->nomor_ref }}', this)" class="btn-sync-mini">
                                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 10px; height: 10px;">
                                                                    <path d="M23 4v6h-6M1 20v-6h6"></path>
                                                                    <path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"></path>
                                                                </svg>
                                                                Sync
                                                            </button>
                                                        </div>
                                                    @endif
                                                </td>
                                                <td style="font-size: 0.85rem;">
                                                    @if($entry->pengajuan)
                                                        <a href="{{ route('finance.approval.show', $entry->pengajuan_id) }}" style="color: #425d87; text-decoration: none; font-weight: 600;" title="Klik untuk lihat Detail Pengajuan">
                                                            {{ $entry->pengajuan->nomor_pengajuan }}
                                                        </a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            @else
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            @endif
                                            
                                            <td style="font-family: 'Courier New', monospace; font-size: 0.8rem; {{ $entry->tipe_posting === 'credit' ? 'padding-left: 1.25rem;' : '' }}">
                                                {{ $entry->coa->kode_coa }}
                                            </td>
                                            <td style="color: #64748b; font-size: 0.85rem; {{ $entry->tipe_posting === 'credit' ? 'padding-left: 2rem;' : '' }}">
                                                {{ $entry->coa->nama_coa }}
                                            </td>
                                            
                                            @if($index === 0)
                                                <td style="font-size: 0.85rem;">{{ Str::limit($entry->deskripsi, 30) }}</td>
                                            @else
                                                <td></td>
                                            @endif

                                            <td style="text-align: right; font-weight: 600; font-size: 0.85rem; color: #059669;">
                                                @if($entry->tipe_posting === 'debit')
                                                    Rp {{ number_format($entry->nominal, 0, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td style="text-align: right; font-weight: 600; font-size: 0.85rem; color: #dc2626;">
                                                @if($entry->tipe_posting === 'credit')
                                                    Rp {{ number_format($entry->nominal, 0, ',', '.') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td style="text-align: center;">
                                                @if($index === 0 && $entry->pengajuan && $entry->pengajuan->file_bukti)
                                                    <button type="button" 
                                                            onclick="openProofModal('{{ route('proof.show', $entry->pengajuan) }}', {{ str_ends_with(strtolower($entry->pengajuan->file_bukti), '.pdf') ? 'true' : 'false' }})" 
                                                            class="btn-action-report" 
                                                            title="Lihat Bukti Lampiran"
                                                            style="background: none; border: none; cursor: pointer; padding: 0;">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 16px; height: 16px; color: #425d87;">
                                                            <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background: #f8fafc; border-top: 2px solid #f1f5f9;">
                                    <td colspan="6" style="padding: 1rem 0.75rem; text-align: right; font-weight: 700; color: #64748b; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em;">TOTAL KESELURUHAN</td>
                                    <td style="padding: 1rem 0.75rem; text-align: right; font-weight: 800; color: #059669; font-size: 0.9rem;">
                                        Rp {{ number_format($totalDebit, 0, ',', '.') }}
                                    </td>
                                    <td style="padding: 1rem 0.75rem; text-align: right; font-weight: 800; color: #dc2626; font-size: 0.9rem;">
                                        Rp {{ number_format($totalCredit, 0, ',', '.') }}
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="pagination-wrapper">
                        {{ $paginatedJournal->links('components.pagination') }}
                    </div>
                @else
                    <x-report-empty-state title="Tidak ada data jurnal" description="Tidak ada jurnal entry untuk periode yang dipilih" />
                @endif
            </div>
        </section>
        </div>
    </div>
</div>

<x-proof-modal />

@push('scripts')
<script src="{{ asset('js/finance-master.js') }}"></script>
<script>
// Live Filtering
(function() {
    const initJurnalUmum = () => {
        const filterForm = document.getElementById('filterForm');
        const searchInput = document.getElementById('searchInput');
        
        if (filterForm) {
            const inputs = filterForm.querySelectorAll('select, input[type="date"]');
            inputs.forEach(input => {
                input.addEventListener('change', () => {
                    filterForm.submit();
                });
            });

            if (searchInput) {
                let timeout = null;
                searchInput.addEventListener('input', () => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => {
                        filterForm.submit();
                    }, 800);
                });
            }
        }

        window.syncByRef = async function(nomorRef, btnElement) {
            const btn = btnElement || (event && event.target ? event.target.closest('button') : null);
            if (!btn) return;
            const originalContent = btn.innerHTML;
            
            if (typeof openConfirmModal === 'function') {
                openConfirmModal(() => {
                    performSync(nomorRef, btn, originalContent);
                }, 'Sinkronisasi Ulang', `Apakah Anda yakin ingin menyinkronkan ulang transaksi ${nomorRef} dari Accurate? Ini akan memperbarui data jurnal di sistem.`);
            } else if (confirm(`Apakah Anda yakin ingin menyinkronkan ulang transaksi ${nomorRef}?`)) {
                performSync(nomorRef, btn, originalContent);
            }
        };

        async function performSync(nomorRef, btn, originalContent) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="width: 10px; height: 10px; margin-right: 4px;"></span> Syncing...';
            
            try {
                const response = await fetch("{{ route('finance.report.jurnal_umum.sync_by_ref') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ nomor_ref: nomorRef })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    if (window.showNotification) window.showNotification('success', 'Berhasil', result.message);
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    if (window.showNotification) window.showNotification('error', 'Gagal', result.message);
                    btn.disabled = false;
                    btn.innerHTML = originalContent;
                }
            } catch (error) {
                console.error('Error syncing:', error);
                if (window.showNotification) window.showNotification('error', 'Error', 'Terjadi kesalahan sistem saat sinkronisasi.');
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }
        }
    };

    initJurnalUmum();
    document.addEventListener('livewire:navigated', initJurnalUmum);
})();
</script>
@endpush
@endsection
