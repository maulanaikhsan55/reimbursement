<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#243b61">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32.png') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">
    
    <!-- PREVENT FOUC - MUST BE AT TOP -->
    <style>
        body { 
            opacity: 0; 
            visibility: hidden;
            background: #ffffff;
        }
        body.ready { 
            opacity: 1; 
            visibility: visible;
            transition: opacity 0.2s ease-out;
        }
    </style>

    <title>@yield('title', 'Smart Reimbursement')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/css/auth.css', 'resources/js/app.js', 'resources/js/auth.js'])
    @stack('styles')
    <style>
        .guest-top-progress {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            z-index: 2147483000;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.14s ease;
            background: linear-gradient(90deg, rgba(36, 59, 97, 0.04), rgba(59, 130, 246, 0.08));
        }

        .guest-top-progress__fill {
            position: absolute;
            inset: 0;
            transform-origin: left center;
            transform: scaleX(0);
            background: linear-gradient(90deg, #243b61 0%, #3565a2 45%, #2563eb 78%, #38bdf8 100%);
            box-shadow: 0 0 14px rgba(37, 99, 235, 0.25);
            transition: transform 0.14s linear, opacity 0.12s linear;
        }

        .guest-top-progress__fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: -34%;
            width: 34%;
            height: 100%;
            opacity: 0;
            background: linear-gradient(90deg, rgba(255,255,255,0), rgba(255,255,255,0.7), rgba(255,255,255,0));
            transform: translateX(-140%);
        }

        .guest-top-progress.is-active {
            opacity: 1;
        }

        .guest-top-progress.is-active .guest-top-progress__fill::after {
            opacity: 0.9;
            animation: guest-top-progress-sheen 0.72s linear infinite;
        }

        .guest-top-progress.is-exit {
            opacity: 0;
        }

        .guest-top-progress.is-exit .guest-top-progress__fill {
            opacity: 0;
        }

        @keyframes guest-top-progress-sheen {
            0% { transform: translateX(-140%); }
            100% { transform: translateX(430%); }
        }

        @media (prefers-reduced-motion: reduce) {
            .guest-top-progress__fill::after {
                animation: none !important;
                opacity: 0 !important;
            }
        }
    </style>
