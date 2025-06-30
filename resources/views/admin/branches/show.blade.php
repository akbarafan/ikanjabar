@extends('admin.layouts.app')

@section('title', 'Detail Cabang - ' . $branch->name)
@section('page-title', 'Detail Cabang')

@section('content')
<div class="container mx-auto px-6">
    <!-- Header -->
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $branch->name }}</h1>
            <p class="text-gray-600">{{ $branch->location }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('admin.branches.edit', $branch) }}" class="bg-yellow-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-yellow-600 transition-colors">
                <i class="fas fa-edit mr-2"></i> Edit
            </a>
            <a href="{{ route('admin.branches.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>
    </div>

    <!-- Branch Info Card -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Cabang</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-3">
                <div class="flex">
                    <span class="text-gray-500 w-32">Nama Cabang:</span>
                    <span class="font-medium">{{ $branch->name }}</span>
                </div>
                <div class="flex">
                    <span class="text-gray-500 w-32">Lokasi:</span>
                    <span>{{ $branch->location }}</span>
                </div>
                <div class="flex">
                    <span class="text-gray-500 w-32">PIC:</span>
                    <span>{{ $branch->pic_name }}</span>
                </div>
                <div class="flex">
                    <span class="text-gray-500 w-32">Kontak:</span>
                    <span>{{ $branch->contact_person }}</span>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-500">Total Kolam</p>
                    <p class="text-2xl font-bold text-blue-700">{{ $totalPonds }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-500">Stok Ikan</p>
                    <p class="text-2xl font-bold text-green-700">{{ number_format($totalFish) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
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

        <div class="bg-white rounded-lg shadow p-4">
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

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-money-bill-wave text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Omset Bulan Ini</p>
                    <p class="text-lg font-bold text-gray-900">{{ $monthlyRevenue['formatted'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
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

    <!-- Pond Stock Details -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Detail Stok per Kolam</h3>
        <div class="space-y-4">
            @forelse($pondStockDetails as $pond)
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-2">
                            <span class="font-semibold text-gray-900">{{ $pond->pond_name }}</span>
                            <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full">{{ $pond->pond_code }}</span>
                        </div>
                        <div class="text-sm text-gray-600">
                            <span>🐟 {{ $pond->fish_type ?? 'Belum ada ikan' }}</span>
                            <span class="ml-4">💧 {{ number_format($pond->volume_liters) }}L</span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-blue-600">
                            {{ number_format($pond->current_stock) }}
                        </div>
                        <div class="text-xs text-gray-500">ekor</div>
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

    <!-- Water Quality Status -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Status Kualitas Air</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($pondsStatus as $pond)
                <div class="p-4 rounded-lg border-l-4 
                    @if ($pond['status'] == 'healthy') border-green-500 bg-green-50
                    @elseif($pond['status'] == 'warning') border-yellow-500 bg-yellow-50
                    @else border-red-500 bg-red-50 @endif">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-medium text-gray-900">{{ $pond['name'] }}</span>
                        <div class="w-3 h-3 rounded-full
                            @if ($pond['status'] == 'healthy') bg-green-500
                            @elseif($pond['status'] == 'warning') bg-yellow-500
                            @else bg-red-500 @endif">
                        </div>
                    </div>
                    <div class="text-sm text-gray-600 space-y-1">
                        <div>🌡️ {{ $pond['temperature'] }}°C | 🧪 pH {{ $pond['ph'] }}</div>
                        <div>💨 DO {{ $pond['do'] }}mg/L | ⚠️ NH₃ {{ $pond['ammonia'] }}mg/L</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Performance Metrics -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Metrik Performa</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Survival Rate</h4>
                <div class="text-3xl font-bold text-blue-600 mb-2">{{ $survivalRate }}%</div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $survivalRate }}%"></div>
                </div>
            </div>

            <div class="text-center p-4 bg-green-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">FCR Rata-rata</h4>
                <div class="text-3xl font-bold text-green-600 mb-2">{{ $averageFCR }}</div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full" style="width: {{ (2 - $averageFCR) * 50 }}%"></div>
                </div>
            </div>

            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Alert Terbaru</h4>
                <div class="text-3xl font-bold text-purple-600 mb-2">{{ $recentAlerts->count() }}</div>
                <div class="text-xs text-gray-600">
                    {{ $recentAlerts->count() > 0 ? 'Perlu perhatian' : 'Semua normal' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Water Quality Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Trend Kualitas Air (7 Hari)</h3>
                <select id="pondFilter" class="text-sm border border-gray-300 rounded px-2 py-1">
                    <option value="">Semua Kolam</option>
                    @foreach($pondOptions as $option)
                        <option value="{{ $option['id'] }}" {{ $selectedPondId == $option['id'] ? 'selected' : '' }}>
                            {{ $option['name'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="height: 300px;">
                <canvas id="waterQualityChart"></canvas>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Analisis Penjualan</h3>
                <select id="periodFilter" class="text-sm border border-gray-300 rounded px-2 py-1">
                    <option value="1month" {{ $selectedPeriod == '1month' ? 'selected' : '' }}>1 Bulan</option>
                    <option value="3months" {{ $selectedPeriod == '3months' ? 'selected' : '' }}>3 Bulan</option>
                    <option value="6months" {{ $selectedPeriod == '6months' ? 'selected' : '' }}>6 Bulan</option>
                    <option value="1year" {{ $selectedPeriod == '1year' ? 'selected' : '' }}>1 Tahun</option>
                </select>
            </div>
            <div style="height: 300px;">
                <canvas id="fishSalesChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Harvest Predictions -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Prediksi Panen</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @forelse ($harvestPredictions as $prediction)
                <div class="p-4 rounded-lg bg-gray-50 border">
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

    <!-- Ponds Table -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Daftar Kolam</h3>
            <a href="{{ route('admin.ponds.create') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                <i class="fas fa-plus mr-1"></i> Tambah Kolam
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Nama Kolam</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Volume</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($branch->ponds as $pond)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $pond->name }}</td>
                        <td class="py-3 px-4 text-sm text-gray-500">{{ $pond->type }}</td>
                        <td class="py-3 px-4 text-sm text-gray-500">{{ $pond->volume }} m³</td>
                        <td class="py-3 px-4 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                        </td>
                        <td class="py-3 px-4 text-sm">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.ponds.show', $pond) }}" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                {{-- <a href="{{ route('admin.ponds.edit', $pond) }}" class="text-yellow-600 hover:text-yellow-900">
                                    <i class="fas fa-edit"></i>
                                </a> --}}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            Belum ada kolam
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Pengguna Cabang</h3>
            <a href="{{ route('admin.users.create') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                <i class="fas fa-plus mr-1"></i> Tambah Pengguna
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Peran</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($branch->users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="py-3 px-4 text-sm text-gray-500">{{ $user->email }}</td>
                        <td class="py-3 px-4 text-sm text-gray-500">{{ $user->role ?? 'Operator' }}</td>
                        <td class="py-3 px-4 text-sm">
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                        </td>
                        <td class="py-3 px-4 text-sm">
                            <div class="flex space-x-2">
                                <a href="{{ route('admin.users.show', $user) }}" class="text-blue-600 hover:text-blue-900">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-yellow-600 hover:text-yellow-900">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            Belum ada pengguna
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Filter handlers
    document.getElementById('pondFilter')?.addEventListener('change', function() {
        const pondId = this.value;
        const url = new URL(window.location.href);
        if (pondId) {
            url.searchParams.set('pond_id', pondId);
        } else {
            url.searchParams.delete('pond_id');
        }
        window.location.href = url.toString();
    });

    document.getElementById('periodFilter')?.addEventListener('change', function() {
        const period = this.value;
        const url = new URL(window.location.href);
        url.searchParams.set('period', period);
        window.location.href = url.toString();
    });

    // Water Quality Chart
    const waterQualityCtx = document.getElementById('waterQualityChart');
    if (waterQualityCtx) {
        new Chart(waterQualityCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($waterQualityTrend['labels']) !!},
                datasets: [{
                    label: 'Suhu (°C)',
                    data: {!! json_encode($waterQualityTrend['temperature']) !!},
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4
                }, {
                    label: 'pH',
                    data: {!! json_encode($waterQualityTrend['ph']) !!},
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }, {
                    label: 'DO (mg/L)',
                    data: {!! json_encode($waterQualityTrend['do']) !!},
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Fish Sales Chart
    const fishSalesCtx = document.getElementById('fishSalesChart');
    if (fishSalesCtx) {
        new Chart(fishSalesCtx.getContext('2d'), {
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
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                }
            }
        });
    }

    // Auto refresh every 5 minutes
    setInterval(() => location.reload(), 300000);
</script>
@endpush

@push('styles')
<style>
    /* Custom styles for branch detail */
    .container {
        max-width: 1200px;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    
    /* Status indicators */
    .border-green-500 { border-left-color: #10b981; }
    .border-yellow-500 { border-left-color: #f59e0b; }
    .border-red-500 { border-left-color: #ef4444; }
    
    .bg-green-50 { background-color: #f0fdf4; }
    .bg-yellow-50 { background-color: #fffbeb; }
    .bg-red-50 { background-color: #fef2f2; }
    
    /* Hover effects */
    .hover\:shadow-md:hover {
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    
    /* Progress bars */
    .h-2 {
        transition: width 0.5s ease-in-out;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .grid.grid-cols-1.md\:grid-cols-4 {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .grid.grid-cols-1.lg\:grid-cols-2 {
            grid-template-columns: 1fr;
        }
        
        .chart-container {
            height: 250px;
        }
    }
    
    @media (max-width: 640px) {
        .grid.grid-cols-1.md\:grid-cols-4 {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush
