<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengajuanList->isEmpty() && !request()->anyFilled(['search', 'status', 'tanggal_from', 'tanggal_to'])): ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
            </svg>
        </div>
        <div class="empty-state-title">Belum ada pengajuan</div>
        <p>Mulai dengan membuat pengajuan reimbursement baru</p>
        <div class="empty-state-actions">
            <a href="<?php echo e(route('pegawai.pengajuan.create')); ?>" class="btn-modern btn-modern-primary">
                Buat Pengajuan Baru
            </a>
        </div>
    </div>
<?php elseif($pengajuanList->isEmpty()): ?>
    <div class="empty-state">
        <div class="empty-state-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="m21 21-4.35-4.35"></path>
            </svg>
        </div>
        <div class="empty-state-title">Tidak ada hasil</div>
        <p>Coba ubah filter pencarian Anda</p>
    </div>
<?php else: ?>
    <div class="data-table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="col-no-pengajuan">No. Pengajuan</th>
                    <th class="col-vendor">Vendor</th>
                    <th class="col-tanggal">Tanggal</th>
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
                            <span class="code-badge">
                                <?php echo e($pengajuan->nomor_pengajuan); ?>

                            </span>
                        </td>
                        <td data-label="Vendor">
                            <div class="vendor-name"><?php echo e($pengajuan->nama_vendor); ?></div>
                        </td>
                        <td data-label="Tanggal" class="col-tanggal">
                            <span class="text-secondary"><?php echo e($pengajuan->tanggal_pengajuan->format('d M Y')); ?></span>
                        </td>
                        <td data-label="Nominal" class="col-nominal">
                            <span class="amount-text amount-text-strong"><?php echo e(format_rupiah($pengajuan->nominal)); ?></span>
                        </td>
                        <td data-label="Status" class="col-status">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengajuan->status->value == 'validasi_ai'): ?>
                                <?php if (isset($component)) { $__componentOriginal8c81617a70e11bcf247c4db924ab1b62 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8c81617a70e11bcf247c4db924ab1b62 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.status-badge','data' => ['status' => 'validasi_ai']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('status-badge'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => 'validasi_ai']); ?>
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
                            <?php else: ?>
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
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td data-label="Validasi AI" class="col-ai">
                            <?php $validasi = $pengajuan->validasiAi->where('jenis_validasi', 'ocr')->first(); ?>
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
                                <a href="<?php echo e(route('pegawai.pengajuan.show', $pengajuan->pengajuan_id)); ?>" class="btn-action-icon" title="Lihat detail">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                </a>
                                <a href="<?php echo e(route('pegawai.pengajuan.create', ['duplicate_id' => $pengajuan->pengajuan_id])); ?>" class="btn-action-icon btn-action-icon-duplicate" title="Ajukan lagi (Duplikat)">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($pengajuan->status->value, ['validasi_ai', 'menunggu_atasan', 'ditolak_atasan', 'ditolak_finance'])): ?>
                                <form action="<?php echo e(route('pegawai.pengajuan.destroy', $pengajuan->pengajuan_id)); ?>" method="POST" class="inline-action-form">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="button" class="btn-action-icon btn-action-delete" title="Batalkan pengajuan" onclick="openConfirmModal(() => this.closest('form').submit(), 'Batalkan Pengajuan', 'Yakin ingin membatalkan pengajuan ini?')">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                    </button>
                                </form>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/dashboard/pegawai/pengajuan/partials/_table.blade.php ENDPATH**/ ?>