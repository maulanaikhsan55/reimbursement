<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengajuanList->isEmpty()): ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(request()->anyFilled(['search', 'tanggal_from', 'tanggal_to', 'status'])): ?>
            <div class="empty-state-title">Tidak ada hasil</div>
            <p>Coba ubah filter pencarian Anda</p>
        <?php else: ?>
            <div class="empty-state-title">Belum ada pengajuan</div>
            <p>Tidak ada pengajuan yang menunggu persetujuan saat ini</p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
<?php else: ?>
    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-no-pengajuan">No. Pengajuan</th>
                    <th class="col-tanggal">Tanggal</th>
                    <th class="col-staff">Staff</th>
                    <th class="col-vendor">Vendor</th>
                    <th class="col-nominal">Nominal</th>
                    <th class="col-status">Status</th>
                    <th class="col-ai">Validasi AI</th>
                    <th class="col-aksi">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pengajuanList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pengajuan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td data-label="No. Pengajuan">
                            <span class="code-badge"><?php echo e($pengajuan->nomor_pengajuan); ?></span>
                        </td>
                        <td data-label="Tanggal" class="col-tanggal">
                            <span class="text-secondary"><?php echo e($pengajuan->tanggal_pengajuan->format('d/m/Y')); ?></span>
                        </td>
                        <td data-label="Staff">
                            <div style="font-weight: 600; color: #334155;"><?php echo e($pengajuan->user->name); ?></div>
                        </td>
                        <td data-label="Vendor">
                            <div style="font-weight: 600; color: #334155;"><?php echo e($pengajuan->nama_vendor); ?></div>
                        </td>
                        <td data-label="Nominal" class="col-nominal">
                            <span class="amount-text" style="font-weight: 700; color: #0f172a;"><?php echo e(format_rupiah($pengajuan->nominal)); ?></span>
                        </td>
                        <td data-label="Status" class="col-status">
                            <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => $pengajuan->status,'transactionId' => $pengajuan->accurate_transaction_id]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pengajuan->status),'transactionId' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pengajuan->accurate_transaction_id)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $attributes = $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__attributesOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62)): ?>
<?php $component = $__componentOriginal8c81617a70e11bcf247c4db924ab1b62; ?>
<?php unset($__componentOriginal8c81617a70e11bcf247c4db924ab1b62); ?>
<?php endif; ?>
                        </td>
                        <td data-label="Validasi AI" class="col-ai">
                            <?php
                                // Get any validation record for this pengajuan to determine status
                                $validasi = $pengajuan->validasiAi->first();
                            ?>
                            <?php if (isset($component)) { $__componentOriginala2ab4d49732a7e339653db9c46d8cd92 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginala2ab4d49732a7e339653db9c46d8cd92 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ai-validation-status','data' => ['status' => $validasi?->status]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ai-validation-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($validasi?->status)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginala2ab4d49732a7e339653db9c46d8cd92)): ?>
<?php $attributes = $__attributesOriginala2ab4d49732a7e339653db9c46d8cd92; ?>
<?php unset($__attributesOriginala2ab4d49732a7e339653db9c46d8cd92); ?>
<?php endif; ?>
<?php if (isset($__componentOriginala2ab4d49732a7e339653db9c46d8cd92)): ?>
<?php $component = $__componentOriginala2ab4d49732a7e339653db9c46d8cd92; ?>
<?php unset($__componentOriginala2ab4d49732a7e339653db9c46d8cd92); ?>
<?php endif; ?>
                        </td>
                        <td data-label="Aksi" class="col-aksi">
                            <div class="action-buttons-centered">
                                <a href="<?php echo e(route('atasan.approval.show', $pengajuan->pengajuan_id)); ?>" 
                                   class="btn-action-icon" 
                                   title="<?php echo e($pengajuan->status->value === 'menunggu_atasan' ? 'Persetujuan' : 'Lihat Detail'); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengajuan->status->value === 'menunggu_atasan'): ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <polyline points="9 11 12 14 22 4"></polyline>
                                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                        </svg>
                                    <?php else: ?>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination-wrapper">
        <?php echo e($pengajuanList->links('components.pagination')); ?>

    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/dashboard/atasan/approval/partials/_table.blade.php ENDPATH**/ ?>