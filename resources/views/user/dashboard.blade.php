@extends('user.layouts.app')

@section('title', 'Dashboard - ')
@section('content')
    <!-- Branch Header -->
    <div class="mb-8 bg-gradient-to-r from-blue-600 to-blue-800 rounded-xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">{{ $branchInfo->name }}</h1>
                <p class="text-blue-100 mb-1">📍 {{ $branchInfo->location }}</p>
                <p class="text-blue-100 text-sm">PIC: {{ $branchInfo->pic_name }} | Kontak: {{ $branchInfo->contact_person }}</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-blue-200">Update terakhir</div>
                <div class="text-lg font-semibold">{{ now()->format('d M Y, H:i') }} WIB</div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-water text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Total Kolam</p>
                    <p class="text-xl font-bold text-gray-900">{{ $totalPonds }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-fish text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Stok Ikan</p>
                    <p class="text-xl font-bold text-gray-900">{{ number_format($totalFish) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-money-bill-wave text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Omset Bulan Ini</p>
                    <p class="text-lg font-bold text-gray-900">{{ $monthlyRevenue['formatted'] }}</p>
                    <p class="text-xs {{ $monthlyRevenue['growth'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $monthlyRevenue['growth'] >= 0 ? '+' : '' }}{{ $monthlyRevenue['growth'] }}%
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4 hover:shadow-md transition-shadow">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-layer-group text-purple-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Jenis Ikan</p>
                    <p class="text-xl font-bold text-gray-900">{{ $totalFishTypes }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Pond Stock Details (2/3 width) -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-water text-blue-500 mr-2"></i>
                    Detail Stok per Kolam
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-4 max-h-96 overflow-y-auto">
                    @forelse($pondStockDetails as $pond)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="font-semibold text-gray-900">{{ $pond->pond_name }}</span>
                                    <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full">{{ $pond->pond_code }}</span>
                                    <span class="text-xs px-2 py-1 bg-gray-200 text-gray-700 rounded-full">{{ ucfirst($pond->pond_type) }}</span>
                                </div>
                                <div class="text-sm text-gray-600">
                                    <span class="block">🐟 {{ $pond->fish_type ?? 'Belum ada ikan' }}</span>
                                    <span class="text-xs text-gray-500">💧 {{ number_format($pond->volume_liters) }}L</span>
                                </div>
                                <div class="flex space-x-4 mt-2 text-xs">
                                    <span class="text-red-600">💀 {{ number_format($pond->total_dead) }}</span>
                                    <span class="text-green-600">💰 {{ number_format($pond->total_sold) }}</span>
                                    @if($pond->transferred_in > 0 || $pond->transferred_out > 0)
                                        <span class="text-blue-600">🔄 {{ $pond->transferred_in - $pond->transferred_out }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold {{ $pond->current_stock > 0 ? 'text-blue-600' : 'text-gray-400' }}">
                                    {{ number_format($pond->current_stock) }}
                                </div>
                                <div class="text-xs text-gray-500">ekor</div>
                                @if($pond->current_stock > 0)
                                    <div class="text-xs text-gray-500 mt-1">
                                        Kepadatan: {{ round($pond->current_stock / ($pond->volume_liters / 1000), 1) }}/m³
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-water text-4xl mb-2"></i>
                            <p>Belum ada data kolam</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Pond Status (1/3 width) -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-heartbeat text-red-500 mr-2"></i>
                    Status Kualitas Air
                </h3>
            </div>
            <div class="p-6">
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach ($pondsStatus as $pond)
                        <div class="p-3 rounded-lg border-l-4 status-{{ $pond['status'] }}">
                            <div class="flex items-center justify-between mb-2">
                                <span class="font-medium text-gray-900">{{ $pond['name'] }}</span>
                                <div class="w-2 h-2 rounded-full
                                    @if ($pond['status'] == 'healthy') bg-green-500
                                    @elseif($pond['status'] == 'warning') bg-yellow-500
                                    @else bg-red-500 @endif">
                                </div>
                            </div>
                            <div class="text-xs text-gray-600 space-y-1">
                                <div>🌡️ {{ $pond['temperature'] }}°C | 🧪 pH {{ $pond['ph'] }}</div>
                                <div>💨 DO {{ $pond['do'] }}mg/L | ⚠️ NH₃ {{ $pond['ammonia'] }}mg/L</div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Water Quality Trend -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-chart-line text-blue-500 mr-2"></i>
                            Trend Kualitas Air (7 Hari)
                        </h3>
                        <select id="pondFilter" class="text-sm border border-gray-300 rounded-lg px-3 py-1 focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Kolam</option>
                            @foreach($pondOptions as $option)
                                <option value="{{ $option['id'] }}" {{ $selectedPondId == $option['id'] ? 'selected' : '' }}>
                                    {{ $option['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="p-6">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="waterQualityChart"></canvas>
                    </div>
                    <div class="mt-4 grid grid-cols-4 gap-2 text-center">
                        <div class="p-2 bg-red-50 rounded">
                            <div class="text-xs text-red-600 font-medium">Suhu</div>
                            <div class="text-sm font-bold text-red-700">{{ $waterQualityTrend['temperature']->last() }}°C</div>
                        </div>
                        <div class="p-2 bg-blue-50 rounded">
                            <div class="text-xs text-blue-600 font-medium">pH</div>
                            <div class="text-sm font-bold text-blue-700">{{ $waterQualityTrend['ph']->last() }}</div>
                        </div>
                        <div class="p-2 bg-green-50 rounded">
                            <div class="text-xs text-green-600 font-medium">DO</div>
                            <div class="text-sm font-bold text-green-700">{{ $waterQualityTrend['do']->last() }} mg/L</div>
                        </div>
                        <div class="p-2 bg-orange-50 rounded">
                            <div class="text-xs text-orange-600 font-medium">NH₃</div>
                            <div class="text-sm font-bold text-orange-700">{{ $waterQualityTrend['ammonia']->last() }} mg/L</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fish Sales Analysis -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-chart-bar text-green-500 mr-2"></i>
                            Analisis Penjualan
                        </h3>
                        <select id="periodFilter" class="text-sm border border-gray-300 rounded-lg px-3 py-1 focus:ring-2 focus:ring-green-500">
                            <option value="1month" {{ $selectedPeriod == '1month' ? 'selected' : '' }}>1 Bulan</option>
                            <option value="3months" {{ $selectedPeriod == '3months' ? 'selected' : '' }}>3 Bulan</option>
                            <option value="6months" {{ $selectedPeriod == '6months' ? 'selected' : '' }}>6 Bulan</option>
                            <option value="1year" {{ $selectedPeriod == '1year' ? 'selected' : '' }}>1 Tahun</option>
                        </select>
                    </div>
                </div>
                <div class="p-6">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="fishSalesChart"></canvas>
                    </div>
                    <div class="mt-4 space-y-2">
                        @foreach($fishSalesAnalysis['top_fish_sales']->take(3) as $fish)
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center">
                                    <div class="w-3 h-3 rounded-full mr-2" style="background-color: {{ ['#10b981', '#3b82f6', '#f59e0b'][$loop->index] }}"></div>
                                    <span class="font-medium">{{ $fish->fish_name }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-gray-900 font-medium">Rp {{ number_format($fish->total_revenue, 0, ',', '.') }}</span>
                                    <span class="text-gray-500 text-xs block">{{ number_format($fish->total_quantity) }} ekor</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics & Alerts -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
            <!-- Survival Rate -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-semibold text-gray-900">Survival Rate</h4>
                    <i class="fas fa-heart text-red-500"></i>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600 mb-2">{{ $survivalRate }}%</div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $survivalRate }}%"></div>
                    </div>
                </div>
            </div>

            <!-- FCR -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-semibold text-gray-900">FCR Rata-rata</h4>
                    <i class="fas fa-utensils text-orange-500"></i>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600 mb-2">{{ $averageFCR }}</div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full" style="width: {{ (2 - $averageFCR) * 50 }}%"></div>
                    </div>
                </div>
            </div>

            <!-- Target Panen -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-semibold text-gray-900">Target Panen</h4>
                    <i class="fas fa-target text-purple-500"></i>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600 mb-2">{{ $monthlyEstimatedHarvest['percentage'] }}%</div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-500 h-2 rounded-full" style="width: {{ $monthlyEstimatedHarvest['percentage'] }}%"></div>
                    </div>
                    <div class="text-xs text-gray-600 mt-2">
                        {{ number_format($monthlyEstimatedHarvest['total']) }}/{{ number_format($monthlyEstimatedHarvest['target']) }} kg
                    </div>
                </div>
            </div>

            <!-- Recent Alerts -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="font-semibold text-gray-900">Alert Terbaru</h4>
                    <i class="fas fa-bell text-yellow-500"></i>
                </div>
                <div class="space-y-2">
                    @forelse ($recentAlerts->take(2) as $alert)
                        <div class="p-2 rounded bg-red-50 border-l-4 border-red-500">
                            <p class="text-xs font-medium text-red-800">{{ $alert['message'] }}</p>
                            <p class="text-xs text-red-600">{{ $alert['detail'] }}</p>
                        </div>
                    @empty
                        <div class="text-center py-4 text-gray-500">
                            <i class="fas fa-check-circle text-2xl mb-1 text-green-400"></i>
                            <p class="text-xs">Tidak ada alert</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Harvest Predictions -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-calendar-alt text-purple-500 mr-2"></i>
                    Prediksi Panen
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    @forelse ($harvestPredictions as $prediction)
                        <div class="p-4 rounded-lg bg-gray-50 border hover:shadow-md transition-shadow">
                            <div class="mb-3">
                                <h4 class="font-medium text-gray-900">{{ $prediction['pond'] }}</h4>
                                <p class="text-sm text-gray-600">{{ $prediction['fish_type'] }}</p>
                            </div>
                            <div class="text-center">
                                @if ($prediction['status'] == 'ready')
                                    <span class="inline-block bg-green-100 text-green-800 text-xs font-medium px-2 py-1 rounded-full mb-2">
                                        ✅ Siap Panen
                                    </span>
                                @else
                                    <span class="inline-block bg-yellow-100 text-yellow-800 text-xs font-medium px-2 py-1 rounded-full mb-2">
                                        ⏳ {{ $prediction['days_left'] }} hari lagi
                                    </span>
                                @endif
                                <p class="text-sm text-gray-500">~{{ $prediction['estimated_weight'] }}kg</p>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-8 text-gray-500">
                            <i class="fas fa-calendar-times text-4xl mb-2"></i>
                            <p>Belum ada prediksi panen</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endsection

    @push('scripts')
        <script>
            // Filter handlers
            document.getElementById('pondFilter').addEventListener('change', function() {
                const pondId = this.value;
                const url = new URL(window.location.href);
                if (pondId) {
                    url.searchParams.set('pond_id', pondId);
                } else {
                    url.searchParams.delete('pond_id');
                }
                window.location.href = url.toString();
            });

            document.getElementById('periodFilter').addEventListener('change', function() {
                const period = this.value;
                const url = new URL(window.location.href);
                url.searchParams.set('period', period);
                window.location.href = url.toString();
            });

            // Water Quality Chart
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
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top' }
                    },
                    scales: {
                        x: { display: true },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { display: true, text: 'Suhu, pH, DO' }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: { display: true, text: 'Ammonia (mg/L)'                        },
                        grid: { drawOnChartArea: false },
                        max: 1.0,
                        min: 0
                    }
                }
            }
        });

        // Fish Sales Chart
        const fishSalesCtx = document.getElementById('fishSalesChart').getContext('2d');
        new Chart(fishSalesCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($fishSalesAnalysis['chart_data']['labels']) !!},
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: {!! json_encode($fishSalesAnalysis['chart_data']['revenues']) !!},
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)'
                    ],
                    borderColor: [
                        'rgb(16, 185, 129)',
                        'rgb(59, 130, 246)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)'
                    ],
                    borderWidth: 2,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Pendapatan: Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: { display: true, text: 'Pendapatan (Rp)' },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    },
                    x: {
                        title: { display: true, text: 'Jenis Ikan' }
                    }
                }
            }
        });

        // Auto refresh every 5 minutes
        setInterval(() => location.reload(), 300000);

        // Animation effects
        document.addEventListener('DOMContentLoaded', function() {
            // Animate cards on scroll
            const cards = document.querySelectorAll('.hover\\:shadow-md');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.transform = 'translateY(-2px)';
                        setTimeout(() => {
                            entry.target.style.transform = 'translateY(0)';
                        }, 300);
                    }
                });
            });
            cards.forEach(card => observer.observe(card));

            // Show notification for critical alerts
            const criticalAlerts = {{ $recentAlerts->count() }};
            if (criticalAlerts > 0) {
                showNotification(`${criticalAlerts} alert kritis memerlukan perhatian!`, 'danger');
            }
        });

        // Notification function
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 transition-all duration-300 ${
                type === 'danger' ? 'bg-red-500 text-white' :
                type === 'warning' ? 'bg-yellow-500 text-white' :
                type === 'success' ? 'bg-green-500 text-white' :
                'bg-blue-500 text-white'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-${type === 'danger' ? 'exclamation-triangle' : 'info-circle'} mr-2"></i>
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            document.body.appendChild(notification);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'r') {
                e.preventDefault();
                location.reload();
            }
        });

        // Real-time updates for stock density warnings
        function checkStockDensity() {
            const densityElements = document.querySelectorAll('[data-density]');
            densityElements.forEach(element => {
                const density = parseFloat(element.dataset.density);
                if (density > 50) {
                    element.classList.add('text-red-600', 'font-bold');
                    element.title = 'Kepadatan tinggi! Pertimbangkan untuk mengurangi stok.';
                } else if (density > 30) {
                    element.classList.add('text-yellow-600', 'font-medium');
                    element.title = 'Kepadatan sedang. Monitor pertumbuhan ikan.';
                }
            });
        }

        // Initialize density check
        document.addEventListener('DOMContentLoaded', checkStockDensity);
    </script>
