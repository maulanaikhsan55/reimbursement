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
                <a href="{{ route('pegawai.pengajuan.index') }}" class="link-back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Kembali
                </a>
            </div>

            <x-budget-indicator 
                :status="$budgetStatus" 
                :departmentName="Auth::user()->departemen->nama_departemen" 
            />

            <!-- Guidelines Section - Compact -->
            <details class="compact-details info-details">
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

            <!-- Approval Flow - Compact -->
            <details class="compact-details info-details" style="margin-bottom: 1.5rem;">
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

                        @if(Auth::user()->role === 'pegawai')
                            <div class="flow-step">
                                <span class="flow-step-number">2</span>
                                <span class="flow-step-text">Atasan ({{ Auth::user()->atasan->name ?? 'Belum Diatur' }})</span>
                            </div>
                            
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flow-arrow"><polyline points="9 18 15 12 9 6"></polyline></svg>

                            <div class="flow-step">
                                <span class="flow-step-number">3</span>
                                <span class="flow-step-text">Finance</span>
                            </div>
                        @else
                            <div class="flow-step">
                                <span class="flow-step-number">2</span>
                                <span class="flow-step-text">Finance</span>
                            </div>
                        @endif

                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flow-arrow"><polyline points="9 18 15 12 9 6"></polyline></svg>

                        <div class="flow-step flow-step-success">
                            <x-icon name="check" class="w-3 h-3" style="color: #16a34a;" />
                            <span class="flow-step-success-text">Cair</span>
                        </div>
                    </div>
                </div>
            </details>

            <form action="{{ route('pegawai.pengajuan.store') }}" method="POST" enctype="multipart/form-data" class="form-pengajuan">
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
                        @error('nominal')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label for="deskripsi" class="form-label" style="font-size: 0.9rem;">Deskripsi Pengeluaran <span class="required">*</span></label>
                    <textarea class="form-input @error('deskripsi') error @enderror" id="deskripsi" name="deskripsi" rows="2" placeholder="Apa yang dibeli dan untuk keperluan apa?" required style="font-size: 0.9rem; min-height: 80px;">{{ old('deskripsi', isset($duplicateFrom) ? $duplicateFrom->deskripsi : '') }}</textarea>
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

                <!-- AI Validation Dashboard (Initially Hidden) -->
                <div id="validationResults" class="validation-dashboard" style="display: none; margin-top: 1.25rem;">
                    <div style="padding: 1.25rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 1rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem; padding-bottom: 0.75rem; border-bottom: 1px solid #f1f5f9;">
                            <x-icon name="cpu" class="w-5 h-5" style="color: #4f46e5;" />
                            <h3 style="margin: 0; font-size: 0.95rem; font-weight: 700; color: #1e293b;">Validasi AI & OCR</h3>
                        </div>
                        
                        <div class="validation-grid" style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <!-- Header -->
                            <div style="display: grid; grid-template-columns: 100px 1fr 1fr 80px; gap: 1rem; padding: 0 0.5rem; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">
                                <div>Field</div>
                                <div>Hasil OCR</div>
                                <div>Input Anda</div>
                                <div style="text-align: center;">Status</div>
                            </div>

                            <!-- Vendor -->
                            <div style="display: grid; grid-template-columns: 100px 1fr 1fr 80px; gap: 1rem; padding: 0.75rem; background: #f8fafc; border-radius: 0.75rem; align-items: center; border: 1px solid #f1f5f9;">
                                <div style="font-weight: 600; color: #475569; font-size: 0.85rem;">Vendor</div>
                                <div id="dash-ocr-vendor" style="padding: 0.4rem 0.6rem; color: #4f46e5; background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 0.4rem; cursor: pointer; font-size: 0.85rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="Klik untuk menggunakan nilai ini">-</div>
                                <div id="dash-input-vendor" style="padding: 0.4rem 0.6rem; color: #1e293b; background: white; border: 1px solid #e2e8f0; border-radius: 0.4rem; font-size: 0.85rem; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">-</div>
                                <div id="dash-status-vendor" style="text-align: center;">-</div>
                            </div>

                            <!-- Nominal -->
                            <div style="display: grid; grid-template-columns: 100px 1fr 1fr 80px; gap: 1rem; padding: 0.75rem; background: #f8fafc; border-radius: 0.75rem; align-items: center; border: 1px solid #f1f5f9;">
                                <div style="font-weight: 600; color: #475569; font-size: 0.85rem;">Nominal</div>
                                <div id="dash-ocr-nominal" style="padding: 0.4rem 0.6rem; color: #4f46e5; background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 0.4rem; cursor: pointer; font-size: 0.85rem; font-weight: 500;" title="Klik untuk menggunakan nilai ini">-</div>
                                <div id="dash-input-nominal" style="padding: 0.4rem 0.6rem; color: #1e293b; background: white; border: 1px solid #e2e8f0; border-radius: 0.4rem; font-size: 0.85rem; font-weight: 500;">-</div>
                                <div id="dash-status-nominal" style="text-align: center;">-</div>
                            </div>

                            <!-- Date -->
                            <div style="display: grid; grid-template-columns: 100px 1fr 1fr 80px; gap: 1rem; padding: 0.75rem; background: #f8fafc; border-radius: 0.75rem; align-items: center; border: 1px solid #f1f5f9;">
                                <div style="font-weight: 600; color: #475569; font-size: 0.85rem;">Tanggal</div>
                                <div id="dash-ocr-date" style="padding: 0.4rem 0.6rem; color: #4f46e5; background: #f5f3ff; border: 1px solid #ddd6fe; border-radius: 0.4rem; cursor: pointer; font-size: 0.85rem; font-weight: 500;" title="Klik untuk menggunakan nilai ini">-</div>
                                <div id="dash-input-date" style="padding: 0.4rem 0.6rem; color: #1e293b; background: white; border: 1px solid #e2e8f0; border-radius: 0.4rem; font-size: 0.85rem; font-weight: 500;">-</div>
                                <div id="dash-status-date" style="text-align: center;">-</div>
                            </div>
                        </div>
                    </div>

                    <div id="duplicateAlert" style="display: none; padding: 0.6rem; background: #fef2f2; border: 1px solid #fecdd3; border-radius: 0.4rem; color: #dc2626; font-size: 0.8rem; font-weight: 600;">
                        <div style="display: flex; gap: 0.5rem; align-items: flex-start;">
                            <x-icon name="alert-triangle" class="w-4 h-4" style="flex-shrink: 0; margin-top: 0.1rem;" />
                            <span>Duplikasi: File ini sudah pernah diupload.</span>
                        </div>
                    </div>

                    <div id="validationSummary" style="margin-top: 0.5rem; padding: 0.6rem; border-radius: 0.4rem; font-size: 0.85rem;">
                        <!-- Summary text will go here -->
                    </div>

                    <!-- Ultra Smart OCR Summary Section -->
                    <div id="ultra-smart-summary" class="ultra-smart-container" style="display: none;">
                        <div class="smart-header">
                            <x-icon name="ai" class="w-5 h-5" />
                            <h3>Ultra Smart OCR Result</h3>
                        </div>
                        
                        <div class="smart-grid">
                            <!-- Kolom Kiri: Ringkasan Transaksi -->
                            <div class="smart-card">
                                <p class="smart-card-title">Detail Transaksi</p>
                                <div id="smart-items-list" style="font-size: 0.85rem; color: #1e293b;">
                                    <!-- Daftar barang akan muncul di sini -->
                                    <span style="color: #94a3b8; font-style: italic;">Menganalisis item...</span>
                                </div>
                            </div>
                            
                            <!-- Kolom Kanan: Keamanan & Kategori -->
                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <div class="smart-card fraud-card" style="background: #f0fdf4; border-color: #bbf7d0;">
                                    <p class="smart-card-title" style="color: #166534;">Analisis Keamanan</p>
                                    <div id="smart-fraud-score" style="font-size: 0.9rem; font-weight: 800; color: #16a34a;">-</div>
                                    <div id="smart-fraud-note" style="font-size: 0.75rem; color: #15803d; line-height: 1.3; margin-top: 0.25rem;">-</div>
                                </div>

                                <div class="smart-card">
                                    <p class="smart-card-title">Confidence AI</p>
                                    <div id="smart-confidence-score" style="font-size: 0.9rem; font-weight: 800; color: #475569;">-</div>
                                    <div id="smart-confidence-reason" style="font-size: 0.7rem; color: #64748b; line-height: 1.3; margin-top: 0.25rem;">-</div>
                                </div>

                                <div class="smart-card" style="background: #eff6ff; border-color: #bfdbfe;">
                                    <p class="smart-card-title" style="color: #1e40af;">Kategori Pintar</p>
                                    <div id="smart-category-badge" class="smart-badge" style="background: #3b82f6;">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Debug Section - Compact -->
                    <details style="font-size: 0.8rem;">
                        <summary style="cursor: pointer; color: #64748b; font-weight: 500; padding: 0.4rem; user-select: none;">Lihat Raw OCR Text</summary>
                        <textarea id="debug-raw-ocr" rows="4" style="margin-top: 0.4rem; width: 100%; padding: 0.5rem; font-size: 0.75rem; border: 1px solid #e2e8f0; border-radius: 0.4rem; font-family: monospace; background: #f8fafc; color: #475569;" readonly placeholder="Raw OCR data akan muncul di sini..."></textarea>
                    </details>
                </div>

                <div class="form-actions" style="margin-top: 2.5rem; padding-top: 1.5rem; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; align-items: center; gap: 1rem;">
                    <a href="{{ route('pegawai.pengajuan.index') }}" class="btn-modern btn-modern-secondary" style="min-width: 120px;">
                        Batal
                    </a>
                    <button type="submit" id="submitBtn" class="btn-modern btn-modern-primary" style="min-width: 180px;" disabled>
                        <x-icon name="send" class="w-4 h-4 mr-2" />
                        Ajukan
                    </button>
                </div>
            </form>
        </section>
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

@endsection

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
    .validation-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        padding: 1rem;
        transition: all 0.3s ease;
    }

    .validation-card.valid { border-color: #10b981; background: #f0fdf4; }
    .validation-card.invalid { border-color: #ef4444; background: #fef2f2; }
    .validation-card.warning { border-color: #f59e0b; background: #fffbeb; }

    .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        color: #64748b;
    }

    .val-item {
        display: flex;
        flex-direction: column;
        margin-bottom: 0.5rem;
    }

    .val-item:last-child { margin-bottom: 0; }

    .val-item .label {
        font-size: 0.7rem;
        color: #94a3b8;
        font-weight: 600;
    }

    .val-item .value-input, .val-item .value-ocr {
        font-size: 0.85rem;
        font-weight: 700;
        word-break: break-all;
    }

    .validation-summary-box {
        padding: 1rem 1.25rem;
        border-radius: 1rem;
        font-size: 0.9rem;
        line-height: 1.5;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        margin-top: 1rem;
        animation: slideIn 0.3s ease-out;
    }
    .validation-summary-error {
        background: #fef2f2;
        border-color: #fecdd3;
        color: #991b1b;
    }
    .validation-summary-warning {
        background: #fffbeb;
        border-color: #fef3c7;
        color: #92400e;
    }
    .validation-summary-success {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #166534;
    }
    .validation-summary-info {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1e40af;
    }

    /* Ultra Smart Summary Styles */
    .ultra-smart-container {
        margin-bottom: 1.25rem;
        padding: 1.25rem;
        background: #ffffff;
        border: 2px solid #e0e7ff;
        border-radius: 1.25rem;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        animation: slideIn 0.4s ease-out;
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .smart-header {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        margin-bottom: 1rem;
        color: #4338ca;
    }
    .smart-header h3 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 800;
        letter-spacing: -0.02em;
    }
    .smart-grid {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 1rem;
    }
    .smart-card {
        padding: 1rem;
        background: #f8fafc;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .smart-card.fraud-card {
        transition: all 0.3s ease;
    }
    .smart-card-title {
        margin: 0;
        font-size: 0.7rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .smart-badge {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        background: #4f46e5;
        color: white;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 700;
        width: fit-content;
    }

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
</style>
@endpush

@push('scripts')
<script>
    function closeFileModal() {
        const modal = document.getElementById('fileModal');
        if (modal) {
            modal.classList.remove('show');
        }
    }

    const OCR_ROUTES = {
        process: "{{ route('pegawai.validasi-ai.process-file') }}",
        validate: "{{ route('pegawai.validasi-ai.validate-input') }}"
    };
</script>
<script src="{{ asset('js/tesseract-ocr.js') }}"></script>
<script src="{{ asset('js/modules/pengajuan.js') }}"></script>
@endpush
