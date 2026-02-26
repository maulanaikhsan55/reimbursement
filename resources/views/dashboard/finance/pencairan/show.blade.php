@extends('layouts.app')

@section('title', 'Proses Pencairan - ' . $pengajuan->nomor_pengajuan)

@push('styles')
    <style>
        .info-card-pencairan {
            background: #fff;
            border-radius: 1.25rem;
            padding: 1.5rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        .pencairan-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .pencairan-icon {
            width: 48px;
            height: 48px;
            background: rgba(79, 70, 229, 0.1);
            color: #4f46e5;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pencairan-icon svg {
            width: 24px;
            height: 24px;
            stroke-width: 2.5;
        }
    </style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
    <x-page-header 
        title="{{ $pengajuan->status->value === 'dicairkan' ? 'Detail Riwayat Pencairan' : 'Proses Pencairan Dana' }}" 
        subtitle="{{ $pengajuan->status->value === 'dicairkan' ? 'Informasi lengkap pengajuan yang telah dicairkan' : 'Verifikasi dan proses pencairan untuk pengajuan #' . $pengajuan->nomor_pengajuan }}" 
        :showNotification="true" 
        :showProfile="true" 
    />

    <div class="dashboard-content detail-single-content">
        <!-- Main Info Section -->
        <section class="modern-section">
            <div class="section-header" style="margin-bottom: 1.25rem; padding-bottom: 0; border: none; display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                <h2 class="section-title">Informasi Pengajuan</h2>
                <a href="{{ $pengajuan->status->value === 'terkirim_accurate' ? route('finance.disbursement.index') : route('finance.disbursement.history') }}" class="btn-modern btn-modern-secondary btn-modern-sm">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; margin-right: 0.5rem;">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    {{ $pengajuan->status->value === 'terkirim_accurate' ? 'Kembali' : 'Kembali ke Riwayat' }}
                </a>
            </div>

            <div class="detail-grid">
                <!-- Row 1: Identitas Pengajuan -->
                <div class="detail-item">
                    <div class="detail-label">No. Pengajuan</div>
                    <div class="detail-value text-mono">{{ $pengajuan->nomor_pengajuan }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Tanggal Pengajuan</div>
                    <div class="detail-value">{{ $pengajuan->tanggal_pengajuan->format('d M Y') }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status Saat Ini</div>
                    <div class="detail-value">
                        <x-status-badge :status="$pengajuan->status" :transactionId="$pengajuan->accurate_transaction_id" />
                    </div>
                </div>

                <!-- Row 2: Identitas Staff -->
                <div class="detail-item">
                    <div class="detail-label">Nama Staff</div>
                    <div class="detail-value">{{ $pengajuan->user->name }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Departemen</div>
                    <div class="detail-value">{{ $pengajuan->departemen->nama_departemen }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Rekening / Bank</div>
                    <div class="detail-value">
                        <div class="info-stack">
                            <span class="info-main text-mono" style="font-size: 0.9rem;">{{ $pengajuan->user->nomor_rekening ?? '-' }}</span>
                            <span class="info-sub">{{ $pengajuan->user->nama_bank ?? '-' }} (a.n {{ $pengajuan->user->nama_rekening ?? $pengajuan->user->name }})</span>
                        </div>
                    </div>
                </div>

                <!-- Row 3: Detail Transaksi -->
                <div class="detail-item">
                    <div class="detail-label">Vendor</div>
                    <div class="detail-value">{{ $pengajuan->nama_vendor ?? '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Bukti Transaksi</div>
                    <div class="detail-value">
                        @if($pengajuan->file_bukti)
                            <button type="button" class="btn-modern btn-modern-secondary btn-modern-sm btn-proof-compact" onclick="openProofModal('{{ route('proof.show', $pengajuan) }}', {{ str_ends_with(strtolower($pengajuan->file_bukti), '.pdf') ? 'true' : 'false' }})">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                                Lihat Bukti
                            </button>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Total Pencairan</div>
                    <div class="detail-value text-lg text-primary" style="font-weight: 800;">{{ format_rupiah($pengajuan->nominal) }}</div>
                </div>

                <!-- Row 4: Deskripsi (Full Width) -->
                <div class="detail-item full-width">
                    <div class="detail-label">Deskripsi Keperluan</div>
                    <div class="detail-value description-box">{{ $pengajuan->deskripsi ?? '-' }}</div>
                </div>
            </div>
        </section>

        <!-- Account Info Section (COA & Kas/Bank used in Accurate) -->
        <section class="modern-section">
            <div class="section-header" style="margin-bottom: 1.25rem; padding-bottom: 0; border: none;">
                <h2 class="section-title">Informasi Akun Accurate</h2>
            </div>
            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Nomor COA</div>
                    <div class="detail-value text-mono">{{ $pengajuan->coa->kode_coa ?? '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Nama Akun</div>
                    <div class="detail-value">{{ $pengajuan->coa->nama_coa ?? '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Sumber Dana (Kas/Bank)</div>
                    <div class="detail-value">{{ $pengajuan->kasBank->nama_kas_bank ?? '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">ID Transaksi Accurate</div>
                    <div class="detail-value text-mono" style="color: #6366f1; font-weight: 700;">
                        {{ $pengajuan->accurate_transaction_id ? '#' . $pengajuan->accurate_transaction_id : '-' }}
                    </div>
                </div>
            </div>
        </section>

        <!-- AI Validation Results -->
        <section class="modern-section">
            <div class="section-header" style="margin-bottom: 1.75rem; padding-bottom: 0; border: none;">
                <h2 class="section-title" style="display: flex; align-items: center; gap: 0.75rem;">
                    <x-icon name="cpu" class="w-6 h-6 text-primary" style="color: #4f46e5;" />
                    Hasil Validasi AI
                </h2>
                <p class="text-muted small mb-0" style="margin-top: 0.5rem;">Detail hasil analisis otomatis dokumen</p>
            </div>
            
            <x-ai-validation-result :results="$pengajuan->validasiAi" :pengajuan="$pengajuan" />

            @if(!$pengajuan->validasiAi->isEmpty())
                <!-- Status Summary -->
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                    @php
                        $allPass = $pengajuan->validasiAi->every(fn($v) => $v->status->value === 'valid');
                        $hasFail = $pengajuan->validasiAi->some(fn($v) => $v->status->value === 'invalid');
                    @endphp
                    
                    @if($allPass)
                        <div class="alert alert-success d-flex align-items-center" style="background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; padding: 1rem; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            <div>
                                <strong style="display: block; font-size: 0.95rem;">Semua Validasi AI Lolos</strong>
                                <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Data pengajuan telah divalidasi oleh sistem dan dinyatakan sesuai.</p>
                            </div>
                        </div>
                    @elseif($hasFail)
                        <div class="alert alert-danger d-flex align-items-center" style="background-color: #fef2f2; color: #991b1b; border: 1px solid #fecdd3; padding: 1rem; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <div>
                                <strong style="display: block; font-size: 0.95rem;">Catatan: Ada Validasi Gagal</strong>
                                <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Terdapat ketidaksesuaian yang terdeteksi AI. Pastikan nominal transfer sesuai.</p>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning d-flex align-items-center" style="background-color: #fffbeb; color: #92400e; border: 1px solid #fde68a; padding: 1rem; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            <div>
                                <strong style="display: block; font-size: 0.95rem;">Perhatian: Ada Catatan AI</strong>
                                <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Beberapa poin validasi memerlukan tinjauan ulang sebelum pencairan.</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </section>

        <!-- Action Section -->
        <section class="modern-section">
            <div class="section-header" style="margin-bottom: 1.25rem; padding-bottom: 0; border: none;">
                <h2 class="section-title">{{ $pengajuan->status->value === 'terkirim_accurate' ? 'Konfirmasi Pencairan' : 'Status Pencairan' }}</h2>
            </div>

            @if ($pengajuan->status->value === 'terkirim_accurate')
                <form method="POST" action="{{ route('finance.disbursement.mark', $pengajuan->pengajuan_id) }}" class="accurate-form">
                    @csrf
                    <div class="form-grid" style="display: grid; grid-template-columns: 1fr; gap: 1.5rem; margin-bottom: 1.5rem; max-width: 400px;">
                        <div class="form-group">
                            <label class="form-label">Tanggal Pencairan <span class="required" style="color: #ef4444;">*</span></label>
                            <input type="date" name="tanggal_pencairan" 
                                   class="form-input"
                                   style="width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 0.75rem;"
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="form-actions" style="display: flex; justify-content: flex-end; align-items: center; margin-top: 2rem;">
                        <button type="button" class="btn-modern btn-modern-primary" style="padding: 0.75rem 2rem;" onclick="openConfirmModal(() => this.closest('form').submit(), 'Konfirmasi Pencairan', 'Pastikan dana sudah ditransfer ke rekening pegawai. Lanjutkan?')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; margin-right: 0.5rem;">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Tandai Sudah Dicairkan
                        </button>
                    </div>
                </form>
            @else
                <div class="success-state" style="text-align: center; padding: 3rem; background: #f0fdf4; border-radius: 1.5rem; border: 1px solid #bbf7d0; margin-bottom: 1.5rem;">
                    <div class="success-icon" style="font-size: 3.5rem; margin-bottom: 1rem; color: #22c55e;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width: 64px; height: 64px;">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <div class="success-title" style="font-size: 1.5rem; font-weight: 800; color: #166534; margin-bottom: 0.5rem;">Dana Sudah Dicairkan</div>
                    <p style="color: #15803d; font-weight: 500; margin-bottom: 0;">Dicairkan pada {{ $pengajuan->tanggal_pencairan?->format('d F Y') }}</p>
                </div>
            @endif
        </section>
        </div>
    </div>
</div>

<x-proof-modal />
@endsection
