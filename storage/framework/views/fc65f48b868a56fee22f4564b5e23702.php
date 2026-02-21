<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status', 'departmentName' => null]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter((['status', 'departmentName' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($status): ?>
<div class="budget-status-container linkedin-style" id="budget-indicator-container">
    <div class="budget-header">
        <div class="budget-title">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-primary">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
            <span>Anggaran <?php echo e($departmentName ?? 'Departemen'); ?></span>
        </div>
        <div class="budget-meta" id="budget-percentage-text">
            <?php echo e($status['percentage']); ?>%
        </div>
    </div>
    
    <div class="progress-wrapper">
        <div id="budget-progress-bar" class="progress-bar progress-<?php echo e($status['status']); ?>" style="width: <?php echo e(min($status['percentage'], 100)); ?>%"></div>
    </div>

    <div class="budget-info-footer">
        <div class="usage-info">
            <span class="label">Terpakai:</span>
            <span class="value" id="budget-usage-value">Rp <?php echo e(number_format($status['usage'] + $status['current'], 0, ',', '.')); ?></span>
        </div>
        <div class="limit-info">
            <span class="label">Plafon:</span>
            <span class="value">Rp <?php echo e(number_format($status['limit'], 0, ',', '.')); ?></span>
        </div>
    </div>

    <div id="budget-warning-box" class="budget-warning <?php echo e($status['is_over'] ? '' : 'd-none'); ?>">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span>Estimasi melebihi plafon anggaran bulan ini.</span>
    </div>
</div>

<style>
    .budget-status-container.linkedin-style {
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    .budget-status-container.linkedin-style:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .budget-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }

    .budget-title {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-weight: 700;
        color: #1e293b;
        font-size: 0.95rem;
    }

    .budget-meta {
        font-weight: 800;
        font-size: 0.9rem;
        color: #4f46e5;
        background: #f5f3ff;
        padding: 0.2rem 0.6rem;
        border-radius: 6px;
    }

    .progress-wrapper {
        height: 10px;
        background: #f1f5f9;
        border-radius: 999px;
        overflow: hidden;
        margin-bottom: 0.75rem;
        position: relative;
    }

    .progress-bar {
        height: 100%;
        border-radius: 999px;
        transition: width 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
    }

    .progress-success { background: linear-gradient(90deg, #10b981, #34d399); }
    .progress-warning { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .progress-danger { background: linear-gradient(90deg, #ef4444, #f87171); }

    .budget-info-footer {
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
        color: #64748b;
    }

    .budget-info-footer .label {
        font-weight: 500;
        margin-right: 0.25rem;
    }

    .budget-info-footer .value {
        font-weight: 700;
        color: #334155;
    }

    .budget-warning {
        margin-top: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: #dc2626;
        padding: 0.75rem;
        background: #fef2f2;
        border-radius: 8px;
        border: 1px solid #fee2e2;
        animation: slideIn 0.3s ease-out;
    }

    .d-none { display: none; }

    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/components/budget-indicator.blade.php ENDPATH**/ ?>