<div class="stats-grid">
    <div class="stat-card modern">
        <div class="stat-left">
            <div class="stat-value"><?php echo e($stats->pending_approvals ?? 0); ?></div>
            <div class="stat-label">Total Pending</div>
            <div class="stat-sub-label">Menunggu Review Anda</div>
        </div>
        <div class="stat-icon warning-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <path d="M12 6v6l4 2"></path>
            </svg>
        </div>
    </div>

    <div class="stat-card modern">
        <div class="stat-left">
            <div class="stat-value"><?php echo e(format_rupiah($stats->pending_nominal ?? 0)); ?></div>
            <div class="stat-label">Nominal Pending</div>
            <div class="stat-sub-label">Total Beban Persetujuan</div>
        </div>
        <div class="stat-icon info-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                <line x1="2" y1="10" x2="22" y2="10"></line>
            </svg>
        </div>
    </div>

    <div class="stat-card modern">
        <div class="stat-left">
            <div class="stat-value"><?php echo e(format_rupiah($stats->this_month_approved_amount ?? 0)); ?></div>
            <div class="stat-label">Disetujui (Bulan Ini)</div>
            <div class="stat-sub-label"><?php echo e($stats->approved_this_month ?? 0); ?> Pengajuan Tim</div>
        </div>
        <div class="stat-icon success-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </div>
    </div>

    <div class="stat-card modern">
        <div class="stat-left">
            <div class="stat-value" style="color: <?php echo e(($stats->oversla_count ?? 0) > 0 ? '#ef4444' : 'inherit'); ?>"><?php echo e($stats->oversla_count ?? 0); ?></div>
            <div class="stat-label">Overdue SLA</div>
            <div class="stat-sub-label">> <?php echo e($slaDays ?? 3); ?> Hari Belum Diproses</div>
        </div>
        <div class="stat-icon <?php echo e(($stats->oversla_count ?? 0) > 0 ? 'danger-icon' : 'primary-icon'); ?>">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/dashboard/atasan/approval/partials/_stats.blade.php ENDPATH**/ ?>