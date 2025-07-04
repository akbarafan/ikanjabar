@extends('user.layouts.app')

@section('page-title', 'Transfer Batch Ikan')

@section('content')
<div class="space-y-4 lg:space-y-6">
    <!-- Stats Cards - Mobile Optimized -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4">
        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-blue-100">
                    <i class="fas fa-exchange-alt text-blue-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Total Transfer</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ $stats['total_transfers'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-green-100">
                    <i class="fas fa-fish text-green-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Ikan Transfer</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ number_format($stats['total_fish_transferred']) }}</p>
                    <p class="text-xs text-gray-500">Ekor</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-orange-100">
                    <i class="fas fa-calendar-alt text-orange-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Bulan Ini</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ $stats['this_month_transfers'] }}</p>
                    <p class="text-xs text-gray-500">Transfer</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-purple-100">
                    <i class="fas fa-layer-group text-purple-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Batch Aktif</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ $stats['active_batches'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Transfers Content -->
    <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100">
        <!-- Header -->
        <div class="p-4 lg:p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 lg:gap-4">
                <div>
                    <h3 class="text-base lg:text-lg font-semibold text-gray-900">Riwayat Transfer Batch</h3>
                    <p class="text-xs lg:text-sm text-gray-600 mt-1">{{ $branchInfo->name }}</p>
                </div>
                <button onclick="openAddModal()" class="inline-flex items-center justify-center px-3 lg:px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-1 lg:mr-2"></i>
                    <span class="hidden sm:inline">Tambah Transfer</span>
                    <span class="sm:hidden">Transfer</span>
                </button>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="block lg:hidden">
            @forelse($transfers as $transfer)
            <hr><hr><hr>
            <hr><hr><hr>
            <div class="border-b border-gray-100 last:border-b-0 p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center space-x-2">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <i class="fas fa-exchange-alt text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $transfer->formatted_date }}</div>
                            <div class="text-xs text-gray-500">{{ $transfer->formatted_count }} ekor</div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button onclick="editTransfer({{ $transfer->id }})" class="text-blue-600 hover:text-blue-900 p-1" title="Edit">
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                        <button onclick="deleteTransfer({{ $transfer->id }}, '{{ $transfer->formatted_date }}')" class="text-red-600 hover:text-red-900 p-1" title="Hapus">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Transfer Flow -->
                <div class="bg-gray-50 rounded-lg p-3 mb-3">
                    <div class="flex items-center justify-between">
                        <!-- Source -->
                        <div class="flex items-center space-x-2 flex-1">
                            @if($transfer->source_batch_image_url)
                                <img class="h-8 w-8 rounded-lg object-cover border border-gray-200 cursor-pointer"
                                     src="{{ $transfer->source_batch_image_url }}"
                                     alt="Source Batch"
                                     onclick="showImageModal('{{ $transfer->source_batch_image_url }}', 'Batch #{{ $transfer->source_batch_id }}')"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="h-8 w-8 rounded-lg bg-gray-100 border border-gray-200 items-center justify-center hidden">
                                    <i class="fas fa-fish text-gray-400 text-xs"></i>
                                </div>
                            @else
                                <div class="h-8 w-8 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center">
                                    <i class="fas fa-fish text-gray-400 text-xs"></i>
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-medium text-gray-900 truncate">Batch #{{ $transfer->source_batch_id }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ $transfer->source_pond_name }}</div>
                            </div>
                        </div>

                        <!-- Arrow -->
                        <div class="px-2">
                            <i class="fas fa-arrow-right text-gray-400 text-sm"></i>
                        </div>

                        <!-- Target -->
                        <div class="flex items-center space-x-2 flex-1">
                            @if($transfer->target_batch_image_url)
                                <img class="h-8 w-8 rounded-lg object-cover border border-gray-200 cursor-pointer"
                                     src="{{ $transfer->target_batch_image_url }}"
                                     alt="Target Batch"
                                     onclick="showImageModal('{{ $transfer->target_batch_image_url }}', 'Batch #{{ $transfer->target_batch_id }}')"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="h-8 w-8 rounded-lg bg-gray-100 border border-gray-200 items-center justify-center hidden">
                                    <i class="fas fa-fish text-gray-400 text-xs"></i>
                                </div>
                            @else
                                <div class="h-8 w-8 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center">
                                    <i class="fas fa-fish text-gray-400 text-xs"></i>
                                </div>
                            @endif
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-medium text-gray-900 truncate">Batch #{{ $transfer->target_batch_id }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ $transfer->target_pond_name }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Additional Info -->
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <span class="text-gray-500">Jenis Asal:</span>
                        <div class="font-medium text-gray-900 truncate">{{ $transfer->source_fish_type }}</div>
                    </div>
                    <div>
                        <span class="text-gray-500">Jenis Tujuan:</span>
                        <div class="font-medium text-gray-900 truncate">{{ $transfer->target_fish_type }}</div>
                    </div>
                </div>

                @if($transfer->short_notes)
                <div class="mt-2 text-xs text-gray-600 bg-gray-50 rounded p-2">
                    {{ $transfer->short_notes }}
                </div>
                @endif

                @if($transfer->created_by_name)
                <div class="mt-2 flex justify-end">
                    <span class="text-xs text-gray-400">{{ $transfer->created_by_name }}</span>
                </div>
                @endif
            </div>
            @empty
            <div class="p-8 text-center">
                <i class="fas fa-exchange-alt text-gray-300 text-3xl mb-3"></i>
                <h3 class="text-base font-medium text-gray-900 mb-2">Belum ada transfer</h3>
                <p class="text-sm text-gray-500 mb-4">Mulai dengan menambahkan transfer batch pertama.</p>
                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Transfer
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transfer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Saat Ini</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transfers as $transfer)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($transfer->date_transfer)->format('d M Y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($transfer->created_at)->format('H:i') }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-3">
                                <!-- Source Batch -->
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        @if($transfer->source_batch_image_url)
                                            <img class="h-8 w-8 rounded-lg object-cover border border-gray-200 cursor-pointer"
                                                 src="{{ $transfer->source_batch_image_url }}"
                                                 alt="Source Batch #{{ $transfer->source_batch_id }}"
                                                 onclick="showImageModal('{{ $transfer->source_batch_image_url }}', 'Batch #{{ $transfer->source_batch_id }}')"
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
                                        <div class="text-sm font-medium text-gray-900">Batch #{{ $transfer->source_batch_id }}</div>
                                        <div class="text-sm text-gray-500">{{ $transfer->source_pond_name }} ({{ $transfer->source_pond_code }})</div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-red-800 mt-1">
                                            Dari: {{ $transfer->source_fish_type }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Arrow -->
                                <div class="flex justify-center">
                                    <i class="fas fa-arrow-down text-gray-400"></i>
                                </div>

                                <!-- Target Batch -->
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        @if($transfer->target_batch_image_url)
                                            <img class="h-8 w-8 rounded-lg object-cover border border-gray-200 cursor-pointer"
                                                 src="{{ $transfer->target_batch_image_url }}"
                                                 alt="Target Batch #{{ $transfer->target_batch_id }}"
                                                 onclick="showImageModal('{{ $transfer->target_batch_image_url }}', 'Batch #{{ $transfer->target_batch_id }}')"
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
                                        <div class="text-sm font-medium text-gray-900">Batch #{{ $transfer->target_batch_id }}</div>
                                        <div class="text-sm text-gray-500">{{ $transfer->target_pond_name }} ({{ $transfer->target_pond_code }})</div>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium text-green-800 mt-1">
                                            Ke: {{ $transfer->target_fish_type }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-blue-600">
                                {{ number_format($transfer->transferred_count) }} ekor
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="space-y-1">
                                <div class="text-sm text-gray-900">
                                    Asal: {{ number_format($transfer->source_current_stock) }} ekor
                                </div>
                                <div class="text-sm text-gray-900">
                                    Tujuan: {{ number_format($transfer->target_current_stock) }} ekor
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            @if($transfer->notes)
                            <div class="text-sm text-gray-900">{{ Str::limit($transfer->notes, 30) }}</div>
                            @else
                            <span class="text-xs text-gray-400">Tidak ada catatan</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($transfer->created_at)->format('d M Y') }}
                            @if($transfer->created_by_name)
                                <div class="text-xs text-gray-400">{{ $transfer->created_by_name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <button onclick="editTransfer({{ $transfer->id }})" class="text-blue-600 hover:text-blue-900 p-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteTransfer({{ $transfer->id }}, '{{ \Carbon\Carbon::parse($transfer->date_transfer)->format('d M Y') }}')" class="text-red-600 hover:text-red-900 p-1" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-exchange-alt text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada transfer batch</h3>
                                <p class="text-gray-500 mb-4">Mulai dengan menambahkan transfer batch pertama untuk cabang ini.</p>
                                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Tambah Transfer Batch
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
<div id="transferModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-4 lg:top-20 mx-auto p-4 lg:p-5 border w-full max-w-3xl shadow-lg rounded-lg bg-white m-4">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modalTitle" class="text-base lg:text-lg font-medium text-gray-900">Tambah Transfer Batch</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="transferForm" class="space-y-4">
                <input type="hidden" id="transferId" name="id">

                <!-- Source Batch Dropdown -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Batch Asal *</label>
                    <div class="relative">
                        <select id="source_batch_id" name="source_batch_id" required class="hidden">
                            <option value="">Pilih Batch Asal</option>
                            @foreach($activeBatches as $batch)
                            <option value="{{ $batch->id }}">
                                Batch #{{ $batch->id }} - {{ $batch->pond_name }} ({{ $batch->fish_type_name }})
                            </option>
                            @endforeach
                        </select>

                        <button type="button" id="sourceBatchDropdownBtn" onclick="toggleSourceBatchDropdown()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-left flex items-center justify-between">
                            <div class="flex items-center space-x-2 lg:space-x-3 min-w-0 flex-1">
                                <div id="selectedSourceBatchImage" class="hidden flex-shrink-0">
                                    <img class="h-5 w-5 lg:h-6 lg:w-6 rounded object-cover border border-gray-200" src="" alt="">
                                </div>
                                <span id="selectedSourceBatchText" class="text-gray-500 truncate">Pilih Batch Asal</span>
                            </div>
                            <i id="sourceBatchDropdownIcon" class="fas fa-chevron-down text-gray-400 transition-transform duration-200 flex-shrink-0"></i>
                        </button>

                        <div id="sourceBatchDropdownMenu" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 lg:max-h-60 overflow-y-auto hidden">
                            @foreach($activeBatches as $batch)
                                <div class="source-batch-option px-3 py-2 lg:py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                     data-value="{{ $batch->id }}"
                                     data-batch-id="{{ $batch->id }}"
                                     data-pond-name="{{ $batch->pond_name }}"
                                     data-fish-type="{{ $batch->fish_type_name }}"
                                     data-current-stock="{{ $batch->current_stock }}"
                                     data-age-days="{{ $batch->age_days }}"
                                     data-image="{{ $batch->image_url }}"
                                     onclick="selectSourceBatch(this)">
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
                    <div id="sourceBatchInfo" class="text-xs text-gray-500 mt-1 hidden">
                        Stok: <span id="sourceCurrentStock">0</span> ekor | Umur: <span id="sourceBatchAge">0</span> hari
                    </div>
                </div>

                <!-- Target Batch Dropdown -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Batch Tujuan *</label>
                    <div class="relative">
                        <select id="target_batch_id" name="target_batch_id" required class="hidden">
                            <option value="">Pilih Batch Tujuan</option>
                            @foreach($activeBatches as $batch)
                            <option value="{{ $batch->id }}">
                                Batch #{{ $batch->id }} - {{ $batch->pond_name }} ({{ $batch->fish_type_name }})
                            </option>
                            @endforeach
                        </select>

                        <button type="button" id="targetBatchDropdownBtn" onclick="toggleTargetBatchDropdown()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white text-left flex items-center justify-between">
                            <div class="flex items-center space-x-2 lg:space-x-3 min-w-0 flex-1">
                                <div id="selectedTargetBatchImage" class="hidden flex-shrink-0">
                                    <img class="h-5 w-5 lg:h-6 lg:w-6 rounded object-cover border border-gray-200" src="" alt="">
                                </div>
                                <span id="selectedTargetBatchText" class="text-gray-500 truncate">Pilih Batch Tujuan</span>
                            </div>
                            <i id="targetBatchDropdownIcon" class="fas fa-chevron-down text-gray-400 transition-transform duration-200 flex-shrink-0"></i>
                        </button>

                        <div id="targetBatchDropdownMenu" class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-48 lg:max-h-60 overflow-y-auto hidden">
                            @foreach($activeBatches as $batch)
                                <div class="target-batch-option px-3 py-2 lg:py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                                     data-value="{{ $batch->id }}"
                                     data-batch-id="{{ $batch->id }}"
                                     data-pond-name="{{ $batch->pond_name }}"
                                     data-fish-type="{{ $batch->fish_type_name }}"
                                     data-current-stock="{{ $batch->current_stock }}"
                                     data-age-days="{{ $batch->age_days }}"
                                     data-image="{{ $batch->image_url }}"
                                     onclick="selectTargetBatch(this)">
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
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium text-green-800">
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
                    <div id="targetBatchInfo" class="text-xs text-gray-500 mt-1 hidden">
                        Stok: <span id="targetCurrentStock">0</span> ekor | Umur: <span id="targetBatchAge">0</span> hari
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label for="date_transfer" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Transfer *</label>
                        <input type="date" id="date_transfer" name="date_transfer" required max="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="transferred_count" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Transfer (Ekor) *</label>
                        <input type="number" id="transferred_count" name="transferred_count" required min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="100">
                        <div id="stockWarning" class="text-xs text-red-500 mt-1 hidden">
                            Jumlah melebihi stok yang tersedia
                        </div>
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea id="notes" name="notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Catatan tambahan tentang transfer batch..."></textarea>
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
                <h3 class="text-base lg:text-lg font-medium text-gray-900 mb-2">Hapus Transfer Batch</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Apakah Anda yakin ingin menghapus data transfer tanggal <strong id="deleteTransferDate"></strong>?
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
    let currentTransferId = null;
    let deleteId = null;
    let sourceCurrentStock = 0;

    // Source Batch Dropdown Functions
    function toggleSourceBatchDropdown() {
        const menu = document.getElementById('sourceBatchDropdownMenu');
        const icon = document.getElementById('sourceBatchDropdownIcon');

        // Close target dropdown if open
        document.getElementById('targetBatchDropdownMenu').classList.add('hidden');
        document.getElementById('targetBatchDropdownIcon').classList.remove('rotate-180');

        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            menu.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    }

    function selectSourceBatch(element) {
        const batchId = element.dataset.value;
        const batchIdNum = element.dataset.batchId;
        const pondName = element.dataset.pondName;
        const fishType = element.dataset.fishType;
        const currentStock = element.dataset.currentStock;
        const ageDays = element.dataset.ageDays;
        const imageUrl = element.dataset.image;

        // Update hidden select
        document.getElementById('source_batch_id').value = batchId;

        // Update display
        const selectedText = document.getElementById('selectedSourceBatchText');
        const selectedImage = document.getElementById('selectedSourceBatchImage');
        const batchInfo = document.getElementById('sourceBatchInfo');

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
        document.getElementById('sourceCurrentStock').textContent = new Intl.NumberFormat().format(currentStock);
        document.getElementById('sourceBatchAge').textContent = ageDays;
        batchInfo.classList.remove('hidden');

        // Store current stock for validation
        sourceCurrentStock = parseInt(currentStock);

        // Close dropdown
        document.getElementById('sourceBatchDropdownMenu').classList.add('hidden');
        document.getElementById('sourceBatchDropdownIcon').classList.remove('rotate-180');

        // Validate transfer count if already entered
        validateTransferCount();
    }

    // Target Batch Dropdown Functions
    function toggleTargetBatchDropdown() {
        const menu = document.getElementById('targetBatchDropdownMenu');
        const icon = document.getElementById('targetBatchDropdownIcon');

        // Close source dropdown if open
        document.getElementById('sourceBatchDropdownMenu').classList.add('hidden');
        document.getElementById('sourceBatchDropdownIcon').classList.remove('rotate-180');

        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            menu.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    }

    function selectTargetBatch(element) {
        const batchId = element.dataset.value;
        const batchIdNum = element.dataset.batchId;
        const pondName = element.dataset.pondName;
        const fishType = element.dataset.fishType;
        const currentStock = element.dataset.currentStock;
        const ageDays = element.dataset.ageDays;
        const imageUrl = element.dataset.image;

        // Update hidden select
        document.getElementById('target_batch_id').value = batchId;

        // Update display
        const selectedText = document.getElementById('selectedTargetBatchText');
        const selectedImage = document.getElementById('selectedTargetBatchImage');
        const batchInfo = document.getElementById('targetBatchInfo');

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
        document.getElementById('targetCurrentStock').textContent = new Intl.NumberFormat().format(currentStock);
        document.getElementById('targetBatchAge').textContent = ageDays;
        batchInfo.classList.remove('hidden');

        // Close dropdown
        document.getElementById('targetBatchDropdownMenu').classList.add('hidden');
        document.getElementById('targetBatchDropdownIcon').classList.remove('rotate-180');
    }

    // Validate transfer count
    function validateTransferCount() {
        const transferCountInput = document.getElementById('transferred_count');
        const stockWarning = document.getElementById('stockWarning');
        const transferCount = parseInt(transferCountInput.value) || 0;

        if (transferCount > sourceCurrentStock && sourceCurrentStock > 0) {
            stockWarning.classList.remove('hidden');
            transferCountInput.classList.add('border-red-500');
            return false;
        } else {
            stockWarning.classList.add('hidden');
            transferCountInput.classList.remove('border-red-500');
            return true;
        }
    }

    // Add event listener for transfer count validation
    document.getElementById('transferred_count').addEventListener('input', validateTransferCount);

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const sourceDropdown = document.getElementById('sourceBatchDropdownMenu');
        const sourceButton = document.getElementById('sourceBatchDropdownBtn');
        const targetDropdown = document.getElementById('targetBatchDropdownMenu');
        const targetButton = document.getElementById('targetBatchDropdownBtn');

        if (!sourceDropdown.contains(event.target) && !sourceButton.contains(event.target)) {
            sourceDropdown.classList.add('hidden');
            document.getElementById('sourceBatchDropdownIcon').classList.remove('rotate-180');
        }

        if (!targetDropdown.contains(event.target) && !targetButton.contains(event.target)) {
            targetDropdown.classList.add('hidden');
            document.getElementById('targetBatchDropdownIcon').classList.remove('rotate-180');
        }
    });

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
        document.getElementById('modalTitle').textContent = 'Tambah Transfer Batch';
        document.getElementById('submitText').textContent = 'Simpan';
        document.getElementById('transferForm').reset();
        document.getElementById('transferId').value = '';
        currentTransferId = null;
        sourceCurrentStock = 0;

        // Reset dropdowns
        resetDropdowns();

        document.getElementById('transferModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Focus on date field after a short delay
        setTimeout(() => {
            document.getElementById('date_transfer').focus();
        }, 100);
    }

    function closeModal() {
        document.getElementById('transferModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        deleteId = null;
    }

    function resetDropdowns() {
        // Reset source batch dropdown
        document.getElementById('source_batch_id').value = '';
        document.getElementById('selectedSourceBatchText').textContent = 'Pilih Batch Asal';
        document.getElementById('selectedSourceBatchText').classList.add('text-gray-500');
        document.getElementById('selectedSourceBatchText').classList.remove('text-gray-900');
        document.getElementById('selectedSourceBatchImage').classList.add('hidden');
        document.getElementById('sourceBatchInfo').classList.add('hidden');
        document.getElementById('sourceBatchDropdownMenu').classList.add('hidden');
        document.getElementById('sourceBatchDropdownIcon').classList.remove('rotate-180');

        // Reset target batch dropdown
        document.getElementById('target_batch_id').value = '';
        document.getElementById('selectedTargetBatchText').textContent = 'Pilih Batch Tujuan';
        document.getElementById('selectedTargetBatchText').classList.add('text-gray-500');
        document.getElementById('selectedTargetBatchText').classList.remove('text-gray-900');
        document.getElementById('selectedTargetBatchImage').classList.add('hidden');
        document.getElementById('targetBatchInfo').classList.add('hidden');
        document.getElementById('targetBatchDropdownMenu').classList.add('hidden');
        document.getElementById('targetBatchDropdownIcon').classList.remove('rotate-180');

        // Reset validation
        document.getElementById('stockWarning').classList.add('hidden');
        document.getElementById('transferred_count').classList.remove('border-red-500');
    }

    // CRUD functions
    function editTransfer(id) {
        currentTransferId = id;
        document.getElementById('modalTitle').textContent = 'Edit Transfer Batch';
        document.getElementById('submitText').textContent = 'Perbarui';

        document.getElementById('transferModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';

        // Show loading state
        const form = document.getElementById('transferForm');
        form.style.opacity = '0.6';

        fetch(`/fish-transfers/${id}`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    const data = result.data;
                    document.getElementById('transferId').value = data.id;
                    document.getElementById('source_batch_id').value = data.source_batch_id;
                    document.getElementById('target_batch_id').value = data.target_batch_id;
                    document.getElementById('date_transfer').value = data.date_transfer;
                    document.getElementById('transferred_count').value = data.transferred_count;
                    document.getElementById('notes').value = data.notes || '';

                    // Update dropdown displays
                    const selectedSourceBatch = document.querySelector(`[data-value="${data.source_batch_id}"].source-batch-option`);
                    if (selectedSourceBatch) {
                        selectSourceBatch(selectedSourceBatch);
                    }

                    const selectedTargetBatch = document.querySelector(`[data-value="${data.target_batch_id}"].target-batch-option`);
                    if (selectedTargetBatch) {
                        selectTargetBatch(selectedTargetBatch);
                    }

                    form.style.opacity = '1';
                    document.getElementById('date_transfer').focus();
                } else {
                    showNotification('Error: ' + result.message, 'error');
                    closeModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Gagal memuat data transfer', 'error');
                closeModal();
            })
            .finally(() => {
                form.style.opacity = '1';
            });
    }

    function deleteTransfer(id, date) {
        deleteId = id;
        document.getElementById('deleteTransferDate').textContent = date;
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

        fetch(`/fish-transfers/${deleteId}`, {
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
        showNotification('Gagal menghapus data transfer', 'error');
    })
    .finally(() => {
        deleteText.textContent = 'Hapus';
        deleteLoader.classList.add('hidden');
        deleteBtn.disabled = false;
        closeDeleteModal();
    });
}

// Form submission
document.getElementById('transferForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitLoader = document.getElementById('submitLoader');

    const formData = new FormData(this);
    const isEdit = currentTransferId !== null;

    // Validate batch selections
    if (!formData.get('source_batch_id')) {
        showNotification('Silakan pilih batch asal terlebih dahulu', 'error');
        return;
    }

    if (!formData.get('target_batch_id')) {
        showNotification('Silakan pilih batch tujuan terlebih dahulu', 'error');
        return;
    }

    if (formData.get('source_batch_id') === formData.get('target_batch_id')) {
        showNotification('Batch asal dan tujuan tidak boleh sama', 'error');
        return;
    }

    // Validate transfer count
    if (!validateTransferCount()) {
        showNotification('Jumlah transfer melebihi stok yang tersedia', 'error');
        return;
    }

    submitText.textContent = isEdit ? 'Memperbarui...' : 'Menyimpan...';
    submitLoader.classList.remove('hidden');
    submitBtn.disabled = true;

    const url = isEdit ? `/fish-transfers/${currentTransferId}` : '/fish-transfers';

    const data = {
        source_batch_id: formData.get('source_batch_id'),
        target_batch_id: formData.get('target_batch_id'),
        transferred_count: formData.get('transferred_count'),
        date_transfer: formData.get('date_transfer'),
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
document.getElementById('transferModal').addEventListener('click', function(e) {
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
    const sourceDropdown = document.getElementById('sourceBatchDropdownMenu');
    const targetDropdown = document.getElementById('targetBatchDropdownMenu');

    if (!sourceDropdown.classList.contains('hidden')) {
        sourceDropdown.classList.add('hidden');
        document.getElementById('sourceBatchDropdownIcon').classList.remove('rotate-180');
    }

    if (!targetDropdown.classList.contains('hidden')) {
        targetDropdown.classList.add('hidden');
        document.getElementById('targetBatchDropdownIcon').classList.remove('rotate-180');
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
    const dateInput = document.getElementById('date_transfer');
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

    // Process transfers data for mobile display
    @foreach($transfers as $transfer)
        // Add formatted data for mobile display
        const transfer{{ $transfer->id }} = {
            formatted_date: '{{ \Carbon\Carbon::parse($transfer->date_transfer)->format("d M") }}',
            formatted_count: '{{ number_format($transfer->transferred_count) }}',
            short_notes: '{{ $transfer->notes ? \Str::limit($transfer->notes, 20) : null }}'
        };
    @endforeach
});

// Batch stock validation helper
async function validateBatchStock(batchId) {
    try {
        const response = await fetch(`/fish-transfers/batch-stock/${batchId}`);
        const result = await response.json();

        if (result.success) {
            return result.current_stock;
        }
        return 0;
    } catch (error) {
        console.error('Error validating batch stock:', error);
        return 0;
    }
}

// Enhanced batch selection with real-time stock validation
async function selectSourceBatchWithValidation(element) {
    selectSourceBatch(element);

    // Get real-time stock data
    const batchId = element.dataset.value;
    const realTimeStock = await validateBatchStock(batchId);

    if (realTimeStock !== sourceCurrentStock) {
        sourceCurrentStock = realTimeStock;
        document.getElementById('sourceCurrentStock').textContent = new Intl.NumberFormat().format(realTimeStock);

        // Re-validate transfer count
        validateTransferCount();
    }
}

// Update batch options to use enhanced validation
document.querySelectorAll('.source-batch-option').forEach(option => {
    option.setAttribute('onclick', 'selectSourceBatchWithValidation(this)');
});

// Keyboard navigation for dropdowns
document.addEventListener('keydown', function(e) {
    const sourceDropdown = document.getElementById('sourceBatchDropdownMenu');
    const targetDropdown = document.getElementById('targetBatchDropdownMenu');

    if (!sourceDropdown.classList.contains('hidden')) {
        handleDropdownKeyNavigation(e, sourceDropdown, 'source-batch-option');
    }

    if (!targetDropdown.classList.contains('hidden')) {
        handleDropdownKeyNavigation(e, targetDropdown, 'target-batch-option');
    }
});

function handleDropdownKeyNavigation(e, dropdown, optionClass) {
    const options = dropdown.querySelectorAll(`.${optionClass}`);
    let currentIndex = -1;

    // Find currently focused option
    options.forEach((option, index) => {
        if (option.classList.contains('bg-blue-50')) {
            currentIndex = index;
        }
    });

    switch(e.key) {
        case 'ArrowDown':
            e.preventDefault();
            if (currentIndex < options.length - 1) {
                if (currentIndex >= 0) options[currentIndex].classList.remove('bg-blue-50');
                options[currentIndex + 1].classList.add('bg-blue-50');
            }
            break;

        case 'ArrowUp':
            e.preventDefault();
            if (currentIndex > 0) {
                options[currentIndex].classList.remove('bg-blue-50');
                options[currentIndex - 1].classList.add('bg-blue-50');
            }
            break;

        case 'Enter':
            e.preventDefault();
            if (currentIndex >= 0) {
                options[currentIndex].click();
            }
            break;

        case 'Escape':
            e.preventDefault();
            dropdown.classList.add('hidden');
            break;
    }
}

// Touch gesture support for mobile
let touchStartY = 0;
let touchEndY = 0;

document.addEventListener('touchstart', function(e) {
    touchStartY = e.changedTouches[0].screenY;
});

document.addEventListener('touchend', function(e) {
    touchEndY = e.changedTouches[0].screenY;
    handleSwipeGesture();
});

function handleSwipeGesture() {
    const swipeThreshold = 50;
    const swipeDistance = touchStartY - touchEndY;

    // Swipe up to close modals
    if (swipeDistance > swipeThreshold) {
        const modals = ['transferModal', 'deleteModal'];
        modals.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (!modal.classList.contains('hidden')) {
                if (modalId === 'transferModal') closeModal();
                if (modalId === 'deleteModal') closeDeleteModal();
            }
        });
    }
}

// Performance optimization: Lazy load images
const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;
            if (img.dataset.src) {
                img.src = img.dataset.src;
                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        }
    });
});

