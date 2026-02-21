@extends('layouts.app')

@section('title', 'Laporan Arus Kas')

@push('styles')
<style>
    .filter-form-finance {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 1fr auto;
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
        margin-bottom: 1.5rem;
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

    .coa-summary-badge {
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .data-table {
        width: 100% !important;
        border-collapse: separate;
        border-spacing: 0;
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

    .badge-dept {
        background: #f1f5f9;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 600;
        color: #475569;
        text-transform: uppercase;
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
        <x-page-header title="Laporan Arus Kas" subtitle="Laporan transaksi kas dan bank periode terpilih" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content reports-clean-content">
        <div class="stats-grid report-stats-grid">
            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value">Rp {{ number_format($totalInflow, 0, ',', '.') }}</div>
                    <div class="stat-label">Total Penerimaan Kas</div>
                </div>
                <div class="stat-icon success-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                </div>
            </div>

            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value">Rp {{ number_format($totalOutflow, 0, ',', '.') }}</div>
                    <div class="stat-label">Total Pengeluaran Kas</div>
                </div>
                <div class="stat-icon warning-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5v14"></path>
                    </svg>
                </div>
            </div>

            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value">Rp {{ number_format($netFlow, 0, ',', '.') }}</div>
                    <div class="stat-label">Kenaikan/(Penurunan) Kas</div>
                </div>
                <div class="stat-icon {{ $netFlow >= 0 ? 'success-icon' : 'primary-icon' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                </div>
            </div>

            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value">{{ $totalEntries }}</div>
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
            <div class="section-header">
                <div>
                    <h2 class="section-title">Arus Kas Berdasarkan Aktivitas</h2>
                    <p class="section-subtitle">Menampilkan total <strong>{{ $totalEntries }}</strong> baris transaksi kas</p>
                </div>
                <div class="header-actions">
                    <div class="export-actions">
                        <a href="{{ route('finance.report.laporan_arus_kas.export_csv', request()->query()) }}" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke CSV">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            CSV
                        </a>
                        <a href="{{ route('finance.report.laporan_arus_kas.export_xlsx', request()->query()) }}" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke XLSX">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <path d="M8 13l3 4"></path>
                                <path d="M11 13l-3 4"></path>
                                <path d="M14 17h4"></path>
                            </svg>
                            XLSX
                        </a>
                        <a href="{{ route('finance.report.laporan_arus_kas.export_pdf', request()->query()) }}" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke PDF">
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
                <form id="filterForm" method="GET" action="{{ route('finance.report.laporan_arus_kas') }}" class="filter-form-finance">
                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Tanggal Mulai</label>
                        <input type="date" id="startDateInput" name="start_date" class="filter-input-pegawai" value="{{ request('start_date') }}">
                    </div>

                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Tanggal Akhir</label>
                        <input type="date" id="endDateInput" name="end_date" class="filter-input-pegawai" value="{{ request('end_date') }}">
                    </div>

                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Kategori Biaya</label>
                        <select id="kategoriInput" name="kategori_id" class="filter-input-pegawai">
                            <option value="">-- Semua Kategori --</option>
                            @foreach($kategori as $kat)
                                <option value="{{ $kat->kategori_id }}" {{ request('kategori_id') == $kat->kategori_id ? 'selected' : '' }}>
                                    {{ $kat->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Departemen</label>
                        <select id="departemenInput" name="departemen_id" class="filter-input-pegawai">
                            <option value="">-- Semua Departemen --</option>
                            @foreach($departemen as $dept)
                                <option value="{{ $dept->departemen_id }}" {{ request('departemen_id') == $dept->departemen_id ? 'selected' : '' }}>
                                    {{ $dept->nama_departemen }}
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
                        <a href="{{ route('finance.report.laporan_arus_kas') }}" class="btn-reset-pegawai" title="Reset Filter">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </div>
                </form>
            </div>

            <div id="tableContainer">
                @php $hasData = false; @endphp
                @foreach($activities as $key => $activity)
                    @if($activity['entries']->count() > 0)
                        @php $hasData = true; @endphp
                        <div class="coa-group-card">
                            <div class="coa-header">
                                <h3>{{ $activity['label'] }}</h3>
                                <div class="coa-summary-badge" style="background: {{ $activity['total'] >= 0 ? '#ecfdf5' : '#fef2f2' }}; color: {{ $activity['total'] >= 0 ? '#059669' : '#dc2626' }};">
                                    Net: Rp {{ number_format($activity['total'], 0, ',', '.') }}
                                </div>
                            </div>
                            
                            <div style="overflow-x: auto;">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 85px;">Tanggal</th>
                                            <th style="width: 140px;">No. Referensi</th>
                                            <th style="width: 100px;">Departemen</th>
                                            <th>Keterangan / Deskripsi</th>
                                            <th>Akun Kontra / Kategori</th>
                                            <th style="text-align: right; width: 110px;">Mutasi Kas</th>
                                            <th style="text-align: center; width: 70px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activity['entries'] as $entry)
                                            <tr>
                                                <td style="color: #64748b; font-size: 0.75rem;">{{ $entry['tanggal']->format('d/m/Y') }}</td>
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 6px;">
                                                        @if($entry['accurate_id'])
                                                            <div title="Synced to Accurate" style="width: 8px; height: 8px; border-radius: 50%; background: #059669; flex-shrink: 0;"></div>
                                                        @else
                                                            <div title="Not Synced / Local Only" style="width: 8px; height: 8px; border-radius: 50%; background: #94a3b8; flex-shrink: 0;"></div>
                                                        @endif
                                                        <a href="{{ route('finance.report.jurnal_umum', ['search' => $entry['nomor_ref']]) }}" style="font-weight: 700; color: #425d87; text-decoration: none; font-size: 0.8rem;" title="Lihat di Jurnal Umum">
                                                            {{ $entry['nomor_ref'] }}
                                                        </a>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge-dept">
                                                        {{ $entry['departemen'] }}
                                                    </span>
                                                </td>
                                                <td style="font-size: 0.8rem; color: #475569;">{{ Str::limit($entry['deskripsi'], 30) }}</td>
                                                <td style="color: #64748b; font-size: 0.8rem;">
                                                    <div style="font-weight: 600; color: #1e293b;">{{ $entry['counterpart_name'] }}</div>
                                                    @if($entry['kategori'] != '-' && $entry['kategori'] != $entry['counterpart_name'])
                                                        <div style="font-size: 0.7rem; color: #94a3b8;">Kat: {{ $entry['kategori'] }}</div>
                                                    @endif
                                                </td>
                                                <td style="text-align: right; font-weight: 700; font-size: 0.85rem; color: {{ $entry['flow'] >= 0 ? '#059669' : '#dc2626' }};">
                                                    {{ $entry['flow'] >= 0 ? '+' : '-' }} Rp {{ number_format(abs($entry['flow']), 0, ',', '.') }}
                                                </td>
                                                <td style="text-align: center;">
                                                    <div style="display: flex; justify-content: center;">
                                                        @if($entry['file_bukti'])
                                                            <button type="button" onclick="openProofModal('{{ route('proof.show', $entry['pengajuan_id']) }}', {{ str_ends_with(strtolower($entry['file_bukti']), '.pdf') ? 'true' : 'false' }})" class="btn-reset-pegawai" title="Lihat Bukti" style="width: 28px; height: 28px; background: #f1f5f9; color: #425d87; border: 1px solid #e2e8f0;">
                                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 12px; height: 12px;">
                                                                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                                                                </svg>
                                                            </button>
                                                        @else
                                                            -
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr style="background: #f8fafc; border-top: 2px solid #f1f5f9;">
                                            <td colspan="5" style="padding: 1rem 0.75rem; font-weight: 700; text-align: right; color: #64748b; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em;">Total {{ $activity['label'] }}</td>
                                            <td style="padding: 1rem 0.75rem; text-align: right; font-weight: 800; font-size: 0.95rem; color: {{ $activity['total'] >= 0 ? '#059669' : '#dc2626' }};">
                                                Rp {{ number_format($activity['total'], 0, ',', '.') }}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    @endif
                @endforeach

                <!-- Summary Section for PSAK Compliance -->
                <div class="coa-group-card" style="margin-top: 3rem; background: #f8fafc; border: 2px solid #e2e8f0; border-radius: 12px; padding: 1.5rem;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin-bottom: 1.5rem; border-bottom: 2px solid #cbd5e1; padding-bottom: 0.5rem;">
                        Ringkasan Posisi Kas & Bank
                    </h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px dashed #cbd5e1;">
                            <span style="font-weight: 500; color: #475569;">Kenaikan / (Penurunan) Bersih Kas</span>
                            <span style="font-weight: 700; color: {{ $netFlow >= 0 ? '#059669' : '#dc2626' }};">
                                {{ $netFlow >= 0 ? '+' : '' }} Rp {{ number_format($netFlow, 0, ',', '.') }}
                            </span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px dashed #cbd5e1;">
                            <span style="font-weight: 500; color: #475569;">Saldo Kas pada Awal Periode ({{ $startDate->format('d/m/Y') }})</span>
                            <span style="font-weight: 700; color: #1e293b;">
                                Rp {{ number_format($saldoAwalKas, 0, ',', '.') }}
                            </span>
                        </div>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; margin-top: 0.5rem; background: #f1f5f9; border-radius: 8px; padding: 1rem;">
                            <span style="font-weight: 800; color: #1e293b; font-size: 1.1rem;">Saldo Kas pada Akhir Periode ({{ $endDate->format('d/m/Y') }})</span>
                            <span style="font-weight: 800; color: #059669; font-size: 1.25rem;">
                                Rp {{ number_format($saldoAkhirKas, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                    <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 1rem; font-style: italic;">
                        * Saldo dihitung berdasarkan saldo awal dari Accurate yang disesuaikan dengan mutasi jurnal lokal.
                    </p>
                </div>

                @if(!$hasData)
                    <x-report-empty-state title="Tidak Ada Arus Kas" description="Tidak ada transaksi arus kas untuk periode yang dipilih" />
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
    (function() {
        const initArusKas = () => {
            const filterForm = document.getElementById('filterForm');
            if (filterForm) {
                const inputs = filterForm.querySelectorAll('select, input[type="date"]');
                
                inputs.forEach(input => {
                    input.addEventListener('change', () => {
                        filterForm.submit();
                    });
                });
            }
        };

        initArusKas();
        document.addEventListener('livewire:navigated', initArusKas);
    })();
    </script>
    @endpush
@endsection
