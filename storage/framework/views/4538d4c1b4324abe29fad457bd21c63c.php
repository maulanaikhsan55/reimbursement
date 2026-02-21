<?php $__env->startSection('title', 'Kelola Departemen'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .filter-form-finance {
        display: grid;
        grid-template-columns: 1fr auto auto auto;
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

    /* Column Widths & Alignment */
    .col-kode { width: 80px; }
    .col-nama { width: 220px; }
    .col-deskripsi { width: auto; min-width: 200px; }
    .col-status { width: 90px; text-align: center !important; }
    .col-users { width: 80px; text-align: center !important; }
    .col-budget { width: 150px; text-align: right !important; }
    .col-usage { width: 150px; text-align: right !important; }
    .col-sync { width: 140px; text-align: center !important; }
    .col-actions { width: 70px; text-align: center !important; }

    .data-table th.col-status, 
    .data-table th.col-actions, 
    .data-table th.col-users, 
    .data-table th.col-sync {
        text-align: center !important;
    }

    .data-table th.col-budget, 
    .data-table th.col-usage {
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

    .budget-info-banner {
        margin: 1rem 0;
        padding: 0.9rem 1rem;
        border: 1px solid #dbeafe;
        border-left: 4px solid #3b82f6;
        border-radius: 0.85rem;
        background: #f8fbff;
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.05);
    }

    .budget-info-icon {
        color: #3b82f6;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .budget-info-text {
        flex: 1;
        font-size: 0.9rem;
        line-height: 1.5;
        color: #334155;
    }

    .budget-info-close {
        border: 0;
        background: #eef2ff;
        color: #475569;
        width: 30px;
        height: 30px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        flex-shrink: 0;
    }

    .budget-info-close:hover {
        background: #dbeafe;
        color: #1e3a8a;
    }

    @media (max-width: 768px) {
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Kelola Departemen','subtitle' => 'Daftar departemen organisasi yang disinkronkan dari Accurate','showNotification' => true,'showProfile' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Kelola Departemen','subtitle' => 'Daftar departemen organisasi yang disinkronkan dari Accurate','showNotification' => true,'showProfile' => true]); ?>
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
                    <div class="stat-value"><?php echo e($departemen->count()); ?></div>
                    <div class="stat-label">Total Departemen</div>
                </div>
                <div class="stat-icon primary-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                </div>
            </div>
            <div class="stat-card modern">
                <div class="stat-left">
                    <div class="stat-value"><?php echo e($departemen->where('users_count', '>', 0)->count()); ?></div>
                    <div class="stat-label">Dengan Pengguna</div>
                </div>
                <div class="stat-icon success-icon">
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
                    <div class="stat-value"><?php echo e($departemen->where('users_count', 0)->count()); ?></div>
                    <div class="stat-label">Tanpa Pengguna</div>
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
                    <h2 class="section-title">Daftar Departemen</h2>
                    <p class="section-subtitle">Data disinkronkan secara otomatis dari Accurate</p>
                </div>
                <div class="header-actions">
                    <form id="syncForm" action="<?php echo e(route('finance.masterdata.departemen.sync')); ?>" method="POST" class="d-inline">
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
                <form id="filterForm" action="<?php echo e(route('finance.masterdata.departemen.index')); ?>" method="GET" class="filter-form-finance">
                    <!-- Search -->
                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Pencarian Departemen</label>
                        <div class="search-group">
                            <div class="search-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <input type="text" name="search" id="searchInput" value="<?php echo e(request('search')); ?>" class="filter-input-pegawai search-input" placeholder="Ketik nama atau kode departemen...">
                        </div>
                    </div>

                    <!-- Month Filter -->
                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Bulan</label>
                        <select name="month" id="monthInput" class="filter-input-pegawai">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = range(1, 12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($m); ?>" <?php echo e($selectedMonth == $m ? 'selected' : ''); ?>>
                                    <?php echo e(\Carbon\Carbon::create()->month($m)->isoFormat('MMMM')); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>

                    <!-- Year Filter -->
                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Tahun</label>
                        <select name="year" id="yearInput" class="filter-input-pegawai">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = range(now()->year - 2, now()->year + 1); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $y): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($y); ?>" <?php echo e($selectedYear == $y ? 'selected' : ''); ?>>
                                    <?php echo e($y); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="filter-actions-pegawai">
                        <button type="submit" class="btn-modern btn-modern-secondary btn-modern-sm">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                            </svg>
                            Filter
                        </button>
                        <a href="<?php echo e(route('finance.masterdata.departemen.index')); ?>" class="btn-reset-pegawai" title="Reset Filter">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </div>
                </form>
            </div>

            <div class="budget-info-banner" id="budgetInfoBanner">
                <svg class="budget-info-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="16" x2="12" y2="12"></line>
                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                </svg>
                <div class="budget-info-text">
                    <strong>Info:</strong> Budget limit berlaku per bulan. Realisasi dan Sisa Budget dihitung otomatis berdasarkan transaksi pada periode <strong><?php echo e(\Carbon\Carbon::create()->month($selectedMonth)->isoFormat('MMMM')); ?> <?php echo e($selectedYear); ?></strong>.
                </div>
                <button type="button" class="budget-info-close" id="budgetInfoClose" aria-label="Tutup informasi budget" title="Tutup">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <div id="tableContainer">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($departemen->isEmpty()): ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                            </svg>
                        </div>
                        <div class="empty-state-title">Belum ada departemen</div>
                        <p>Silakan lakukan sinkronisasi untuk mengambil data dari Accurate</p>
                    </div>
                <?php else: ?>
                    <div class="data-table-wrapper">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th class="col-kode">Kode</th>
                                    <th class="col-nama">Nama Departemen</th>
                                    <th class="col-deskripsi">Deskripsi</th>
                                    <th class="col-status">Status</th>
                                    <th class="col-users">Users</th>
                                    <th class="col-budget">Budget/Bln</th>
                                    <th class="col-usage">Realisasi & Sisa</th>
                                    <th class="col-sync">Sinkron</th>
                                    <th class="col-actions">Aksi</th>
                                </tr>
                            </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $departemen; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $percentage = $dept->budget_limit > 0 ? ($dept->current_usage / $dept->budget_limit) * 100 : 0;
                                            $usageColor = $percentage > 100 ? '#e11d48' : ($percentage > 80 ? '#f59e0b' : '#10b981');
                                        ?>
                                        <tr>
                                            <td data-label="Kode" class="col-kode">
                                                <code class="code-badge"><?php echo e($dept->kode_departemen); ?></code>
                                            </td>
                                            <td data-label="Nama Departemen" class="col-nama">
                                                <div style="font-weight: 700; color: #1e293b; font-size: 0.95rem;"><?php echo e($dept->nama_departemen); ?></div>
                                            </td>
                                            <td data-label="Deskripsi" class="col-deskripsi">
                                                <div style="font-size: 0.8rem; color: #64748b;"><?php echo e($dept->deskripsi ?? '-'); ?></div>
                                            </td>
                                            <td data-label="Status" class="col-status">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dept->is_active): ?>
                                                    <span class="badge-status-active">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge-status-inactive">Nonaktif</span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td data-label="Users" class="col-users">
                                                <div style="display: flex; justify-content: center;">
                                                    <span class="badge-status-active" style="background: rgba(59, 130, 246, 0.1) !important; color: #2563eb !important; border: 1px solid rgba(59, 130, 246, 0.2) !important; min-width: 32px; display: inline-flex; justify-content: center;">
                                                        <?php echo e($dept->users_count); ?>

                                                    </span>
                                                </div>
                                            </td>
                                            <td data-label="Budget/Bln" class="col-budget">
                                                <div style="font-weight: 700; color: #1e293b;">
                                                    Rp <?php echo e(number_format($dept->budget_limit, 0, ',', '.')); ?>

                                                </div>
                                            </td>
                                            <td data-label="Realisasi & Sisa" class="col-usage">
                                                <?php
                                                    $sisa = $dept->budget_limit - $dept->current_usage;
                                                    $sisaColor = $sisa < 0 ? '#e11d48' : '#64748b';
                                                ?>
                                                <div style="font-weight: 700; color: <?php echo e($usageColor); ?>;">
                                                    Rp <?php echo e(number_format($dept->current_usage, 0, ',', '.')); ?>

                                                </div>
                                                <div style="font-size: 0.75rem; color: <?php echo e($sisaColor); ?>; font-weight: 600;">
                                                    Sisa: Rp <?php echo e(number_format($sisa, 0, ',', '.')); ?>

                                                </div>
                                            </td>
                                            <td data-label="Sinkron" class="col-sync">
                                                <div style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                                        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                                        <path d="M3 21v-5h5"></path>
                                                        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                                        <path d="M16 3h5v5"></path>
                                                    </svg>
                                                    <span style="font-size: 0.75rem; color: #64748b; font-weight: 500;">
                                                        <?php echo e($dept->last_sync_at ? $dept->last_sync_at->diffForHumans() : '-'); ?>

                                                    </span>
                                                </div>
                                            </td>
                                            <td class="col-actions">
                                                <button type="button" class="btn-modern btn-modern-secondary btn-modern-sm" onclick="openEditBudgetModal('<?php echo e($dept->departemen_id); ?>', '<?php echo e($dept->nama_departemen); ?>', '<?php echo e($dept->budget_limit); ?>', '<?php echo e($dept->deskripsi); ?>')" style="padding: 6px;">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                    </svg>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="pagination-wrapper">
                            <?php echo e($departemen->links('components.pagination')); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Edit Budget Modal -->