// Apply lazy loading to batch images
document.querySelectorAll('img[data-src]').forEach(img => {
    imageObserver.observe(img);
});

// Accessibility improvements
document.addEventListener('DOMContentLoaded', function() {
    // Add ARIA labels
    document.getElementById('sourceBatchDropdownBtn').setAttribute('aria-label', 'Pilih batch asal');
    document.getElementById('targetBatchDropdownBtn').setAttribute('aria-label', 'Pilih batch tujuan');

    // Add role attributes
    document.getElementById('sourceBatchDropdownMenu').setAttribute('role', 'listbox');
    document.getElementById('targetBatchDropdownMenu').setAttribute('role', 'listbox');

    // Add option roles
    document.querySelectorAll('.source-batch-option, .target-batch-option').forEach(option => {
        option.setAttribute('role', 'option');
        option.setAttribute('tabindex', '0');
    });
});

// Error handling for network issues
window.addEventListener('online', function() {
    showNotification('Koneksi internet tersambung kembali', 'success');
});

window.addEventListener('offline', function() {
    showNotification('Koneksi internet terputus. Beberapa fitur mungkin tidak berfungsi.', 'error');
});

// Auto-save draft functionality (optional)
let draftTimer;
function saveDraft() {
    const formData = new FormData(document.getElementById('transferForm'));
    const draft = {
        source_batch_id: formData.get('source_batch_id'),
        target_batch_id: formData.get('target_batch_id'),
        transferred_count: formData.get('transferred_count'),
        date_transfer: formData.get('date_transfer'),
        notes: formData.get('notes'),
        timestamp: Date.now()
    };

    localStorage.setItem('transfer_draft', JSON.stringify(draft));
}

