<?php $__env->startSection('title', 'Persetujuan Pengajuan'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .data-table th {
        padding: 1.25rem 1.25rem !important;
        white-space: nowrap;
        background: #f8fafc;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        font-weight: 700;
        color: #64748b;
        border-bottom: 2px solid #f1f5f9;
    }
    
    .data-table td {
        padding: 1.15rem 1.25rem !important;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }

    /* Column Widths & Alignment */
    .col-no-pengajuan { width: 180px; }
    .col-tanggal { width: 130px; text-align: center !important; }
    .col-staff { min-width: 150px; }
    .col-vendor { min-width: 150px; }
    .col-nominal { width: 140px; text-align: center !important; }
    .col-status { min-width: 180px; text-align: center !important; }
    .col-ai { min-width: 120px; text-align: center !important; }
    .col-aksi { width: 100px; text-align: center !important; }

    .data-table th.col-tanggal, 
    .data-table th.col-status, 
    .data-table th.col-ai, 
    .data-table th.col-aksi,
    .data-table th.col-nominal {
        text-align: center !important;
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

    .action-buttons-centered {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
    }

    .status-transaction-id {
        display: inline-block;
        font-size: 0.65rem !important;
        color: #94a3b8 !important;
        font-weight: 600;
        margin-top: 2px;
        letter-spacing: 0.02em;
    }

    .stat-sub-label {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 600;
        margin-top: 2px;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Persetujuan Pengajuan','subtitle' => 'Review pengajuan reimbursement dari staff Anda','showNotification' => true,'showProfile' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Persetujuan Pengajuan','subtitle' => 'Review pengajuan reimbursement dari staff Anda','showNotification' => true,'showProfile' => true]); ?>
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
            <div id="statsContainer">
                <?php echo $__env->make('dashboard.atasan.approval.partials._stats', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>

            <section class="modern-section">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Daftar Pengajuan</h2>
                        <p class="section-subtitle">Total: <?php echo e($pengajuanList->total()); ?> pengajuan</p>
                    </div>
                    <div class="header-actions">
                        <div class="export-actions">
                            <a href="#" onclick="exportCsv(event)" data-url="<?php echo e(route('atasan.approval.export-csv')); ?>" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke CSV">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg>
                                CSV
                            </a>

                            <a href="#" onclick="exportXlsx(event)" data-url="<?php echo e(route('atasan.approval.export-xlsx')); ?>" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke XLSX">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <path d="M8 13l3 4"></path>
                                    <path d="M11 13l-3 4"></path>
                                    <path d="M14 17h4"></path>
                                </svg>
                                XLSX
                            </a>

                            <a href="#" onclick="exportPdf(event)" data-url="<?php echo e(route('atasan.approval.export-pdf')); ?>" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke PDF">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="12" y1="18" x2="12" y2="12"></line>
                                    <line x1="9" y1="15" x2="15" y2="15"></line>
                                </svg>
                                PDF
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-container">
                    <form id="filterForm" action="<?php echo e(route('atasan.approval.index')); ?>" method="GET" class="filter-form-pegawai">
                        <div class="filter-group-pegawai">
                            <label class="filter-label-pegawai">Pencarian</label>
                            <div class="search-group">
                                <div class="search-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                    </svg>
                                </div>
                                <input type="text" name="search" id="searchInput" value="<?php echo e(request('search')); ?>" class="filter-input-pegawai search-input" placeholder="No. pengajuan, vendor, staff...">
                            </div>
                        </div>

                        <div class="filter-group-pegawai">
                            <label class="filter-label-pegawai">Status</label>
                            <select name="status" id="statusInput" class="filter-input-pegawai">
                                <option value="menunggu_atasan" <?php echo e($currentStatus === 'menunggu_atasan' ? 'selected' : ''); ?>>Menunggu Persetujuan</option>
                                <option value="all" <?php echo e($currentStatus === 'all' ? 'selected' : ''); ?>>Semua Status</option>
                                <option value="menunggu_finance" <?php echo e($currentStatus === 'menunggu_finance' ? 'selected' : ''); ?>>Disetujui (Menunggu Finance)</option>
                                <option value="terkirim_accurate" <?php echo e($currentStatus === 'terkirim_accurate' ? 'selected' : ''); ?>>Disetujui Finance</option>
                                <option value="ditolak_atasan" <?php echo e($currentStatus === 'ditolak_atasan' ? 'selected' : ''); ?>>Ditolak</option>
                            </select>
                        </div>

                        <div class="filter-group-pegawai">
                            <label class="filter-label-pegawai">Tanggal</label>
                            <div class="date-group-pegawai">
                                <input type="date" name="tanggal_from" id="tanggalFrom" value="<?php echo e(request('tanggal_from')); ?>" class="filter-input-pegawai">
                                <span class="date-separator">-</span>
                                <input type="date" name="tanggal_to" id="tanggalTo" value="<?php echo e(request('tanggal_to')); ?>" class="filter-input-pegawai">
                            </div>
                        </div>

                        <div class="filter-actions-pegawai">
                            <button type="submit" class="btn-modern btn-modern-secondary btn-modern-sm">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                    <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                </svg>
                                Filter
                            </button>
                            <a href="<?php echo e(route('atasan.approval.index')); ?>" class="btn-reset-pegawai" title="Reset Filter">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </a>
                        </div>
                    </form>
                </div>

                <div id="tableContainer">
                    <?php echo $__env->make('dashboard.atasan.approval.partials._table', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </section>
        </div>
    </div>
</div>
<?php $__env->startPush('scripts'); ?>
<script src="<?php echo e(asset('js/pages/atasan/approval.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/dashboard/atasan/approval/index.blade.php ENDPATH**/ ?>