<div id="editBudgetModal" class="modal" style="display: none; position: fixed; inset: 0; z-index: 10000; background: rgba(0,0,0,0.5); backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div style="background: white; width: 90%; max-width: 500px; border-radius: 1.5rem; padding: 2rem; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2 id="modalTitle" style="font-size: 1.25rem; font-weight: 700; color: #1e293b;">Set Budget Departemen</h2>
            <button onclick="closeEditBudgetModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
        </div>
        
        <form id="editBudgetForm" method="POST">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            
            <div style="margin-bottom: 1.5rem;">
                <label for="budgetLimitInput" style="display: block; font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Anggaran Bulanan (Rp)</label>
                <input type="number" name="budget_limit" id="budgetLimitInput" required min="0" step="1000" style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 0.75rem; font-size: 1rem; font-weight: 700; color: #1e293b;">
                <p style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.5rem;">Tentukan batas maksimal pengeluaran per bulan untuk departemen ini.</p>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label for="deskripsiInput" style="display: block; font-size: 0.85rem; font-weight: 600; color: #64748b; margin-bottom: 0.5rem;">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsiInput" rows="3" style="width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #e2e8f0; border-radius: 0.75rem; font-size: 0.9rem; resize: none;"></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                <button type="button" onclick="closeEditBudgetModal()" class="btn-modern btn-modern-secondary" style="flex: 1;">Batal</button>
                <button type="submit" class="btn-modern btn-modern-primary" style="flex: 2;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/finance-master.js')); ?>"></script>
