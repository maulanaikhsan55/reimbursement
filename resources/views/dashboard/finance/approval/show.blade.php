@extends('layouts.app')

@section('title', 'Detail Pengajuan - ' . $pengajuan->nomor_pengajuan)

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
    <x-page-header 
        title="Detail Pengajuan" 
        subtitle="Review dan kirim pengajuan ke Accurate" 
        :showNotification="true" 
        :showProfile="true" 
    />

    <div class="dashboard-content detail-single-content">
        <!-- Main Info Section -->
        <section class="modern-section">
            <div class="section-header" style="margin-bottom: 1rem; padding-bottom: 0; border: none;">
                <h2 class="section-title">Informasi Pengajuan</h2>
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
                    <div class="detail-value">
                        {{ $pengajuan->departemen->nama_departemen }}
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Kategori Biaya</div>
                    <div class="detail-value">{{ $pengajuan->kategori->nama_kategori ?? '-' }}</div>
                </div>

                <!-- Row 3: Detail Transaksi -->
                <div class="detail-item">
                    <div class="detail-label">Tipe Transaksi</div>
                    <div class="detail-value">
                        @switch($pengajuan->jenis_transaksi)
                            @case('marketplace')
                                <span class="badge" style="background: #dbeafe; color: #0284c7;">Marketplace</span>
                                @break
                            @case('transfer_direct')
                                <span class="badge" style="background: #d1fae5; color: #059669;">Transfer Langsung</span>
                                @break
                            @case('transport')
                                <span class="badge" style="background: #fef3c7; color: #d97706;">Ojek/Transport</span>
                                @break
                            @default
                                <span class="badge" style="background: #f3f4f6; color: #6b7280;">Lainnya</span>
                        @endswitch
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Vendor</div>
                    <div class="detail-value">{{ $pengajuan->nama_vendor ?? '-' }}</div>
                </div>
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
                <div class="detail-item">
                    <div class="detail-label">Total Nominal</div>
                    <div class="detail-value text-lg text-primary">{{ format_rupiah($pengajuan->nominal) }}</div>
                </div>

                <!-- Row 4: Deskripsi (Full Width) -->
                <div class="detail-item full-width">
                    <div class="detail-label">Deskripsi Keperluan</div>
                    <div class="detail-value description-box">{{ $pengajuan->deskripsi ?? '-' }}</div>
                </div>
            </div>
        </section>

        <!-- AI Validation Results -->
        <section class="modern-section">
            <div class="section-header" style="margin-bottom: 1rem; padding-bottom: 0; border: none;">
                <h2 class="section-title" style="display: flex; align-items: center; gap: 0.75rem;">
                    <x-icon name="cpu" class="w-6 h-6 text-primary" style="color: #4f46e5;" />
                    Hasil Validasi AI
                </h2>
                <p class="text-muted small mb-0" style="margin-top: 0.25rem;">Detail hasil analisis otomatis dokumen</p>
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
                                <strong style="display: block; font-size: 0.95rem;">Semua Validasi Lolos</strong>
                                <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Data pengajuan sesuai dengan bukti lampiran dan kebijakan.</p>
                            </div>
                        </div>
                    @elseif($hasFail)
                        <div class="alert alert-danger d-flex align-items-center" style="background-color: #fef2f2; color: #991b1b; border: 1px solid #fecdd3; padding: 1rem; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <div>
                                <strong style="display: block; font-size: 0.95rem;">Ada Validasi yang Gagal</strong>
                                <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Harap review dengan teliti sebelum mengirim ke Accurate.</p>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning d-flex align-items-center" style="background-color: #fffbeb; color: #92400e; border: 1px solid #fde68a; padding: 1rem; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            <div>
                                <strong style="display: block; font-size: 0.95rem;">Ada Warning</strong>
                                <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Beberapa validasi memerlukan perhatian. Harap review sebelum lanjut.</p>
                            </div>
                        </div>
                    @endif
                </div>

            @endif
        </section>

        <x-budget-indicator 
            :status="$budgetStatus" 
            :departmentName="$pengajuan->departemen->nama_departemen ?? 'Departemen'" 
        />

        <!-- Send to Accurate Form -->
        @if($pengajuan->status->value === 'menunggu_finance')
        <section class="modern-section" style="padding: 1.25rem 1.5rem;">
            <div class="section-header" style="margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f1f5f9;">
                <h2 class="section-title">Kirim ke Accurate</h2>
            </div>

            <form action="{{ route('finance.approval.send-to-accurate', $pengajuan) }}" method="POST" class="accurate-form" style="gap: 1rem;">
                @csrf
                
                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 0.75rem;">
                    <div class="form-group" style="gap: 0.4rem;">
                        <label for="coa_id" class="form-label" style="margin-bottom: 0; display: flex; align-items: center; gap: 0.5rem; height: 20px;">
                            Pilih COA <span class="required">*</span>
                            @if($pengajuan->coa_id)
                                <span class="badge" style="background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; font-size: 0.65rem; padding: 2px 8px; border-radius: 0.375rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.25rem;">
                                    <svg viewBox="0 0 24 24" fill="currentColor" style="width: 12px; height: 12px;">
                                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                                    </svg>
                                    Default from {{ $pengajuan->kategori->nama_kategori ?? 'Kategori' }}
                                </span>
                            @endif
                        </label>
                        <select name="coa_id" id="coa_id" class="form-select" style="padding: 0.6rem 0.875rem; border-radius: 0.75rem; border-color: #e2e8f0;" required>
                            <option value="">-- Pilih COA --</option>
                            @foreach($coas as $coa)
                                <option value="{{ $coa->coa_id }}" {{ old('coa_id', $pengajuan->coa_id) == $coa->coa_id ? 'selected' : '' }} data-default="{{ $pengajuan->kategori && $pengajuan->kategori->default_coa_id == $coa->coa_id ? 'true' : 'false' }}">
                                    {{ $coa->kode_coa }} - {{ $coa->nama_coa }}
                                    @if($pengajuan->kategori && $pengajuan->kategori->default_coa_id == $coa->coa_id)
                                        (Default - {{ $pengajuan->kategori->nama_kategori }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <p style="font-size: 0.75rem; color: #6b7280; margin-top: 0.4rem; margin-bottom: 0;">
                            Rekomendasi COA dari kategori <strong>{{ $pengajuan->kategori->nama_kategori ?? '-' }}</strong>. Anda bisa mengubahnya jika diperlukan.
                            @if($coaPrediction)
                                <br />
                                <span style="color: #059669; font-weight: 500;">💡 Saran: {{ $coaPrediction['coa_code'] }} - {{ $coaPrediction['coa_name'] }} ({{ $coaPrediction['confidence'] }} confidence)</span>
                                <br />
                                <span style="color: #6b7280; font-size: 0.7rem;">{{ $coaPrediction['reason'] }}</span>
                            @endif
                        </p>
                        @error('coa_id')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group" style="gap: 0.4rem;">
                        <label for="kas_bank_id" class="form-label" style="margin-bottom: 0; display: flex; align-items: center; justify-content: space-between; height: 20px;">
                            <span>Pilih Kas/Bank <span class="required">*</span></span>
                            <span id="balance-info" style="font-size: 0.7rem; font-weight: 700; display: none;">
                                Saldo: <span id="balance-value">Rp 0</span>
                            </span>
                        </label>
                        <select name="kas_bank_id" id="kas_bank_id" class="form-select" style="padding: 0.6rem 0.875rem; border-radius: 0.75rem; border-color: #e2e8f0;" required onchange="checkRealTimeBalance(this.value)">
                            <option value="">-- Pilih Kas/Bank --</option>
                            @foreach($kasBanks as $kas)
                                <option value="{{ $kas->kas_bank_id }}" {{ old('kas_bank_id', $pengajuan->kas_bank_id) == $kas->kas_bank_id ? 'selected' : '' }}>
                                    {{ $kas->kode_kas_bank }} - {{ $kas->nama_kas_bank }}
                                </option>
                            @endforeach
                        </select>
                        <div id="balance-warning" style="display: none; color: #dc2626; font-size: 0.7rem; margin-top: 0.25rem; font-weight: 600;">
                            <x-icon name="alert-triangle" class="w-3 h-3" style="display: inline; vertical-align: middle; margin-right: 0.25rem;" />
                            Saldo di Accurate tidak mencukupi untuk pengajuan ini!
                        </div>
                        @error('kas_bank_id')
                            <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group" style="gap: 0.4rem;">
                    <label for="catatan_finance" class="form-label" style="margin-bottom: 0;">Catatan (Opsional)</label>
                    <textarea name="catatan_finance" id="catatan_finance" rows="2" class="form-textarea" style="min-height: 70px; padding: 0.75rem 1rem; border-radius: 0.75rem; border-color: #e2e8f0;" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                </div>

                <div class="form-actions" style="padding-top: 0.75rem; margin-top: 0.75rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                    <a href="{{ route('finance.approval.index') }}" class="btn-modern btn-modern-secondary" style="padding: 0.5rem 1.25rem; border-radius: 0.75rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 0.5rem;">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        Kembali
                    </a>
                    <div class="action-group" style="display: flex; gap: 0.75rem;">
                        <button type="button" class="btn-modern btn-modern-danger" style="padding: 0.5rem 1.25rem; border-radius: 0.75rem;" onclick="openRejectModal('{{ route('finance.approval.reject', $pengajuan) }}', 'catatan_finance')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 0.5rem;">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                            Tolak
                        </button>
                        <button type="button" class="btn-modern btn-modern-primary" style="padding: 0.5rem 1.5rem; border-radius: 0.75rem;" onclick="validateAndSend()">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 0.5rem;">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                            Kirim ke Accurate
                        </button>
                    </div>
                </div>
            </form>
        </section>
        @else
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
                    <div class="detail-value text-mono" style="color: #6366f1; font-weight: 600;">{{ $pengajuan->accurate_transaction_id ?? '-' }}</div>
                </div>
            </div>
            <div class="form-actions" style="margin-top: 1.5rem; border-top: 1px solid #f1f5f9; padding-top: 1rem;">
                <a href="{{ url()->previous() }}" class="btn-modern btn-modern-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 0.5rem;">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Kembali
                </a>
            </div>
        </section>
        @endif
    </div>
    </div>
</div>

<x-proof-modal />

@push('styles')
<style>
    /* Modern Section */
    .modern-section {
        background: white;
        border-radius: 1.75rem;
        padding: 2rem;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
        border: 1px solid #e5eaf2;
        overflow: hidden;
    }

    .dashboard-content {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    /* Compact Header - Removed */


    .btn-sm {
        padding: 0.4rem 0.8rem;
        font-size: 0.8rem;
    }

    /* Detail Grid Modern */
    .detail-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        padding: 0.5rem 0;
    }

    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .detail-item.full-width {
        grid-column: 1 / -1;
        margin-top: 1rem;
        padding-top: 1.5rem;
        border-top: 1px solid #f1f5f9;
    }

    .detail-label {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-value {
        font-size: 0.95rem;
        color: #1e293b;
        font-weight: 500;
        line-height: 1.5;
    }

    .text-mono {
        font-family: 'Monaco', 'Consolas', monospace;
        color: #425d87;
        font-weight: 600;
        letter-spacing: 0.5px;
    }

    .text-lg {
        font-size: 1.25rem;
        font-weight: 700;
    }

    .text-primary {
        color: #425d87;
    }

    .description-box {
        background: #f8fafc;
        padding: 1rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        color: #334155;
        line-height: 1.6;
    }

    .link-proof {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: #425d87;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.4rem 0.8rem;
        background: #f1f5f9;
        border-radius: 0.5rem;
        transition: all 0.2s;
        border: 1px solid #e2e8f0;
    }

    .link-proof:hover {
        background: #e2e8f0;
        color: #1e293b;
        transform: translateY(-1px);
    }

    .link-proof svg {
        width: 16px;
        height: 16px;
    }

    .badge-status {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    @media (max-width: 768px) {
        .detail-grid {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.75rem;
        gap: 1rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e8ecf1;
    }

    .section-header div {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .section-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #2c394e;
        margin: 0;
    }

    .section-subtitle {
        font-size: clamp(0.75rem, 1.5vw, 0.85rem);
        color: #2c3e50;
        font-weight: 500;
        margin: 0;
        line-height: 1.3;
    }

    /* Status Badge in Header - Removed */


    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .info-item {
        padding: 1.25rem;
        background: #f9fafb;
        border-radius: 1rem;
        border: 1px solid #e8ecf1;
    }

    .info-item.highlight {
        background: rgba(66, 93, 135, 0.06);
        border-color: #5575a2;
    }

    .info-label {
        font-size: 0.75rem;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .info-value {
        font-size: 0.95rem;
        color: #2c394e;
        font-weight: 600;
    }

    .info-value.total {
        font-size: 1.25rem;
        color: #5575a2;
    }

    /* Items List */
    .items-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .item-card {
        padding: 1.5rem;
        background: #f9fafb;
        border-radius: 1.25rem;
        border: 1px solid #e8ecf1;
    }

    .item-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .item-title {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .item-category {
        font-size: 0.75rem;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .item-vendor {
        font-size: 1rem;
        color: #2c394e;
        font-weight: 700;
    }

    .item-amount {
        font-size: 1.1rem;
        color: #5575a2;
        font-weight: 700;
        white-space: nowrap;
    }

    .item-description {
        font-size: 0.875rem;
        color: #4b5563;
        line-height: 1.6;
        margin-bottom: 1rem;
    }

    .item-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e8ecf1;
    }

    .meta-date {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: #6b7280;
    }

    .meta-date svg {
        width: 16px;
        height: 16px;
    }

    .btn-link {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: #5575a2;
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s;
    }

    .btn-link:hover {
        color: #3d5885;
    }

    .btn-link svg {
        width: 16px;
        height: 16px;
    }

    /* Validation Results */
    .validation-results {
        margin-top: 1.25rem;
        padding-top: 1.25rem;
        border-top: 1px solid #e8ecf1;
    }

    .validation-title {
        font-size: 0.75rem;
        color: #6b7280;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1rem;
    }

    .validation-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }

    .validation-item {
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        border: 1px solid;
    }

    .validation-item.status-valid {
        background: rgba(34, 197, 94, 0.08);
        border-color: rgba(34, 197, 94, 0.3);
    }

    .validation-item.status-invalid {
        background: rgba(239, 68, 68, 0.08);
        border-color: rgba(239, 68, 68, 0.3);
    }

    .validation-item.status-warning {
        background: rgba(251, 146, 60, 0.08);
        border-color: rgba(251, 146, 60, 0.3);
    }

    .validation-label {
        font-size: 0.7rem;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 0.25rem;
    }

    .validation-status {
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-valid .validation-status {
        color: #16a34a;
    }

    .status-invalid .validation-status {
        color: #dc2626;
    }

    .status-warning .validation-status {
        color: #ea580c;
    }

    /* Form Styles */
    .accurate-form {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-label {
        font-size: 0.875rem;
        color: #2c394e;
        font-weight: 600;
    }

    .required {
        color: #ef4444;
    }

    .form-select,
    .form-textarea {
        padding: 0.75rem 1rem;
        border: 1px solid #d0d9e7;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: #2c394e;
        background: white;
        transition: all 0.2s;
    }

    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #425d87;
        box-shadow: 0 0 0 3px rgba(66, 93, 135, 0.1);
    }

    .form-textarea {
        resize: vertical;
        min-height: 100px;
        font-family: inherit;
    }

    .form-error {
        font-size: 0.75rem;
        color: #ef4444;
        font-weight: 500;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding-top: 2rem;
        border-top: 1px solid #e8ecf1;
        margin-top: 2rem;
    }

    .action-group {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .btn-primary,
    .btn-secondary,
    .btn-danger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
        line-height: 1.5;
    }

    .btn-primary svg,
    .btn-secondary svg,
    .btn-danger svg {
        width: 18px;
        height: 18px;
    }

    .btn-primary {
        background: #425d87;
        color: white;
    }

    .btn-primary:hover {
        background: #314464;
        box-shadow: 0 4px 12px rgba(66, 93, 135, 0.25);
        transform: translateY(-1px);
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid #d0d9e7;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
        border-color: #cbd5e1;
    }

    .btn-danger {
        background: #fee2e2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .btn-danger:hover {
        background: #fecaca;
        color: #b91c1c;
        border-color: #fca5a5;
    }

    @media (max-width: 768px) {
        .dashboard-wrapper {
            padding: 1rem;
        }

        .modern-section {
            padding: 1.5rem;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .item-header {
            flex-direction: column;
        }

        .item-amount {
            align-self: flex-start;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .btn-primary,
        .btn-secondary {
            width: 100%;
            justify-content: center;
        }
    }
    /* Data Table */
    .data-table-wrapper {
        overflow-x: auto;
        border-radius: 1.25rem;
        background: white;
        margin-top: 1rem;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        background: white;
    }

    .data-table thead {
        background: #f3f4f6;
        border-bottom: 2px solid #e5eaf2;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .data-table th {
        padding: 1rem 0.875rem;
        text-align: left;
        font-size: 0.75rem;
        font-weight: 700;
        color: #4b5563;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        vertical-align: middle;
    }

    .data-table tbody tr {
        border-bottom: 1px solid #e5eaf2;
        transition: background-color 0.15s ease;
    }

    .data-table tbody tr:hover {
        background: #fafbfc;
    }

    .data-table td {
        padding: 1rem 0.875rem;
        font-size: 0.875rem;
        color: #2c394e;
        word-break: break-word;
        overflow-wrap: break-word;
        white-space: normal;
        vertical-align: middle;
        line-height: 1.4;
    }

    .text-bold {
        font-weight: 600;
        color: #2c394e;
    }

    /* Stepper - Removed */


    /* Status Badges - Removed */


    @media print {
        .sidebar, header, .btn-secondary, .btn-primary, .btn-danger, .form-actions, .alert, .page-header-actions, .modal-overlay {
            display: none !important;
        }
        .dashboard-wrapper {
            padding: 0;
            background: white;
            min-height: auto;
        }
        .modern-section {
            box-shadow: none;
            border: 1px solid #eee;
            padding: 1rem;
            margin-bottom: 1rem;
            break-inside: avoid;
        }
        .dashboard-content {
            gap: 1rem;
            width: 100%;
        }
        body {
            background: white;
            font-size: 12pt;
        }
        .stepper-wrapper {
            display: none;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    const PENGELUARAN_TOTAL = {{ $pengajuan->nominal }};

    async function checkRealTimeBalance(kasBankId) {
        const infoEl = document.getElementById('balance-info');
        const valueEl = document.getElementById('balance-value');
        const warningEl = document.getElementById('balance-warning');
        
        if (!kasBankId) {
            infoEl.style.display = 'none';
            warningEl.style.display = 'none';
            return;
        }

        // Show loading state
        infoEl.style.display = 'inline-flex';
        valueEl.innerHTML = '<span style="color: #94a3b8;">loading...</span>';
        warningEl.style.display = 'none';

        try {
            const response = await fetch(`/finance/masterdata/kas_bank/${kasBankId}/balance`);
            const result = await response.json();

            if (result.success) {
                const balance = parseFloat(result.balance);
                const formatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(balance);
                
                valueEl.innerText = formatted;
                
                if (balance < PENGELUARAN_TOTAL) {
                    valueEl.style.color = '#dc2626';
                    warningEl.style.display = 'block';
                } else {
                    valueEl.style.color = '#059669';
                    warningEl.style.display = 'none';
                }
            } else {
                valueEl.innerHTML = '<span style="color: #dc2626;">Error!</span>';
            }
        } catch (error) {
            console.error('Balance check failed:', error);
            valueEl.innerHTML = '<span style="color: #dc2626;">Offline</span>';
        }
    }

    // Run on load if already selected
    document.addEventListener('DOMContentLoaded', () => {
        const initialVal = document.getElementById('kas_bank_id').value;
        if (initialVal) checkRealTimeBalance(initialVal);
    });

    function validateAndSend() {
        const form = document.querySelector('.accurate-form');
        if (!form.reportValidity()) return;

        openConfirmModal(
            () => form.submit(),
            'Kirim ke Accurate',
            'Pastikan data COA dan Kas/Bank sudah benar. Kirim jurnal ke Accurate Online sekarang?'
        );
    }

    function openProofModal(fileUrl, isPdf = false) {
        const modal = document.getElementById('proofModal');
        const modalBody = document.getElementById('proofModalBody');
        
        // Clear previous content and show loader
        modalBody.innerHTML = '<div class="clip-loader" style="border-color: #3b82f6; border-bottom-color: transparent; margin: 2rem auto;"></div>';
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        if (isPdf) {
            modalBody.innerHTML = `<iframe src="${fileUrl}" style="width: 100%; height: 600px; border: none; border-radius: 0.5rem;" title="Bukti Transaksi"></iframe>`;
        } else {
            const img = new Image();
            img.onload = function() {
                modalBody.innerHTML = `<img src="${fileUrl}" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 0.5rem;" alt="Bukti Transaksi">`;
            };
            img.onerror = function() {
                modalBody.innerHTML = '<div style="padding: 2rem; text-align: center;"><p style="color: #dc2626;">Gagal memuat gambar bukti.</p></div>';
            };
            img.src = fileUrl;
        }
    }

    function closeProofModal() {
        const modal = document.getElementById('proofModal');
        const modalBody = document.getElementById('proofModalBody');
        modal.style.display = 'none';
        modalBody.innerHTML = '';
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeProofModal();
        }
    });
</script>
@endpush

@endsection
