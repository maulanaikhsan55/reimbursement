<?php $__env->startSection('title', 'Edit Pengguna - Humplus Reimbursement'); ?>
<?php $__env->startSection('page-title', 'Edit Pengguna'); ?>

<?php $__env->startPush('styles'); ?>
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .form-help-text {
        display: block;
        font-size: 0.8rem;
        color: #64748b;
        margin-top: 0.35rem;
        line-height: 1.4;
    }

    .role-description {
        font-size: 0.85rem;
        color: #64748b;
        margin-top: 0.5rem;
        padding: 0.75rem 1rem;
        background: #f1f5f9;
        border-radius: 1rem;
        border-left: 4px solid #425d87;
        line-height: 1.5;
    }
    
    .alert-warning-simple {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #92400e;
        background: #fffbeb;
        padding: 0.75rem;
        border-radius: 0.5rem;
        margin-top: 0.5rem;
        font-size: 0.85rem;
        border: 1px solid #fef3c7;
    }

    /* Hierarchy Preview Styles */
    .hierarchy-container {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: white;
        padding: 1.25rem;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        margin-top: 0.5rem;
    }

    .hierarchy-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.35rem;
        flex: 1;
    }

    .step-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
    }

    .step-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: #64748b;
        text-align: center;
        white-space: nowrap;
    }

    .hierarchy-arrow {
        color: #cbd5e1;
    }

    .current-user .step-icon {
        background: #425d87;
        color: white;
        border-color: #425d87;
    }

    .current-user .step-label {
        color: #425d87;
    }

    .next-approver.active .step-icon {
        background: #10b981;
        color: white;
        border-color: #10b981;
    }

    .next-approver.active .step-label {
        color: #059669;
    }

    /* TomSelect Modern Theme Overrides */
    .ts-wrapper {
        width: 100%;
    }
    .ts-control {
        border-radius: 50px !important;
        padding: 0.625rem 1.25rem !important;
        border-color: #e2e8f0 !important;
        font-family: inherit !important;
        font-size: 0.875rem !important;
        transition: all 0.2s ease !important;
        box-shadow: none !important;
    }
    .ts-wrapper.focus .ts-control {
        border-color: #425d87 !important;
        box-shadow: 0 0 0 3px rgba(66, 93, 135, 0.1) !important;
    }
    .ts-dropdown {
        border-radius: 1rem !important;
        margin-top: 0.5rem !important;
        border-color: #e2e8f0 !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
        padding: 0.5rem !important;
    }
    .ts-dropdown .option {
        padding: 0.5rem 0.75rem !important;
        border-radius: 0.5rem !important;
    }
    .ts-dropdown .active {
        background-color: #f1f5f9 !important;
        color: #1e293b !important;
    }

    /* Input & Form Control Overrides */
    .form-control {
        border-radius: 50px !important;
        padding: 0.625rem 1.25rem !important;
        border-color: #e2e8f0 !important;
        font-size: 0.875rem !important;
    }

    .form-control:focus {
        border-color: #425d87 !important;
        box-shadow: 0 0 0 3px rgba(66, 93, 135, 0.1) !important;
    }

    .form-label {
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.5rem;
        font-size: 0.85rem;
        margin-left: 0.75rem;
    }

    /* Split Container Layout */
    .unified-form-card {
        background: white;
        border-radius: 1.5rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }

    .unified-form-body {
        padding: 2.5rem;
    }

    .form-split-grid {
        display: grid;
        grid-template-columns: 1.5fr 1fr;
        gap: 3.5rem;
        align-items: start;
    }

    @media (max-width: 1024px) {
        .form-split-grid {
            grid-template-columns: 1fr;
            gap: 2.5rem;
        }
    }

    .form-side-card {
        background: #f8fafc;
        border-radius: 1.5rem;
        padding: 2rem;
        border: 1px solid #eef2f7;
        position: sticky;
        top: 2rem;
    }

    .form-section-header {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        margin-bottom: 2rem;
        padding-bottom: 1.25rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .form-section-header svg {
        color: #425d87;
        background: #eef2f7;
        padding: 0.6rem;
        border-radius: 0.85rem;
        width: 38px;
        height: 38px;
    }

    .form-section-header h3 {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .form-grid-double {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 768px) {
        .form-grid-double {
            grid-template-columns: 1fr;
        }
        .unified-form-body {
            padding: 1.5rem;
        }
    }

    .btn-modern-primary {
        background: #425d87 !important;
        box-shadow: 0 4px 12px rgba(66, 93, 135, 0.25) !important;
    }

    .btn-modern-primary:hover {
        background: #364d70 !important;
        box-shadow: 0 8px 20px rgba(66, 93, 135, 0.35) !important;
    }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Edit Pengguna','subtitle' => 'Perbarui informasi dan hak akses pengguna: '.e($user->name).'','showNotification' => true,'showProfile' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Edit Pengguna','subtitle' => 'Perbarui informasi dan hak akses pengguna: '.e($user->name).'','showNotification' => true,'showProfile' => true]); ?>
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
            <form method="POST" action="<?php echo e(route('finance.masterdata.users.update', $user->id)); ?>">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                
                <div class="unified-form-card">
                    <div class="unified-form-body">
                        <div class="form-split-grid">
                            <!-- Left Side: Form Fields -->
                            <div class="form-main-content">
                                <!-- Account Data Section -->
                                <div class="form-section mb-5">
                                    <div class="form-section-header">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        <h3>Data Akun</h3>
                                    </div>
                                    
                                    <div class="form-grid-double">
                                        <div class="form-group">
                                            <label for="name" class="form-label">Nama Lengkap <span class="required">*</span></label>
                                            <input type="text" id="name" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('name', $user->name)); ?>" placeholder="Contoh: Budi Santoso" required autocomplete="name">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="form-error ml-3"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>

                                        <div class="form-group">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" id="email" class="form-control" value="<?php echo e($user->email); ?>" readonly style="background-color: #f1f5f9; cursor: not-allowed;">
                                            <small class="form-help-text ml-3"><?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'lock','class' => 'w-3 h-3 inline']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'lock','class' => 'w-3 h-3 inline']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?> Email tidak dapat diubah.</small>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-3">
                                        <div class="alert alert-info mb-0" style="font-size: 0.85rem; border-radius: 1rem; background: #f0f7ff; border: 1px solid #e0efff; color: #0c4a6e; padding: 1rem;">
                                            <div class="d-flex align-items-center gap-2">
                                                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'info','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'info','class' => 'w-4 h-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                                                <span>Password dikelola melalui fitur <strong>Reset Password</strong> di halaman daftar pengguna.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Organization Data Section -->
                                <div class="form-section mb-5">
                                    <div class="form-section-header">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="9" cy="7" r="4"></circle>
                                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                        </svg>
                                        <h3>Data Organisasi</h3>
                                    </div>

                                    <div class="form-grid-double">
                                        <div class="form-group">
                                            <label for="departemen_id" class="form-label">Departemen <span class="required">*</span></label>
                                            <select id="departemen_id" name="departemen_id" class="form-control <?php $__errorArgs = ['departemen_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                                <option value="">-- Pilih Departemen --</option>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $departemen->sortBy('nama_departemen'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($dept->departemen_id); ?>" <?php echo e(old('departemen_id', $user->departemen_id) == $dept->departemen_id ? 'selected' : ''); ?>>
                                                        <?php echo e($dept->nama_departemen); ?>

                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </select>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['departemen_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="form-error ml-3"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>

                                        <div class="form-group">
                                            <label for="role" class="form-label">Peran (Role) <span class="required">*</span></label>
                                            <select id="role" name="role" class="form-control <?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" required>
                                                <option value="pegawai" <?php echo e(old('role', $user->role) === 'pegawai' ? 'selected' : ''); ?>>Pegawai</option>
                                                <option value="atasan" <?php echo e(old('role', $user->role) === 'atasan' ? 'selected' : ''); ?>>Atasan (Supervisor)</option>
                                                <option value="finance" <?php echo e(old('role', $user->role) === 'finance' ? 'selected' : ''); ?>>Finance</option>
                                            </select>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['role'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="form-error ml-3"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>

                                        <div class="form-group">
                                            <label for="jabatan" class="form-label">Jabatan <span class="required">*</span></label>
                                            <input type="text" id="jabatan" name="jabatan" class="form-control <?php $__errorArgs = ['jabatan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('jabatan', $user->jabatan)); ?>" placeholder="Contoh: Staff Admin, Manager IT" required>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['jabatan'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="form-error ml-3"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>

                                        <div class="form-group">
                                            <label for="is_active" class="form-label">Status Akun</label>
                                            <select id="is_active" name="is_active" class="form-control">
                                                <option value="1" <?php echo e(old('is_active', $user->is_active) == 1 ? 'selected' : ''); ?>>Aktif</option>
                                                <option value="0" <?php echo e(old('is_active', $user->is_active) == 0 ? 'selected' : ''); ?>>Nonaktif</option>
                                            </select>
                                        </div>

                                        <div class="form-group" id="atasan_group" style="grid-column: 1 / -1;">
                                            <label for="atasan_id" class="form-label">
                                                Atasan Langsung <span id="atasan_required" class="required">*</span>
                                                <span id="atasan_optional" style="color: #6b7280; font-size: 0.85rem; display: none;">(Opsional)</span>
                                            </label>
                                            <select id="atasan_id" name="atasan_id" class="form-control">
                                                <option value="">-- Pilih Atasan --</option>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $supervisors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supervisor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($supervisor->id); ?>" 
                                                            data-dept="<?php echo e($supervisor->departemen_id); ?>"
                                                            <?php echo e(old('atasan_id', $user->atasan_id) == $supervisor->id ? 'selected' : ''); ?>>
                                                        <?php echo e($supervisor->name); ?> (<?php echo e($supervisor->departemen->nama_departemen ?? '-'); ?>)
                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </select>
                                            <div id="atasanWarning" class="alert-warning-simple mt-2" style="display: none;">
                                                <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'alert-circle','class' => 'w-4 h-4']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'alert-circle','class' => 'w-4 h-4']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $attributes = $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__attributesOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc)): ?>
