<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status', 'transactionId' => null]));

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

foreach (array_filter((['status', 'transactionId' => null]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $statusValue = $status instanceof \UnitEnum ? $status->value : $status;
    $normalizedStatus = str_replace('_', '-', $statusValue);
    
    $labels = [
        'draft' => 'Draft',
        'validasi_ai' => 'Validasi AI',
        'menunggu_atasan' => 'Menunggu Atasan',
        'ditolak_atasan' => 'Ditolak Atasan',
        'menunggu_finance' => 'Menunggu Finance',
        'ditolak_finance' => 'Ditolak Finance',
        'terkirim_accurate' => 'Disetujui',
        'dicairkan' => 'Dicairkan',
        'void_accurate' => 'Void',
        'selesai' => 'Selesai'
    ];

    $label = $labels[$statusValue] ?? ucfirst(str_replace('_', ' ', $statusValue));
    $colorClass = 'status-' . $normalizedStatus;
?>

<div class="status-badge-wrapper" style="display: inline-flex; flex-direction: column; align-items: center; gap: 4px;">
    <span <?php echo e($attributes->merge(['class' => 'status-badge ' . $colorClass])); ?>>
        <?php echo e($label); ?>

    </span>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($transactionId && in_array($statusValue, ['terkirim_accurate', 'dicairkan'])): ?>
        <span class="status-transaction-id" style="font-size: 0.65rem; color: #94a3b8; font-weight: 600; font-family: 'JetBrains Mono', monospace; letter-spacing: 0.02em;">
            <?php echo e($transactionId); ?>

        </span>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/components/status-badge.blade.php ENDPATH**/ ?>