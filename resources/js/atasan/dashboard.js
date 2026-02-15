var activityChartInstance = activityChartInstance || null;
var statusChartInstance = statusChartInstance || null;
var isInitializingCharts = false;

document.addEventListener('DOMContentLoaded', function() {
    initActivityChart();
    initStatusChart();
});

// Listen for Livewire navigation - destroy charts first
document.addEventListener('livewire:navigated', function() {
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
    setTimeout(function() {
        initActivityChart();
        initStatusChart();
    }, 100);
});

function initActivityChart() {
    const canvas = document.getElementById('activityChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    if (activityChartInstance) {
        activityChartInstance.destroy();
    }

    // Process Data
    const rawData = window.atasanChartData?.activity || { labels: [], approved: [], rejected: [] };
    const labels = rawData.labels || [];
    const approvedData = rawData.approved || [];
    const rejectedData = rawData.rejected || [];

    // Fallback if no data
    if (labels.length === 0) {
        labels.push('No Data');
        approvedData.push(0);
        rejectedData.push(0);
    }

    activityChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Disetujui',
                    data: approvedData,
                    backgroundColor: '#10b981', // green.500
                    borderRadius: 4,
                    barPercentage: 0.6,
                },
                {
                    label: 'Ditolak',
                    data: rejectedData,
                    backgroundColor: '#ef4444', // red.500
                    borderRadius: 4,
                    barPercentage: 0.6,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: {
                        color: '#64748b',
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 6
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(44, 57, 78, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 12,
                    borderRadius: 8,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        drawBorder: false,
                        color: 'rgba(66, 93, 135, 0.08)',
                        drawTicks: false
                    },
                    ticks: {
                        color: '#9ca3af',
                        font: { size: 11, weight: '500' },
                        padding: 10,
                        stepSize: 1
                    }
                },
                x: {
                    grid: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        color: '#9ca3af',
                        font: { size: 11, weight: '500' },
                        padding: 8
                    }
                }
            }
        }
    });
}

function initStatusChart() {
    const canvas = document.getElementById('statusChart');
    if (!canvas) return;

    const ctx = canvas.getContext('2d');

    // Additional safety: Check if canvas already has a chart
    if (typeof Chart !== 'undefined' && Chart.getChart(ctx)) {
        Chart.getChart(ctx).destroy();
    }

    if (statusChartInstance) {
        statusChartInstance.destroy();
        statusChartInstance = null;
    }

    // Process Data
    const rawData = window.atasanChartData?.status || { labels: [], data: [] };
    const labels = rawData.labels || [];
    const data = rawData.data || [];

    // Colors mapping for common statuses
    const statusColors = {
        'Menunggu Atasan': '#f59e0b', // amber.500
        'Menunggu Finance': '#3b82f6', // blue.500
        'Disetujui Finance': '#10b981', // green.500
        'Ditolak': '#ef4444', // red.500
        'Ditolak Atasan': '#ef4444', // red.500
        'Ditolak Finance': '#ef4444', // red.500
        'Selesai': '#059669', // emerald.600
        'Revisi': '#8b5cf6', // violet.500
        'Draft': '#9ca3af', // gray.400
    };

    const backgroundColors = labels.map(label => statusColors[label] || '#cbd5e1'); // fallback gray

    // Fallback if no data
    if (labels.length === 0) {
        labels.push('No Data');
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
                borderColor: 'white',
                borderWidth: 3,
                borderRadius: 8,
                spacing: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#64748b',
                        font: { size: 11, weight: '500' },
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 8
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(44, 57, 78, 0.9)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    padding: 10,
                    borderRadius: 8,
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const value = context.raw;
                            const percentage = ((value / total) * 100).toFixed(1);
                            return ` ${context.label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}