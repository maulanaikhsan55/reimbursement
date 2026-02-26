/**
 * DASHBOARD ULTRA - MODERN PROGRESS BAR
 * Like LinkedIn, Twitter, GitHub - smooth & fast
 */
import '../css/dashboard-ultra.css';

let globalLoader;
let progressBar, progressFill;
let loaderFailSafeTimer = null;
let hideProgressTimer = null;
let progressResetTimer = null;
let isProgressVisible = false;
let progressRafId = null;
let progressValue = 0;
let progressVisibleSince = 0;
let progressLastFrameAt = 0;
let progressCycleId = 0;

const PROGRESS_MIN_VISIBLE_MS = 420;
const PROGRESS_HIDE_ANIMATION_MS = 180;
const PROGRESS_RESET_AFTER_HIDE_MS = 120;
const PROGRESS_START_VALUE = 0.24;
const PROGRESS_ACTIVE_FLOOR = 0.34;
const PROGRESS_BAR_ID = 'modernProgressBar';
const PROGRESS_BAR_STYLE_ID = 'modernProgressBarStyles';

function injectProgressBarStyles() {
    if (document.getElementById(PROGRESS_BAR_STYLE_ID)) {
        return;
    }

    const style = document.createElement('style');
    style.id = PROGRESS_BAR_STYLE_ID;
    style.textContent = `
        .progress-bar-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            z-index: 2147483000;
            pointer-events: none;
            opacity: 0;
            transform: translateZ(0);
            transition: opacity 0.16s ease;
            contain: layout paint style;
        }

        .progress-bar-container::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(36, 59, 97, 0.05), rgba(59, 130, 246, 0.09));
            opacity: 0.9;
        }

        .progress-bar-fill {
            position: absolute;
            inset: 0;
            width: 100%;
            transform-origin: left center;
            transform: scaleX(0);
            opacity: 1;
            background: linear-gradient(90deg, #203a5f 0%, #2f5f9a 38%, #2563eb 70%, #38bdf8 100%);
            box-shadow: 0 0 18px rgba(37, 99, 235, 0.32), 0 0 32px rgba(56, 189, 248, 0.18);
            transition: transform 0.16s cubic-bezier(0.2, 0.88, 0.24, 1), opacity 0.12s linear;
            will-change: transform, opacity;
        }

        .progress-bar-fill::after {
            content: '';
            position: absolute;
            top: 0;
            left: -38%;
            width: 38%;
            height: 100%;
            opacity: 0;
            background: linear-gradient(90deg, rgba(255, 255, 255, 0), rgba(255, 255, 255, 0.72), rgba(255, 255, 255, 0));
            transform: translateX(-140%);
            will-change: transform, opacity;
        }

        .progress-bar-active {
            opacity: 1 !important;
        }

        .progress-bar-active .progress-bar-fill::after {
            opacity: 0.85;
            animation: progress-bar-sheen 0.72s linear infinite;
        }

        .progress-bar-exit {
            opacity: 0 !important;
        }

        .progress-bar-exit .progress-bar-fill {
            opacity: 0 !important;
        }

        @keyframes progress-bar-sheen {
            0% { transform: translateX(-140%); }
            100% { transform: translateX(430%); }
        }

        @media (prefers-reduced-motion: reduce) {
            .progress-bar-fill {
                transition: transform 0.12s linear, opacity 0.12s linear;
            }

            .progress-bar-fill::after {
                animation: none !important;
                opacity: 0 !important;
            }
        }
    `;

    document.head.appendChild(style);
}

function createProgressBarElement() {
    if (!document.body) {
        return null;
    }

    const bar = document.createElement('div');
    bar.id = PROGRESS_BAR_ID;
    bar.className = 'progress-bar-container';
    bar.setAttribute('aria-hidden', 'true');

    const fill = document.createElement('div');
    fill.className = 'progress-bar-fill';
    bar.appendChild(fill);

    document.body.prepend(bar);
    return bar;
}

function ensureProgressElements(createIfMissing = false) {
    if (createIfMissing) {
        injectProgressBarStyles();
    }

    // Livewire navigate can morph DOM and replace the topbar node.
    // Rebind when previous references are detached.
    if (!progressBar || !progressBar.isConnected) {
        progressBar = document.getElementById(PROGRESS_BAR_ID);
    }

    if (!progressBar && createIfMissing) {
        progressBar = createProgressBarElement();
    }

    const progressFillInvalid = !progressFill
        || !progressFill.isConnected
        || (progressBar && !progressBar.contains(progressFill));

    if (progressFillInvalid && progressBar) {
        progressFill = progressBar.querySelector('.progress-bar-fill');
    }

    return Boolean(progressBar && progressFill);
}

