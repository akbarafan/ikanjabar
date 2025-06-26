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
</style>
@endpush

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6 card-hover card-glow animate-fade-in-up stagger-1">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Cabang</p>
                <h3 class="text-2xl font-bold text-gray-800 number-counter">{{ $totalBranches }}</h3>
            </div>
            <div class="bg-blue-100 p-3 rounded-full icon-bounce">
                <i class="fas fa-building text-blue-600"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ $totalBranches }}</span> cabang aktif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 card-hover card-glow animate-fade-in-up stagger-2">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Kolam</p>
                <h3 class="text-2xl font-bold text-gray-800 number-counter">{{ $totalPonds }}</h3>
            </div>
            <div class="bg-purple-100 p-3 rounded-full icon-pulse">
                <i class="fas fa-water text-purple-600"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ $totalPonds }}</span> kolam terdaftar
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 card-hover card-glow animate-fade-in-up stagger-3">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Pengguna</p>
                <h3 class="text-2xl font-bold text-gray-800 number-counter">{{ $totalUsers }}</h3>
            </div>
            <div class="bg-green-100 p-3 rounded-full icon-bounce">
                <i class="fas fa-users text-green-600"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ $totalUsers }}</span> pengguna aktif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 card-hover card-glow animate-fade-in-up stagger-4">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Batch Aktif</p>
                <h3 class="text-2xl font-bold text-gray-800 number-counter">{{ $activeBatches }}</h3>
            </div>
            <div class="bg-green-100 p-3 rounded-full icon-pulse">
                <i class="fas fa-fish text-green-600"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ $activeBatches }}</span> batch aktif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 card-hover card-glow animate-fade-in-up stagger-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Penjualan</p>
                <h3 class="text-2xl font-bold text-gray-800 number-counter">Rp {{ number_format($totalSales, 0, ',', '.') }}</h3>
            </div>
            <div class="bg-yellow-100 p-3 rounded-full icon-rotate">
                <i class="fas fa-money-bill-wave text-yellow-600"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ number_format($salesGrowth, 1) }}%</span> dari bulan lalu
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6 card-hover animate-slide-in-left">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Penjualan Bulanan</h3>
        <div class="chart-container">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 card-hover animate-slide-in-right">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Distribusi Jenis Kolam</h3>
        <div class="chart-container">
            <canvas id="pondTypesChart"></canvas>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md p-6 mb-6 card-hover animate-scale-in">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-800">Daftar Cabang</h3>
        <a href="{{ route('admin.branches.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-all duration-300 transform hover:scale-105 hover:shadow-lg">
            <i class="fas fa-plus mr-2"></i> Tambah Cabang
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Cabang</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Kolam</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Aktif</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($branches as $branch)
                <tr class="hover:bg-gray-50 transition-all duration-300 transform hover:scale-[1.01]">
                    <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $branch->name }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ Str::limit($branch->location, 30) }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ $branch->ponds_count }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ $branch->active_batches_count }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ $branch->users_count }}</td>
                    <td class="py-3 px-4 text-sm">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.branches.show', $branch) }}" class="text-blue-600 hover:text-blue-900 transition-all duration-300 transform hover:scale-110" title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.branches.edit', $branch) }}" class="text-yellow-600 hover:text-yellow-900 transition-all duration-300 transform hover:scale-110" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus cabang ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 transition-all duration-300 transform hover:scale-110" title="Hapus">
                                    <i class="fas fa-trash"></i>
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

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6 card-hover animate-slide-in-left">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Kualitas Air Rata-rata</h3>

        <div class="grid grid-cols-2 gap-4">
            <div class="bg-blue-50 rounded-lg p-4 card-hover transition-all duration-300 transform hover:scale-105">
                <p class="text-sm text-gray-600">pH Air</p>
                <div class="flex items-end">
                    <span class="text-3xl font-bold text-blue-700 number-counter">{{ number_format($avgWaterQuality['avg_ph'], 1) }}</span>
                    <span class="text-sm text
                                        <span class="text-sm text-gray-500 ml-2 mb-1">pH</span>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    Rentang ideal: 7.0 - 8.0 pH
                </div>
            </div>

            <div class="bg-blue-50 rounded-lg p-4 card-hover transition-all duration-300 transform hover:scale-105">
                <p class="text-sm text-gray-600">Suhu Air</p>
                <div class="flex items-end">
                    <span class="text-3xl font-bold text-blue-700 number-counter">{{ number_format($avgWaterQuality['avg_temperature'], 1) }}</span>
                    <span class="text-sm text-gray-500 ml-2 mb-1">°C</span>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    Rentang ideal: 25 - 30 °C
                </div>
            </div>

            <div class="bg-blue-50 rounded-lg p-4 card-hover transition-all duration-300 transform hover:scale-105">
                <p class="text-sm text-gray-600">Oksigen Terlarut</p>
                <div class="flex items-end">
                    <span class="text-3xl font-bold text-blue-700 number-counter">{{ number_format($avgWaterQuality['avg_do'], 1) }}</span>
                    <span class="text-sm text-gray-500 ml-2 mb-1">mg/L</span>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    Rentang ideal: > 5 mg/L
                </div>
            </div>

            <div class="bg-blue-50 rounded-lg p-4 card-hover transition-all duration-300 transform hover:scale-105">
                <p class="text-sm text-gray-600">Ammonia</p>
                <div class="flex items-end">
                    <span class="text-3xl font-bold text-blue-700 number-counter">{{ number_format($avgWaterQuality['avg_ammonia'], 2) }}</span>
                    <span class="text-sm text-gray-500 ml-2 mb-1">mg/L</span>
                </div>
                <div class="mt-2 text-xs text-gray-500">
                    Rentang ideal: < 0.5 mg/L
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 card-hover animate-slide-in-right">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Statistik Cabang</h3>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cabang</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kolam</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Aktif</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Ikan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($branches->take(5) as $branch)
                    <tr class="hover:bg-gray-50 transition-all duration-300 transform hover:scale-[1.01]">
                        <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $branch->name }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                            {{ $branch->statistics['total_ponds'] ?? 0 }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                            {{ $branch->statistics['total_active_batches'] ?? 0 }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-500">
                            {{ number_format($branch->statistics['total_fish_stock'] ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow-md p-6 mb-6 card-hover animate-scale-in">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi Cepat</h3>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <a href="{{ route('admin.branches.create') }}" class="bg-blue-50 rounded-lg p-4 hover:bg-blue-100 transition-all duration-300 transform hover:scale-105 hover:shadow-lg card-glow">
            <div class="flex items-center">
                <div class="bg-blue-100 p-3 rounded-full mr-3 icon-bounce">
                    <i class="fas fa-building text-blue-600"></i>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-700">Tambah Cabang</h4>
                    <p class="text-xs text-gray-500 mt-1">Buat cabang baru</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.ponds.create') }}" class="bg-purple-50 rounded-lg p-4 hover:bg-purple-100 transition-all duration-300 transform hover:scale-105 hover:shadow-lg card-glow">
            <div class="flex items-center">
                <div class="bg-purple-100 p-3 rounded-full mr-3 icon-pulse">
                    <i class="fas fa-water text-purple-600"></i>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-700">Tambah Kolam</h4>
                    <p class="text-xs text-gray-500 mt-1">Buat kolam baru</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.fish-batches.create') }}" class="bg-green-50 rounded-lg p-4 hover:bg-green-100 transition-all duration-300 transform hover:scale-105 hover:shadow-lg card-glow">
            <div class="flex items-center">
                <div class="bg-green-100 p-3 rounded-full mr-3 icon-bounce">
                    <i class="fas fa-fish text-green-600"></i>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-700">Tambah Batch</h4>
                    <p class="text-xs text-gray-500 mt-1">Buat batch ikan baru</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.users.create') }}" class="bg-yellow-50 rounded-lg p-4 hover:bg-yellow-100 transition-all duration-300 transform hover:scale-105 hover:shadow-lg card-glow">
            <div class="flex items-center">
                <div class="bg-yellow-100 p-3 rounded-full mr-3 icon-rotate">
                    <i class="fas fa-user-plus text-yellow-600"></i>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-700">Tambah Pengguna</h4>
                    <p class="text-xs text-gray-500 mt-1">Buat pengguna baru</p>
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

    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_column($monthlySales->toArray(), 'month')) !!},
            datasets: [{
                label: 'Penjualan (Rp)',
                data: {!! json_encode(array_column($monthlySales->toArray(), 'amount')) !!},
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true,
                pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 8,
                pointHoverBackgroundColor: 'rgba(59, 130, 246, 1)',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 3
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
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            }
        }
    });

    const pondTypesCtx = document.getElementById('pondTypesChart').getContext('2d');
    const pondTypesChart = new Chart(pondTypesCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode(array_keys($pondTypes)) !!},
            datasets: [{
                data: {!! json_encode(array_values($pondTypes)) !!},
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(236, 72, 153, 0.8)'
                ],
                borderWidth: 3,
                borderColor: '#fff',
                hoverBorderWidth: 5,
                hoverBorderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                }
            },
            animation: {
                duration: 2000,
                easing: 'easeInOutQuart'
            },
            hover: {
                animationDuration: 300
            }
        }
    });

    // Add loading animation to charts
    setTimeout(() => {
        document.querySelectorAll('.chart-container').forEach(container => {
            container.style.opacity = '0';
            container.style.transform = 'scale(0.9)';
            container.style.transition = 'all 0.6s ease';

            setTimeout(() => {
                container.style.opacity = '1';
                container.style.transform = 'scale(1)';
            }, 100);
        });
    }, 1000);
</script>
@endpush
