<?php $__env->startSection('title', 'Detail Pengajuan'); ?>

<?php $__env->startSection('content'); ?>
<div class="dashboard-wrapper">
    <div class="dashboard-container">
    <?php if (isset($component)) { $__componentOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalf8d4ea307ab1e58d4e472a43c8548d8e = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.page-header','data' => ['title' => 'Detail Pengajuan','subtitle' => 'Nomor: '.e($pengajuan->nomor_pengajuan).'','showNotification' => true,'showProfile' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('page-header'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Detail Pengajuan','subtitle' => 'Nomor: '.e($pengajuan->nomor_pengajuan).'','showNotification' => true,'showProfile' => true]); ?>
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

    <div class="dashboard-content detail-single-content">
        <!-- Status Section -->
        <section class="modern-section">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Status Pengajuan</h2>
                </div>
                <a href="<?php echo e(route('atasan.pengajuan.index')); ?>" class="link-back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    Kembali
                </a>
            </div>

            <div class="timeline">
                <div class="timeline-item active">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <h6 class="mb-1">Diajukan</h6>
                        <p class="text-muted small mb-0"><?php echo e($pengajuan->created_at->format('d M Y, H:i')); ?></p>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengajuan->status->value == 'menunggu_atasan' || $pengajuan->tanggal_disetujui_atasan || $pengajuan->status->value == 'ditolak_atasan'): ?>
                    <div class="timeline-item <?php echo e($pengajuan->tanggal_disetujui_atasan || $pengajuan->status->value == 'ditolak_atasan' ? 'active' : ''); ?>">
                        <div class="timeline-dot <?php echo e($pengajuan->status->value == 'ditolak_atasan' ? 'bg-danger' : ''); ?>"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Review Atasan</h6>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengajuan->tanggal_disetujui_atasan): ?>
                                <p class="text-muted small mb-0">Disetujui - <?php echo e($pengajuan->tanggal_disetujui_atasan->format('d M Y')); ?></p>
                                <p class="text-muted small mb-0"><?php echo e($pengajuan->approvedByAtasan->name ?? '-'); ?></p>
                            <?php elseif($pengajuan->status->value == 'ditolak_atasan'): ?>
                                <p class="text-danger small mb-0">Ditolak</p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengajuan->catatan_atasan): ?>
                                    <p class="text-danger small fst-italic mt-1">"<?php echo e($pengajuan->catatan_atasan); ?>"</p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?>
                                <p class="text-muted small mb-0">Menunggu persetujuan...</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengajuan->status->value == 'menunggu_finance' || $pengajuan->tanggal_disetujui_finance || $pengajuan->status->value == 'ditolak_finance' || $pengajuan->status->value == 'terkirim_accurate' || $pengajuan->status->value == 'dicairkan'): ?>
                    <div class="timeline-item <?php echo e($pengajuan->tanggal_disetujui_finance || $pengajuan->status->value == 'ditolak_finance' || $pengajuan->status->value == 'terkirim_accurate' || $pengajuan->status->value == 'dicairkan' ? 'active' : ''); ?>">
                        <div class="timeline-dot <?php echo e($pengajuan->status->value == 'ditolak_finance' ? 'bg-danger' : ''); ?>"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Review Finance</h6>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengajuan->status->value == 'terkirim_accurate' || $pengajuan->status->value == 'dicairkan'): ?>
                                <p class="text-muted small mb-0">Disetujui Finance</p>
                            <?php elseif($pengajuan->status->value == 'ditolak_finance'): ?>
                                <p class="text-danger small mb-0">Ditolak</p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengajuan->catatan_finance): ?>
                                    <p class="text-danger small fst-italic mt-1">"<?php echo e($pengajuan->catatan_finance); ?>"</p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php else: ?>
                                <p class="text-muted small mb-0">Menunggu proses...</p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengajuan->status->value == 'dicairkan'): ?>
                    <div class="timeline-item active">
                        <div class="timeline-dot bg-success"></div>
                        <div class="timeline-content">
                            <h6 class="mb-1">Dicairkan</h6>
                            <p class="text-muted small mb-0"><?php echo e($pengajuan->tanggal_pencairan ? $pengajuan->tanggal_pencairan->format('d M Y') : '-'); ?></p>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengajuan->status->value == 'menunggu_finance'): ?>
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e8ecf1;">
                    <form action="<?php echo e(route('atasan.pengajuan.destroy', $pengajuan->pengajuan_id)); ?>" method="POST" style="display: inline-block;">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="button" class="btn-modern btn-modern-danger" onclick="openConfirmModal(() => this.closest('form').submit(), 'Batalkan Pengajuan', 'Apakah Anda yakin ingin membatalkan pengajuan ini? Data akan dihapus.')">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; margin-right: 8px;">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                            Batalkan Pengajuan
                        </button>
                    </form>
                    <p class="text-muted small mt-2">Anda dapat membatalkan pengajuan selama belum disetujui oleh finance.</p>
                </div>
            <?php elseif($pengajuan->status->value == 'ditolak_atasan' || $pengajuan->status->value == 'ditolak_finance'): ?>
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid #e8ecf1;">
                    <a href="<?php echo e(route('atasan.pengajuan.create')); ?>" class="btn-modern btn-modern-primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px; margin-right: 8px;">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Pengajuan Baru
                    </a>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>

        <?php if (isset($component)) { $__componentOriginal64b96e2218f28f533bfdb9d2d5f74543 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal64b96e2218f28f533bfdb9d2d5f74543 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.budget-indicator','data' => ['status' => $budgetStatus,'departmentName' => $pengajuan->departemen->nama_departemen ?? 'Departemen']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('budget-indicator'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['status' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($budgetStatus),'departmentName' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pengajuan->departemen->nama_departemen ?? 'Departemen')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal64b96e2218f28f533bfdb9d2d5f74543)): ?>
<?php $attributes = $__attributesOriginal64b96e2218f28f533bfdb9d2d5f74543; ?>
<?php unset($__attributesOriginal64b96e2218f28f533bfdb9d2d5f74543); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal64b96e2218f28f533bfdb9d2d5f74543)): ?>
<?php $component = $__componentOriginal64b96e2218f28f533bfdb9d2d5f74543; ?>
<?php unset($__componentOriginal64b96e2218f28f533bfdb9d2d5f74543); ?>
<?php endif; ?>

        <!-- Detail Info Section -->
        <section class="modern-section">
            <div class="section-header" style="margin-bottom: 1.25rem; padding-bottom: 0; border: none;">
                <h2 class="section-title">Informasi Pengajuan</h2>
            </div>

            <div class="detail-grid">
                <div class="detail-item">
                    <div class="detail-label">Nama Vendor</div>
                    <div class="detail-value"><?php echo e($pengajuan->nama_vendor); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Tanggal Transaksi</div>
                    <div class="detail-value"><?php echo e($pengajuan->tanggal_transaksi->format('d F Y')); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Kategori Biaya</div>
                    <div class="detail-value"><?php echo e($pengajuan->kategori->nama_kategori ?? '-'); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Nominal</div>
                    <div class="detail-value text-lg text-primary"><?php echo e(format_rupiah($pengajuan->nominal)); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Status Saat Ini</div>
                    <div class="detail-value">
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
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">No. Pengajuan</div>
                    <div class="detail-value text-mono"><?php echo e($pengajuan->nomor_pengajuan); ?></div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Tanggal Pengajuan</div>
                    <div class="detail-value"><?php echo e($pengajuan->created_at->format('d M Y')); ?></div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Bukti Transaksi</div>
                    <div class="detail-value">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengajuan->file_bukti): ?>
                            <button type="button" class="btn-modern btn-modern-secondary btn-modern-sm" onclick="openProofModal('<?php echo e(route('proof.show', $pengajuan)); ?>', <?php echo e(str_ends_with(strtolower($pengajuan->file_bukti), '.pdf') ? 'true' : 'false'); ?>)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; display: inline; margin-right: 0.5rem;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                                Lihat Bukti
                            </button>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="detail-item full-width">
                    <div class="detail-label">Deskripsi</div>
                    <div class="detail-value description-box"><?php echo e($pengajuan->deskripsi); ?></div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengajuan->catatan_pegawai): ?>
                <div class="detail-item full-width">
                    <div class="detail-label">Catatan Tambahan</div>
                    <div class="detail-value description-box"><?php echo e($pengajuan->catatan_pegawai); ?></div>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <!-- AI Validation Result Section -->
        <section class="modern-section">
            <div class="section-header" style="margin-bottom: 1.75rem; padding-bottom: 0; border: none;">
                <h2 class="section-title" style="display: flex; align-items: center; gap: 0.75rem;">
                    <?php if (isset($component)) { $__componentOriginalce262628e3a8d44dc38fd1f3965181bc = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalce262628e3a8d44dc38fd1f3965181bc = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.icon','data' => ['name' => 'cpu','class' => 'w-6 h-6 text-primary','style' => 'color: #4f46e5;']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('icon'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['name' => 'cpu','class' => 'w-6 h-6 text-primary','style' => 'color: #4f46e5;']); ?>
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
                    Hasil Validasi AI
                </h2>
                <p class="text-muted small mb-0" style="margin-top: 0.5rem;">Sistem AI telah memvalidasi dokumen Anda secara otomatis</p>
            </div>
            
            <?php if (isset($component)) { $__componentOriginal259598a25870b77ba27e5af2e5a125d4 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal259598a25870b77ba27e5af2e5a125d4 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ai-validation-result','data' => ['results' => $pengajuan->validasiAi]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ai-validation-result'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['results' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($pengajuan->validasiAi)]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal259598a25870b77ba27e5af2e5a125d4)): ?>
