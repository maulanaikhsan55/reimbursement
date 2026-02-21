@extends('layouts.app')

@section('title', 'Review Pengajuan - ' . $pengajuan->nomor_pengajuan)

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
    <x-page-header 
        title="Review Pengajuan" 
        subtitle="Nomor: {{ $pengajuan->nomor_pengajuan }}" 
        :showNotification="true" 
        :showProfile="true" 
    />

    <div class="dashboard-content detail-single-content">
        <!-- Employee Card -->
            <section class="modern-section" style="padding: 1.5rem;">
                <div class="employee-card-inline">
                    <div class="employee-avatar">{{ strtoupper(substr($pengajuan->user->name, 0, 1)) }}</div>
                    <div class="employee-info">
                        <h4>{{ $pengajuan->user->name }}</h4>
                        <p>{{ $pengajuan->departemen->nama_departemen ?? '-' }} • {{ ucfirst($pengajuan->user->role) }} • {{ $pengajuan->user->email }}</p>
                    </div>
                </div>
            </section>

            <!-- Status Timeline Section (Adapted from Pegawai View) -->
            <section class="modern-section">
                <div class="section-header" style="margin-bottom: 1.25rem; padding-bottom: 0; border: none;">
                    <h2 class="section-title">Status Pengajuan</h2>
                    <a href="{{ route('atasan.approval.index') }}" class="link-back">
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

                    @if($pengajuan->status->value == 'menunggu_atasan' || $pengajuan->tanggal_disetujui_atasan || $pengajuan->status->value == 'ditolak_atasan' || $pengajuan->isBypassed())
                        <div class="timeline-item {{ $pengajuan->tanggal_disetujui_atasan || $pengajuan->status->value == 'ditolak_atasan' || $pengajuan->isBypassed() ? 'active' : '' }}">
                            <div class="timeline-dot {{ $pengajuan->status->value == 'ditolak_atasan' ? 'bg-danger' : '' }}"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Review Atasan</h6>
                                @if($pengajuan->isBypassed())
                                    <p class="text-success small mb-0">Auto-Approved (Bypass)</p>
                                @elseif($pengajuan->tanggal_disetujui_atasan)
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
                                <p class="text-muted small mb-0">
                                    @if($pengajuan->status->value == 'menunggu_finance') 
                                        Menunggu proses finance...
                                    @elseif($pengajuan->status->value == 'ditolak_finance') 
                                        <span class="text-danger">Ditolak Finance</span>
                                        @if($pengajuan->catatan_finance)
                                            <p class="text-danger small fst-italic mt-1">"{{ $pengajuan->catatan_finance }}"</p>
                                        @endif
                                    @else 
                                        Disetujui Finance
                                        @if($pengajuan->tanggal_disetujui_finance)
                                            - {{ $pengajuan->tanggal_disetujui_finance->format('d M Y') }}
                                        @endif
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <!-- Detail Info Section -->
            <section class="modern-section">
                <div class="section-header" style="margin-bottom: 1.25rem; padding-bottom: 0; border: none;">
                    <h2 class="section-title">Informasi Pengajuan</h2>
                </div>

                <div class="detail-grid">
                    <div class="detail-item">
                        <div class="detail-label">Nama Vendor</div>
                        <div class="detail-value">{{ $pengajuan->nama_vendor ?? '-' }}</div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Tanggal Transaksi</div>
                        <div class="detail-value">{{ $pengajuan->tanggal_transaksi?->format('d F Y') ?? '-' }}</div>
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
                        <div class="detail-label">Tanggal Diajukan</div>
                        <div class="detail-value">{{ $pengajuan->created_at->format('d M Y') }}</div>
                    </div>

                    <div class="detail-item full-width">
                        <div class="detail-label">Deskripsi Pengajuan</div>
                        <div class="description-box">{{ $pengajuan->deskripsi }}</div>
                    </div>

                    @if($pengajuan->catatan_pegawai)
                    <div class="detail-item full-width">
                        <div class="detail-label">Catatan Pegawai</div>
                        <div class="description-box" style="background: #fffbeb; border-left-color: #f59e0b;">{{ $pengajuan->catatan_pegawai }}</div>
                    </div>
                    @endif

                    <!-- File Bukti -->
                    <div class="detail-item full-width">
                        <div class="detail-label">Bukti Transaksi</div>
                        @if($pengajuan->file_bukti)
                            <button type="button" class="btn-modern btn-modern-secondary btn-modern-sm" onclick="openProofModal('{{ route('proof.show', $pengajuan) }}', {{ str_ends_with(strtolower($pengajuan->file_bukti), '.pdf') ? 'true' : 'false' }})">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 8px;">
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
            </section>

            <!-- Budget Status Card -->
            <x-budget-indicator 
                :status="$budgetData" 
                :departmentName="$pengajuan->departemen->nama_departemen ?? 'Departemen'" 
            />

            <!-- AI Validation Results -->
            <section class="modern-section">
                <div class="section-header" style="margin-bottom: 1.75rem; padding-bottom: 0; border: none;">
                    <h2 class="section-title" style="display: flex; align-items: center; gap: 0.75rem;">
                        <x-icon name="cpu" class="w-6 h-6 text-primary" style="color: #4f46e5;" />
                        Hasil Validasi AI
                    </h2>
                </div>
                <x-ai-validation-result :results="$pengajuan->validasiAi" />

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
                                    <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Data pengajuan sesuai dengan bukti lampiran dan kebijakan perusahaan.</p>
                                </div>
                            </div>
                        @elseif($hasFail)
                            <div class="alert alert-danger d-flex align-items-center" style="background-color: #fef2f2; color: #991b1b; border: 1px solid #fecdd3; padding: 1rem; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                                <div>
                                    <strong style="display: block; font-size: 0.95rem;">Peringatan: Ada Validasi Gagal</strong>
                                    <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Ditemukan ketidaksesuaian data. Mohon periksa kembali sebelum memberikan persetujuan.</p>
                                </div>
                            </div>
                        @else
                            <div class="alert alert-warning d-flex align-items-center" style="background-color: #fffbeb; color: #92400e; border: 1px solid #fde68a; padding: 1rem; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
                                <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                <div>
                                    <strong style="display: block; font-size: 0.95rem;">Perhatian: Ada Catatan AI</strong>
                                    <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Beberapa validasi memerlukan perhatian ekstra. Silakan tinjau detail di atas.</p>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </section>

            <!-- Approval Actions Card (Only if status is menunggu_atasan) -->
            @if($pengajuan->status->value == 'menunggu_atasan')
            <section class="modern-section" style="border-color: #3b82f6; box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.1);">
                <div class="section-header" style="margin-bottom: 1.5rem; padding-bottom: 0; border: none;">
                    <h2 class="section-title" style="color: #1e40af;">Keputusan Persetujuan</h2>
                </div>

                <div class="action-buttons-simple">
                    <!-- Reject Button -->
                    <button 
                        type="button" 
                        class="btn-modern btn-modern-danger" 
                        onclick="openRejectModal('{{ route('atasan.approval.reject', $pengajuan->pengajuan_id) }}', 'catatan_atasan')"
                        style="flex: 1; justify-content: center;"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; margin-right: 8px;">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                        Tolak
                    </button>

                    <!-- Approve Form -->
                    <form action="{{ route('atasan.approval.approve', $pengajuan->pengajuan_id) }}" method="POST" style="flex: 1;">
                        @csrf
                        <button type="button" class="btn-modern btn-modern-primary" style="width: 100%; justify-content: center;" onclick="openConfirmModal(() => this.closest('form').submit(), 'Setujui Pengajuan', 'Apakah Anda yakin ingin menyetujui pengajuan ini?')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; margin-right: 8px;">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            Setujui
                        </button>
                    </form>
                </div>
            </section>
            @endif
        </div>
    </div>
</div>

<x-proof-modal />
@endsection
