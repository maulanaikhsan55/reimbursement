@props([
    'user',
    'uploadField' => 'foto_profil',
])

@php
    $fullName = trim((string) ($user->name ?? 'User'));
    $initial = strtoupper(substr($fullName !== '' ? $fullName : 'U', 0, 1));
    $roleLabel = $user->role_label ?? ucfirst((string) ($user->role ?? 'User'));
    $departemen = $user->departemen->nama_departemen ?? 'Belum diatur';
    $joinedAt = $user->created_at ? $user->created_at->format('d M Y') : '-';
    $updatedAt = $user->updated_at ? $user->updated_at->format('d M Y, H:i') : '-';
    $phone = $user->masked_nomor_telepon ?: ($user->nomor_telepon ?: '-');
    $rekeningMasked = $user->masked_nomor_rekening ?? $user->nomor_rekening ?? '-';
    $rekening = trim(($user->nama_bank ? $user->nama_bank.' - ' : '').$rekeningMasked);
    $atasanName = $user->atasan->name ?? null;
    $photoUrl = $user->foto_profil_url;

    $summaryItems = [
        ['label' => 'Departemen', 'value' => $departemen],
        ['label' => 'Role', 'value' => $roleLabel],
        ['label' => 'Telepon', 'value' => $phone],
        ['label' => 'Rekening', 'value' => $rekening],
        ['label' => 'Terdaftar', 'value' => $joinedAt],
        ['label' => 'Update Terakhir', 'value' => $updatedAt],
    ];

    if ($atasanName) {
        $summaryItems[] = ['label' => 'Atasan', 'value' => $atasanName];
    }
@endphp

<div class="profile-summary-card" data-profile-summary-card>
    <div class="profile-summary-card__head">
        <div class="profile-summary-card__avatar-wrap">
            <div class="profile-summary-card__avatar">
                <img
                    src="{{ $photoUrl ?? '' }}"
                    alt="Foto profil {{ $fullName }}"
                    class="profile-summary-card__avatar-img {{ $photoUrl ? 'is-visible' : '' }}"
                    data-profile-photo-preview
                    @if(! $photoUrl) hidden @endif
                >
                <span
                    class="profile-summary-card__avatar-fallback {{ $photoUrl ? 'is-hidden' : '' }}"
                    data-profile-photo-fallback
                    @if($photoUrl) hidden @endif
                >{{ $initial }}</span>
            </div>

            <label class="profile-summary-card__upload-btn">
                <input
                    type="file"
                    name="{{ $uploadField }}"
                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                    class="profile-summary-card__upload-input"
                    data-profile-photo-input
                >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
                <span data-profile-upload-btn-label>{{ $photoUrl ? 'Ganti Foto' : 'Upload Foto' }}</span>
            </label>
            <button type="button" class="profile-summary-card__camera-btn" data-profile-camera-open>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                    <circle cx="12" cy="13" r="4"></circle>
                </svg>
                <span>Gunakan Kamera</span>
            </button>
            <input type="hidden" name="hapus_foto_profil" value="0" data-profile-photo-remove-flag>

            <button
                type="button"
                class="profile-summary-card__remove-btn {{ $photoUrl ? '' : 'is-hidden' }}"
                data-profile-photo-remove-btn
                @if(! $photoUrl) hidden @endif
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                    <path d="M10 11v6"></path>
                    <path d="M14 11v6"></path>
                    <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path>
                </svg>
                <span>Hapus Foto</span>
            </button>

            <p class="profile-summary-card__upload-help">JPG/PNG/WEBP, maksimal 2MB</p>
            @error($uploadField)
                <small class="form-error">{{ $message }}</small>
            @enderror
        </div>

        <div class="profile-summary-card__identity">
            <div class="profile-summary-card__eyebrow">Ringkasan Profil</div>
            <h3 class="profile-summary-card__name">{{ $fullName }}</h3>
            <div class="profile-summary-card__chips">
                <span class="profile-summary-card__chip">{{ $roleLabel }}</span>
                <span class="profile-summary-card__chip profile-summary-card__chip--muted">
                    {{ $user->is_active ? 'Akun Aktif' : 'Akun Nonaktif' }}
                </span>
            </div>
            <div class="profile-summary-card__email">{{ $user->email }}</div>
        </div>
    </div>

    <div class="profile-summary-card__grid">
        @foreach($summaryItems as $item)
            <div class="profile-summary-card__item">
                <div class="profile-summary-card__label">{{ $item['label'] }}</div>
                <div class="profile-summary-card__value" title="{{ $item['value'] }}">{{ $item['value'] }}</div>
            </div>
        @endforeach
    </div>
</div>

<div class="profile-photo-cropper-modal" data-profile-cropper-modal hidden aria-hidden="true">
    <div class="profile-photo-cropper-modal__backdrop" data-profile-cropper-cancel></div>
    <div class="profile-photo-cropper-modal__dialog" role="dialog" aria-modal="true" aria-label="Atur foto profil">
        <div class="profile-photo-cropper-modal__header">
            <div>
                <h4 class="profile-photo-cropper-modal__title">Atur Foto Profil</h4>
                <p class="profile-photo-cropper-modal__subtitle">Geser dan zoom agar wajah pas di tengah</p>
            </div>
            <button type="button" class="profile-photo-cropper-modal__icon-btn" data-profile-cropper-cancel aria-label="Tutup">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="profile-photo-cropper-modal__body">
            <div class="profile-photo-cropper-modal__workspace">
                <div class="profile-photo-cropper-modal__canvas-wrap">
                    <img src="" alt="Preview crop foto profil" data-profile-cropper-image>
                </div>

                <div class="profile-photo-cropper-modal__live-preview-panel">
                    <div class="profile-photo-cropper-modal__live-preview-label">Preview Avatar</div>
                    <div class="profile-photo-cropper-modal__live-preview">
                        <img src="" alt="Preview avatar" data-profile-cropper-live-preview hidden>
                    </div>
                    <div class="profile-photo-cropper-modal__live-preview-help">Hasil akhir tampilan avatar</div>
                </div>
            </div>

            <div class="profile-photo-cropper-modal__controls">
                <div class="profile-photo-cropper-modal__controls-top">
                    <label class="profile-photo-cropper-modal__zoom-label" for="profilePhotoCropZoom-{{ $user->id ?? 'user' }}">Zoom</label>
                    <div class="profile-photo-cropper-modal__tool-actions">
                        <button type="button" class="profile-photo-cropper-modal__tool-btn" data-profile-cropper-rotate-left aria-label="Putar kiri">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <polyline points="1 4 1 10 7 10"></polyline>
                                <path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"></path>
                            </svg>
                        </button>
                        <button type="button" class="profile-photo-cropper-modal__tool-btn" data-profile-cropper-rotate-right aria-label="Putar kanan">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <path d="M20.49 15a9 9 0 1 1-2.13-9.36L23 10"></path>
                            </svg>
                        </button>
                        <button type="button" class="profile-photo-cropper-modal__reset-btn" data-profile-cropper-reset>Reset Crop</button>
                    </div>
                </div>
                <input id="profilePhotoCropZoom-{{ $user->id ?? 'user' }}" type="range" min="0" max="120" value="0" step="1" data-profile-cropper-zoom>
                <small class="profile-photo-cropper-modal__validation" data-profile-cropper-validation hidden></small>
            </div>
        </div>

        <div class="profile-photo-cropper-modal__footer">
            <button type="button" class="btn-modern btn-modern-secondary profile-photo-cropper-modal__btn" data-profile-cropper-cancel>Batal</button>
            <button type="button" class="btn-modern btn-modern-primary profile-photo-cropper-modal__btn" data-profile-cropper-apply>Gunakan Foto</button>
        </div>
    </div>
