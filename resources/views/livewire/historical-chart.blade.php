<div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6" id="historical-chart-container">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Historical Trend</h3>
            <p class="text-sm text-gray-500">Last <span id="months-display">{{ $months }}</span> months</p>
        </div>

        <!-- Period selector -->
        <div class="flex items-center space-x-2">
            <button onclick="window.historicalChart.changeMonths(3)"
                class="period-btn px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $months === 3 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                data-months="3">
                3M
            </button>
            <button onclick="window.historicalChart.changeMonths(6)"
                class="period-btn px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $months === 6 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                data-months="6">
                6M
            </button>
            <button onclick="window.historicalChart.changeMonths(12)"
                class="period-btn px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors {{ $months === 12 ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                data-months="12">
                12M
            </button>
        </div>
    </div>

    <div class="relative h-80" wire:ignore>
        <canvas id="historicalChart"></canvas>
    </div>
</div>

<script>
    window.historicalChart = {
        chart: null,
        currentMonths: @js($months),

        init() {
            this.renderChart(@json($chartData));
        },

        changeMonths(months) {
            this.currentMonths = months;

            document.getElementById('months-display').textContent = months;

            document.querySelectorAll('.period-btn').forEach(btn => {
                if (parseInt(btn.dataset.months) === months) {
                    btn.className =
                        'period-btn px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors bg-blue-600 text-white';
                } else {
                    btn.className =
                        'period-btn px-3 py-1.5 text-xs font-semibold rounded-lg transition-colors bg-gray-100 text-gray-600 hover:bg-gray-200';
                }
            });

            @this.call('setMonths', months);
        },

        renderChart(chartData) {
            const ctx = document.getElementById('historicalChart');
            if (!ctx || typeof Chart === 'undefined') return;

            // Update existing chart instead of recreating it
            if (this.chart) {
                this.chart.data = chartData;
                this.chart.update();
                return;
            }

            this.chart = new Chart(ctx, {
                type: 'line',
                data: chartData,
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                padding: 15,
                                font: {
                                    size: 12,
                                    weight: '500'
                                },
                                color: '#374151',
                                usePointStyle: true,
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            padding: 12,
                            callbacks: {
                                label: (context) => {
                                    return context.dataset.label + ': $' + context.parsed.y.toFixed(2);
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
                                callback: (value) => '$' + value.toFixed(2)
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
                            backgroundColor: 'rgba(99, 102, 241, 0.1)'
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
    };

    document.addEventListener('DOMContentLoaded', () => {
        window.historicalChart.init();
    });

    document.addEventListener('livewire:init', () => {
        Livewire.on('historicalChartUpdated', (event) => {
            window.historicalChart.renderChart(event.chartData);
        });
    });
</script>
