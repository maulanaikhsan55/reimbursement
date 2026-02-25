@extends('layouts.app')

@section('title', 'Buku Besar Report')

@push('styles')
<style>
    .filter-form-finance {
        display: grid;
        grid-template-columns: 1fr 1fr 1.2fr auto;
        gap: 1rem;
        align-items: flex-end;
    }

    .report-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
    }

    .coa-group-card {
        background: white;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }

    .coa-group-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.06);
    }

    .coa-header {
        background: #f8fafc;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .coa-header h3 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e293b;
    }

    .coa-header-meta {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 2px;
        display: block;
    }

    .data-table {
        width: 100% !important;
        min-width: 820px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .ledger-table-wrap {
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
    }

    .data-table th {
        padding: 0.85rem 0.75rem !important;
        background: #f8fafc;
        text-transform: uppercase;
        font-size: 0.65rem;
        letter-spacing: 0.075em;
        font-weight: 700;
        color: #64748b;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .data-table td {
        padding: 0.85rem 0.75rem !important;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.8rem;
    }

    .coa-summary-badge {
        background: #425d87;
        color: white;
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-reconcile-mini {
        background: rgba(255, 255, 255, 0.2);
        border: none;
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .btn-reconcile-mini:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: rotate(180deg);
    }
    
    @media (max-width: 1024px) {
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

    .recon-result-banner {
        padding: 0.75rem 1.5rem;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .recon-match {
        background: #f0fdf4;
        color: #166534;
    }
    .recon-mismatch {
        background: #fff1f2;
        color: #991b1b;
    }
</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Buku Besar" subtitle="Ringkasan saldo per akun dari jurnal yang telah dibuat" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content reports-clean-content">
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
                    <div class="stat-value">{{ $ledger->count() }}</div>
                    <div class="stat-label">Total Akun</div>
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
            <div class="section-header">
                <div>
                    <h2 class="section-title">Saldo Per Akun</h2>
                    <p class="section-subtitle">Menampilkan total <strong>{{ $ledger->count() }}</strong> akun keuangan</p>
                </div>
                <div class="header-actions">
                    <div class="export-actions">
                        <a href="{{ route('finance.report.buku_besar.export_csv', request()->query()) }}" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke CSV">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            CSV
                        </a>
                        <a href="{{ route('finance.report.buku_besar.export_xlsx', request()->query()) }}" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke XLSX">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <path d="M8 13l3 4"></path>
                                <path d="M11 13l-3 4"></path>
                                <path d="M14 17h4"></path>
                            </svg>
                            XLSX
                        </a>
                        <a href="{{ route('finance.report.buku_besar.export_pdf', request()->query()) }}" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke PDF">
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
                <form id="filterForm" method="GET" action="{{ route('finance.report.buku_besar') }}" class="filter-form-finance">
                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Tanggal Mulai</label>
                        <input type="date" id="start_date" name="start_date" class="filter-input-pegawai" value="{{ request('start_date') }}">
                    </div>

                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Tanggal Akhir</label>
                        <input type="date" id="end_date" name="end_date" class="filter-input-pegawai" value="{{ request('end_date') }}">
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
                        <a href="{{ route('finance.report.buku_besar') }}" class="btn-reset-pegawai" title="Reset Filter">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </div>
                </form>
            </div>

            <div id="tableContainer" style="display: flex; flex-direction: column; gap: 1.5rem;">
                @if($ledger->count() > 0)
                    @foreach($ledger as $group)
                        <div class="coa-group-card">
                            <div class="coa-header">
                                <div>
                                    <h3>{{ $group['coa']->kode_coa }} - {{ $group['coa']->nama_coa }}</h3>
                                    <span class="coa-header-meta">{{ $group['entries']->count() }} transaksi</span>
                                </div>
                                <div class="coa-summary-badge" id="recon-badge-{{ $group['coa']->coa_id }}">
                                    Saldo Akhir: Rp {{ number_format($group['saldo_akhir'], 0, ',', '.') }}
                                    <button type="button" 
                                            onclick="reconcileAccurate('{{ $group['coa']->coa_id }}', {{ $group['saldo_akhir'] }})" 
                                            class="btn-reconcile-mini" 
                                            title="Reconcile with Accurate">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 12px; height: 12px;">
                                            <path d="M20 11a8.1 8.1 0 0 0-15.5-2m-.5 5v-5h5"></path>
                                            <path d="M4 13a8.1 8.1 0 0 0 15.5 2m.5-5v5h-5"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            
                            <div id="recon-result-{{ $group['coa']->coa_id }}" class="recon-result-banner" style="display: none;"></div>
                            <div class="ledger-table-wrap">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 85px;">Tanggal</th>
                                            <th style="width: 140px;">No. Referensi</th>
                                            <th>Deskripsi</th>
                                            <th style="text-align: right; width: 100px;">Debit</th>
                                            <th style="text-align: right; width: 100px;">Kredit</th>
                                            <th style="text-align: right; width: 100px;">Saldo</th>
                                            <th style="text-align: center; width: 60px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="3" style="font-style: italic; color: #64748b; font-size: 0.85rem;">Saldo Awal (Per {{ $startDate->format('d/m/Y') }})</td>
                                            <td style="text-align: right;">-</td>
                                            <td style="text-align: right;">-</td>
                                            <td style="text-align: right; font-weight: 600; background: #f8fafc; font-size: 0.85rem;">
                                                Rp {{ number_format($group['saldo_awal'], 0, ',', '.') }}
                                            </td>
                                            <td></td>
                                        </tr>
                                        @foreach($group['entries'] as $entry)
                                            <tr>
                                                <td style="font-size: 0.85rem; color: #64748b;">{{ $entry->tanggal_posting->format('d/m/Y') }}</td>
                                                <td style="font-size: 0.85rem;">
                                                    <div style="display: flex; align-items: center; gap: 6px;">
                                                        @if($entry->pengajuan && $entry->pengajuan->accurate_transaction_id)
                                                            <div title="Synced to Accurate" style="width: 8px; height: 8px; border-radius: 50%; background: #059669; flex-shrink: 0;"></div>
                                                        @else
                                                            <div title="Not Synced / Local Only" style="width: 8px; height: 8px; border-radius: 50%; background: #94a3b8; flex-shrink: 0;"></div>
                                                        @endif
                                                        <a href="{{ route('finance.report.jurnal_umum', ['search' => $entry->nomor_ref]) }}" style="font-weight: 600; color: #425d87; text-decoration: none;" title="Klik untuk lihat di Jurnal Umum">
                                                            {{ $entry->nomor_ref }}
                                                        </a>
                                                    </div>
                                                </td>
                                                <td style="font-size: 0.85rem;">{{ Str::limit($entry->deskripsi, 40) }}</td>
                                                <td style="text-align: right; font-size: 0.85rem;">
                                                    @if($entry->tipe_posting == 'debit')
                                                        <span style="font-weight: 600; color: #059669;">Rp {{ number_format($entry->nominal, 0, ',', '.') }}</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td style="text-align: right; font-size: 0.85rem;">
                                                    @if($entry->tipe_posting == 'credit')
                                                        <span style="font-weight: 600; color: #dc2626;">Rp {{ number_format($entry->nominal, 0, ',', '.') }}</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td style="text-align: right; font-weight: 600; background: #f8fafc; font-size: 0.85rem;">
                                                    Rp {{ number_format($entry->running_balance, 0, ',', '.') }}
                                                </td>
                                                <td style="text-align: center;">
                                                    <div style="display: flex; justify-content: center;">
                                                        @if($entry->pengajuan && $entry->pengajuan->file_bukti)
                                                            <button type="button" 
                                                                    onclick="openProofModal('{{ route('proof.show', $entry->pengajuan) }}', {{ str_ends_with(strtolower($entry->pengajuan->file_bukti), '.pdf') ? 'true' : 'false' }})" 
                                                                    class="btn-reset-pegawai" title="Lihat Bukti" style="width: 28px; height: 28px; background: #f1f5f9; color: #425d87; border: 1px solid #e2e8f0;">
                                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 16px; height: 16px; color: #425d87;">
                                                                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                                                                </svg>
                                                            </button>
                                                        @else
                                                            <span style="opacity: 0.3;">-</span>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr style="background: #f1f5f9;">
                                            <td colspan="3" style="font-weight: 700;">Subtotal Pergerakan & Saldo Akhir</td>
                                            <td style="text-align: right; font-weight: 700; color: #059669;">Rp {{ number_format($group['total_debit'], 0, ',', '.') }}</td>
                                            <td style="text-align: right; font-weight: 700; color: #dc2626;">Rp {{ number_format($group['total_credit'], 0, ',', '.') }}</td>
                                            <td style="text-align: right; font-weight: 800; color: #1e293b; background: #e2e8f0;">
                                                Rp {{ number_format($group['saldo_akhir'], 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @else
                    <x-report-empty-state title="Tidak Ada Jurnal" description="Tidak ada jurnal untuk periode yang dipilih" />
                @endif
            </div>
        </section>
        </div>
    </div>
</div>

<x-proof-modal />
@endsection

@push('scripts')
<script src="{{ asset('js/finance-master.js') }}"></script>
<script>
    (function() {
        const initBukuBesar = () => {
        const filterForm = document.getElementById('filterForm');
        if (filterForm) {
            const inputs = filterForm.querySelectorAll('select, input[type="date"]');
            inputs.forEach(input => {
                input.addEventListener('change', () => {
                    filterForm.submit();
                });
            });
        }

        window.reconcileAccurate = async function(coaId, localBalance) {
        const badge = document.getElementById(`recon-badge-${coaId}`);
        const resultBanner = document.getElementById(`recon-result-${coaId}`);
        const btn = badge.querySelector('.btn-reconcile-mini');
        
        // Loading state
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span class="loading-spinner-tiny"></span>';
        btn.disabled = true;

        try {
            const response = await fetch(`/finance/reports/buku-besar/reconcile?coa_id=${coaId}&local_balance=${localBalance}`);
            const result = await response.json();

            if (result.success) {
                resultBanner.style.display = 'flex';
                resultBanner.className = 'recon-result-banner ' + (result.is_match ? 'recon-match' : 'recon-mismatch');
                
                const formattedAccurate = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(result.accurate_balance);
                
                if (result.is_match) {
                    resultBanner.innerHTML = `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 16px; height: 16px;">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span>Saldo Sinkron dengan Accurate: <strong>${formattedAccurate}</strong></span>
                    `;
                    badge.style.background = '#059669';
                } else {
                    const diff = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(result.diff);
                    let html = `
                        <div style="display: flex; flex-direction: column; gap: 4px; width: 100%;">
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 16px; height: 16px;">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="8" x2="12" y2="12"></line>
                                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                </svg>
                                <span>Selisih dengan Accurate! Accurate: <strong>${formattedAccurate}</strong> (Selisih: ${diff})</span>
                            </div>
                    `;

                    if (result.discrepancies && result.discrepancies.length > 0) {
                        html += `<div style="margin-top: 8px; font-size: 0.75rem; border-top: 1px solid rgba(0,0,0,0.05); padding-top: 8px;">
                                    <strong>Transaksi di Accurate yang tidak ada di Web (30 hari terakhir):</strong>
                                    <ul style="margin: 4px 0 0 16px; padding: 0;">`;
                        result.discrepancies.forEach(d => {
                            const amt = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(d.amount);
                            html += `
                                <li style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                    <span>${d.date} - ${d.number}: ${amt} (${d.description})</span>
                                    <button onclick="syncToWeb('${d.accurate_id}', '${coaId}', '${d.trans_type}', this)" 
                                            style="padding: 2px 8px; font-size: 0.7rem; border-radius: 4px; border: 1px solid #dc2626; background: white; color: #dc2626; cursor: pointer;">
                                        Sync ke Web
                                    </button>
                                </li>`;
                        });
                        html += `    </ul>`;
                    } else {
                        html += `<div style="margin-top: 8px; font-size: 0.75rem; color: #666; font-style: italic;">
                                    Tidak ditemukan transaksi di Accurate yang cocok untuk disinkronkan dalam 90 hari terakhir.
                                 </div>`;
                    }
                    
                    html += `</div>`;
                    resultBanner.innerHTML = html;
                    badge.style.background = '#dc2626';
                }
            } else {
                if (window.showNotification) window.showNotification('error', 'Gagal', result.message || 'Gagal melakukan rekonsiliasi');
                else alert(result.message || 'Gagal melakukan rekonsiliasi');
            }
        } catch (error) {
            console.error('Reconciliation failed:', error);
            if (window.showNotification) window.showNotification('error', 'Error', 'Terjadi kesalahan jaringan');
        } finally {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        }
    }

    window.syncToWeb = async function(accurateId, coaId, transType, btn) {
        const originalText = btn.innerText;
        
        if (typeof openConfirmModal === 'function') {
            openConfirmModal(() => {
                performSyncToWeb(accurateId, coaId, transType, btn, originalText);
            }, 'Sinkronisasi Transaksi', 'Apakah Anda yakin ingin menyinkronkan transaksi ini ke database Web?');
        } else if (confirm('Apakah Anda yakin ingin menyinkronkan transaksi ini?')) {
            performSyncToWeb(accurateId, coaId, transType, btn, originalText);
        }
    }

    async function performSyncToWeb(accurateId, coaId, transType, btn, originalText) {
        btn.innerText = 'Syncing...';
        btn.disabled = true;

        try {
            const response = await fetch('{{ route("finance.report.buku_besar.sync_missing") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ accurate_id: accurateId, coa_id: coaId, trans_type: transType })
            });

            const text = await response.text();
            let result;
            try {
                result = JSON.parse(text);
            } catch (e) {
                console.error('Server returned non-JSON:', text);
                if (window.showNotification) window.showNotification('error', 'Server Error', text.substring(0, 100));
                btn.innerText = originalText;
                btn.disabled = false;
                return;
            }

            if (result.success) {
                if (window.showNotification) window.showNotification('success', 'Berhasil', 'Transaksi berhasil disinkronkan.');
                btn.innerText = 'Synced!';
                btn.className = 'btn-synced-success';
                btn.style.borderColor = '#059669';
                btn.style.color = '#059669';
                
                // Hide the parent list item
                const listItem = btn.closest('li');
                if (listItem) {
                    listItem.style.opacity = '0.5';
                    listItem.style.textDecoration = 'line-through';
                    setTimeout(() => {
                        listItem.style.display = 'none';
                    }, 800);
                }
                
                setTimeout(() => location.reload(), 1500);
            } else {
                if (window.showNotification) window.showNotification('error', 'Gagal', result.message || 'Gagal sinkronisasi');
                btn.innerText = originalText;
                btn.disabled = false;
            }
        } catch (error) {
            console.error('Sync failed:', error);
            if (window.showNotification) window.showNotification('error', 'Error', 'Terjadi kesalahan jaringan');
            btn.innerText = originalText;
            btn.disabled = false;
        }
    }

    };

    initBukuBesar();
    document.addEventListener('livewire:navigated', initBukuBesar);
})();
</script>
<style>
    .loading-spinner-tiny {
        width: 12px;
        height: 12px;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 0.8s linear infinite;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endpush