<script>
    function openEditBudgetModal(id, name, budget, desc) {
        const modal = document.getElementById('editBudgetModal');
        const form = document.getElementById('editBudgetForm');
        const title = document.getElementById('modalTitle');
        const budgetInput = document.getElementById('budgetLimitInput');
        const descInput = document.getElementById('deskripsiInput');
        
        title.innerText = `Set Budget: ${name}`;
        form.action = `<?php echo e(url('/finance/masterdata/departemen')); ?>/${id}`;
        budgetInput.value = Math.floor(budget);
        descInput.value = desc === 'null' ? '' : desc;
        
        modal.style.display = 'flex';
    }

    function closeEditBudgetModal() {
        document.getElementById('editBudgetModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('editBudgetModal');
        if (event.target == modal) {
            closeEditBudgetModal();
        }
    };

    function initBudgetInfoBannerClose() {
        const infoBanner = document.getElementById('budgetInfoBanner');
        const closeButton = document.getElementById('budgetInfoClose');

        if (!infoBanner || !closeButton) {
            return;
        }

        if (closeButton.dataset.closeBound === '1') {
            return;
        }
        closeButton.dataset.closeBound = '1';

        closeButton.addEventListener('click', function () {
            infoBanner.style.display = 'none';
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initBudgetInfoBannerClose);
    } else {
        initBudgetInfoBannerClose();
    }

    document.addEventListener('livewire:navigated', initBudgetInfoBannerClose);
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/dashboard/finance/masterdata/departemen/index.blade.php ENDPATH**/ ?>