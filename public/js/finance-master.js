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
    // Generic Live Search for Master Data
    const filterForm = document.getElementById('filterForm');
    const tableContainer = document.getElementById('tableContainer');

    if (filterForm && tableContainer) {
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

        const updateTable = debounce(function() {
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            const url = `${filterForm.action}?${params.toString()}`;

            window.history.pushState({}, '', url);

            tableContainer.style.opacity = '0.5';
            tableContainer.style.pointerEvents = 'none';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableContainer = doc.getElementById('tableContainer');
                if (newTableContainer) {
                    const newTableContent = newTableContainer.innerHTML;
                    tableContainer.innerHTML = newTableContent;
                }
                tableContainer.style.opacity = '1';
                tableContainer.style.pointerEvents = 'auto';
            })
            .catch(error => {
                console.error('Error:', error);
                tableContainer.style.opacity = '1';
                tableContainer.style.pointerEvents = 'auto';
            });
        }, 500);

        // Attach listeners to all inputs and selects within the filter form
        const inputs = filterForm.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.tagName === 'INPUT' && (input.type === 'text' || input.type === 'search')) {
                input.addEventListener('input', updateTable);
            } else {
                input.addEventListener('change', updateTable);
            }
        });

        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            updateTable();
        });
    }

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
