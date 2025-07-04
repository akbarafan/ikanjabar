@extends('user.layouts.app')

@section('page-title', 'Batch Ikan')

@section('content')
<div class="space-y-4 lg:space-y-6">
    <!-- Stats Cards - Mobile Optimized -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 lg:gap-4">
        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-blue-100">
                    <i class="fas fa-layer-group text-blue-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Total</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ $stats['total_batches'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-green-100">
                    <i class="fas fa-play text-green-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Aktif</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ $stats['active_batches'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-purple-100">
                    <i class="fas fa-fish text-purple-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Stok</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ number_format($stats['total_current_stock']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-orange-100">
                    <i class="fas fa-calendar text-orange-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Rata Umur</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ $stats['avg_age_days'] }}</p>
                    <p class="text-xs text-gray-500">Hari</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100 p-3 lg:p-6 col-span-2 lg:col-span-1">
            <div class="flex items-center">
                <div class="p-2 lg:p-3 rounded-full bg-red-100">
                    <i class="fas fa-exchange-alt text-red-600 text-sm lg:text-xl"></i>
                </div>
                <div class="ml-2 lg:ml-4 min-w-0 flex-1">
                    <p class="text-xs lg:text-sm font-medium text-gray-600 truncate">Transfer</p>
                    <p class="text-lg lg:text-2xl font-bold text-gray-900">{{ number_format($stats['total_transferred']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Fish Batches Content -->
    <div class="bg-white rounded-lg lg:rounded-xl shadow-sm border border-gray-100">
        <!-- Header -->
        <div class="p-4 lg:p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 lg:gap-4">
                <div>
                    <h3 class="text-base lg:text-lg font-semibold text-gray-900">Batch Ikan</h3>
                    <p class="text-xs lg:text-sm text-gray-600 mt-1">{{ $branchInfo->name }}</p>
                </div>
                <button onclick="openAddModal()" class="inline-flex items-center justify-center px-3 lg:px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-1 lg:mr-2"></i>
                    <span class="hidden sm:inline">Tambah Batch Ikan</span>
                    <span class="sm:hidden">Tambah</span>
                </button>
            </div>
        </div>

        <!-- Mobile Card View -->
        <div class="block lg:hidden">
            @forelse($fishBatches as $batch)
            <hr><hr><hr>
            <hr><hr><hr>
            <div class="border-b border-gray-100 last:border-b-0 p-4 hover:bg-gray-50 transition-colors">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center space-x-3">
                        <!-- Batch Image -->
                        @if($batch->image_url)
                            <img class="h-10 w-10 rounded-lg object-cover border border-gray-200 cursor-pointer"
                                 src="{{ $batch->image_url }}"
                                 alt="Batch #{{ $batch->id }}"
                                 onclick="showImageModal('{{ $batch->image_url }}', 'Batch #{{ $batch->id }}')"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="h-10 w-10 rounded-lg bg-gray-100 border border-gray-200 items-center justify-center hidden">
                                <i class="fas fa-fish text-gray-400"></i>
                            </div>
                        @else
                            <div class="h-10 w-10 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center">
                                <i class="fas fa-fish text-gray-400"></i>
                            </div>
                        @endif

                        <div>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm font-medium text-gray-900">Batch #{{ $batch->id }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    @if($batch->status === 'new') bg-green-100 text-green-800
                                    @elseif($batch->status === 'growing') bg-blue-100 text-blue-800
                                    @elseif($batch->status === 'mature') bg-purple-100 text-purple-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    @if($batch->status === 'new') Baru
                                    @elseif($batch->status === 'growing') Tumbuh
                                    @elseif($batch->status === 'mature') Dewasa
                                    @else Selesai @endif
                                </span>
                            </div>
                            <div class="text-xs text-gray-500">{{ $batch->formatted_date }} • {{ $batch->age_days }} hari</div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <button onclick="editBatch({{ $batch->id }})" class="text-blue-600 hover:text-blue-900 p-1" title="Edit">
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                        <button onclick="deleteBatch({{ $batch->id }}, 'Batch #{{ $batch->id }}')" class="text-red-600 hover:text-red-900 p-1" title="Hapus">
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 text-sm mb-3">
                    <div>
                        <span class="text-gray-500">Kolam:</span>
                        <div class="font-medium text-gray-900">{{ $batch->pond_name }}</div>
                    </div>
                    <div>
                        <span class="text-gray-500">Stok Saat Ini:</span>
                        <div class="font-medium text-green-600">{{ $batch->formatted_stock }} ekor</div>
                    </div>
                    <div>
                        <span class="text-gray-500">Jenis Ikan:</span>
                        <div class="font-medium text-gray-900">{{ $batch->fish_type_name }}</div>
                    </div>
                    <div>
                        <span class="text-gray-500">Awal:</span>
                        <div class="font-medium text-gray-900">{{ number_format($batch->initial_count) }} ekor</div>
                    </div>
                </div>

                @if($batch->short_notes)
                <div class="text-xs text-gray-600 bg-gray-50 rounded p-2 mb-2">
                    {{ $batch->short_notes }}
                </div>
                @endif

                <!-- Stock Summary for Mobile -->
                <div class="flex items-center justify-between text-xs">
                    <div class="flex items-center space-x-3">
                        @if($batch->sold > 0)
                            <span class="text-blue-600">Terjual: {{ number_format($batch->sold) }}</span>
                        @endif
                        @if($batch->mortality > 0)
                            <span class="text-red-600">Mati: {{ number_format($batch->mortality) }}</span>
                        @endif
                        @if($batch->transferred_out > 0)
                            <span class="text-orange-600">Transfer: {{ number_format($batch->transferred_out) }}</span>
                        @endif
                    </div>
                    @if($batch->created_by_name)
                        <span class="text-gray-400">{{ $batch->created_by_name }}</span>
                    @endif
                </div>
            </div>
            @empty
            <div class="p-8 text-center">
                <i class="fas fa-fish text-gray-300 text-3xl mb-3"></i>
                <h3 class="text-base font-medium text-gray-900 mb-2">Belum ada batch ikan</h3>
                <p class="text-sm text-gray-500 mb-4">Mulai dengan menambahkan batch ikan pertama.</p>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch & Info</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kolam & Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Umur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktivitas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($fishBatches as $batch)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0">
                                    @if($batch->image_url)
                                        <img class="h-10 w-10 rounded-lg object-cover border border-gray-200 cursor-pointer"
                                             src="{{ $batch->image_url }}"
                                             alt="Batch #{{ $batch->id }}"
                                             onclick="showImageModal('{{ $batch->image_url }}', 'Batch #{{ $batch->id }}')"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="h-10 w-10 rounded-lg bg-gray-100 border border-gray-200 items-center justify-center hidden">
                                            <i class="fas fa-fish text-gray-400"></i>
                                        </div>
                                    @else
                                        <div class="h-10 w-10 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center">
                                            <i class="fas fa-fish text-gray-400"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-sm font-medium text-gray-900">Batch #{{ $batch->id }}</div>
                                    <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($batch->date_start)->format('d M Y') }}</div>
                                    @if($batch->notes)
                                        <div class="text-xs text-gray-400 mt-1">{{ Str::limit($batch->notes, 40) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center space-x-2">
                                @if($batch->pond_image_url)
                                    <img class="h-6 w-6 rounded object-cover border border-gray-200 cursor-pointer"
                                         src="{{ $batch->pond_image_url }}"
                                         alt="Kolam"
                                         onclick="showImageModal('{{ $batch->pond_image_url }}', '{{ $batch->pond_name }}')"
                                         onerror="this.style.display='none';">
                                @endif
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $batch->pond_name }}</div>
                                    <div class="text-sm text-gray-500">({{ $batch->pond_code }})</div>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                {{ $batch->fish_type_name }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-green-600">
                                {{ number_format($batch->current_stock) }} ekor
                            </div>
                            <div class="text-xs text-gray-500">
                                dari {{ number_format($batch->initial_count) }} awal
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $batch->age_days }} hari</div>
                            <div class="text-xs text-gray-500">{{ $batch->age_weeks }} minggu</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                @if($batch->status === 'new') bg-green-100 text-green-800
                                @elseif($batch->status === 'growing') bg-blue-100 text-blue-800
                                @elseif($batch->status === 'mature') bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800 @endif">
                                @if($batch->status === 'new') Baru
                                @elseif($batch->status === 'growing') Tumbuh
                                @elseif($batch->status === 'mature') Dewasa
                                @else Selesai @endif
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-xs space-y-1">
                                @if($batch->sold > 0)
                                    <div class="text-blue-600">Terjual: {{ number_format($batch->sold) }}</div>
                                @endif
                                @if($batch->mortality > 0)
                                    <div class="text-red-600">Mati: {{ number_format($batch->mortality) }}</div>
                                @endif
                                @if($batch->transferred_out > 0)
                                    <div class="text-orange-600">Transfer: {{ number_format($batch->transferred_out) }}</div>
                                @endif
                                @if($batch->transferred_in > 0)
                                    <div class="text-green-600">Masuk: {{ number_format($batch->transferred_in) }}</div>
                                @endif
                                @if($batch->sold == 0 && $batch->mortality == 0 && $batch->transferred_out == 0 && $batch->transferred_in == 0)
                                    <span class="text-gray-400">Belum ada aktivitas</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($batch->created_at)->format('d M Y') }}
                            @if($batch->created_by_name)
                                <div class="text-xs text-gray-400">{{ $batch->created_by_name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <button onclick="editBatch({{ $batch->id }})" class="text-blue-600 hover:text-blue-900 p-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteBatch({{ $batch->id }}, 'Batch #{{ $batch->id }}')" class="text-red-600 hover:text-red-900 p-1" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-fish text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada batch ikan</h3>
                                <p class="text-gray-500 mb-4">Mulai dengan menambahkan batch ikan pertama untuk cabang ini.</p>
                                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Tambah Batch Ikan
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
<div id="batchModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
<div class="relative top-4 lg:top-20 mx-auto p-4 lg:p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white m-4">
    <div class="mt-3">
        <div class="flex items-center justify-between mb-4">
            <h3 id="modalTitle" class="text-base lg:text-lg font-medium text-gray-900">Tambah Batch Ikan</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 p-1">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="batchForm" class="space-y-4" enctype="multipart/form-data">
            <input type="hidden" id="batchId" name="id">

            <!-- Pond Selection - Mobile Optimized -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kolam *</label>
                <div class="relative">
                    <select id="pond_id" name="pond_id" required class="hidden">
                        <option value="">Pilih Kolam</option>
                        @foreach($ponds as $pond)
                        <option value="{{ $pond->id }}">{{ $pond->name }} ({{ $pond->code }})</option>
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
                                 data-name="{{ $pond->name }}"
                                 data-code="{{ $pond->code }}"
                                 data-type="{{ $pond->type }}"
                                 data-volume="{{ $pond->volume_liters }}"
                                 data-image="{{ $pond->image_url }}"
                                 onclick="selectPond(this)">
                                <div class="flex items-center space-x-2 lg:space-x-3">
                                    <div class="flex-shrink-0">
                                        @if($pond->image_url)
                                            <img class="h-8 w-8 lg:h-10 lg:w-10 rounded-lg object-cover border border-gray-200"
                                                 src="{{ $pond->image_url }}"
                                                 alt="{{ $pond->name }}"
                                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                            <div class="h-8 w-8 lg:h-10 lg:w-10 rounded-lg bg-gray-100 border border-gray-200 items-center justify-center hidden">
                                                <i class="fas fa-water text-gray-400 text-sm"></i>
                                            </div>
                                        @else
                                            <div class="h-8 w-8 lg:h-10 lg:w-10 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center">
                                                <i class="fas fa-water text-gray-400 text-sm"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900">{{ $pond->name }}</p>
                                        <p class="text-xs lg:text-sm text-gray-500">{{ $pond->code }} • {{ $pond->type }}</p>
                                        <p class="text-xs text-gray-400">{{ number_format($pond->volume_liters) }} L</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Fish Type Selection -->
            <div>
                <label for="fish_type_id" class="block text-sm font-medium text-gray-700 mb-1">Jenis Ikan *</label>
                <select id="fish_type_id" name="fish_type_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Pilih Jenis Ikan</option>
                    @foreach($fishTypes as $fishType)
                    <option value="{{ $fishType->id }}">{{ $fishType->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label for="date_start" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai *</label>
                    <input type="date" id="date_start" name="date_start" required max="{{ date('Y-m-d') }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <label for="initial_count" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Awal (Ekor) *</label>
                    <input type="number" id="initial_count" name="initial_count" required min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="1000">
                </div>
            </div>

            <div>
                <label for="documentation_file" class="block text-sm font-medium text-gray-700 mb-1">Foto Dokumentasi</label>
                <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-gray-400 transition-colors">
                    <div class="space-y-1 text-center">
                        <div id="imagePreview" class="hidden mb-4">
                            <img id="previewImg" src="" alt="Preview" class="mx-auto h-32 w-32 object-cover rounded-lg">
                        </div>
                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <div class="flex text-sm text-gray-600">
                            <label for="documentation_file" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                <span>Upload foto</span>
                                <input id="documentation_file" name="documentation_file" type="file" class="sr-only" accept="image/*" onchange="previewImage(this)">
                            </label>
                            <p class="pl-1">atau drag and drop</p>
                        </div>
                        <p class="text-xs text-gray-500">PNG, JPG, GIF hingga 2MB</p>
                    </div>
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                <textarea id="notes" name="notes" rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Catatan tambahan tentang batch ikan..."></textarea>
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
        <h3 class="text-base lg:text-lg font-medium text-gray-900 mb-2">Hapus Batch Ikan</h3>
        <p class="text-sm text-gray-500 mb-4">
            Apakah Anda yakin ingin menghapus <strong id="deleteBatchName"></strong>?
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
let currentBatchId = null;
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
    const pondName = element.dataset.name;
    const pondCode = element.dataset.code;
    const pondType = element.dataset.type;
    const imageUrl = element.dataset.image;

    // Update hidden select
    document.getElementById('pond_id').value = pondId;

    // Update display
    const selectedText = document.getElementById('selectedPondText');
    const selectedImage = document.getElementById('selectedPondImage');

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

// Image Preview Function
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        }

        reader.readAsDataURL(input.files[0]);
    } else {
        preview.classList.add('hidden');
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
    document.getElementById('modalTitle').textContent = 'Tambah Batch Ikan';
    document.getElementById('submitText').textContent = 'Simpan';
    document.getElementById('batchForm').reset();
    document.getElementById('batchId').value = '';
    currentBatchId = null;

    // Reset pond dropdown
    resetPondDropdown();

    // Reset image preview
    document.getElementById('imagePreview').classList.add('hidden');

    document.getElementById('batchModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Focus on date field after a short delay
    setTimeout(() => {
        document.getElementById('date_start').focus();
    }, 100);
}

function closeModal() {
    document.getElementById('batchModal').classList.add('hidden');
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
    document.getElementById('pondDropdownMenu').classList.add('hidden');
    document.getElementById('pondDropdownIcon').classList.remove('rotate-180');
}

// CRUD functions
function editBatch(id) {
    currentBatchId = id;
    document.getElementById('modalTitle').textContent = 'Edit Batch Ikan';
    document.getElementById('submitText').textContent = 'Perbarui';

    document.getElementById('batchModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Show loading state
    const form = document.getElementById('batchForm');
    form.style.opacity = '0.6';

    fetch(`/fish-batches/${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const data = result.data;
                document.getElementById('batchId').value = data.id;
                document.getElementById('pond_id').value = data.pond_id;
                document.getElementById('fish_type_id').value = data.fish_type_id;
                document.getElementById('date_start').value = data.date_start;
                document.getElementById('initial_count').value = data.initial_count;
                document.getElementById('notes').value = data.notes || '';

                // Update pond dropdown display
                const selectedPond = document.querySelector(`[data-value="${data.pond_id}"]`);
                if (selectedPond) {
                    selectPond(selectedPond);
                }

                // Show existing image if available
                if (data.image_url) {
                    document.getElementById('previewImg').src = data.image_url;
                    document.getElementById('imagePreview').classList.remove('hidden');
                }

                form.style.opacity = '1';
                document.getElementById('date_start').focus();
            } else {
                showNotification('Error: ' + result.message, 'error');
                closeModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Gagal memuat data batch ikan', 'error');
            closeModal();
        })
        .finally(() => {
            form.style.opacity = '1';
        });
}

function deleteBatch(id, name) {
    deleteId = id;
    document.getElementById('deleteBatchName').textContent = name;
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

    fetch(`/fish-batches/${deleteId}`, {
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
        showNotification('Gagal menghapus batch ikan', 'error');
    })
    .finally(() => {
        deleteText.textContent = 'Hapus';
        deleteLoader.classList.add('hidden');
        deleteBtn.disabled = false;
        closeDeleteModal();
    });
}

// Form submission
document.getElementById('batchForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitLoader = document.getElementById('submitLoader');

    const formData = new FormData(this);
    const isEdit = currentBatchId !== null;

    // Validate pond selection
    if (!formData.get('pond_id')) {
        showNotification('Silakan pilih kolam terlebih dahulu', 'error');
        return;
    }

    submitText.textContent = isEdit ? 'Memperbarui...' : 'Menyimpan...';
    submitLoader.classList.remove('hidden');
    submitBtn.disabled = true;

    const url = isEdit ? `/fish-batches/${currentBatchId}` : '/fish-batches';

    if (isEdit) {
        formData.append('_method', 'PUT');
    }

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: formData
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
document.getElementById('batchModal').addEventListener('click', function(e) {
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

// Drag and drop functionality for file upload
const fileInput = document.getElementById('documentation_file');
const dropZone = fileInput.closest('.border-dashed');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

['dragenter', 'dragover'].forEach(eventName => {
    dropZone.addEventListener(eventName, highlight, false);
});

['dragleave', 'drop'].forEach(eventName => {
    dropZone.addEventListener(eventName, unhighlight, false);
});

function highlight(e) {
    dropZone.classList.add('border-blue-400', 'bg-blue-50');
}

function unhighlight(e) {
    dropZone.classList.remove('border-blue-400', 'bg-blue-50');
}

dropZone.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    const dt = e.dataTransfer;
    const files = dt.files;

    if (files.length > 0) {
        fileInput.files = files;
        previewImage(fileInput);
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Set default date to today
    const dateInput = document.getElementById('date_start');
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

    // Process batch data for mobile display
    @foreach($fishBatches as $batch)
        // Add formatted data for mobile cards
        const batch{{ $batch->id }} = document.querySelector(`[data-batch-id="{{ $batch->id }}"]`);
        if (batch{{ $batch->id }}) {
            batch{{ $batch->id }}.setAttribute('data-formatted-date', '{{ \Carbon\Carbon::parse($batch->date_start)->format('d M') }}');
            batch{{ $batch->id }}.setAttribute('data-formatted-stock', '{{ number_format($batch->current_stock) }}');
            batch{{ $batch->id }}.setAttribute('data-short-notes', '{{ $batch->notes ? \Str::limit($batch->notes, 20) : '' }}');
        }
    @endforeach
});

// File size validation
document.getElementById('documentation_file').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        // Check file size (2MB = 2 * 1024 * 1024 bytes)
        if (file.size > 2 * 1024 * 1024) {
            showNotification('Ukuran file terlalu besar. Maksimal 2MB.', 'error');
            this.value = '';
            document.getElementById('imagePreview').classList.add('hidden');
            return;
        }

        // Check file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!allowedTypes.includes(file.type)) {
            showNotification('Tipe file tidak didukung. Gunakan JPG, PNG, atau GIF.', 'error');
            this.value = '';
            document.getElementById('imagePreview').classList.add('hidden');
            return;
        }

        previewImage(this);
    }
});

// Add touch feedback for mobile buttons
if ('ontouchstart' in window) {
    const buttons = document.querySelectorAll('button, .batch-option, .pond-option');
    buttons.forEach(button => {
        button.addEventListener('touchstart', function() {
            this.style.transform = 'scale(0.98)';
        });

        button.addEventListener('touchend', function() {
            this.style.transform = 'scale(1)';
        });
    });
}

// Optimize scroll performance
let ticking = false;

function updateScrollPosition() {
    // Add any scroll-based optimizations here
    ticking = false;
}

window.addEventListener('scroll', function() {
    if (!ticking) {
        requestAnimationFrame(updateScrollPosition);
        ticking = true;
    }
});

// Add connection status indicator for mobile
function checkConnection() {
    if (!navigator.onLine) {
        showNotification('Koneksi internet terputus', 'error');
    }
}

window.addEventListener('online', function() {
    showNotification('Koneksi internet tersambung kembali', 'success');
});

window.addEventListener('offline', function() {
    showNotification('Koneksi internet terputus', 'error');
});

// Lazy loading for images
const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src;
            img.classList.remove('lazy');
            observer.unobserve(img);
        }
    });
});

document.querySelectorAll('img[data-src]').forEach(img => {
    imageObserver.observe(img);
});
</script>

<!-- Custom CSS for mobile optimization -->
<style>
/* Mobile-specific styles */
@media (max-width: 640px) {
    /* Ensure modals are properly sized on mobile */
    #batchModal > div,
    #deleteModal > div {
        margin: 1rem;
        max-height: calc(100vh - 2rem);
        overflow-y: auto;
    }

    /* Improve touch targets */
    button, .batch-option, .pond-option {
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

    /* Better file upload area */
    .border-dashed {
        padding: 1rem;
    }

    /* Optimize image preview */
    #previewImg {
        max-height: 120px;
        max-width: 120px;
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

    .hover\:border-gray-400:hover {
        border-color: #9ca3af;
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

img.lazy {
    opacity: 0;
    transition: opacity 0.3s;
}

img.lazy.loaded {
    opacity: 1;
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

/* Status badge colors */
.status-new {
    background-color: #dcfce7;
    color: #166534;
}

.status-growing {
    background-color: #dbeafe;
    color: #1d4ed8;
}

.status-mature {
    background-color: #e9d5ff;
    color: #7c3aed;
}

.status-finished {
    background-color: #f3f4f6;
    color: #374151;
}

/* File upload drag and drop styles */
.border-dashed.drag-over {
    border-color: #3b82f6;
    background-color: #eff6ff;
}

/* Touch feedback */
@media (hover: none) {
    button:active,
    .batch-option:active,
    .pond-option:active {
        transform: scale(0.98);
        transition: transform 0.1s;
    }
}

/* Optimize for small screens */
@media (max-width: 375px) {
    .grid-cols-2 {
        gap: 0.5rem;
    }

    .p-3 {
        padding: 0.5rem;
    }

    .text-lg {
        font-size: 1rem;
    }
}

/* Dark mode support (if needed) */
@media (prefers-color-scheme: dark) {
    /* Add dark mode styles here if needed */
}

/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}

/* Print styles */
@media print {
    .no-print {
        display: none !important;
    }

    .print-full-width {
        width: 100% !important;
    }
}
</style>
@endsection


