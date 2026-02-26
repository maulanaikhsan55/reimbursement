<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#243b61">
    <title>Reimbursement Platform - Smart Expense Management</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    
    <!-- Performance Optimization: Preconnect & DNS-Prefetch -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://unpkg.com">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://unpkg.com">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css">
    @vite(['resources/css/app.css', 'resources/css/landing.css', 'resources/js/app.js', 'resources/js/dashboard-ultra.js'])
    <style>
        body {
            opacity: 0;
            background: #ffffff;
        }

        body.ready {
            opacity: 1;
            transition: opacity 0.5s ease;
        }

        @keyframes clip-loader-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .btn.is-loading {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            pointer-events: none;
            cursor: wait;
            opacity: 0.88;
            filter: saturate(0.95);
        }

        .btn-inline-loader {
            width: 1em;
            height: 1em;
            border-radius: 999px;
            border: 2px solid currentColor;
            border-right-color: transparent;
            display: inline-block;
            flex: 0 0 auto;
            animation: clip-loader-spin 0.65s linear infinite;
        }

        .btn-loading-text {
            white-space: nowrap;
        }

        .route-loading-status {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        @keyframes topbar-route-loading {
            0% {
                width: 22%;
                transform: translateX(-120%);
            }
            50% {
                width: 42%;
            }
            100% {
                width: 22%;
                transform: translateX(460%);
            }
        }

        #scrollProgress.is-route-loading {
            width: 22% !important;
            animation: topbar-route-loading 1s ease-in-out infinite;
            will-change: transform, width;
        }

        @media (prefers-reduced-motion: reduce) {
            body.ready {
                transition: none;
            }

            .btn-inline-loader {
                animation-duration: 1.25s;
            }

            #scrollProgress.is-route-loading {
                animation: none;
                width: 34% !important;
                transform: none !important;
            }
        }
    </style>
