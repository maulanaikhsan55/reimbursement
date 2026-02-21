<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames((['status', 'showLabel' => true]));

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

foreach (array_filter((['status', 'showLabel' => true]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
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
    
    $config = [
        'valid' => ['class' => 'status-approved', 'label' => 'Lolos'],
        'invalid' => ['class' => 'status-draft', 'label' => 'Review'],
        'pending' => ['class' => 'status-pending', 'label' => 'Proses'],
    ];
    
    $item = $config[$statusValue] ?? null;
?>

<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item): ?>
    <span class="status-badge <?php echo e($item['class']); ?>">
        <?php echo e($item['label']); ?>

    </span>
<?php else: ?>
    <span class="text-secondary" style="opacity: 0.5;">-</span>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/components/ai-validation-status.blade.php ENDPATH**/ ?>