/**
 * DASHBOARD ULTRA - MODERN PROGRESS BAR
 * Like LinkedIn, Twitter, GitHub - smooth & fast
 */
import '../css/dashboard-ultra.css';

let globalLoader;
let progressBar, progressFill;

// 1. Global dismiss alert function
window.dismissAlert = function(element) {
    if (!element) return;
    element.style.opacity = '0';
    element.style.transform = 'translateX(50px)';
    setTimeout(() => element.remove(), 500);
};

// 2. Global Modern Notification Helper
// Accepts optional 4th parameter: duration in milliseconds (default 5000)
window.showNotification = function(type, title, message, duration = 5000) {
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

    const triggerToast = () => {
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: duration,
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
            }
        });

        Toast.fire({
            icon: type === 'danger' || type === 'error' ? 'error' : (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'info')),
            title: title,
            html: `<div style="font-family: 'Poppins', sans-serif; font-size: 0.85rem; color: #64748b;">${message}</div>`,
        });
    };

    if (typeof Swal === 'undefined') {
        const checkSwal = setInterval(() => {
            if (typeof Swal !== 'undefined') {
                clearInterval(checkSwal);
                triggerToast();
            }
        }, 100);
        setTimeout(() => clearInterval(checkSwal), Math.max(duration, 5000));
    } else {
        triggerToast();
    }
};

// 3. ULTRA MODERN Progress Bar Functions - SEAMLESS FLOW Ujung ke Ujung
function showProgressBar() {
    if (!progressBar) {
        progressBar = document.getElementById('modernProgressBar');
        progressFill = progressBar?.querySelector('.progress-bar-fill');
    }
    
    if (progressBar && progressFill) {
        // Enable smooth continuous flow immediately
        progressBar.style.opacity = '1';
        progressBar.classList.add('progress-bar-active');
        progressBar.classList.remove('progress-bar-exit');
        progressFill.style.opacity = '1';
        progressFill.style.animation = 'smooth-flow 0.6s ease-in-out infinite';
    }
}

function hideProgressBar() {
    if (!progressBar) {
        progressBar = document.getElementById('modernProgressBar');
        progressFill = progressBar?.querySelector('.progress-bar-fill');
    }
    
    if (progressBar && progressFill) {
        // Instant hide - no waiting
        progressBar.classList.remove('progress-bar-active');
        progressBar.classList.add('progress-bar-exit');
        progressFill.style.animation = 'none';
        
        // Force reflow then fade
        void progressBar.offsetWidth;
        progressBar.style.opacity = '0';
    }
}

// Expose to global scope
window.showProgressBar = showProgressBar;
window.hideProgressBar = hideProgressBar;

// 4. Loader Functions (for form submissions)
window.hideAllLoaders = () => {
    sessionStorage.removeItem('pending_flash');
    hideProgressBar();
    
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

    if (type === 'heavy') {
        showProgressBar();
        const loader = globalLoader || document.getElementById('global-loader');
        if (loader) {
            loader.style.opacity = '1';
            loader.style.pointerEvents = 'auto';
            loader.style.zIndex = '9000';
        }
    } else {
        // For navigation, use progress bar
        showProgressBar();
    }
};

document.addEventListener('DOMContentLoaded', function() {
    globalLoader = document.getElementById('global-loader');
    progressBar = document.getElementById('modernProgressBar');

    // Hide progress bar on initial load
    hideProgressBar();
    updateMenuIndicator();

    // Animate session alerts
    const sessionAlerts = document.querySelectorAll('.app-alerts .alert');
    sessionAlerts.forEach((alert, index) => {
        setTimeout(() => {
            alert.style.opacity = '1';
            alert.style.transform = 'translateX(0)';
        }, 100 * (index + 1));

        setTimeout(() => {
            window.dismissAlert(alert);
        }, 8000 + (index * 500));
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

    // Navigation Interception for non-wire:navigate links
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
                !link.hasAttribute('download') &&
                !link.classList.contains('no-loader') &&
                !link.dataset.noLoader &&
                !link.hasAttribute('wire:navigate') &&
                href !== window.location.href
            ) {
                try {
                    const url = new URL(href, window.location.origin);
                    if (url.origin === window.location.origin) {
                        // Show progress bar with seamless flow IMMEDIATELY
                        if (progressBar && progressBar.querySelector('.progress-bar-fill')) {
                            progressBar.style.opacity = '1';
                            progressBar.classList.add('progress-bar-active');
                            const fill = progressBar.querySelector('.progress-bar-fill');
                            fill.style.opacity = '1';
                            fill.style.animation = 'smooth-flow 0.6s ease-in-out infinite';
                        }
                    }
                } catch (err) {}
            }
        }
    });

    // Form Interception
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.classList.contains('no-loader')) return;

        // Set flag for expected flash message after redirect
        sessionStorage.setItem('pending_flash', 'true');
        showProgressBar();
    });

    // Lifecycle events
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            hideProgressBar();
        }
    });
    
    window.addEventListener('load', () => {
        hideProgressBar();
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

    // Livewire navigation events
    document.addEventListener('livewire:navigate', () => {
        showProgressBar();
    });

    document.addEventListener('livewire:navigated', () => {
        hideProgressBar();
        updateMenuIndicator();
        sessionStorage.removeItem('pending_flash');
    });
    
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