function loadDraft() {
    const draft = localStorage.getItem('transfer_draft');
    if (draft) {
        const draftData = JSON.parse(draft);

        // Only load if draft is less than 1 hour old
        if (Date.now() - draftData.timestamp < 3600000) {
            if (confirm('Ditemukan draft yang belum disimpan. Muat draft?')) {
                document.getElementById('source_batch_id').value = draftData.source_batch_id || '';
                document.getElementById('target_batch_id').value = draftData.target_batch_id || '';
                document.getElementById('transferred_count').value = draftData.transferred_count || '';
                document.getElementById('date_transfer').value = draftData.date_transfer || '';
                document.getElementById('notes').value = draftData.notes || '';

                // Update dropdown displays if values exist
                if (draftData.source_batch_id) {
                    const sourceOption = document.querySelector(`[data-value="${draftData.source_batch_id}"].source-batch-option`);
                    if (sourceOption) selectSourceBatch(sourceOption);
                }

                if (draftData.target_batch_id) {
                    const targetOption = document.querySelector(`[data-value="${draftData.target_batch_id}"].target-batch-option`);
                    if (targetOption) selectTargetBatch(targetOption);
                }
            }
        }

        // Clear old draft
        localStorage.removeItem('transfer_draft');
    }
}

// Auto-save draft every 30 seconds when modal is open
document.getElementById('transferForm').addEventListener('input', function() {
    clearTimeout(draftTimer);
    draftTimer = setTimeout(saveDraft, 30000);
});

