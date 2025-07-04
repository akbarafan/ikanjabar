@extends('user.layouts.app')

@section('page-title', 'Kualitas Air')

@section('content')
<div class="space-y-4 lg:space-y-6">
    <!-- Stats Cards - Mobile Optimized -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-blue-100">
                    <i class="fas fa-tint text-blue-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Total</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ $stats['total_records'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full " style="background-color: #dcfce7;">
                    <i class="fas fa-check-circle text-green-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Baik</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ $stats['good_quality'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-yellow-100">
                    <i class="fas fa-exclamation-triangle text-yellow-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Perhatian</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ $stats['warning_quality'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full" style="background-color: #fee2e2;">
                    <i class="fas fa-times-circle text-red-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Kritis</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ $stats['critical_quality'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Water Quality Content -->
    <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100">
        <!-- Header -->
        <div class="p-4 lg:p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 lg:gap-4">
                <div>
                    <h3 class="text-base lg:text-lg font-semibold text-gray-900">Monitoring Kualitas Air</h3>
                    <p class="text-xs lg:text-sm text-gray-600 mt-1">{{ $branchInfo->name }} • {{ $stats['monitored_ponds'] }} kolam dipantau</p>
                </div>
                <button onclick="openAddModal()" class="inline-flex items-center justify-center px-3 lg:px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-1 lg:mr-2"></i>
                    <span class="hidden sm:inline">Tambah Data</span>
                    <span class="sm:hidden">Tambah</span>
                </button>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="block lg:hidden">
            @forelse($waterQualities as $wq)
            <hr><hr><hr>
            <hr><hr><hr>
            <div class="border-b border-gray-100 last:border-b-0 p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <!-- Pond Image -->
                        @if($wq->pond_image_url)
                            <img class="h-10 w-10 rounded-lg object-cover border border-gray-200 cursor-pointer"
                                 src="{{ $wq->pond_image_url }}"
                                 alt="{{ $wq->pond_name }}"
                                 onclick="showImageModal('{{ $wq->pond_image_url }}', '{{ $wq->pond_name }}')"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="h-10 w-10 rounded-lg bg-blue-100 border border-gray-200 items-center justify-center hidden">
                                <i class="fas fa-swimming-pool text-blue-400 text-sm"></i>
                            </div>
                        @else
                            <div class="h-10 w-10 rounded-lg bg-blue-100 border border-gray-200 flex items-center justify-center">
                                <i class="fas fa-swimming-pool text-blue-400 text-sm"></i>
                            </div>
                        @endif

                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $wq->pond_name }}</div>
                            <div class="text-xs text-gray-500">{{ $wq->formatted_date }} • {{ $wq->pond_code }}</div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <!-- Overall Status Badge -->
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                            style="{{ $wq->status_color === 'green' ? 'background-color: #dcfce7; color: #15803d;' :
                               ($wq->status_color === 'yellow' ? 'background-color: #fef3c7; color: #92400e;' : 'background-color: #fee2e2; color: #b91c1c;') }}">
                            {{ $wq->status_text }}
                        </span>

                        <!-- Action Buttons -->
                        <div class="flex items-center space-x-1">
                            <button onclick="editWaterQuality({{ $wq->id }})" class="text-blue-600 hover:text-blue-900 p-1" title="Edit">
                                <i class="fas fa-edit text-sm"></i>
                            </button>
                            <button onclick="deleteWaterQuality({{ $wq->id }}, '{{ $wq->formatted_date }}')" class="text-red-600 hover:text-red-900 p-1" title="Hapus">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Key Parameters Grid -->
                <div class="grid grid-cols-2 gap-3 mb-3">
                    <div class="bg-gray-50 rounded-lg p-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">pH</span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium"
                                style="{{ $wq->ph_status === 'good' ? 'background-color: #dcfce7; color: #15803d;' :
                                   ($wq->ph_status === 'warning' ? 'background-color: #fef3c7; color: #92400e;' : 'background-color: #fee2e2; color: #b91c1c;') }}">
                                {{ $wq->formatted_ph }}
                            </span>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">Suhu</span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium"
                                style="{{ $wq->temp_status === 'good' ? 'background-color: #dcfce7; color: #15803d;' :
                                   ($wq->temp_status === 'warning' ? 'background-color: #fef3c7; color: #92400e;' : 'background-color: #fee2e2; color: #b91c1c;') }}">
                                {{ $wq->formatted_temp }}°C
                            </span>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">DO</span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium"
                                style="{{ $wq->do_status === 'good' ? 'background-color: #dcfce7; color: #15803d;' :
                                   ($wq->do_status === 'warning' ? 'background-color: #fef3c7; color: #92400e;' : 'background-color: #fee2e2; color: #b91c1c;') }}">
                            {{ $wq->formatted_do }}
                        </span>
                    </div>
                </div>

                <div class="bg-gray-50 rounded-lg p-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">NH₃</span>
                        @if($wq->formatted_ammonia)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium"
                                style="{{ $wq->ammonia_status === 'good' ? 'background-color: #dcfce7; color: #15803d;' :
                                   ($wq->ammonia_status === 'warning' ? 'background-color: #fef3c7; color: #92400e;' : 'background-color: #fee2e2; color: #b91c1c;') }}">
                                {{ $wq->formatted_ammonia }}
                            </span>
                        @else
                            <span class="text-xs text-gray-400">-</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Fish Types and Additional Info -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    @if($wq->active_batches > 0)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $wq->active_batches }} batch
                        </span>
                        @if($wq->fish_types_array)
                            <span class="text-xs text-gray-500 truncate">
                                {{ implode(', ', array_slice($wq->fish_types_array, 0, 2)) }}
                                @if(count($wq->fish_types_array) > 2)
                                    +{{ count($wq->fish_types_array) - 2 }}
                                @endif
                            </span>
                        @endif
                    @else
                        <span class="text-xs text-gray-400">Tidak ada batch aktif</span>
                    @endif
                </div>

                @if($wq->critical_params > 0)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        <i class="fas fa-exclamation-triangle mr-1"></i>
                        {{ $wq->critical_params }} kritis
                    </span>
                @endif
            </div>

            @if($wq->created_by_name)
                <div class="mt-2 text-xs text-gray-400">
                    Oleh {{ $wq->created_by_name }}
                </div>
            @endif
        </div>
        @empty
        <div class="p-8 text-center">
            <i class="fas fa-tint text-gray-300 text-3xl mb-3"></i>
            <h3 class="text-base font-medium text-gray-900 mb-2">Belum ada data</h3>
            <p class="text-sm text-gray-500 mb-4">Mulai dengan menambahkan data kualitas air pertama.</p>
            <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fas fa-plus mr-2"></i>
                Tambah Sekarang
            </button>
        </div>
        @endforelse
    </div>

    <!-- Desktop Table View -->
    <div class="hidden lg:block overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kolam</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">pH</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suhu (°C)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DO (mg/L)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NH₃ (mg/L)</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($waterQualities as $wq)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">
                            {{ \Carbon\Carbon::parse($wq->date_recorded)->format('d M Y') }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ \Carbon\Carbon::parse($wq->created_at)->format('H:i') }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-3">
                            <div class="flex-shrink-0">
                                @if($wq->pond_image_url)
                                    <img class="h-8 w-8 rounded-lg object-cover border border-gray-200 cursor-pointer"
                                         src="{{ $wq->pond_image_url }}"
                                         alt="{{ $wq->pond_name }}"
                                         onclick="showImageModal('{{ $wq->pond_image_url }}', '{{ $wq->pond_name }}')"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="h-8 w-8 rounded-lg bg-blue-100 border border-gray-200 items-center justify-center hidden">
                                        <i class="fas fa-swimming-pool text-blue-400 text-xs"></i>
                                    </div>
                                @else
                                    <div class="h-8 w-8 rounded-lg bg-blue-100 border border-gray-200 flex items-center justify-center">
                                        <i class="fas fa-swimming-pool text-blue-400 text-xs"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm font-medium text-gray-900">{{ $wq->pond_name }}</div>
                                <div class="text-sm text-gray-500">{{ $wq->pond_code }} • {{ $wq->pond_type }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                style="{{ $wq->ph_status === 'good' ? 'background-color: #dcfce7; color: #15803d;' :
                                   ($wq->ph_status === 'warning' ? 'background-color: #fef3c7; color: #92400e;' : 'background-color: #fee2e2; color: #b91c1c;') }}">
                            {{ $wq->formatted_ph }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                style="{{ $wq->temp_status === 'good' ? 'background-color: #dcfce7; color: #15803d;' :
                                   ($wq->temp_status === 'warning' ? 'background-color: #fef3c7; color: #92400e;' : 'background-color: #fee2e2; color: #b91c1c;') }}">
                            {{ $wq->formatted_temp }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                style="{{ $wq->do_status === 'good' ? 'background-color: #dcfce7; color: #15803d;' :
                                   ($wq->do_status === 'warning' ? 'background-color: #fef3c7; color: #92400e;' : 'background-color: #fee2e2; color: #b91c1c;') }}">
                            {{ $wq->formatted_do }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($wq->formatted_ammonia)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                style="{{ $wq->ammonia_status === 'good' ? 'background-color: #dcfce7; color: #15803d;' :
                                   ($wq->ammonia_status === 'warning' ? 'background-color: #fef3c7; color: #92400e;' : 'background-color: #fee2e2; color: #b91c1c;') }}">
                                {{ $wq->formatted_ammonia }}
                            </span>
                        @else
                            <span class="text-xs text-gray-400">Tidak diukur</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                style="{{ $wq->status_color === 'green' ? 'background-color: #dcfce7; color: #15803d;' :
                                   ($wq->status_color === 'yellow' ? 'background-color: #fef3c7; color: #92400e;' : 'background-color: #fee2e2; color: #b91c1c;') }}">
                            {{ $wq->status_text }}
                        </span>
                        @if($wq->critical_params > 0)
                            <div class="text-xs text-red-600 mt-1">
                                {{ $wq->critical_params }} parameter kritis
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($wq->active_batches > 0)
                            <div class="text-sm text-gray-900">{{ $wq->active_batches }} batch aktif</div>
                            @if($wq->fish_types_array)
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ implode(', ', array_slice($wq->fish_types_array, 0, 2)) }}
                                    @if(count($wq->fish_types_array) > 2)
                                        <span class="text-gray-400">+{{ count($wq->fish_types_array) - 2 }}</span>
                                    @endif
                                </div>
                            @endif
                        @else
                            <span class="text-xs text-gray-400">Tidak ada batch</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-2">
                            <button onclick="editWaterQuality({{ $wq->id }})" class="text-blue-600 hover:text-blue-900 p-1" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteWaterQuality({{ $wq->id }}, '{{ \Carbon\Carbon::parse($wq->date_recorded)->format('d M Y') }}')" class="text-red-600 hover:text-red-900 p-1" title="Hapus">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-tint text-gray-300 text-4xl mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada data kualitas air</h3>
                            <p class="text-gray-500 mb-4">Mulai dengan menambahkan data monitoring kualitas air pertama untuk cabang ini.</p>
                            <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <i class="fas fa-plus mr-2"></i>
                                Tambah Data Kualitas Air
                            </button>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($pagination['total_pages'] > 1)
    <div class="px-4 py-3 lg:px-6 lg:py-4 border-t border-gray-200">
        <div class="flex flex-col sm:flex-row items-center justify-between space-y-3 sm:space-y-0">
            <!-- Mobile Pagination Info -->
            <div class="text-xs lg:text-sm text-gray-700 order-2 sm:order-1">
                Halaman {{ $pagination['current_page'] }} dari {{ $pagination['total_pages'] }}
                <span class="hidden sm:inline">({{ $pagination['total_items'] }} total)</span>
            </div>
                            <!-- Pagination Controls -->
                            <div class="flex items-center space-x-2 order-1 sm:order-2">
                                @if($pagination['has_prev'])
                                    <a href="?page={{ $pagination['prev_page'] }}"
                                       class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 transition-colors">
                                        <i class="fas fa-chevron-left mr-1"></i>
                                        <span class="hidden sm:inline">Sebelumnya</span>
                                    </a>
                                @endif

                                <!-- Page Numbers (Desktop Only) -->
                                <div class="hidden lg:flex items-center space-x-1">
                                    @php
                                        $start = max(1, $pagination['current_page'] - 2);
                                        $end = min($pagination['total_pages'], $pagination['current_page'] + 2);
                                    @endphp

                                    @if($start > 1)
                                        <a href="?page=1" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">1</a>
                                        @if($start > 2)
                                            <span class="px-2 text-gray-500">...</span>
                                        @endif
                                    @endif

                                    @for($i = $start; $i <= $end; $i++)
                                        @if($i == $pagination['current_page'])
                                            <span class="px-3 py-2 text-sm font-medium text-white bg-blue-600 border border-blue-600 rounded-lg">{{ $i }}</span>
                                        @else
                                            <a href="?page={{ $i }}" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">{{ $i }}</a>
                                        @endif
                                    @endfor

                                    @if($end < $pagination['total_pages'])
                                        @if($end < $pagination['total_pages'] - 1)
                                            <span class="px-2 text-gray-500">...</span>
                                        @endif
                                        <a href="?page={{ $pagination['total_pages'] }}" class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">{{ $pagination['total_pages'] }}</a>
                                    @endif
                                </div>

                                @if($pagination['has_next'])
                                    <a href="?page={{ $pagination['next_page'] }}"
                                       class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 hover:text-gray-700 transition-colors">
                                        <span class="hidden sm:inline">Selanjutnya</span>
                                        <i class="fas fa-chevron-right ml-1"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Add/Edit Modal - Mobile Optimized -->
            <div id="waterQualityModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
                <div class="relative top-4 lg:top-20 mx-auto p-4 lg:p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white m-4">
                    <div class="mt-3">
                        <div class="flex items-center justify-between mb-4">
                            <h3 id="modalTitle" class="text-base lg:text-lg font-medium text-gray-900">Tambah Data Kualitas Air</h3>
                            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 p-1">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>

                        <form id="waterQualityForm" class="space-y-4">
                            <input type="hidden" id="waterQualityId" name="id">

                            <!-- Custom Pond Dropdown - Mobile Optimized -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Kolam *</label>
                                <div class="relative">
                                    <select id="pond_id" name="pond_id" required class="hidden">
                                        <option value="">Pilih Kolam</option>
                                        @foreach($ponds as $pond)
                                        <option value="{{ $pond->id }}">
                                            {{ $pond->name }} ({{ $pond->code }})
                                        </option>
                                        @endforeach
                                    </select>

                                    <button type="button" id="pondDropdownBtn" onclick="togglePondDropdown()"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-left flex items-center justify-between">
                                        <div class="flex items-center space-x-2 lg:space-x-3 min-w-0 flex-1">
                                            <div id="selectedPondImage" class="hidden flex-shrink-0">
                                                <img class="h-5 w-5 lg:h-6 lg:w-6 rounded object-cover border border-gray-200" src="" alt="">
                                            </div>
                                            <span id="selectedPondText" class="text-gray-500 truncate">Pilih Kolam</span>
                                        </div>
                                        <i id="pondDropdownIcon" class="fas fa-chevron-down text-gray-400 transition-transform duration-200 flex-shrink-0"></i>
                                    </button>

                                    <div id="pondDropdownMenu" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 lg:max-h-60 overflow-y-auto hidden">
                                        @foreach($ponds as $pond)
                                            <div class="pond-option px-3 py-2 lg:py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                                 data-value="{{ $pond->id }}"
                                                 data-pond-name="{{ $pond->name }}"
                                                 data-pond-code="{{ $pond->code }}"
                                                 data-pond-type="{{ $pond->type }}"
                                                 data-volume="{{ $pond->volume_liters }}"
                                                 data-active-batches="{{ $pond->active_batches }}"
                                                 data-image="{{ $pond->image_url }}"
                                                 onclick="selectPond(this)">
                                                <div class="flex items-center space-x-2 lg:space-x-3">
                                                    <div class="flex-shrink-0">
                                                        @if($pond->image_url)
                                                            <img class="h-8 w-8 lg:h-10 lg:w-10 rounded-lg object-cover border border-gray-200"
                                                                 src="{{ $pond->image_url }}"
                                                                 alt="{{ $pond->name }}"
                                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                            <div class="h-8 w-8 lg:h-10 lg:w-10 rounded-lg bg-blue-100 border border-gray-200 items-center justify-center hidden">
                                                                <i class="fas fa-swimming-pool text-blue-400 text-sm"></i>
                                                            </div>
                                                        @else
                                                            <div class="h-8 w-8 lg:h-10 lg:w-10 rounded-lg bg-blue-100 border border-gray-200 flex items-center justify-center">
                                                                <i class="fas fa-swimming-pool text-blue-400 text-sm"></i>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="flex items-center space-x-2">
                                                            <p class="text-sm font-medium text-gray-900">{{ $pond->name }}</p>
                                                            @if($pond->active_batches > 0)
                                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                                    {{ $pond->active_batches }} batch
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <p class="text-xs lg:text-sm text-gray-500 truncate">{{ $pond->code }} • {{ $pond->type }}</p>
                                                        <div class="flex items-center space-x-2 mt-1">
                                                            <span class="text-xs text-gray-500">{{ number_format($pond->volume_liters) }}L</span>
                                                            @if($pond->fish_types_array)
                                                                <span class="text-xs text-blue-600">
                                                                    {{ implode(', ', array_slice($pond->fish_types_array, 0, 2)) }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div id="pondInfo" class="text-xs text-gray-500 mt-1 hidden">
                                    Volume: <span id="pondVolume">0</span>L | Batch aktif: <span id="activeBatches">0</span>
                                </div>
                            </div>

                            <div>
                                <label for="date_recorded" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Pengukuran *</label>
                                <input type="date" id="date_recorded" name="date_recorded" required max="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <!-- Water Quality Parameters Grid -->
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                <div>
                                    <label for="ph" class="block text-sm font-medium text-gray-700 mb-1">
                                        pH *
                                        <span class="text-xs text-gray-500">(6.5-8.5 optimal)</span>
                                    </label>
                                    <input type="number" id="ph" name="ph" required min="0" max="14" step="0.1"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="7.0">
                                </div>

                                <div>
                                    <label for="temperature_c" class="block text-sm font-medium text-gray-700 mb-1">
                                        Suhu (°C) *
                                        <span class="text-xs text-gray-500">(25-30 optimal)</span>
                                    </label>
                                    <input type="number" id="temperature_c" name="temperature_c" required min="0" max="50" step="0.1"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="28.0">
                                </div>

                                <div>
                                    <label for="do_mg_l" class="block text-sm font-medium text-gray-700 mb-1">
                                        DO (mg/L) *
                                        <span class="text-xs text-gray-500">(≥5 optimal)</span>
                                    </label>
                                    <input type="number" id="do_mg_l" name="do_mg_l" required min="0" max="20" step="0.1"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="6.0">
                                </div>

                                <div>
                                    <label for="ammonia_mg_l" class="block text-sm font-medium text-gray-700 mb-1">
                                        Amonia (mg/L)
                                        <span class="text-xs text-gray-500">(≤0.1 optimal)</span>
                                    </label>
                                    <input type="number" id="ammonia_mg_l" name="ammonia_mg_l" min="0" max="10" step="0.01"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0.05">
                                </div>
                            </div>

                            <!-- Parameter Status Indicators -->
                            <div id="parameterStatus" class="hidden">
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <h4 class="text-sm font-medium text-gray-700 mb-2">Status Parameter:</h4>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div id="phStatus" class="flex items-center space-x-2">
                                            <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                            <span>pH: <span class="font-medium">-</span></span>
                                        </div>
                                        <div id="tempStatus" class="flex items-center space-x-2">
                                            <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                            <span>Suhu: <span class="font-medium">-</span></span>
                                        </div>
                                        <div id="doStatus" class="flex items-center space-x-2">
                                            <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                            <span>DO: <span class="font-medium">-</span></span>
                                        </div>
                                        <div id="ammoniaStatus" class="flex items-center space-x-2">
                                            <span class="w-2 h-2 rounded-full bg-gray-300"></span>
                                            <span>NH₃: <span class="font-medium">-</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col-reverse sm:flex-row items-center justify-end space-y-reverse space-y-2 sm:space-y-0 sm:space-x-3 pt-4">
                                <button type="button" onclick="closeModal()"
                                        class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                    Batal
                                </button>
                                <button type="submit" id="submitBtn"
                                        class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                                    <span id="submitText">Simpan</span>
                                    <i id="submitLoader" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal - Mobile Optimized -->
            <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
                <div class="relative top-20 mx-auto p-4 lg:p-5 border w-full max-w-md shadow-lg rounded-lg bg-white m-4">
                    <div class="mt-3 text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <h3 class="text-base lg:text-lg font-medium text-gray-900 mb-2">Hapus Data Kualitas Air</h3>
                        <p class="text-sm text-gray-500 mb-4">
                            Apakah Anda yakin ingin menghapus data kualitas air tanggal <strong id="deleteWaterQualityDate"></strong>?
                            Tindakan ini tidak dapat dibatalkan.
                        </p>
                        <div class="flex flex-col-reverse sm:flex-row items-center justify-center space-y-reverse space-y-2 sm:space-y-0 sm:space-x-3">
                            <button onclick="closeDeleteModal()"
                                    class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                                Batal
                            </button>
                            <button onclick="confirmDelete()" id="deleteBtn"
                                    class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                                <span id="deleteText">Hapus</span>
                                <i id="deleteLoader" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Image Modal -->
            <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
                <div class="relative max-w-4xl max-h-full p-4">
                    <button onclick="closeImageModal()" class="absolute top-2 right-2 text-white hover:text-gray-300 z-10">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                    <img id="modalImage" src="" alt="" class="max-w-full max-h-full rounded-lg">
                    <div class="text-center mt-2">
                        <p id="modalImageTitle" class="text-white text-sm"></p>
                    </div>
                </div>
            </div>

            <script>
            let currentWaterQualityId = null;
            let deleteId = null;

            // Pond Dropdown Functions
            function togglePondDropdown() {
                const menu = document.getElementById('pondDropdownMenu');
                const icon = document.getElementById('pondDropdownIcon');

                if (menu.classList.contains('hidden')) {
                    menu.classList.remove('hidden');
                    icon.classList.add('rotate-180');
                } else {
                    menu.classList.add('hidden');
                    icon.classList.remove('rotate-180');
                }
            }

            function selectPond(element) {
                const pondId = element.dataset.value;
                const pondName = element.dataset.pondName;
                const pondCode = element.dataset.pondCode;
                const pondType = element.dataset.pondType;
                const volume = element.dataset.volume;
                const activeBatches = element.dataset.activeBatches;
                const imageUrl = element.dataset.image;

                // Update hidden select
                document.getElementById('pond_id').value = pondId;

                // Update display
                const selectedText = document.getElementById('selectedPondText');
                const selectedImage = document.getElementById('selectedPondImage');
                const pondInfo = document.getElementById('pondInfo');

                selectedText.textContent = `${pondName} (${pondCode})`;
                selectedText.classList.remove('text-gray-500');
                selectedText.classList.add('text-gray-900');

                // Update image
                if (imageUrl && imageUrl !== 'null') {
                    selectedImage.querySelector('img').src = imageUrl;
                    selectedImage.classList.remove('hidden');
                } else {
                    selectedImage.classList.add('hidden');
                }

                // Update pond info
                document.getElementById('pondVolume').textContent = new Intl.NumberFormat().format(volume);
                document.getElementById('activeBatches').textContent = activeBatches;
                pondInfo.classList.remove('hidden');

                // Close dropdown
                document.getElementById('pondDropdownMenu').classList.add('hidden');
                document.getElementById('pondDropdownIcon').classList.remove('rotate-180');
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                const dropdown = document.getElementById('pondDropdownMenu');
                const button = document.getElementById('pondDropdownBtn');

                if (!dropdown.contains(event.target) && !button.contains(event.target)) {
                    dropdown.classList.add('hidden');
                    document.getElementById('pondDropdownIcon').classList.remove('rotate-180');
                }
            });

            // Parameter Status Functions
            function updateParameterStatus() {
                const ph = parseFloat(document.getElementById('ph').value);
                const temp = parseFloat(document.getElementById('temperature_c').value);
                const doValue = parseFloat(document.getElementById('do_mg_l').value);
                const ammonia = parseFloat(document.getElementById('ammonia_mg_l').value);

                const statusContainer = document.getElementById('parameterStatus');

                if (ph || temp || doValue || ammonia) {
                    statusContainer.classList.remove('hidden');

                    // Update pH status
                    updateStatus('phStatus', ph, getPhStatus(ph), 'pH');

                    // Update temperature status
                    updateStatus('tempStatus', temp, getTempStatus(temp), 'Suhu');

                    // Update DO status
                    updateStatus('doStatus', doValue, getDoStatus(doValue), 'DO');

                    // Update ammonia status
                    updateStatus('ammoniaStatus', ammonia, getAmmoniaStatus(ammonia), 'NH₃');
                } else {
                    statusContainer.classList.add('hidden');
                }
            }

            function updateStatus(elementId, value, status, label) {
                const element = document.getElementById(elementId);
                const indicator = element.querySelector('.w-2.h-2');
                const text = element.querySelector('.font-medium');

                if (value) {
                    text.textContent = getStatusText(status);
                    indicator.className = `w-2 h-2 rounded-full ${getStatusColorClass(status)}`;
                } else {
                    text.textContent = '-';
                    indicator.className = 'w-2 h-2 rounded-full bg-gray-300';
                }
            }

            function getPhStatus(ph) {
                if (ph >= 6.5 && ph <= 8.5) return 'good';
                if (ph >= 6.0 && ph <= 9.0) return 'warning';
                return 'danger';
            }

            function getTempStatus(temp) {
                if (temp >= 25 && temp <= 30) return 'good';
                if (temp >= 20 && temp <= 35) return 'warning';
                return 'danger';
            }

            function getDoStatus(doValue) {
                if (doValue >= 5) return 'good';
                if (doValue >= 3) return 'warning';
                return 'danger';
            }

            function getAmmoniaStatus(ammonia) {
                if (!ammonia) return 'unknown';
                if (ammonia <= 0.1) return 'good';
                if (ammonia <= 0.5) return 'warning';
                return 'danger';
            }

            function getStatusText(status) {
                switch (status) {
                    case 'good': return 'Baik';
                    case 'warning': return 'Perhatian';
                    case 'danger': return 'Kritis';
                    default: return '-';
                }
            }

            function getStatusColorClass(status) {
                switch (status) {
                    case 'good': return 'bg-green-500';
                    case 'warning': return 'bg-yellow-500';
                    case 'danger': return 'bg-red-500';
                    default: return 'bg-gray-300';
                }
            }

            // Image Modal Functions
            function showImageModal(src, title) {
                document.getElementById('modalImage').src = src;
                document.getElementById('modalImageTitle').textContent = title;
                document.getElementById('imageModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeImageModal() {
                document.getElementById('imageModal').classList.add('hidden');
                document.body.style.overflow = 'auto';
            }

            // Modal functions
            function openAddModal() {
                document.getElementById('modalTitle').textContent = 'Tambah Data Kualitas Air';
                document.getElementById('submitText').textContent = 'Simpan';
                document.getElementById('waterQualityForm').reset();
                document.getElementById('waterQualityId').value = '';
                currentWaterQualityId = null;

                // Reset pond dropdown
                resetPondDropdown();

                document.getElementById('waterQualityModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                // Focus on date field after a short delay
                setTimeout(() => {
                    document.getElementById('date_recorded').focus();
                }, 100);
            }

            function closeModal() {
                document.getElementById('waterQualityModal').classList.add('hidden');
                document.body.style.overflow = 'auto';
            }

            function closeDeleteModal() {
                document.getElementById('deleteModal').classList.add('hidden');
                document.body.style.overflow = 'auto';
                deleteId = null;
            }

            function resetPondDropdown() {
                document.getElementById('pond_id').value = '';
                document.getElementById('selectedPondText').textContent = 'Pilih Kolam';
                document.getElementById('selectedPondText').classList.add('text-gray-500');
                document.getElementById('selectedPondText').classList.remove('text-gray-900');
                document.getElementById('selectedPondImage').classList.add('hidden');
                document.getElementById('pondInfo').classList.add('hidden');
                document.getElementById('pondDropdownMenu').classList.add('hidden');
                document.getElementById('pondDropdownIcon').classList.remove('rotate-180');
                document.getElementById('parameterStatus').classList.add('hidden');
            }

            // CRUD functions
            function editWaterQuality(id) {
                currentWaterQualityId = id;
                document.getElementById('modalTitle').textContent = 'Edit Data Kualitas Air';
                document.getElementById('submitText').textContent = 'Perbarui';

                document.getElementById('waterQualityModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';

                // Show loading state
                const form = document.getElementById('waterQualityForm');
                form.style.opacity = '0.6';

                fetch(`/water-qualities/${id}`)
                    .then(response => response.json())
                    .then(result => {
                        if (result.success) {
                            const data = result.data;
                            document.getElementById('waterQualityId').value = data.id;
                            document.getElementById('pond_id').value = data.pond_id;
                            document.getElementById('date_recorded').value = data.date_recorded;
                            document.getElementById('ph').value = data.ph;
                            document.getElementById('temperature_c').value = data.temperature_c;
                            document.getElementById('do_mg_l').value = data.do_mg_l;
                            document.getElementById('ammonia_mg_l').value = data.ammonia_mg_l || '';

                            // Update pond dropdown display
                            const selectedPond = document.querySelector(`[data-value="${data.pond_id}"]`);
                            if (selectedPond) {
                                selectPond(selectedPond);
                            }

                            // Update parameter status
                            updateParameterStatus();

                            form.style.opacity = '1';
                            document.getElementById('date_recorded').focus();
                        } else {
                            showNotification('Error: ' + result.message, 'error');
                            closeModal();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showNotification('Gagal memuat data kualitas air', 'error');
                        closeModal();
                    })
                    .finally(() => {
                        form.style.opacity = '1';
                    });
            }

            function deleteWaterQuality(id, date) {
                deleteId = id;
                document.getElementById('deleteWaterQualityDate').textContent = date;
                document.getElementById('deleteModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function confirmDelete() {
                if (!deleteId) return;

                const deleteBtn = document.getElementById('deleteBtn');
                const deleteText = document.getElementById('deleteText');
                const deleteLoader = document.getElementById('deleteLoader');

                deleteText.textContent = 'Menghapus...';
                deleteLoader.classList.remove('hidden');
                deleteBtn.disabled = true;

                fetch(`/water-qualities/${deleteId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showNotification(result.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showNotification(result.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Gagal menghapus data kualitas air', 'error');
                })
                .finally(() => {
                    deleteText.textContent = 'Hapus';
                    deleteLoader.classList.add('hidden');
                    deleteBtn.disabled = false;
                    closeDeleteModal();
                });
            }

            // Form submission
document.getElementById('waterQualityForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitLoader = document.getElementById('submitLoader');

    const formData = new FormData(this);
    const isEdit = currentWaterQualityId !== null;

    // Validate pond selection
    if (!formData.get('pond_id')) {
        showNotification('Silakan pilih kolam terlebih dahulu', 'error');
        return;
    }

    submitText.textContent = isEdit ? 'Memperbarui...' : 'Menyimpan...';
    submitLoader.classList.remove('hidden');
    submitBtn.disabled = true;

    const url = isEdit ? `/water-qualities/${currentWaterQualityId}` : '/water-qualities';

    const data = {
        pond_id: formData.get('pond_id'),
        date_recorded: formData.get('date_recorded'),
        ph: formData.get('ph'),
        temperature_c: formData.get('temperature_c'),
        do_mg_l: formData.get('do_mg_l'),
        ammonia_mg_l: formData.get('ammonia_mg_l') || null
    };

    if (isEdit) {
        data._method = 'PUT';
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            showNotification(result.message, 'success');
            closeModal();
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(result.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Terjadi kesalahan. Silakan coba lagi.', 'error');
    })
    .finally(() => {
        submitText.textContent = isEdit ? 'Perbarui' : 'Simpan';
        submitLoader.classList.add('hidden');
        submitBtn.disabled = false;
    });
});

// Add event listeners for parameter status updates
document.getElementById('ph').addEventListener('input', updateParameterStatus);
document.getElementById('temperature_c').addEventListener('input', updateParameterStatus);
document.getElementById('do_mg_l').addEventListener('input', updateParameterStatus);
document.getElementById('ammonia_mg_l').addEventListener('input', updateParameterStatus);

// Notification function - Mobile optimized
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 left-4 right-4 sm:top-4 sm:right-4 sm:left-auto z-50 p-3 sm:p-4 rounded-lg shadow-lg sm:max-w-sm transform transition-all duration-300 translate-y-[-100px] sm:translate-y-0 sm:translate-x-full`;

    if (type === 'success') {
        notification.classList.add('bg-green-500', 'text-white');
        notification.innerHTML = `<div class="flex items-center"><i class="fas fa-check-circle mr-2 flex-shrink-0"></i><span class="flex-1">${message}</span></div>`;
    } else if (type === 'error') {
        notification.classList.add('bg-red-500', 'text-white');
        notification.innerHTML = `<div class="flex items-center"><i class="fas fa-exclamation-circle mr-2 flex-shrink-0"></i><span class="flex-1">${message}</span></div>`;
    } else {
        notification.classList.add('bg-blue-500', 'text-white');
        notification.innerHTML = `<div class="flex items-center"><i class="fas fa-info-circle mr-2 flex-shrink-0"></i><span class="flex-1">${message}</span></div>`;
    }

    document.body.appendChild(notification);

    setTimeout(() => {
        notification.classList.remove('translate-y-[-100px]', 'sm:translate-x-full');
    }, 100);

    setTimeout(() => {
        notification.classList.add('translate-y-[-100px]', 'sm:translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 4000);
}

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
        closeDeleteModal();
        closeImageModal();
    }
});

// Close modals when clicking outside
document.getElementById('waterQualityModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});

// Handle window resize for responsive behavior
window.addEventListener('resize', function() {
    // Close dropdowns on resize to prevent positioning issues
    const dropdown = document.getElementById('pondDropdownMenu');
    if (!dropdown.classList.contains('hidden')) {
        dropdown.classList.add('hidden');
        document.getElementById('pondDropdownIcon').classList.remove('rotate-180');
    }
});

// Prevent zoom on iOS when focusing inputs
if (/iPad|iPhone|iPod/.test(navigator.userAgent)) {
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            input.style.fontSize = '16px';
        });
        input.addEventListener('blur', function() {
            input.style.fontSize = '';
        });
    });
}

// Auto-hide mobile keyboard when scrolling
let lastScrollTop = 0;
window.addEventListener('scroll', function() {
    const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;

    if (currentScrollTop > lastScrollTop) {
        // Scrolling down - blur active input to hide keyboard
        const activeElement = document.activeElement;
        if (activeElement && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA')) {
            activeElement.blur();
        }
    }

    lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop;
}, false);

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Set default date to today
    const dateInput = document.getElementById('date_recorded');
    if (dateInput && !dateInput.value) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }

    // Add loading states to pagination links
    const paginationLinks = document.querySelectorAll('a[href*="page="]');
    paginationLinks.forEach(link => {
        link.addEventListener('click', function() {
            this.style.opacity = '0.6';
            this.style.pointerEvents = 'none';
        });
    });

    // Add input validation hints
    const phInput = document.getElementById('ph');
    const tempInput = document.getElementById('temperature_c');
    const doInput = document.getElementById('do_mg_l');
    const ammoniaInput = document.getElementById('ammonia_mg_l');

    // Add real-time validation feedback
    phInput.addEventListener('input', function() {
        const value = parseFloat(this.value);
        if (value < 6.0 || value > 9.0) {
            this.classList.add('border-red-300');
            this.classList.remove('border-gray-300');
        } else {
            this.classList.remove('border-red-300');
            this.classList.add('border-gray-300');
        }
    });

    tempInput.addEventListener('input', function() {
        const value = parseFloat(this.value);
        if (value < 20 || value > 35) {
            this.classList.add('border-red-300');
            this.classList.remove('border-gray-300');
        } else {
            this.classList.remove('border-red-300');
            this.classList.add('border-gray-300');
        }
    });

    doInput.addEventListener('input', function() {
        const value = parseFloat(this.value);
        if (value < 3) {
            this.classList.add('border-red-300');
            this.classList.remove('border-gray-300');
        } else {
            this.classList.remove('border-red-300');
            this.classList.add('border-gray-300');
        }
    });

    ammoniaInput.addEventListener('input', function() {
        const value = parseFloat(this.value);
        if (value > 0.5) {
            this.classList.add('border-red-300');
            this.classList.remove('border-gray-300');
        } else {
            this.classList.remove('border-red-300');
            this.classList.add('border-gray-300');
        }
    });
});
</script>

<!-- Custom CSS for mobile optimization -->
<style>
/* Mobile-specific styles */
@media (max-width: 640px) {
    /* Ensure modals are properly sized on mobile */
    #waterQualityModal > div,
    #deleteModal > div {
        margin: 1rem;
        max-height: calc(100vh - 2rem);
        overflow-y: auto;
    }

    /* Improve touch targets */
    button, .pond-option {
        min-height: 44px;
    }

    /* Better spacing for mobile cards */
    .mobile-card {
        padding: 1rem;
        margin-bottom: 0.5rem;
    }

    /* Prevent horizontal scroll */
    body {
        overflow-x: hidden;
    }

    /* Improve dropdown positioning on mobile */
    #pondDropdownMenu {
        position: fixed;
        top: auto;
        left: 1rem;
        right: 1rem;
        width: auto;
        max-height: 50vh;
        z-index: 60;
    }

    /* Better notification positioning */
    .notification-mobile {
        left: 1rem;
        right: 1rem;
        width: auto;
    }

    /* Parameter status grid optimization */
    #parameterStatus .grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.5rem;
    }
}

/* Loading animation */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.loading {
    animation: pulse 1.5s ease-in-out infinite;
}

/* Smooth transitions */
.transition-all {
    transition: all 0.3s ease;
}

/* Custom scrollbar for dropdown */
#pondDropdownMenu::-webkit-scrollbar {
    width: 4px;
}

#pondDropdownMenu::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

#pondDropdownMenu::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

#pondDropdownMenu::-webkit-scrollbar-thumb:hover {
    background: #a1a1a1;
}

/* Improve focus states for accessibility */
input:focus, select:focus, textarea:focus, button:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

/* Better hover states on touch devices */
@media (hover: hover) {
    .hover\:bg-gray-50:hover {
        background-color: #f9fafb;
    }

    .hover\:text-blue-900:hover {
        color: #1e3a8a;
    }

    .hover\:text-red-900:hover {
        color: #7f1d1d;
    }
}

/* Prevent text selection on buttons */
button {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
}

/* Improve image loading */
img {
    loading: lazy;
}

/* Better spacing for mobile stats */
@media (max-width: 640px) {
    .stats-grid {
        gap: 0.75rem;
    }

    .stats-card {
        padding: 0.75rem;
    }
}

/* Parameter status indicators */
.parameter-status-good {
    background-color: #10b981;
}

.parameter-status-warning {
    background-color: #f59e0b;
}

.parameter-status-danger {
    background-color: #ef4444;
}

/* Input validation styles */
.border-red-300 {
    border-color: #fca5a5 !important;
    box-shadow: 0 0 0 1px #fca5a5;
}

/* Mobile-friendly parameter cards */
@media (max-width: 640px) {
    .parameter-card {
        padding: 0.5rem;
        border-radius: 0.5rem;
    }

    .parameter-card .text-xs {
        font-size: 0.7rem;
    }
}

/* Responsive grid adjustments */
@media (max-width: 640px) {
    .grid-cols-2 {
        gap: 0.75rem;
    }
}

/* Better touch feedback */
@media (max-width: 640px) {
    button:active, .pond-option:active {
        transform: scale(0.98);
        transition: transform 0.1s ease;
    }
}
</style>
@endsection

