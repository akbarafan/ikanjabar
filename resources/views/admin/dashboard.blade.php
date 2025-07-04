@extends('admin.layouts.app')

@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard Admin')

@push('styles')
<style>
    .card-hover {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .card-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.1);
    }
    .chart-container {
        position: relative;
        height: 300px;
        width: 100%;
    }
    .activity-item {
        transition: all 0.2s ease;
    }
    .activity-item:hover {
        background-color: #f9fafb;
    }
    @media (max-width: 640px) {
        .chart-container {
            height: 250px;
        }
    }
</style>
@endpush

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Cabang</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $totalBranches }}</h3>
            </div>
            <div class="bg-blue-100 p-3 rounded-full">
                <i class="fas fa-building text-blue-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Kolam</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $totalPonds }}</h3>
            </div>
            <div class="bg-purple-100 p-3 rounded-full">
                <i class="fas fa-water text-purple-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Pengguna</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $totalUsers }}</h3>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
                <i class="fas fa-users text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Batch Aktif</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $activeBatches }}</h3>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
                <i class="fas fa-fish text-green-600"></i>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Penjualan</p>
                <h3 class="text-lg font-bold text-gray-800">Rp {{ number_format($totalSales, 0, ',', '.') }}</h3>
                <div class="flex items-center mt-1">
                    @if($salesGrowth >= 0)
                    <i class="fas fa-arrow-up text-green-500 text-xs mr-1"></i>
                    <span class="text-xs text-green-500">+{{ number_format($salesGrowth, 1) }}%</span>
                    @else
                    <i class="fas fa-arrow-down text-red-500 text-xs mr-1"></i>
                    <span class="text-xs text-red-500">{{ number_format($salesGrowth, 1) }}%</span>
                    @endif
                </div>
            </div>
            <div class="bg-yellow-100 p-3 rounded-full">
                <i class="fas fa-money-bill-wave text-yellow-600"></i>
            </div>
        </div>
    </div>
</div>

