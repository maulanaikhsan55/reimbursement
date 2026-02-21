@extends('layouts.app')

@section('title', 'Buat Pengajuan Baru')

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
    <x-page-header 
        title="Buat Pengajuan Baru" 
        subtitle="Isi formulir untuk mengajukan reimbursement" 
        :showNotification="true" 
        :showProfile="true" 
    />

    <div class="dashboard-content">
        <section class="modern-section">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Formulir Pengajuan</h2>
                </div>
                <a href="{{ route('atasan.pengajuan.index') }}" class="link-back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Kembali
                </a>
            </div>

            <div class="create-layout-grid">
                <aside class="create-side-panel">
                    <x-budget-indicator 
                        :status="$budgetStatus" 
                        :departmentName="Auth::user()->departemen->nama_departemen" 
                    />

                    <details class="compact-details info-details" open>
                        <summary class="info-summary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; color: #4f46e5; flex-shrink: 0;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="16" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12.01" y2="8"></line>
                            </svg>
                            <span>Ketentuan Pengajuan</span>
                        </summary>
                        <div class="info-content">
                            <ul class="guidelines-list">
                                <li class="guideline-item">
                                    <x-icon name="check-circle" class="w-4 h-4" style="color: #4f46e5; flex-shrink: 0; margin-top: 0.15rem;" />
                                    <div><strong class="guideline-title">Batas 15 Hari</strong><span class="guideline-desc">Dari tanggal transaksi</span></div>
                                </li>
                                <li class="guideline-item">
                                    <x-icon name="check-circle" class="w-4 h-4" style="color: #4f46e5; flex-shrink: 0; margin-top: 0.15rem;" />
                                    <div><strong class="guideline-title">Dokumen Jelas</strong><span class="guideline-desc">Tidak buram/terpotong</span></div>
                                </li>
                                <li class="guideline-item">
                                    <x-icon name="check-circle" class="w-4 h-4" style="color: #4f46e5; flex-shrink: 0; margin-top: 0.15rem;" />
                                    <div><strong class="guideline-title">Data Akurat</strong><span class="guideline-desc">Sesuai struk untuk validasi AI</span></div>
                                </li>
                            </ul>
                        </div>
                    </details>

                    <details class="compact-details info-details" open>
                        <summary class="info-summary info-summary-blue">
                            <x-icon name="git-branch" class="w-4 h-4" style="flex-shrink: 0;" />
                            <span>Alur Persetujuan</span>
                        </summary>
                        <div class="info-content info-content-blue">
                            <div class="flow-container">
                                <div class="flow-step">
                                    <span class="flow-step-number">1</span>
                                    <span class="flow-step-text">AI & Kirim</span>
                                </div>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flow-arrow"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                <div class="flow-step">
                                    <span class="flow-step-number">2</span>
                                    <span class="flow-step-text">Finance</span>
                                </div>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flow-arrow"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                <div class="flow-step flow-step-success">
                                    <x-icon name="check" class="w-3 h-3" style="color: #16a34a;" />
                                    <span class="flow-step-success-text">Cair</span>
                                </div>
                            </div>
                        </div>
                    </details>

                    <div class="compact-details docs-compact-card">
                        <div class="docs-card-header">
                            <x-icon name="file-text" class="w-4 h-4" />
                            <span>Checklist Dokumen</span>
                        </div>
                        <ul class="docs-checklist">
                            <li>Struk/nota terbaca lengkap</li>
                            <li>Tanggal transaksi valid</li>
                            <li>Nominal sama dengan bukti</li>
                            <li>Deskripsi pengeluaran jelas</li>
                            <li>Kategori biaya sesuai transaksi</li>
                        </ul>
                    </div>
                </aside>

                <form action="{{ route('atasan.pengajuan.store') }}" method="POST" enctype="multipart/form-data" class="form-pengajuan create-main-form">
                @csrf
                <input type="hidden" name="ocr_text" id="ocr_text">
                <input type="hidden" name="ocr_data_json" id="ocr_data_json">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="tanggal_transaksi" class="form-label" style="font-size: 0.9rem;">Tanggal Transaksi <span class="required">*</span></label>
                        <input type="date" class="form-input @error('tanggal_transaksi') error @enderror" id="tanggal_transaksi" name="tanggal_transaksi" value="{{ old('tanggal_transaksi', (isset($duplicateFrom) && $duplicateFrom->tanggal_transaksi) ? $duplicateFrom->tanggal_transaksi->format('Y-m-d') : '') }}" required style="font-size: 0.9rem;">
                        <small style="color: #64748b; font-size: 0.75rem; display: flex; align-items: center; gap: 0.25rem; margin-top: 0.2rem;">
                            <x-icon name="alert-circle" class="w-3 h-3" />
                            <span>Harus dalam <strong>15 hari</strong> dari tanggal ini</span>
                        </small>
                        @error('tanggal_transaksi')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="kategori_id" class="form-label" style="font-size: 0.9rem;">Kategori Biaya <span class="required">*</span></label>
                        <select class="form-input @error('kategori_id') error @enderror" id="kategori_id" name="kategori_id" required style="font-size: 0.9rem;">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategoriBiaya as $kategori)
                                <option value="{{ $kategori->kategori_id }}" 
                                    data-description="{{ $kategori->deskripsi }}"
                                    data-coa-code="{{ $kategori->defaultCoa->kode_coa ?? '' }}"
                                    data-coa-name="{{ $kategori->defaultCoa->nama_coa ?? '' }}"
                                    {{ old('kategori_id', isset($duplicateFrom) ? $duplicateFrom->kategori_id : '') == $kategori->kategori_id ? 'selected' : '' }}>
                                    {{ $kategori->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        <div id="kategori-description" style="display: none; margin-top: 0.4rem; padding: 0.5rem; background: #fefce8; border: 1px solid #fef08a; border-radius: 0.4rem; font-size: 0.8rem; color: #854d0e;">
                            <div style="display: flex; gap: 0.4rem;">
                                <x-icon name="info" class="w-3 h-3" style="flex-shrink: 0; margin-top: 0.05rem;" />
                                <span id="description-text"></span>
                            </div>
                        </div>

                        <!-- Selected COA Section -->
                        <div id="recommendedCoaSection" style="display: none; margin-top: 0.75rem; padding: 0.75rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem;">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem;">
                                <div>
                                    <p style="margin: 0 0 0.25rem 0; font-size: 0.75rem; color: #64748b; font-weight: 600;">Akun Akuntansi</p>
                                    <div style="display: flex; align-items: center; gap: 0.3rem; font-size: 0.95rem; font-weight: 700; color: #1e293b;">
                                        <span id="recommendedCoaCode">-</span>
                                        <span style="color: #cbd5e1; font-weight: 400;">—</span>
                                        <span id="recommendedCoaName">-</span>
                                    </div>
                                </div>
                                <div id="coaIconWrapper" style="padding: 0.3rem; border-radius: 0.5rem; background: #e2e8f0; color: #64748b;">
                                    <svg id="coaIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        @error('kategori_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="jenis_transaksi" class="form-label" style="font-size: 0.9rem;">Tipe Transaksi</label>
                        <select class="form-input @error('jenis_transaksi') error @enderror" id="jenis_transaksi" name="jenis_transaksi" style="font-size: 0.9rem;">
                            <option value="">-- Pilih --</option>
                            <option value="marketplace" {{ old('jenis_transaksi', isset($duplicateFrom) ? $duplicateFrom->jenis_transaksi : '') == 'marketplace' ? 'selected' : '' }}>Marketplace</option>
                            <option value="transfer_direct" {{ old('jenis_transaksi', isset($duplicateFrom) ? $duplicateFrom->jenis_transaksi : '') == 'transfer_direct' ? 'selected' : '' }}>Transfer Langsung</option>
                            <option value="transport" {{ old('jenis_transaksi', isset($duplicateFrom) ? $duplicateFrom->jenis_transaksi : '') == 'transport' ? 'selected' : '' }}>Ojek/Transport</option>
                            <option value="other" {{ old('jenis_transaksi', isset($duplicateFrom) ? $duplicateFrom->jenis_transaksi : '') == 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                        <small style="color: #64748b; font-size: 0.75rem; display: flex; align-items: center; gap: 0.25rem; margin-top: 0.2rem;">
                            <x-icon name="alert-circle" class="w-3 h-3" />
                            Bantu AI identifikasi vendor
                        </small>
                        @error('jenis_transaksi')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nama_vendor" class="form-label" style="font-size: 0.9rem;">Nama Merchant / Toko <span class="required">*</span></label>
                        <input type="text" class="form-input @error('nama_vendor') error @enderror" id="nama_vendor" name="nama_vendor" value="{{ old('nama_vendor', isset($duplicateFrom) ? $duplicateFrom->nama_vendor : '') }}" placeholder="Contoh: Toko ABC, Gojek" required style="font-size: 0.9rem;">
                        <small style="color: #64748b; font-size: 0.75rem; display: flex; align-items: center; gap: 0.25rem; margin-top: 0.2rem;">
                            <x-icon name="alert-circle" class="w-3 h-3" />
                            <span>Marketplace: nama toko penjual | Ojol: Gojek/Grab</span>
                        </small>
                        @error('nama_vendor')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="nominal" class="form-label" style="font-size: 0.9rem;">Nominal (Rp) <span class="required">*</span></label>
                        <input type="text" class="form-input @error('nominal') error @enderror" id="nominal" name="nominal" value="{{ old('nominal', (isset($duplicateFrom) && $duplicateFrom->nominal) ? number_format($duplicateFrom->nominal, 0, '', '') : '') }}" placeholder="0" required style="font-size: 0.9rem;">
                        
                        <!-- Multi-Invoice Selector (Hidden by default) -->
                        <div id="multi-invoice-selector" style="display: none; margin-top: 10px; padding: 10px; background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px;">
                            <p style="margin: 0 0 8px 0; font-size: 0.9rem; color: #166534; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                                <x-icon name="cpu" class="w-4 h-4" />
                                AI mendeteksi beberapa nominal. Pilih yang sesuai:
                            </p>
                            <div id="multi-invoice-options">
                                <!-- Options will be injected by JS -->
                            </div>
                        </div>

                        <!-- Manual/Fallback Logic for Multi-Invoice -->
                        <div id="manual-invoice-logic" style="display: none; margin-top: 5px;">
                            <small style="color: #64748b; font-size: 0.85rem; display: flex; flex-direction: column; gap: 0.4rem;">
                                <div style="display: flex; align-items: center; gap: 0.4rem;">
                                    <x-icon name="help-circle" class="w-3.5 h-3.5" />
                                    <span>Nominal tidak sesuai? Masukkan manual atau <a href="#" id="retry-ocr" style="color: #4f46e5; text-decoration: underline;">scan ulang</a>.</span>
                                </div>
                                <div style="display: flex; align-items: center; gap: 0.4rem;">
                                    <x-icon name="zap" class="w-3.5 h-3.5 text-warning" style="color: #f59e0b;" />
                                    <span>Tip: Jika nominal Anda adalah salah satu item (misal: "Total Belanja" Rp 157.752), sistem akan tetap meloloskannya selama angka tersebut ditemukan di struk.</span>
                                </div>
                            </small>
                        </div>

                        <small style="color: #64748b; font-size: 0.85rem; display: flex; align-items: center; gap: 0.4rem; margin-top: 0.35rem;">
                            <x-icon name="alert-circle" class="w-3.5 h-3.5" />
                            <span>Jumlah yang ada di bukti/struk. AI akan memverifikasi angka ini.</span>
                        </small>
                        @error('nominal')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="deskripsi" class="form-label" style="font-size: 0.9rem;">Deskripsi Pengeluaran <span class="required">*</span></label>
                    <textarea class="form-input textarea @error('deskripsi') error @enderror" id="deskripsi" name="deskripsi" rows="2" placeholder="Apa yang dibeli dan untuk keperluan apa?" style="font-size: 0.9rem;" required>{{ old('deskripsi', isset($duplicateFrom) ? $duplicateFrom->deskripsi : '') }}</textarea>
                    <small style="color: #64748b; font-size: 0.75rem; display: flex; align-items: center; gap: 0.25rem; margin-top: 0.2rem;">
                        <x-icon name="alert-circle" class="w-3 h-3" />
                        <span>Contoh: "Beli supplies kantor: kertas, tinta, folder"</span>
                    </small>
                    @error('deskripsi')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="file_bukti" class="form-label" style="font-size: 0.9rem;">Upload Bukti (Struk/Nota) <span class="required">*</span></label>
                    <div class="file-upload @error('file_bukti') error @enderror" id="fileUploadZone" style="padding: 2rem 1.5rem;">
                        <input type="file" id="file_bukti" name="file_bukti" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                        <div class="file-upload-text" style="gap: 0.5rem;" id="uploadContent">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 28px; height: 28px;">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            <p style="margin: 0.4rem 0 0; font-size: 0.95rem;">Upload bukti transaksi</p>
                            <span style="font-size: 0.75rem;">JPG, PNG, WebP, PDF | Maks 5MB</span>
                        </div>
                    </div>

                    <div id="filePreview" class="file-preview-container">
                        <div class="file-preview-box">
                            <div class="file-preview-content">
                                <svg id="previewIcon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="file-preview-icon">
                                    <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
                                    <polyline points="13 2 13 9 20 9"></polyline>
                                </svg>
                                <div class="file-preview-info">
                                    <p id="fileName" class="file-preview-name"></p>
                                    <p id="fileSize" class="file-preview-size"></p>
                                </div>
                                <button type="button" id="previewBtn" class="file-preview-button view">Lihat</button>
                                <button type="button" id="removeFile" class="file-preview-button remove">Hapus</button>
                            </div>
                        </div>
                    </div>
                    @if(isset($duplicateFrom) && $duplicateFrom->file_bukti)
                        <div style="margin-top: 0.5rem; padding: 0.5rem; background: #f1f5f9; border-radius: 0.5rem; font-size: 0.85rem; color: #64748b; display: flex; align-items: center; gap: 0.5rem;">
                            <x-icon name="copy" class="w-4 h-4" />
                            <span>File dari pengajuan sebelumnya tersedia. Upload baru untuk mengganti.</span>
                        </div>
                    @endif
                    @error('file_bukti')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div id="validationResults" class="validation-dashboard" style="display: none;">
                    <div class="validation-shell">
                        <div class="validation-shell-header">
                            <x-icon name="cpu" class="w-5 h-5 validation-shell-icon" />
                            <h3 class="validation-shell-title">Validasi AI & OCR</h3>
                        </div>

                        <div class="validation-grid">
                            <div class="validation-row validation-row-head">
                                <div>Field</div>
                                <div>Hasil OCR</div>
                                <div>Input Anda</div>
                                <div class="validation-status-head">Status</div>
                            </div>

                            <div class="validation-row">
                                <div class="validation-field-name">Vendor</div>
                                <div id="dash-ocr-vendor" class="validation-pill validation-pill-ocr" title="Klik untuk menggunakan nilai ini">-</div>
                                <div id="dash-input-vendor" class="validation-pill validation-pill-input">-</div>
                                <div id="dash-status-vendor" class="validation-status-cell">-</div>
                            </div>

                            <div class="validation-row">
                                <div class="validation-field-name">Nominal</div>
                                <div id="dash-ocr-nominal" class="validation-pill validation-pill-ocr" title="Klik untuk menggunakan nilai ini">-</div>
                                <div id="dash-input-nominal" class="validation-pill validation-pill-input">-</div>
                                <div id="dash-status-nominal" class="validation-status-cell">-</div>
                            </div>

                            <div class="validation-row">
                                <div class="validation-field-name">Tanggal</div>
                                <div id="dash-ocr-date" class="validation-pill validation-pill-ocr" title="Klik untuk menggunakan nilai ini">-</div>
                                <div id="dash-input-date" class="validation-pill validation-pill-input">-</div>
                                <div id="dash-status-date" class="validation-status-cell">-</div>
                            </div>
                        </div>
                    </div>

                    <div id="duplicateAlert" class="validation-alert-duplicate" style="display: none;">
                        <div class="validation-alert-content">
                            <x-icon name="alert-triangle" class="w-4 h-4 validation-alert-icon" />
                            <span>Duplikasi: File ini sudah pernah diupload.</span>
                        </div>
                    </div>

                    <div id="validationSummary" class="validation-summary-box">
                        <!-- Summary text will go here -->
                    </div>

                    <div id="ultra-smart-summary" class="ultra-smart-container" style="display: none;">
                        <div class="smart-header">
                            <x-icon name="ai" class="w-5 h-5" />
                            <h3>Ultra Smart OCR Result</h3>
                        </div>

                        <div class="smart-grid">
                            <div class="smart-card">
                                <p class="smart-card-title">Detail Transaksi</p>
                                <div id="smart-items-list" class="smart-items-list">
                                    <span class="smart-items-placeholder">Menganalisis item...</span>
                                </div>
                            </div>

                            <div class="smart-side-stack">
                                <div class="smart-card fraud-card">
                                    <p class="smart-card-title smart-card-title-success">Analisis Keamanan</p>
                                    <div id="smart-fraud-score" class="smart-score smart-score-safe">-</div>
                                    <div id="smart-fraud-note" class="smart-note smart-note-safe">-</div>
                                </div>

                                <div class="smart-card">
                                    <p class="smart-card-title">Confidence AI</p>
                                    <div id="smart-confidence-score" class="smart-score">-</div>
                                    <div id="smart-confidence-reason" class="smart-note">-</div>
                                </div>

                                <div class="smart-card smart-card-accent">
                                    <p class="smart-card-title smart-card-title-accent">Kategori Pintar</p>
                                    <div id="smart-category-badge" class="smart-badge">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <details class="validation-debug-details">
                        <summary class="validation-debug-summary">Lihat Raw OCR Text</summary>
                        <textarea id="debug-raw-ocr" rows="4" class="validation-debug-textarea" readonly placeholder="Raw OCR data akan muncul di sini..."></textarea>
                    </details>
                </div>

                <div class="form-actions" style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; align-items: center; gap: 1rem;">
                    <a href="{{ route('atasan.pengajuan.index') }}" class="btn-modern btn-modern-secondary" style="min-width: 120px;">
                        Batal
                    </a>
                    <button type="submit" id="submitBtn" class="btn-modern btn-modern-primary" style="min-width: 180px;" disabled>
                        <x-icon name="send" class="w-4 h-4 mr-2" />
                        Ajukan
                    </button>
                </div>
            </form>
            </div>
        </section>
    </div>
    </div>
</div>

<!-- File Preview Modal -->
<div id="fileModal" class="file-modal">
    <div class="file-modal-overlay" onclick="closeFileModal()"></div>
    <div class="file-modal-container">
        <div class="file-modal-header">
            <h3 id="modalFileName">Bukti Transaksi</h3>
            <button class="file-modal-close" onclick="closeFileModal()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="file-modal-body" id="modalBody"></div>
    </div>
</div>

@push('styles')
    <style>
        /* LinkedIn-style Compact Components */
        .compact-details {
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .compact-details[open] summary {
            border-radius: 0.875rem 0.875rem 0 0;
            background: #f1f5f9;
        }

        .create-layout-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 340px;
            gap: 1rem;
            align-items: start;
        }

        .create-main-form {
            grid-column: 1;
            grid-row: 1;
            margin: 0;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1.05rem;
            padding: 1rem 1.05rem;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.05);
        }

        .create-main-form .form-row {
            gap: 0.7rem;
        }

        .create-main-form .form-group {
            gap: 0.42rem;
        }

        .create-main-form .form-actions {
            margin-top: 1.25rem !important;
            padding-top: 1rem !important;
        }

        .create-side-panel {
            grid-column: 2;
            grid-row: 1;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            position: sticky;
            top: 0.85rem;
        }

        .create-side-panel .budget-status-container,
        .create-side-panel .budget-status-container.linkedin-style {
            margin: 0 !important;
            padding: 1rem !important;
            border-radius: 1rem !important;
        }

        .create-side-panel .info-details {
            margin: 0 !important;
            border: 1px solid #e2e8f0;
            border-radius: 0.9rem;
            background: #ffffff;
        }

        .create-side-panel .guidelines-list {
            grid-template-columns: 1fr;
            gap: 0.65rem;
        }

        .create-side-panel .flow-container {
            gap: 0.45rem;
        }

        .create-side-panel .flow-step {
            padding: 0.35rem 0.58rem;
            border-radius: 0.85rem;
            gap: 0.32rem;
        }

        .create-side-panel .flow-step-number {
            width: 16px;
            height: 16px;
            font-size: 0.62rem;
        }

        .create-side-panel .flow-step-text,
        .create-side-panel .flow-step-success-text {
            font-size: 0.74rem;
        }

        .docs-compact-card {
            border: 1px solid #dbe4f2;
            border-radius: 0.9rem;
            background: #f8fbff;
            padding: 0.85rem 0.95rem;
        }

        .docs-card-header {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            color: #39577f;
            font-weight: 700;
            font-size: 0.82rem;
            margin-bottom: 0.55rem;
        }

        .docs-checklist {
            margin: 0;
            padding-left: 1rem;
            display: grid;
            gap: 0.32rem;
        }

        .docs-checklist li {
            color: #4d607e;
            font-size: 0.78rem;
            line-height: 1.35;
            font-weight: 500;
        }

        @media (max-width: 1280px) {
            .create-layout-grid {
                grid-template-columns: minmax(0, 1fr) 310px;
            }
        }

        @media (max-width: 1024px) {
            .create-layout-grid {
                grid-template-columns: 1fr;
            }

            .create-main-form {
                order: 1;
                grid-column: 1;
                grid-row: auto;
            }

            .create-side-panel {
                order: 2;
                grid-column: 1;
                grid-row: auto;
                position: static;
                top: auto;
            }
        }

        .form-group .form-label {
            color: #475569;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-input {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 0.6rem 1rem;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            background: #ffffff;
            border-color: #4f46e5;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        /* AI Validation Styling */
        .validation-dashboard {
            margin-top: 1.15rem;
            display: flex;
            flex-direction: column;
            gap: 0.7rem;
        }

        .validation-shell {
            padding: 1rem 1.05rem;
            background: linear-gradient(165deg, #ffffff 0%, #f8fbff 100%);
            border: 1px solid #dbe7f7;
            border-radius: 1rem;
            box-shadow: 0 10px 24px rgba(30, 58, 106, 0.08);
        }

        .validation-shell-header {
            display: flex;
            align-items: center;
            gap: 0.52rem;
            margin-bottom: 0.82rem;
            padding-bottom: 0.62rem;
            border-bottom: 1px solid #e7eef8;
        }

        .validation-shell-icon {
            color: #4f46e5;
        }

        .validation-shell-title {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 700;
            color: #1e293b;
            letter-spacing: 0.01em;
        }

        .validation-grid {
            display: flex;
            flex-direction: column;
            gap: 0.48rem;
        }

        .validation-row {
            display: grid;
            grid-template-columns: 90px minmax(0, 1fr) minmax(0, 1fr) 70px;
            gap: 0.7rem;
            padding: 0.58rem 0.64rem;
            align-items: center;
            border: 1px solid #edf2fa;
            border-radius: 0.72rem;
            background: #fbfdff;
        }

        .validation-row-head {
            border: none;
            background: transparent;
            padding: 0 0.5rem 0.2rem;
            border-radius: 0;
            font-size: 0.68rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .validation-field-name {
            font-weight: 700;
            color: #475569;
            font-size: 0.8rem;
        }

        .validation-pill {
            padding: 0.38rem 0.58rem;
            border-radius: 0.52rem;
            font-size: 0.81rem;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .validation-pill-ocr {
            color: #4338ca;
            background: #f2f0ff;
            border: 1px solid #ddd6fe;
            cursor: pointer;
        }

        .validation-pill-input {
            color: #1e293b;
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }

        .validation-status-head,
        .validation-status-cell {
            text-align: center;
        }

        .validation-alert-duplicate {
            padding: 0.58rem 0.7rem;
            background: #fef2f2;
            border: 1px solid #fecdd3;
            border-radius: 0.7rem;
            color: #dc2626;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .validation-alert-content {
            display: flex;
            gap: 0.5rem;
            align-items: flex-start;
        }

        .validation-alert-icon {
            flex-shrink: 0;
            margin-top: 0.1rem;
        }

        .validation-summary-box {
            padding: 0.72rem 0.86rem;
            border-radius: 0.72rem;
            font-size: 0.82rem;
            line-height: 1.45;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            margin-top: 0.08rem;
            animation: slideIn 0.3s ease-out;
        }

        .validation-debug-details {
            font-size: 0.8rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.8rem;
            background: #f8fafc;
            padding: 0.15rem;
        }

        .validation-debug-summary {
            cursor: pointer;
            color: #64748b;
            font-weight: 600;
            padding: 0.4rem 0.5rem;
            user-select: none;
        }

        .validation-debug-textarea {
            margin-top: 0.2rem;
            width: 100%;
            padding: 0.55rem;
            font-size: 0.74rem;
            border: 1px solid #e2e8f0;
            border-radius: 0.55rem;
            font-family: "JetBrains Mono", "Fira Code", monospace;
            background: #f8fafc;
            color: #475569;
            resize: vertical;
            min-height: 88px;
        }

        /* Budget Progress Bar Styles */
        .budget-status-container {
            background: white;
            border-radius: 1.25rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid #eef2f7;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }
        .budget-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }
        .budget-title {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }
        .budget-meta {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
        }
        .progress-wrapper {
            height: 12px;
            background: #f1f5f9;
            border-radius: 10px;
            overflow: hidden;
            position: relative;
        }
        .progress-bar {
            height: 100%;
            border-radius: 10px;
            transition: width 1s ease-in-out;
        }
        .progress-success { background: linear-gradient(90deg, #10b981, #34d399); }
        .progress-warning { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
        .progress-danger { background: linear-gradient(90deg, #ef4444, #f87171); }
        
        .budget-info-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 0.75rem;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .usage-text { color: #1e293b; }
        .remaining-text { color: #64748b; }

        /* Info & Flow Components */
        .info-details { margin-bottom: 1.25rem; }
        .info-summary {
            cursor: pointer; padding: 1rem; background: #f8fafc; border: 1px solid #e2e8f0; 
            border-radius: 0.875rem; font-weight: 600; color: #1e293b; 
            display: flex; align-items: center; gap: 0.75rem; user-select: none;
        }
        .info-summary-blue { background: #eff6ff; border-color: #bfdbfe; color: #1e40af; }
        .info-content {
            padding: 1rem; background: #ffffff; border: 1px solid #e2e8f0; 
            border-top: none; border-radius: 0 0 0.875rem 0.875rem;
        }
        .info-content-blue { border-color: #bfdbfe; }

        .guidelines-list {
            list-style: none; padding: 0; margin: 0; 
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;
        }
        .guideline-item { display: flex; gap: 0.5rem; align-items: flex-start; font-size: 0.9rem; }
        .guideline-title { display: block; color: #1e293b; font-weight: 700; }
        .guideline-desc { color: #64748b; font-size: 0.85rem; }

        .flow-container { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; font-size: 0.9rem; }
        .flow-step {
            display: flex; align-items: center; gap: 0.4rem; background: #f0f4ff; 
            padding: 0.4rem 0.8rem; border-radius: 1.5rem; border: 1px solid #dbeafe;
        }
        .flow-step-number {
            width: 20px; height: 20px; background: #3b82f6; color: white; 
            border-radius: 50%; display: flex; align-items: center; justify-content: center; 
            font-size: 0.7rem; font-weight: 700;
        }
        .flow-step-text { color: #1e3a8a; font-weight: 600; font-size: 0.85rem; }
        .flow-step-success { background: #f0fdf4; border-color: #bbf7d0; }
        .flow-step-success-text { color: #166534; font-weight: 700; font-size: 0.85rem; }
        .flow-arrow { width: 14px; height: 14px; color: #93c5fd; flex-shrink: 0; }

        /* Ultra Smart Summary Styles */
        .ultra-smart-container {
            margin-bottom: 1rem;
            padding: 1.05rem;
            background: linear-gradient(170deg, #ffffff 0%, #f7faff 100%);
            border: 1px solid #dbe8fb;
            border-radius: 1rem;
            box-shadow: 0 12px 26px rgba(31, 56, 94, 0.08);
            animation: slideIn 0.4s ease-out;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .smart-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.7rem;
            color: #4338ca;
        }

        .smart-header h3 {
            margin: 0;
            font-size: 0.9rem;
            font-weight: 700;
            letter-spacing: 0.01em;
        }

        .smart-grid {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 0.72rem;
        }

        .smart-side-stack {
            display: flex;
            flex-direction: column;
            gap: 0.62rem;
        }

        .smart-card {
            padding: 0.82rem;
            background: #f8fafc;
            border-radius: 0.82rem;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            gap: 0.38rem;
        }

        .smart-card.fraud-card {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .smart-card-accent {
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .smart-card-title {
            margin: 0;
            font-size: 0.65rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .smart-card-title-success {
            color: #166534;
        }

        .smart-card-title-accent {
            color: #1e40af;
        }

        .smart-items-list {
            font-size: 0.82rem;
            color: #1e293b;
            line-height: 1.45;
        }

        .smart-items-placeholder {
            color: #94a3b8;
            font-style: italic;
        }

        .smart-score {
            font-size: 0.88rem;
            font-weight: 800;
            color: #475569;
            line-height: 1.25;
        }

        .smart-score-safe {
            color: #16a34a;
        }

        .smart-note {
            font-size: 0.72rem;
            color: #64748b;
            line-height: 1.35;
        }

        .smart-note-safe {
            color: #15803d;
        }

        .smart-badge {
            display: inline-block;
            padding: 0.2rem 0.72rem;
            background: #3b82f6;
            color: #ffffff;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            width: fit-content;
        }

        @media (max-width: 900px) {
            .validation-row {
                grid-template-columns: 82px minmax(0, 1fr) minmax(0, 1fr) 62px;
                gap: 0.52rem;
                padding: 0.52rem;
            }

            .smart-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@push('scripts')
<script>
    function closeFileModal() {
        const modal = document.getElementById('fileModal');
        if (modal) modal.classList.remove('show');
    }

    const OCR_ROUTES = {
        process: "{{ route('atasan.validasi-ai.process-file') }}",
        validate: "{{ route('atasan.validasi-ai.validate-input') }}"
    };

    (function () {
        const notify = (type, title, message) => {
            if (window.showNotification) {
                window.showNotification(type, title, message);
            } else {
                console[type === 'error' ? 'error' : 'log'](`${title}: ${message}`);
            }
        };

        const formatRupiah = (value) => {
            const num = Number(String(value || 0).replace(/[^\d.-]/g, '')) || 0;
            return `Rp ${num.toLocaleString('id-ID')}`;
        };

        const statusBadge = (status) => {
            if (status === 'pass') return '<span style="color:#16a34a;font-weight:700;">OK</span>';
            if (status === 'warning') return '<span style="color:#d97706;font-weight:700;">WARN</span>';
            if (status === 'fail') return '<span style="color:#dc2626;font-weight:700;">FAIL</span>';
            return '<span style="color:#64748b;font-weight:600;">-</span>';
        };

        const initOcrCreateForm = () => {
            const form = document.querySelector('form.form-pengajuan');
            if (!form || form.dataset.ocrBound === '1') return;
            form.dataset.ocrBound = '1';

            const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const fileInput = document.getElementById('file_bukti');
            const submitBtn = document.getElementById('submitBtn');
            const vendorInput = document.getElementById('nama_vendor');
            const nominalInput = document.getElementById('nominal');
            const dateInput = document.getElementById('tanggal_transaksi');
            const trxTypeInput = document.getElementById('jenis_transaksi');
            const ocrTextInput = document.getElementById('ocr_text');
            const ocrJsonInput = document.getElementById('ocr_data_json');
            const validationResults = document.getElementById('validationResults');
            const duplicateAlert = document.getElementById('duplicateAlert');
            const validationSummary = document.getElementById('validationSummary');
            const uploadContent = document.getElementById('uploadContent');
            const fileName = document.getElementById('fileName');
            const fileSize = document.getElementById('fileSize');
            const filePreview = document.getElementById('filePreview');
            const removeFile = document.getElementById('removeFile');
            const retryOcr = document.getElementById('retry-ocr');
            const multiInvoice = document.getElementById('multi-invoice-selector');
            const multiOptions = document.getElementById('multi-invoice-options');

            const ocrVendor = document.getElementById('dash-ocr-vendor');
            const ocrNominal = document.getElementById('dash-ocr-nominal');
            const ocrDate = document.getElementById('dash-ocr-date');
            const inputVendor = document.getElementById('dash-input-vendor');
            const inputNominal = document.getElementById('dash-input-nominal');
            const inputDate = document.getElementById('dash-input-date');
            const statusVendor = document.getElementById('dash-status-vendor');
            const statusNominal = document.getElementById('dash-status-nominal');
            const statusDate = document.getElementById('dash-status-date');

            const rawOcr = document.getElementById('debug-raw-ocr');
            const smartWrap = document.getElementById('ultra-smart-summary');
            const smartItems = document.getElementById('smart-items-list');
            const smartFraudScore = document.getElementById('smart-fraud-score');
            const smartFraudNote = document.getElementById('smart-fraud-note');
            const smartConfidenceScore = document.getElementById('smart-confidence-score');
            const smartConfidenceReason = document.getElementById('smart-confidence-reason');
            const smartCategory = document.getElementById('smart-category-badge');

            let currentOcrData = null;
            let isDuplicate = false;
            let canSubmitByValidation = false;
            let validateTimer = null;
            let validateRequestId = 0;

            const debounceValidateInput = (delay = 220) => {
                clearTimeout(validateTimer);
                validateTimer = setTimeout(() => {
                    void validateInput();
                }, delay);
            };

            const setSubmitState = () => {
                if (!submitBtn) return;
                const hasFile = !!(fileInput && fileInput.files && fileInput.files.length);
                submitBtn.disabled = !(hasFile && !isDuplicate && canSubmitByValidation);
            };

            const setSummary = (type, text) => {
                if (!validationSummary) return;
                const map = {
                    error: ['#fef2f2', '#fecaca', '#991b1b'],
                    warning: ['#fffbeb', '#fde68a', '#92400e'],
                    success: ['#f0fdf4', '#bbf7d0', '#166534'],
                    info: ['#eff6ff', '#bfdbfe', '#1e40af']
                };
                const [bg, bd, color] = map[type] || map.info;
                validationSummary.style.background = bg;
                validationSummary.style.border = `1px solid ${bd}`;
                validationSummary.style.color = color;
                validationSummary.textContent = text;
            };

            const bindCopy = (sourceEl, setter) => {
                if (!sourceEl || sourceEl.dataset.copyBound === '1') return;
                sourceEl.dataset.copyBound = '1';
                sourceEl.addEventListener('click', () => setter(sourceEl.textContent.trim()));
            };

            bindCopy(ocrVendor, (val) => {
                if (val && val !== '-' && vendorInput) vendorInput.value = val;
                debounceValidateInput(0);
            });
            bindCopy(ocrNominal, (val) => {
                if (val && val !== '-' && nominalInput) nominalInput.value = String(val).replace(/[^\d]/g, '');
                debounceValidateInput(0);
            });
            bindCopy(ocrDate, (val) => {
                if (val && val !== '-' && dateInput) dateInput.value = val;
                debounceValidateInput(0);
            });

            const renderOcrData = (data) => {
                validationResults && (validationResults.style.display = 'block');
                duplicateAlert && (duplicateAlert.style.display = 'none');

                const vendor = data?.vendor || '-';
                const nominal = data?.nominal || 0;
                const tanggal = data?.tanggal || '-';

                if (ocrVendor) ocrVendor.textContent = vendor;
                if (ocrNominal) ocrNominal.textContent = nominal ? formatRupiah(nominal) : '-';
                if (ocrDate) ocrDate.textContent = tanggal;
                if (rawOcr) rawOcr.value = data?.raw_text || '';

                if (ocrTextInput) ocrTextInput.value = data?.raw_text || '';
                if (ocrJsonInput) ocrJsonInput.value = JSON.stringify(data || {});

                if (smartWrap) smartWrap.style.display = 'block';
                if (smartItems) smartItems.textContent = (data?.items || []).length ? data.items.join(', ') : (data?.detail_transaksi || 'Data item tidak tersedia');
                if (smartFraudScore) smartFraudScore.textContent = data?.fraud_score ? `${data.fraud_score}% aman` : 'Aman';
                if (smartFraudNote) smartFraudNote.textContent = data?.fraud_note || 'Tidak ada anomali signifikan terdeteksi';
                if (smartConfidenceScore) smartConfidenceScore.textContent = data?.confidence_score ? `${data.confidence_score}%` : '-';
                if (smartConfidenceReason) smartConfidenceReason.textContent = data?.confidence_reason || 'Analisis OCR selesai';
                if (smartCategory) smartCategory.textContent = data?.predicted_category || data?.kategori || 'Umum';
            };

            const renderValidation = (result) => {
                const matches = result?.matches || {};
                if (inputVendor) inputVendor.textContent = vendorInput?.value || '-';
                if (inputNominal) inputNominal.textContent = nominalInput?.value ? formatRupiah(nominalInput.value) : '-';
                if (inputDate) inputDate.textContent = dateInput?.value || '-';

                if (statusVendor) statusVendor.innerHTML = statusBadge(matches.vendor?.status);
                if (statusNominal) statusNominal.innerHTML = statusBadge(matches.nominal?.status);
                if (statusDate) statusDate.innerHTML = statusBadge(matches.tanggal?.status);

                const issues = result?.issues || [];
                if (issues.length > 0) {
                    const top = issues[0];
                    setSummary(top.type || 'warning', `${top.title}: ${top.message}`);
                } else {
                    setSummary('success', 'Data valid. Pengajuan siap dikirim.');
                }
            };

            const validateInput = async () => {
                const requestId = ++validateRequestId;
                if (!currentOcrData) {
                    canSubmitByValidation = !!(vendorInput?.value && nominalInput?.value && dateInput?.value);
                    setSubmitState();
                    return;
                }
                try {
                    const response = await fetch(OCR_ROUTES.validate, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            ocr_data: currentOcrData,
                            nama_vendor: vendorInput?.value || '',
                            nominal: nominalInput?.value || '',
                            tanggal_transaksi: dateInput?.value || '',
                            jenis_transaksi: trxTypeInput?.value || ''
                        })
                    });
                    const result = await response.json();
                    if (requestId !== validateRequestId) return;
                    if (!response.ok || result.success === false) {
                        canSubmitByValidation = false;
                        setSummary('error', result.message || 'Validasi input gagal.');
                        setSubmitState();
                        return;
                    }
                    canSubmitByValidation = !!result.can_submit;
                    renderValidation(result);
                    setSubmitState();
                } catch (e) {
                    if (requestId !== validateRequestId) return;
                    canSubmitByValidation = false;
                    setSummary('error', 'Gagal menghubungi server validasi.');
                    setSubmitState();
                }
            };

            const processOcr = async () => {
                if (!fileInput || !fileInput.files.length) return;
                const file = fileInput.files[0];
                const formData = new FormData();
                formData.append('file_bukti', file);
                formData.append('jenis_transaksi', trxTypeInput?.value || '');
                formData.append('ocr_text', ocrTextInput?.value || '');

                if (uploadContent) uploadContent.innerHTML = '<p style="margin:0;font-size:.9rem;color:#4f46e5;">Memproses OCR...</p>';

                try {
                    const response = await fetch(OCR_ROUTES.process, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });
                    const result = await response.json();
                    if (!response.ok || result.success === false) {
                        currentOcrData = null;
                        validateRequestId++;
                        canSubmitByValidation = false;
                        setSummary('error', result.message || 'OCR gagal diproses.');
                        setSubmitState();
                        notify('error', 'OCR Gagal', result.message || 'Proses OCR gagal.');
                        return;
                    }

                    isDuplicate = !!result.is_duplicate;
                    if (duplicateAlert) duplicateAlert.style.display = isDuplicate ? 'block' : 'none';
                    if (isDuplicate) {
                        validateRequestId++;
                        canSubmitByValidation = false;
                        setSummary('error', result.message || 'File terdeteksi duplikat.');
                        setSubmitState();
                        return;
                    }

                    currentOcrData = result.ocr_data || null;
                    renderOcrData(currentOcrData || {});

                    if (multiInvoice && multiOptions) {
                        const options = result.multi_invoice?.amounts || [];
                        if (options.length > 1) {
                            multiInvoice.style.display = 'block';
                            multiOptions.innerHTML = options.map((amt, idx) => `
                                <label style="display:flex;gap:.4rem;align-items:center;margin:.25rem 0;">
                                    <input type="radio" name="ocr_nominal_pick" value="${amt}" ${idx === 0 ? 'checked' : ''}>
                                    <span>${formatRupiah(amt)}</span>
                                </label>
                            `).join('');
                            multiOptions.querySelectorAll('input[name="ocr_nominal_pick"]').forEach((el) => {
                                el.addEventListener('change', () => {
                                    if (nominalInput) nominalInput.value = String(el.value).replace(/[^\d]/g, '');
                                    debounceValidateInput(0);
                                });
                            });
                        } else {
                            multiInvoice.style.display = 'none';
                        }
                    }

                    await validateInput();
                    notify('success', 'OCR Berhasil', result.message || 'Data struk berhasil dibaca.');
                } catch (e) {
                    currentOcrData = null;
                    validateRequestId++;
                    canSubmitByValidation = false;
                    setSummary('error', 'Terjadi gangguan jaringan saat OCR.');
                    setSubmitState();
                } finally {
                    if (uploadContent) {
                        uploadContent.innerHTML = `
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 28px; height: 28px;">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            <p style="margin: 0.4rem 0 0; font-size: 0.95rem;">Upload bukti transaksi</p>
                            <span style="font-size: 0.75rem;">JPG, PNG, WebP, PDF | Maks 5MB</span>
                        `;
                    }
                }
            };

            fileInput?.addEventListener('change', () => {
                const file = fileInput.files?.[0];
                if (!file) return;
                if (fileName) fileName.textContent = file.name;
                if (fileSize) fileSize.textContent = `${(file.size / 1024 / 1024).toFixed(2)} MB`;
                if (filePreview) filePreview.style.display = 'block';
                processOcr();
            });

            removeFile?.addEventListener('click', () => {
                if (fileInput) fileInput.value = '';
                if (filePreview) filePreview.style.display = 'none';
                if (validationResults) validationResults.style.display = 'none';
                if (duplicateAlert) duplicateAlert.style.display = 'none';
                if (multiInvoice) multiInvoice.style.display = 'none';
                currentOcrData = null;
                isDuplicate = false;
                canSubmitByValidation = false;
                clearTimeout(validateTimer);
                validateRequestId++;
                setSubmitState();
            });

            retryOcr?.addEventListener('click', (e) => {
                e.preventDefault();
                fileInput?.click();
            });

            [vendorInput, nominalInput, dateInput, trxTypeInput].forEach((el) => {
                el?.addEventListener('input', () => debounceValidateInput(220));
                el?.addEventListener('change', () => debounceValidateInput(0));
            });

            form.addEventListener('submit', (e) => {
                if (submitBtn?.disabled) {
                    e.preventDefault();
                    notify('warning', 'Belum Valid', 'Lengkapi data dan tunggu validasi OCR selesai.');
                }
            });

            setSubmitState();
        };

        document.addEventListener('DOMContentLoaded', initOcrCreateForm);
        document.addEventListener('livewire:navigated', initOcrCreateForm);
    })();
</script>
@endpush

@endsection
