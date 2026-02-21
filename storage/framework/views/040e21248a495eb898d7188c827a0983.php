<?php $__env->startSection('title', 'Kelola COA'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .filter-form-finance {
        display: grid;
        grid-template-columns: 1fr 180px 180px auto;
        gap: 1rem;
        align-items: flex-end;
    }
    
    .modern-section {
        padding: 1.25rem !important;
    }

    .data-table {
        table-layout: fixed !important;
        width: 100% !important;
    }

    .data-table th {
        padding: 0.75rem 0.5rem !important;
        white-space: nowrap;
        background: #f8fafc;
        text-transform: uppercase;
        font-size: 0.65rem;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: #64748b;
        border-bottom: 2px solid #f1f5f9;
    }
    
    .data-table td {
        padding: 0.75rem 0.5rem !important;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
        word-wrap: break-word;
    }

    .col-kode { width: 100px; }
    .col-nama { width: 220px; }
    .col-catatan { width: auto; min-width: 150px; }
    .col-status { width: 90px; text-align: center !important; }
    .col-tipe { width: 120px; text-align: center !important; }
    .col-sub { width: 90px; text-align: center !important; }
    .col-saldo { width: 160px; text-align: right !important; }
    .col-as-of { width: 110px; text-align: center !important; }
    .col-sync { width: 140px; text-align: center !important; }

    .data-table th.col-tipe, .data-table th.col-as-of, .data-table th.col-sub, .data-table th.col-status, .data-table th.col-sync {
        text-align: center !important;
    }
    .data-table th.col-saldo {
        text-align: right !important;
    }

    .badge-status-active {
        background: rgba(16, 185, 129, 0.1) !important;
        color: #059669 !important;
        border: 1px solid rgba(16, 185, 129, 0.2) !important;
        font-size: 0.7rem !important;
        font-weight: 600 !important;
        padding: 4px 10px !important;
        border-radius: 50px !important;
    }

    .badge-status-inactive {
        background: rgba(239, 68, 68, 0.1) !important;
        color: #dc2626 !important;
        border: 1px solid rgba(239, 68, 68, 0.2) !important;
        font-size: 0.7rem !important;
        font-weight: 600 !important;
        padding: 4px 10px !important;
        border-radius: 50px !important;
    }

    .code-badge {
        font-family: 'JetBrains Mono', 'Fira Code', monospace;
        background: #f8fafc;
        color: #475569;
        padding: 4px 10px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .child-row {
        background-color: #fafbfc;
    }
    .child-row td {
        padding-top: 0.6rem;
        padding-bottom: 0.6rem;
        border-top: none;
    }
    .child-label {
        color: #64748b;
        padding-left: 0.5rem;
        font-size: 0.9em;
        position: relative;
    }
    .child-label::before {
        content: "";
        position: absolute;
        left: -12px;
        top: -15px;
        width: 12px;
        height: 25px;
        border-left: 2px solid #e2e8f0;
        border-bottom: 2px solid #e2e8f0;
        border-bottom-left-radius: 4px;
    }
    .parent-row {
        background-color: #ffffff;
    }
    .parent-row:has(+ .child-row) {
        border-bottom: none;
    }

    .amount-text {
        font-family: 'Inter', sans-serif;
        font-weight: 700;
        color: #1e293b;
        font-size: 0.95rem;
    }

    @media (max-width: 1200px) {
        .filter-form-finance {
            grid-template-columns: 1fr 1fr;
        }
        .filter-actions-pegawai {
            grid-column: span 2;
        }
    }
    @media (max-width: 768px) {
        .filter-form-finance {
            grid-template-columns: 1fr;
        }
        .filter-actions-pegawai {
            grid-column: 1;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Kelola COA','subtitle' => 'Daftar Chart of Accounts yang disinkronkan dari Accurate','showNotification' => true,'showProfile' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Kelola COA','subtitle' => 'Daftar Chart of Accounts yang disinkronkan dari Accurate','showNotification' => true,'showProfile' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $attributes = $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e)): ?>
<?php $component = $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e; ?>
<?php unset($__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e); ?>
<?php endif; ?>

        <div class="dashboard-content">
            <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value"><?php echo e(\App\Models\COA::count()); ?></div>
                    <div class="stat-label">Total COA</div>
                </div>
                <div class="stat-icon primary-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2v20M2 12h20"></path>
                    </svg>
                </div>
            </div>
            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value"><?php echo e(\App\Models\COA::where('is_active', true)->count()); ?></div>
                    <div class="stat-label">Aktif</div>
                </div>
                <div class="stat-icon success-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
            </div>
            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value"><?php echo e(\App\Models\COA::where('is_active', false)->count()); ?></div>
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
                    <h2 class="section-title">Daftar COA</h2>
                    <p class="section-subtitle">Data disinkronkan secara otomatis dari Accurate</p>
                </div>
                <div class="header-actions">
                    <form action="<?php echo e(route('finance.masterdata.coa.sync')); ?>" method="POST" class="d-inline" id="syncForm">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="force_full_sync" id="forceFullSync" value="0">
                        <button type="button" class="btn-modern btn-modern-primary" id="syncButton" style="margin-right: 8px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
                                <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
                                <path d="M3 3v5h5"></path>
                                <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"></path>
                                <path d="M16 21h5v-5"></path>
                            </svg>
                            <span id="syncButtonText">Sync Accurate Sekarang</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="filter-container">
                <form id="filterForm" action="<?php echo e(route('finance.masterdata.coa.index')); ?>" method="GET" class="filter-form-finance">
                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Pencarian</label>
                        <div class="search-group">
                            <div class="search-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <input type="text" name="search" id="searchInput" value="<?php echo e(request('search')); ?>" class="filter-input-pegawai search-input" placeholder="Kode, nama COA...">
                        </div>
                    </div>

                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Tipe Akun</label>
                        <select name="tipe_akun" id="tipeAkunInput" class="filter-input-pegawai">
                            <option value="">Semua Tipe</option>
                            <option value="asset" <?php echo e(request('tipe_akun') == 'asset' ? 'selected' : ''); ?>>Asset</option>
                            <option value="liability" <?php echo e(request('tipe_akun') == 'liability' ? 'selected' : ''); ?>>Liability</option>
                            <option value="equity" <?php echo e(request('tipe_akun') == 'equity' ? 'selected' : ''); ?>>Equity</option>
                            <option value="revenue" <?php echo e(request('tipe_akun') == 'revenue' ? 'selected' : ''); ?>>Revenue</option>
                            <option value="expense" <?php echo e(request('tipe_akun') == 'expense' ? 'selected' : ''); ?>>Expense</option>
                        </select>
                    </div>

                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Status</label>
                        <select name="status" id="statusInput" class="filter-input-pegawai">
                            <option value="">Semua Status</option>
                            <option value="aktif" <?php echo e(request('status') == 'aktif' ? 'selected' : ''); ?>>Aktif</option>
                            <option value="nonaktif" <?php echo e(request('status') == 'nonaktif' ? 'selected' : ''); ?>>Tidak Aktif</option>
                        </select>
                    </div>

                    <div class="filter-actions-pegawai">
                        <button type="submit" class="btn-modern btn-modern-secondary btn-modern-sm">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            Filter
                        </button>
                        <a href="<?php echo e(route('finance.masterdata.coa.index')); ?>" class="btn-reset-pegawai" title="Reset Filter">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </div>
                </form>
            </div>

            <div id="tableContainer">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($coas->isEmpty()): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="3" y1="9" x2="21" y2="9"></line>
                                <line x1="9" y1="21" x2="9" y2="9"></line>
                            </svg>
                        </div>
                        <div class="empty-state-title">Data COA Kosong</div>
                        <p>Silakan lakukan sinkronisasi untuk mengambil data dari Accurate</p>
                    </div>
                <?php else: ?>
                    <div class="data-table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="col-kode">Kode Perkiraan</th>
                                    <th class="col-nama">Nama</th>
                                    <th class="col-catatan">Deskripsi</th>
                                    <th class="col-status">Status</th>
                                    <th class="col-tipe">Tipe Akun</th>
                                    <th class="col-sub">Sub-Akun</th>
                                    <th class="col-saldo">Saldo</th>
                                    <th class="col-as-of">Per Tanggal</th>
                                    <th class="col-sync">Sinkron</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $coas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="<?php echo e($coa->children->count() > 0 ? 'parent-row' : ''); ?>">
                                        <td data-label="Kode Perkiraan" class="col-kode">
                                            <code class="code-badge"><?php echo e($coa->kode_coa); ?></code>
                                        </td>
                                        <td data-label="Nama" class="col-nama">
                                            <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;"><?php echo e($coa->nama_coa); ?></div>
                                        </td>
                                        <td data-label="Deskripsi" class="col-catatan">
                                            <div style="font-size: 0.85rem; color: #64748b; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo e($coa->deskripsi); ?>">
                                                <?php echo e($coa->deskripsi ?? '-'); ?>

                                            </div>
                                        </td>
                                        <td data-label="Status" class="col-status">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($coa->is_active): ?>
                                                <span class="badge-status-active">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge-status-inactive">Nonaktif</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td data-label="Tipe Akun" class="col-tipe">
                                            <span class="meta-badge" style="text-transform: uppercase; background: rgba(66, 93, 135, 0.08); color: #425d87; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600;"><?php echo e($coa->tipe_akun); ?></span>
                                        </td>
                                        <td data-label="Sub-Akun" class="col-sub">
                                            <span class="meta-badge" style="background: rgba(66, 93, 135, 0.08); color: #425d87; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 600;"><?php echo e($coa->children->count()); ?> sub</span>
                                        </td>
                                        <td data-label="Saldo" class="col-saldo">
                                            <span class="amount-text"><?php echo e($coa->currency_code ?? 'IDR'); ?> <?php echo e(number_format($coa->saldo ?? 0, 0, ',', '.')); ?></span>
                                        </td>
                                        <td data-label="Per Tanggal" class="col-as-of">
                                            <span style="font-size: 0.85rem; color: #64748b; font-weight: 500;">
                                                <?php echo e($coa->as_of_date ? $coa->as_of_date->format('d/m/Y') : '-'); ?>

                                            </span>
                                        </td>
                                        <td data-label="Sinkron" class="col-sync">
                                            <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                                    <path d="M3 21v-5h5"></path>
                                                    <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                                    <path d="M16 3h5v5"></path>
                                                </svg>
                                                <span style="font-size: 0.75rem; color: #64748b; font-weight: 500;"><?php echo e($coa->last_sync_at ? $coa->last_sync_at->diffForHumans() : '-'); ?></span>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($coa->children->count() > 0): ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $coa->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <tr class="child-row">
                                                <td data-label="Kode Perkiraan" class="col-kode">
                                                    <code class="code-badge" style="margin-left: 1rem; color: #64748b;"><?php echo e($child->kode_coa); ?></code>
                                                </td>
                                                <td data-label="Nama" class="col-nama">
                                                    <span class="child-label" style="display: flex; align-items: center; gap: 0.5rem;">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #cbd5e1;">
                                                            <polyline points="9 10 4 15 9 20"></polyline>
                                                            <path d="M20 4v7a4 4 0 0 1-4 4H4"></path>
                                                        </svg>
                                                        <?php echo e($child->nama_coa); ?>

                                                    </span>
                                                </td>
                                                <td data-label="Deskripsi" class="col-catatan">
                                                    <div style="font-size: 0.8rem; color: #94a3b8; max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo e($child->deskripsi); ?>">
                                                        <?php echo e($child->deskripsi ?? '-'); ?>

                                                    </div>
                                                </td>
                                                <td data-label="Status" class="col-status">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($child->is_active): ?>
                                                        <span class="badge-status-active">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge-status-inactive">Non-aktif</span>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </td>
                                                <td data-label="Tipe Akun" class="col-tipe">
                                                    <span class="meta-badge" style="text-transform: uppercase; background: rgba(66, 93, 135, 0.05); color: #64748b; padding: 3px 8px; border-radius: 5px; font-size: 0.7rem; font-weight: 600;"><?php echo e($child->tipe_akun); ?></span>
                                                </td>
                                                <td data-label="Sub-Akun" class="col-sub">
                                                    <span style="font-size: 0.7rem; color: #94a3b8;">-</span>
                                                </td>
                                                <td data-label="Saldo" class="col-saldo">
                                                    <span class="amount-text" style="font-size: 0.85rem; color: #64748b;"><?php echo e($child->currency_code ?? 'IDR'); ?> <?php echo e(number_format($child->saldo ?? 0, 0, ',', '.')); ?></span>
                                                </td>
                                                <td data-label="Per Tanggal" class="col-as-of">
                                                    <span style="font-size: 0.8rem; color: #64748b; font-weight: 500;">
                                                        <?php echo e($child->as_of_date ? $child->as_of_date->format('d/m/Y') : '-'); ?>

                                                    </span>
                                                </td>
                                                <td data-label="Sinkron" class="col-sync">
                                                    <div style="display: flex; align-items: center; justify-content: center; gap: 6px; color: #cbd5e1;">
                                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.6;">
                                                            <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                                            <path d="M3 21v-5h5"></path>
                                                            <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                                            <path d="M16 3h5v5"></path>
                                                        </svg>
                                                        <span style="font-size: 0.7rem; font-weight: 500;"><?php echo e($child->last_sync_at ? $child->last_sync_at->diffForHumans() : '-'); ?></span>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-wrapper">
                        <?php echo e($coas->links('components.pagination')); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>
    </div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/finance-master.js')); ?>"></script>
<script>
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/dashboard/finance/masterdata/coa/index.blade.php ENDPATH**/ ?>