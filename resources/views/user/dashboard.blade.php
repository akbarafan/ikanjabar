@extends('user.layouts.app')

@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <i class="fas fa-swimming-pool text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Kolam</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalPonds }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <i class="fas fa-fish text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Stok Ikan Saat Ini</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalFish) }}</p>
                    <p class="text-xs text-gray-500">Ekor</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100">
                    <i class="fas fa-skull-crossbones text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Mortalitas</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalDeadFish) }}</p>
                    <p class="text-xs text-gray-500">Ekor</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <i class="fas fa-layer-group text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Jenis Ikan</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalFishTypes }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100">
                    <i class="fas fa-chart-line text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pendapatan Bulan Ini</p>
                    <p class="text-lg font-bold text-gray-900">{{ $monthlyRevenue['formatted'] }}</p>
                    <div class="flex items-center mt-1">
                        @if($monthlyRevenue['growth'] >= 0)
                        <i class="fas fa-arrow-up text-green-500 text-xs mr-1"></i>
                        <span class="text-xs text-green-500">+{{ $monthlyRevenue['growth'] }}%</span>
                        @else
                        <i class="fas fa-arrow-down text-red-500 text-xs mr-1"></i>
                        <span class="text-xs text-red-500">{{ $monthlyRevenue['growth'] }}%</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Tingkat Kelangsungan Hidup</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $survival_rate }}%</p>
                </div>
                <div class="p-3 rounded-full bg-green-100">
                    <i class="fas fa-heart text-green-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-4">
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ $survival_rate }}%"></div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Feed Conversion Ratio</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $fcr }}</p>
                    <p class="text-xs text-gray-500">{{ number_format($total_feed_used) }} kg pakan</p>
                </div>
                <div class="p-3 rounded-full bg-orange-100">
                    <i class="fas fa-utensils text-orange-600 text-xl"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-xs {{ $fcr <= 1.5 ? 'text-green-600' : ($fcr <= 2.0 ? 'text-yellow-600' : 'text-red-600') }}">
                    {{ $fcr <= 1.5 ? 'Sangat Baik' : ($fcr <= 2.0 ? 'Baik' : 'Perlu Perbaikan') }}
                </span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Total Berat Penjualan</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($total_sales_weight, 1) }}</p>
                    <p class="text-xs text-gray-500">Kg</p>
                </div>
                <div class="p-3 rounded-full bg-blue-100">
                    <i class="fas fa-weight text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Water Quality Trend -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Tren Kualitas Air (7 Hari)</h3>
                <select id="pondFilter" class="text-sm border border-gray-300 rounded-lg px-3 py-1">
                    <option value="">Semua Kolam</option>
                    @foreach($pondOptions as $pond)
                    <option value="{{ $pond->id }}" {{ $selectedPondId == $pond->id ? 'selected' : '' }}>
                        {{ $pond->name }} ({{ $pond->code }})
                    </option>
                    @endforeach
                </select>
            </div>
            <!-- Fixed height container for chart -->
            <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                <canvas id="waterQualityChart"></canvas>
            </div>
        </div>

        <!-- Production Distribution -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Distribusi Stok per Jenis Ikan</h3>
            <!-- Fixed height container for chart -->
            <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                <canvas id="productionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Pond Stock Details -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Detail Stok per Kolam</h3>
            <p class="text-sm text-gray-600 mt-1">Monitoring stok ikan di setiap kolam dengan perhitungan transfer</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kolam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Ikan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Awal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Saat Ini</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transfer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terjual</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mortalitas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($pondStockDetails as $pond)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $pond->pond_name }}</div>
                                <div class="text-sm text-gray-500">{{ $pond->pond_code }}</div>
                                <div class="text-xs text-gray-400">{{ ucfirst($pond->pond_type) }} - {{ number_format($pond->volume_liters) }}L</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($pond->fish_type)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $pond->fish_type }}
                            </span>
                            @else
                            <span class="text-gray-400 text-sm">Kosong</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-900">{{ number_format($pond->initial_count) }}</span>
                            <div class="text-xs text-gray-500">Ekor</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-lg font-bold {{ $pond->current_stock > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                {{ number_format($pond->current_stock) }}
                            </span>
                            <div class="text-xs text-gray-500">Ekor</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($pond->transferred_in > 0 || $pond->transferred_out > 0)
                            <div class="space-y-1">
                                @if($pond->transferred_in > 0)
                                <div class="flex items-center text-xs">
                                    <i class="fas fa-arrow-down text-green-600 mr-1"></i>
                                    <span class="text-green-600">+{{ number_format($pond->transferred_in) }}</span>
                                </div>
                                @endif
                                @if($pond->transferred_out > 0)
                                <div class="flex items-center text-xs">
                                    <i class="fas fa-arrow-up text-red-600 mr-1"></i>
                                    <span class="text-red-600">-{{ number_format($pond->transferred_out) }}</span>
                                </div>
                                @endif
                            </div>
                            @else
                            <span class="text-xs text-gray-400">Tidak ada</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-900">{{ number_format($pond->total_sold) }}</span>
                            <div class="text-xs text-gray-500">Ekor</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-red-600">{{ number_format($pond->total_dead) }}</span>
                            <div class="text-xs text-gray-500">Ekor</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($pond->current_stock > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-400 mr-1.5"></div>
                                Aktif
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <div class="w-1.5 h-1.5 rounded-full bg-gray-400 mr-1.5"></div>
                                Kosong
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-swimming-pool text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada data kolam</h3>
                                <p class="text-gray-500">Tambahkan kolam dan batch ikan untuk melihat detail stok.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Fish Sales Analysis -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Analisis Penjualan Ikan</h3>
                    <p class="text-sm text-gray-600 mt-1">{{ $fishSalesAnalysis['period_label'] }}</p>
                </div>
                <select id="periodFilter" class="text-sm border border-gray-300 rounded-lg px-3 py-2">
                    <option value="1month" {{ $selectedPeriod == '1month' ? 'selected' : '' }}>1 Bulan Terakhir</option>
                    <option value="3months" {{ $selectedPeriod == '3months' ? 'selected' : '' }}>3 Bulan Terakhir</option>
                    <option value="6months" {{ $selectedPeriod == '6months' ? 'selected' : '' }}>6 Bulan Terakhir</option>
                    <option value="1year" {{ $selectedPeriod == '1year' ? 'selected' : '' }}>1 Tahun Terakhir</option>
                </select>
            </div>
        </div>
        <div class="p-6">
            @if(count($fishSalesAnalysis['top_fish_sales']) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <!-- Fixed height container for sales chart -->
                    <div class="chart-container" style="position: relative; height: 300px; width: 100%;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
                <div class="space-y-4">
                    @foreach($fishSalesAnalysis['top_fish_sales'] as $index => $sale)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center">
                            <div class="w-8 h-8 rounded-full bg-blue-{{ 500 + ($index * 100) }} text-white flex items-center justify-center text-sm font-medium">
                                {{ $index + 1 }}
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">{{ $sale->fish_name }}</p>
                                <p class="text-xs text-gray-500">{{ number_format($sale->total_quantity) }} ekor</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">Rp {{ number_format($sale->total_revenue, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-500">Rp {{ number_format($sale->avg_price, 0, ',', '.') }}/kg</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @else
            <div class="text-center py-12">
                <i class="fas fa-chart-bar text-gray-300 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada data penjualan</h3>
                <p class="text-gray-500">Data penjualan akan muncul setelah ada transaksi penjualan ikan.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Harvest Predictions -->
    @if(count($harvestPredictions) > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Prediksi Panen</h3>
            <p class="text-sm text-gray-600 mt-1">Batch yang siap atau akan siap dipanen</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($harvestPredictions as $prediction)
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-3">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900">Batch #{{ $prediction['batch_id'] }}</h4>
                            <p class="text-xs text-gray-500">{{ $prediction['pond_name'] }}</p>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $prediction['readiness'] === 'ready' ? 'bg-green-100 text-green-800' :
                               ($prediction['readiness'] === 'soon' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                            {{ $prediction['readiness'] === 'ready' ? 'Siap Panen' :
                               ($prediction['readiness'] === 'soon' ? 'Segera' : 'Berkembang') }}
                        </span>
                    </div>

                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Jenis Ikan:</span>
                            <span class="font-medium">{{ $prediction['fish_type'] }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Stok Saat Ini:</span>
                            <span class="font-medium">{{ number_format($prediction['current_stock']) }} ekor</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Umur:</span>
                            <span class="font-medium">{{ $prediction['age_days'] }} hari</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Estimasi Panen:</span>
                            <span class="font-medium">{{ $prediction['estimated_harvest']->format('d M Y') }}</span>
                        </div>
                        @if($prediction['days_to_harvest'] > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Sisa Hari:</span>
                            <span class="font-medium text-orange-600">{{ $prediction['days_to_harvest'] }} hari</span>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Alerts -->
    @if(count($recentAlerts) > 0)
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Peringatan Terbaru</h3>
            <p class="text-sm text-gray-600 mt-1">Monitoring kondisi yang memerlukan perhatian</p>
        </div>
        <div class="p-6">
            <div class="space-y-3">
                @foreach($recentAlerts as $alert)
                <div class="flex items-start p-3 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-red-800">{{ $alert['message'] }}</p>
                        <p class="text-xs text-red-600 mt-1">{{ $alert['detail'] }}</p>
                        <p class="text-xs text-red-500 mt-1">{{ $alert['time'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Water Quality Chart
    const waterQualityCtx = document.getElementById('waterQualityChart').getContext('2d');
    new Chart(waterQualityCtx, {
        type: 'line',
        data: {
            labels: @json($waterQualityTrend['labels']),
            datasets: [{
                label: 'Suhu (°C)',
                data: @json($waterQualityTrend['temperature']),
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: false
            }, {
                label: 'pH',
                data: @json($waterQualityTrend['ph']),
                borderColor: 'rgb(59, 130, 246)',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: false
            }, {
                label: 'DO (mg/L)',
                data: @json($waterQualityTrend['do']),
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                tension: 0.4,
                fill: false
            }, {
                label: 'Ammonia (mg/L)',
                data: @json($waterQualityTrend['ammonia']),
                borderColor: 'rgb(245, 158, 11)',
                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                tension: 0.4,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Tanggal'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Nilai'
                    },
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1
                }
            }
        }
    });

    // Production Distribution Chart
    const productionCtx = document.getElementById('productionChart').getContext('2d');
    new Chart(productionCtx, {
        type: 'doughnut',
        data: {
            labels: @json($productionDistribution['labels']),
            datasets: [{
                data: @json($productionDistribution['data']),
                backgroundColor: [
                    'rgb(59, 130, 246)',
                    'rgb(16, 185, 129)',
                    'rgb(245, 158, 11)',
                    'rgb(239, 68, 68)',
                    'rgb(139, 92, 246)',
                    'rgb(236, 72, 153)',
                    'rgb(34, 197, 94)',
                    'rgb(251, 146, 60)'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
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
                        usePointStyle: true,
                        padding: 20,
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                return data.labels.map((label, i) => {
                                    const value = data.datasets[0].data[i];
                                    return {
                                        text: `${label}: ${value.toLocaleString()} ekor`,
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        strokeStyle: data.datasets[0].backgroundColor[i],
                                        pointStyle: 'circle',
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
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value.toLocaleString()} ekor (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Sales Chart (if data exists)
    @if(count($fishSalesAnalysis['top_fish_sales']) > 0)
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'bar',
        data: {
            labels: @json($fishSalesAnalysis['chart_data']['labels']),
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: @json($fishSalesAnalysis['chart_data']['revenues']),
                backgroundColor: 'rgba(59, 130, 246, 0.8)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1,
                borderRadius: 4,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Jenis Ikan'
                    },
                    grid: {
                        display: false
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Pendapatan (Rp)'
                    },
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1,
                    callbacks: {
                        label: function(context) {
                            return 'Pendapatan: Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
    @endif

    // Filter handlers
    document.getElementById('pondFilter').addEventListener('change', function() {
        const pondId = this.value;
        const url = new URL(window.location);
        if (pondId) {
            url.searchParams.set('pond_id', pondId);
        } else {
            url.searchParams.delete('pond_id');
        }
        window.location.href = url.toString();
    });

    document.getElementById('periodFilter').addEventListener('change', function() {
        const period = this.value;
        const url = new URL(window.location);
        url.searchParams.set('period', period);
        window.location.href = url.toString();
    });

    // Auto refresh every 5 minutes
    setTimeout(function() {
        location.reload();
    }, 300000);
});

// Notification function for alerts
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full`;

    if (type === 'success') {
        notification.classList.add('bg-green-500', 'text-white');
        notification.innerHTML = `<i class="fas fa-check-circle mr-2"></i>${message}`;
    } else if (type === 'error') {
        notification.classList.add('bg-red-500', 'text-white');
        notification.innerHTML = `<i class="fas fa-exclamation-circle mr-2"></i>${message}`;
    } else if (type === 'warning') {
        notification.classList.add('bg-yellow-500', 'text-white');
        notification.innerHTML = `<i class="fas fa-exclamation-triangle mr-2"></i>${message}`;
    } else {
        notification.classList.add('bg-blue-500', 'text-white');
        notification.innerHTML = `<i class="fas fa-info-circle mr-2"></i>${message}`;
    }

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}

// Check for critical alerts on page load
@if(count($recentAlerts) > 0)
document.addEventListener('DOMContentLoaded', function() {
    showNotification('{{ count($recentAlerts) }} peringatan kualitas air memerlukan perhatian!', 'warning');
});
@endif
</script>

<style>
.gradient-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Chart container styling */
.chart-container {
    position: relative;
    width: 100%;
    height: 300px !important;
    max-height: 300px;
    overflow: hidden;
}

.chart-container canvas {
    position: absolute;
    top: 0;
    left: 0;
    width: 100% !important;
    height: 100% !important;
    max-height: 300px !important;
}

/* Custom scrollbar for tables */
.overflow-x-auto::-webkit-scrollbar {
    height: 6px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Animation for cards */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.bg-white {
    animation: fadeInUp 0.5s ease-out;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .chart-container {
        height: 250px !important;
        max-height: 250px;
    }

    .chart-container canvas {
        max-height: 250px !important;
    }
}

@media (max-width: 640px) {
    .chart-container {
        height: 200px !important;
        max-height: 200px;
    }

    .chart-container canvas {
        max-height: 200px !important;
    }
}

/* Prevent chart overflow */
.bg-white.rounded-xl {
    overflow: hidden;
}

/* Grid responsive fixes */
.grid {
    display: grid;
    gap: 1.5rem;
}

.grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

@media (min-width: 768px) {
    .md\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .md\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .lg\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .lg\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .lg\:grid-cols-5 {
        grid-template-columns: repeat(5, minmax(0, 1fr));
    }
}

/* Fix for chart legend spacing */
.chart-container .chartjs-legend {
    margin-top: 10px;
}

/* Ensure proper spacing */
.space-y-6 > * + * {
    margin-top: 1.5rem;
}

.space-y-4 > * + * {
    margin-top: 1rem;
}

/* Loading state for charts */
.chart-container::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 40px;
    height: 40px;
    border: 4px solid #f3f4f6;
    border-top: 4px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 1;
}

.chart-container canvas {
    z-index: 2;
}

@keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
}

/* Hide loading spinner when chart is loaded */
.chart-container.loaded::before {
    display: none;
}
</style>

<script>
// Add loaded class to chart containers when charts are rendered
document.addEventListener('DOMContentLoaded', function() {
    // Wait for charts to be rendered
    setTimeout(function() {
        document.querySelectorAll('.chart-container').forEach(function(container) {
            container.classList.add('loaded');
        });
    }, 1000);
});
</script>
@endsection
