/**
 * DASHBOARD ULTRA - MODERN PROGRESS BAR
 * Like LinkedIn, Twitter, GitHub - smooth & fast
 */
import '../css/dashboard-ultra.css';

let globalLoader;
let progressBar, progressFill;
let loaderFailSafeTimer = null;
let hideProgressTimer = null;
let isProgressVisible = false;
let progressTrickleTimer = null;
let progressValue = 0;
let progressVisibleSince = 0;

const PROGRESS_MIN_VISIBLE_MS = 240;
const PROGRESS_HIDE_ANIMATION_MS = 120;
const PROGRESS_START_VALUE = 0.16;
const PROGRESS_ACTIVE_FLOOR = 0.22;

// 1. Global dismiss alert function
window.dismissAlert = function(element) {
    if (!element) return;
    element.style.opacity = '0';
    element.style.transform = 'translateX(50px)';
    setTimeout(() => element.remove(), 500);
};

// 2. Global Modern Notification Helper
// Optional parameters:
// - 4th: duration in ms (default 0 = close manually)
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

    const hasAutoClose = Number(duration) > 0;
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
            timer: hasAutoClose ? duration : undefined,
            timerProgressBar: hasAutoClose,
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

function startProgressTrickle() {
    if (progressTrickleTimer) {
        clearInterval(progressTrickleTimer);
    }

    progressTrickleTimer = setInterval(() => {
        if (!isProgressVisible) {
            return;
        }

        let step = 0.05;
        if (progressValue >= 0.7) step = 0.02;
        if (progressValue >= 0.85) step = 0.01;
        if (progressValue >= 0.93) step = 0.004;

        setProgress(Math.min(0.96, progressValue + step));
    }, 140);
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

    if (event?.defaultPrevented) {
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
    if (!progressBar) {
        progressBar = document.getElementById('modernProgressBar');
        progressFill = progressBar?.querySelector('.progress-bar-fill');
    }
    
    if (progressBar && progressFill) {
        if (hideProgressTimer) {
            clearTimeout(hideProgressTimer);
            hideProgressTimer = null;
        }
        if (!isProgressVisible) {
            isProgressVisible = true;
            progressVisibleSince = Date.now();
            progressBar.classList.remove('progress-bar-exit');
            progressBar.classList.add('progress-bar-active');
            progressBar.style.opacity = '1';
            progressFill.style.opacity = '1';
            setProgress(PROGRESS_START_VALUE);
            startProgressTrickle();
            return;
        }

        setProgress(Math.max(progressValue, PROGRESS_ACTIVE_FLOOR));
    }
}

function hideProgressBar() {
    if (!progressBar) {
        progressBar = document.getElementById('modernProgressBar');
        progressFill = progressBar?.querySelector('.progress-bar-fill');
    }
    
    if (progressBar && progressFill) {
        if (!isProgressVisible && progressBar.style.opacity === '0') {
            return;
        }

        isProgressVisible = false;
        if (progressTrickleTimer) {
            clearInterval(progressTrickleTimer);
            progressTrickleTimer = null;
        }

        setProgress(1);

        if (hideProgressTimer) {
            clearTimeout(hideProgressTimer);
        }

        const elapsed = Date.now() - progressVisibleSince;
        const waitBeforeHide = Math.max(PROGRESS_HIDE_ANIMATION_MS, PROGRESS_MIN_VISIBLE_MS - elapsed);

        hideProgressTimer = setTimeout(() => {
            if (isProgressVisible) {
                return;
            }
            progressBar.classList.remove('progress-bar-active');
            progressBar.classList.add('progress-bar-exit');
            progressFill.style.opacity = '0';
            progressBar.style.opacity = '0';
            setProgress(0);
            hideProgressTimer = null;
        }, waitBeforeHide);
    }
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
    
    const loader = globalLoader || document.getElementById('global-loader');
    if (loader) {
        loader.style.opacity = '0';
        loader.style.pointerEvents = 'none';
        loader.style.zIndex = '-1';
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
        showProgressBar();
        const loader = globalLoader || document.getElementById('global-loader');
        if (loader) {
            loader.style.opacity = '1';
            loader.style.pointerEvents = 'auto';
            loader.style.zIndex = '9000';
        }

        // Fail-safe: never leave overlay stuck if some request path fails silently
        loaderFailSafeTimer = setTimeout(() => {
            window.hideAllLoaders();
        }, 12000);
    } else {
        // For navigation, use progress bar
        showProgressBar();
    }
};

document.addEventListener('DOMContentLoaded', function() {
    globalLoader = document.getElementById('global-loader');
    progressBar = document.getElementById('modernProgressBar');

    // Hide progress bar on initial load
    window.hideAllLoaders();
    updateMenuIndicator();

    // Animate session alerts (no auto dismiss)
    const sessionAlerts = document.querySelectorAll('.app-alerts .alert');
    sessionAlerts.forEach((alert, index) => {
        setTimeout(() => {
            alert.style.opacity = '1';
            alert.style.transform = 'translateX(0)';
        }, 100 * (index + 1));
    });

    // INTERACTIVE SIDEBAR - Hover effects
    const menuItems = document.querySelectorAll('.menu-item:not(.logout-item-menu)');
    const indicator = document.getElementById('sidebarIndicator');
    const menuContainer = document.querySelector('.sidebar-menu');
    
    menuItems.forEach(item => {
        item.addEventListener('mouseenter', function() {
            // Add subtle scale effect to hovered item
            this.style.transform = 'translateX(4px) scale(1.02)';
            this.style.boxShadow = '0 4px 12px rgba(66, 93, 135, 0.15)';
        });
        
        item.addEventListener('mouseleave', function() {
            // Remove scale effect
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });

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

    // Sliding Sidebar Indicator - Fixed positioning calculation
    function updateMenuIndicator() {
        const activeMenu = document.querySelector('.menu-item.active');
        const indicator = document.getElementById('sidebarIndicator');
        const menuContainer = document.querySelector('.sidebar-menu');
        
        if (activeMenu && indicator && menuContainer) {
            setTimeout(() => {
                const menuRect = menuContainer.getBoundingClientRect();
                const activeRect = activeMenu.getBoundingClientRect();
                
                // Calculate position relative to the menu container
                const relativeTop = activeRect.top - menuRect.top + menuContainer.scrollTop;
                
                indicator.style.opacity = '1';
                indicator.style.height = `${activeMenu.offsetHeight}px`;
                indicator.style.top = `${relativeTop}px`;
            }, 100);
        } else if (indicator) {
            indicator.style.opacity = '0';
        }
    }

    window.addEventListener('resize', updateMenuIndicator);

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