<?php $component = $__componentOriginalce262628e3a8d44dc38fd1f3965181bc; ?>
<?php unset($__componentOriginalce262628e3a8d44dc38fd1f3965181bc); ?>
<?php endif; ?>
                                                <span>Departemen ini belum memiliki user dengan role Atasan.</span>
                                            </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['atasan_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="form-error ml-3"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- Additional Info Section -->
                                <div class="form-section">
                                    <div class="form-section-header">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                            <line x1="2" y1="10" x2="22" y2="10"></line>
                                        </svg>
                                        <h3>Data Tambahan</h3>
                                    </div>

                                    <div class="form-grid-double">
                                        <div class="form-group">
                                            <label for="nomor_telepon" class="form-label">Nomor Telepon</label>
                                            <input type="tel" id="nomor_telepon" name="nomor_telepon" class="form-control <?php $__errorArgs = ['nomor_telepon'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('nomor_telepon', $user->nomor_telepon)); ?>" placeholder="08123456789" autocomplete="tel">
                                        </div>

                                        <div class="form-group">
                                            <label for="nama_bank" class="form-label">Nama Bank</label>
                                            <input type="text" id="nama_bank" name="nama_bank" class="form-control <?php $__errorArgs = ['nama_bank'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('nama_bank', $user->nama_bank)); ?>" placeholder="Contoh: BCA, Mandiri">
                                        </div>

                                        <div class="form-group" style="grid-column: 1 / -1;">
                                            <label for="nomor_rekening" class="form-label">Nomor Rekening</label>
                                            <input type="text" id="nomor_rekening" name="nomor_rekening" class="form-control <?php $__errorArgs = ['nomor_rekening'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" value="<?php echo e(old('nomor_rekening', $user->nomor_rekening)); ?>" placeholder="Nomor rekening bank">
                                            <small class="form-help-text ml-3">Pastikan nomor valid atas nama pemilik akun.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Side: Preview & Info -->
                            <div class="form-sidebar-content">
                                <div class="form-side-card">
                                    <div class="side-section-title">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                                            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path>
                                        </svg>
                                        Bantuan & Preview
                                    </div>
                                    
                                    <div class="mb-4">
                                        <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Deskripsi Peran</div>
                                        <div id="roleDescription" class="role-description">Pilih peran untuk melihat deskripsi...</div>
                                    </div>
                                    
                                    <div id="hierarchyPreview" style="display: none;">
                                        <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Alur Persetujuan</div>
                                        <div class="hierarchy-container">
                                            <div class="hierarchy-step current-user">
                                                <div class="step-icon">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                                </div>
                                                <div class="step-label">User</div>
                                            </div>
                                            <div class="hierarchy-arrow">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                            </div>
                                            <div class="hierarchy-step next-approver" id="approverStep">
                                                <div class="step-icon">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle></svg>
                                                </div>
                                                <div class="step-label" id="approverLabel">Atasan</div>
                                            </div>
                                            <div class="hierarchy-arrow">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                            </div>
                                            <div class="hierarchy-step">
                                                <div class="step-icon">
                                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                                </div>
                                                <div class="step-label">Finance</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="mt-5 pt-4 border-top d-flex justify-content-end gap-3">
                            <a href="<?php echo e(route('finance.masterdata.users.index')); ?>" class="btn-modern btn-modern-secondary px-5">
                                Batal
                            </a>
                            <button type="submit" class="btn-modern btn-modern-primary px-5">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 8px;">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
(function() {
    const initPage = function() {
        const roleSelect = document.getElementById('role');
        const deptSelect = document.getElementById('departemen_id');
        const atasanSelect = document.getElementById('atasan_id');
        const atasanGroup = document.getElementById('atasan_group');
        const atasanWarning = document.getElementById('atasanWarning');
        const roleDescription = document.getElementById('roleDescription');
        const hierarchyPreview = document.getElementById('hierarchyPreview');
        const approverLabel = document.getElementById('approverLabel');
        const approverStep = document.getElementById('approverStep');

        if (!roleSelect || !deptSelect) return;

        // Cleanup TomSelect instances if already existing
        if (roleSelect.tomselect) roleSelect.tomselect.destroy();
        if (deptSelect.tomselect) deptSelect.tomselect.destroy();
        if (atasanSelect && atasanSelect.tomselect) atasanSelect.tomselect.destroy();

        const tsDept = new TomSelect('#departemen_id', { create: false, controlInput: null });
        const tsAtasan = new TomSelect('#atasan_id', { create: false, allowEmptyOption: true, placeholder: '-- Pilih Atasan --' });
        const allAtasanOptions = atasanSelect ? Array.from(atasanSelect.options).slice(1) : [];

        const roleData = {
            'pegawai': { desc: 'Karyawan yang mengajukan reimbursement. Memerlukan atasan untuk persetujuan awal.', hierarchy: true },
            'atasan': { desc: 'Manager/Supervisor yang menyetujui pengajuan tim. Pengajuan pribadi langsung ke Finance.', hierarchy: true },
            'finance': { desc: 'Tim keuangan yang memverifikasi dokumen dan mencairkan dana.', hierarchy: false }
        };

        function updateRoleInfo() {
            const role = roleSelect.value;
            if (roleDescription) {
                roleDescription.innerHTML = roleData[role] ? roleData[role].desc : 'Pilih peran untuk melihat deskripsi...';
            }
            
            if (hierarchyPreview) {
                if (roleData[role]?.hierarchy) {
                    hierarchyPreview.style.display = 'block';
                    if (approverLabel) approverLabel.innerText = role === 'pegawai' ? 'Atasan' : 'Finance (Direct)';
                    if (approverStep) approverStep.style.display = role === 'atasan' ? 'none' : 'flex';
                    const arrows = hierarchyPreview.querySelectorAll('.hierarchy-arrow');
                    if (arrows.length > 0) arrows[0].style.display = role === 'atasan' ? 'none' : 'block';
                } else {
                    hierarchyPreview.style.display = 'none';
                }
            }
            
            if (atasanGroup) atasanGroup.style.display = role === 'pegawai' ? 'block' : 'none';
            if (atasanSelect) atasanSelect.required = role === 'pegawai';
            if (role !== 'pegawai') tsAtasan.setValue("");
        }

        function filterAtasan() {
            const selectedDept = deptSelect.value;
            const currentAtasanId = "<?php echo e($user->atasan_id); ?>";
            tsAtasan.clearOptions();
            if (atasanWarning) atasanWarning.style.display = 'none';
            
            if (!selectedDept) return;

            const filtered = allAtasanOptions.filter(opt => opt.dataset.dept == selectedDept);
            if (filtered.length > 0) {
                filtered.forEach(opt => tsAtasan.addOption({ value: opt.value, text: opt.text }));
                
                // Restore selection if matching current dept
                if (currentAtasanId && selectedDept == "<?php echo e($user->departemen_id); ?>") {
                    tsAtasan.setValue(currentAtasanId);
                } else if (filtered.length === 1 && roleSelect.value === 'pegawai') {
                    tsAtasan.setValue(filtered[0].value);
                }
            } else if (roleSelect.value === 'pegawai') {
                if (atasanWarning) atasanWarning.style.display = 'flex';
            }
            
            updateApproverStatus();
        }

        function updateApproverStatus() {
            if (atasanSelect && approverStep) {
                if (atasanSelect.value) approverStep.classList.add('active');
                else approverStep.classList.remove('active');
            }
        }

        roleSelect.addEventListener('change', () => { updateRoleInfo(); filterAtasan(); });
        deptSelect.addEventListener('change', filterAtasan);
        if (atasanSelect) atasanSelect.addEventListener('change', updateApproverStatus);

        updateRoleInfo();
        if (deptSelect.value) filterAtasan();
    };

    if (document.readyState === 'complete') {
        initPage();
    } else {
        document.addEventListener('DOMContentLoaded', initPage);
    }
    
    // Also handle Livewire navigation
    document.addEventListener('livewire:navigated', initPage);
})();
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/dashboard/finance/masterdata/users/edit.blade.php ENDPATH**/ ?>