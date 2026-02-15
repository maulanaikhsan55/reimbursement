// Use window to persist instances across Livewire navigation if needed,
// but local module variables also work as long as we destroy them properly.
let activityChartInstance = null;
let statusChartInstance = null;
let isInitializingCharts = false;

function initAllFinanceCharts() {
    // Prevent multiple simultaneous initializations
    if (isInitializingCharts) return;
    
    // Safety check: Only run if finance chart data is present
    if (!window.financeChartData) return;
    
    isInitializingCharts = true;
    
    // Small delay to ensure DOM and window.financeChartData are fully ready
    setTimeout(() => {
        initActivityChart();
        initStatusChart();
        isInitializingCharts = false;
    }, 50);
}

// Support both standard load and Livewire navigation
if (document.readyState === 'complete') {
    initAllFinanceCharts();
} else {
    document.addEventListener('DOMContentLoaded', initAllFinanceCharts);
}

// Listen for Livewire navigation - destroy charts first
document.addEventListener('livewire:navigated', () => {
    // Destroy existing charts before navigating
    if (activityChartInstance) {
        activityChartInstance.destroy();
        activityChartInstance = null;
    }
    if (statusChartInstance) {
        statusChartInstance.destroy();
        statusChartInstance = null;
    }
    // Re-initialize after a short delay
    setTimeout(initAllFinanceCharts, 100);
});

function initActivityChart() {
    const canvas = document.getElementById('activityChart');
    if (!canvas || !window.financeChartData) return;

    const ctx = canvas.getContext('2d');
    
    // Cleanup previous instance if exists (important for Livewire)
    if (activityChartInstance) {
        activityChartInstance.destroy();
        activityChartInstance = null;
    }

    // Process Data from global window object
    const rawData = window.financeChartData?.activity || [];
    
    if (rawData.length === 0) {
        // If data not ready yet, try once more after a short delay
        if (!window.activityRetryCount) {
            window.activityRetryCount = 1;
            setTimeout(initActivityChart, 100);
            return;
        }
    }
    window.activityRetryCount = 0;

    const labels = rawData.map(item => {
        const date = new Date(item.tanggal);
        return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    });
    const data = rawData.map(item => item.count);

    // Fallback if no data
    if (labels.length === 0) {
        labels.push('Belum ada data');
        data.push(0);
    }

    // Gradient for Bars
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, '#6366f1'); // Indigo 500
    gradient.addColorStop(1, '#818cf8'); // Indigo 400

    activityChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Pengajuan',
                data: data,
                backgroundColor: gradient,
                borderRadius: 50,
                borderSkipped: false,
                barThickness: 24,
                hoverBackgroundColor: '#4f46e5', // Indigo 600
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1e293b', // Slate 800
                    titleColor: '#ffffff',
                    bodyColor: '#cbd5e1', // Slate 300
                    padding: 12,
                    cornerRadius: 12,
                    displayColors: false,
                    titleFont: { size: 13, weight: '600', family: "'Poppins', sans-serif" },
                    bodyFont: { size: 12, family: "'Poppins', sans-serif" },
                    callbacks: {
                        title: (context) => context[0].label,
                        label: function(context) {
                            return `${context.formattedValue} Pengajuan`;
                        }
                    },
                    yAlign: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: Math.max(...data, 5) + 2,
                    grid: {
                        color: '#f1f5f9',
                        borderDash: [5, 5],
                        drawBorder: false,
                    },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11, family: "'Poppins', sans-serif" },
                        padding: 10,
                        stepSize: 1
                    },
                    border: {
                        display: false
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        color: '#94a3b8',
                        font: { size: 11, family: "'Poppins', sans-serif" },
                        padding: 10
                    },
                    border: {
                        display: false
                    }
                }
            }
        }
    });
}

function initStatusChart() {
    const canvas = document.getElementById('statusChart');
    if (!canvas || !window.financeChartData) return;

    const ctx = canvas.getContext('2d');

    // Additional safety: Check if canvas already has a chart
    if (Chart.getChart(ctx)) {
        Chart.getChart(ctx).destroy();
    }

    if (statusChartInstance) {
        statusChartInstance.destroy();
        statusChartInstance = null;
    }

    // Process Data
    const rawData = window.financeChartData?.status || [];
    
    if (rawData.length === 0) {
        // If data not ready yet, try once more
        if (!window.statusRetryCount) {
            window.statusRetryCount = 1;
            setTimeout(initStatusChart, 100);
            return;
        }
    }
    window.statusRetryCount = 0;

    // Modern Palette
    const departmentColors = [
        '#6366f1', // Indigo
        '#ec4899', // Pink
        '#8b5cf6', // Violet
        '#10b981', // Emerald
        '#f59e0b'  // Amber
    ];

    const labels = rawData.map(item => item.nama_departemen || 'Unknown');
    const data = rawData.map(item => item.count);
    const backgroundColors = rawData.map((item, index) => departmentColors[index % departmentColors.length]);

    // Fallback if no data
    if (labels.length === 0) {
        labels.push('Belum ada data');
        data.push(1);
        backgroundColors.push('#e2e8f0');
    }

    statusChartInstance = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: backgroundColors,
                borderColor: '#ffffff',
                borderWidth: 3,
                borderRadius: 8,
                spacing: 2,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '50%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#64748b',
                        font: {
                            size: 11,
                            family: "'Poppins', sans-serif",
                            weight: '500'
                        },
                        boxWidth: 10,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 15
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    titleColor: '#1e293b',
                    bodyColor: '#475569',
                    padding: 12,
                    borderRadius: 8,
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    titleFont: { size: 13, weight: '600' },
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.raw;
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return ` ${context.label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}
