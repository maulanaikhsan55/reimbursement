document.addEventListener('DOMContentLoaded', function() {
        const statusCtx = document.getElementById('statusChart');
        if (statusCtx) {
            const statusData = {
                'menunggu_atasan': {{ count($recentRequests->filter(fn($r) => $r->status === 'menunggu_atasan')) }},
                'menunggu_finance': {{ count($recentRequests->filter(fn($r) => $r->status === 'menunggu_finance')) }},
                'dicairkan': {{ count($recentRequests->filter(fn($r) => $r->status === 'dicairkan')) }},
                'ditolak_atasan': {{ count($recentRequests->filter(fn($r) => $r->status === 'ditolak_atasan')) }},
                'ditolak_finance': {{ count($recentRequests->filter(fn($r) => $r->status === 'ditolak_finance')) }}
            };

            const labels = Object.keys(statusData).map(s => {
                return s.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
            });
            const data = Object.values(statusData);

            new Chart(statusCtx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            '#0284c7',
                            '#06b6d4',
                            '#059669',
                            '#dc2626',
                            '#ef4444'
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        borderRadius: 8,
                        spacing: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { size: 11, weight: '600', family: 'system-ui' },
                                color: '#64748b',
                                padding: 15,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        }
                    }
                }
            });
        }


    });