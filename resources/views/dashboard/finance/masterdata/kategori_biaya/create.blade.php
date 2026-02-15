@extends('layouts.app')

@section('title', 'Tambah Kategori Biaya - Humplus Reimbursement')
@section('page-title', 'Tambah Kategori Biaya Baru')

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Tambah Kategori Biaya Baru" subtitle="Isi form di bawah untuk menambahkan kategori biaya baru" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content">
            <section class="modern-section">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Form Tambah Kategori</h2>
                        <p class="section-subtitle">Isi data lengkap kategori biaya baru</p>
                    </div>
                </div>

        <form method="POST" action="{{ route('finance.masterdata.kategori_biaya.store') }}">
            @csrf

            <div class="form-grid">
                <!-- Identitas Kategori -->
                <div class="form-group">
                    <label for="kode_kategori">Kode Kategori <span class="required">*</span></label>
                    <input type="text" 
                           id="kode_kategori" 
                           name="kode_kategori" 
                           class="form-control @error('kode_kategori') is-invalid @enderror"
                           value="{{ old('kode_kategori') }}"
                           placeholder="Contoh: TRN, KNS, TKT"
                           maxlength="20"
                           required>
                    <small style="color: #999; margin-top: 4px; display: block;">
                        Kode unik maksimal 20 karakter
                    </small>
                    @error('kode_kategori')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="nama_kategori">Nama Kategori <span class="required">*</span></label>
                    <input type="text" 
                           id="nama_kategori" 
                           name="nama_kategori" 
                           class="form-control @error('nama_kategori') is-invalid @enderror"
                           value="{{ old('nama_kategori') }}"
                           placeholder="Contoh: Transportasi, Konsumsi, Tiket"
                           maxlength="100"
                           required>
                    @error('nama_kategori')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Konfigurasi Accurate -->
                <div class="form-group">
                    <label for="default_coa_id">Default COA (Auto-Mapping)</label>
                    <select id="default_coa_id" 
                            name="default_coa_id" 
                            class="form-control @error('default_coa_id') is-invalid @enderror">
                        <option value="">-- Pilih Akun COA (Opsional) --</option>
                        @foreach($coas as $coa)
                            <option value="{{ $coa->coa_id }}" {{ old('default_coa_id') == $coa->coa_id ? 'selected' : '' }}>
                                {{ $coa->kode_coa }} - {{ $coa->nama_coa }}
                            </option>
                        @endforeach
                    </select>
                    <small style="color: #666; margin-top: 4px; display: block;">
                        Akun ini akan otomatis terpilih saat proses verifikasi finance.
                    </small>
                    @error('default_coa_id')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="is_active">Status <span class="required">*</span></label>
                    <select id="is_active" 
                            name="is_active" 
                            class="form-control @error('is_active') is-invalid @enderror"
                            required>
                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>
                            Aktif
                        </option>
                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>
                            Nonaktif
                        </option>
                    </select>
                    @error('is_active')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Deskripsi Full Width -->
                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="deskripsi">Deskripsi (Opsional)</label>
                    <textarea id="deskripsi" 
                              name="deskripsi" 
                              class="form-control @error('deskripsi') is-invalid @enderror"
                              placeholder="Deskripsi kategori biaya..."
                              rows="3"
                              maxlength="500">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="{{ route('finance.masterdata.kategori_biaya.index') }}" class="btn-modern btn-modern-secondary">
                    Batal
                </a>
                <button type="submit" class="btn-modern btn-modern-primary">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                    Simpan Kategori
                </button>
            </div>
        </form>
            </section>
        </div>
    </div>
</div>

@push('styles')
<style>
    .form-group {
        margin-bottom: 1.5rem;
        display: flex;
        flex-direction: column;
    }

    .form-group label {
        font-size: 0.95rem;
        font-weight: 600;
        color: #2c394e;
        margin-bottom: 0.5rem;
    }

    .form-control {
        padding: 0.75rem 1rem;
        border: 1px solid #e5eaf2;
        border-radius: 0.75rem;
        font-size: 0.95rem;
        font-family: inherit;
        transition: all 0.3s ease;
        background: white;
    }

    .form-control:focus {
        outline: none;
        border-color: #5575a2;
        box-shadow: 0 0 0 3px rgba(85, 117, 162, 0.1);
    }

    .form-control.is-invalid {
        border-color: #ef4444;
        background-color: rgba(239, 68, 68, 0.05);
    }

    .form-control.is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .form-group small {
        color: #6b7280;
        font-size: 0.85rem;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e5eaf2;
        justify-content: flex-end;
    }

    @media (max-width: 768px) {
        .form-actions {
            flex-direction: column;
        }
    }
</style>
@endpush

@endsection
