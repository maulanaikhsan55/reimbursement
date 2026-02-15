(function() {
    if (typeof window.initPengajuan === 'undefined') {
        window.initPengajuan = function() {
            const filterForm = document.getElementById('filterForm');
            const searchInput = document.getElementById('searchInput');
            const statusInput = document.getElementById('statusInput');
            const tanggalFrom = document.getElementById('tanggalFrom');
            const tanggalTo = document.getElementById('tanggalTo');
            const tableContainer = document.getElementById('tableContainer');

            if (!filterForm || !tableContainer) return;

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

            const performUpdate = function(url) {
                window.history.pushState({}, '', url);

                tableContainer.style.opacity = '0.5';
                tableContainer.style.pointerEvents = 'none';

                const statsContainer = document.getElementById('statsContainer');
                if (statsContainer) statsContainer.style.opacity = '0.5';

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(data => {
                    // Update Table
                    if (data.table) {
                        tableContainer.innerHTML = data.table;
                    }
                    tableContainer.style.opacity = '1';
                    tableContainer.style.pointerEvents = 'auto';

                    // Update Stats
                    if (statsContainer && data.stats) {
                        statsContainer.innerHTML = data.stats;
                        statsContainer.style.opacity = '1';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    tableContainer.style.opacity = '1';
                    tableContainer.style.pointerEvents = 'auto';
                    if (statsContainer) statsContainer.style.opacity = '1';

                    // Show user-friendly error message
                    if (window.showNotification) {
                        window.showNotification('error', 'Terjadi Kesalahan', 'Gagal memuat data. Silakan refresh halaman atau coba lagi.');
                    }
                });
            };

            const updateTable = debounce(function() {
                const formData = new FormData(filterForm);
                const params = new URLSearchParams(formData);
                const url = `${filterForm.action}?${params.toString()}`;
                performUpdate(url);
            }, 500);

            // Handle Pagination Clicks
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

            filterForm.addEventListener('submit', function(e) {
                e.preventDefault();
                updateTable();
            });

            const submitPostExport = function(url, params) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                form.style.display = 'none';

                // CSRF Token
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);
                }

                // Parameters
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

            window.exportPdf = function(e) {
                e.preventDefault();
                const search = document.getElementById('searchInput')?.value || '';
                const status = document.getElementById('statusInput')?.value || '';
                const tFrom = document.getElementById('tanggalFrom')?.value || '';
                const tTo = document.getElementById('tanggalTo')?.value || '';
                
                const btn = e.currentTarget;
                let url = btn.dataset.url;
                
                if (!url) return;
                
                const params = new URLSearchParams();
                if (search) params.append('search', search);
                if (status) params.append('status', status);
                if (tFrom) params.append('tanggal_from', tFrom);
                if (tTo) params.append('tanggal_to', tTo);
                
                submitPostExport(url, params);
            };

            window.exportCsv = function(e) {
                e.preventDefault();
                const search = document.getElementById('searchInput')?.value || '';
                const status = document.getElementById('statusInput')?.value || '';
                const tFrom = document.getElementById('tanggalFrom')?.value || '';
                const tTo = document.getElementById('tanggalTo')?.value || '';
                
                const btn = e.currentTarget;
                let url = btn.dataset.url;
                
                if (!url) return;
                
                const params = new URLSearchParams();
                if (search) params.append('search', search);
                if (status) params.append('status', status);
                if (tFrom) params.append('tanggal_from', tFrom);
                if (tTo) params.append('tanggal_to', tTo);
                
                submitPostExport(url, params);
            };
        };
    }

    document.addEventListener('DOMContentLoaded', window.initPengajuan);
    document.addEventListener('livewire:navigated', window.initPengajuan);
})();