<div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Cost Trend</h3>
            <p class="text-sm text-gray-500">Last 7 days</p>
        </div>
        <div class="p-2 bg-gradient-to-br from-blue-50 to-purple-50 rounded-lg">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
            </svg>
        </div>
    </div>

    <div class="relative h-64">
        <canvas id="costChart"></canvas>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('costChart');

        if (ctx && typeof Chart !== 'undefined') {
            const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
            gradient.addColorStop(0, 'rgba(99, 102, 241, 0.3)');
            gradient.addColorStop(1, 'rgba(99, 102, 241, 0.01)');

            new Chart(ctx, {
                type: 'line',
                data: @json($chartData),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            titleFont: {
                                size: 14,
                                weight: '600'
                            },
                            bodyFont: {
                                size: 13
                            },
                            borderColor: 'rgba(99, 102, 241, 0.5)',
                            borderWidth: 1,
                            callbacks: {
                                label: function(context) {
                                    return '$' + context.parsed.y.toFixed(4);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 11,
                                    weight: '500'
                                },
                                color: '#6B7280',
                                callback: function(value) {
                                    return '$' + value.toFixed(2);
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    size: 11,
                                    weight: '500'
                                },
                                color: '#6B7280'
                            }
                        }
                    },
                    elements: {
                        line: {
                            tension: 0.4,
                            borderWidth: 3,
                            borderColor: 'rgb(99, 102, 241)',
                            backgroundColor: gradient
                        },
                        point: {
                            radius: 4,
                            backgroundColor: 'white',
                            borderWidth: 3,
                            borderColor: 'rgb(99, 102, 241)',
                            hoverRadius: 6,
                            hoverBorderWidth: 3
                        }
                    }
                }
            });
        }
    });
</script>
