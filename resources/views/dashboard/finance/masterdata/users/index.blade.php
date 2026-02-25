@extends('layouts.app')

@section('title', 'Kelola Pengguna')

@push('styles')
<style>
    .filter-form-finance {
        display: grid;
        grid-template-columns: 1fr 220px 200px auto;
        gap: 1rem;
        align-items: flex-end;
    }
    
    .modern-section {
        padding: 1.25rem !important;
    }

    .data-table-wrapper {
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        overflow-x: auto;
        overflow-y: hidden;
        -webkit-overflow-scrolling: touch;
        background: white;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04);
        margin-top: 1rem;
    }

    .data-table {
        table-layout: fixed !important;
        width: 100% !important;
        min-width: 1120px;
        border-collapse: separate;
        border-spacing: 0;
    }

    .data-table th {
        padding: 1rem 0.75rem !important;
        background: #f8fafc;
        text-transform: uppercase;
        font-size: 0.65rem;
        letter-spacing: 0.075em;
        font-weight: 700;
        color: #64748b;
        border-bottom: 2px solid #f1f5f9;
    }
    
    .data-table td {
        padding: 1rem 0.75rem !important;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
        word-wrap: break-word;
        transition: all 0.2s ease;
    }

    .data-table tbody tr:hover td {
        background-color: #f8fafc;
    }

    /* Column Widths */
    .col-nama { width: auto; min-width: 200px; }
    .col-kontak { width: 220px; }
    .col-role { width: 110px; text-align: center !important; }
    .col-dept { width: 150px; }
    .col-rekening { width: 200px; }
    .col-status { width: 100px; text-align: center !important; }
    .col-aksi { width: 150px; text-align: center !important; }

    .badge-status-active {
        background: #ecfdf5 !important;
        color: #059669 !important;
        border: 1px solid #d1fae5 !important;
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        padding: 6px 12px !important;
        border-radius: 50px !important;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .badge-status-active::before {
        content: '';
        width: 6px;
        height: 6px;
        background: currentColor;
        border-radius: 50%;
    }

    .badge-status-inactive {
        background: #fef2f2 !important;
        color: #dc2626 !important;
        border: 1px solid #fee2e2 !important;
        font-size: 0.7rem !important;
        font-weight: 700 !important;
        padding: 6px 12px !important;
        border-radius: 50px !important;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }

    .role-badge {
        padding: 6px 10px;
        border-radius: 50px;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        display: inline-block;
    }

    .role-atasan { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
    .role-pegawai { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
    .role-finance { background: #fff1f2; color: #be123c; border: 1px solid #ffe4e6; }

    .email-text {
        font-family: 'JetBrains Mono', monospace;
        color: #475569;
        font-size: 0.75rem;
        word-break: break-all;
        letter-spacing: -0.01em;
        margin-bottom: 4px;
    }

    .icon-leading {
        margin-right: 8px;
    }

    .icon-leading-sm {
        width: 16px;
        height: 16px;
        margin-right: 6px;
    }

    .user-name {
        font-weight: 700;
        color: #1e293b;
        font-size: 0.95rem;
    }

    .user-subtext {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 2px;
    }

    .contact-phone {
        font-size: 0.75rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .dept-text {
        font-weight: 500;
        color: #475569;
    }

    .bank-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.85rem;
    }

    .bank-number {
        font-family: 'JetBrains Mono', monospace;
        font-size: 0.75rem;
        color: #64748b;
    }

    .bank-empty {
        color: #94a3b8;
        font-style: italic;
        font-size: 0.75rem;
    }

    .btn-action-warn {
        color: #f59e0b;
    }

    .action-buttons-centered {
        display: flex;
        justify-content: center;
        gap: 0.6rem;
    }

    .section-title, .section-subtitle {
        text-align: left !important;
    }

</style>
@endpush

@section('content')
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <x-page-header title="Kelola Pengguna" subtitle="Tambah, edit, atau hapus pengguna sistem" :showNotification="true" :showProfile="true" />

        <div class="dashboard-content">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-value">{{ $stats['total'] }}</div>
                        <div class="stat-label">Total Pengguna</div>
                    </div>
                    <div class="stat-icon primary-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                </div>
                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-value">{{ $stats['aktif'] }}</div>
                        <div class="stat-label">Aktif</div>
                    </div>
                    <div class="stat-icon success-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                </div>
                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-value">{{ $stats['nonaktif'] }}</div>
                        <div class="stat-label">Nonaktif</div>
                    </div>
                    <div class="stat-icon warning-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="15" y1="9" x2="9" y2="15"></line>
                            <line x1="9" y1="9" x2="15" y2="15"></line>
                        </svg>
                    </div>
                </div>
            </div>

            <section class="modern-section">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Daftar Pengguna</h2>
                        <p class="section-subtitle">Kelola akses dan informasi pengguna sistem</p>
                    </div>
                    <div class="header-actions">
                        <a href="{{ route('finance.masterdata.users.create') }}" class="btn-modern btn-modern-primary">
                            <svg class="icon-leading" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Tambah Pengguna
                        </a>
                    </div>
                </div>

                <div class="filter-container">
                    <form id="filterForm" action="{{ route('finance.masterdata.users.index') }}" method="GET" class="filter-form-finance">
                        <!-- Search -->
                        <div class="filter-group-pegawai">
                            <label for="searchInput" class="filter-label-pegawai">Pencarian</label>
                            <div class="search-group">
                                <div class="search-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                </div>
                                <input type="text" name="search" id="searchInput" value="{{ request('search') }}" class="filter-input-pegawai search-input" placeholder="Nama atau email..." autocomplete="search">
                            </div>
                        </div>

                        <!-- Departemen -->
                        <div class="filter-group-pegawai">
                            <label for="deptInput" class="filter-label-pegawai">Departemen</label>
                            <select name="departemen_id" id="deptInput" class="filter-input-pegawai" onchange="this.form.submit()">
                                <option value="">Semua Departemen</option>
                                @foreach($departemens as $dept)
                                    <option value="{{ $dept->departemen_id }}" {{ request('departemen_id') == $dept->departemen_id ? 'selected' : '' }}>
                                        {{ $dept->nama_departemen }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="filter-group-pegawai">
                            <label for="statusInput" class="filter-label-pegawai">Status</label>
                            <select name="status" id="statusInput" class="filter-input-pegawai" onchange="this.form.submit()">
                                <option value="">Semua Status</option>
                                <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>

                        <!-- Actions -->
                        <div class="filter-actions-pegawai">
                            <button type="submit" class="btn-modern btn-modern-secondary btn-modern-sm">
                                <svg class="icon-leading-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                </svg>
                                Filter
                            </button>
                            <a href="{{ route('finance.masterdata.users.index') }}" class="btn-reset-pegawai" title="Reset Filter">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </a>
                        </div>
                    </form>
                </div>

                <div id="tableContainer">
                    @if ($users->isEmpty())
                        <div class="empty-state">
                            <div class="empty-state-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="9" cy="7" r="4"></circle>
                                </svg>
                            </div>
                            <div class="empty-state-title">Belum ada pengguna</div>
                            <p>Mulai dengan menambahkan pengguna baru ke sistem</p>
                        </div>
                    @else
                        <div class="data-table-wrapper">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th class="col-nama">Nama Lengkap</th>
                                        <th class="col-kontak">Kontak & Email</th>
                                        <th class="col-role">Role</th>
                                        <th class="col-dept">Departemen</th>
                                        <th class="col-rekening">Informasi Rekening</th>
                                        <th class="col-status">Status</th>
                                        <th class="col-aksi">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $user)
                                        <tr>
                                            <td data-label="Nama Lengkap" class="col-nama">
                                                <div class="user-name">{{ $user->name }}</div>
                                                <div class="user-subtext">{{ $user->jabatan }}</div>
                                            </td>
                                            <td data-label="Kontak" class="col-kontak">
                                                <div class="email-text">{{ $user->email }}</div>
                                                @if($user->nomor_telepon)
                                                    <div class="contact-phone">
                                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                                        {{ $user->nomor_telepon }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td data-label="Role" class="col-role">
                                                <span class="role-badge role-{{ $user->role }}">
                                                    {{ $user->role }}
                                                </span>
                                            </td>
                                            <td data-label="Departemen" class="col-dept">
                                                <div class="dept-text">{{ $user->departemen->nama_departemen ?? '-' }}</div>
                                            </td>
                                            <td data-label="Rekening" class="col-rekening">
                                                @if($user->nama_bank || $user->nomor_rekening)
                                                    <div class="bank-name">{{ $user->nama_bank ?? 'Bank -' }}</div>
                                                    <div class="bank-number">{{ $user->nomor_rekening ?? '-' }}</div>
                                                @else
                                                    <span class="bank-empty">Data belum lengkap</span>
                                                @endif
                                            </td>
                                            <td data-label="Status" class="col-status">
                                                @if ($user->is_active)
                                                    <span class="badge-status-active">Aktif</span>
                                                @else
                                                    <span class="badge-status-inactive">Nonaktif</span>
                                                @endif
                                            </td>
                                            <td data-label="Aksi" class="col-aksi">
                                                <div class="action-buttons-centered">
                                                    <a href="{{ route('finance.masterdata.users.edit', $user->id) }}" class="btn-action-icon" title="Edit">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                        </svg>
                                                    </a>
                                                    <button type="button" class="btn-action-icon btn-action-warn" onclick="confirmResetPassword('{{ $user->id }}', '{{ $user->name }}')" title="Reset Password">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                                            <path d="M12 8v4"></path>
                                                            <path d="M12 16h.01"></path>
                                                        </svg>
                                                    </button>
                                                    @if ($user->id !== auth()->id())
                                                        <button type="button" class="btn-action-icon btn-action-delete" onclick="confirmDeleteUser('{{ $user->id }}', '{{ $user->name }}')" title="Hapus">
                                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination-wrapper">
                            {{ $users->links('components.pagination') }}
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</div>

<div id="resetPasswordModal" class="modal" data-app-modal="1" style="display: none; position: fixed; inset: 0; width: 100%; height: 100%; background: rgba(15,23,42,0.45); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px);">
    <div class="modal-content" style="max-width: 500px; background: white; border-radius: 24px; box-shadow: 0 20px 50px rgba(0, 0, 0, 0.2); position: relative; animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1); border: 1px solid rgba(255,255,255,0.8);">
        <div class="modal-header" style="padding: 24px 30px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h2 class="modal-title" style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #1e293b; letter-spacing: -0.025em;">Reset Password</h2>
            <button type="button" class="modal-close" onclick="closeResetModal()" style="background: #f1f5f9; border: none; font-size: 20px; cursor: pointer; color: #64748b; padding: 0; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.2s ease;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
        <div class="modal-body" style="padding: 30px; min-height: 120px;">
            <div id="resetStatus"></div>
            <div id="tempPasswordDisplay" style="display: none;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding: 12px 16px; background: #f0fdf4; border-radius: 12px; border: 1px solid #dcfce7;">
                    <div style="background: #22c55e; color: white; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <p style="margin: 0; font-size: 0.9rem; color: #166534; font-weight: 500;">Password berhasil direset!</p>
                </div>

                <div style="background: #f8fafc; padding: 24px; border-radius: 20px; border: 1px solid #e2e8f0; margin-bottom: 20px; position: relative; overflow: hidden;">
                    <div style="position: absolute; top: 0; right: 0; padding: 10px; opacity: 0.05;">
                        <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 4px; font-weight: 700;">Pengguna</div>
                        <div id="displayUserName" style="font-weight: 700; font-size: 1.1rem; color: #1e293b;"></div>
                        <div id="displayUserEmail" style="font-size: 0.85rem; color: #64748b; font-family: 'JetBrains Mono', monospace;"></div>
                    </div>
                    
                    <div>
                        <div style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 8px; font-weight: 700;">Password Baru</div>
                        <div style="display: flex; gap: 10px; align-items: stretch;">
                            <div style="flex: 1; position: relative;">
                                <code id="tempPassword" style="display: block; width: 100%; padding: 14px 16px; background: white; border-radius: 12px; font-size: 1.1rem; letter-spacing: 0.1em; border: 1px solid #cbd5e1; font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #1e293b; box-shadow: inset 0 2px 4px rgba(0,0,0,0.05);"></code>
                            </div>
                            <button type="button" class="btn-modern btn-modern-primary" onclick="copyPassword()" style="padding: 0 20px; border-radius: 12px; font-weight: 600;">
                                Copy
                            </button>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px; align-items: flex-start; padding: 16px; background: #fffbeb; border-radius: 12px; border: 1px solid #fef3c7;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" style="margin-top: 2px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <p style="font-size: 0.85rem; color: #92400e; margin: 0; line-height: 1.5;">
                        <strong>Penting:</strong> Berikan password ini kepada pengguna. Mereka akan diminta untuk mengubahnya pada saat login pertama kali.
                    </p>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="padding: 20px 30px; border-top: 1px solid #f1f5f9; text-align: right; background: #f8fafc; border-bottom-left-radius: 24px; border-bottom-right-radius: 24px;">
            <button type="button" class="btn-modern btn-modern-secondary" onclick="closeResetModal()" style="border-radius: 50px; padding: 10px 24px; font-weight: 600;">Tutup</button>
        </div>
    </div>
</div>

<form id="singleDeleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    (function() {
        // Use block scope to avoid redeclaration errors with Livewire navigation
        let searchTimer;
        const searchInput = document.getElementById('searchInput');
        const filterForm = document.getElementById('filterForm');
        window.__resetModalState = window.__resetModalState || { prevOverflow: '' };

        const ensureResetModalMounted = () => {
            const modal = document.getElementById('resetPasswordModal');
            if (!modal) return null;
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            return modal;
        };

        const openResetModal = () => {
            const modal = ensureResetModalMounted();
            if (!modal) return;

            modal.style.display = 'flex';
            window.__resetModalState.prevOverflow = document.body.style.overflow || '';
            document.body.style.overflow = 'hidden';
        };

        if (searchInput) {
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    filterForm.submit();
                }, 800); // 800ms delay
            });
        }

        // Attach functions to window object to keep them accessible globally if called from onclick
        window.confirmResetPassword = function(userId, userName) {
            window.openConfirmModal(() => {
                performPasswordReset(userId, userName);
            }, 'Reset Password', `Yakin ingin mereset password untuk ${userName}?`);
        };

        window.performPasswordReset = function(userId, userName) {
            const resetModal = ensureResetModalMounted();
            const resetStatus = document.getElementById('resetStatus');
            const tempPasswordDisplay = document.getElementById('tempPasswordDisplay');
            
            if (resetModal) openResetModal();
            if (resetStatus) {
                resetStatus.innerHTML = `
                    <div style="text-align: center; padding: 40px 20px;">
                        <div class="loading-spinner" style="width: 40px; height: 40px; border: 3px solid #f3f3f3; border-top: 3px solid #425d87; border-radius: 50%; margin: 0 auto 20px; animation: spin 1s linear infinite;"></div>
                        <h3 style="margin: 0 0 8px; font-size: 1.1rem; color: #1e293b;">Memproses Reset</h3>
                        <p style="font-size: 0.9rem; color: #64748b; margin: 0;">Sedang membuat password baru untuk ${userName}...</p>
                    </div>
                    <style>
                        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
                    </style>
                `;
            }
            if (tempPasswordDisplay) tempPasswordDisplay.style.display = 'none';
            
            fetch(`/finance/masterdata/users/${userId}/reset-password`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (resetStatus) resetStatus.innerHTML = '';
                    document.getElementById('displayUserName').textContent = data.user.name;
                    document.getElementById('displayUserEmail').textContent = data.user.email;
                    document.getElementById('tempPassword').textContent = data.password;
                    if (tempPasswordDisplay) tempPasswordDisplay.style.display = 'block';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message || 'Gagal mereset password',
                        confirmButtonText: 'Tutup',
                        ...window.swalConfig
                    });
                    closeResetModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Kesalahan',
                    text: 'Terjadi kesalahan sistem saat mencoba mereset password.',
                    confirmButtonText: 'Tutup',
                    ...window.swalConfig
                });
                closeResetModal();
            });
        };

        window.closeResetModal = function() {
            const modal = document.getElementById('resetPasswordModal');
            if (modal) {
                modal.style.display = 'none';
                document.body.style.overflow = window.__resetModalState.prevOverflow || '';
                window.__resetModalState.prevOverflow = '';
            }
        };

        window.copyPassword = function() {
            const passwordElement = document.getElementById('tempPassword');
            if (!passwordElement) return;
            const password = passwordElement.textContent;
            navigator.clipboard.writeText(password).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Disalin!',
                    text: 'Password telah disalin ke clipboard',
                    timer: 1500,
                    showConfirmButton: false,
                    ...window.swalConfig
                });
            });
        };

        window.confirmDeleteUser = function(userId, userName) {
            window.openConfirmModal(() => {
                const form = document.getElementById('singleDeleteForm');
                if (form) {
                    form.action = `/finance/masterdata/users/${userId}`;
                    form.submit();
                }
            }, 'Hapus Pengguna', `Apakah Anda yakin ingin menghapus pengguna ${userName}? Tindakan ini tidak dapat dibatalkan.`);
        };

        if (!window.__resetModalBehaviorBound) {
            window.__resetModalBehaviorBound = true;

            document.addEventListener('click', function(event) {
                const modal = document.getElementById('resetPasswordModal');
                if (!modal) return;
                if (event.target === modal) {
                    closeResetModal();
                }
            });

            document.addEventListener('keydown', function(event) {
                const modal = document.getElementById('resetPasswordModal');
                if (!modal) return;
                if (event.key === 'Escape' && modal.style.display === 'flex') {
                    closeResetModal();
                }
            });
        }

        const initResetModal = () => {
            ensureResetModalMounted();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initResetModal);
        } else {
            initResetModal();
        }
        document.addEventListener('livewire:navigated', initResetModal);
    })();
</script>
@endpush
