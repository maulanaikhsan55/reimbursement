<header class="landing-header">
    <div class="header-container">
        <a href="#home" class="logo">
            <img src="<?php echo e(asset('images/logo.png')); ?>" alt="Logo" class="logo-image" width="350" height="100" decoding="async">
        </a>

        <nav class="nav-menu" aria-label="Primary Navigation">
            <a href="#home" class="nav-link">Home</a>
            <a href="#features" class="nav-link">Technology</a>
            <a href="#roles" class="nav-link">Ecosystem</a>
            <a href="#faq" class="nav-link">FAQ</a>
        </nav>

        <div class="header-actions">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isFinance()): ?>
                    <a href="<?php echo e(route('finance.dashboard')); ?>" class="btn btn-primary btn-sm" data-external>Dashboard</a>
                <?php elseif(auth()->user()->isAtasan()): ?>
                    <a href="<?php echo e(route('atasan.dashboard')); ?>" class="btn btn-primary btn-sm" data-external>Dashboard</a>
                <?php elseif(auth()->user()->isPegawai()): ?>
                    <a href="<?php echo e(route('pegawai.dashboard')); ?>" class="btn btn-primary btn-sm" data-external>Dashboard</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php else: ?>
                <a href="<?php echo e(route('login')); ?>" class="btn btn-primary btn-sm" data-external>Get Started</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="menu-toggle" id="menuToggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</header>
<?php /**PATH C:\xampp\htdocs\reimbursement_ikhsansblm dipush\resources\views/components/landing-header.blade.php ENDPATH**/ ?>