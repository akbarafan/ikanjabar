@extends('user.layouts.app')

@section('title', 'Dashboard')
@section('content')
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Dashboard Monitoring Perikanan</h1>
        <p class="text-gray-600">Sistem terpusat untuk memantau seluruh kolam budidaya ikan</p>
        <div class="mt-4 flex items-center text-sm text-gray-500">
            <i class="fas fa-clock mr-2"></i>
            <span>Update terakhir: {{ now()->format('d F Y, H:i') }} WIB</span>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Kolam (ubah dari Total Cabang) -->
        <div class="bg-white rounded-xl shadow-lg p-6 transform hover:scale-105 transition-all duration-300">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center animate-float mr-4">
                    <i class="fas fa-water text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Kolam</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalPonds }}</p>
                    <p class="text-xs text-green-600">Aktif semua</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 transform hover:scale-105 transition-all duration-300">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center animate-float mr-4">
                    <i class="fas fa-fish text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Ikan</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalFish) }}</p>
                    <p class="text-xs text-green-600">Survival {{ $survivalRate }}%</p>
                </div>
            </div>
        </div>

        <!-- Ikan Mati (ubah dari Alert Aktif) -->
        <div class="bg-white rounded-xl shadow-lg p-6 transform hover:scale-105 transition-all duration-300">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center animate-float mr-4">
                    <i class="fas fa-skull text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Ikan Mati</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalDeadFish) }}</p>
                    <p class="text-xs text-red-600">Total kematian</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 transform hover:scale-105 transition-all duration-300">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center animate-float mr-4">
                    <i class="fas fa-exclamation-triangle text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Alert Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $activeAlerts }}</p>
                    <p class="text-xs text-yellow-600">Perlu perhatian</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pond Status & Recent Alerts (ubah dari Branch Status) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Status Kolam -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Status Kolam</h3>
                <div class="flex items-center space-x-2">
                    <i class="fas fa-water text-blue-500"></i>
                    <span class="text-sm text-gray-500">{{ $pondsStatus->count() }} kolam</span>
                </div>
            </div>
            <div class="space-y-4 max-h-96 overflow-y-auto">
                @foreach ($pondsStatus as $pond)
                    <div
                        class="flex items-center justify-between p-4 rounded-lg border status-{{ $pond['status'] }} hover:shadow-md transition-shadow">
                        <div class="flex items-center">
                            <div
                                class="w-3 h-3 rounded-full mr-3
                    @if ($pond['status'] == 'healthy') bg-green-500 animate-pulse
                    @elseif($pond['status'] == 'warning') bg-yellow-500 animate-pulse
                    @else bg-red-500 animate-ping @endif">
                            </div>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <p class="font-medium text-gray-900">{{ $pond['name'] }}</p>
                                    <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full">
                                        {{ $pond['branch_name'] }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">
                                    🌡️ {{ $pond['temperature'] }}°C |
                                    🧪 pH {{ $pond['ph'] }} |
                                    💨 DO {{ $pond['do'] }}mg/L |
                                    ⚠️ NH₃ {{ $pond['ammonia'] }}mg/L
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span
                                class="text-xs font-medium px-3 py-1 rounded-full
                    @if ($pond['status'] == 'healthy') bg-green-100 text-green-800
                    @elseif($pond['status'] == 'warning') bg-yellow-100 text-yellow-800
                    @else bg-red-100 text-red-800 @endif">
                                @if ($pond['status'] == 'healthy')
                                    ✅ Sehat
                                @elseif($pond['status'] == 'warning')
                                    ⚠️ Peringatan
                                @else
                                    🚨 Bahaya
                                @endif
                            </span>
                        </div>
                    </div>
                @endforeach

                @if ($pondsStatus->isEmpty())
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-water text-4xl mb-2"></i>
                        <p>Belum ada data kolam</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Alerts -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Alert Terbaru</h3>
                <i class="fas fa-bell text-yellow-500"></i>
            </div>
            <div class="space-y-4">
                @foreach ($recentAlerts as $alert)
                    <div
                        class="flex items-start p-4 rounded-lg border-l-4
                @if ($alert['type'] == 'danger') border-red-500 bg-red-50
                @elseif($alert['type'] == 'warning') border-yellow-500 bg-yellow-50
                @else border-blue-500 bg-blue-50 @endif">
                        <div class="flex-shrink-0 mr-3">
                            <i
                                class="fas
                        @if ($alert['type'] == 'danger') fa-exclamation-circle text-red-500
                        @elseif($alert['type'] == 'warning') fa-exclamation-triangle text-yellow-500
                        @else fa-info-circle text-blue-500 @endif">
                            </i>
                        </div>
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">{{ $alert['message'] }}</p>
                            <p class="text-sm text-gray-600">{{ $alert['detail'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $alert['time'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Water Quality Trend -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Trend Kualitas Air (7 Hari)</h3>
                <i class="fas fa-tint text-blue-500"></i>
            </div>
            <div class="chart-container" style="height: 300px;">
                <canvas id="waterQualityChart"></canvas>
            </div>
            <div class="mt-4 grid grid-cols-4 gap-4 text-center">
                <div class="p-2 bg-red-50 rounded">
                    <div class="text-xs text-red-600 font-medium">Suhu</div>
                    <div class="text-sm font-bold text-red-700">{{ end($waterQualityTrend['temperature']) }}°C</div>
                </div>
                <div class="p-2 bg-blue-50 rounded">
                    <div class="text-xs text-blue-600 font-medium">pH</div>
                    <div class="text-sm font-bold text-blue-700">{{ end($waterQualityTrend['ph']) }}</div>
                </div>
                <div class="p-2 bg-green-50 rounded">
                    <div class="text-xs text-green-600 font-medium">DO</div>
                    <div class="text-sm font-bold text-green-700">{{ end($waterQualityTrend['do']) }} mg/L</div>
                </div>
                <div class="p-2 bg-orange-50 rounded">
                    <div class="text-xs text-orange-600 font-medium">NH₃</div>
                    <div class="text-sm font-bold text-orange-700">{{ end($waterQualityTrend['ammonia']) }} mg/L</div>
                </div>
            </div>
        </div>

        <!-- Production Distribution -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Distribusi Produksi per Kolam</h3>
                <i class="fas fa-chart-pie text-green-500"></i>
            </div>
            <div class="chart-container" style="height: 300px;">
                <canvas id="productionChart"></canvas>
            </div>

            <!-- Tambahkan legend detail di bawah chart -->
            <div class="mt-4 space-y-2">
                @foreach ($productionDistribution->take(3) as $pond)
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded-full mr-2"
                                style="background-color: {{ ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'][$loop->index] }}">
                            </div>
                            <span class="font-medium">{{ $pond['name'] }}</span>
                        </div>
                        <span class="text-gray-600">{{ number_format($pond['production']) }} ekor</span>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Growth Analysis & Harvest Predictions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Growth Analysis -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Analisis Pertumbuhan</h3>
                <i class="fas fa-seedling text-green-500"></i>
            </div>
            <div class="chart-container" style="height: 300px;">
                <canvas id="growthChart"></canvas>
            </div>
        </div>

        <!-- Harvest Predictions -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Prediksi Panen</h3>
                <i class="fas fa-calendar-alt text-purple-500"></i>
            </div>
            <div class="space-y-4">
                @foreach ($harvestPredictions as $prediction)
                    <div class="flex items-center justify-between p-4 rounded-lg bg-gray-50 border">
                        <div>
                            <p class="font-medium text-gray-900">{{ $prediction['pond'] }}</p>
                            <p class="text-sm text-gray-600">{{ $prediction['branch'] }} - {{ $prediction['fish_type'] }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $prediction['days'] }} hari</p>
                        </div>
                        <div class="text-right">
                            @if ($prediction['status'] == 'ready')
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full">
                                    Siap Panen
                                </span>
                            @else
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded-full">
                                    {{ $prediction['days_left'] }} hari lagi
                                </span>
                            @endif
                            <p class="text-xs text-gray-500 mt-1">~{{ $prediction['estimated_weight'] }}kg</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-semibold text-gray-900">Survival Rate</h4>
                <i class="fas fa-heart text-red-500"></i>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-green-600 mb-2">{{ $survivalRate }}%</div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $survivalRate }}%"></div>
                </div>
                <p class="text-sm text-gray-600 mt-2">Tingkat kelangsungan hidup</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-semibold text-gray-900">FCR Rata-rata</h4>
                <i class="fas fa-utensils text-orange-500"></i>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-blue-600 mb-2">{{ $averageFCR }}</div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ (2 - $averageFCR) * 50 }}%"></div>
                </div>
                <p class="text-sm text-gray-600 mt-2">Feed Conversion Ratio</p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h4 class="text-lg font-semibold text-gray-900">Target Panen Bulan Ini</h4>
                <i class="fas fa-target text-purple-500"></i>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold text-purple-600 mb-2">{{ $monthlyEstimatedHarvest['percentage'] }}%</div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-purple-500 h-2 rounded-full"
                        style="width: {{ $monthlyEstimatedHarvest['percentage'] }}%"></div>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    {{ number_format($monthlyEstimatedHarvest['total']) }}/{{ number_format($monthlyEstimatedHarvest['target']) }}
                    kg</p>
            </div>
        </div>
    </div>

    <!-- Water Quality Parameters Info -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Parameter Kualitas Air - Standar Ideal</h3>
            <i class="fas fa-info-circle text-blue-500"></i>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center p-4 bg-red-50 rounded-lg hover-scale">
                <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-thermometer-half text-white"></i>
                </div>
                <h4 class="font-semibold text-red-700 mb-2">Suhu</h4>
                <p class="text-sm text-gray-600">Ideal: 25-28°C</p>
                <p class="text-xs text-red-600 mt-1">Bahaya: >30°C</p>
            </div>

            <div class="text-center p-4 bg-blue-50 rounded-lg hover-scale">
                <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-white font-bold text-sm">pH</span>
                </div>
                <h4 class="font-semibold text-blue-700 mb-2">pH</h4>
                <p class="text-sm text-gray-600">Ideal: 7.0-8.0</p>
                <p class="text-xs text-blue-600 mt-1">Bahaya: <6.5 atau>8.5</p>
            </div>

            <div class="text-center p-4 bg-green-50 rounded-lg hover-scale">
                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-wind text-white"></i>
                </div>
                <h4 class="font-semibold text-green-700 mb-2">Oksigen Terlarut</h4>
                <p class="text-sm text-gray-600">Ideal: >6 mg/L</p>
                <p class="text-xs text-green-600 mt-1">Bahaya: <5 mg/L</p>
            </div>

            <div class="text-center p-4 bg-orange-50 rounded-lg hover-scale">
                <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center mx-auto mb-3">
                    <span class="text-white font-bold text-xs">NH₃</span>
                </div>
                <h4 class="font-semibold text-orange-700 mb-2">Ammonia</h4>
                <p class="text-sm text-gray-600">Ideal: <0.25 mg/L</p>
                        <p class="text-xs text-orange-600 mt-1">Bahaya: >0.5 mg/L</p>
            </div>
        </div>
    </div>

    <!-- Mortality Analysis Section -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Analisis Kematian Ikan</h3>
            <i class="fas fa-chart-bar text-red-500"></i>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center p-4 bg-red-50 rounded-lg">
                <div class="w-12 h-12 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-skull text-white"></i>
                </div>
                <h4 class="font-semibold text-red-700 mb-2">Total Kematian</h4>
                <p class="text-2xl font-bold text-red-600">{{ number_format($totalDeadFish) }}</p>
                <p class="text-xs text-gray-600 mt-1">Ekor ikan</p>
            </div>

            <div class="text-center p-4 bg-yellow-50 rounded-lg">
                <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-percentage text-white"></i>
                </div>
                <h4 class="font-semibold text-yellow-700 mb-2">Mortality Rate</h4>
                <p class="text-2xl font-bold text-yellow-600">{{ 100 - $survivalRate }}%</p>
                <p class="text-xs text-gray-600 mt-1">Tingkat kematian</p>
            </div>

            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-heart text-white"></i>
                </div>
                <h4 class="font-semibold text-green-700 mb-2">Ikan Hidup</h4>
                <p class="text-2xl font-bold text-green-600">{{ number_format($totalFish) }}</p>
                <p class="text-xs text-gray-600 mt-1">Ekor ikan</p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Water Quality Chart dengan Ammonia
        const waterQualityCtx = document.getElementById('waterQualityChart').getContext('2d');
        new Chart(waterQualityCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($waterQualityTrend['labels']) !!},
                datasets: [{
                    label: 'Suhu (°C)',
                    data: {!! json_encode($waterQualityTrend['temperature']) !!},
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y'
                }, {
                    label: 'pH',
                    data: {!! json_encode($waterQualityTrend['ph']) !!},
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y'
                }, {
                    label: 'DO (mg/L)',
                    data: {!! json_encode($waterQualityTrend['do']) !!},
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y'
                }, {
                    label: 'Ammonia (mg/L)',
                    data: {!! json_encode($waterQualityTrend['ammonia']) !!},
                    borderColor: 'rgb(245, 158, 11)',
                    backgroundColor: 'rgba(245, 158, 11, 0.1)',
                    tension: 0.4,
                    yAxisID: 'y1'
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
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y;
                                    if (context.dataset.label === 'Suhu (°C)') label += '°C';
                                    else if (context.dataset.label !== 'pH') label += ' mg/L';
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Suhu (°C), pH, DO (mg/L)'
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.1)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Ammonia (mg/L)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                        max: 1.0,
                        min: 0
                    }
                }
            }
        });

        // Production Distribution Chart
        const productionCtx = document.getElementById('productionChart').getContext('2d');
        new Chart(productionCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($productionDistribution->pluck('name')) !!},
                datasets: [{
                    data: {!! json_encode($productionDistribution->pluck('production')) !!},
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)'
                    ],
                    borderColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        return {
                                            text: `${label}: ${value} ekor`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            strokeStyle: data.datasets[0].borderColor[i],
                                            lineWidth: data.datasets[0].borderWidth,
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} ekor (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });


        // Growth Analysis Chart
        const growthCtx = document.getElementById('growthChart').getContext('2d');

        // Handle empty data
        const growthLabels = {!! json_encode($growthAnalysis['labels']->toArray()) !!};
        const growthWeights = {!! json_encode($growthAnalysis['weights']->toArray()) !!};

        // Check if data is empty
        if (growthLabels.length === 0 || growthWeights.every(weight => weight === 0)) {
            document.getElementById('growthChart').parentElement.innerHTML =
                '<div class="flex items-center justify-center h-64 text-gray-500">' +
                '<div class="text-center">' +
                '<i class="fas fa-chart-bar text-4xl mb-2"></i>' +
                '<p>Belum ada data pertumbuhan</p>' +
                '</div>' +
                '</div>';
        } else {
            new Chart(growthCtx, {
                type: 'bar',
                data: {
                    labels: growthLabels,
                    datasets: [{
                        label: 'Berat Rata-rata (kg)',
                        data: growthWeights,
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: 'rgb(16, 185, 129)',
                        borderWidth: 2,
                        borderRadius: 8,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Berat: ' + context.parsed.y + ' kg';
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Berat (kg)'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Bulan'
                            },
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
        }


        // Auto refresh data every 5 minutes
        setInterval(function() {
            location.reload();
        }, 300000);

        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on scroll
            const cards = document.querySelectorAll('.transform');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-pulse');
                        setTimeout(() => {
                            entry.target.classList.remove('animate-pulse');
                        }, 1000);
                    }
                });
            });

            cards.forEach(card => {
                observer.observe(card);
            });

            // Add hover effects for water quality parameters
            const parameterCards = document.querySelectorAll('.hover-scale');
            parameterCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.classList.add('transform', 'scale-105', 'shadow-lg');
                });
                card.addEventListener('mouseleave', function() {
                    this.classList.remove('transform', 'scale-105', 'shadow-lg');
                });
            });

            // Real-time status indicator animation
            const statusIndicators = document.querySelectorAll('.w-3.h-3.rounded-full');
            statusIndicators.forEach(indicator => {
                if (indicator.classList.contains('bg-red-500')) {
                    indicator.classList.add('animate-ping');
                }
            });
        });

        // Function to show detailed pond info
        function showPondDetail(pondName) {
            // This could be expanded to show a modal with detailed information
            console.log('Showing details for pond:', pondName);
        }

        // Function to export chart data
        function exportChartData(chartType) {
            const data = {
                waterQuality: {!! json_encode($waterQualityTrend) !!},
                production: {!! json_encode($productionDistribution) !!},
                growth: {!! json_encode($growthAnalysis) !!}
            };

            const blob = new Blob([JSON.stringify(data[chartType], null, 2)], {
                type: 'application/json'
            });

            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${chartType}_data_${new Date().toISOString().split('T')[0]}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl + R to refresh
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                location.reload();
            }

            // Ctrl + E to export current view
            if (e.ctrlKey && e.key === 'e') {
                e.preventDefault();
                window.print();
            }
        });

        // Add notification system for alerts
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'danger' ? 'bg-red-500 text-white' :
        type === 'warning' ? 'bg-yellow-500 text-white' :
        type === 'success' ? 'bg-green-500 text-white' :
        'bg-blue-500 text-white'
    }`;
            notification.textContent = message;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 5000);
        }

        // Check for critical alerts on page load
        document.addEventListener('DOMContentLoaded', function() {
            const criticalAlerts = {!! json_encode($recentAlerts->where('type', 'danger')->count()) !!};
            if (criticalAlerts > 0) {
                showNotification(`${criticalAlerts} alert kritis memerlukan perhatian segera!`, 'danger');
            }

            // Check mortality rate
            const mortalityRate = {{ 100 - $survivalRate }};
            if (mortalityRate > 10) {
                showNotification(`Tingkat kematian ikan tinggi: ${mortalityRate}%`, 'warning');
            }
        });

        // Function to filter pond status
        function filterPondStatus(status) {
            const pondCards = document.querySelectorAll('.status-healthy, .status-warning, .status-danger');

            pondCards.forEach(card => {
                if (status === 'all' || card.classList.contains(`status-${status}`)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Add pond status filter buttons (you can add these to the HTML if needed)
        function addPondStatusFilters() {
            const statusSection = document.querySelector('.bg-white.rounded-xl.shadow-lg.p-6');
            if (statusSection) {
                const filterDiv = document.createElement('div');
                filterDiv.className = 'flex gap-2 mb-4';
                filterDiv.innerHTML = `
            <button onclick="filterPondStatus('all')" class="px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded-full">Semua</button>
            <button onclick="filterPondStatus('healthy')" class="px-3 py-1 text-xs bg-green-100 hover:bg-green-200 text-green-800 rounded-full">Sehat</button>
            <button onclick="filterPondStatus('warning')" class="px-3 py-1 text-xs bg-yellow-100 hover:bg-yellow-200 text-yellow-800 rounded-full">Peringatan</button>
            <button onclick="filterPondStatus('danger')" class="px-3 py-1 text-xs bg-red-100 hover:bg-red-200 text-red-800 rounded-full">Bahaya</button>
        `;

                const title = statusSection.querySelector('h3');
                title.parentNode.insertBefore(filterDiv, title.nextSibling);
            }
        }

        // Initialize filters on page load
        document.addEventListener('DOMContentLoaded', function() {
            addPondStatusFilters();
        });

        // Function to calculate and display pond statistics
        function updatePondStatistics() {
            const healthyPonds = document.querySelectorAll('.status-healthy').length;
            const warningPonds = document.querySelectorAll('.status-warning').length;
            const dangerPonds = document.querySelectorAll('.status-danger').length;
            const totalPonds = healthyPonds + warningPonds + dangerPonds;

            console.log(`Pond Statistics:
        Total: ${totalPonds}
        Healthy: ${healthyPonds} (${Math.round(healthyPonds/totalPonds*100)}%)
        Warning: ${warningPonds} (${Math.round(warningPonds/totalPonds*100)}%)
        Danger: ${dangerPonds} (${Math.round(dangerPonds/totalPonds*100)}%)`);
        }

        // Real-time clock update
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });

            const clockElement = document.querySelector('.text-gray-500 span');
            if (clockElement) {
                clockElement.textContent = `Update terakhir: ${timeString} WIB`;
            }
        }

        // Update clock every minute
        setInterval(updateClock, 60000);

        // Add smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        /* Custom animations */
        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        /* Status indicators */
        .status-healthy {
            border-left: 4px solid #10b981;
            background-color: #f0fdf4;
        }

        .status-warning {
            border-left: 4px solid #f59e0b;
            background-color: #fffbeb;
        }

        .status-danger {
            border-left: 4px solid #ef4444;
            background-color: #fef2f2;
        }

        /* Chart container */
        .chart-container {
            position: relative;
            width: 100%;
        }

        /* Hover effects */
        .hover-scale:hover {
            transform: scale(1.02);
            transition: transform 0.2s ease-in-out;
        }

        /* Custom scrollbar for pond status */
        .space-y-4 {
            max-height: 400px;
            overflow-y: auto;
        }

        .space-y-4::-webkit-scrollbar {
            width: 4px;
        }

        .space-y-4::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 2px;
        }

        .space-y-4::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 2px;
        }

        .space-y-4::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .chart-container {
                height: 250px !important;
            }

            .grid.grid-cols-4 {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.5rem;
            }

            .space-y-4 {
                max-height: 300px;
            }
        }

        /* Print styles */
        @media print {
            .no-print {
                display: none !important;
            }

            .bg-white {
                background: white !important;
            }

            .shadow-lg {
                box-shadow: none !important;
                border: 1px solid #e5e7eb !important;
            }

            .chart-container {
                height: 200px !important;
            }
        }

        /* Loading states */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        /* Alert animations */
        .alert-blink {
            animation: blink 1s linear infinite;
        }

        @keyframes blink {

            0%,
            50% {
                opacity: 1;
            }

            51%,
            100% {
                opacity: 0.5;
            }
        }

        /* Tooltip styles */
        .tooltip {
            position: relative;
            display: inline-block;
        }

        .tooltip .tooltiptext {
            visibility: hidden;
            width: 200px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 8px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -100px;
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 12px;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }

        /* Status badge animations */
        .status-badge {
            transition: all 0.3s ease;
        }

        .status-badge:hover {
            transform: scale(1.1);
        }

        /* Mortality section styling */
        .mortality-analysis {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        }

        /* Enhanced card hover effects */
        .transform:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        /* Pond status card enhancements */
        .pond-status-card {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .pond-status-card:hover {
            transform: translateX(4px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        /* Filter button styles */
        .filter-btn {
            transition: all 0.2s ease;
        }

        .filter-btn:hover {
            transform: translateY(-1px);
        }

        .filter-btn.active {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Notification styles */
        .notification {
            animation: slideInRight 0.3s ease-out;
        }

        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        /* Progress bar animations */
        .progress-bar {
            transition: width 1s ease-in-out;
        }

        /* Chart loading animation */
        .chart-loading {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 300px;
        }

        .chart-loading::after {
            content: 'Memuat data...';
            color: #6b7280;
            font-size: 14px;
        }

        /* Responsive grid adjustments */
        @media (max-width: 640px) {
            .grid.grid-cols-1.md\\:grid-cols-3 {
                grid-template-columns: 1fr;
            }

            .grid.grid-cols-1.md\\:grid-cols-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Dark mode support (if needed) */
        @media (prefers-color-scheme: dark) {
            .bg-white {
                background-color: #1f2937;
                color: #f9fafb;
            }

            .text-gray-900 {
                color: #f9fafb;
            }

            .text-gray-600 {
                color: #d1d5db;
            }

            .border {
                border-color: #374151;
            }
        }
    </style>
@endpush