</head>
<body class="landing-page">
    <div class="scroll-progress-bar" aria-hidden="true">
        <span id="scrollProgress"></span>
    </div>
    <div id="routeLoadingStatus" class="route-loading-status" aria-live="polite" aria-atomic="true"></div>

    @include('components.landing-header')

    <!-- HERO SECTION -->
    <section class="hero" id="home">
        <div class="hero-background">
            <div class="hero-blob hero-blob-1"></div>
            <div class="hero-blob hero-blob-2"></div>
        </div>
        <div class="hero-overlay"></div>
        
        <div class="container hero-layout">
            <div class="hero-content-wrapper">
                <div class="hero-text-section" data-aos="fade-up" data-aos-duration="800">
                    <h1 class="hero-title">Smarter <span class="text-gradient">Reimbursement</span> for Modern Teams</h1>
                    <p class="hero-subtitle">AI OCR reads receipts in seconds, blocks duplicates instantly, and routes approvals with full control for every team.</p>
                    <div class="hero-buttons">
                        @auth
                            @if(auth()->user()->isFinance())
                                <a href="{{ route('finance.dashboard') }}" class="btn btn-primary" data-external data-route-loading>Finance Dashboard</a>
                            @elseif(auth()->user()->isAtasan())
                                <a href="{{ route('atasan.dashboard') }}" class="btn btn-primary" data-external data-route-loading>Manager Dashboard</a>
                            @elseif(auth()->user()->isPegawai())
                                <a href="{{ route('pegawai.dashboard') }}" class="btn btn-primary" data-external data-route-loading>Employee Dashboard</a>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary" data-external data-route-loading>Start Smart Now</a>
                        @endauth
                    </div>
                </div>
            </div>

            <div class="hero-image-section">
                <div class="hero-image-wrapper hero-floating" data-aos="zoom-in" data-aos-duration="1000" data-aos-delay="200">
                    <div class="hero-note hero-note-left" data-aos="fade-right" data-aos-delay="260">
                        <span class="hero-note-icon"><x-icon name="cpu" /></span>
                        <div>
                            <p class="hero-note-title">AI OCR Assist</p>
                            <p class="hero-note-desc">Auto-check amount, vendor, date.</p>
                        </div>
                    </div>

                    <div class="hero-note hero-note-right" data-aos="fade-left" data-aos-delay="320">
                        <span class="hero-note-icon"><x-icon name="shield" /></span>
                        <div>
                            <p class="hero-note-title">Duplicate Guard</p>
                            <p class="hero-note-desc">Detect duplicate claims instantly.</p>
                        </div>
                    </div>

                    <div class="hero-note hero-note-bottom" data-aos="fade-up" data-aos-delay="360">
                        <span class="hero-note-icon"><x-icon name="git-branch" /></span>
                        <div>
                            <p class="hero-note-title">Approval + Sync</p>
                            <p class="hero-note-desc">Fast flow to manager and finance.</p>
                        </div>
                    </div>

                    <img src="{{ asset('images/mockup.png') }}" alt="Smart reimbursement dashboard preview" class="dashboard-image" fetchpriority="high" width="1920" height="1536" sizes="(max-width: 1024px) 100vw, 56vw" decoding="async">
                </div>
            </div>
        </div>
    </section>

    <!-- DATA PROOF STRIP -->
    <section class="data-proof-section" id="proof">
        <div class="container">
            <div class="proof-head" data-aos="fade-up" data-aos-duration="700">
                <p class="proof-eyebrow">Operational Highlights</p>
                <h2>Built for Clean Submissions and Controlled Approvals</h2>
                <p class="proof-summary">A clearer operating model for reimbursement: better inputs, safer validation, and cleaner finance handoff.</p>
            </div>

            <div class="proof-grid">
                <article class="proof-item" data-aos="fade-up" data-aos-delay="0">
                    <p class="proof-label">Input Experience</p>
                    <h3 class="proof-value">AI-Assisted</h3>
                    <p class="proof-note">Receipt OCR helps capture core fields before users submit.</p>
                </article>

                <article class="proof-item" data-aos="fade-up" data-aos-delay="80">
                    <p class="proof-label">Validation Layer</p>
                    <h3 class="proof-value">Duplicate Check</h3>
                    <p class="proof-note">Flags overlapping claims before they move deeper in workflow.</p>
                </article>

                <article class="proof-item" data-aos="fade-up" data-aos-delay="160">
                    <p class="proof-label">Approval Routing</p>
                    <h3 class="proof-value">Role-Based</h3>
                    <p class="proof-note">Focused actions for employees, managers, and finance teams.</p>
                </article>

                <article class="proof-item" data-aos="fade-up" data-aos-delay="240">
                    <p class="proof-label">Finance Handoff</p>
                    <h3 class="proof-value">Structured Sync</h3>
                    <p class="proof-note">Approved claims stay organized for reconciliation and reporting.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- SMART TECHNOLOGY BENTO -->
    <section class="bento-section" id="features">
        <div class="container">
            <div class="section-header section-split" data-aos="fade-up">
                <h2>The Smart Core</h2>
                <p>Core automation that accelerates submissions while preserving strict financial integrity.</p>
            </div>
            
            <div class="bento-grid">
                <!-- Main Feature -->
                <div class="bento-item large" data-aos="fade-up">
                    <div class="bento-icon"><x-icon name="cpu" /></div>
                    <h3>AI OCR Validation</h3>
                    <p>AI reads and validates receipt fields instantly, reducing manual entry and improving data consistency.</p>
                    
                    <!-- Smart OCR Preview Simulation -->
                    <div class="ocr-preview-box">
                    <div class="ocr-scanner-line"></div>
                    <div class="ocr-mock-content">
                    <div class="ocr-mock-item mid shimmer"></div>
                    <div class="ocr-mock-item long shimmer"></div>
                    <div class="ocr-mock-item short shimmer"></div>
                    </div>
                    <div class="ocr-badge" id="ocrBadge">
                    <x-icon name="check-circle" style="width: 14px; height: 14px;" />
                    <span id="ocrAmount">AI Verified: $142.50</span>
                    </div>
                    </div>
                </div>

                <!-- Security -->
                <div class="bento-item" data-aos="fade-up" data-aos-delay="100">
                    <div class="bento-icon"><x-icon name="shield" /></div>
                    <h3>Integrity Guard</h3>
                    <p>Smart duplicate and fraud checks keep every submission unique before approval starts.</p>
                </div>

                <!-- Integration -->
                <div class="bento-item" data-aos="fade-up" data-aos-delay="200">
                    <div class="bento-icon"><x-icon name="git-branch" /></div>
                    <h3>Accurate Sync</h3>
                    <p>Sync approved claims to Accurate Online with structured, reconciliation-ready records.</p>
                </div>

                <!-- Results -->
                <div class="bento-item medium" data-aos="fade-up" data-aos-delay="300">
                    <div style="display: flex; align-items: center; gap: 24px;">
                        <div class="bento-icon" style="margin-bottom: 0;"><x-icon name="zap" /></div>
                        <div>
                            <h3>90% Faster Flow</h3>
                            <p>Speed up reimbursement from submission to close with an integrity-first process.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ROLE ECOSYSTEM -->
    <section class="roles-section" id="roles">
        <div class="container">
            <div class="section-header section-split" data-aos="fade-up">
                <h2>Unified Ecosystem</h2>
                <p>Focused experiences for employees, managers, and finance in one connected platform.</p>
            </div>

            <div class="roles-container">
                <div class="role-tabs" data-aos="fade-up">
                    <button class="role-tab active" onclick="switchRole(this, 'pegawai')">Pegawai</button>
                    <button class="role-tab" onclick="switchRole(this, 'atasan')">Atasan</button>
                    <button class="role-tab" onclick="switchRole(this, 'finance')">Finance</button>
                </div>

                <div class="role-content-wrapper">
                    <!-- Pegawai Panel -->
                    <div id="role-pegawai" class="role-panel active">
                        <div class="role-info" data-aos="fade-right">
                            <h3>Smart Data Entry</h3>
                            <p>Employees submit faster with AI-assisted validation, so Finance receives cleaner requests from the start.</p>
                            <ul class="role-features">
                                <li><x-icon name="check-circle" /> AI OCR data verification</li>
                                <li><x-icon name="check-circle" /> Reduced Finance verification time</li>
                                <li><x-icon name="check-circle" /> Real-time status tracking</li>
                            </ul>
                        </div>
                        <div class="role-visual" data-aos="fade-left">
                            <img src="{{ asset('images/pegawai.png') }}" alt="Employee reimbursement dashboard view" class="floating-dashboard" loading="lazy" width="1920" height="1536" sizes="(max-width: 1024px) 100vw, 44vw" decoding="async">
                        </div>
                    </div>

                    <!-- Atasan Panel -->
                    <div id="role-atasan" class="role-panel">
                        <div class="role-info">
                            <h3>Dynamic Workflow</h3>
                            <p>Managers can submit direct claims or review team requests with clear controls and traceable decisions.</p>
                            <ul class="role-features">
                                <li><x-icon name="check-circle" /> Direct-to-Finance submissions</li>
                                <li><x-icon name="check-circle" /> Secure individual validation</li>
                                <li><x-icon name="check-circle" /> Team budget oversight</li>
                            </ul>
                        </div>
                        <div class="role-visual">
                            <img src="{{ asset('images/atasan.png') }}" alt="Manager approval dashboard view" class="floating-dashboard" loading="lazy" width="1920" height="1536" sizes="(max-width: 1024px) 100vw, 44vw" decoding="async">
                        </div>
                    </div>

                    <!-- Finance Panel -->
                    <div id="role-finance" class="role-panel">
                        <div class="role-info">
                            <h3>Accurate Integration</h3>
                            <p>Finance teams close requests with confidence and sync validated records to Accurate Online.</p>
                            <ul class="role-features">
                                <li><x-icon name="check-circle" /> Accurate Online integration</li>
                                <li><x-icon name="check-circle" /> Security-first 1-by-1 validation</li>
                                <li><x-icon name="check-circle" /> Automated financial reports</li>
                            </ul>
                        </div>
                        <div class="role-visual">
                            <img src="{{ asset('images/finance.png') }}" alt="Finance control dashboard view" class="floating-dashboard" loading="lazy" width="1920" height="1536" sizes="(max-width: 1024px) 100vw, 44vw" decoding="async">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- HOW IT WORKS -->
    <section class="process-section" id="how-it-works">
        <div class="container">
            <div class="section-header section-split" data-aos="fade-up">
                <h2>From Receipt to Reimbursement in 3 Steps</h2>
                <p>A simple flow that keeps submissions fast and finance validation consistently accurate.</p>
            </div>

            <div class="process-grid">
                <article class="process-card" data-aos="fade-up" data-aos-delay="0">
                    <div class="process-top">
                        <span class="process-index">01</span>
                        <span class="process-line"></span>
                    </div>
                    <div class="process-icon"><x-icon name="cpu" /></div>
                    <h3>Submit with AI OCR</h3>
                    <p>Upload once, then AI validates core fields so every request starts with cleaner data.</p>
                </article>

                <article class="process-card" data-aos="fade-up" data-aos-delay="120">
                    <div class="process-top">
                        <span class="process-index">02</span>
                        <span class="process-line"></span>
                    </div>
                    <div class="process-icon"><x-icon name="shield" /></div>
                    <h3>Review with Control</h3>
                    <p>Manager and Finance review with duplicate checks and policy guardrails.</p>
                </article>

                <article class="process-card" data-aos="fade-up" data-aos-delay="240">
                    <div class="process-top">
                        <span class="process-index">03</span>
                        <span class="process-line"></span>
                    </div>
                    <div class="process-icon"><x-icon name="git-branch" /></div>
                    <h3>Sync and Close</h3>
                    <p>Approved claims sync to financial records for traceable and aligned reporting.</p>
                </article>
            </div>
        </div>
    </section>

    <!-- FAQ SECTION -->
    <section class="faq-section" id="faq">
        <div class="container">
            <div class="section-header" data-aos="fade-up" data-aos-duration="800">
                <p class="proof-eyebrow">FAQ</p>
                <h2>Smart FAQ</h2>
                <p>Clear answers for how validation, approvals, and routing work across the platform.</p>
            </div>
            <div class="faq-grid">
                <div class="faq-item active" data-aos="fade-up" data-aos-duration="800" data-aos-delay="50">
                    <div class="faq-header">
                        <h3>How does AI OCR work?</h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-content">
                        <p>AI reads receipt images, extracts vendor, date, and amount, then pre-fills data for faster submission.</p>
                    </div>
                </div>

                <div class="faq-item" data-aos="fade-up" data-aos-duration="800" data-aos-delay="100">
                    <div class="faq-header">
                        <h3>What if I submit a duplicate?</h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-content">
                        <p>The system flags it instantly by matching key fields against previous requests.</p>
                    </div>
                </div>

                <div class="faq-item" data-aos="fade-up" data-aos-duration="800" data-aos-delay="150">
                    <div class="faq-header">
                        <h3>Who sees my request first?</h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-content">
                        <p>Your manager reviews first, then approved requests move automatically to Finance.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA SECTION -->
    <section class="cta-section" id="contact">
        <div class="container">
            <h2 data-aos="fade-up" data-aos-duration="800">Work Smarter. Approve Faster.</h2>
            <p data-aos="fade-up" data-aos-duration="800" data-aos-delay="100">Validate with AI, block duplicates, and keep Finance in sync inside one clean operational workflow.</p>

            <div class="cta-highlights" data-aos="fade-up" data-aos-duration="800" data-aos-delay="120">
                <div class="cta-chip">
                    <x-icon name="check-circle" /> Role-based approval controls
                </div>
                <div class="cta-chip">
                    <x-icon name="check-circle" /> Traceable validation flow
                </div>
                <div class="cta-chip">
                    <x-icon name="check-circle" /> Finance-ready structured records
                </div>
            </div>

            <div class="cta-buttons">
                @auth
                    @if(auth()->user()->isFinance())
                        <a href="{{ route('finance.dashboard') }}" class="btn btn-primary btn-lg" data-external data-route-loading data-aos="fade-up" data-aos-duration="800" data-aos-delay="150">Go to Dashboard</a>
                    @elseif(auth()->user()->isAtasan())
                        <a href="{{ route('atasan.dashboard') }}" class="btn btn-primary btn-lg" data-external data-route-loading data-aos="fade-up" data-aos-duration="800" data-aos-delay="150">Go to Dashboard</a>
                    @elseif(auth()->user()->isPegawai())
                        <a href="{{ route('pegawai.dashboard') }}" class="btn btn-primary btn-lg" data-external data-route-loading data-aos="fade-up" data-aos-duration="800" data-aos-delay="150">Go to Dashboard</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg" data-external data-route-loading data-aos="fade-up" data-aos-duration="800" data-aos-delay="150">Get Started Now</a>
                @endauth
                <a href="#features" class="btn btn-secondary btn-lg">Explore Features</a>
            </div>
            <p class="cta-proof">Designed for organizations that need speed without losing control.</p>
        </div>
    </section>

    @include('components.landing-footer')

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Smooth Scroll Optimization -->
    <script>
        // Disable AOS on mobile for smoother scrolling
        AOS.init({
            once: true,
            offset: 100,
            duration: 600,
            easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
            disable: function() {
                // Disable on mobile for ultra-smooth scrolling
                return window.innerWidth < 768;
            }
        });

        // Lightweight OCR amount pulse to simulate AI verification feedback
        (function(){
            const el = document.getElementById('ocrAmount');
            const badge = document.getElementById('ocrBadge');
            if (!el || !badge) return;
            let base = 142.5;
            setInterval(() => {
                const jitter = (Math.random() * 0.8 - 0.4); // +/- 0.40
                const value = (base + jitter).toFixed(2);
                el.textContent = `AI Verified: ${value}`;
                badge.style.transform = 'scale(1.02)';
                badge.style.boxShadow = '0 4px 12px rgba(16,185,129,0.2)';
                setTimeout(() => {
                    badge.style.transform = 'scale(1)';
                    badge.style.boxShadow = 'none';
                }, 220);
            }, 2800);
        })();

        let roleTransitionLock = false;
        function getRoleWrapper() {
            return document.querySelector('.role-content-wrapper');
        }

        function getRoleTargetHeight(panel, wrapper) {
            if (!panel || !wrapper) return 0;
            const styles = window.getComputedStyle(wrapper);
            const padTop = parseFloat(styles.paddingTop) || 0;
            const padBottom = parseFloat(styles.paddingBottom) || 0;
            return panel.offsetHeight + padTop + padBottom;
        }

        function syncRoleWrapperHeight(immediate = false) {
            const wrapper = getRoleWrapper();
            const active = document.querySelector('.role-panel.active');
            if (!wrapper || !active) return;
            const nextHeight = getRoleTargetHeight(active, wrapper);
            if (!nextHeight) return;
            if (immediate) {
                const prevTransition = wrapper.style.transition;
                wrapper.style.transition = 'none';
                wrapper.style.height = `${nextHeight}px`;
                void wrapper.offsetHeight;
                wrapper.style.transition = prevTransition;
            } else {
                wrapper.style.height = `${nextHeight}px`;
            }
        }

        function switchRole(el, roleId) {
            if (roleTransitionLock) return;

            const nextPanel = document.getElementById('role-' + roleId);
            const currentPanel = document.querySelector('.role-panel.active');
            if (!nextPanel || currentPanel === nextPanel) return;

            roleTransitionLock = true;

            // Update tabs
            document.querySelectorAll('.role-tab').forEach((tab) => {
                tab.classList.remove('active');
            });
            if (el) el.classList.add('active');

            // Crossfade + slide transition between panels
            const wrapper = getRoleWrapper();
            if (!currentPanel) {
                nextPanel.classList.add('active');
                if (wrapper) wrapper.style.height = 'auto';
                roleTransitionLock = false;
                return;
            }

            if (wrapper) {
                wrapper.style.height = `${wrapper.offsetHeight}px`;
            }

            currentPanel.classList.add('is-exiting');
            nextPanel.classList.add('active', 'is-entering');

            requestAnimationFrame(() => {
                if (wrapper) {
                    const nextHeight = getRoleTargetHeight(nextPanel, wrapper);
                    if (nextHeight) wrapper.style.height = `${nextHeight}px`;
                }
                nextPanel.classList.remove('is-entering');
            });

            setTimeout(() => {
                currentPanel.classList.remove('active', 'is-exiting');
                if (wrapper) wrapper.style.height = 'auto';
                roleTransitionLock = false;
            }, 360);
        }


        document.addEventListener('DOMContentLoaded', function() {
            const scrollProgress = document.getElementById('scrollProgress');
            const routeLoadingStatus = document.getElementById('routeLoadingStatus');
            const ROUTE_LOADING_TIMEOUT_MS = 10000;
            let routeLoading = false;
            let activeLoadingButton = null;
            let routeLoadingResetTimer = null;
            
            const stopButtonLoading = () => {
                if (!activeLoadingButton) return;
                activeLoadingButton.classList.remove('is-loading');
                activeLoadingButton.removeAttribute('aria-busy');
                activeLoadingButton.removeAttribute('aria-disabled');
                activeLoadingButton.innerHTML = activeLoadingButton.dataset.originalHtml || activeLoadingButton.innerHTML;
                if (Object.prototype.hasOwnProperty.call(activeLoadingButton.dataset, 'loadingMinWidth')) {
                    activeLoadingButton.style.minWidth = activeLoadingButton.dataset.loadingMinWidth || '';
                    delete activeLoadingButton.dataset.loadingMinWidth;
                }
                delete activeLoadingButton.dataset.originalHtml;
                activeLoadingButton = null;
            };

            const startButtonLoading = (buttonEl) => {
                if (!buttonEl || buttonEl.classList.contains('is-loading')) return;
                stopButtonLoading();
                buttonEl.dataset.loadingMinWidth = buttonEl.style.minWidth || '';
                buttonEl.style.minWidth = `${Math.ceil(buttonEl.getBoundingClientRect().width)}px`;
                buttonEl.dataset.originalHtml = buttonEl.innerHTML;
                buttonEl.classList.add('is-loading');
                buttonEl.setAttribute('aria-busy', 'true');
                buttonEl.setAttribute('aria-disabled', 'true');
                const loadingLabel = buttonEl.dataset.loadingLabel || 'Loading...';
                buttonEl.innerHTML = `<span class="btn-inline-loader" aria-hidden="true"></span><span class="btn-loading-text">${loadingLabel}</span>`;
                activeLoadingButton = buttonEl;
            };

            const stopRouteLoading = () => {
                routeLoading = false;
                if (routeLoadingResetTimer) {
                    clearTimeout(routeLoadingResetTimer);
                    routeLoadingResetTimer = null;
                }
                if (scrollProgress) {
                    scrollProgress.classList.remove('is-route-loading');
                    scrollProgress.style.transform = '';
                }
                if (routeLoadingStatus) routeLoadingStatus.textContent = '';
                stopButtonLoading();
            };

            const startRouteLoading = (e, link) => {
                if (e && (e.ctrlKey || e.metaKey || e.button === 1)) return;
                if (routeLoading) return;
                routeLoading = true;
                if (routeLoadingResetTimer) clearTimeout(routeLoadingResetTimer);
                if (scrollProgress) {
                    scrollProgress.classList.add('is-route-loading');
                }

                const button = link?.closest('.btn');
                if (button) startButtonLoading(button);
                if (routeLoadingStatus) routeLoadingStatus.textContent = 'Loading next page';

                // Fallback: reset UI if navigation is blocked/slow and page does not unload
                routeLoadingResetTimer = setTimeout(() => {
                    stopRouteLoading();
                }, ROUTE_LOADING_TIMEOUT_MS);
            };

            // Function to reveal page and clear temporary loading state
            const hideLoader = () => {
                stopRouteLoading();
                document.body.classList.add('ready');
            };

            // Reveal page initially (no full-screen loader)
            setTimeout(hideLoader, 100); 

            // Ensure role wrapper follows active panel height
            syncRoleWrapperHeight(true);
            const roleWrapper = getRoleWrapper();
            if (roleWrapper) roleWrapper.style.height = 'auto';
            window.addEventListener('resize', () => {
                if (!roleTransitionLock) {
                    syncRoleWrapperHeight(true);
                    const currentRoleWrapper = getRoleWrapper();
                    if (currentRoleWrapper) currentRoleWrapper.style.height = 'auto';
                }
            });

            // Scroll progress indicator
            const updateScrollProgress = () => {
                if (!scrollProgress) return;
                if (routeLoading) return;
                const doc = document.documentElement;
                const maxScroll = doc.scrollHeight - window.innerHeight;
                const progress = maxScroll > 0 ? (window.scrollY / maxScroll) * 100 : 0;
                scrollProgress.style.width = `${Math.min(100, Math.max(0, progress))}%`;
            };

            let scrollTicking = false;
            const onScrollProgress = () => {
                if (scrollTicking) return;
                scrollTicking = true;
                requestAnimationFrame(() => {
                    updateScrollProgress();
                    scrollTicking = false;
                });
            };
            window.addEventListener('scroll', onScrollProgress, { passive: true });
            window.addEventListener('resize', updateScrollProgress);
            updateScrollProgress();

            // Step reveal animation for process section
            const processCards = document.querySelectorAll('.process-card');
            if (processCards.length && 'IntersectionObserver' in window) {
                const processObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('in-view');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.28 });
                processCards.forEach((card) => processObserver.observe(card));
            }

            // Subtle desktop tilt interactions
            const supportsHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
            const isMobileViewport = window.matchMedia('(max-width: 768px)').matches;
            if (supportsHover) {
                const tiltCards = document.querySelectorAll('.process-card, .stat-item, .bento-item');
                tiltCards.forEach((card) => {
                    card.classList.add('interactive-tilt');
                    card.addEventListener('mousemove', (e) => {
                        const rect = card.getBoundingClientRect();
                        const x = (e.clientX - rect.left) / rect.width;
                        const y = (e.clientY - rect.top) / rect.height;
                        const rotateY = (x - 0.5) * 2.6;
                        const rotateX = (0.5 - y) * 1.9;
                        card.style.transform = `translateY(-3px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
                    });
                    card.addEventListener('mouseleave', () => {
                        card.style.transform = '';
                    });
                });
            }

            // Framer-like subtle reveal motion (staggered)
            const revealTargets = document.querySelectorAll(
                '.proof-head, .proof-item, .section-header, .bento-item, .roles-container, .process-card, .faq-section h2, .faq-item, .cta-section, .footer-top-band, .footer-content, .footer-bottom'
            );
            const revealStep = isMobileViewport ? 30 : 55;
            revealTargets.forEach((el, index) => {
                el.classList.add('wow-reveal');
                el.style.setProperty('--reveal-delay', `${(index % 6) * revealStep}ms`);
            });

            if ('IntersectionObserver' in window) {
                const revealObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.16, rootMargin: '0px 0px -8% 0px' });

                revealTargets.forEach((el) => revealObserver.observe(el));
            } else {
                revealTargets.forEach((el) => el.classList.add('is-visible'));
            }

            // Gentle magnetic interaction for CTA/header buttons
            if (supportsHover) {
                const magneticButtons = document.querySelectorAll('.hero-buttons .btn-primary, .cta-buttons .btn-primary, .cta-buttons .btn-secondary');
                magneticButtons.forEach((btn) => {
                    btn.addEventListener('mousemove', (e) => {
                        const rect = btn.getBoundingClientRect();
                        const x = (e.clientX - rect.left) / rect.width - 0.5;
                        const y = (e.clientY - rect.top) / rect.height - 0.5;
                        btn.style.transform = `translate(${x * 2}px, ${y * 1.5}px)`;
                    });
                    btn.addEventListener('mouseleave', () => {
                        btn.style.transform = '';
                    });
                });
            }

            // Intercept links
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (link) {
                    if (!link.hasAttribute('data-route-loading')) return;
                    const href = link.getAttribute('href');
                    const target = link.getAttribute('target');
                    
                    if (href && 
                        !href.startsWith('#') && 
                        !href.startsWith('javascript:') && 
                        !href.startsWith('mailto:') && 
                        !href.startsWith('tel:') && 
                        target !== '_blank' &&
                        href !== window.location.href
                    ) {
                        try {
                            const url = new URL(href, window.location.origin);
                            if (url.origin === window.location.origin) {
                                startRouteLoading(e, link);
                            }
                        } catch (err) {}
                    }
                }
            });

            // Handle Back Button
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    hideLoader();
                }
            });
            
            // Fallback
            window.addEventListener('load', hideLoader);
        });
    </script>
</body>
</html>