</head>
<body>
    <div id="guestTopProgressBar" class="guest-top-progress" aria-hidden="true">
        <div class="guest-top-progress__fill"></div>
    </div>

    <div class="guest-page">
        <div class="guest-content">
            @yield('content')
        </div>
    </div>
    
    @stack('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const topBar = document.getElementById('guestTopProgressBar');
            const topFill = topBar ? topBar.querySelector('.guest-top-progress__fill') : null;
            const markReady = () => {
                document.body.classList.add('ready');
            };

            let hideTimer = null;
            let resetTimer = null;
            let rafId = null;
            let visible = false;
            let progress = 0;
            let visibleSince = 0;
            let lastFrameAt = 0;

            const MIN_VISIBLE_MS = 260;
            const HIDE_ANIM_MS = 140;
            const START_VALUE = 0.22;
            const ACTIVE_FLOOR = 0.34;

            const setProgress = (value) => {
                if (!topFill) return;
                progress = Math.max(0, Math.min(1, value));
                topFill.style.transform = `scaleX(${progress})`;
            };

            const setProgressInstant = (value) => {
                if (!topFill) return;
                const prev = topFill.style.transition;
                topFill.style.transition = 'none';
                setProgress(value);
                void topFill.offsetWidth;
                topFill.style.transition = prev;
            };

            const stopMotion = () => {
                if (rafId) {
                    cancelAnimationFrame(rafId);
                    rafId = null;
                }
                lastFrameAt = 0;
            };

            const clearTimers = () => {
                if (hideTimer) {
                    clearTimeout(hideTimer);
                    hideTimer = null;
                }
                if (resetTimer) {
                    clearTimeout(resetTimer);
                    resetTimer = null;
                }
            };

            const runMotion = () => {
                stopMotion();
                const frame = (ts) => {
                    if (!visible || !topFill) {
                        rafId = null;
                        lastFrameAt = 0;
                        return;
                    }

                    if (!lastFrameAt) {
                        lastFrameAt = ts;
                    }

                    const delta = Math.min(64, Math.max(8, ts - lastFrameAt));
                    lastFrameAt = ts;

                    let cap = 0.965;
                    if (progress < 0.55) cap = 0.9;
                    else if (progress < 0.82) cap = 0.94;

                    if (progress < cap) {
                        let speed = 0.00155;
                        if (progress >= 0.62) speed = 0.00072;
                        if (progress >= 0.86) speed = 0.00026;
                        const step = Math.max((cap - progress) * 0.08, speed * delta);
                        setProgress(Math.min(cap, progress + step));
                    }

                    rafId = requestAnimationFrame(frame);
                };

                rafId = requestAnimationFrame(frame);
            };

            const showTopBar = () => {
                if (!topBar || !topFill) return;
                clearTimers();

                if (!visible) {
                    visible = true;
                    visibleSince = Date.now();
                    topBar.classList.remove('is-exit');
                    topBar.classList.add('is-active');
                    topBar.style.opacity = '1';
                    topFill.style.opacity = '1';
                    setProgressInstant(0);
                    setProgress(START_VALUE);
                    runMotion();
                    return;
                }

                setProgress(Math.max(progress, ACTIVE_FLOOR));
            };

            const hideTopBar = (immediate = false) => {
                if (!topBar || !topFill) return;

                clearTimers();
                visible = false;
                stopMotion();

                if (immediate) {
                    topBar.classList.remove('is-active', 'is-exit');
                    topBar.style.opacity = '0';
                    topFill.style.opacity = '1';
                    setProgressInstant(0);
                    return;
                }

                setProgress(Math.max(progress, 0.94));
                requestAnimationFrame(() => {
                    if (visible) return;
                    setProgress(1);
                });

                const elapsed = Date.now() - visibleSince;
                const wait = Math.max(HIDE_ANIM_MS, MIN_VISIBLE_MS - elapsed);

                hideTimer = setTimeout(() => {
                    if (visible) return;
                    topBar.classList.remove('is-active');
                    topBar.classList.add('is-exit');
                    topFill.style.opacity = '0';
                    topBar.style.opacity = '0';

                    resetTimer = setTimeout(() => {
                        if (visible) return;
                        topBar.classList.remove('is-exit');
                        topFill.style.opacity = '1';
                        setProgressInstant(0);
                    }, 120);
                }, wait);
            };

            const isSkippableHref = (href) => !href
                || href.startsWith('#')
                || href.startsWith('javascript:')
                || href.startsWith('mailto:')
                || href.startsWith('tel:');

            const shouldShowForLink = (link, event = null) => {
                if (!link || link.dataset.noLoader || link.classList.contains('no-loader')) {
                    return false;
                }

                if (event && (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey)) {
                    return false;
                }

                if (event && typeof event.button === 'number' && event.button !== 0) {
                    return false;
                }

                const href = link.getAttribute('href');
                if (isSkippableHref(href)) {
                    return false;
                }

                const target = link.getAttribute('target');
                if (target && target.toLowerCase() === '_blank') {
                    return false;
                }

                if (link.hasAttribute('download')) {
                    return false;
                }

                try {
                    const url = new URL(href, window.location.href);
                    if (url.origin !== window.location.origin) {
                        return false;
                    }
                    const samePathAndQuery = url.pathname === window.location.pathname && url.search === window.location.search;
                    if (samePathAndQuery && url.hash) {
                        return false;
                    }
                    return url.href !== window.location.href;
                } catch (error) {
                    return false;
                }
            };

            window.showProgressBar = showTopBar;
            window.hideProgressBar = hideTopBar;

            document.addEventListener('pointerdown', function(e) {
                const link = e.target.closest('a');
                if (!shouldShowForLink(link, e)) return;
                showTopBar();
            }, { capture: true, passive: true });

            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (!shouldShowForLink(link, e)) return;
                showTopBar();
            }, true);

            document.addEventListener('submit', function(e) {
                const form = e.target;
                if (!(form instanceof HTMLFormElement)) return;
                if (form.dataset.noLoader || form.classList.contains('no-loader')) return;
                if ((form.getAttribute('target') || '').toLowerCase() === '_blank') return;
                showTopBar();
            }, true);

            window.addEventListener('beforeunload', function() {
                showTopBar();
            });

            requestAnimationFrame(function() {
                markReady();
                hideTopBar(true);
            });

            window.addEventListener('pageshow', function(event) {
                if (event.persisted) {
                    hideTopBar(true);
                    markReady();
                }
            });
            window.addEventListener('load', function() {
                markReady();
                hideTopBar(true);
            }, { once: true });
        });
    </script>
</body>
</html>
