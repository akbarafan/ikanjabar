@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard Admin')

@push('styles')
<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes slideInLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {
            transform: translateY(0);
        }
        40% {
            transform: translateY(-10px);
        }
        60% {
            transform: translateY(-5px);
        }
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
        100% {
            transform: scale(1);
        }
    }

    @keyframes rotate {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
    }

    .animate-slide-in-left {
        animation: slideInLeft 0.6s ease-out forwards;
    }

    .animate-slide-in-right {
        animation: slideInRight 0.6s ease-out forwards;
    }

    .animate-scale-in {
        animation: scaleIn 0.6s ease-out forwards;
    }

    .card-hover {
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .card-hover:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .icon-bounce:hover {
        animation: bounce 1s;
    }

    .icon-pulse:hover {
        animation: pulse 1s infinite;
    }

    .icon-rotate:hover {
        animation: rotate 1s linear;
    }

    .number-counter {
        transition: all 0.3s ease;
    }

    .card-glow {
        position: relative;
        overflow: hidden;
    }

    .card-glow::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transform: rotate(45deg);
        transition: all 0.6s;
        opacity: 0;
    }

    .card-glow:hover::before {
        animation: shimmer 1.5s ease-in-out;
        opacity: 1;
    }

    @keyframes shimmer {
        0% {
            transform: translateX(-100%) translateY(-100%) rotate(45deg);
        }
        100% {
            transform: translateX(100%) translateY(100%) rotate(45deg);
        }
    }

    .stagger-1 { animation-delay: 0.1s; }
    .stagger-2 { animation-delay: 0.2s; }
    .stagger-3 { animation-delay: 0.3s; }
    .stagger-4 { animation-delay: 0.4s; }
    .stagger-5 { animation-delay: 0.5s; }

    /* Style untuk ranking badges */
    .rank-badge {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
        color: white;
    }

    .rank-1 { background: linear-gradient(135deg, #FFD700, #FFA500); }
    .rank-2 { background: linear-gradient(135deg, #C0C0C0, #A9A9A9); }
    .rank-3 { background: linear-gradient(135deg, #CD7F32, #B8860B); }
    .rank-other { background: linear-gradient(135deg, #6B7280, #4B5563); }

    /* Responsive Chart containers */
    .bar-chart-container {
        position: relative;
        height: 350px;
        width: 100%;
        padding: 15px;
    }

    .bar-chart-container canvas {
        max-height: 100% !important;
        max-width: 100% !important;
    }

    .doughnut-chart-container {
        position: relative;
        height: 350px;
        width: 100%;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .doughnut-chart-container canvas {
        max-height: 100% !important;
        max-width: 100% !important;
    }

    /* Mobile Responsive Styles */
    @media (max-width: 640px) {
        .bar-chart-container,
        .doughnut-chart-container {
            height: 280px;
            padding: 10px;
        }
        
        .rank-badge {
            width: 28px;
            height: 28px;
            font-size: 12px;
        }
        
        .number-counter {
            font-size: 1.5rem !important;
        }
        
        .card-hover {
            padding: 1rem !important;
        }
        
        .icon-bounce,
        .icon-pulse,
        .icon-rotate {
            padding: 0.5rem !important;
        }
    }

    @media (max-width: 768px) {
        .bar-chart-container,
        .doughnut-chart-container {
            height: 300px;
            padding: 12px;
        }
    }

    /* Tablet specific adjustments */
    @media (min-width: 641px) and (max-width: 1024px) {
        .bar-chart-container,
        .doughnut-chart-container {
            height: 320px;
        }
    }

    /* Large screen optimizations */
    @media (min-width: 1280px) {
        .bar-chart-container,
        .doughnut-chart-container {
            height: 400px;
        }
    }
</style>
@endpush

@section('content')
<!-- Stats Cards - Fully Responsive -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 lg:gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-4 lg:p-6 card-hover card-glow animate-fade-in-up stagger-1">
        <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm text-gray-500 mb-1 truncate">Total Cabang</p>
                <h3 class="text-xl sm:text-2xl font-bold text-gray-800 number-counter">{{ $totalBranches }}</h3>
            </div>
            <div class="bg-blue-100 p-2 sm:p-3 rounded-full icon-bounce flex-shrink-0 ml-2">
                <i class="fas fa-building text-blue-600 text-sm sm:text-base"></i>
            </div>
        </div>
        <div class="mt-3 sm:mt-4 text-xs sm:text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ $totalBranches }}</span> cabang aktif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 lg:p-6 card-hover card-glow animate-fade-in-up stagger-2">
        <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm text-gray-500 mb-1 truncate">Total Kolam</p>
                <h3 class="text-xl sm:text-2xl font-bold text-gray-800 number-counter">{{ $totalPonds }}</h3>
            </div>
            <div class="bg-purple-100 p-2 sm:p-3 rounded-full icon-pulse flex-shrink-0 ml-2">
                <i class="fas fa-water text-purple-600 text-sm sm:text-base"></i>
            </div>
        </div>
        <div class="mt-3 sm:mt-4 text-xs sm:text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ $totalPonds }}</span> kolam terdaftar
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 lg:p-6 card-hover card-glow animate-fade-in-up stagger-3">
        <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm text-gray-500 mb-1 truncate">Total Pengguna</p>
                <h3 class="text-xl sm:text-2xl font-bold text-gray-800 number-counter">{{ $totalUsers }}</h3>
            </div>
            <div class="bg-green-100 p-2 sm:p-3 rounded-full icon-bounce flex-shrink-0 ml-2">
                <i class="fas fa-users text-green-600 text-sm sm:text-base"></i>
            </div>
        </div>
        <div class="mt-3 sm:mt-4 text-xs sm:text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ $totalUsers }}</span> pengguna aktif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 lg:p-6 card-hover card-glow animate-fade-in-up stagger-4">
        <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm text-gray-500 mb-1 truncate">Batch Aktif</p>
                <h3 class="text-xl sm:text-2xl font-bold text-gray-800 number-counter">{{ $activeBatches }}</h3>
            </div>
            <div class="bg-green-100 p-2 sm:p-3 rounded-full icon-pulse flex-shrink-0 ml-2">
                <i class="fas fa-fish text-green-600 text-sm sm:text-base"></i>
            </div>
        </div>
        <div class="mt-3 sm:mt-4 text-xs sm:text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ $activeBatches }}</span> batch aktif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-4 lg:p-6 card-hover card-glow animate-fade-in-up stagger-5 sm:col-span-2 lg:col-span-1">
        <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm text-gray-500 mb-1 truncate">Total Penjualan</p>
                <h3 class="text-lg sm:text-xl lg:text-2xl font-bold text-gray-800 number-counter">Rp {{ number_format($totalSales, 0, ',', '.') }}</h3>
            </div>
            <div class="bg-yellow-100 p-2 sm:p-3 rounded-full icon-rotate flex-shrink-0 ml-2">
                <i class="fas fa-money-bill-wave text-yellow-600 text-sm sm:text-base"></i>
            </div>
        </div>
        <div class="mt-3 sm:mt-4 text-xs sm:text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ number_format($salesGrowth, 1) }}%</span> dari bulan lalu
        </div>
    </div>
</div>

<!-- Charts Section - Responsive -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-4 lg:gap-6 mb-6">
    <!-- Chart Omset Per Cabang -->
    <div class="bg-white rounded-lg shadow-md p-4 lg:p-6 card-hover animate-slide-in-left">
        <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
            <span class="hidden sm:inline">Omset Per Cabang</span>
            <span class="sm:hidden">Omset Cabang</span>
        </h3>
        <div class="bar-chart-container">
            <canvas id="branchRevenueChart"></canvas>
        </div>
    </div>

    <!-- Tabel Omset Tertinggi -->
    <div class="bg-white rounded-lg shadow-md p-4 lg:p-6 card-hover animate-slide-in-right">
        <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-trophy text-yellow-500 mr-2"></i>
            <span class="hidden sm:inline">Omset Tertinggi</span>
            <span class="sm:hidden">Top Omset</span>
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 sm:px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                        <th class="px-2 sm:px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <span class="hidden sm:inline">Nama Cabang</span>
                            <span class="sm:hidden">Cabang</span>
                        </th>
                        <th class="px-2 sm:px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <span class="hidden sm:inline">Penghasilan</span>
                            <span class="sm:hidden">Omset</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @php
                        $topRevenue = [
                            ['name' => 'Cabang Jakarta Pusat', 'revenue' => 125000000],
                            ['name' => 'Cabang Bandung', 'revenue' => 98500000],
                            ['name' => 'Cabang Surabaya', 'revenue' => 87200000],
                            ['name' => 'Cabang Medan', 'revenue' => 76800000],
                            ['name' => 'Cabang Semarang', 'revenue' => 65400000],
                        ];
                    @endphp
                    
                    @foreach($topRevenue as $index => $branch)
                    <tr class="hover:bg-gray-50 transition-all duration-300 transform hover:scale-[1.01]">
                        <td class="px-2 sm:px-3 py-2 whitespace-nowrap">
                            <div class="rank-badge {{ $index == 0 ? 'rank-1' : ($index == 1 ? 'rank-2' : ($index == 2 ? 'rank-3' : 'rank-other')) }}">
                                {{ $index + 1 }}
                            </div>
                        </td>
                        <td class="px-2 sm:px-3 py-2 whitespace-nowrap">
                            <div class="flex items-center">
                                @if($index == 0)
                                    <i class="fas fa-crown text-yellow-500 mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                                @elseif($index == 1)
                                    <i class="fas fa-medal text-gray-400 mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                                @elseif($index == 2)
                                    <i class="fas fa-award text-yellow-600 mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                                @else
                                    <i class="fas fa-building text-gray-400 mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                                @endif
                                <span class="text-xs sm:text-sm font-medium text-gray-900 truncate max-w-[120px] sm:max-w-none">
                                    <span class="hidden sm:inline">{{ $branch['name'] }}</span>
                                    <span class="sm:hidden">{{ Str::limit(str_replace('Cabang ', '', $branch['name']), 10) }}</span>
                                </span>
                            </div>
                        </td>
                        <td class="px-2 sm:px-3 py-2 whitespace-nowrap">
                            <span class="text-xs sm:text-sm font-bold {{ $index == 0 ? 'text-yellow-600' : ($index == 1 ? 'text-gray-600' : ($index == 2 ? 'text-yellow-700' : 'text-gray-500')) }}">
                                <span class="hidden sm:inline">Rp {{ number_format($branch['revenue'], 0, ',', '.') }}</span>
                                <span class="sm:hidden">{{ number_format($branch['revenue']/1000000, 0) }}M</span>
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Branch List Table - Responsive -->
<div class="bg-white rounded-lg shadow-md p-4 lg:p-6 mb-6 card-hover animate-scale-in">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 space-y-4 sm:space-y-0">
        <h3 class="text-base lg:text-lg font-semibold text-gray-800">Daftar Cabang</h3>
        <a href="{{ route('admin.branches.create') }}" class="bg-blue-600 text-white px-3 sm:px-4 py-2 rounded-md text-xs sm:text-sm font-medium hover:bg-blue-700 transition-all duration-300 transform hover:scale-105 hover:shadow-lg text-center">
            <i class="fas fa-plus mr-1 sm:mr-2"></i> 
            <span class="hidden sm:inline">Tambah Cabang</span>
            <span class="sm:hidden">Tambah</span>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-2 sm:py-3 px-2 sm:px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <span class="hidden sm:inline">Nama Cabang</span>
                        <span class="sm:hidden">Cabang</span>
                    </th>
                    <th class="py-2 sm:py-3 px-2 sm:px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Lokasi</th>
                    <th class="py-2 sm:py-3 px-2 sm:px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <span class="hidden sm:inline">Jumlah Kolam</span>
                        <span class="sm:hidden">Kolam</span>
                    </th>
                    <th class="py-2 sm:py-3 px-2 sm:px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                        <span class="hidden xl:inline">Batch Aktif</span>
                        <span class="xl:hidden">Batch</span>
                    </th>
                    <th class="py-2 sm:py-3 px-2 sm:px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">User</th>
                    <th class="py-2 sm:py-3 px-2 sm:px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($branches as $branch)
                <tr class="hover:bg-gray-50 transition-all duration-300 transform hover:scale-[1.01]">
                    <td class="py-2 sm:py-3 px-2 sm:px-4 text-xs sm:text-sm font-medium text-gray-900">
                        <div class="truncate max-w-[120px] sm:max-w-none">{{ $branch->name }}</div>
                    </td>
                    <td class="py-2 sm:py-3 px-2 sm:px-4 text-xs sm:text-sm text-gray-500 hidden md:table-cell">
                        <div class="truncate max-w-[150px]">{{ Str::limit($branch->location, 30) }}</div>
                    </td>
                    <td class="py-2 sm:py-3 px-2 sm:px-4 text-xs sm:text-sm text-gray-500 text-center">{{ $branch->ponds_count }}</td>
                    <td class="py-2 sm:py-3 px-2 sm:px-4 text-xs sm:text-sm text-gray-500 text-center hidden lg:table-cell">{{ $branch->active_batches_count }}</td>
                    <td class="py-2 sm:py-3 px-2 sm:px-4 text-xs sm:text-sm text-gray-500 text-center hidden lg:table-cell">{{ $branch->users_count }}</td>
                    <td class="py-2 sm:py-3 px-2 sm:px-4 text-xs sm:text-sm">
                        <div class="flex space-x-1 sm:space-x-2">
                            <a href="{{ route('admin.branches.show', $branch) }}" class="text-blue-600 hover:text-blue-900">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.branches.edit', $branch) }}" class="text-yellow-600 hover:text-yellow-900 transition-all duration-300 transform hover:scale-110 p-1" title="Edit">
                                <i class="fas fa-edit text-xs sm:text-sm"></i>
                            </a>
                            <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus cabang ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 transition-all duration-300 transform hover:scale-110 p-1" title="Hapus">
                                    <i class="fas fa-trash text-xs sm:text-sm"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $branches->links() }}
    </div>
</div>

<!-- Charts Section 2 - Responsive -->
    <!-- Distribusi Jenis Kolam -->
    <div class="bg-white rounded-lg shadow-md p-4 lg:p-6 card-hover animate-slide-in-left gap-6 mb-6">
        <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-chart-pie text-purple-500 mr-2"></i>
            <span class="hidden sm:inline">Distribusi Jenis Kolam</span>
            <span class="sm:hidden">Jenis Kolam</span>
        </h3>
        <div class="doughnut-chart-container">
            <canvas id="pondTypesChart"></canvas>
        </div>

    <!-- Kualitas Air Rata-rata -->
    {{-- <div class="bg-white rounded-lg shadow-md p-4 lg:p-6 card-hover animate-slide-in-right">
        <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-tint text-blue-500 mr-2"></i>
            <span class="hidden sm:inline">Kualitas Air Rata-rata</span>
            <span class="sm:hidden">Kualitas Air</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <div class="bg-blue-50 rounded-lg p-3 sm:p-4 card-hover transition-all duration-300 transform hover:scale-105">
                <p class="text-xs sm:text-sm text-gray-600">pH Air</p>
                <div class="flex items-end">
                    <span class="text-2xl sm:text-3xl font-bold text-blue-700 number-counter">{{ number_format($avgWaterQuality['avg_ph'], 1) }}</span>
                    <span class="text-xs sm:text-sm text-gray-500 ml-2 mb-1">pH</span>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    <span class="hidden sm:inline">Rentang ideal: 7.0 - 8.0 pH</span>
                    <span class="sm:hidden">Ideal: 7.0-8.0</span>
                </div>
            </div>

            <div class="bg-blue-50 rounded-lg p-3 sm:p-4 card-hover transition-all duration-300 transform hover:scale-105">
                <p class="text-xs sm:text-sm text-gray-600">Suhu Air</p>
                <div class="flex items-end">
                    <span class="text-2xl sm:text-3xl font-bold text-blue-700 number-counter">{{ number_format($avgWaterQuality['avg_temperature'], 1) }}</span>
                    <span class="text-xs sm:text-sm text-gray-500 ml-2 mb-1">°C</span>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    <span class="hidden sm:inline">Rentang ideal: 25 - 30 °C</span>
                    <span class="sm:hidden">Ideal: 25-30°C</span>
                </div>
            </div>

            <div class="bg-blue-50 rounded-lg p-3 sm:p-4 card-hover transition-all duration-300 transform hover:scale-105">
                <p class="text-xs sm:text-sm text-gray-600">
                    <span class="hidden sm:inline">Oksigen Terlarut</span>
                    <span class="sm:hidden">Oksigen</span>
                </p>
                <div class="flex items-end">
                    <span class="text-2xl sm:text-3xl font-bold text-blue-700 number-counter">{{ number_format($avgWaterQuality['avg_do'], 1) }}</span>
                    <span class="text-xs sm:text-sm text-gray-500 ml-2 mb-1">mg/L</span>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    <span class="hidden sm:inline">Rentang ideal: > 5 mg/L</span>
                    <span class="sm:hidden">Ideal: >5 mg/L</span>
                </div>
            </div>

            <div class="bg-blue-50 rounded-lg p-3 sm:p-4 card-hover transition-all duration-300 transform hover:scale-105">
                <p class="text-xs sm:text-sm text-gray-600">Ammonia</p>
                <div class="flex items-end">
                    <span class="text-2xl sm:text-3xl font-bold text-blue-700 number-counter">{{ number_format($avgWaterQuality['avg_ammonia'], 2) }}</span>
                    <span class="text-xs sm:text-sm text-gray-500 ml-2 mb-1">mg/L</span>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    <span class="hidden sm:inline">Rentang ideal: < 0.5 mg/L</span>
                    <span class="sm:hidden">Ideal: <0.5 mg/L</span>
                </div>
            </div>
        </div>
    </div> --}}
</div>

<!-- Statistik Cabang - Responsive -->
<div class="bg-white rounded-lg shadow-md p-4 lg:p-6 mb-6 card-hover animate-slide-in-left">
    <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-4">
        <i class="fas fa-chart-line text-green-500 mr-2"></i>
        <span class="hidden sm:inline">Statistik Cabang</span>
        <span class="sm:hidden">Statistik</span>
    </h3>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-2 sm:px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cabang</th>
                    <th class="px-2 sm:px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kolam</th>
                    <th class="px-2 sm:px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                        <span class="hidden lg:inline">Batch Aktif</span>
                        <span class="lg:hidden">Batch</span>
                    </th>
                    <th class="px-2 sm:px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                        <span class="hidden lg:inline">Stok Ikan</span>
                        <span class="lg:hidden">Stok</span>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($branches->take(5) as $branch)
                <tr class="hover:bg-gray-50 transition-all duration-300 transform hover:scale-[1.01]">
                    <td class="px-2 sm:px-3 py-2 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900">
                        <div class="truncate max-w-[120px] sm:max-w-none">{{ $branch->name }}</div>
                    </td>
                    <td class="px-2 sm:px-3 py-2 whitespace-nowrap text-xs sm:text-sm text-gray-500 text-center">
                        {{ $branch->statistics['total_ponds'] ?? 0 }}
                    </td>
                    <td class="px-2 sm:px-3 py-2 whitespace-nowrap text-xs sm:text-sm text-gray-500 text-center hidden sm:table-cell">
                        {{ $branch->statistics['total_active_batches'] ?? 0 }}
                    </td>
                    <td class="px-2 sm:px-3 py-2 whitespace-nowrap text-xs sm:text-sm text-gray-500 text-center hidden md:table-cell">
                        <span class="hidden lg:inline">{{ number_format($branch->statistics['total_fish_stock'] ?? 0, 0, ',', '.') }}</span>
                        <span class="lg:hidden">{{ number_format(($branch->statistics['total_fish_stock'] ?? 0)/1000, 0) }}K</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Aksi Cepat - Responsive -->
<div class="bg-white rounded-lg shadow-md p-4 lg:p-6 mb-6 card-hover animate-scale-in">
    <h3 class="text-base lg:text-lg font-semibold text-gray-800 mb-4">
        <i class="fas fa-bolt text-yellow-500 mr-2"></i>
        <span class="hidden sm:inline">Aksi Cepat</span>
        <span class="sm:hidden">Menu Cepat</span>
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <a href="{{ route('admin.branches.create') }}" class="bg-blue-50 rounded-lg p-3 sm:p-4 hover:bg-blue-100 transition-all duration-300 transform hover:scale-105 hover:shadow-lg card-glow">
            <div class="flex items-center">
                <div class="bg-blue-100 p-2 sm:p-3 rounded-full mr-2 sm:mr-3 icon-bounce flex-shrink-0">
                    <i class="fas fa-building text-blue-600 text-sm sm:text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="text-xs sm:text-sm font-medium text-gray-700 truncate">
                        <span class="hidden sm:inline">Tambah Cabang</span>
                        <span class="sm:hidden">+ Cabang</span>
                    </h4>
                    <p class="text-xs text-gray-500 mt-1 hidden sm:block">Buat cabang baru</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.ponds.create') }}" class="bg-purple-50 rounded-lg p-3 sm:p-4 hover:bg-purple-100 transition-all duration-300 transform hover:scale-105 hover:shadow-lg card-glow">
            <div class="flex items-center">
                <div class="bg-purple-100 p-2 sm:p-3 rounded-full mr-2 sm:mr-3 icon-pulse flex-shrink-0">
                    <i class="fas fa-water text-purple-600 text-sm sm:text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="text-xs sm:text-sm font-medium text-gray-700 truncate">
                        <span class="hidden sm:inline">Tambah Kolam</span>
                        <span class="sm:hidden">+ Kolam</span>
                    </h4>
                    <p class="text-xs text-gray-500 mt-1 hidden sm:block">Buat kolam baru</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.fish-batches.create') }}" class="bg-green-50 rounded-lg p-3 sm:p-4 hover:bg-green-100 transition-all duration-300 transform hover:scale-105 hover:shadow-lg card-glow">
            <div class="flex items-center">
                <div class="bg-green-100 p-2 sm:p-3 rounded-full mr-2 sm:mr-3 icon-bounce flex-shrink-0">
                    <i class="fas fa-fish text-green-600 text-sm sm:text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="text-xs sm:text-sm font-medium text-gray-700 truncate">
                        <span class="hidden sm:inline">Tambah Batch</span>
                        <span class="sm:hidden">+ Batch</span>
                    </h4>
                    <p class="text-xs text-gray-500 mt-1 hidden sm:block">Buat batch ikan baru</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.users.create') }}" class="bg-yellow-50 rounded-lg p-3 sm:p-4 hover:bg-yellow-100 transition-all duration-300 transform hover:scale-105 hover:shadow-lg card-glow">
            <div class="flex items-center">
                <div class="bg-yellow-100 p-2 sm:p-3 rounded-full mr-2 sm:mr-3 icon-rotate flex-shrink-0">
                    <i class="fas fa-user-plus text-yellow-600 text-sm sm:text-base"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <h4 class="text-xs sm:text-sm font-medium text-gray-700 truncate">
                        <span class="hidden sm:inline">Tambah Pengguna</span>
                        <span class="sm:hidden">+ User</span>
                    </h4>
                    <p class="text-xs text-gray-500 mt-1 hidden sm:block">Buat pengguna baru</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Counter Animation
    function animateCounter(element, target) {
        let current = 0;
        const increment = target / 100;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current).toLocaleString('id-ID');
        }, 20);
    }

    // Initialize counter animations when page loads
    document.addEventListener('DOMContentLoaded', function() {
        const counters = document.querySelectorAll('.number-counter');
        counters.forEach(counter => {
            const target = parseInt(counter.textContent.replace(/[^\d]/g, ''));
            if (!isNaN(target)) {
                counter.textContent = '0';
                setTimeout(() => {
                    animateCounter(counter, target);
                }, 500);
            }
        });
    });

    // Data untuk Chart Omset Per Cabang
    const branchRevenueData = {
        labels: [
            'Jakarta Pusat',
            'Bandung', 
            'Surabaya',
            'Medan',
            'Semarang',
            'Yogyakarta',
            'Malang'
        ],
        revenues: [125000000, 98500000, 87200000, 76800000, 65400000, 58900000, 45200000]
    };

    // Responsive Chart Configuration
    function getResponsiveChartOptions() {
        const isMobile = window.innerWidth < 640;
        const isTablet = window.innerWidth >= 640 && window.innerWidth < 1024;
        
        return {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: isMobile ? 10 : 20,
                    bottom: isMobile ? 5 : 10,
                    left: isMobile ? 5 : 10,
                    right: isMobile ? 5 : 10
                }
            },
            plugins: {
                legend: {
                    display: !isMobile,
                    position: isMobile ? 'bottom' : 'top'
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: false,
                    titleFont: {
                        size: isMobile ? 12 : 14
                    },
                    bodyFont: {
                        size: isMobile ? 11 : 13
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#6B7280',
                        font: {
                            size: isMobile ? 9 : 11,
                            weight: '500'
                        },
                        maxRotation: isMobile ? 45 : 0,
                        minRotation: 0
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(107, 114, 128, 0.1)',
                        drawBorder: false
                    },
                    ticks: {
                        color: '#6B7280',
                        font: {
                            size: isMobile ? 9 : 11
                        },
                        callback: function(value) {
                            if (isMobile) {
                                if (value >= 1000000) {
                                    return (value / 1000000).toFixed(0) + 'M';
                                }
                                return value.toLocaleString('id-ID');
                            } else {
                                if (value >= 1000000000) {
                                    return 'Rp ' + (value / 1000000000).toFixed(1) + 'B';
                                } else if (value >= 1000000) {
                                    return 'Rp ' + (value / 1000000).toFixed(0) + 'M';
                                }
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        };
    }

    // Chart Omset Per Cabang (Bar Chart) - Responsive
    const branchRevenueCtx = document.getElementById('branchRevenueChart').getContext('2d');
    const branchRevenueChart = new Chart(branchRevenueCtx, {
        type: 'bar',
        data: {
            labels: branchRevenueData.labels,
            datasets: [{
                label: 'Omset (Rp)',
                data: branchRevenueData.revenues,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',   // Blue
                    'rgba(16, 185, 129, 0.8)',   // Green
                    'rgba(245, 158, 11, 0.8)',   // Yellow
                    'rgba(99, 102, 241, 0.8)',   // Indigo
                    'rgba(236, 72, 153, 0.8)',   // Pink
                    'rgba(139, 69, 19, 0.8)',    // Brown
                    'rgba(75, 85, 99, 0.8)'      // Gray
                ],
                borderColor: [
                    'rgba(59, 130, 246, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(99, 102, 241, 1)',
                    'rgba(236, 72, 153, 1)',
                    'rgba(139, 69, 19, 1)',
                    'rgba(75, 85, 99, 1)'
                ],
                borderWidth: window.innerWidth < 640 ? 1 : 2,
                borderRadius: window.innerWidth < 640 ? 4 : 8,
                borderSkipped: false
            }]
        },
        options: getResponsiveChartOptions()
    });

    // Chart Distribusi Jenis Kolam (Responsive)
    const pondTypesCtx = document.getElementById('pondTypesChart').getContext('2d');
    const pondTypesChart = new Chart(pondTypesCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($pondTypes)) !!},
            datasets: [{
                data: {!! json_encode(array_values($pondTypes)) !!},
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',   // Blue
                    'rgba(16, 185, 129, 0.8)',   // Green  
                    'rgba(245, 158, 11, 0.8)',   // Yellow
                    'rgba(99, 102, 241, 0.8)',   // Indigo
                    'rgba(236, 72, 153, 0.8)',   // Pink
                    'rgba(139, 69, 19, 0.8)',    // Brown
                    'rgba(75, 85, 99, 0.8)'      // Gray
                ],
                borderWidth: window.innerWidth < 640 ? 2 : 3,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: window.innerWidth < 640 ? 10 : 20,
                    bottom: window.innerWidth < 640 ? 30 : 50,
                    left: window.innerWidth < 640 ? 10 : 20,
                    right: window.innerWidth < 640 ? 10 : 20
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    align: 'center',
                    labels: {
                        padding: window.innerWidth < 640 ? 10 : 20,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: {
                            size: window.innerWidth < 640 ? 10 : 12,
                            weight: '500'
                        },
                        boxWidth: window.innerWidth < 640 ? 8 : 12,
                        boxHeight: window.innerWidth < 640 ? 8 : 12,
                        color: '#374151',
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                const dataset = data.datasets[0];
                                const total = dataset.data.reduce((a, b) => a + b, 0);
                                
                                return data.labels.map((label, i) => {
                                    const value = dataset.data[i];
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    
                                    return {
                                        text: window.innerWidth < 640 ? 
                                            `${label.length > 8 ? label.substring(0, 8) + '...' : label} (${percentage}%)` :
                                            `${label} (${percentage}%)`,
                                        fillStyle: dataset.backgroundColor[i],
                                        strokeStyle: dataset.borderColor,
                                        lineWidth: dataset.borderWidth,
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
                    titleColor: '#fff',
                    bodyColor: '#fff',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    displayColors: true,
                    titleFont: {
                        size: window.innerWidth < 640 ? 12 : 14
                    },
                    bodyFont: {
                        size: window.innerWidth < 640 ? 11 : 13
                    }
                }
            },
            cutout: window.innerWidth < 640 ? '50%' : '60%',
            radius: window.innerWidth < 640 ? '75%' : '85%'
        }
    });

    // Responsive resize handler
    function handleResize() {
        // Update chart options based on new screen size
        const newBarOptions = getResponsiveChartOptions();
        branchRevenueChart.options = { ...branchRevenueChart.options, ...newBarOptions };
        
        // Update doughnut chart responsive settings
        const isMobile = window.innerWidth < 640;
        pondTypesChart.options.cutout = isMobile ? '50%' : '60%';
        pondTypesChart.options.radius = isMobile ? '75%' : '85%';
        pondTypesChart.options.layout.padding = {
            top: isMobile ? 10 : 20,
            bottom: isMobile ? 30 : 50,
            left: isMobile ? 10 : 20,
            right: isMobile ? 10 : 20
        };
        
        // Update dataset properties
        branchRevenueChart.data.datasets[0].borderWidth = isMobile ? 1 : 2;
        branchRevenueChart.data.datasets[0].borderRadius = isMobile ? 4 : 8;
        pondTypesChart.data.datasets[0].borderWidth = isMobile ? 2 : 3;
        
        // Resize charts
        branchRevenueChart.resize();
        pondTypesChart.resize();
        branchRevenueChart.update();
        pondTypesChart.update();
    }

    // Debounced resize handler
    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(handleResize, 250);
    });

    // Touch and mobile interaction improvements
    if ('ontouchstart' in window) {
        // Add touch-friendly interactions for mobile
        document.querySelectorAll('.card-hover').forEach(card => {
            card.addEventListener('touchstart', function() {
                this.style.transform = 'translateY(-2px)';
            });
            
            card.addEventListener('touchend', function() {
                this.style.transform = '';
            });
        });
        
        // Improve chart touch interactions
        branchRevenueChart.options.interaction = {
            intersect: false,
            mode: 'nearest'
        };
        
        pondTypesChart.options.interaction = {
            intersect: false,
            mode: 'nearest'
        };
    }

    // Intersection Observer for mobile performance
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const chartObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const chart = entry.target.querySelector('canvas');
                if (chart && chart.id === 'branchRevenueChart') {
                    branchRevenueChart.update('active');
                }
                if (chart && chart.id === 'pondTypesChart') {
                    pondTypesChart.update('active');
                }
            }
        });
    }, observerOptions);

    // Observe chart containers
    const barChartContainer = document.querySelector('.bar-chart-container');
    const doughnutChartContainer = document.querySelector('.doughnut-chart-container');
    
    if (barChartContainer) {
        chartObserver.observe(barChartContainer);
    }
    if (doughnutChartContainer) {
        chartObserver.observe(doughnutChartContainer);
    }

    // Mobile-specific optimizations
    if (window.innerWidth < 768) {
        // Reduce animation duration on mobile for better performance
        branchRevenueChart.options.animation.duration = 1000;
        pondTypesChart.options.animation.duration = 1000;
        
        // Simplify hover effects on mobile
        branchRevenueChart.options.hover = {
            animationDuration: 200
        };
        pondTypesChart.options.hover = {
            animationDuration: 200
        };
    }

    // Add loading states for better mobile UX
    function showMobileLoading(containerId) {
        const container = document.querySelector(`.${containerId}`);
        if (container) {
            container.innerHTML = `
                <div class="flex items-center justify-center h-48 sm:h-64">
                    <div class="text-center">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mb-2"></div>
                        <p class="text-gray-500 text-sm">Loading...</p>
                    </div>
                </div>
            `;
        }
    }

    // Error handling with mobile-friendly messages
    function handleMobileChartError(error, chartName) {
        console.error(`Error in ${chartName}:`, error);
        
        const errorMessage = document.createElement('div');
        errorMessage.className = 'fixed top-4 left-4 right-4 sm:top-5 sm:right-5 sm:left-auto sm:w-80 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg shadow-lg z-50';
        errorMessage.innerHTML = `
            <div class="flex items-start">
                <i class="fas fa-exclamation-triangle text-red-500 mr-2 mt-0.5 flex-shrink-0"></i>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium">Chart Error</p>
                    <p class="text-xs mt-1">Failed to load ${chartName}. Please refresh.</p>
                </div>
                <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-red-500 hover:text-red-700 flex-shrink-0">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        `;
        
        document.body.appendChild(errorMessage);
        
        setTimeout(() => {
            if (document.body.contains(errorMessage)) {
                errorMessage.remove();
            }
        }, 5000);
    }

    // Initialize responsive features
    try {
        // Set initial responsive state
        handleResize();
        console.log('Responsive dashboard initialized successfully');
    } catch (error) {
        handleMobileChartError(error, 'Dashboard Charts');
    }

    // Add swipe gestures for mobile chart navigation (optional)
    if ('ontouchstart' in window) {
        let startX, startY, distX, distY;
        const threshold = 100;
        
        document.addEventListener('touchstart', function(e) {
            const touch = e.touches[0];
            startX = touch.clientX;
            startY = touch.clientY;
        });
        
        document.addEventListener('touchmove', function(e) {
            if (!startX || !startY) return;
            
            const touch = e.touches[0];
            distX = touch.clientX - startX;
            distY = touch.clientY - startY;
        });
        
        document.addEventListener('touchend', function(e) {
            if (!startX || !startY) return;
            
            if (Math.abs(distX) > Math.abs(distY) && Math.abs(distX) > threshold) {
                // Horizontal swipe detected - could be used for chart navigation
                if (distX > 0) {
                    // Swipe right
                } else {
                    // Swipe left
                }
            }
            
            startX = startY = distX = distY = null;
        });
    }

    // Performance monitoring for mobile
    if ('performance' in window) {
        window.addEventListener('load', function() {
            setTimeout(() => {
                const perfData = performance.getEntriesByType('navigation')[0];
                if (perfData && perfData.loadEventEnd - perfData.loadEventStart > 3000) {
                    console.warn('Dashboard loaded slowly, consider optimizing for mobile');
                }
            }, 1000);
        });
    }
</script>
@endpush