<?php $attributes = $__attributesOriginal259598a25870b77ba27e5af2e5a125d4; ?>
<?php unset($__attributesOriginal259598a25870b77ba27e5af2e5a125d4); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal259598a25870b77ba27e5af2e5a125d4)): ?>
<?php $component = $__componentOriginal259598a25870b77ba27e5af2e5a125d4; ?>
<?php unset($__componentOriginal259598a25870b77ba27e5af2e5a125d4); ?>
<?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$pengajuan->validasiAi->isEmpty() && $pengajuan->status->value !== 'validasi_ai'): ?>
                <!-- Status Summary -->
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                    <?php
                        $allPass = $pengajuan->validasiAi->every(fn($v) => $v->status->value === 'valid');
                        $hasFail = $pengajuan->validasiAi->some(fn($v) => $v->status->value === 'invalid');
                    ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($allPass): ?>
                        <div class="alert alert-success d-flex align-items-center" style="background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; padding: 1rem; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            <div>
                                <strong style="display: block; font-size: 0.95rem;">Semua Validasi AI Lolos</strong>
                                <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Pengajuan Anda telah diverifikasi otomatis oleh sistem AI dengan hasil sempurna.</p>
                            </div>
                        </div>
                    <?php elseif($hasFail): ?>
                        <div class="alert alert-danger d-flex align-items-center" style="background-color: #fef2f2; color: #991b1b; border: 1px solid #fecdd3; padding: 1rem; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            <div>
                                <strong style="display: block; font-size: 0.95rem;">Catatan Validasi AI</strong>
                                <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Sistem AI mendeteksi beberapa ketidaksesuaian. Hal ini mungkin akan ditinjau lebih lanjut oleh finance.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning d-flex align-items-center" style="background-color: #fffbeb; color: #92400e; border: 1px solid #fde68a; padding: 1rem; border-radius: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
                            <svg viewBox="0 0 24 24" width="24" height="24" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            <div>
                                <strong style="display: block; font-size: 0.95rem;">Perhatian AI</strong>
                                <p style="margin: 0; font-size: 0.85rem; opacity: 0.9;">Beberapa detail dalam dokumen Anda mendapatkan catatan dari sistem AI.</p>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengajuan->status->value === 'validasi_ai'): ?>
                <div style="margin-top: 1.5rem; padding: 1.5rem; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.75rem;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pengajuan->status_validasi->value === 'invalid'): ?>
                        <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div style="color: #dc2626; margin-top: 0.25rem;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="15" y1="9" x2="9" y2="15"></line>
                                    <line x1="9" y1="9" x2="15" y2="15"></line>
                                </svg>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 0.5rem 0; color: #7f1d1d; font-size: 1rem; font-weight: 600;">Validasi AI Tidak Lolos</h4>
                                <p style="margin: 0 0 1rem 0; color: #991b1b; font-size: 0.875rem; line-height: 1.5;">
                                    Pengajuan Anda tidak memenuhi kriteria validasi otomatis. Pastikan nominal dan tanggal sesuai dengan bukti pembayaran.
                                </p>
                                
                                <form action="<?php echo e(route('atasan.pengajuan.destroy', $pengajuan->pengajuan_id)); ?>" method="POST">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="button" class="btn-modern btn-modern-danger btn-modern-sm" onclick="openConfirmModal(() => this.closest('form').submit(), 'Batalkan & Hapus', 'Yakin ingin membatalkan dan menghapus pengajuan ini?')" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                                        <span>Batalkan & Hapus</span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                         <div style="display: flex; gap: 1rem; align-items: flex-start;">
                            <div style="color: #3b82f6; margin-top: 0.25rem;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <line x1="12" y1="16" x2="12" y2="12"></line>
                                    <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                </svg>
                            </div>
                            <div>
                                <h4 style="margin: 0 0 0.5rem 0; color: #1e3a8a; font-size: 1rem; font-weight: 600;">Sedang Diproses</h4>
                                <p style="margin: 0 0 1rem 0; color: #1e40af; font-size: 0.875rem; line-height: 1.5;">
                                    Pengajuan Anda sedang dalam antrian proses sistem. Silakan refresh halaman beberapa saat lagi.
                                </p>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>
    </div>
    </div>
</div>

<?php if (isset($component)) { $__componentOriginal76fcdb01cf34d52c9c975265300be645 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal76fcdb01cf34d52c9c975265300be645 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.proof-modal','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('proof-modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal76fcdb01cf34d52c9c975265300be645)): ?>
<?php $attributes = $__attributesOriginal76fcdb01cf34d52c9c975265300be645; ?>
<?php unset($__attributesOriginal76fcdb01cf34d52c9c975265300be645); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal76fcdb01cf34d52c9c975265300be645)): ?>
<?php $component = $__componentOriginal76fcdb01cf34d52c9c975265300be645; ?>
<?php unset($__componentOriginal76fcdb01cf34d52c9c975265300be645); ?>
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/dashboard/atasan/pengajuan/show.blade.php ENDPATH**/ ?>