</div>

<div class="profile-photo-camera-modal" data-profile-camera-modal hidden aria-hidden="true">
    <div class="profile-photo-camera-modal__backdrop" data-profile-camera-cancel></div>
    <div class="profile-photo-camera-modal__dialog" role="dialog" aria-modal="true" aria-label="Ambil foto profil dengan kamera">
        <div class="profile-photo-camera-modal__header">
            <div>
                <h4 class="profile-photo-camera-modal__title">Ambil Foto Profil</h4>
                <p class="profile-photo-camera-modal__subtitle">Posisikan wajah di tengah, lalu ambil foto</p>
            </div>
            <button type="button" class="profile-photo-camera-modal__icon-btn" data-profile-camera-cancel aria-label="Tutup kamera">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="profile-photo-camera-modal__body">
            <div class="profile-photo-camera-modal__viewport">
                <video autoplay playsinline muted data-profile-camera-video></video>
                <div class="profile-photo-camera-modal__mask" aria-hidden="true"></div>
                <div class="profile-photo-camera-modal__placeholder" data-profile-camera-placeholder>
                    Kamera belum aktif
                </div>
            </div>
            <small class="profile-photo-camera-modal__status" data-profile-camera-status>Izinkan akses kamera untuk mengambil foto.</small>
        </div>

        <div class="profile-photo-camera-modal__footer">
            <button type="button" class="btn-modern btn-modern-secondary profile-photo-camera-modal__btn" data-profile-camera-cancel>Batal</button>
            <button type="button" class="btn-modern btn-modern-primary profile-photo-camera-modal__btn" data-profile-camera-capture disabled>Ambil Foto</button>
        </div>
    </div>
</div>