// 1. Global dismiss alert function
window.dismissAlert = function(element) {
    if (!element) return;
    element.style.opacity = '0';
    element.style.transform = 'translateX(50px)';
    setTimeout(() => element.remove(), 500);
};

// 2. Global Modern Notification Helper
// Optional parameters:
// - 4th: duration in ms (default uses type-based auto-close)
// - 5th: options object, e.g. { url: '/target-page', onClick: () => {} }
window.showNotification = function(type, title, message, duration = 0, options = {}) {
    // Hide progress bar when showing notification
    hideProgressBar();
    
    const gLoader = document.getElementById('global-loader');
    if (gLoader) {
        gLoader.style.opacity = '0';
        gLoader.style.pointerEvents = 'none';
        gLoader.style.zIndex = '-1';
    }

    if (arguments.length === 2) {
        message = title;
        title = type.charAt(0).toUpperCase() + type.slice(1);
    }

    const normalizedType = (type || '').toString().toLowerCase();
    const defaultDurationByType = (
        normalizedType === 'error' || normalizedType === 'danger' ? 6500
        : normalizedType === 'warning' ? 5500
        : 4500
    );
    const resolvedDuration = Number(duration) > 0 ? Number(duration) : defaultDurationByType;
    const resolvedOptions = options && typeof options === 'object' ? options : {};
    const clickUrl = typeof resolvedOptions.url === 'string' && resolvedOptions.url.length > 0
        ? resolvedOptions.url
        : null;
    const clickHandler = typeof resolvedOptions.onClick === 'function'
        ? resolvedOptions.onClick
        : null;
    const hasClickableAction = Boolean(clickUrl || clickHandler);

    const triggerToast = () => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: resolvedDuration,
            timerProgressBar: true,
            showCloseButton: true,
            customClass: {
                popup: 'swal2-modern-toast',
            },
            didOpen: () => {
                const container = document.querySelector('.swal2-container');
                if (container) {
                    container.style.zIndex = '999999';
                }

                const popup = Swal.getPopup();
                if (popup && hasClickableAction) {
                    popup.style.cursor = 'pointer';
                    popup.title = 'Klik untuk buka detail';
                    popup.addEventListener('click', (evt) => {
                        if (evt.target && evt.target.closest('.swal2-close')) {
                            return;
                        }

                        if (clickHandler) {
                            clickHandler();
                            return;
                        }

                        if (clickUrl) {
                            window.location.href = clickUrl;
                        }
                    });
                }
            }
        });

        Toast.fire({
            icon: type === 'danger' || type === 'error' ? 'error' : (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'info')),
            title: title,
            html: `<div style="font-family: 'Poppins', sans-serif; font-size: 0.85rem; color: #64748b;">${message}${hasClickableAction ? '<div style="margin-top:4px;font-size:0.72rem;color:#3b82f6;font-weight:600;">Klik untuk buka detail</div>' : ''}</div>`,
        });
    };

    if (typeof Swal === 'undefined') {
        const checkSwal = setInterval(() => {
            if (typeof Swal !== 'undefined') {
                clearInterval(checkSwal);
                triggerToast();
            }
        }, 100);
        setTimeout(() => clearInterval(checkSwal), Math.max(Number(duration) || 5000, 5000));
    } else {
        triggerToast();
    }
};

// 3. ULTRA MODERN Progress Bar Functions - SEAMLESS FLOW Ujung ke Ujung
function setProgress(value) {
    if (!progressFill) {
        return;
    }

    progressValue = Math.max(0, Math.min(1, value));
    progressFill.style.transform = `scaleX(${progressValue})`;
}

function setProgressInstant(value) {
    if (!progressFill) {
        return;
    }

    const previousInlineTransition = progressFill.style.transition;
    progressFill.style.transition = 'none';
    setProgress(value);
    // Force style flush so the next update can animate normally.
    void progressFill.offsetWidth;
    progressFill.style.transition = previousInlineTransition;
}

function stopProgressMotion() {
    if (progressRafId) {
        cancelAnimationFrame(progressRafId);
        progressRafId = null;
    }
    progressLastFrameAt = 0;
}

