@extends('user.layouts.app')

@section('page-title', 'Pemberian Pakan')

@section('content')
<div class="space-y-4 lg:space-y-6">
    <!-- Stats Cards - Mobile Optimized -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-blue-100">
                    <i class="fas fa-utensils text-blue-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Total</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ $stats['total_feedings'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-green-100">
                    <i class="fas fa-weight text-green-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Pakan</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ number_format($stats['total_feed_kg'], 1) }}</p>
                    <p class="text-xs text-gray-500">Kg</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-orange-100">
                    <i class="fas fa-chart-line text-orange-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Rata-rata</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ number_format($stats['avg_feed_per_day'], 1) }}</p>
                    <p class="text-xs text-gray-500">Kg/hari</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-purple-100">
                    <i class="fas fa-layer-group text-purple-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Batch</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ $stats['active_batches'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedings Content -->
    <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100">
        <!-- Header -->
        <div class="p-4 lg:p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 lg:gap-4">
                <div>
                    <h3 class="text-base lg:text-lg font-semibold text-gray-900">Catatan Pemberian Pakan</h3>
                    <p class="text-xs lg:text-sm text-gray-600 mt-1">{{ $branchInfo->name }}</p>
                </div>
                <button onclick="openAddModal()" class="inline-flex items-center justify-center px-3 lg:px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-1 lg:mr-2"></i>
                    <span class="hidden sm:inline">Tambah Pemberian Pakan</span>
                    <span class="sm:hidden">Tambah</span>
                </button>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="block lg:hidden">
            @forelse($feedings as $feeding)
            <hr><hr><hr>
            <hr><hr><hr>
            <div class="border-b border-gray-100 last:border-b-0 p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <!-- Batch Image -->
                        @if($feeding->batch_image_url)
                            <img class="h-8 w-8 rounded-lg object-cover border border-gray-200 cursor-pointer"
                                 src="{{ $feeding->batch_image_url }}"
                                 alt="Batch #{{ $feeding->batch_id }}"
                                 onclick="showImageModal('{{ $feeding->batch_image_url }}', 'Batch #{{ $feeding->batch_id }}')"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="h-8 w-8 rounded-lg bg-gray-100 border border-gray-200 items-center justify-center hidden">
                                <i class="fas fa-fish text-gray-400 text-xs"></i>
                            </div>
                        @else
                            <div class="h-8 w-8 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center">
                                <i class="fas fa-fish text-gray-400 text-xs"></i>
                            </div>
                        @endif

                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $feeding->formatted_date }}</div>
                            <div class="text-xs text-gray-500">Batch #{{ $feeding->batch_id }}</div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button onclick="editFeeding({{ $feeding->id }})" class="text-blue-600 hover:text-blue-900 p-1" title="Edit">
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                        <button onclick="deleteFeeding({{ $feeding->id }}, '{{ $feeding->formatted_date }}')" class="text-red-600 hover:text-red-900 p-1" title="Hapus">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div>
                        <span class="text-gray-500">Kolam:</span>
                        <div class="font-medium text-gray-900">{{ $feeding->pond_name }}</div>
                    </div>
                    <div>
                        <span class="text-gray-500">Jumlah:</span>
                        <div class="font-medium text-green-600">{{ $feeding->formatted_amount }} kg</div>
                    </div>
                    <div>
                        <span class="text-gray-500">Jenis:</span>
                        <div class="font-medium text-gray-900 truncate">{{ $feeding->feed_type }}</div>
                    </div>
                    <div>
                        <span class="text-gray-500">Umur:</span>
                        <div class="font-medium text-gray-900">{{ $feeding->batch_age_days }}h</div>
                    </div>
                </div>

                @if($feeding->short_notes)
                <div class="mt-2 text-xs text-gray-600 bg-gray-50 rounded p-2">
                    {{ $feeding->short_notes }}
                </div>
                @endif

                <div class="mt-2 flex items-center justify-between">
                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $feeding->fish_type_name }}
                    </span>
                    @if($feeding->created_by_name)
                        <span class="text-xs text-gray-400">{{ $feeding->created_by_name }}</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <i class="fas fa-utensils text-gray-300 text-3xl mb-3"></i>
                <h3 class="text-base font-medium text-gray-900 mb-2">Belum ada catatan</h3>
                <p class="text-sm text-gray-500 mb-4">Mulai dengan menambahkan catatan pemberian pakan.</p>
                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch & Kolam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Pakan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Umur Batch</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($feedings as $feeding)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($feeding->date)->format('d M Y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($feeding->created_at)->format('H:i') }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    @if($feeding->batch_image_url)
                                        <img class="h-8 w-8 rounded-lg object-cover border border-gray-200 cursor-pointer"
                                             src="{{ $feeding->batch_image_url }}"
                                             alt="Batch #{{ $feeding->batch_id }}"
                                             onclick="showImageModal('{{ $feeding->batch_image_url }}', 'Batch #{{ $feeding->batch_id }}')"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="h-8 w-8 rounded-lg bg-gray-100 border border-gray-200 items-center justify-center hidden">
                                            <i class="fas fa-fish text-gray-400 text-xs"></i>
                                        </div>
                                    @else
                                        <div class="h-8 w-8 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center">
                                            <i class="fas fa-fish text-gray-400 text-xs"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-medium text-gray-900">Batch #{{ $feeding->batch_id }}</div>
                                    <div class="text-sm text-gray-500">{{ $feeding->pond_name }} ({{ $feeding->pond_code }})</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                        {{ $feeding->fish_type_name }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $feeding->feed_type }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-green-600">
                                {{ number_format($feeding->feed_amount_kg, 1) }} kg
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $feeding->batch_age_days }} hari</div>
                            <div class="text-xs text-gray-500">{{ floor($feeding->batch_age_days / 7) }} minggu</div>
                        </td>
                        <td class="px-6 py-4">
                            @if($feeding->notes)
                            <div class="text-sm text-gray-900">{{ Str::limit($feeding->notes, 30) }}</div>
                            @else
                            <span class="text-xs text-gray-400">Tidak ada catatan</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($feeding->created_at)->format('d M Y') }}
                            @if($feeding->created_by_name)
                                <div class="text-xs text-gray-400">{{ $feeding->created_by_name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <button onclick="editFeeding({{ $feeding->id }})" class="text-blue-600 hover:text-blue-900 p-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteFeeding({{ $feeding->id }}, '{{ \Carbon\Carbon::parse($feeding->date)->format('d M Y') }}')" class="text-red-600 hover:text-red-900 p-1" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-utensils text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada catatan pemberian pakan</h3>
                                <p class="text-gray-500 mb-4">Mulai dengan menambahkan catatan pemberian pakan pertama untuk cabang ini.</p>
                                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Tambah Pemberian Pakan
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
<div id="feedingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-4 lg:top-20 mx-auto p-4 lg:p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white m-4">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modalTitle" class="text-base lg:text-lg font-medium text-gray-900">Tambah Pemberian Pakan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="feedingForm" class="space-y-4">
                <input type="hidden" id="feedingId" name="id">

                <!-- Custom Batch Dropdown - Mobile Optimized -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Batch Ikan *</label>
                    <div class="relative">
                        <select id="fish_batch_id" name="fish_batch_id" required class="hidden">
                            <option value="">Pilih Batch Ikan</option>
                            @foreach($fishBatches as $batch)
                            <option value="{{ $batch->id }}">
                                Batch #{{ $batch->id }} - {{ $batch->pond_name }} ({{ $batch->fish_type_name }})
                            </option>
                            @endforeach
                        </select>

                        <button type="button" id="batchDropdownBtn" onclick="toggleBatchDropdown()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-left flex items-center justify-between">
                            <div class="flex items-center space-x-2 lg:space-x-3 min-w-0 flex-1">
                                <div id="selectedBatchImage" class="hidden flex-shrink-0">
                                    <img class="h-5 w-5 lg:h-6 lg:w-6 rounded object-cover border border-gray-200" src="" alt="">
                                </div>
                                <span id="selectedBatchText" class="text-gray-500 truncate">Pilih Batch Ikan</span>
                            </div>
                            <i id="batchDropdownIcon" class="fas fa-chevron-down text-gray-400 transition-transform duration-200 flex-shrink-0"></i>
                        </button>

                        <div id="batchDropdownMenu" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 lg:max-h-60 overflow-y-auto hidden">
                            @foreach($fishBatches as $batch)
                                <div class="batch-option px-3 py-2 lg:py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                     data-value="{{ $batch->id }}"
                                     data-batch-id="{{ $batch->id }}"
                                     data-pond-name="{{ $batch->pond_name }}"
                                     data-fish-type="{{ $batch->fish_type_name }}"
                                     data-current-stock="{{ $batch->current_stock }}"
                                     data-age-days="{{ $batch->age_days }}"
                                     data-image="{{ $batch->image_url }}"
                                     onclick="selectBatch(this)">
                                    <div class="flex items-center space-x-2 lg:space-x-3">
                                        <div class="flex-shrink-0">
                                            @if($batch->image_url)
                                                <img class="h-8 w-8 lg:h-10 lg:w-10 rounded-lg object-cover border border-gray-200"
                                                     src="{{ $batch->image_url }}"
                                                     alt="Batch #{{ $batch->id }}"
                                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="h-8 w-8 lg:h-10 lg:w-10 rounded-lg bg-gray-100 border border-gray-200 items-center justify-center hidden">
                                                    <i class="fas fa-fish text-gray-400 text-sm"></i>
                                                </div>
                                            @else
                                                <div class="h-8 w-8 lg:h-10 lg:w-10 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center">
                                                    <i class="fas fa-fish text-gray-400 text-sm"></i>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center space-x-2">
                                                <p class="text-sm font-medium text-gray-900">Batch #{{ $batch->id }}</p>
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    {{ number_format($batch->current_stock) }}
                                                </span>
                                            </div>
                                            <p class="text-xs lg:text-sm text-gray-500 truncate">{{ $batch->pond_name }} ({{ $batch->pond_code }})</p>
                                            <div class="flex items-center space-x-2 mt-1">
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $batch->fish_type_name }}
                                                </span>
                                                <span class="text-xs text-gray-500">{{ $batch->age_days }}h</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div id="batchInfo" class="text-xs text-gray-500 mt-1 hidden">
                        Stok: <span id="currentStock">0</span> ekor | Umur: <span id="batchAge">0</span> hari
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
                        <input type="date" id="date" name="date" required max="{{ date('Y-m-d') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
             </div>

             <div>
                 <label for="feed_amount_kg" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pakan (Kg) *</label>
                 <input type="number" id="feed_amount_kg" name="feed_amount_kg" required min="0.1" step="0.1"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="1.5">
             </div>
         </div>

         <div>
             <label for="feed_type" class="block text-sm font-medium text-gray-700 mb-1">Jenis Pakan *</label>
             <input type="text" id="feed_type" name="feed_type" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Contoh: Pelet Apung, Pakan Alami, dll">
         </div>

         <div>
             <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
             <textarea id="notes" name="notes" rows="3"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Catatan tambahan tentang pemberian pakan..."></textarea>
         </div>

         <div class="flex flex-col-reverse sm:flex-row items-center justify-end space-y-reverse space-y-2 sm:space-y-0 sm:space-x-3 pt-4">
             <button type="button" onclick="closeModal()"
                     class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                 Batal
             </button>
             <button type="submit" id="submitBtn"
                     class="w-full sm:w-auto px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
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
     <h3 class="text-base lg:text-lg font-medium text-gray-900 mb-2">Hapus Data Pemberian Pakan</h3>
     <p class="text-sm text-gray-500 mb-4">
         Apakah Anda yakin ingin menghapus data pemberian pakan tanggal <strong id="deleteFeedingDate"></strong>?
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
let currentFeedingId = null;
let deleteId = null;

// Batch Dropdown Functions
function toggleBatchDropdown() {
const menu = document.getElementById('batchDropdownMenu');
const icon = document.getElementById('batchDropdownIcon');

if (menu.classList.contains('hidden')) {
 menu.classList.remove('hidden');
 icon.classList.add('rotate-180');
} else {
 menu.classList.add('hidden');
 icon.classList.remove('rotate-180');
}
}

function selectBatch(element) {
const batchId = element.dataset.value;
const batchIdNum = element.dataset.batchId;
const pondName = element.dataset.pondName;
const fishType = element.dataset.fishType;
const currentStock = element.dataset.currentStock;
const ageDays = element.dataset.ageDays;
const imageUrl = element.dataset.image;

// Update hidden select
document.getElementById('fish_batch_id').value = batchId;

// Update display
const selectedText = document.getElementById('selectedBatchText');
const selectedImage = document.getElementById('selectedBatchImage');
const batchInfo = document.getElementById('batchInfo');

selectedText.textContent = `Batch #${batchIdNum} - ${pondName} (${fishType})`;
selectedText.classList.remove('text-gray-500');
selectedText.classList.add('text-gray-900');

// Update image
if (imageUrl && imageUrl !== 'null') {
 selectedImage.querySelector('img').src = imageUrl;
 selectedImage.classList.remove('hidden');
} else {
 selectedImage.classList.add('hidden');
}

// Update batch info
document.getElementById('currentStock').textContent = new Intl.NumberFormat().format(currentStock);
document.getElementById('batchAge').textContent = ageDays;
batchInfo.classList.remove('hidden');

// Close dropdown
document.getElementById('batchDropdownMenu').classList.add('hidden');
document.getElementById('batchDropdownIcon').classList.remove('rotate-180');
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
const dropdown = document.getElementById('batchDropdownMenu');
const button = document.getElementById('batchDropdownBtn');

if (!dropdown.contains(event.target) && !button.contains(event.target)) {
 dropdown.classList.add('hidden');
 document.getElementById('batchDropdownIcon').classList.remove('rotate-180');
}
});

// Image Modal Functions
function showImageModal(src, title) {
document.getElementById('modalImage').src = src;
document.getElementById('modalImageTitle').textContent = title;
document.getElementById('imageModal').classList.remove('hidden');
document.body.style.overflow = 'hidden'; // Prevent background scroll
}

function closeImageModal() {
document.getElementById('imageModal').classList.add('hidden');
document.body.style.overflow = 'auto'; // Restore scroll
}

// Modal functions
function openAddModal() {
document.getElementById('modalTitle').textContent = 'Tambah Pemberian Pakan';
document.getElementById('submitText').textContent = 'Simpan';
document.getElementById('feedingForm').reset();
document.getElementById('feedingId').value = '';
currentFeedingId = null;

// Reset batch dropdown
resetBatchDropdown();

document.getElementById('feedingModal').classList.remove('hidden');
document.body.style.overflow = 'hidden'; // Prevent background scroll

// Focus on date field after a short delay to ensure modal is visible
setTimeout(() => {
 document.getElementById('date').focus();
}, 100);
}

function closeModal() {
document.getElementById('feedingModal').classList.add('hidden');
document.body.style.overflow = 'auto'; // Restore scroll
}

function closeDeleteModal() {
document.getElementById('deleteModal').classList.add('hidden');
document.body.style.overflow = 'auto'; // Restore scroll
deleteId = null;
}

function resetBatchDropdown() {
document.getElementById('fish_batch_id').value = '';
document.getElementById('selectedBatchText').textContent = 'Pilih Batch Ikan';
document.getElementById('selectedBatchText').classList.add('text-gray-500');
document.getElementById('selectedBatchText').classList.remove('text-gray-900');
document.getElementById('selectedBatchImage').classList.add('hidden');
document.getElementById('batchInfo').classList.add('hidden');
document.getElementById('batchDropdownMenu').classList.add('hidden');
document.getElementById('batchDropdownIcon').classList.remove('rotate-180');
}

// CRUD functions
function editFeeding(id) {
currentFeedingId = id;
document.getElementById('modalTitle').textContent = 'Edit Pemberian Pakan';
document.getElementById('submitText').textContent = 'Perbarui';

document.getElementById('feedingModal').classList.remove('hidden');
document.body.style.overflow = 'hidden';

// Show loading state
const form = document.getElementById('feedingForm');
form.style.opacity = '0.6';

fetch(`/feedings/${id}`)
 .then(response => response.json())
 .then(result => {
     if (result.success) {
         const data = result.data;
         document.getElementById('feedingId').value = data.id;
         document.getElementById('fish_batch_id').value = data.fish_batch_id;
         document.getElementById('date').value = data.date;
         document.getElementById('feed_type').value = data.feed_type;
         document.getElementById('feed_amount_kg').value = data.feed_amount_kg;
         document.getElementById('notes').value = data.notes || '';

         // Update batch dropdown display
         const selectedBatch = document.querySelector(`[data-value="${data.fish_batch_id}"]`);
         if (selectedBatch) {
             selectBatch(selectedBatch);
         }

         form.style.opacity = '1';
         document.getElementById('date').focus();
     } else {
         showNotification('Error: ' + result.message, 'error');
         closeModal();
     }
 })
 .catch(error => {
     console.error('Error:', error);
     showNotification('Gagal memuat data pemberian pakan', 'error');
     closeModal();
 })
 .finally(() => {
     form.style.opacity = '1';
 });
}

function deleteFeeding(id, date) {
deleteId = id;
document.getElementById('deleteFeedingDate').textContent = date;
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

fetch(`/feedings/${deleteId}`, {
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
 showNotification('Gagal menghapus data pemberian pakan', 'error');
})
.finally(() => {
 deleteText.textContent = 'Hapus';
 deleteLoader.classList.add('hidden');
 deleteBtn.disabled = false;
 closeDeleteModal();
});
}

// Form submission
document.getElementById('feedingForm').addEventListener('submit', function(e) {
e.preventDefault();

const submitBtn = document.getElementById('submitBtn');
const submitText = document.getElementById('submitText');
const submitLoader = document.getElementById('submitLoader');

const formData = new FormData(this);
const isEdit = currentFeedingId !== null;

// Validate batch selection
if (!formData.get('fish_batch_id')) {
 showNotification('Silakan pilih batch ikan terlebih dahulu', 'error');
 return;
}

submitText.textContent = isEdit ? 'Memperbarui...' : 'Menyimpan...';
submitLoader.classList.remove('hidden');
submitBtn.disabled = true;

const url = isEdit ? `/feedings/${currentFeedingId}` : '/feedings';

const data = {
 fish_batch_id: formData.get('fish_batch_id'),
 date: formData.get('date'),
 feed_type: formData.get('feed_type'),
 feed_amount_kg: formData.get('feed_amount_kg'),
 notes: formData.get('notes')
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
document.getElementById('feedingModal').addEventListener('click', function(e) {
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
    const dropdown = document.getElementById('batchDropdownMenu');
    if (!dropdown.classList.contains('hidden')) {
        dropdown.classList.add('hidden');
        document.getElementById('batchDropdownIcon').classList.remove('rotate-180');
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
    const dateInput = document.getElementById('date');
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
});
</script>

<!-- Custom CSS for mobile optimization -->
<style>
/* Mobile-specific styles */
@media (max-width: 640px) {
    /* Ensure modals are properly sized on mobile */
    #feedingModal > div,
    #deleteModal > div {
        margin: 1rem;
        max-height: calc(100vh - 2rem);
        overflow-y: auto;
    }

    /* Improve touch targets */
    button, .batch-option {
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
    #batchDropdownMenu {
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
#batchDropdownMenu::-webkit-scrollbar {
    width: 4px;
}

#batchDropdownMenu::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

#batchDropdownMenu::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

#batchDropdownMenu::-webkit-scrollbar-thumb:hover {
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
</style>
@endsection

