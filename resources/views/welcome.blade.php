<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reimbursement Platform - Smart Expense Management</title>
    
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

        .clip-loader {
            background: transparent !important;
            width: 32px;
            height: 32px;
            border-radius: 100%;
            border: 3px solid;
            border-color: #425d87;
            border-bottom-color: transparent;
            display: inline-block;
            animation: clip-loader-spin 0.75s 0s infinite linear;
            animation-fill-mode: both;
        }

        /* --- MODERN UI ENHANCEMENTS --- */
        
        /* 1. Smooth Scroll */
        html {
            scroll-behavior: smooth;
        }

        /* 2. Solid Brand Text */
        .text-gradient {
            color: #425d87; /* Solid primary color */
            background: none;
            -webkit-text-fill-color: initial;
        }

        @keyframes float-hero {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); } 
            100% { transform: translateY(0px); }
        }
        .hero-floating {
            animation: float-hero 6s ease-in-out infinite;
            will-change: transform; /* Hardware acceleration */
            filter: drop-shadow(0 20px 30px rgba(0,0,0,0.1)); 
        }

        /* 4. Glassmorphism Cards (LIGHTWEIGHT) */
        .feature-card, .benefit-card, .problem-card, .user-card {
            background: rgba(255, 255, 255, 0.9) !important; 
            backdrop-filter: blur(8px) !important; 
            -webkit-backdrop-filter: blur(8px) !important;
            border: 1px solid rgba(255, 255, 255, 0.6) !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important; /* Simpler shadow */
            transition: transform 0.3s ease, box-shadow 0.3s ease !important; /* Simplified transition */
            border-radius: 1.5rem !important;
        }

        /* Hover Effect: Optimized */
        .feature-card:hover, .benefit-card:hover, .problem-card:hover, .detail-step:hover, .user-card:hover {
            transform: translateY(-5px) !important; /* Reduced movement */
            box-shadow: 0 15px 30px -5px rgba(66, 93, 135, 0.1) !important; /* Lighter shadow */
            border-color: rgba(66, 93, 135, 0.2) !important;
            background: #ffffff !important; 
            z-index: 10;
        }

        /* 5. Modern Button Consistency (Matching App) */
        .btn-primary {
            background: linear-gradient(135deg, #425d87 0%, #3c5379 100%) !important;
            color: white !important;
            padding: 12px 28px !important;
            border-radius: 20px !important;
            font-weight: 600 !important;
            box-shadow: 0 8px 24px rgba(66, 93, 135, 0.2) !important;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: none !important;
            text-decoration: none !important;
        }
        
        .btn-primary:hover {
            transform: translate3d(0, -3px, 0) !important;
            box-shadow: 0 16px 40px rgba(66, 93, 135, 0.3) !important;
            color: white !important;
        }

        .btn-sm {
            padding: 8px 18px !important;
            font-size: 12px !important;
            border-radius: 18px !important;
        }

        /* 6. Hero Background */
        .hero {
            background: #ffffff;
        }

        /* 7. FAQ Accordion Style */
        .faq-item {
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 1rem;
            background: #fff;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .faq-item:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-color: rgba(66, 93, 135, 0.2);
        }

        .btn-secondary {
            background: #ffffff !important;
            color: #425d87 !important;
            padding: 12px 28px !important;
            border-radius: 20px !important;
            font-weight: 600 !important;
            border: 1px solid #d0d9e7 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-decoration: none !important;
        }

        .btn-secondary:hover {
            background: #f8fafc !important;
            border-color: #425d87 !important;
            transform: translate3d(0, -2px, 0) !important;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
        }
    </style>
</head>
<body>
    <!-- Global Loader -->
    <div id="global-loader" style="position: fixed; inset: 0; z-index: 9999; background-color: rgba(255, 255, 255, 0.9); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; transition: opacity 0.3s ease-out;">
        <div style="display: flex; flex-direction: column; align-items: center; gap: 0.5rem;">
            <div class="clip-loader"></div>
            <span class="text-xs text-gray-500 font-medium tracking-wide" style="font-family: 'Poppins', sans-serif;">loading...</span>
        </div>
    </div>

    @include('components.landing-header')

    <!-- HERO SECTION -->
    <section class="hero" id="home">
        <div class="hero-background">
            <div class="hero-blob hero-blob-1"></div>
            <div class="hero-blob hero-blob-2"></div>
        </div>
        <div class="hero-overlay"></div>
        
        <div class="hero-content-wrapper">
            <div class="hero-text-section" data-aos="fade-up" data-aos-duration="800">
                <h1 class="hero-title">Smarter <span class="text-gradient">Reimbursement</span> for Modern Teams</h1>
                <p class="hero-subtitle">Ultra-fast expense management powered by AI OCR. Submit receipts in seconds, eliminate duplicates, and get approved faster than ever.</p>
                <div class="hero-buttons">
                    @auth
                        @if(auth()->user()->isFinance())
                            <a href="{{ route('finance.dashboard') }}" class="btn btn-primary" data-external>Finance Dashboard</a>
                        @elseif(auth()->user()->isAtasan())
                            <a href="{{ route('atasan.dashboard') }}" class="btn btn-primary" data-external>Manager Dashboard</a>
                        @elseif(auth()->user()->isPegawai())
                            <a href="{{ route('pegawai.dashboard') }}" class="btn btn-primary" data-external>Employee Dashboard</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary" data-external>Start Smart Now</a>
                    @endauth
                </div>
            </div>
        </div>

        <div class="hero-image-section">
            <div class="hero-image-wrapper hero-floating" data-aos="zoom-in" data-aos-duration="1000" data-aos-delay="200" style="max-width: 1000px; margin: 0 auto;">
                <img src="{{ asset('images/mockup.png') }}" alt="Smart Dashboard Mockup" class="dashboard-image" fetchpriority="high" decoding="async">
            </div>
        </div>
    </section>

    <!-- STATISTICS SECTION -->
    <section class="statistics-section" id="statistics">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item" data-aos="fade-up" data-aos-delay="0">
                    <div class="stat-icon">
                        <x-icon name="check-circle" />
                    </div>
                    <div class="stat-counter" data-target="99.9">0<span class="counter-suffix">%</span></div>
                    <div class="stat-label">Accuracy Rate</div>
                </div>
                <div class="stat-item" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-icon">
                        <x-icon name="zap" />
                    </div>
                    <div class="stat-counter" data-target="90">0<span class="counter-suffix">%</span></div>
                    <div class="stat-label">Faster Processing</div>
                </div>
                <div class="stat-item" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-icon">
                        <x-icon name="users" />
                    </div>
                    <div class="stat-counter" data-target="5000">0<span class="counter-suffix">+</span></div>
                    <div class="stat-label">Transactions Processed</div>
                </div>
                <div class="stat-item" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-icon">
                        <x-icon name="shield" />
                    </div>
                    <div class="stat-counter" data-target="100">0<span class="counter-suffix">%</span></div>
                    <div class="stat-label">Duplicate Prevention</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ABOUT SECTION -->
    <section class="about" id="about">
        <div class="container">
            <div data-aos="fade-up">
                <span class="platform-badge">The Platform</span>
                <h2>Ultra-Smart Workflow</h2>
                <p class="about-text">Stop wasting time on manual data entry. Our platform uses advanced AI OCR to extract receipt data with 99.9% accuracy, detects duplicates automatically, and routes approvals through your company's hierarchy in real-time.</p>
            </div>
        </div>
    </section>

    <!-- SMART TECHNOLOGY BENTO -->
    <section class="bento-section" id="features">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>The Smart Core</h2>
                <p>Advanced technology that streamlines your workflow and secures your financial integrity.</p>
            </div>
            
            <div class="bento-grid">
                <!-- Main Feature -->
                <div class="bento-item large" data-aos="fade-up">
                    <div class="bento-icon"><x-icon name="cpu" /></div>
                    <h3>AI OCR Validation</h3>
                    <p>Our AI assists your data entry by validating receipts instantly. This ensures high data integrity and saves the Finance team hours of manual verification.</p>
                    
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
                    <p>High-security validation process that ensures every receipt is unique and legitimate before approval.</p>
                </div>

                <!-- Integration -->
                <div class="bento-item" data-aos="fade-up" data-aos-delay="200">
                    <div class="bento-icon"><x-icon name="git-branch" /></div>
                    <h3>Accurate Sync</h3>
                    <p>Seamlessly integrated with Accurate Online to ensure all reports are recorded with precision.</p>
                </div>

                <!-- Results -->
                <div class="bento-item medium" data-aos="fade-up" data-aos-delay="300">
                    <div style="display: flex; align-items: center; gap: 24px;">
                        <div class="bento-icon" style="margin-bottom: 0;"><x-icon name="zap" /></div>
                        <div>
                            <h3>90% Faster Flow</h3>
                            <p>Accelerate your reimbursement from submission to payment with integrity-first processing.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ROLE ECOSYSTEM -->
    <section class="roles-section" id="roles">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h2>Unified Ecosystem</h2>
                <p>Tailored experiences designed for every role in your financial journey.</p>
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
                            <p>Employees submit receipts with AI-assisted validation, making it significantly faster for Finance to verify and process.</p>
                            <ul class="role-features">
                                <li><x-icon name="check-circle" /> AI OCR data verification</li>
                                <li><x-icon name="check-circle" /> Reduced Finance verification time</li>
                                <li><x-icon name="check-circle" /> Real-time status tracking</li>
                            </ul>
                        </div>
                        <div class="role-visual" data-aos="fade-left">
                            <img src="{{ asset('images/pegawai.png') }}" alt="Pegawai View" class="floating-dashboard" loading="lazy" decoding="async">
                        </div>
                    </div>

                    <!-- Atasan Panel -->
                    <div id="role-atasan" class="role-panel">
                        <div class="role-info">
                            <h3>Dynamic Workflow</h3>
                            <p>Managers can submit their own expenses directly to Finance or review team requests with secure, one-by-one validation.</p>
                            <ul class="role-features">
                                <li><x-icon name="check-circle" /> Direct-to-Finance submissions</li>
                                <li><x-icon name="check-circle" /> Secure individual validation</li>
                                <li><x-icon name="check-circle" /> Team budget oversight</li>
                            </ul>
                        </div>
                        <div class="role-visual">
                            <img src="{{ asset('images/atasan.png') }}" alt="Atasan View" class="floating-dashboard" loading="lazy" decoding="async">
                        </div>
                    </div>

                    <!-- Finance Panel -->
                    <div id="role-finance" class="role-panel">
                        <div class="role-info">
                            <h3>Accurate Integration</h3>
                            <p>Finance teams sync seamlessly with Accurate Online for smart reporting and one-by-one integrity checks.</p>
                            <ul class="role-features">
                                <li><x-icon name="check-circle" /> Accurate Online integration</li>
                                <li><x-icon name="check-circle" /> Security-first 1-by-1 validation</li>
                                <li><x-icon name="check-circle" /> Automated financial reports</li>
                            </ul>
                        </div>
                        <div class="role-visual">
                            <img src="{{ asset('images/finance.png') }}" alt="Finance View" class="floating-dashboard" loading="lazy" decoding="async">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ SECTION -->
    <section class="faq-section" id="faq">
        <div class="container">
            <h2 data-aos="fade-up" data-aos-duration="800">Smart FAQ</h2>
            <div class="faq-grid">
                <div class="faq-item" data-aos="fade-up" data-aos-duration="800" data-aos-delay="50">
                    <div class="faq-header">
                        <h3>How does AI OCR work?</h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-content">
                        <p>Our ultra-smart AI reads your receipt image, identifies the vendor, date, and amount, then fills the form for you automatically. Smart and accurate.</p>
                    </div>
                </div>

                <div class="faq-item" data-aos="fade-up" data-aos-duration="800" data-aos-delay="100">
                    <div class="faq-header">
                        <h3>What if I submit a duplicate?</h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-content">
                        <p>The system will catch it instantly! It compares names, dates, and amounts against your history to ensure everything is unique and valid.</p>
                    </div>
                </div>

                <div class="faq-item" data-aos="fade-up" data-aos-duration="800" data-aos-delay="150">
                    <div class="faq-header">
                        <h3>Who sees my request first?</h3>
                        <span class="faq-toggle">+</span>
                    </div>
                    <div class="faq-content">
                        <p>Your department's Atasan will review it first. Once approved, it moves automatically to Finance for final processing and payment.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA SECTION -->
    <section class="cta-section" id="contact">
        <div class="container">
            <h2 data-aos="fade-up" data-aos-duration="800">Work Smarter. Approve Faster.</h2>
            <p data-aos="fade-up" data-aos-duration="800" data-aos-delay="100">Auto-validate receipts with AI, prevent duplicates, and keep finance in sync — all in one clean, modern workflow.</p>

            <div class="cta-highlights" data-aos="fade-up" data-aos-duration="800" data-aos-delay="120">
                <div class="cta-chip">
                    <x-icon name="check-circle" /> AI OCR & duplicate check
                </div>
                <div class="cta-chip">
                    <x-icon name="check-circle" /> Real-time approval
                </div>
                <div class="cta-chip">
                    <x-icon name="check-circle" /> Accurate sync
                </div>
            </div>

            <div class="cta-buttons">
                @auth
                    @if(auth()->user()->isFinance())
                        <a href="{{ route('finance.dashboard') }}" class="btn btn-primary btn-lg" data-external data-aos="fade-up" data-aos-duration="800" data-aos-delay="150">Go to Dashboard</a>
                    @elseif(auth()->user()->isAtasan())
                        <a href="{{ route('atasan.dashboard') }}" class="btn btn-primary btn-lg" data-external data-aos="fade-up" data-aos-duration="800" data-aos-delay="150">Go to Dashboard</a>
                    @elseif(auth()->user()->isPegawai())
                        <a href="{{ route('pegawai.dashboard') }}" class="btn btn-primary btn-lg" data-external data-aos="fade-up" data-aos-duration="800" data-aos-delay="150">Go to Dashboard</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg" data-external data-aos="fade-up" data-aos-duration="800" data-aos-delay="150">Get Started Now</a>
                @endauth
            </div>
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

        // Counter Animation Function
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-counter[data-target]');
            
            counters.forEach(counter => {
                const target = parseFloat(counter.dataset.target);
                const duration = 2000; // 2 seconds animation
                const startTime = performance.now();
                const isDecimal = target % 1 !== 0;
                
                function updateCounter(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    
                    // Easing function for smooth animation (ease-out)
                    const easeOut = 1 - Math.pow(1 - progress, 3);
                    const currentValue = target * easeOut;
                    
                    if (isDecimal) {
                        counter.firstChild.textContent = currentValue.toFixed(1);
                    } else {
                        counter.firstChild.textContent = Math.floor(currentValue).toLocaleString();
                    }
                    
                    if (progress < 1) {
                        requestAnimationFrame(updateCounter);
                    } else {
                        // Ensure final value is exact
                        if (isDecimal) {
                            counter.firstChild.textContent = target.toFixed(1);
                        } else {
                            counter.firstChild.textContent = Math.floor(target).toLocaleString();
                        }
                    }
                }
                
                requestAnimationFrame(updateCounter);
            });
        }

        // Intersection Observer for counter animation
        const statsObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    statsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        const statsSection = document.querySelector('.statistics-section');
        if (statsSection) {
            statsObserver.observe(statsSection);
        }

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

        function switchRole(el, roleId) {
            // Update tabs
            document.querySelectorAll('.role-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            if (el) el.classList.add('active');

            // Update panels
            document.querySelectorAll('.role-panel').forEach(panel => {
                panel.classList.remove('active');
            });
            document.getElementById('role-' + roleId).classList.add('active');
        }


        document.addEventListener('DOMContentLoaded', function() {
            const loader = document.getElementById('global-loader');
            
            // Function to hide loader
            const hideLoader = () => {
                loader.style.opacity = '0';
                loader.style.pointerEvents = 'none';
                document.body.classList.add('ready');
                setTimeout(() => {
                    loader.style.display = 'none';
                }, 300);
            };

            // Function to show loader immediately
            const showLoader = (e) => {
                if (e && (e.ctrlKey || e.metaKey || e.button === 1)) return;
                
                loader.style.display = 'flex';
                requestAnimationFrame(() => {
                    loader.style.opacity = '1';
                    loader.style.pointerEvents = 'auto';
                });
            };

            // Hide initially
            setTimeout(hideLoader, 100); 

            // Intercept links
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (link) {
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
                                showLoader(e);
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
