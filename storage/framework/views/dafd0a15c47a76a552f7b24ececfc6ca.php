<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paginator->total() > 0): ?>
<?php
    $normalizePaginationUrl = static function (?string $url): ?string {
        if (! $url) {
            return $url;
        }

        if (preg_match('/^(https?:\/\/|\/\/|\/|#|\?)/i', $url)) {
            return $url;
        }

        return '/'.ltrim($url, '/');
    };
?>
<div class="pagination-footer-wrapper">
    <div class="pagination-info">
        Menampilkan <strong><?php echo e($paginator->firstItem()); ?></strong> sampai <strong><?php echo e($paginator->lastItem()); ?></strong> dari <strong><?php echo e($paginator->total()); ?></strong> data
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paginator->hasPages()): ?>
    <nav role="navigation" aria-label="<?php echo e(__('Pagination Navigation')); ?>" class="pagination-container">
        <div class="pagination-wrapper">
        
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paginator->onFirstPage()): ?>
                <button class="pagination-arrow disabled" aria-disabled="true" disabled>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>
            <?php else: ?>
                <a href="<?php echo e($normalizePaginationUrl($paginator->previousPageUrl())); ?>" rel="prev" class="pagination-arrow">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="pagination-numbers">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_string($element)): ?>
                        <span class="pagination-dots"><?php echo e($element); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(is_array($element)): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($page == $paginator->currentPage()): ?>
                                <button class="pagination-number active" aria-current="page" disabled><?php echo e($page); ?></button>
                            <?php else: ?>
                                <a href="<?php echo e($normalizePaginationUrl($url)); ?>" class="pagination-number"><?php echo e($page); ?></a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($paginator->hasMorePages()): ?>
                <a href="<?php echo e($normalizePaginationUrl($paginator->nextPageUrl())); ?>" rel="next" class="pagination-arrow">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
            <?php else: ?>
                <button class="pagination-arrow disabled" aria-disabled="true" disabled>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</nav>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>

<style>
        .pagination-footer-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 1rem 0;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .pagination-info {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .pagination-info strong {
            color: #111827;
            font-weight: 600;
        }

        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0.25rem 0 0 0 !important;
            padding: 0.5rem 0 !important;
            width: 100%;
        }

        .pagination-wrapper {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .pagination-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 0.375rem;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            font-weight: 500;
        }

        .pagination-arrow:hover:not(.disabled) {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.2);
        }

        .pagination-arrow.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f9fafb;
            color: #d1d5db;
            border-color: #f3f4f6;
        }

        .pagination-numbers {
            display: flex;
            align-items: center;
            gap: 0.375rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .pagination-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 0.5rem;
            border-radius: 0.375rem;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            color: #374151;
            font-weight: 500;
            font-size: 0.8125rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .pagination-number:hover:not(.active) {
            background: #f3f4f6;
            color: #2563eb;
            border-color: #2563eb;
        }

        .pagination-number.active {
            background: #2563eb;
            color: white;
            font-weight: 600;
            border-color: #2563eb;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.2);
        }

        .pagination-dots {
            color: #9ca3af;
            padding: 0 0.25rem;
            font-weight: 500;
            font-size: 0.8125rem;
        }
    </style>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/components/pagination.blade.php ENDPATH**/ ?>