@endpush

@push('styles')
    <style>
        /* Status indicators */
        .status-healthy {
            border-left-color: #10b981;
            background-color: #f0fdf4;
        }

        .status-warning {
            border-left-color: #f59e0b;
            background-color: #fffbeb;
        }

        .status-danger {
            border-left-color: #ef4444;
            background-color: #fef2f2;
        }

        /* Chart container */
        .chart-container {
            position: relative;
            width: 100%;
        }

        /* Custom scrollbar */
        .overflow-y-auto::-webkit-scrollbar {
            width: 4px;
        }

        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 2px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 2px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Hover effects */
        .hover\:shadow-md:hover {
            transition: all 0.3s ease;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .chart-container {
                height: 250px !important;
            }

            .grid.grid-cols-2.lg\:grid-cols-4 {
                grid-template-columns: 1fr;
            }

            .grid.grid-cols-1.lg\:grid-cols-3 {
                grid-template-columns: 1fr;
            }

            .grid.grid-cols-1.lg\:grid-cols-4 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {
            .grid.grid-cols-2 {
                grid-template-columns: 1fr;
            }
        }

        /* Loading animation */
        .loading {
            opacity: 0.6;
            pointer-events: none;
            position: relative;
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
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Enhanced card animations */
        .hover\:bg-gray-100:hover {
            transition: background-color 0.2s ease;
        }

        /* Gradient background for branch header */
        .bg-gradient-to-r {
            background: linear-gradient(135deg, #3b82f6 0%, #1e40af 100%);
        }

        /* Progress bars */
        .h-2 {
            transition: width 0.5s ease-in-out;
        }

        /* Notification animation */
        .fixed.top-4.right-4 {
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

        /* Focus styles for accessibility */
        select:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Print styles */
        @media print {
            .fixed, .no-print {
                display: none !important;
            }

            .shadow, .shadow-lg, .shadow-md {
                box-shadow: none !important;
                border: 1px solid #e5e7eb !important;
            }

            .bg-gradient-to-r {
                background: #3b82f6 !important;
                color: white !important;
            }
        }

        /* High density warning styles */
        .text-red-600 {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.7;
            }
        }
    </style>
@endpush

