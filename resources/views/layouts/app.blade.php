<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'Humplus Reimbursement')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/css/dashboard-ultra.css', 'resources/css/dashboard/finance.css', 'resources/css/pages/pegawai/dashboard.css', 'resources/css/pages/pegawai/pengajuan.css', 'resources/css/modules/pengajuan-detail.css', 'resources/css/pages/pegawai/notifikasi.css', 'resources/css/pages/pegawai/profile.css', 'resources/css/pages/atasan/dashboard.css', 'resources/css/pages/pegawai/responsive-fixes.css', 'resources/js/app.js', 'resources/js/dashboard-ultra.js', 'resources/js/finance/finance.js'])

 
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    @livewireStyles
    @stack('styles')
    <style>
      
        .progress-bar-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: transparent;
            z-index: 9999999;
            overflow: hidden;
            pointer-events: none;
            opacity: 0;
            transform: translateZ(0);
            backface-visibility: hidden;
            will-change: opacity;
        }

        .progress-bar-fill {
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 100%;
            background: linear-gradient(90deg, #425d87 0%, #6366f1 50%, #8b5cf6 100%);
            background-size: 200% 100%;
            border-radius: 0 2px 2px 0;
            will-change: transform;
            transform: translateX(-100%);
            opacity: 0;
        }

        /* Active state - SEAMLESS continuous flow from left to right */
        .progress-bar-active {
            opacity: 1 !important;
        }

        .progress-bar-active .progress-bar-fill {
            opacity: 1;
            animation: smooth-flow 0.6s ease-in-out infinite;
        }

        @keyframes smooth-flow {
            0% {
                transform: translateX(-100%);
            }
            50% {
                transform: translateX(0%);
            }
            51% {
                transform: translateX(100%);
            }
            100% {
                transform: translateX(100%);
            }
        }

        /* Exit state - instant vanish */
        .progress-bar-exit {
            opacity: 0 !important;
            transition: opacity 0.05s ease-out !important;
        }

        .progress-bar-exit .progress-bar-fill {
            transform: translateX(100%) !important;
            transition: transform 0.05s ease-out !important;
        }

        /* PAGE TRANSITION - Ultra fast */
        .page-content {
            animation: quickFade 0.15s ease-out;
            will-change: opacity, transform;
        }

        @keyframes quickFade {
            from { opacity: 0.97; transform: translateY(1px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* BUTTON RIPPLE EFFECT - GPU Accelerated */
        .btn-modern {
            position: relative;
            overflow: hidden;
            transform: translateZ(0);
            backface-visibility: hidden;
            -webkit-tap-highlight-color: transparent;
        }

        .btn-modern::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.25s ease-out, height 0.25s ease-out, opacity 0.25s ease-out;
            opacity: 0;
        }

        .btn-modern:active::after {
            width: 400px;
            height: 400px;
            opacity: 1;
            transition: 0s;
        }

        .btn-modern:active:not(:disabled) {
            transform: scale(0.97) translateZ(0);
        }

        /* SIDEBAR ITEM GLOW ON HOVER - Fast response */
        .menu-item {
            position: relative;
            transition: transform 0.15s ease, text-shadow 0.15s ease;
            transform: translateZ(0);
        }

        .menu-item:hover {
            text-shadow: 0 0 10px rgba(66, 93, 135, 0.3);
            transform: translateX(4px);
        }

        .menu-sliding-bg {
            box-shadow: 0 0 20px rgba(66, 93, 135, 0.6), 0 4px 16px rgba(66, 93, 135, 0.4);
            transition: top 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* BADGE PULSE ANIMATION */
        .badge-sidebar {
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        /* NOTIFICATION BELL SHAKE - Faster */
        .notification-bell.shake {
            animation: shake 0.4s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: rotate(0); }
            20% { transform: rotate(-15deg); }
            40% { transform: rotate(15deg); }
            60% { transform: rotate(-10deg); }
            80% { transform: rotate(10deg); }
        }

        /* CARD HOVER LIFT - Fast response */
        .card {
            transition: transform 0.15s ease-out, box-shadow 0.15s ease-out;
            transform: translateZ(0);
            backface-visibility: hidden;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        /* STATUS BADGE GLOW - Fast */
        .status-badge {
            transition: box-shadow 0.15s ease, transform 0.1s ease;
        }

        .status-badge:hover {
            box-shadow: 0 0 12px rgba(66, 93, 135, 0.3);
            transform: scale(1.05);
        }

        /* ULTRA SMOOTH UTILITIES */
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px) saturate(180%);
            -webkit-backdrop-filter: blur(12px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .badge-bounce {
            animation: badge-bounce 0.5s cubic-bezier(0.36, 0, 0.66, -0.56) alternate 2;
        }
        @keyframes badge-bounce {
            0% { transform: scale(1); }
            100% { transform: scale(1.4); }
        }

        .main-content {
            transition: margin-left 0.25s ease, width 0.25s ease;
            width: 100%;
        }

        .main-content-shifted {
            margin-left: 260px;
            width: calc(100% - 260px);
        }

        .content {
            padding: 1rem !important;
        }

        @media (max-width: 768px) {
            .main-content-shifted {
                margin-left: 0 !important;
                width: 100% !important;
                padding-top: 60px !important;
            }
            .content {
                padding: 0.75rem !important;
            }
        }

        /* Mobile Header */
        .mobile-header {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: white;
            z-index: 999;
            padding: 0 1rem;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        @media (max-width: 768px) {
            .mobile-header {
                display: flex;
            }
        }

        .mobile-toggle {
            background: #f4f7fa;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #425d87;
            cursor: pointer;
        }

        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 998;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .sidebar-backdrop.show {
            display: block;
            opacity: 1;
        }

        /* ==========================================================================
           STAGGERED ENTRANCE ANIMATIONS - Lists, Tables, Cards
           ========================================================================== */
        
        /* Table rows stagger animation */
        .data-table tbody tr {
            opacity: 0;
            transform: translateY(8px);
            animation: table-row-in 0.2s ease-out forwards;
            will-change: opacity, transform;
        }

        .data-table tbody tr:nth-child(1) { animation-delay: 0ms; }
        .data-table tbody tr:nth-child(2) { animation-delay: 40ms; }
        .data-table tbody tr:nth-child(3) { animation-delay: 80ms; }
        .data-table tbody tr:nth-child(4) { animation-delay: 120ms; }
        .data-table tbody tr:nth-child(5) { animation-delay: 160ms; }
        .data-table tbody tr:nth-child(6) { animation-delay: 200ms; }
        .data-table tbody tr:nth-child(7) { animation-delay: 240ms; }
        .data-table tbody tr:nth-child(8) { animation-delay: 280ms; }
        .data-table tbody tr:nth-child(9) { animation-delay: 320ms; }
        .data-table tbody tr:nth-child(10) { animation-delay: 360ms; }

        @keyframes table-row-in {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Stat cards stagger */
        .stat-card {
            opacity: 0;
            transform: scale(0.95) translateY(10px);
            animation: stat-card-in 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        .stat-card:nth-child(1) { animation-delay: 0ms; }
        .stat-card:nth-child(2) { animation-delay: 50ms; }
        .stat-card:nth-child(3) { animation-delay: 100ms; }
        .stat-card:nth-child(4) { animation-delay: 150ms; }

        @keyframes stat-card-in {
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Stagger item class for custom lists */
        .stagger-item {
            opacity: 0;
            transform: translateY(10px);
            animation: stagger-in 0.25s ease-out forwards;
            will-change: opacity, transform;
        }

        .stagger-item:nth-child(1) { animation-delay: 0ms; }
        .stagger-item:nth-child(2) { animation-delay: 50ms; }
        .stagger-item:nth-child(3) { animation-delay: 100ms; }
        .stagger-item:nth-child(4) { animation-delay: 150ms; }
        .stagger-item:nth-child(5) { animation-delay: 200ms; }
        .stagger-item:nth-child(6) { animation-delay: 250ms; }

        @keyframes stagger-in {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Content flash effect on load - perception of speed */
        @keyframes content-flash {
            0% {
                background-color: rgba(99, 102, 241, 0.03);
            }
            100% {
                background-color: transparent;
            }
        }

        .content-flash {
            animation: content-flash 0.25s ease-out;
            will-change: background-color;
        }
    </style>
    
    <script>
        // Enable View Transitions API if available (Progressive Enhancement)
        if ('startViewTransition' in document) {
            document.documentElement.classList.add('view-transitions-available');
        }
    </script>
    
    <!-- FALLBACK: Livewire config callbacks BEFORE Livewire loads -->
    <script>
        // Initialize progress bar functions as no-ops until dashboard-ultra.js loads
        window.showProgressBar = window.showProgressBar || function() { console.log('[ProgressBar] show (pending)'); };
        window.hideProgressBar = window.hideProgressBar || function() { console.log('[ProgressBar] hide (pending)'); };
        window.hideAllLoaders = window.hideAllLoaders || function() { /* no-op until loaded */ };
    </script>
</head>
<body>
    <!-- Modern Progress Bar - LinkedIn/Twitter Style -->
    <div class="progress-bar-container" id="modernProgressBar">
        <div class="progress-bar-fill"></div>
    </div>

    <div class="wrapper">
        <div class="mobile-header">
            <button class="mobile-toggle" id="mobileSidebarToggle">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <div class="mobile-logo">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" style="height: 30px;">
            </div>
            <div style="width: 40px;"></div> <!-- Spacer for centering -->
        </div>

        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <div id="sidebar-wrapper">
            @if (request()->routeIs('finance.*'))
                <x-finance-sidebar />
            @elseif (request()->routeIs('pegawai.*'))
                <x-pegawai-sidebar />
            @elseif (request()->routeIs('atasan.*'))
                <x-atasan-sidebar />
            @endif
        </div>
        
        <div class="main-content {{ (request()->is('finance*') || request()->is('pegawai*') || request()->is('atasan*')) ? 'main-content-shifted' : '' }}" style="position: relative; min-height: 100vh;">
            <!-- Unified Loading System: Only ONE loader active at a time -->
            
            <!-- PAGE CONTENT -->
            <div class="content" style="position: relative;">
                <div id="page-content-wrapper" style="min-height: 400px;">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <x-confirm-modal />

    <!-- Global Loader (Minimalist & Non-Blocking) -->
    <div id="global-loader" style="position: fixed; inset: 0; z-index: 9000; background-color: rgba(255, 255, 255, 0.4); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; transition: all 0.3s ease; opacity: 0; pointer-events: none;">
        <div class="loader-content" style="background: white; padding: 1.5rem; border-radius: 1.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.05); display: flex; flex-direction: column; align-items: center; gap: 0.75rem;">
            <div class="clip-loader" style="width: 24px; height: 24px; border: 2.5px solid #425d87; border-bottom-color: transparent; border-radius: 50%; animation: clip-loader-spin 0.75s linear infinite;"></div>
            <span style="font-size: 0.7rem; color: #64748b; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase;">Memproses...</span>
        </div>
    </div>

    @if(session('success') || session('error') || session('warning') || session('info') || $errors->any())
    <script>
        (function() {
            const initNotifications = () => {
                // FORCE HIDE ANY LOADERS BEFORE SHOWING NOTIF
                if (window.hideAllLoaders) window.hideAllLoaders();
                const gLoader = document.getElementById('global-loader');
                if (gLoader) { 
                    gLoader.style.opacity = '0'; 
                    gLoader.style.pointerEvents = 'none';
                    gLoader.style.zIndex = '-1';
                }

                @if (session('success'))
                    window.showNotification('success', 'Berhasil', "{!! addslashes(session('success')) !!}");
                @endif

                @if (session('error'))
                    window.showNotification('error', 'Terjadi Kesalahan', "{!! addslashes(session('error')) !!}");
                @endif

                @if (session('warning'))
                    window.showNotification('warning', 'Peringatan', "{!! addslashes(session('warning')) !!}");
                @endif

                @if (session('info'))
                    window.showNotification('info', 'Informasi', "{!! addslashes(session('info')) !!}");
                @endif

                @if ($errors->any())
                    let errorHtml = '<ul style="margin: 5px 0 0; padding-left: 15px; text-align: left;">';
                    @foreach ($errors->all() as $error)
                        errorHtml += '<li>{!! addslashes($error) !!}</li>';
                    @endforeach
                    errorHtml += '</ul>';
                    
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 5000,
                        timerProgressBar: true,
                        showCloseButton: true,
                        customClass: { popup: 'swal2-modern-toast' },
                        icon: 'error',
                        title: 'Validasi Gagal',
                        html: `<div style="font-family: \'Poppins\', sans-serif; font-size: 0.85rem; color: #64748b;">${errorHtml}</div>`
                    });
                @endif
            };

            if (document.readyState === 'complete') {
                setTimeout(initNotifications, 100);
            } else {
                window.addEventListener('load', () => setTimeout(initNotifications, 100));
            }
        })();
    </script>
    @endif

    <!-- PREFETCHING & NAVIGATION SMART SCRIPTS -->
    
    <script data-navigate-once>
        let skeletonTimeout;

        // Mobile Sidebar Toggle Logic
        document.addEventListener('click', function(e) {
            const toggle = document.getElementById('mobileSidebarToggle');
            const backdrop = document.getElementById('sidebarBackdrop');
            const sidebar = document.querySelector('.pegawai-sidebar, .finance-sidebar, .atasan-sidebar');
            
            if (e.target.closest('#mobileSidebarToggle')) {
                sidebar?.classList.toggle('show');
                backdrop?.classList.toggle('show');
                document.body.style.overflow = sidebar?.classList.contains('show') ? 'hidden' : '';
            } else if (e.target.closest('#sidebarBackdrop') || (e.target.closest('.menu-item') && window.innerWidth <= 768)) {
                sidebar?.classList.remove('show');
                backdrop?.classList.remove('show');
                document.body.style.overflow = '';
            }
        });

        document.addEventListener('livewire:navigate', () => {
            // SHOW ULTRA MODERN PROGRESS BAR IMMEDIATELY
            if (window.showProgressBar) {
                window.showProgressBar();
            }

            // Only show skeleton if navigation takes longer than 300ms
            if (skeletonTimeout) clearTimeout(skeletonTimeout);
            
            skeletonTimeout = setTimeout(() => {
                const skeleton = document.getElementById('skeleton-overlay');
                if (skeleton && !document.body.classList.contains('ready')) {
                    skeleton.classList.add('show');
                }
            }, 300);
        });

        document.addEventListener('livewire:navigated', () => {
            // HIDE ALL LOADERS immediately when content is ready
            if (window.hideProgressBar) {
                window.hideProgressBar();
            }
            if (window.hideAllLoaders) {
                window.hideAllLoaders();
            }
            
            // Remove skeleton immediately
            const skeleton = document.getElementById('skeleton-overlay');
            if (skeleton) {
                skeleton.classList.remove('show');
            }
            if (window.hideAllLoaders) window.hideAllLoaders();

            if (skeletonTimeout) clearTimeout(skeletonTimeout);
            
            // Force re-trigger animation for content
            const wrapper = document.getElementById('page-content-wrapper');
            if (wrapper) {
                wrapper.classList.remove('page-transition-fade');
                void wrapper.offsetWidth; // trigger reflow
                wrapper.classList.add('page-transition-fade');
            }

            // Trigger resize to fix layout/charts
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 100);
            
            // Re-run AOS
            if (typeof AOS !== 'undefined') {
                AOS.refresh();
            }
        });
    </script>

    <!-- ===================================================== -->
    <!-- SCRIPT ORDER -->
    <!-- ===================================================== -->
    
    <!-- 1. Livewire (includes Alpine.js in v3) -->
    @livewireScripts

    <!-- 4. OCR & PDF Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@5/dist/tesseract.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        if (window.pdfjsLib) {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }
    </script>

    <!-- 5. Page Scripts -->
    @stack('scripts')
    
    @auth
    <script data-navigate-once>
        const initEchoListener = () => {
            if (window.Echo) {
                window.Echo.private('App.Models.User.{{ Auth::id() }}')
                    .listen('.notifikasi.pengajuan', (e) => {
                        window.showNotification(
                            e.type === 'error' ? 'error' : (e.type === 'success' ? 'success' : 'info'),
                            e.title ?? 'Notifikasi',
                            e.message ?? '',
                            6000
                        );
                        window.dispatchEvent(new CustomEvent('refresh-notif-badges'));
                        if (window.Livewire) {
                            window.Livewire.dispatch('notifikasi-baru');
                        }
                        window.dispatchEvent(new CustomEvent('refresh-approval-table'));
                        window.dispatchEvent(new CustomEvent('refresh-pengajuan-table'));
                    });
            } else {
                setTimeout(initEchoListener, 200);
            }
        };

        document.addEventListener('DOMContentLoaded', () => setTimeout(initEchoListener, 500));
        document.addEventListener('livewire:navigated', () => setTimeout(initEchoListener, 300));
    </script>
    @endauth
</body>
</html>
