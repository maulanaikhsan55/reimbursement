(function() {
    function initApproval() {
        const filterForm = document.getElementById('filterForm');
        const searchInput = document.getElementById('searchInput');
        const statusInput = document.getElementById('statusInput');
        const tanggalFrom = document.getElementById('tanggalFrom');
        const tanggalTo = document.getElementById('tanggalTo');
        const tableContainer = document.getElementById('tableContainer');
        const statsContainer = document.querySelector('.stats-grid')?.parentElement;

        if (!tableContainer) return;
        if (filterForm && filterForm.dataset.enhanced === '1') return;
        if (filterForm) {
            filterForm.dataset.enhanced = '1';
        }

        let activeRequestController = null;
        let latestRequestSeq = 0;

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

        const performUpdate = function(url, options = {}) {
            const { pushHistory = true } = options;
            if (pushHistory) {
                window.history.pushState({}, '', url);
            }

            tableContainer.style.opacity = '0.5';
            tableContainer.style.pointerEvents = 'none';
            
            if (statsContainer) statsContainer.style.opacity = '0.5';

            if (activeRequestController) {
                activeRequestController.abort();
            }

            const requestSeq = ++latestRequestSeq;
            activeRequestController = new AbortController();

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                signal: activeRequestController.signal,
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (requestSeq !== latestRequestSeq) return;

                if (data.table) {
                    tableContainer.innerHTML = data.table;
                }
                tableContainer.style.opacity = '1';
                tableContainer.style.pointerEvents = 'auto';

                if (statsContainer && data.stats) {
                    statsContainer.innerHTML = data.stats;
                    statsContainer.style.opacity = '1';
                }
            })
            .catch(error => {
                if (error?.name === 'AbortError') return;
                if (requestSeq !== latestRequestSeq) return;

                console.error('Error:', error);
                tableContainer.style.opacity = '1';
                tableContainer.style.pointerEvents = 'auto';
                if (statsContainer) statsContainer.style.opacity = '1';
            })
            .finally(() => {
                if (requestSeq !== latestRequestSeq) return;
                activeRequestController = null;
            });
        };

        const updateTable = debounce(function() {
            if (!filterForm) return;
            const formData = new FormData(filterForm);
            const params = new URLSearchParams(formData);
            const url = `${filterForm.action}?${params.toString()}`;
            performUpdate(url);
        }, 500);

        tableContainer.addEventListener('click', function(e) {
            const link = e.target.closest('.pagination a');
            if (link) {
                e.preventDefault();
                performUpdate(link.href);
            }
        });

        if (searchInput) searchInput.addEventListener('input', updateTable);
        if (statusInput) statusInput.addEventListener('change', updateTable);
        if (tanggalFrom) tanggalFrom.addEventListener('change', updateTable);
        if (tanggalTo) tanggalTo.addEventListener('change', updateTable);

        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                updateTable();
            });
        }

        // Real-time refresh listener (guard against duplicate bindings)
        window.removeEventListener('refresh-approval-table', window.__atasanApprovalRefreshHandler);
        window.__atasanApprovalRefreshHandler = function() {
            performUpdate(window.location.href, { pushHistory: false });
        };
        window.addEventListener('refresh-approval-table', window.__atasanApprovalRefreshHandler);

        window.exportPdf = function(e) {
            window.exportWithFilters?.(e, {
                method: 'POST',
                fields: {
                    search: 'searchInput',
                    status: 'statusInput',
                    tanggal_from: 'tanggalFrom',
                    tanggal_to: 'tanggalTo',
                },
            });
        };

        window.exportCsv = function(e) {
            window.exportWithFilters?.(e, {
                method: 'POST',
                fields: {
                    search: 'searchInput',
                    status: 'statusInput',
                    tanggal_from: 'tanggalFrom',
                    tanggal_to: 'tanggalTo',
                },
            });
        };

        window.exportXlsx = function(e) {
            window.exportWithFilters?.(e, {
                method: 'POST',
                fields: {
                    search: 'searchInput',
                    status: 'statusInput',
                    tanggal_from: 'tanggalFrom',
                    tanggal_to: 'tanggalTo',
                },
            });
        };
    }

    document.addEventListener('DOMContentLoaded', initApproval);
    document.addEventListener('livewire:navigated', initApproval);
})();
