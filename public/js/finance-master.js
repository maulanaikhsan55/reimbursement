// Global event listeners for Shift key visual feedback
// Moved outside init function to prevent multiple attachments during Livewire navigation
if (typeof window.handleShiftKeyDown === 'undefined') {
    window.handleShiftKeyDown = function(e) {
        const syncButton = document.getElementById('syncButton');
        const syncButtonText = document.getElementById('syncButtonText');
        if (e.key === 'Shift' && syncButtonText && syncButton && !syncButton.disabled) {
            syncButton.classList.add('btn-modern-danger');
            syncButton.classList.remove('btn-modern-primary');
            syncButtonText.innerText = 'Full Sync Accurate';
        }
    };

    window.handleShiftKeyUp = function(e) {
        const syncButton = document.getElementById('syncButton');
        const syncButtonText = document.getElementById('syncButtonText');
        if (e.key === 'Shift' && syncButtonText && syncButton && !syncButton.disabled) {
            syncButton.classList.remove('btn-modern-danger');
            syncButton.classList.add('btn-modern-primary');
            syncButtonText.innerText = 'Sync Accurate Sekarang';
        }
    };

    window.addEventListener('keydown', window.handleShiftKeyDown);
    window.addEventListener('keyup', window.handleShiftKeyUp);
}

if (typeof window.initFinanceMaster === 'undefined') {
    window.initFinanceMaster = function() {
    // Generic live filter for all finance filter forms
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    function buildFormUrl(form) {
        const action = form.getAttribute('action') || window.location.href;
        const actionUrl = new URL(action, window.location.origin);
        const params = new URLSearchParams(new FormData(form));

        actionUrl.search = params.toString();

        return `${actionUrl.pathname}${actionUrl.search}`;
    }

    function bindLiveFilterForm(form) {
        if (!form || form.dataset.liveFilterBound === 'true') {
            return;
        }

        const method = (form.getAttribute('method') || 'GET').toUpperCase();
        if (method !== 'GET') {
            return;
        }

        form.dataset.liveFilterBound = 'true';

        const scope = form.closest('.dashboard-content') || document;
        const tableContainer = scope.querySelector('#tableContainer');
        let activeRequestController = null;

        const setLoading = (isLoading) => {
            if (!tableContainer) return;
            tableContainer.style.opacity = isLoading ? '0.55' : '1';
            tableContainer.style.pointerEvents = isLoading ? 'none' : 'auto';
        };

        const navigate = (url) => {
            window.location.assign(url);
        };

        const update = debounce(() => {
            const url = buildFormUrl(form);

            if (!tableContainer) {
                navigate(url);
                return;
            }

            if (activeRequestController) {
                activeRequestController.abort();
            }

            activeRequestController = new AbortController();
            window.history.replaceState({}, '', url);
            setLoading(true);

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: activeRequestController.signal,
            })
                .then((response) => response.text())
                .then((html) => {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const nextTableContainer = doc.getElementById('tableContainer');

                    if (!nextTableContainer) {
                        navigate(url);
                        return;
                    }

                    tableContainer.innerHTML = nextTableContainer.innerHTML;
                })
                .catch((error) => {
                    if (error.name !== 'AbortError') {
                        console.error('Live filter error:', error);
                        navigate(url);
                    }
                })
                .finally(() => {
                    setLoading(false);
                    activeRequestController = null;
                });
        }, 420);

        const controls = form.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([type="button"]):not([type="reset"]), select, textarea');

        controls.forEach((control) => {
            if (control.dataset.liveFilter === 'off') {
                return;
            }

            const type = (control.getAttribute('type') || '').toLowerCase();
            const isTypingControl = control.tagName === 'TEXTAREA'
                || type === 'text'
                || type === 'search'
                || type === 'email'
                || type === 'number';

            if (isTypingControl) {
                control.addEventListener('input', update);
            }

            control.addEventListener('change', update);
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            update();
        });
    }

    const filterForms = document.querySelectorAll(
        '.dashboard-content form.filter-form-finance, .dashboard-content form.filter-form-history, .dashboard-content .filter-container form.filter-form'
    );

    filterForms.forEach(bindLiveFilterForm);

    // Sync Button logic
    const syncButton = document.getElementById('syncButton');
    const syncForm = document.getElementById('syncForm');
    const forceFullSyncInput = document.getElementById('forceFullSync');
    const syncButtonText = document.getElementById('syncButtonText');

    if (syncButton && syncForm) {
        // Remove existing listeners if any by cloning (simple way to reset listeners on re-init)
        const newSyncButton = syncButton.cloneNode(true);
        syncButton.parentNode.replaceChild(newSyncButton, syncButton);
        
        newSyncButton.addEventListener('click', function(e) {
            const isFullSync = e.shiftKey;
            
            if (forceFullSyncInput) {
                forceFullSyncInput.value = isFullSync ? '1' : '0';
            }

            const title = isFullSync ? 'Full Sync Accurate' : 'Sinkronisasi Accurate';
            const message = isFullSync 
                ? 'Anda akan melakukan sinkronisasi penuh. Seluruh data lokal akan diperbarui dan data yang sudah dihapus di Accurate akan dinonaktifkan secara lokal. Lanjutkan?' 
                : 'Proses sinkronisasi incremental akan dimulai. Lanjutkan?';

            const startSync = () => {
                // Visual feedback
                newSyncButton.disabled = true;
                newSyncButton.style.opacity = '0.7';
                newSyncButton.style.cursor = 'not-allowed';
                const currentText = newSyncButton.querySelector('#syncButtonText');
                if (currentText) {
                    currentText.innerText = 'Sedang Menyinkronkan...';
                }
                
                // Show heavy loader manually
                if (window.showLoaders) {
                    window.showLoaders('heavy');
                } else {
                    const loader = document.getElementById('global-loader');
                    if (loader) {
                        loader.style.opacity = '1';
                        loader.style.pointerEvents = 'auto';
                    }
                }

                syncForm.submit();
            };

            if (typeof window.openConfirmModal === 'function') {
                window.openConfirmModal(startSync, title, message);
            } else {
                if (confirm(message)) {
                    startSync();
                }
            }
        });
    }
}

// Initialize on first load
document.addEventListener('DOMContentLoaded', window.initFinanceMaster);

// Initialize on Livewire navigation
document.addEventListener('livewire:navigated', window.initFinanceMaster);

if (typeof window.handleSingleDelete === 'undefined') {
    window.handleSingleDelete = function(url, title = 'Hapus Data', message = 'Apakah Anda yakin ingin menghapus data ini?') {
        if (typeof window.openConfirmModal === 'function') {
            window.openConfirmModal(
                () => {
                    const form = document.getElementById('singleDeleteForm');
                    if (form) {
                        form.action = url;
                        form.submit();
                    }
                },
                title,
                message,
                'danger'
            );
        } else {
            if (confirm(message)) {
                const form = document.getElementById('singleDeleteForm');
                if (form) {
                    form.action = url;
                    form.submit();
                }
            }
        }
    }
}
} // closing for if (typeof window.initFinanceMaster === 'undefined') {
