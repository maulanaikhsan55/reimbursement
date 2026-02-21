<?php $__env->startSection('title', 'Pengajuan Reimbursement'); ?>

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
    .col-vendor { min-width: 200px; }
    .col-tanggal { width: 130px; text-align: center !important; }
    .col-nominal { width: 150px; text-align: right !important; }
    .col-status { width: 160px; text-align: center !important; }
    .col-ai { width: 120px; text-align: center !important; }
    .col-aksi { width: 100px; text-align: center !important; }

    .data-table th.col-tanggal, .data-table th.col-status, .data-table th.col-ai, .data-table th.col-aksi {
        text-align: center !important;
    }
    .data-table th.col-nominal {
        text-align: right !important;
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

    .pengajuan-index-shell {
        padding: 1.08rem 1.16rem 1.14rem !important;
    }

    .pengajuan-index-shell .section-header {
        margin-bottom: 0.8rem !important;
        padding-bottom: 0.62rem !important;
    }

    .pengajuan-index-shell .filter-container {
        margin-top: 0.3rem;
    }

    .pengajuan-index-shell .data-table-wrapper {
        margin-top: 0.8rem !important;
        border: 1px solid #e4edf8;
        border-radius: 1rem;
        box-shadow: 0 10px 24px rgba(37, 64, 97, 0.08);
        overflow: hidden;
        background: #ffffff;
    }

    .pengajuan-index-shell .data-table tbody tr {
        transition: background-color 0.16s ease;
    }

    .pengajuan-index-shell .data-table tbody tr:hover {
        background: #f8fbff;
    }

    .vendor-name {
        font-weight: 600;
        color: #334155;
    }

    .amount-text-strong {
        font-weight: 700;
        color: #0f172a;
    }

    .btn-action-icon-duplicate {
        background: #eef5ff;
        color: #3b82f6;
        border-color: #dbeafe;
    }

    .btn-action-icon-duplicate:hover {
        background: #dbeafe;
        color: #1d4ed8;
        border-color: #bfdbfe;
    }

    .inline-action-form {
        display: inline;
    }

    .empty-state-actions {
        margin-top: 1.2rem;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Pengajuan Reimbursement','subtitle' => 'Kelola dan pantau pengajuan reimbursement Anda','showNotification' => true,'showProfile' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pengajuan Reimbursement','subtitle' => 'Kelola dan pantau pengajuan reimbursement Anda','showNotification' => true,'showProfile' => true]); ?>
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
        <!-- Stats Section -->
        <div id="statsContainer">
            <?php echo $__env->make('dashboard.atasan.pengajuan.partials._stats', ['stats' => $stats], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>

        <!-- Pengajuan List Section -->
        <section class="modern-section pengajuan-index-shell">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Daftar Pengajuan</h2>
                    <p class="section-subtitle">Total: <?php echo e($pengajuanList->total()); ?> pengajuan pribadi</p>
                </div>
                <div class="header-actions">
                    <div class="export-actions">
                        <a href="#" onclick="exportCsv(event)" data-url="<?php echo e(route('atasan.pengajuan.export-csv')); ?>" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke CSV">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                            CSV
                        </a>

                        <a href="#" onclick="exportXlsx(event)" data-url="<?php echo e(route('atasan.pengajuan.export-xlsx')); ?>" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke XLSX">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <path d="M8 13l3 4"></path>
                                <path d="M11 13l-3 4"></path>
                                <path d="M14 17h4"></path>
                            </svg>
                            XLSX
                        </a>

                        <a href="#" onclick="exportPdf(event)" data-url="<?php echo e(route('atasan.pengajuan.export-pdf')); ?>" class="btn-modern btn-modern-secondary btn-modern-sm no-loader" title="Export ke PDF">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="12" y1="18" x2="12" y2="12"></line>
                                <line x1="9" y1="15" x2="15" y2="15"></line>
                            </svg>
                            PDF
                        </a>
                    </div>

                    <a href="<?php echo e(route('atasan.pengajuan.create')); ?>" class="btn-modern btn-modern-primary">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg> Pengajuan Baru
                    </a>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-container">
                <form id="filterForm" action="<?php echo e(route('atasan.pengajuan.index')); ?>" method="GET" class="filter-form-pegawai">
                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Pencarian</label>
                        <div class="search-group">
                            <div class="search-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <input type="text" name="search" id="searchInput" value="<?php echo e(request('search')); ?>" class="filter-input-pegawai search-input" placeholder="No. pengajuan, vendor, deskripsi...">
                        </div>
                    </div>

                    <div class="filter-group-pegawai">
                        <label class="filter-label-pegawai">Status</label>
                        <select name="status" id="statusInput" class="filter-input-pegawai">
                            <option value="">Semua Status</option>
                            <option value="validasi_ai" <?php echo e(request('status') == 'validasi_ai' ? 'selected' : ''); ?>>Validasi AI</option>
                            <option value="menunggu_finance" <?php echo e(request('status') == 'menunggu_finance' ? 'selected' : ''); ?>>Menunggu Finance</option>
                            <option value="ditolak_finance" <?php echo e(request('status') == 'ditolak_finance' ? 'selected' : ''); ?>>Ditolak Finance</option>
                            <option value="terkirim_accurate" <?php echo e(request('status') == 'terkirim_accurate' ? 'selected' : ''); ?>>Disetujui Finance</option>
                            <option value="dicairkan" <?php echo e(request('status') == 'dicairkan' ? 'selected' : ''); ?>>Dicairkan</option>
                            <option value="selesai" <?php echo e(request('status') == 'selesai' ? 'selected' : ''); ?>>Selesai</option>
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
                        <a href="<?php echo e(route('atasan.pengajuan.index')); ?>" class="btn-reset-pegawai" title="Reset Filter">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </a>
                    </div>
                </form>
            </div>

            <div id="tableContainer">
                <?php echo $__env->make('dashboard.atasan.pengajuan.partials._table', ['pengajuanList' => $pengajuanList], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </section>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const refreshTable = function() {
            if (window.__atasanPengajuanRefreshBusy) return;
            const form = document.getElementById('filterForm');
            if (!form) return;

            window.__atasanPengajuanRefreshBusy = true;
            const url = new URL(form.action);
            const params = new URLSearchParams(new FormData(form));
            
            fetch(`${url.pathname}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('tableContainer').innerHTML = data.table;
                document.getElementById('statsContainer').innerHTML = data.stats;
            })
            .catch(error => console.error('Error refreshing table:', error))
            .finally(() => {
                window.__atasanPengajuanRefreshBusy = false;
            });
        };

        // Listener for real-time refresh
        window.removeEventListener('refresh-pengajuan-table', window.__atasanPengajuanRefreshHandler);
        window.__atasanPengajuanRefreshHandler = refreshTable;
        window.addEventListener('refresh-pengajuan-table', window.__atasanPengajuanRefreshHandler);
    });
</script>
<script src="<?php echo e(asset('js/pages/pegawai/pengajuan.js')); ?>"></script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/dashboard/atasan/pengajuan/index.blade.php ENDPATH**/ ?>