function scheduleProgressMotion() {
    stopProgressMotion();

    const frame = (timestamp) => {
        if (!isProgressVisible || !progressFill) {
            progressRafId = null;
            progressLastFrameAt = 0;
            return;
        }

        if (!progressLastFrameAt) {
            progressLastFrameAt = timestamp;
        }

        const deltaMs = Math.min(64, Math.max(8, timestamp - progressLastFrameAt));
        progressLastFrameAt = timestamp;

        let cap = 0.965;
        if (progressValue < 0.55) cap = 0.9;
        else if (progressValue < 0.82) cap = 0.94;

        if (progressValue < cap) {
            const distance = cap - progressValue;
            let basePerMs = 0.0016;
            if (progressValue >= 0.62) basePerMs = 0.00075;
            if (progressValue >= 0.86) basePerMs = 0.00028;

            const step = Math.max(distance * 0.075, basePerMs * deltaMs);
            setProgress(Math.min(cap, progressValue + step));
        }

        progressRafId = requestAnimationFrame(frame);
    };

    progressRafId = requestAnimationFrame(frame);
}

function clearProgressTimers() {
    if (hideProgressTimer) {
        clearTimeout(hideProgressTimer);
        hideProgressTimer = null;
    }

    if (progressResetTimer) {
        clearTimeout(progressResetTimer);
        progressResetTimer = null;
    }
}

function resolveInternalUrl(href) {
    try {
        const url = new URL(href, window.location.href);
        if (url.origin !== window.location.origin) {
            return null;
        }
        return url;
    } catch (error) {
        return null;
    }
}

function isSkippableHref(href) {
    return !href ||
        href.startsWith('#') ||
        href.startsWith('javascript:') ||
        href.startsWith('mailto:') ||
        href.startsWith('tel:');
}

function shouldShowProgressForLink(link, event = null) {
    if (!link || link.classList.contains('no-loader') || link.dataset.noLoader) {
        return false;
    }

    const isLivewireNavigateLink = link.hasAttribute('wire:navigate')
        || link.hasAttribute('wire:navigate.hover')
        || link.hasAttribute('wire:navigate.prefetch')
        || link.hasAttribute('data-navigate')
        || link.hasAttribute('data-navigate-hover')
        || link.hasAttribute('data-navigate-prefetch');

    // Livewire navigate links usually call preventDefault() internally,
    // but we still want the top progress bar to appear for route changes.
    if (event?.defaultPrevented && !isLivewireNavigateLink) {
        return false;
    }

    if (event && (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey)) {
        return false;
    }

    if (event && typeof event.button === 'number' && event.button !== 0) {
        return false;
    }

    const target = link.getAttribute('target');
    if (target && target.toLowerCase() === '_blank') {
        return false;
    }

    if (link.hasAttribute('download')) {
        return false;
    }

    const href = link.getAttribute('href');
    if (isSkippableHref(href)) {
        return false;
    }

    const url = resolveInternalUrl(href);
    if (!url) {
        return false;
    }

    const samePathAndQuery = url.pathname === window.location.pathname && url.search === window.location.search;
    if (samePathAndQuery && url.hash) {
        return false;
    }

    return url.href !== window.location.href;
}

function showProgressBar() {
    if (!ensureProgressElements(true)) return;

    clearProgressTimers();

    // DOM may have been replaced by Livewire; recover stale visible state.
    if (isProgressVisible) {
        const barLooksActive = progressBar.classList.contains('progress-bar-active')
            && progressBar.style.opacity !== '0';
        if (!barLooksActive) {
            isProgressVisible = false;
            stopProgressMotion();
        }
    }

    if (!isProgressVisible) {
        progressCycleId += 1;
        isProgressVisible = true;
        progressVisibleSince = Date.now();
        progressBar.classList.remove('progress-bar-exit');
        progressBar.classList.add('progress-bar-active');
        progressBar.style.opacity = '1';
        progressFill.style.opacity = '1';

        // Always reset instantly for a clean new cycle and avoid reverse motion.
        setProgressInstant(0);
        setProgress(PROGRESS_START_VALUE);
        scheduleProgressMotion();
        return;
    }

    setProgress(Math.max(progressValue, PROGRESS_ACTIVE_FLOOR));
}