<!-- Charts and Performance Section -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
    <!-- Branch Performance Chart -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-chart-bar text-blue-500 mr-2"></i>
            Performa Cabang (Omset)
        </h3>
        <div class="chart-container">
            <canvas id="branchPerformanceChart"></canvas>
        </div>
    </div>

    <!-- Top Performing Branches -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-trophy text-yellow-500 mr-2"></i>
            Top Performing Branches
        </h3>
        <div class="space-y-3">
            @foreach($branchPerformance->take(5) as $index => $branch)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center">
                    <div class="w-8 h-8 rounded-full {{ $index == 0 ? 'bg-yellow-500' : ($index == 1 ? 'bg-gray-400' : ($index == 2 ? 'bg-yellow-600' : 'bg-gray-300')) }} text-white flex items-center justify-center text-sm font-medium">
                        {{ $index + 1 }}
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">{{ $branch['name'] }}</p>
                        <p class="text-xs text-gray-500">{{ $branch['active_batches'] }} batch aktif</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-900">{{ $branch['revenue_formatted'] }}</p>
                    <p class="text-xs text-gray-500">{{ $branch['total_ponds'] }} kolam</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Recent Activities and Alerts -->
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">
    <!-- Recent Activities -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-clock text-green-500 mr-2"></i>
            Aktivitas Terbaru
        </h3>
        <div class="space-y-3">
            @forelse($recentActivities as $activity)
            <div class="activity-item flex items-start p-3 rounded-lg">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 rounded-full bg-{{ $activity['color'] }}-100 flex items-center justify-center">
                        <i class="fas {{ $activity['icon'] }} text-{{ $activity['color'] }}-600 text-sm"></i>
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-gray-900">{{ $activity['title'] }}</p>
                        <span class="text-xs text-gray-500">{{ $activity['time'] }}</span>
                    </div>
                    <p class="text-sm text-gray-600">{{ $activity['description'] }}</p>
                    <div class="flex items-center mt-1 text-xs text-gray-500">
                        <i class="fas fa-building mr-1"></i>
                        <span>{{ $activity['branch'] }}</span>
                        <span class="mx-2">•</span>
                        <i class="fas fa-user mr-1"></i>
                        <span>{{ $activity['user'] }}</span>
                    </div>
                    @if(isset($activity['amount']))
                    <p class="text-sm font-medium text-green-600 mt-1">{{ $activity['amount'] }}</p>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-8">
                <i class="fas fa-clock text-gray-300 text-3xl mb-2"></i>
                <p class="text-gray-500">Belum ada aktivitas terbaru</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Water Quality Alerts -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
            Peringatan Kualitas Air
        </h3>
        <div class="space-y-3">
            @forelse($waterQualityAlerts as $alert)
            <div class="flex items-start p-3 bg-{{ $alert['severity'] == 'critical' ? 'red' : 'yellow' }}-50 border border-{{ $alert['severity'] == 'critical' ? 'red' : 'yellow' }}-200 rounded-lg">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-{{ $alert['severity'] == 'critical' ? 'red' : 'yellow' }}-600"></i>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-{{ $alert['severity'] == 'critical' ? 'red' : 'yellow' }}-800">
                        {{ $alert['message'] }}
                    </p>
                    <p class="text-sm text-{{ $alert['severity'] == 'critical' ? 'red' : 'yellow' }}-700">
                        Nilai: {{ $alert['value'] }}
                    </p>
                    <div class="flex items-center mt-1 text-xs text-{{ $alert['severity'] == 'critical' ? 'red' : 'yellow' }}-600">
                        <span>{{ $alert['pond'] }} - {{ $alert['branch'] }}</span>
                        <span class="mx-2">•</span>
                        <span>{{ $alert['date'] }}</span>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-8">
                <i class="fas fa-check-circle text-green-300 text-3xl mb-2"></i>
                <p class="text-green-600">Semua parameter kualitas air normal</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Pond Types Distribution -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">
        <i class="fas fa-chart-pie text-purple-500 mr-2"></i>
        Distribusi Jenis Kolam
    </h3>
    <div class="chart-container">
        <canvas id="pondTypesChart"></canvas>
    </div>
</div>

