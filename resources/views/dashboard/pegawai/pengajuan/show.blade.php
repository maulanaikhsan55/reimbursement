@extends('layouts.app')

@section('title', 'Detail Pengajuan')

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
    <x-page-header 
        title="Detail Pengajuan" 
        subtitle="Nomor: {{ $pengajuan->nomor_pengajuan }}" 
        :showNotification="true" 
        :showProfile="true" 
    />

    <div class="dashboard-content">
        <!-- Status Section -->
        <section class="modern-section">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Status Pengajuan</h2>
                </div>
                <a href="{{ route('pegawai.pengajuan.index') }}" class="link-back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Kembali
                </a>
            </div>

            <div class="timeline">
                <div class="timeline-item active">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Diajukan</h6>
                        <p class="text-muted small mb-0">{{ $pengajuan->created_at->format('d M Y, H:i') }}</p>
                    </div>
                </div>

                @if($pengajuan->status->value == 'menunggu_atasan' || $pengajuan->tanggal_disetujui_atasan || $pengajuan->status->value == 'ditolak_atasan')
                    <div class="timeline-item {{ $pengajuan->tanggal_disetujui_atasan || $pengajuan->status->value == 'ditolak_atasan' ? 'active' : '' }}">
                        <div class="timeline-dot {{ $pengajuan->status->value == 'ditolak_atasan' ? 'bg-danger' : '' }}"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Review Atasan</h6>
                            @if($pengajuan->tanggal_disetujui_atasan)
                                <p class="text-muted small mb-0">Disetujui - {{ $pengajuan->tanggal_disetujui_atasan->format('d M Y') }}</p>
                                <p class="text-muted small mb-0">{{ $pengajuan->approvedByAtasan->name ?? '-' }}</p>
                            @elseif($pengajuan->status->value == 'ditolak_atasan')
                                <p class="text-danger small mb-0">Ditolak</p>
                                @if($pengajuan->catatan_atasan)
                                    <p class="text-danger small fst-italic mt-1">"{{ $pengajuan->catatan_atasan }}"</p>
                                @endif
                            @else
                                <p class="text-muted small mb-0">Menunggu persetujuan...</p>
                            @endif
                        </div>
                    </div>
                @endif

                @if($pengajuan->status->value == 'menunggu_finance' || $pengajuan->tanggal_disetujui_finance || $pengajuan->status->value == 'ditolak_finance' || $pengajuan->status->value == 'terkirim_accurate' || $pengajuan->status->value == 'dicairkan')
                    <div class="timeline-item {{ $pengajuan->tanggal_disetujui_finance || $pengajuan->status->value == 'ditolak_finance' || $pengajuan->status->value == 'terkirim_accurate' || $pengajuan->status->value == 'dicairkan' ? 'active' : '' }}">
                        <div class="timeline-dot {{ $pengajuan->status->value == 'ditolak_finance' ? 'bg-danger' : '' }}"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Review Finance</h6>
                            @if($pengajuan->status->value == 'terkirim_accurate' || $pengajuan->status->value == 'dicairkan')
                                <p class="text-muted small mb-0">Disetujui Finance</p>
                            @elseif($pengajuan->status->value == 'ditolak_finance')
                                <p class="text-danger small mb-0">Ditolak</p>
                                @if($pengajuan->catatan_finance)
                                    <p class="text-danger small fst-italic mt-1">"{{ $pengajuan->catatan_finance }}"</p>
                                @endif
                            @else
                                <p class="text-muted small mb-0">Menunggu proses...</p>
                            @endif
                        </div>
                    </div>
                @endif

                @if($pengajuan->status->value == 'dicairkan')
                    <div class="timeline-item active">
                        <div class="timeline-dot bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Dicairkan</h6>
                            <p class="text-muted small mb-0">{{ $pengajuan->tanggal_pencairan ? $pengajuan->tanggal_pencairan->format('d M Y') : '-' }}</p>
                        </div>
                    </div>
                @endif
            </div>

            @if($pengajuan->status->value == 'ditolak_atasan' || $pengajuan->status->value == 'ditolak_finance')
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e8ecf1;">
                    <a href="{{ route('pegawai.pengajuan.create') }}" class="btn-modern btn-modern-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; margin-right: 8px;">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Pengajuan Baru
                    </a>
                </div>
            @elseif($pengajuan->status->value == 'menunggu_atasan')
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e8ecf1;">
                    <form action="{{ route('pegawai.pengajuan.destroy', $pengajuan->pengajuan_id) }}" method="POST" style="display: inline-block;">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn-modern btn-modern-danger" onclick="openConfirmModal(() => this.closest('form').submit(), 'Batalkan Pengajuan', 'Apakah Anda yakin ingin membatalkan pengajuan ini? Data akan dihapus.')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; margin-right: 8px;">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                            Batalkan Pengajuan
                        </button>
                    </form>
                    <p class="text-muted small mt-2">Anda dapat membatalkan pengajuan selama belum disetujui oleh atasan.</p>
                </div>
            @endif
        </section>

        <x-budget-indicator 
            :status="$budgetStatus" 
            :departmentName="$pengajuan->departemen->nama_departemen ?? 'Departemen'" 
        />

        <!-- Detail Info Section -->
        <section class="modern-section">
            <div class="section-header" style="margin-bottom: 1.25rem; padding-bottom: 0; border: none;">
                <h2 class="section-title">Informasi Pengajuan</h2>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Nama Vendor</div>
                    <div class="detail-value">{{ $pengajuan->nama_vendor }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Tanggal Transaksi</div>
                    <div class="detail-value">{{ $pengajuan->tanggal_transaksi->format('d F Y') }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Kategori Biaya</div>
                    <div class="detail-value">{{ $pengajuan->kategori->nama_kategori ?? '-' }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Nominal</div>
                    <div class="detail-value text-lg text-primary">{{ format_rupiah($pengajuan->nominal) }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status Saat Ini</div>
                    <div class="detail-value">
                        <x-status-badge :status="$pengajuan->status" :transactionId="$pengajuan->accurate_transaction_id" />
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">No. Pengajuan</div>
                    <div class="detail-value text-mono">{{ $pengajuan->nomor_pengajuan }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Tanggal Pengajuan</div>
                    <div class="detail-value">{{ $pengajuan->created_at->format('d M Y') }}</div>
                </div>

                @if($pengajuan->accurate_transaction_id)
                <div class="detail-item">
                    <div class="detail-label">ID Transaksi Accurate</div>
                    <div class="detail-value text-mono" style="color: #6366f1; font-weight: 700;">
                        #{{ $pengajuan->accurate_transaction_id }}
                    </div>
                </div>
                @endif

                <div class="detail-item">
                    <div class="detail-label">Bukti Transaksi</div>
                    <div class="detail-value">
                        @if($pengajuan->file_bukti)
                            <button type="button" class="btn-modern btn-modern-secondary btn-modern-sm" onclick="openProofModal('{{ route('proof.show', $pengajuan) }}', {{ str_ends_with(strtolower($pengajuan->file_bukti), '.pdf') ? 'true' : 'false' }})">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 0.5rem;">
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

                <div class="detail-item full-width">
                    <div class="detail-label">Deskripsi</div>
                    <div class="detail-value description-box">{{ $pengajuan->deskripsi }}</div>
                </div>

                @if($pengajuan->catatan_pegawai)
                <div class="detail-item full-width">
                    <div class="detail-label">Catatan Tambahan</div>
                    <div class="detail-value description-box">{{ $pengajuan->catatan_pegawai }}</div>
                </div>
                @endif
            </div>
        </section>



        <!-- AI Validation Result Section -->
        <section class="modern-section">
            <div class="section-header" style="margin-bottom: 1.75rem; padding-bottom: 0; border: none;">
                <h2 class="section-title" style="display: flex; align-items: center; gap: 0.75rem;">
                    <x-icon name="cpu" class="w-6 h-6 text-primary" style="color: #4f46e5;" />
                    Hasil Validasi AI
                </h2>
                <p class="text-muted small mb-0" style="margin-top: 0.5rem;">Sistem AI telah memvalidasi dokumen Anda secara otomatis</p>
            </div>
            
            <x-ai-validation-result :results="$pengajuan->validasiAi" />

            @if(!$pengajuan->validasiAi->isEmpty() && $pengajuan->status->value !== 'validasi_ai')
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
                                <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Pengajuan Anda telah diverifikasi otomatis oleh sistem AI dengan hasil sempurna.</p>
                            </div>
                        </div>
                    @elseif($hasFail)
                        <div class="alert alert-danger d-flex align-items-center" style="background-color: #fef2f2; color: #991b1b; border: 1px solid #fecdd3; padding: 1rem; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <div>
                                <strong style="display: block; font-size: 0.95rem;">Catatan Validasi AI</strong>
                                <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Sistem AI mendeteksi beberapa ketidaksesuaian. Hal ini mungkin akan ditinjau lebih lanjut oleh atasan/finance.</p>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning d-flex align-items-center" style="background-color: #fffbeb; color: #92400e; border: 1px solid #fde68a; padding: 1rem; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            <div>
                                <strong style="display: block; font-size: 0.95rem;">Perhatian AI</strong>
                                <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Beberapa detail dalam dokumen Anda mendapatkan catatan dari sistem AI.</p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            @if($pengajuan->status->value === 'validasi_ai')
                <div style="margin-top: 1.5rem; padding: 1.5rem; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem;">
                    @if($pengajuan->status_validasi->value === 'invalid')
                        <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div style="color: #dc2626; margin-top: 0.25rem;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                </svg>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 0.5rem 0; color: #7f1d1d; font-size: 1rem; font-weight: 600;">Validasi AI Tidak Lolos</h4>
                                <p style="margin: 0 0 1rem 0; color: #991b1b; font-size: 0.875rem; line-height: 1.5;">
                                    Pengajuan Anda tidak memenuhi kriteria validasi otomatis. Pastikan nominal dan tanggal sesuai dengan bukti pembayaran.
                                </p>
                                
                                <form action="{{ route('pegawai.pengajuan.destroy', $pengajuan->pengajuan_id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn-modern btn-modern-danger btn-modern-sm" onclick="openConfirmModal(() => this.closest('form').submit(), 'Batalkan & Hapus', 'Yakin ingin membatalkan dan menghapus pengajuan ini?')" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                        <span>Batalkan & Hapus</span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                         {{-- Case: Stuck in Processing or Valid but not forwarded --}}
                         {{-- Auto-forward is handled in controller, but if stuck, show message --}}
                         <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div style="color: #3b82f6; margin-top: 0.25rem;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 0.5rem 0; color: #1e3a8a; font-size: 1rem; font-weight: 600;">Sedang Diproses</h4>
                                <p style="margin: 0 0 1rem 0; color: #1e40af; font-size: 0.875rem; line-height: 1.5;">
                                    Pengajuan Anda sedang dalam antrian proses sistem. Silakan refresh halaman beberapa saat lagi.
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </section>

    </div>
    </div>
</div>

<x-proof-modal />

@push('scripts')
@endpush
@endsection