function hideProgressBar() {
    if (!ensureProgressElements(false)) {
        isProgressVisible = false;
        stopProgressMotion();
        clearProgressTimers();
        progressBar = null;
        progressFill = null;
        return;
    }

    if (!isProgressVisible && !progressBar.classList.contains('progress-bar-active')) {
        return;
    }

    const cycleIdAtHideRequest = progressCycleId;
    isProgressVisible = false;
    stopProgressMotion();

    setProgress(Math.max(progressValue, 0.94));
    requestAnimationFrame(() => {
        if (!progressFill || isProgressVisible || cycleIdAtHideRequest !== progressCycleId) {
            return;
        }
        setProgress(1);
    });

    if (hideProgressTimer) {
        clearTimeout(hideProgressTimer);
    }

    const elapsed = Date.now() - progressVisibleSince;
    const waitBeforeHide = Math.max(PROGRESS_HIDE_ANIMATION_MS, PROGRESS_MIN_VISIBLE_MS - elapsed);

    hideProgressTimer = setTimeout(() => {
        if (isProgressVisible || cycleIdAtHideRequest !== progressCycleId) {
            return;
        }
        progressBar.classList.remove('progress-bar-active');
        progressBar.classList.add('progress-bar-exit');
        progressFill.style.opacity = '0';
        progressBar.style.opacity = '0';
        hideProgressTimer = null;

        progressResetTimer = setTimeout(() => {
            if (isProgressVisible || cycleIdAtHideRequest !== progressCycleId) {
                return;
            }
            if (!ensureProgressElements(false)) {
                progressResetTimer = null;
                return;
            }
            setProgressInstant(0);
            progressFill.style.opacity = '1';
            progressResetTimer = null;
        }, PROGRESS_RESET_AFTER_HIDE_MS);
    }, waitBeforeHide);
}

// Expose to global scope
window.showProgressBar = showProgressBar;
window.hideProgressBar = hideProgressBar;

// 4. Loader Functions (for form submissions)
window.hideAllLoaders = () => {
    sessionStorage.removeItem('pending_flash');
    hideProgressBar();
    if (loaderFailSafeTimer) {
        clearTimeout(loaderFailSafeTimer);
        loaderFailSafeTimer = null;
    }
};

window.showLoaders = (type = 'nav', e = null) => {
    if (e && (e.ctrlKey || e.metaKey || e.button === 1)) return;
    if (sessionStorage.getItem('pending_flash') && type !== 'heavy') return;

    if (loaderFailSafeTimer) {
        clearTimeout(loaderFailSafeTimer);
        loaderFailSafeTimer = null;
    }

    if (type === 'heavy') {
        // Full-screen overlay removed: use top progress only + fail-safe cleanup.
        showProgressBar();

        // Fail-safe: never leave top progress stuck if some request path fails silently
        loaderFailSafeTimer = setTimeout(() => {
            window.hideAllLoaders();
        }, 12000);
    } else {
        // For navigation, use progress bar
        showProgressBar();
    }
};

const normalizeExportFields = (fields) => {
    if (!fields) {
        return {};
    }

    if (typeof fields === 'object' && !Array.isArray(fields)) {
        return fields;
    }

    if (typeof fields !== 'string') {
        return {};
    }

    return fields.split(',')
        .map((item) => item.trim())
        .filter(Boolean)
        .reduce((acc, item) => {
            const [queryKey, elementId] = item.split(':').map((segment) => segment.trim());
            if (!queryKey || !elementId) {
                return acc;
            }

            acc[queryKey] = elementId;
            return acc;
        }, {});
};

const collectExportParams = (fieldMap) => {
    const params = new URLSearchParams();
    Object.entries(fieldMap).forEach(([queryKey, elementId]) => {
        const element = document.getElementById(elementId);
        if (!element) return;

        const rawValue = 'value' in element ? element.value : '';
        const value = String(rawValue ?? '').trim();
        if (value !== '') {
            params.append(queryKey, value);
        }
    });
    return params;
};

const appendParamsToUrl = (baseUrl, params) => {
    if (!baseUrl) {
        return '';
    }

    const query = params.toString();
    if (!query) {
        return baseUrl;
    }

    return `${baseUrl}${baseUrl.includes('?') ? '&' : '?'}${query}`;
};