@once
    @push('styles')
        <style>
            .profile-summary-card {
                margin-bottom: 1rem;
                border-radius: 1.15rem;
                border: 1px solid rgba(66, 93, 135, 0.14);
                background:
                    radial-gradient(circle at 8% 14%, rgba(255, 255, 255, 0.38), transparent 42%),
                    linear-gradient(135deg, rgba(63, 95, 148, 0.08) 0%, rgba(99, 123, 170, 0.08) 45%, rgba(66, 93, 135, 0.04) 100%),
                    #fff;
                box-shadow: 0 14px 28px rgba(22, 37, 62, 0.07);
                padding: 1rem;
            }

            .profile-summary-card__head {
                display: grid;
                grid-template-columns: auto minmax(0, 1fr);
                gap: 1rem;
                align-items: center;
                margin-bottom: 0.9rem;
            }

            .profile-summary-card__avatar-wrap {
                display: grid;
                gap: 0.45rem;
                justify-items: center;
                width: 148px;
            }

            .profile-summary-card__avatar {
                width: 104px;
                height: 104px;
                border-radius: 999px;
                border: 3px solid rgba(255, 255, 255, 0.75);
                box-shadow: 0 14px 26px rgba(38, 61, 100, 0.14), inset 0 0 0 1px rgba(66, 93, 135, 0.16);
                background: linear-gradient(145deg, #5878ac 0%, #425d87 55%, #314a70 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
                overflow: hidden;
            }

            .profile-summary-card__avatar-img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: none;
            }

            .profile-summary-card__avatar-img.is-visible {
                display: block;
            }

            .profile-summary-card__avatar-fallback {
                color: #fff;
                font-size: 2rem;
                font-weight: 800;
                line-height: 1;
            }

            .profile-summary-card__avatar-fallback.is-hidden {
                display: none;
            }

            .profile-summary-card__upload-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                cursor: pointer;
                border-radius: 999px;
                border: 1px solid rgba(66, 93, 135, 0.18);
                background: rgba(255, 255, 255, 0.92);
                color: #334a70;
                font-size: 0.74rem;
                font-weight: 700;
                padding: 0.4rem 0.72rem;
                transition: all 0.18s ease;
            }

            .profile-summary-card__upload-btn:hover {
                border-color: rgba(66, 93, 135, 0.3);
                color: #263f65;
                box-shadow: 0 8px 16px rgba(22, 37, 62, 0.08);
            }

            .profile-summary-card__upload-btn svg {
                width: 14px;
                height: 14px;
                flex-shrink: 0;
            }

            .profile-summary-card__upload-input {
                display: none;
            }

            .profile-summary-card__upload-help {
                margin: 0;
                text-align: center;
                color: #71829d;
                font-size: 0.68rem;
                line-height: 1.25;
            }

            .profile-summary-card__remove-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                cursor: pointer;
                border-radius: 999px;
                border: 1px solid rgba(239, 68, 68, 0.2);
                background: rgba(255, 255, 255, 0.96);
                color: #d64040;
                font-size: 0.72rem;
                font-weight: 700;
                padding: 0.35rem 0.68rem;
                transition: all 0.18s ease;
            }

            .profile-summary-card__remove-btn:hover {
                background: #fff5f5;
                border-color: rgba(239, 68, 68, 0.32);
                box-shadow: 0 8px 16px rgba(239, 68, 68, 0.08);
            }

            .profile-summary-card__remove-btn svg {
                width: 13px;
                height: 13px;
                flex-shrink: 0;
            }

            .profile-summary-card__remove-btn.is-hidden {
                display: none;
            }

            .profile-summary-card__camera-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                cursor: pointer;
                border-radius: 999px;
                border: 1px solid rgba(66, 93, 135, 0.18);
                background: rgba(247, 250, 255, 0.96);
                color: #425d87;
                font-size: 0.73rem;
                font-weight: 700;
                padding: 0.38rem 0.72rem;
                transition: all 0.18s ease;
            }

            .profile-summary-card__camera-btn:hover {
                border-color: rgba(66, 93, 135, 0.3);
                background: #f7faff;
                box-shadow: 0 8px 16px rgba(22, 37, 62, 0.08);
            }

            .profile-summary-card__camera-btn svg {
                width: 14px;
                height: 14px;
                flex-shrink: 0;
            }

            .profile-summary-card__identity {
                min-width: 0;
                display: grid;
                gap: 0.42rem;
                align-content: center;
            }

            .profile-summary-card__eyebrow {
                color: #58729a;
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.06em;
                text-transform: uppercase;
            }

            .profile-summary-card__name {
                margin: 0;
                color: #1d2d48;
                font-size: 1.12rem;
                line-height: 1.15;
                font-weight: 800;
                word-break: break-word;
            }

            .profile-summary-card__chips {
                display: flex;
                flex-wrap: wrap;
                gap: 0.4rem;
            }

            .profile-summary-card__chip {
                display: inline-flex;
                align-items: center;
                padding: 0.2rem 0.5rem;
                border-radius: 999px;
                background: rgba(66, 93, 135, 0.1);
                color: #425d87;
                font-size: 0.71rem;
                font-weight: 700;
            }

            .profile-summary-card__chip--muted {
                background: rgba(100, 116, 139, 0.12);
                color: #5e708a;
            }

            .profile-summary-card__email {
                color: #556982;
                font-size: 0.82rem;
                font-weight: 500;
                line-height: 1.25;
                word-break: break-word;
            }

            .profile-summary-card__grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 0.7rem;
            }

            .profile-summary-card__item {
                border-radius: 0.9rem;
                border: 1px solid rgba(66, 93, 135, 0.1);
                background: rgba(255, 255, 255, 0.78);
                padding: 0.7rem 0.8rem;
                min-width: 0;
            }

            .profile-summary-card__label {
                color: #7a8da8;
                font-size: 0.7rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.04em;
                margin-bottom: 0.2rem;
            }

            .profile-summary-card__value {
                color: #253852;
                font-size: 0.82rem;
                font-weight: 700;
                line-height: 1.25;
                word-break: break-word;
            }

            .profile-photo-cropper-modal {
                position: fixed;
                inset: 0;
                z-index: 12000;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 1rem;
            }

            .profile-photo-cropper-modal.is-open {
                display: flex;
            }

            .profile-photo-cropper-modal__backdrop {
                position: absolute;
                inset: 0;
                background: rgba(15, 23, 42, 0.52);
                backdrop-filter: blur(3px);
            }

            .profile-photo-cropper-modal__dialog {
                position: relative;
                width: min(640px, 100%);
                border-radius: 1rem;
                border: 1px solid rgba(66, 93, 135, 0.14);
                background: #fff;
                box-shadow: 0 24px 50px rgba(15, 23, 42, 0.22);
                overflow: hidden;
            }

            .profile-photo-cropper-modal__header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.9rem 1rem 0.7rem;
                border-bottom: 1px solid rgba(66, 93, 135, 0.1);
            }

            .profile-photo-cropper-modal__title {
                margin: 0;
                color: #1d2d48;
                font-size: 0.98rem;
                font-weight: 800;
            }

            .profile-photo-cropper-modal__subtitle {
                margin: 0.2rem 0 0;
                color: #687c99;
                font-size: 0.76rem;
                font-weight: 500;
            }

            .profile-photo-cropper-modal__icon-btn {
                width: 34px;
                height: 34px;
                border-radius: 999px;
                border: 1px solid rgba(66, 93, 135, 0.14);
                background: #fff;
                color: #5f7393;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.18s ease;
                flex-shrink: 0;
            }

            .profile-photo-cropper-modal__icon-btn:hover {
                background: #f8fbff;
                color: #425d87;
                border-color: rgba(66, 93, 135, 0.24);
            }

            .profile-photo-cropper-modal__icon-btn svg {
                width: 16px;
                height: 16px;
            }

            .profile-photo-cropper-modal__body {
                padding: 0.95rem 1rem 0.85rem;
                display: grid;
                gap: 0.8rem;
            }

            .profile-photo-cropper-modal__workspace {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 140px;
                gap: 0.85rem;
                align-items: start;
            }

            .profile-photo-cropper-modal__canvas-wrap {
                height: min(52vh, 360px);
                background:
                    linear-gradient(45deg, #eef3fb 25%, transparent 25%),
                    linear-gradient(-45deg, #eef3fb 25%, transparent 25%),
                    linear-gradient(45deg, transparent 75%, #eef3fb 75%),
                    linear-gradient(-45deg, transparent 75%, #eef3fb 75%);
                background-size: 16px 16px;
                background-position: 0 0, 0 8px, 8px -8px, -8px 0;
                border-radius: 0.8rem;
                overflow: hidden;
                border: 1px solid rgba(66, 93, 135, 0.12);
            }

            .profile-photo-cropper-modal__live-preview-panel {
                border-radius: 0.85rem;
                border: 1px solid rgba(66, 93, 135, 0.1);
                background: rgba(248, 251, 255, 0.85);
                padding: 0.7rem;
                display: grid;
                gap: 0.45rem;
                justify-items: center;
            }

            .profile-photo-cropper-modal__live-preview-label {
                color: #4f6482;
                font-size: 0.7rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }

            .profile-photo-cropper-modal__live-preview {
                width: 88px;
                height: 88px;
                border-radius: 50%;
                overflow: hidden;
                background: linear-gradient(145deg, #5878ac 0%, #425d87 55%, #314a70 100%);
                border: 3px solid rgba(255, 255, 255, 0.9);
                box-shadow: 0 10px 20px rgba(22, 37, 62, 0.12);
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-weight: 700;
            }

            .profile-photo-cropper-modal__live-preview img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }

            .profile-photo-cropper-modal__live-preview-help {
                color: #7488a4;
                font-size: 0.68rem;
                text-align: center;
                line-height: 1.25;
            }

            .profile-photo-cropper-modal__canvas-wrap img {
                display: block;
                width: 100%;
                max-width: 100%;
            }

            .profile-photo-cropper-modal__controls {
                display: grid;
                gap: 0.35rem;
            }

            .profile-photo-cropper-modal__controls-top {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.5rem;
            }

            .profile-photo-cropper-modal__tool-actions {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
            }

            .profile-photo-cropper-modal__zoom-label {
                color: #4e6280;
                font-size: 0.74rem;
                font-weight: 700;
            }

            .profile-photo-cropper-modal__tool-btn {
                width: 30px;
                height: 30px;
                border-radius: 999px;
                border: 1px solid rgba(66, 93, 135, 0.16);
                background: #fff;
                color: #425d87;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.18s ease;
                padding: 0;
            }

            .profile-photo-cropper-modal__tool-btn:hover {
                background: #f7faff;
                border-color: rgba(66, 93, 135, 0.28);
            }

            .profile-photo-cropper-modal__tool-btn svg {
                width: 14px;
                height: 14px;
            }

            .profile-photo-cropper-modal__reset-btn {
                border: 1px solid rgba(66, 93, 135, 0.16);
                background: #fff;
                color: #425d87;
                border-radius: 999px;
                padding: 0.28rem 0.55rem;
                font-size: 0.68rem;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.18s ease;
            }

            .profile-photo-cropper-modal__reset-btn:hover {
                background: #f7faff;
                border-color: rgba(66, 93, 135, 0.28);
            }

            .profile-photo-cropper-modal__controls input[type="range"] {
                width: 100%;
                accent-color: #425d87;
            }

            .profile-photo-cropper-modal__validation {
                color: #dc2626;
                font-size: 0.72rem;
                font-weight: 600;
                line-height: 1.2;
            }

            .profile-photo-cropper-modal .cropper-view-box,
            .profile-photo-cropper-modal .cropper-face {
                border-radius: 50%;
            }

            .profile-photo-cropper-modal .cropper-view-box {
                outline: 2px solid rgba(255, 255, 255, 0.7);
                outline-color: rgba(255, 255, 255, 0.75);
            }

            .profile-photo-cropper-modal .cropper-dashed,
            .profile-photo-cropper-modal .cropper-center {
                display: none !important;
            }

            .profile-photo-cropper-modal__footer {
                display: flex;
                justify-content: flex-end;
                gap: 0.6rem;
                padding: 0.8rem 1rem 1rem;
                border-top: 1px solid rgba(66, 93, 135, 0.1);
                background: rgba(248, 251, 255, 0.65);
            }

            .profile-photo-cropper-modal__btn {
                min-width: 112px;
            }

            .profile-photo-camera-modal {
                position: fixed;
                inset: 0;
                z-index: 11980;
                display: none;
                align-items: center;
                justify-content: center;
                padding: 1rem;
            }

            .profile-photo-camera-modal.is-open {
                display: flex;
            }

            .profile-photo-camera-modal__backdrop {
                position: absolute;
                inset: 0;
                background: rgba(15, 23, 42, 0.52);
                backdrop-filter: blur(3px);
            }

            .profile-photo-camera-modal__dialog {
                position: relative;
                width: min(560px, 100%);
                border-radius: 1rem;
                border: 1px solid rgba(66, 93, 135, 0.14);
                background: #fff;
                box-shadow: 0 24px 50px rgba(15, 23, 42, 0.22);
                overflow: hidden;
            }

            .profile-photo-camera-modal__header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.9rem 1rem 0.7rem;
                border-bottom: 1px solid rgba(66, 93, 135, 0.1);
            }

            .profile-photo-camera-modal__title {
                margin: 0;
                color: #1d2d48;
                font-size: 0.98rem;
                font-weight: 800;
            }

            .profile-photo-camera-modal__subtitle {
                margin: 0.2rem 0 0;
                color: #687c99;
                font-size: 0.76rem;
                font-weight: 500;
            }

            .profile-photo-camera-modal__icon-btn {
                width: 34px;
                height: 34px;
                border-radius: 999px;
                border: 1px solid rgba(66, 93, 135, 0.14);
                background: #fff;
                color: #5f7393;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                transition: all 0.18s ease;
                flex-shrink: 0;
            }

            .profile-photo-camera-modal__icon-btn:hover {
                background: #f8fbff;
                color: #425d87;
                border-color: rgba(66, 93, 135, 0.24);
            }

            .profile-photo-camera-modal__icon-btn svg {
                width: 16px;
                height: 16px;
            }

            .profile-photo-camera-modal__body {
                padding: 0.95rem 1rem 0.85rem;
                display: grid;
                gap: 0.6rem;
            }

            .profile-photo-camera-modal__viewport {
                position: relative;
                border-radius: 0.9rem;
                overflow: hidden;
                border: 1px solid rgba(66, 93, 135, 0.12);
                background: linear-gradient(180deg, #eef4fd 0%, #e7effa 100%);
                aspect-ratio: 4 / 3;
                display: grid;
                place-items: center;
            }

            .profile-photo-camera-modal__viewport video {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: none;
                transform: scaleX(-1);
            }

            .profile-photo-camera-modal__viewport.is-ready video {
                display: block;
            }

            .profile-photo-camera-modal__placeholder {
                color: #6f829f;
                font-size: 0.84rem;
                font-weight: 600;
                text-align: center;
                padding: 0.75rem;
            }

            .profile-photo-camera-modal__viewport.is-ready .profile-photo-camera-modal__placeholder {
                display: none;
            }

            .profile-photo-camera-modal__mask {
                position: absolute;
                inset: 0;
                pointer-events: none;
                background:
                    radial-gradient(circle at center, transparent 30%, rgba(15, 23, 42, 0.18) 31%, rgba(15, 23, 42, 0.18) 43%, transparent 44%);
                display: none;
            }

            .profile-photo-camera-modal__viewport.is-ready .profile-photo-camera-modal__mask {
                display: block;
            }

            .profile-photo-camera-modal__status {
                color: #6c819f;
                font-size: 0.74rem;
                font-weight: 600;
                line-height: 1.25;
            }

            .profile-photo-camera-modal__status.is-error {
                color: #dc2626;
            }

            .profile-photo-camera-modal__footer {
                display: flex;
                justify-content: flex-end;
                gap: 0.6rem;
                padding: 0.8rem 1rem 1rem;
                border-top: 1px solid rgba(66, 93, 135, 0.1);
                background: rgba(248, 251, 255, 0.65);
            }

            .profile-photo-camera-modal__btn {
                min-width: 112px;
            }

            .profile-photo-camera-modal__btn:disabled {
                opacity: 0.55;
                cursor: not-allowed;
                transform: none !important;
                box-shadow: none !important;
            }

            .btn-modern-secondary {
                background: #fff;
                color: #425d87;
                border: 1px solid rgba(66, 93, 135, 0.2);
                box-shadow: none;
            }

            .btn-modern-secondary:hover {
                background: #f7faff;
                border-color: rgba(66, 93, 135, 0.3);
                transform: translateY(-1px);
            }

            @media (max-width: 1024px) {
                .profile-summary-card__grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 768px) {
                .profile-summary-card {
                    padding: 0.85rem;
                    border-radius: 1rem;
                }

                .profile-summary-card__head {
                    grid-template-columns: 1fr;
                    gap: 0.75rem;
                    align-items: start;
                }

                .profile-summary-card__avatar-wrap {
                    width: 100%;
                    justify-items: start;
                }

                .profile-summary-card__avatar {
                    width: 88px;
                    height: 88px;
                }

                .profile-summary-card__avatar-fallback {
                    font-size: 1.7rem;
                }

                .profile-summary-card__name {
                    font-size: 1rem;
                }

                .profile-summary-card__grid {
                    grid-template-columns: 1fr;
                    gap: 0.55rem;
                }

                .profile-photo-cropper-modal {
                    padding: 0.6rem;
                }

                .profile-photo-cropper-modal__dialog {
                    border-radius: 0.9rem;
                }

                .profile-photo-cropper-modal__workspace {
                    grid-template-columns: 1fr;
                }

                .profile-photo-cropper-modal__live-preview-panel {
                    width: 100%;
                    grid-template-columns: auto auto 1fr;
                    align-items: center;
                    justify-items: start;
                }

                .profile-photo-cropper-modal__live-preview {
                    width: 64px;
                    height: 64px;
                }

                .profile-photo-cropper-modal__live-preview-label,
                .profile-photo-cropper-modal__live-preview-help {
                    text-align: left;
                }

                .profile-photo-cropper-modal__controls-top {
                    align-items: flex-start;
                    flex-direction: column;
                }

                .profile-photo-cropper-modal__tool-actions {
                    width: 100%;
                    justify-content: space-between;
                }

                .profile-photo-cropper-modal__canvas-wrap {
                    height: min(48vh, 300px);
                }

                .profile-photo-cropper-modal__footer {
                    flex-direction: column-reverse;
                }

                .profile-photo-cropper-modal__btn {
                    width: 100%;
                }

                .profile-photo-camera-modal {
                    padding: 0.6rem;
                }

                .profile-photo-camera-modal__dialog {
                    border-radius: 0.9rem;
                }

                .profile-photo-camera-modal__footer {
                    flex-direction: column-reverse;
                }

                .profile-photo-camera-modal__btn {
                    width: 100%;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script data-navigate-once>
            function initProfilePhotoPreviewInputs() {
                document.querySelectorAll('[data-profile-summary-card]').forEach((card) => {
                    if (!(card instanceof HTMLElement) || card.dataset.cropperBound === '1') return;
                    card.dataset.cropperBound = '1';

                    const input = card.querySelector('[data-profile-photo-input]');
                    const previewImg = card.querySelector('[data-profile-photo-preview]');
                    const fallback = card.querySelector('[data-profile-photo-fallback]');
                    const modal = card.parentElement?.querySelector('[data-profile-cropper-modal]') || card.nextElementSibling;
                    const cropperImg = modal?.querySelector('[data-profile-cropper-image]');
                    const zoomInput = modal?.querySelector('[data-profile-cropper-zoom]');
                    const applyBtn = modal?.querySelector('[data-profile-cropper-apply]');
                    const resetBtn = modal?.querySelector('[data-profile-cropper-reset]');
                    const rotateLeftBtn = modal?.querySelector('[data-profile-cropper-rotate-left]');
                    const rotateRightBtn = modal?.querySelector('[data-profile-cropper-rotate-right]');
                    const livePreviewImg = modal?.querySelector('[data-profile-cropper-live-preview]');
                    const validationEl = modal?.querySelector('[data-profile-cropper-validation]');
                    const cancelBtns = modal ? Array.from(modal.querySelectorAll('[data-profile-cropper-cancel]')) : [];
                    const removeBtn = card.querySelector('[data-profile-photo-remove-btn]');
                    const removeFlag = card.querySelector('[data-profile-photo-remove-flag]');
                    const uploadBtnLabel = card.querySelector('[data-profile-upload-btn-label]');
                    const cameraOpenBtn = card.querySelector('[data-profile-camera-open]');
                    const cameraModal = card.parentElement?.querySelector('[data-profile-camera-modal]');
                    const cameraVideo = cameraModal?.querySelector('[data-profile-camera-video]');
                    const cameraCaptureBtn = cameraModal?.querySelector('[data-profile-camera-capture]');
                    const cameraStatus = cameraModal?.querySelector('[data-profile-camera-status]');
                    const cameraViewport = cameraModal?.querySelector('.profile-photo-camera-modal__viewport');
                    const cameraCancelBtns = cameraModal ? Array.from(cameraModal.querySelectorAll('[data-profile-camera-cancel]')) : [];

                    if (!(input instanceof HTMLInputElement) || !(previewImg instanceof HTMLImageElement) || !(modal instanceof HTMLElement) || !(cropperImg instanceof HTMLImageElement)) {
                        return;
                    }

                    let cropper = null;
                    let sourceObjectUrl = null;
                    let lastAppliedPreviewUrl = null;
                    let zoomValue = 0;
                    let previewFrameId = 0;
                    let cameraStream = null;
                    const MIN_DIMENSION = 128;
                    const resolveCropperConstructor = () => {
                        const candidate = window.ProfilePhotoCropper ?? window.Cropper;
                        if (typeof candidate === 'function') return candidate;
                        if (candidate && typeof candidate.default === 'function') return candidate.default;
                        return null;
                    };
                    const hasCropperMethod = (instance, methodName) => Boolean(instance && typeof instance[methodName] === 'function');
                    const supportsCropperCanvasApi = (instance) => hasCropperMethod(instance, 'getCroppedCanvas');

                    const setRemoveButtonVisible = (visible) => {
                        if (!(removeBtn instanceof HTMLElement)) return;
                        removeBtn.hidden = !visible;
                        removeBtn.classList.toggle('is-hidden', !visible);
                    };

                    const setUploadLabel = (text) => {
                        if (uploadBtnLabel instanceof HTMLElement) {
                            uploadBtnLabel.textContent = text;
                        }
                    };

                    const setRemoveFlagValue = (value) => {
                        if (removeFlag instanceof HTMLInputElement) {
                            removeFlag.value = value ? '1' : '0';
                        }
                    };

                    const setValidationMessage = (message) => {
                        if (!(validationEl instanceof HTMLElement)) return;
                        if (message) {
                            validationEl.textContent = message;
                            validationEl.hidden = false;
                        } else {
                            validationEl.textContent = '';
                            validationEl.hidden = true;
                        }
                    };

                    const setCameraStatus = (message, isError = false) => {
                        if (!(cameraStatus instanceof HTMLElement)) return;
                        cameraStatus.textContent = message;
                        cameraStatus.classList.toggle('is-error', Boolean(isError));
                    };

                    const setCameraCaptureEnabled = (enabled) => {
                        if (!(cameraCaptureBtn instanceof HTMLButtonElement)) return;
                        cameraCaptureBtn.disabled = !enabled;
                    };

                    const updateLiveCropPreview = () => {
                        if (!cropper || !supportsCropperCanvasApi(cropper) || !(livePreviewImg instanceof HTMLImageElement)) return;
                        const canvas = cropper.getCroppedCanvas({
                            width: 160,
                            height: 160,
                            imageSmoothingEnabled: true,
                            imageSmoothingQuality: 'high',
                        });
                        if (!canvas) return;
                        livePreviewImg.src = canvas.toDataURL('image/jpeg', 0.85);
                        livePreviewImg.hidden = false;
                    };

                    const scheduleLiveCropPreview = () => {
                        if (previewFrameId) return;
                        previewFrameId = window.requestAnimationFrame(() => {
                            previewFrameId = 0;
                            updateLiveCropPreview();
                        });
                    };

                    const openModal = () => {
                        setValidationMessage('');
                        modal.hidden = false;
                        modal.setAttribute('aria-hidden', 'false');
                        modal.classList.add('is-open');
                        document.body.style.overflow = 'hidden';
                    };

                    const stopCameraStream = () => {
                        if (!cameraStream) return;
                        cameraStream.getTracks().forEach((track) => track.stop());
                        cameraStream = null;
                    };

                    const closeCameraModal = () => {
                        if (!(cameraModal instanceof HTMLElement)) return;
                        cameraModal.classList.remove('is-open');
                        cameraModal.setAttribute('aria-hidden', 'true');
                        cameraModal.hidden = true;
                        if (!modal.classList.contains('is-open')) {
                            document.body.style.overflow = '';
                        }
                        setCameraCaptureEnabled(false);
                        stopCameraStream();
                        if (cameraVideo instanceof HTMLVideoElement) {
                            cameraVideo.pause();
                            cameraVideo.srcObject = null;
                        }
                        if (cameraViewport instanceof HTMLElement) {
                            cameraViewport.classList.remove('is-ready');
                        }
                        setCameraStatus('Izinkan akses kamera untuk mengambil foto.', false);
                    };

                    const openCameraModal = async () => {
                        if (!(cameraModal instanceof HTMLElement)) return;
                        if (!(cameraVideo instanceof HTMLVideoElement)) return;

                        const hostname = String(location.hostname || '').toLowerCase();
                        const isLocalDevHost = hostname === 'localhost' || hostname === '127.0.0.1' || hostname === '::1';

                        cameraModal.hidden = false;
                        cameraModal.setAttribute('aria-hidden', 'false');
                        cameraModal.classList.add('is-open');
                        document.body.style.overflow = 'hidden';
                        setCameraCaptureEnabled(false);

                        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                            setCameraStatus('Browser ini belum mendukung akses kamera langsung.', true);
                            return;
                        }

                        if (!window.isSecureContext && !isLocalDevHost) {
                            setCameraStatus('Kamera butuh HTTPS (atau localhost/127.0.0.1). Buka ulang dari origin yang aman.', true);
                            return;
                        }

                        try {
                            setCameraStatus('Mempersiapkan kamera...');
                            stopCameraStream();
                            let stream;
                            try {
                                stream = await navigator.mediaDevices.getUserMedia({
                                    video: {
                                        facingMode: { ideal: 'user' },
                                        width: { ideal: 1280 },
                                        height: { ideal: 720 },
                                    },
                                    audio: false,
                                });
                            } catch (error) {
                                stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                            }

                            cameraStream = stream;
                            cameraVideo.srcObject = stream;
                            await cameraVideo.play();
                            if (cameraViewport instanceof HTMLElement) {
                                cameraViewport.classList.add('is-ready');
                            }
                            setCameraStatus('Kamera siap. Klik "Ambil Foto".');
                            setCameraCaptureEnabled(true);
                        } catch (error) {
                            const errorName = typeof error?.name === 'string' ? error.name : 'UnknownError';
                            const messageByError = {
                                NotAllowedError: 'Izin kamera ditolak. Klik ikon kunci di browser lalu izinkan Camera.',
                                PermissionDeniedError: 'Izin kamera ditolak. Aktifkan izin kamera di browser.',
                                NotFoundError: 'Kamera tidak ditemukan di perangkat ini.',
                                DevicesNotFoundError: 'Kamera tidak ditemukan di perangkat ini.',
                                NotReadableError: 'Kamera sedang dipakai aplikasi lain (Zoom/Meet/WhatsApp Desktop). Tutup dulu lalu coba lagi.',
                                TrackStartError: 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi kamera lain lalu coba lagi.',
                                OverconstrainedError: 'Pengaturan kamera tidak cocok. Coba ulang atau gunakan Upload Foto.',
                                ConstraintNotSatisfiedError: 'Pengaturan kamera tidak cocok. Coba ulang atau gunakan Upload Foto.',
                                SecurityError: 'Akses kamera diblokir browser. Gunakan HTTPS/localhost dan cek izin situs.',
                            };
                            setCameraStatus(messageByError[errorName] || `Akses kamera gagal (${errorName}). Cek izin browser/perangkat.`, true);
                            if (window.console && typeof console.warn === 'function') {
                                console.warn('Camera open failed:', error);
                            }
                        }
                    };

                    const closeModal = (clearSelection = false) => {
                        modal.classList.remove('is-open');
                        modal.setAttribute('aria-hidden', 'true');
                        modal.hidden = true;
                        document.body.style.overflow = '';

                        if (cropper) {
                            if (hasCropperMethod(cropper, 'destroy')) {
                                cropper.destroy();
                            }
                            cropper = null;
                        }

                        if (previewFrameId) {
                            cancelAnimationFrame(previewFrameId);
                            previewFrameId = 0;
                        }

                        if (zoomInput instanceof HTMLInputElement) {
                            zoomInput.value = '0';
                        }
                        zoomValue = 0;
                        setValidationMessage('');

                        if (sourceObjectUrl) {
                            URL.revokeObjectURL(sourceObjectUrl);
                            sourceObjectUrl = null;
                        }

                        cropperImg.src = '';
                        if (livePreviewImg instanceof HTMLImageElement) {
                            livePreviewImg.removeAttribute('src');
                            livePreviewImg.hidden = true;
                        }
                        if (clearSelection) {
                            input.value = '';
                        }
                    };

                    const applyPreview = (objectUrl) => {
                        if (lastAppliedPreviewUrl && lastAppliedPreviewUrl.startsWith('blob:')) {
                            URL.revokeObjectURL(lastAppliedPreviewUrl);
                        }
                        lastAppliedPreviewUrl = objectUrl;
                        previewImg.src = objectUrl;
                        previewImg.hidden = false;
                        previewImg.classList.add('is-visible');
                        if (fallback instanceof HTMLElement) {
                            fallback.hidden = true;
                            fallback.classList.add('is-hidden');
                        }
                        setRemoveButtonVisible(true);
                        setUploadLabel('Ganti Foto');
                        setRemoveFlagValue(false);
                    };

                    const resetToFallbackPreview = () => {
                        if (lastAppliedPreviewUrl && lastAppliedPreviewUrl.startsWith('blob:')) {
                            URL.revokeObjectURL(lastAppliedPreviewUrl);
                            lastAppliedPreviewUrl = null;
                        }
                        previewImg.removeAttribute('src');
                        previewImg.hidden = true;
                        previewImg.classList.remove('is-visible');
                        if (fallback instanceof HTMLElement) {
                            fallback.hidden = false;
                            fallback.classList.remove('is-hidden');
                        }
                        setRemoveButtonVisible(false);
                        setUploadLabel('Upload Foto');
                    };

                    const assignCroppedFileToInput = (blob, originalFile) => {
                        const extension = 'jpg';
                        const baseName = (originalFile?.name || 'profile-photo').replace(/\.[^.]+$/, '');
                        const croppedFile = new File(
                            [blob],
                            `${baseName}-avatar.${extension}`,
                            { type: 'image/jpeg', lastModified: Date.now() }
                        );

                        try {
                            const dt = new DataTransfer();
                            dt.items.add(croppedFile);
                            input.files = dt.files;
                        } catch (error) {
                            // Fallback for browsers without DataTransfer constructor support.
                        }
                    };

                    const assignFileToInput = (file) => {
                        try {
                            const dt = new DataTransfer();
                            dt.items.add(file);
                            input.files = dt.files;
                        } catch (error) {
                            // Fallback ignored; crop/apply will still preview but form may keep previous input in rare browsers.
                        }
                    };

                    const initCropperForFile = (file) => {
                        if (!file || !file.type.startsWith('image/')) return;

                        const CropperCtor = resolveCropperConstructor();
                        if (!CropperCtor) {
                            setValidationMessage('Editor foto belum siap. Refresh halaman lalu coba lagi.');
                            applyPreview(URL.createObjectURL(file));
                            return;
                        }

                        if (sourceObjectUrl) {
                            URL.revokeObjectURL(sourceObjectUrl);
                            sourceObjectUrl = null;
                        }
                        sourceObjectUrl = URL.createObjectURL(file);

                        const mountCropper = () => {
                            if (cropper) {
                                if (hasCropperMethod(cropper, 'destroy')) {
                                    cropper.destroy();
                                }
                                cropper = null;
                            }

                            cropper = new CropperCtor(cropperImg, {
                                aspectRatio: 1,
                                viewMode: 1,
                                dragMode: 'move',
                                autoCropArea: 1,
                                movable: true,
                                zoomable: true,
                                scalable: false,
                                rotatable: false,
                                guides: false,
                                center: true,
                                background: false,
                                cropBoxMovable: true,
                                cropBoxResizable: false,
                                toggleDragModeOnDblclick: false,
                                ready() {
                                    scheduleLiveCropPreview();
                                },
                                crop() {
                                    scheduleLiveCropPreview();
                                },
                                cropend() {
                                    scheduleLiveCropPreview();
                                },
                                zoom() {
                                    scheduleLiveCropPreview();
                                },
                            });

                            if (!supportsCropperCanvasApi(cropper)) {
                                setValidationMessage('Editor foto tidak kompatibel (versi Cropper salah/cache lama). Coba Ctrl+F5.');
                                if (window.console && typeof console.warn === 'function') {
                                    console.warn('Unexpected Cropper instance API:', cropper);
                                }
                            }

                            zoomValue = 0;
                            if (zoomInput instanceof HTMLInputElement) {
                                zoomInput.value = '0';
                            }
                            setValidationMessage('');
                        };

                        cropperImg.onload = () => {
                            openModal();
                            mountCropper();
                        };
                        cropperImg.src = sourceObjectUrl;
                    };

                    const validateSelectedImageMinDimensions = (file) => new Promise((resolve) => {
                        if (!file || !file.type.startsWith('image/')) {
                            resolve(false);
                            return;
                        }
                        const probeUrl = URL.createObjectURL(file);
                        const probe = new Image();
                        probe.onload = () => {
                            const ok = probe.naturalWidth >= MIN_DIMENSION && probe.naturalHeight >= MIN_DIMENSION;
                            URL.revokeObjectURL(probeUrl);
                            resolve(ok);
                        };
                        probe.onerror = () => {
                            URL.revokeObjectURL(probeUrl);
                            resolve(false);
                        };
                        probe.src = probeUrl;
                    });

                    const handleSelectedProfilePhotoFile = async (file) => {
                        if (!file) return;

                        const isValidSize = await validateSelectedImageMinDimensions(file);
                        if (!isValidSize) {
                            input.value = '';
                            setValidationMessage(`Ukuran foto terlalu kecil. Minimal ${MIN_DIMENSION}x${MIN_DIMENSION}px.`);
                            return false;
                        }

                        setRemoveFlagValue(false);
                        setValidationMessage('');
                        initCropperForFile(file);
                        return true;
                    };

                    input.addEventListener('change', async () => {
                        const file = input.files && input.files[0] ? input.files[0] : null;
                        if (!file) return;
                        await handleSelectedProfilePhotoFile(file);
                    });

                    if (zoomInput instanceof HTMLInputElement) {
                        zoomInput.addEventListener('input', () => {
                            if (!cropper || !hasCropperMethod(cropper, 'zoom')) return;
                            const next = Number(zoomInput.value || 0);
                            const delta = (next - zoomValue) / 100;
                            cropper.zoom(delta);
                            zoomValue = next;
                        });
                    }

                    if (resetBtn instanceof HTMLButtonElement) {
                        resetBtn.addEventListener('click', () => {
                            if (!cropper || !hasCropperMethod(cropper, 'reset')) return;
                            cropper.reset();
                            zoomValue = 0;
                            if (zoomInput instanceof HTMLInputElement) {
                                zoomInput.value = '0';
                            }
                            scheduleLiveCropPreview();
                        });
                    }

                    if (rotateLeftBtn instanceof HTMLButtonElement) {
                        rotateLeftBtn.addEventListener('click', () => {
                            if (!cropper || !hasCropperMethod(cropper, 'rotate')) {
                                setValidationMessage('Fitur rotate belum tersedia. Refresh halaman (Ctrl+F5) lalu coba lagi.');
                                return;
                            }
                            cropper.rotate(-90);
                            scheduleLiveCropPreview();
                        });
                    }

                    if (rotateRightBtn instanceof HTMLButtonElement) {
                        rotateRightBtn.addEventListener('click', () => {
                            if (!cropper || !hasCropperMethod(cropper, 'rotate')) {
                                setValidationMessage('Fitur rotate belum tersedia. Refresh halaman (Ctrl+F5) lalu coba lagi.');
                                return;
                            }
                            cropper.rotate(90);
                            scheduleLiveCropPreview();
                        });
                    }

                    if (applyBtn instanceof HTMLButtonElement) {
                        applyBtn.addEventListener('click', () => {
                            if (!cropper) {
                                closeModal();
                                return;
                            }
                            if (!supportsCropperCanvasApi(cropper)) {
                                setValidationMessage('Editor foto belum siap dipakai (cache lama/versi tidak cocok). Coba Ctrl+F5.');
                                return;
                            }

                            const selectedFile = input.files && input.files[0] ? input.files[0] : null;
                            const canvas = cropper.getCroppedCanvas({
                                width: 512,
                                height: 512,
                                imageSmoothingEnabled: true,
                                imageSmoothingQuality: 'high',
                            });

                            if (!canvas) return;

                            applyBtn.disabled = true;
                            canvas.toBlob((blob) => {
                                applyBtn.disabled = false;
                                if (!blob) return;

                                assignCroppedFileToInput(blob, selectedFile);
                                applyPreview(URL.createObjectURL(blob));
                                closeModal(false);
                            }, 'image/jpeg', 0.92);
                        });
                    }

                    cancelBtns.forEach((btn) => {
                        if (!(btn instanceof HTMLElement) || btn.dataset.boundCropperCancel === '1') return;
                        btn.dataset.boundCropperCancel = '1';
                        btn.addEventListener('click', () => closeModal(true));
                    });

                    if (removeBtn instanceof HTMLButtonElement) {
                        removeBtn.addEventListener('click', () => {
                            input.value = '';
                            setRemoveFlagValue(true);
                            setValidationMessage('');
                            resetToFallbackPreview();
                        });
                    }

                    if (cameraOpenBtn instanceof HTMLButtonElement) {
                        cameraOpenBtn.addEventListener('click', () => {
                            openCameraModal();
                        });
                    }

                    if (cameraCaptureBtn instanceof HTMLButtonElement && cameraVideo instanceof HTMLVideoElement) {
                        cameraCaptureBtn.addEventListener('click', async () => {
                            if (!cameraStream) {
                                setCameraStatus('Kamera belum siap. Coba buka kamera lagi.', true);
                                return;
                            }

                            const vw = cameraVideo.videoWidth || 0;
                            const vh = cameraVideo.videoHeight || 0;
                            if (vw < 1 || vh < 1) {
                                setCameraStatus('Gagal membaca frame kamera. Coba lagi.', true);
                                return;
                            }

                            cameraCaptureBtn.disabled = true;
                            setCameraStatus('Mengambil foto...');
                            try {
                                const canvas = document.createElement('canvas');
                                canvas.width = vw;
                                canvas.height = vh;
                                const ctx = canvas.getContext('2d');
                                if (!ctx) throw new Error('Canvas context unavailable');

                                ctx.save();
                                ctx.translate(vw, 0);
                                ctx.scale(-1, 1); // mirror to match preview
                                ctx.drawImage(cameraVideo, 0, 0, vw, vh);
                                ctx.restore();

                                const blob = await new Promise((resolve) => {
                                    canvas.toBlob(resolve, 'image/jpeg', 0.94);
                                });
                                if (!(blob instanceof Blob)) throw new Error('Failed to capture image');

                                const capturedFile = new File([blob], `camera-profile-${Date.now()}.jpg`, {
                                    type: 'image/jpeg',
                                    lastModified: Date.now(),
                                });

                                assignFileToInput(capturedFile);
                                closeCameraModal();
                                await handleSelectedProfilePhotoFile(capturedFile);
                            } catch (error) {
                                setCameraStatus('Gagal mengambil foto dari kamera.', true);
                                setCameraCaptureEnabled(Boolean(cameraStream));
                            } finally {
                                if (cameraModal instanceof HTMLElement && cameraModal.classList.contains('is-open')) {
                                    setCameraCaptureEnabled(Boolean(cameraStream));
                                }
                            }
                        });
                    }

                    cameraCancelBtns.forEach((btn) => {
                        if (!(btn instanceof HTMLElement) || btn.dataset.boundCameraCancel === '1') return;
                        btn.dataset.boundCameraCancel = '1';
                        btn.addEventListener('click', () => closeCameraModal());
                    });

                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
                            closeModal(true);
                        }
                        if (event.key === 'Escape' && cameraModal instanceof HTMLElement && cameraModal.classList.contains('is-open')) {
                            closeCameraModal();
                        }
                    });
                });
            }

            document.addEventListener('DOMContentLoaded', initProfilePhotoPreviewInputs);
            document.addEventListener('livewire:navigated', initProfilePhotoPreviewInputs);
        </script>
    @endpush
@endonce