<!-- Branch Management Table -->
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-800">Manajemen Cabang</h3>
        <a href="{{ route('admin.branches.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Tambah Cabang
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Nama Cabang</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Lokasi</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Kolam</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Batch Aktif</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Stok Ikan</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($branches as $branch)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-3 px-4 text-sm font-medium text-gray-900">{{ $branch->name }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500">{{ Str::limit($branch->location, 30) }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500 text-center">{{ $branch->statistics['total_ponds'] }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500 text-center">{{ $branch->statistics['total_active_batches'] }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500 text-center">{{ number_format($branch->statistics['total_fish_stock']) }}</td>
                    <td class="py-3 px-4 text-sm text-gray-500 text-center">{{ $branch->users_count }}</td>
                    <td class="py-3 px-4 text-sm">
                        <div class="flex space-x-2">
                            <a href="{{ route('admin.branches.show', $branch) }}" class="text-blue-600 hover:text-blue-900" title="Lihat">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.branches.edit', $branch) }}" class="text-yellow-600 hover:text-yellow-900" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.branches.destroy', $branch) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus cabang ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
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
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Branch Performance Chart
    const branchPerformanceCtx = document.getElementById('branchPerformanceChart').getContext('2d');
    const branchData = @json($branchPerformance);

    new Chart(branchPerformanceCtx, {
        type: 'bar',
        data: {
            labels: branchData.map(branch => branch.name.length > 15 ? branch.name.substring(0, 15) + '...' : branch.name),
            datasets: [{
                label: 'Omset (Rp)',
                data: branchData.map(branch => branch.total_revenue),
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(236, 72, 153, 0.8)',
                    'rgba(139, 69, 19, 0.8)',
                    'rgba(75, 85, 99, 0.8)'
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
                borderWidth: 2,
                borderRadius: 6,
                borderSkipped: false
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
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const branch = branchData[context.dataIndex];
                            return [
                                'Omset: Rp ' + context.parsed.y.toLocaleString('id-ID'),
                                'Batch Aktif: ' + branch.active_batches,
                                'Total Kolam: ' + branch.total_ponds
                            ];
                        }
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
                            size: 11,
                            weight: '500'
                        },
                        maxRotation: 45
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
                            size: 11
                        },
                        callback: function(value) {
                            if (value >= 1000000000) {
                                return 'Rp ' + (value / 1000000000).toFixed(1) + 'B';
                            } else if (value >= 1000000) {
                                return 'Rp ' + (value / 1000000).toFixed(0) + 'M';
                            } else if (value >= 1000) {
                                return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                            }
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });

    // Pond Types Distribution Chart
    const pondTypesCtx = document.getElementById('pondTypesChart').getContext('2d');
    const pondTypesData = @json($pondTypes);

    new Chart(pondTypesCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(pondTypesData),
            datasets: [{
                data: Object.values(pondTypesData),
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(99, 102, 241, 0.8)',
                    'rgba(236, 72, 153, 0.8)'
                ],
                borderColor: [
                    'rgba(59, 130, 246, 1)',
                    'rgba(16, 185, 129, 1)',
                    'rgba(245, 158, 11, 1)',
                    'rgba(99, 102, 241, 1)',
                    'rgba(236, 72, 153, 1)'
                ],
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        font: {
                            size: 12,
                            weight: '500'
                        },
                        generateLabels: function(chart) {
                            const data = chart.data;
                            if (data.labels.length && data.datasets.length) {
                                const dataset = data.datasets[0];
                                const total = dataset.data.reduce((a, b) => a + b, 0);

                                return data.labels.map((label, i) => {
                                    const value = dataset.data[i];
                                    const percentage = ((value / total) * 100).toFixed(1);

                                    return {
                                        text: `${label}: ${value} kolam (${percentage}%)`,
                                        fillStyle: dataset.backgroundColor[i],
                                        strokeStyle: dataset.borderColor[i],
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
                    titleColor: 'white',
                    bodyColor: 'white',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} kolam (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });

    // Auto refresh every 5 minutes
    setTimeout(function() {
        location.reload();
    }, 300000);

    // Real-time notifications for critical alerts
    @if(count($waterQualityAlerts->where('severity', 'critical')) > 0)
    showNotification('{{ count($waterQualityAlerts->where('severity', 'critical')) }} peringatan kritis kualitas air memerlukan perhatian segera!', 'error');
    @endif

    // Responsive chart handling
    function handleResize() {
        const isMobile = window.innerWidth < 640;

        // Update chart options for mobile
        if (isMobile) {
            // Adjust chart containers height for mobile
            document.querySelectorAll('.chart-container').forEach(container => {
                container.style.height = '250px';
            });
        }
    }

    window.addEventListener('resize', handleResize);
    handleResize(); // Initial call
});

// Notification function
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

// Click handlers for interactive elements
document.addEventListener('click', function(e) {
    // Handle activity item clicks
    if (e.target.closest('.activity-item')) {
        const activityItem = e.target.closest('.activity-item');
        activityItem.style.backgroundColor = '#f3f4f6';
        setTimeout(() => {
            activityItem.style.backgroundColor = '';
        }, 200);
    }
});

// Performance monitoring
if ('performance' in window) {
    window.addEventListener('load', function() {
        setTimeout(() => {
            const perfData = performance.getEntriesByType('navigation')[0];
            if (perfData && perfData.loadEventEnd - perfData.loadEventStart > 3000) {
                console.warn('Dashboard loaded slowly, consider optimizing');
            }
        }, 1000);
    });
}
</script>
@endpush