const submitExportAsPost = (url, params) => {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.style.display = 'none';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (csrfToken) {
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrfToken;
        form.appendChild(csrfInput);
    }

    params.forEach((value, key) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = value;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
};

window.exportWithFilters = (event, options = {}) => {
    if (event) {
        event.preventDefault();
    }

    const trigger = event?.currentTarget || event?.target?.closest?.('a,button');
    const baseUrl = options.url || trigger?.dataset?.url || '';
    if (!baseUrl) {
        return;
    }

    const inheritedConfigNode = trigger?.closest?.('[data-export-fields],[data-export-method]');
    const method = String(
        options.method ||
        trigger?.dataset?.exportMethod ||
        inheritedConfigNode?.dataset?.exportMethod ||
        'GET'
    ).toUpperCase();
    const fields = normalizeExportFields(
        options.fields ||
        trigger?.dataset?.exportFields ||
        inheritedConfigNode?.dataset?.exportFields ||
        ''
    );
    const params = collectExportParams(fields);

    if (method === 'POST') {
        submitExportAsPost(baseUrl, params);
        return;
    }

    window.location.href = appendParamsToUrl(baseUrl, params);
};

document.addEventListener('DOMContentLoaded', function() {
    globalLoader = document.getElementById('global-loader');
    ensureProgressElements(true);

    // Hide progress bar on initial load
    window.hideAllLoaders();

    // Animate session alerts (no auto dismiss)
    const sessionAlerts = document.querySelectorAll('.app-alerts .alert');
    sessionAlerts.forEach((alert, index) => {
        setTimeout(() => {
            alert.style.opacity = '1';
            alert.style.transform = 'translateX(0)';
        }, 100 * (index + 1));
    });

    const normalizeSidebarPath = function(path) {
        const normalized = (path || '/').replace(/\/+$/, '');
        return normalized === '' ? '/' : normalized;
    };

    const syncSidebarActiveLink = function(sidebarRoot) {
        if (!sidebarRoot) return null;
        const links = Array.from(sidebarRoot.querySelectorAll('a.menu-item[href]'));
        if (!links.length) return null;

        const currentPath = normalizeSidebarPath(window.location.pathname);
        let bestLink = null;
        let bestScore = -1;

        links.forEach((link) => {
            let linkPath = '/';
            try {
                linkPath = normalizeSidebarPath(new URL(link.href, window.location.origin).pathname);
            } catch (error) {
                linkPath = normalizeSidebarPath(link.getAttribute('href') || '/');
            }

            let score = -1;
            if (currentPath === linkPath) {
                score = 1000 + linkPath.length;
            } else if (currentPath.startsWith(`${linkPath}/`)) {
                score = 500 + linkPath.length;
            }

            if (score > bestScore) {
                bestScore = score;
                bestLink = link;
            }
        });

        links.forEach((link) => link.classList.remove('active'));
        if (bestLink && bestScore >= 0) {
            bestLink.classList.add('active');
        }

        return bestLink && bestScore >= 0 ? bestLink : null;
    };

    const scrollActiveMenuIntoView = function(sidebarRoot, smooth = true) {
        if (!sidebarRoot) return;
        const menuContainer = sidebarRoot.querySelector('.sidebar-menu');
        const activeMenu = sidebarRoot.querySelector('a.menu-item.active');
        if (!menuContainer || !activeMenu) return;

        const padding = 18;
        const containerTop = menuContainer.scrollTop;
        const containerBottom = containerTop + menuContainer.clientHeight;
        const itemTop = activeMenu.offsetTop - padding;
        const itemBottom = activeMenu.offsetTop + activeMenu.offsetHeight + padding;

        if (itemTop < containerTop) {
            menuContainer.scrollTo({ top: Math.max(0, itemTop), behavior: smooth ? 'smooth' : 'auto' });
            return;
        }

        if (itemBottom > containerBottom) {
            const nextTop = Math.max(0, itemBottom - menuContainer.clientHeight);
            menuContainer.scrollTo({ top: nextTop, behavior: smooth ? 'smooth' : 'auto' });
        }
    };

    // Call after helper declarations to avoid temporal dead zone errors
    updateMenuIndicator();

    // Start top loader as early as possible on all internal links
    document.addEventListener('pointerdown', function(e) {
        const link = e.target.closest('a');
        if (!shouldShowProgressForLink(link, e)) return;
        showProgressBar();
    }, { capture: true, passive: true });

    // Keyboard navigation fallback (Enter on focused links triggers click)
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!shouldShowProgressForLink(link, e)) return;
        showProgressBar();
    }, true);

    // Form Interception
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.classList.contains('no-loader') || form.dataset.noLoader) return;
        if (form.getAttribute('target') === '_blank') return;

        // Set flag for expected flash message after redirect
        sessionStorage.setItem('pending_flash', 'true');
        showProgressBar();
    });

    // Lifecycle events
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.hideAllLoaders();
        }
    });

    window.addEventListener('beforeunload', () => {
        showProgressBar();
    });
    
    window.addEventListener('load', () => {
        window.hideAllLoaders();
        updateMenuIndicator();
    });

    // Keep top bar responsive for Livewire navigation lifecycle.
    document.addEventListener('livewire:navigate', () => showProgressBar());
    document.addEventListener('livewire:navigating', () => showProgressBar());
    document.addEventListener('livewire:navigated', () => hideProgressBar());

    // Sliding Sidebar Indicator - stable while scrolling and after navigation
    function updateMenuIndicator(sidebarRootArg = null) {
        if (document.body.classList.contains('role-finance')) {
            return;
        }

        const sidebarRoot = sidebarRootArg || document.querySelector('.pegawai-sidebar, .atasan-sidebar');
        if (!sidebarRoot) return;

        const syncedActiveMenu = syncSidebarActiveLink(sidebarRoot);
        const activeMenu = syncedActiveMenu || sidebarRoot.querySelector('a.menu-item.active');
        const indicator = sidebarRoot?.querySelector('#sidebarIndicator');
        const menuContainer = sidebarRoot?.querySelector('.sidebar-menu');
        
        if (activeMenu && indicator && menuContainer) {
            const itemHeight = activeMenu.offsetHeight;
            const relativeTop = activeMenu.offsetTop;
            const maxTop = Math.max(0, menuContainer.scrollHeight - itemHeight);
            const safeTop = Math.max(0, Math.min(relativeTop, maxTop));

            indicator.style.opacity = '1';
            indicator.style.height = `${itemHeight}px`;
            indicator.style.top = `${safeTop}px`;
        } else if (indicator) {
            indicator.style.opacity = '0';
        }
    }

    function scheduleIndicatorRefresh(sidebarRoot, durationMs = 420) {
        if (!sidebarRoot || document.body.classList.contains('role-finance')) return;
        const startedAt = performance.now();

        const frame = (now) => {
            updateMenuIndicator(sidebarRoot);
            if (now - startedAt < durationMs) {
                requestAnimationFrame(frame);
            }
        };

        requestAnimationFrame(frame);
    }

    const sidebarRoot = document.querySelector('.pegawai-sidebar, .atasan-sidebar');
    const sidebarMenuForIndicator = sidebarRoot?.querySelector('.sidebar-menu');
    if (sidebarMenuForIndicator && sidebarMenuForIndicator.dataset.indicatorBound !== 'true') {
        sidebarMenuForIndicator.dataset.indicatorBound = 'true';
        sidebarMenuForIndicator.addEventListener('scroll', () => updateMenuIndicator(sidebarRoot), { passive: true });
    }

    if (sidebarRoot && !sidebarRoot.dataset.resizeBound) {
        sidebarRoot.dataset.resizeBound = 'true';
        window.addEventListener('resize', () => updateMenuIndicator(sidebarRoot));
    }

    document.addEventListener('livewire:navigated', () => {
        const livewireSidebar = document.querySelector('.pegawai-sidebar, .atasan-sidebar');
        if (!livewireSidebar || document.body.classList.contains('role-finance')) return;
        requestAnimationFrame(() => {
            scheduleIndicatorRefresh(livewireSidebar, 460);
            setTimeout(() => {
                scrollActiveMenuIntoView(livewireSidebar, true);
                scheduleIndicatorRefresh(livewireSidebar, 460);
            }, 120);
        });
    });

    if (sidebarRoot && !document.body.classList.contains('role-finance')) {
        requestAnimationFrame(() => {
            scheduleIndicatorRefresh(sidebarRoot, 460);
            setTimeout(() => {
                scrollActiveMenuIntoView(sidebarRoot, true);
                scheduleIndicatorRefresh(sidebarRoot, 460);
            }, 120);
        });
    }

    // Global Livewire Event Listener for Notifications
    window.addEventListener('swal', event => {
        const data = event.detail[0] || event.detail;
        if (window.showNotification) {
            window.showNotification(data.type || 'success', data.title || '', data.message || '');
        }
    });

    window.addEventListener('alert', event => {
        const data = event.detail[0] || event.detail;
        if (window.showNotification) {
            window.showNotification(data.type || 'info', data.title || '', data.message || '');
        }
    });
});
