<?php $__env->startSection('title', 'Pusat Laporan Keuangan'); ?>

<?php $__env->startPush('styles'); ?>
<style>
    .reports-overview {
        margin-bottom: 0.15rem;
    }

    .reports-hub-section {
        padding-top: 1rem !important;
    }

    .reports-hub-section .section-header {
        justify-content: flex-start !important;
        align-items: flex-start !important;
    }

    .reports-hub-section .section-header > div {
        width: 100%;
        text-align: left !important;
        padding-left: 0.2rem;
    }

    .reports-hub-section .section-title,
    .reports-hub-section .section-subtitle {
        text-align: left !important;
    }

    .reports-hub-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 0.85rem;
    }

    .report-hub-card {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 0.9rem;
        min-height: 180px;
        padding: 1rem;
        border-radius: 1.25rem;
        border: 1px solid #dde6f1;
        background: linear-gradient(145deg, #ffffff 0%, #f8fbff 100%);
        text-decoration: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        overflow: hidden;
    }

    .report-hub-card::after {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 130px;
        height: 130px;
        border-radius: 999px;
        opacity: 0.08;
        background: currentColor;
        pointer-events: none;
    }

    .report-hub-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 28px rgba(22, 37, 62, 0.12);
        border-color: #c7d6e8;
    }

    .report-hub-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .report-hub-icon {
        width: 46px;
        height: 46px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(66, 93, 135, 0.12);
        color: inherit;
        flex-shrink: 0;
    }

    .report-hub-icon svg {
        width: 21px;
        height: 21px;
        stroke-width: 2.1;
    }

    .report-hub-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        border: 1px solid currentColor;
        padding: 0.2rem 0.55rem;
        font-size: 0.66rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        opacity: 0.82;
    }

    .report-hub-meta h3 {
        margin: 0 0 0.25rem;
        font-size: 0.98rem;
        line-height: 1.3;
        color: #0f172a;
    }

    .report-hub-meta p {
        margin: 0;
        font-size: 0.8rem;
        line-height: 1.5;
        color: #54647d;
    }

    .report-hub-cta {
        margin-top: auto;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.76rem;
        font-weight: 700;
        color: inherit;
        opacity: 0.9;
    }

    .report-hub-cta svg {
        width: 14px;
        height: 14px;
        transition: transform 0.2s ease;
    }

    .report-hub-card:hover .report-hub-cta svg {
        transform: translateX(3px);
    }

    .report-theme-primary {
        color: #425d87;
    }

    .report-theme-info {
        color: #2563eb;
    }

    .report-theme-success {
        color: #059669;
    }

    .report-theme-teal {
        color: #0f766e;
    }

    .report-theme-warning {
        color: #b45309;
    }

    @media (max-width: 768px) {
        .reports-hub-grid {
            grid-template-columns: 1fr;
        }

        .report-hub-card {
            min-height: 165px;
        }
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Pusat Laporan','subtitle' => 'Akses semua laporan operasional dan akuntansi','showNotification' => true,'showProfile' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pusat Laporan','subtitle' => 'Akses semua laporan operasional dan akuntansi','showNotification' => true,'showProfile' => true]); ?>
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
            <div class="stats-grid reports-overview">
                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-value"><?php echo e(number_format($totalCount ?? 0)); ?></div>
                        <div class="stat-label">Pencairan di Periode</div>
                    </div>
                    <div class="stat-icon warning-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                    </div>
                </div>

                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-value"><?php echo e(format_rupiah($totalNominal ?? 0)); ?></div>
                        <div class="stat-label">Total Nominal Dicairkan</div>
                    </div>
                    <div class="stat-icon success-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                </div>

                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-value"><?php echo e(number_format(count($departemen ?? []))); ?></div>
                        <div class="stat-label">Departemen Terdata</div>
                    </div>
                    <div class="stat-icon info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 21h18"></path>
                            <path d="M5 21V7l8-4v18"></path>
                            <path d="M19 21V11l-6-4"></path>
                        </svg>
                    </div>
                </div>

                <div class="stat-card modern">
                    <div class="stat-left">
                        <div class="stat-value">5</div>
                        <div class="stat-label">Modul Laporan Aktif</div>
                    </div>
                    <div class="stat-icon primary-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </div>
                </div>
            </div>

            <section class="modern-section reports-hub-section">
                <div class="section-header">
                    <div>
                        <h2 class="section-title">Daftar Laporan</h2>
                        <p class="section-subtitle">Pilih laporan untuk melihat detail transaksi dan analitik keuangan</p>
                    </div>
                </div>

                <div class="reports-hub-grid">
                    <a href="<?php echo e(route('finance.report.jurnal_umum')); ?>" class="report-hub-card report-theme-primary">
                        <div class="report-hub-top">
                            <div class="report-hub-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg>
                            </div>
                            <span class="report-hub-badge">Akuntansi</span>
                        </div>
                        <div class="report-hub-meta">
                            <h3>Jurnal Umum</h3>
                            <p>Catatan jurnal harian lengkap beserta referensi transaksi.</p>
                        </div>
                        <span class="report-hub-cta">
                            Buka Laporan
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M5 12h14"></path>
                                <path d="M13 6l6 6-6 6"></path>
                            </svg>
                        </span>
                    </a>

                    <a href="<?php echo e(route('finance.report.buku_besar')); ?>" class="report-hub-card report-theme-info">
                        <div class="report-hub-top">
                            <div class="report-hub-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                    <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                                </svg>
                            </div>
                            <span class="report-hub-badge">Ledger</span>
                        </div>
                        <div class="report-hub-meta">
                            <h3>Buku Besar</h3>
                            <p>Rekap mutasi tiap akun COA dan saldo berjalan.</p>
                        </div>
                        <span class="report-hub-cta">
                            Buka Laporan
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M5 12h14"></path>
                                <path d="M13 6l6 6-6 6"></path>
                            </svg>
                        </span>
                    </a>

                    <a href="<?php echo e(route('finance.report.laporan_arus_kas')); ?>" class="report-hub-card report-theme-success">
                        <div class="report-hub-top">
                            <div class="report-hub-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                            </div>
                            <span class="report-hub-badge">Cashflow</span>
                        </div>
                        <div class="report-hub-meta">
                            <h3>Laporan Arus Kas</h3>
                            <p>Aliran kas masuk dan keluar untuk analisis likuiditas.</p>
                        </div>
                        <span class="report-hub-cta">
                            Buka Laporan
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M5 12h14"></path>
                                <path d="M13 6l6 6-6 6"></path>
                            </svg>
                        </span>
                    </a>

                    <a href="<?php echo e(route('finance.report.reconciliation')); ?>" class="report-hub-card report-theme-teal">
                        <div class="report-hub-top">
                            <div class="report-hub-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                            </div>
                            <span class="report-hub-badge">Validasi</span>
                        </div>
                        <div class="report-hub-meta">
                            <h3>Reconciliation</h3>
                            <p>Bandingkan data internal dengan Accurate secara cepat.</p>
                        </div>
                        <span class="report-hub-cta">
                            Buka Laporan
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M5 12h14"></path>
                                <path d="M13 6l6 6-6 6"></path>
                            </svg>
                        </span>
                    </a>

                    <a href="<?php echo e(route('finance.report.budget_audit')); ?>" class="report-hub-card report-theme-warning">
                        <div class="report-hub-top">
                            <div class="report-hub-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                                    <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                                </svg>
                            </div>
                            <span class="report-hub-badge">Budget</span>
                        </div>
                        <div class="report-hub-meta">
                            <h3>Audit Budget</h3>
                            <p>Evaluasi pemakaian anggaran setiap departemen per bulan.</p>
                        </div>
                        <span class="report-hub-cta">
                            Buka Laporan
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <path d="M5 12h14"></path>
                                <path d="M13 6l6 6-6 6"></path>
                            </svg>
                        </span>
                    </a>
                </div>
            </section>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/dashboard/finance/reports/index.blade.php ENDPATH**/ ?>