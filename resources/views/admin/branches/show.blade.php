@extends('admin.layouts.app')

@section('title', 'Detail Cabang - ' . $branch->name)
@section('page-title', 'Detail Cabang')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">{{ $branch->name }}</h1>
        <p class="text-gray-600">{{ $branch->location }}</p>
    </div>
    <div class="flex space-x-3">
        <a href="{{ route('branches.edit', $branch) }}" class="bg-yellow-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-yellow-600 transition-colors">
            <i class="fas fa-edit mr-2"></i> Edit
        </a>
        <a href="{{ route('branches.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-600 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>
</div>

<!-- Branch Overview -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Cabang</h3>
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
                <div class="flex">
                    <span class="text-gray-500 w-32">Status:</span>
                    <span>
                        @if($branch->is_active ?? true)
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Non-Aktif</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div>
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Cabang</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-blue-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Kolam</p>
                    <p class="text-2xl font-bold text-blue-700">{{ $statistics['overview']['total_ponds'] }}</p>
                </div>
                <div class="bg-green-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Volume Total</p>
                    <p class="text-2xl font-bold text-green-700">{{ $statistics['overview']['total_volume'] }} m³</p>
                </div>
                <div class="bg-purple-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Pengguna</p>
                    <p class="text-2xl font-bold text-purple-700">{{ $statistics['overview']['total_users'] }}</p>
                </div>
                <div class="bg-yellow-50 rounded-lg p-4">
                    <p class="text-sm text-gray-500">Batch Aktif</p>
                    <p class="text-2xl font-bold text-yellow-700">{{ $statistics['overview']['total_active_batches'] }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Production Statistics -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Produksi</h3>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-blue-50 rounded-lg p-4">
            <p class="text-sm text-gray-500">Stok Ikan</p>
            <p class="text-2xl font-bold text-blue-700">
                                {{ $statistics['production']['total_fish_stock'] }}
            </p>
            <p class="text-xs text-gray-500 mt-1">Jumlah ikan aktif</p>
        </div>

        <div class="bg-green-50 rounded-lg p-4">
            <p class="text-sm text-gray-500">Total Penjualan</p>
            <p class="text-2xl font-bold text-green-700">
                {{ $statistics['production']['total_sales'] }}
            </p>
            <p class="text-xs text-gray-500 mt-1">Akumulasi penjualan</p>
        </div>

        <div class="bg-yellow-50 rounded-lg p-4">
            <p class="text-sm text-gray-500">Kepadatan Rata-rata</p>
            <p class="text-2xl font-bold text-yellow-700">
                {{ $statistics['production']['average_density'] }}%
            </p>
            <p class="text-xs text-gray-500 mt-1">Dari kapasitas maksimal</p>
        </div>

        <div class="bg-indigo-50 rounded-lg p-4">
            <p class="text-sm text-gray-500">Skor Produktivitas</p>
            <p class="text-2xl font-bold text-indigo-700">
                {{ $statistics['production']['productivity_score'] }}/100
            </p>
            <p class="text-xs text-gray-500 mt-1">Berdasarkan berbagai faktor</p>
        </div>
    </div>
</div>

<!-- Water Quality -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Kualitas Air Rata-rata</h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-gray-700">pH Air</h4>
                <span class="text-xs px-2 py-1 rounded-full
                    {{ ($statistics['water_quality']['avg_ph'] >= 7.0 && $statistics['water_quality']['avg_ph'] <= 8.0) ? 'bg-green-100 text-green-800' :
                       (($statistics['water_quality']['avg_ph'] >= 6.5 && $statistics['water_quality']['avg_ph'] <= 8.5) ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ ($statistics['water_quality']['avg_ph'] >= 7.0 && $statistics['water_quality']['avg_ph'] <= 8.0) ? 'Optimal' :
                       (($statistics['water_quality']['avg_ph'] >= 6.5 && $statistics['water_quality']['avg_ph'] <= 8.5) ? 'Normal' : 'Perhatian') }}
                </span>
            </div>
            <div class="flex items-end">
                <span class="text-3xl font-bold text-blue-700">{{ number_format($statistics['water_quality']['avg_ph'], 1) }}</span>
                <span class="text-sm text-gray-500 ml-2 mb-1">pH</span>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                Rentang ideal: 7.0 - 8.0 pH
            </div>
        </div>

        <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-gray-700">Suhu Air</h4>
                <span class="text-xs px-2 py-1 rounded-full
                    {{ ($statistics['water_quality']['avg_temperature'] >= 25 && $statistics['water_quality']['avg_temperature'] <= 30) ? 'bg-green-100 text-green-800' :
                       (($statistics['water_quality']['avg_temperature'] >= 22 && $statistics['water_quality']['avg_temperature'] <= 32) ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ ($statistics['water_quality']['avg_temperature'] >= 25 && $statistics['water_quality']['avg_temperature'] <= 30) ? 'Optimal' :
                       (($statistics['water_quality']['avg_temperature'] >= 22 && $statistics['water_quality']['avg_temperature'] <= 32) ? 'Normal' : 'Perhatian') }}
                </span>
            </div>
            <div class="flex items-end">
                <span class="text-3xl font-bold text-blue-700">{{ number_format($statistics['water_quality']['avg_temperature'], 1) }}</span>
                <span class="text-sm text-gray-500 ml-2 mb-1">°C</span>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                Rentang ideal: 25 - 30 °C
            </div>
        </div>

        <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-gray-700">Oksigen Terlarut</h4>
                <span class="text-xs px-2 py-1 rounded-full
                    {{ $statistics['water_quality']['avg_do'] >= 5 ? 'bg-green-100 text-green-800' :
                       ($statistics['water_quality']['avg_do'] >= 4 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ $statistics['water_quality']['avg_do'] >= 5 ? 'Optimal' :
                       ($statistics['water_quality']['avg_do'] >= 4 ? 'Normal' : 'Perhatian') }}
                </span>
            </div>
            <div class="flex items-end">
                <span class="text-3xl font-bold text-blue-700">{{ number_format($statistics['water_quality']['avg_do'], 1) }}</span>
                <span class="text-sm text-gray-500 ml-2 mb-1">mg/L</span>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                Rentang ideal: > 5 mg/L
            </div>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Metrik Performa</h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-gray-700">Tingkat Mortalitas</h4>
                <span class="text-xs px-2 py-1 rounded-full
                    {{ $statistics['performance']['mortality_rate'] <= 5 ? 'bg-green-100 text-green-800' :
                       ($statistics['performance']['mortality_rate'] <= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ $statistics['performance']['mortality_rate'] <= 5 ? 'Rendah' :
                       ($statistics['performance']['mortality_rate'] <= 10 ? 'Sedang' : 'Tinggi') }}
                </span>
            </div>
            <div class="flex items-end">
                <span class="text-3xl font-bold text-blue-700">{{ number_format($statistics['performance']['mortality_rate'], 1) }}</span>
                <span class="text-sm text-gray-500 ml-2 mb-1">%</span>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                Target: < 5%
            </div>
        </div>

        <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-gray-700">Tingkat Pertumbuhan</h4>
                <span class="text-xs px-2 py-1 rounded-full
                    {{ $statistics['performance']['growth_rate'] >= 2.5 ? 'bg-green-100 text-green-800' :
                       ($statistics['performance']['growth_rate'] >= 1.5 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ $statistics['performance']['growth_rate'] >= 2.5 ? 'Tinggi' :
                       ($statistics['performance']['growth_rate'] >= 1.5 ? 'Normal' : 'Rendah') }}
                </span>
            </div>
            <div class="flex items-end">
                <span class="text-3xl font-bold text-blue-700">{{ number_format($statistics['performance']['growth_rate'], 2) }}</span>
                <span class="text-sm text-gray-500 ml-2 mb-1">g/hari</span>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                Target: > 2.5 g/hari
            </div>
        </div>

        <div class="bg-blue-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-2">
                <h4 class="text-sm font-medium text-gray-700">FCR (Feed Conversion Ratio)</h4>
                <span class="text-xs px-2 py-1 rounded-full
                    {{ $statistics['performance']['fcr'] <= 1.5 ? 'bg-green-100 text-green-800' :
                       ($statistics['performance']['fcr'] <= 1.8 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                    {{ $statistics['performance']['fcr'] <= 1.5 ? 'Efisien' :
                       ($statistics['performance']['fcr'] <= 1.8 ? 'Normal' : 'Tidak Efisien') }}
                </span>
            </div>
            <div class="flex items-end">
                <span class="text-3xl font-bold text-blue-700">{{ number_format($statistics['performance']['fcr'], 2) }}</span>
                <span class="text-sm text-gray-500 ml-2 mb-1">rasio</span>
            </div>
            <div class="mt-2 text-xs text-gray-500">
                Target: < 1.5 (semakin rendah semakin baik)
            </div>
        </div>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Penjualan Bulanan</h3>
        <div class="chart-container">
            <canvas id="monthlySalesChart"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribusi Jenis Kolam</h3>
        <div class="chart-container">
            <canvas id="pondTypesChart"></canvas>
        </div>
    </div>
</div>

<!-- Ponds List -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Daftar Kolam</h3>
        <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            <i class="fas fa-plus mr-1"></i> Tambah Kolam
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Kolam</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Volume</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Aktif</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kualitas Air</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($branch->ponds as $pond)
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $pond->name }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ $pond->type }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ $pond->volume }} m³</td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ $pond->fishBatches->where('is_active', true)->count() }}</td>
                    <td class="py-3 px-4 text-sm">
                        @php
                            $waterQuality = $pond->waterQualities->last();
                            $qualityStatus = 'Baik';
                            $qualityClass = 'bg-green-100 text-green
                                                        $qualityStatus = 'Baik';
                            $qualityClass = 'bg-green-100 text-green-800';

                            if ($waterQuality) {
                                if ($waterQuality->ph < 6.5 || $waterQuality->ph > 8.5 ||
                                    $waterQuality->temperature < 22 || $waterQuality->temperature > 32 ||
                                    $waterQuality->dissolved_oxygen < 4) {
                                    $qualityStatus = 'Perhatian';
                                    $qualityClass = 'bg-yellow-100 text-yellow-800';
                                }

                                if ($waterQuality->ph < 6.0 || $waterQuality->ph > 9.0 ||
                                    $waterQuality->temperature < 20 || $waterQuality->temperature > 35 ||
                                    $waterQuality->dissolved_oxygen < 3) {
                                    $qualityStatus = 'Kritis';
                                    $qualityClass = 'bg-red-100 text-red-800';
                                }
                            }
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $qualityClass }}">{{ $qualityStatus }}</span>
                    </td>
                    <td class="py-3 px-4 text-sm">
                        @if($pond->is_active ?? true)
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Non-Aktif</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-sm">
                        <div class="flex space-x-2">
                            <a href="#" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="#" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Users List -->
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800">Pengguna Cabang</h3>
        <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
            <i class="fas fa-plus mr-1"></i> Tambah Pengguna
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peran</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($branch->users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $user->name }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ $user->email }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ $user->role ?? 'Operator' }}</td>
                    <td class="py-3 px-4 text-sm">
                        @if($user->is_active ?? true)
                            <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Non-Aktif</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-sm">
                        <div class="flex space-x-2">
                            <a href="#" class="text-blue-600 hover:text-blue-900" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="#" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Monthly Sales Chart
    const salesCtx = document.getElementById('monthlySalesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($monthlyData, 'month')) !!},
            datasets: [{
                label: 'Penjualan (Rp)',
                data: {!! json_encode(array_column($monthlyData, 'sales')) !!},
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });

    // Pond Types Chart
    const pondTypesCtx = document.getElementById('pondTypesChart').getContext('2d');
    const pondTypesChart = new Chart(pondTypesCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($pondTypes)) !!},
            datasets: [{
                data: {!! json_encode(array_values($pondTypes)) !!},
                backgroundColor: [
                    'rgba(59, 130, 246, 0.7)',
                    'rgba(16, 185, 129, 0.7)',
                    'rgba(245, 158, 11, 0.7)',
                    'rgba(99, 102, 241, 0.7)',
                    'rgba(236, 72, 153, 0.7)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush
