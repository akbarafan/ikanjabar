@extends('user.layouts.app')

@section('page-title', 'Penjualan Ikan')

@section('content')
<div class="space-y-4 lg:space-y-6">
    <!-- Stats Cards - Mobile Optimized -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-green-100">
                    <i class="fas fa-shopping-cart text-green-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Total Penjualan</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ $stats['total_sales'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-blue-100">
                    <i class="fas fa-money-bill-wave text-blue-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Pendapatan</p>
                    <p class="text-sm lg:text-lg font-bold text-gray-900">Rp {{ number_format($stats['total_revenue'] / 1000000, 1) }}M</p>
                    <p class="text-xs text-gray-500">Total</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-orange-100">
                    <i class="fas fa-fish text-orange-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Ikan Terjual</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ number_format($stats['total_fish_sold']) }}</p>
                    <p class="text-xs text-gray-500">Ekor</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-purple-100">
                    <i class="fas fa-chart-line text-purple-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Bulan Ini</p>
                    <p class="text-sm lg:text-lg font-bold text-gray-900">Rp {{ number_format($stats['this_month_revenue'] / 1000, 0) }}K</p>
                    <p class="text-xs text-gray-500">Pendapatan</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Content -->
    <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100">
        <!-- Header -->
        <div class="p-4 lg:p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 lg:gap-4">
                <div>
                    <h3 class="text-base lg:text-lg font-semibold text-gray-900">Catatan Penjualan</h3>
                    <p class="text-xs lg:text-sm text-gray-600 mt-1">{{ $branchInfo->name }}</p>
                </div>
                <button onclick="openAddModal()" class="inline-flex items-center justify-center px-3 lg:px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-1 lg:mr-2"></i>
                    <span class="hidden sm:inline">Tambah Penjualan</span>
                    <span class="sm:hidden">Tambah</span>
                </button>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="block lg:hidden">
            @forelse($sales as $sale)
            <hr><hr><hr>
            <hr><hr><hr>
            <div class="border-b border-gray-100 last:border-b-0 p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between mb-2">
                    <div class="flex items-center space-x-2">
                        <!-- Batch Image -->
                        @if($sale->batch_image_url)
                            <img class="h-8 w-8 rounded-lg object-cover border border-gray-200 cursor-pointer"
                                 src="{{ $sale->batch_image_url }}"
                                 alt="Batch #{{ $sale->batch_id }}"
                                 onclick="showImageModal('{{ $sale->batch_image_url }}', 'Batch #{{ $sale->batch_id }}')"
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
                            <div class="text-sm font-medium text-gray-900">{{ $sale->formatted_date }}</div>
                            <div class="text-xs text-gray-500">Batch #{{ $sale->batch_id }}</div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button onclick="editSale({{ $sale->id }})" class="text-blue-600 hover:text-blue-900 p-1" title="Edit">
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                        <button onclick="deleteSale({{ $sale->id }}, '{{ $sale->formatted_date }}')" class="text-red-600 hover:text-red-900 p-1" title="Hapus">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                    <div>
                        <span class="text-gray-500">Pembeli:</span>
                        <div class="font-medium text-gray-900 truncate">{{ $sale->short_buyer_name }}</div>
                    </div>
                    <div>
                        <span class="text-gray-500">Jumlah:</span>
                        <div class="font-medium text-green-600">{{ $sale->formatted_quantity }} ekor</div>
                    </div>
                    <div>
                        <span class="text-gray-500">Berat:</span>
                        <div class="font-medium text-gray-900">{{ $sale->formatted_weight }}</div>
                    </div>
                    <div>
                        <span class="text-gray-500">Total:</span>
                        <div class="font-medium text-green-600">{{ $sale->formatted_total_price }}</div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $sale->fish_type_name }}
                        </span>
                        <span class="text-xs text-gray-500">{{ $sale->pond_name }}</span>
                    </div>
                    @if($sale->created_by_name)
                        <span class="text-xs text-gray-400">{{ $sale->created_by_name }}</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <i class="fas fa-shopping-cart text-gray-300 text-3xl mb-3"></i>
                <h3 class="text-base font-medium text-gray-900 mb-2">Belum ada penjualan</h3>
                <p class="text-sm text-gray-500 mb-4">Mulai dengan menambahkan catatan penjualan pertama.</p>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembeli</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Berat & Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($sale->created_at)->format('H:i') }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    @if($sale->batch_image_url)
                                        <img class="h-8 w-8 rounded-lg object-cover border border-gray-200 cursor-pointer"
                                             src="{{ $sale->batch_image_url }}"
                                             alt="Batch #{{ $sale->batch_id }}"
                                             onclick="showImageModal('{{ $sale->batch_image_url }}', 'Batch #{{ $sale->batch_id }}')"
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
                                    <div class="text-sm font-medium text-gray-900">Batch #{{ $sale->batch_id }}</div>
                                    <div class="text-sm text-gray-500">{{ $sale->pond_name }} ({{ $sale->pond_code }})</div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                        {{ $sale->fish_type_name }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $sale->buyer_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-green-600">
                                {{ number_format($sale->quantity_fish) }} ekor
                            </div>
                            <div class="text-xs text-gray-500">
                                Umur: {{ $sale->batch_age_days }} hari
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                {{ number_format($sale->total_weight_kg, 1) }} kg
                            </div>
                            <div class="text-xs text-gray-500">
                                Rp {{ number_format($sale->price_per_kg) }}/kg
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-green-600">
                                Rp {{ number_format($sale->total_price) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($sale->created_at)->format('d M Y') }}
                            @if($sale->created_by_name)
                                <div class="text-xs text-gray-400">{{ $sale->created_by_name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <button onclick="editSale({{ $sale->id }})" class="text-blue-600 hover:text-blue-900 p-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteSale({{ $sale->id }}, '{{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}')" class="text-red-600 hover:text-red-900 p-1" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-shopping-cart text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada catatan penjualan</h3>
                                <p class="text-gray-500 mb-4">Mulai dengan menambahkan catatan penjualan pertama untuk cabang ini.</p>
                                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Tambah Penjualan
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
<div id="saleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-4 lg:top-20 mx-auto p-4 lg:p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white m-4">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modalTitle" class="text-base lg:text-lg font-medium text-gray-900">Tambah Penjualan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="saleForm" class="space-y-4">
                <input type="hidden" id="saleId" name="id">

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
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white text-left flex items-center justify-between">
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
                            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Penjualan *</label>
                            <input type="date" id="date" name="date" required max="{{ date('Y-m-d') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>

                        <div>
                            <label for="buyer_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Pembeli *</label>
                            <input type="text" id="buyer_name" name="buyer_name" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="Nama pembeli">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label for="quantity_fish" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Ikan (Ekor) *</label>
                            <input type="number" id="quantity_fish" name="quantity_fish" required min="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="100" onchange="calculateTotal()">
                        </div>

                        <div>
                            <label for="avg_weight_per_fish_kg" class="block text-sm font-medium text-gray-700 mb-1">Berat Rata-rata per Ekor (Kg) *</label>
                            <input type="number" id="avg_weight_per_fish_kg" name="avg_weight_per_fish_kg" required min="0.01" step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="0.5" onchange="calculateTotal()">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label for="price_per_kg" class="block text-sm font-medium text-gray-700 mb-1">Harga per Kg (Rp) *</label>
                            <input type="number" id="price_per_kg" name="price_per_kg" required min="0.01" step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                   placeholder="25000" onchange="calculateTotal()">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Harga</label>
                            <div class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-900 font-medium">
                                <span id="totalPrice">Rp 0</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                Total berat: <span id="totalWeight">0 kg</span>
                            </div>
                        </div>
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
                <h3 class="text-base lg:text-lg font-medium text-gray-900 mb-2">Hapus Data Penjualan</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Apakah Anda yakin ingin menghapus data penjualan tanggal <strong id="deleteSaleDate"></strong>?
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
    let currentSaleId = null;
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

        // Update max value for quantity input
        document.getElementById('quantity_fish').max = currentStock;

        if (currentStock == 0) {
            document.getElementById('currentStock').innerHTML = '<span class="text-red-600">0 (Tidak ada stok)</span>';
        }

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

    // Calculate total price
    function calculateTotal() {
        const quantity = parseFloat(document.getElementById('quantity_fish').value) || 0;
        const avgWeight = parseFloat(document.getElementById('avg_weight_per_fish_kg').value) || 0;
        const pricePerKg = parseFloat(document.getElementById('price_per_kg').value) || 0;

        const totalWeight = quantity * avgWeight;
        const totalPrice = totalWeight * pricePerKg;

        document.getElementById('totalWeight').textContent = totalWeight.toFixed(1) + ' kg';
        document.getElementById('totalPrice').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalPrice);
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
        document.getElementById('modalTitle').textContent = 'Tambah Penjualan';
        document.getElementById('submitText').textContent = 'Simpan';
        document.getElementById('saleForm').reset();
        document.getElementById('saleId').value = '';
        currentSaleId = null;

        // Reset batch dropdown
        resetBatchDropdown();

        // Reset total calculation
        document.getElementById('totalPrice').textContent = 'Rp 0';
        document.getElementById('totalWeight').textContent = '0 kg';

        document.getElementById('saleModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Focus on date field after a short delay
        setTimeout(() => {
            document.getElementById('date').focus();
        }, 100);
    }

    function closeModal() {
        document.getElementById('saleModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
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
function editSale(id) {
    currentSaleId = id;
    document.getElementById('modalTitle').textContent = 'Edit Penjualan';
    document.getElementById('submitText').textContent = 'Perbarui';

    document.getElementById('saleModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Show loading state
    const form = document.getElementById('saleForm');
    form.style.opacity = '0.6';

    fetch(`/sales/${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const data = result.data;
                document.getElementById('saleId').value = data.id;
                document.getElementById('fish_batch_id').value = data.fish_batch_id;
                document.getElementById('date').value = data.date;
                document.getElementById('buyer_name').value = data.buyer_name;
                document.getElementById('quantity_fish').value = data.quantity_fish;
                document.getElementById('avg_weight_per_fish_kg').value = data.avg_weight_per_fish_kg;
                document.getElementById('price_per_kg').value = data.price_per_kg;

                // Update batch dropdown display
                const selectedBatch = document.querySelector(`[data-value="${data.fish_batch_id}"]`);
                if (selectedBatch) {
                    selectBatch(selectedBatch);
                }

                // Calculate total
                calculateTotal();

                form.style.opacity = '1';
                document.getElementById('date').focus();
            } else {
                showNotification('Error: ' + result.message, 'error');
                closeModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Gagal memuat data penjualan', 'error');
            closeModal();
        })
        .finally(() => {
            form.style.opacity = '1';
        });
}

function deleteSale(id, date) {
    deleteId = id;
    document.getElementById('deleteSaleDate').textContent = date;
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

    fetch(`/sales/${deleteId}`, {
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
        showNotification('Gagal menghapus data penjualan', 'error');
    })
    .finally(() => {
        deleteText.textContent = 'Hapus';
        deleteLoader.classList.add('hidden');
        deleteBtn.disabled = false;
        closeDeleteModal();
    });
}

// Form submission
document.getElementById('saleForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitLoader = document.getElementById('submitLoader');

    const formData = new FormData(this);
    const isEdit = currentSaleId !== null;

    // Validate batch selection
    if (!formData.get('fish_batch_id')) {
        showNotification('Silakan pilih batch ikan terlebih dahulu', 'error');
        return;
    }

    // Validate stock
    const quantity = parseInt(formData.get('quantity_fish'));
    const maxStock = parseInt(document.getElementById('quantity_fish').max);

    if (quantity > maxStock && maxStock > 0) {
        showNotification(`Jumlah ikan melebihi stok yang tersedia (${maxStock} ekor)`, 'error');
        return;
    }

    submitText.textContent = isEdit ? 'Memperbarui...' : 'Menyimpan...';
    submitLoader.classList.remove('hidden');
    submitBtn.disabled = true;

    const url = isEdit ? `/sales/${currentSaleId}` : '/sales';

    const data = {
        fish_batch_id: formData.get('fish_batch_id'),
        date: formData.get('date'),
        buyer_name: formData.get('buyer_name'),
        quantity_fish: formData.get('quantity_fish'),
        avg_weight_per_fish_kg: formData.get('avg_weight_per_fish_kg'),
        price_per_kg: formData.get('price_per_kg')
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
document.getElementById('saleModal').addEventListener('click', function(e) {
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

// Validate quantity doesn't exceed current stock
document.getElementById('quantity_fish').addEventListener('input', function() {
    const maxStock = parseInt(this.max);
    const inputValue = parseInt(this.value);

    if (inputValue > maxStock && maxStock > 0) {
        this.setCustomValidity(`Jumlah ikan tidak boleh melebihi stok yang tersedia (${maxStock})`);
    } else {
        this.setCustomValidity('');
    }
});

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

    // Add auto-calculation listeners
    ['quantity_fish', 'avg_weight_per_fish_kg', 'price_per_kg'].forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('input', calculateTotal);
        }
    });
});
</script>

<!-- Custom CSS for mobile optimization -->
<style>
/* Mobile-specific styles */
@media (max-width: 640px) {
    /* Ensure modals are properly sized on mobile */
    #saleModal > div,
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
    outline: 2px solid #10b981;
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

/* Price formatting */
.price-display {
    font-variant-numeric: tabular-nums;
}

/* Batch dropdown improvements */
.batch-option:last-child {
    border-bottom: none;
}

.batch-option:hover {
    background-color: #f9fafb;
}

/* Form validation styles */
.invalid-input {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.valid-input {
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

/* Mobile table improvements */
@media (max-width: 1024px) {
    .mobile-table-card {
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        margin-bottom: 0.75rem;
    }
}

/* Sticky header for mobile */
@media (max-width: 640px) {
    .sticky-header {
        position: sticky;
        top: 0;
        z-index: 10;
        background: white;
        border-bottom: 1px solid #e5e7eb;
    }
}
</style>
@endsection