// Clear draft on successful submission
document.getElementById('transferForm').addEventListener('submit', function() {
    localStorage.removeItem('transfer_draft');
});
</script>

<!-- Custom CSS for mobile optimization -->
<style>
/* Mobile-specific styles */
@media (max-width: 640px) {
    /* Ensure modals are properly sized on mobile */
    #transferModal > div,
    #deleteModal > div {
        margin: 1rem;
        max-height: calc(100vh - 2rem);
        overflow-y: auto;
    }

    /* Improve touch targets */
    button, .source-batch-option, .target-batch-option {
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
    #sourceBatchDropdownMenu,
    #targetBatchDropdownMenu {
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

    /* Transfer flow visualization */
    .transfer-flow {
        background: linear-gradient(90deg, #fee2e2 0%, #dbeafe 100%);
        border-radius: 0.5rem;
        padding: 0.75rem;
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

/* Custom scrollbar for dropdowns */
#sourceBatchDropdownMenu::-webkit-scrollbar,
#targetBatchDropdownMenu::-webkit-scrollbar {
    width: 4px;
}

#sourceBatchDropdownMenu::-webkit-scrollbar-track,
#targetBatchDropdownMenu::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 2px;
}

#sourceBatchDropdownMenu::-webkit-scrollbar-thumb,
#targetBatchDropdownMenu::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 2px;
}

#sourceBatchDropdownMenu::-webkit-scrollbar-thumb:hover,
#targetBatchDropdownMenu::-webkit-scrollbar-thumb:hover {
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
</style>
@endsection
