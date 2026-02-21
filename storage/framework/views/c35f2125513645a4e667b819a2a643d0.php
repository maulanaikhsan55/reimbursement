<div>
    <!-- Quick Stats -->
    <div class="notif-stats">
        <div class="stat-card modern">
            <div class="stat-left">
                <div class="stat-value"><?php echo e(\App\Models\Notifikasi::where('user_id', auth()->id())->where('is_read', false)->count()); ?></div>
                <div class="stat-label">Belum Dibaca</div>
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
                <div class="stat-value"><?php echo e(\App\Models\Notifikasi::where('user_id', auth()->id())->count()); ?></div>
                <div class="stat-label">Total Notifikasi</div>
            </div>
            <div class="stat-icon primary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="notif-section">
        <div class="notif-header">
            <div class="notif-header-copy">
                <h2 class="section-title">Aktivitas Terbaru</h2>
                <p class="section-subtitle notif-subtitle">Notifikasi terbaru yang perlu perhatian dan tindak lanjut</p>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\App\Models\Notifikasi::where('user_id', auth()->id())->where('is_read', false)->count() > 0): ?>
                <button
                    type="button"
                    class="btn-modern btn-modern-secondary btn-modern-sm"
                    x-on:click.prevent.stop="$wire.markAllAsRead()"
                    wire:loading.attr="disabled"
                    wire:target="markAllAsRead"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 14px; height: 14px; margin-right: 6px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <span wire:loading.remove wire:target="markAllAsRead">Tandai Semua Dibaca</span>
                    <span wire:loading wire:target="markAllAsRead">Memproses...</span>
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($notifikasi->count() > 0): ?>
            <div class="notif-items">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $notifikasi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="notif-card <?php echo e(!$item->is_read ? 'unread' : ''); ?>" wire:key="notif-<?php echo e($item->notifikasi_id); ?>">
                        <div class="notif-content" wire:click="markAsRead('<?php echo e($item->notifikasi_id); ?>')" style="cursor: pointer;">
                            <div class="notif-title"><?php echo e($item->judul); ?></div>
                            <div class="notif-message"><?php echo e($item->pesan); ?></div>
                            <div class="notif-meta">
                                <span class="notif-type"><?php echo e(ucfirst(str_replace('_', ' ', $item->tipe))); ?></span>
                                <span class="notif-time"><?php echo e($item->created_at->diffForHumans()); ?></span>
                            </div>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$item->is_read): ?>
                            <button wire:click="markAsRead('<?php echo e($item->notifikasi_id); ?>')" class="btn-check" title="Tandai dibaca">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="notif-pagination">
                <?php echo e($notifikasi->links('components.pagination')); ?>

            </div>
        <?php else: ?>
            <div class="notif-empty">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <div class="notif-empty-title">Tidak Ada Notifikasi</div>
                <p>Semua notifikasi telah ditandai dibaca</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/livewire/notification-list.blade.php ENDPATH**/ ?>