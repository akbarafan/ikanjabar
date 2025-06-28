@extends('user.layouts.app')

@section('page-title', 'Mortalitas Ikan')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100">
                    <i class="fas fa-skull-crossbones text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Catatan</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_records'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100">
                    <i class="fas fa-fish text-orange-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Kematian</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_dead_fish']) }}</p>
                    <p class="text-xs text-gray-500">Ekor</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100">
                    <i class="fas fa-percentage text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Rata-rata Tingkat</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['avg_mortality_rate'], 1) }}%</p>
                    <p class="text-xs text-gray-500">Mortalitas</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <i class="fas fa-layer-group text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Batch Terdampak</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['affected_batches'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Mortalities Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Catatan Mortalitas</h3>
                    <p class="text-sm text-gray-600 mt-1">Kelola data mortalitas ikan di cabang {{ $branchInfo->name }}</p>
                </div>
                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Mortalitas
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch & Kolam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Sebelum</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kematian</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tingkat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penyebab</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Umur Batch</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($mortalities as $mortality)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">
                                {{ \Carbon\Carbon::parse($mortality->date)->format('d M Y') }}
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($mortality->created_at)->format('H:i') }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">Batch #{{ $mortality->batch_id }}</div>
                            <div class="text-sm text-gray-500">{{ $mortality->pond_name }} ({{ $mortality->pond_code }})</div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                {{ $mortality->fish_type_name }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">
                                <span class="font-medium">{{ number_format($mortality->stock_before_mortality) }}</span> ekor
                            </div>
                            <div class="text-xs text-gray-500">
                                Awal: {{ number_format($mortality->initial_count) }}
                                @if($mortality->transferred_in > 0)
                                <br><span class="text-green-600">+{{ number_format($mortality->transferred_in) }} transfer</span>
                                @endif
                                @if($mortality->transferred_out > 0)
                                <br><span class="text-red-600">-{{ number_format($mortality->transferred_out) }} transfer</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-red-600">
                                {{ number_format($mortality->dead_count) }} ekor
                            </div>
                            <div class="text-xs text-gray-500">
                                Sisa: {{ number_format($mortality->current_stock) }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $mortality->mortality_rate < 5 ? 'bg-green-100 text-green-800' :
                                   ($mortality->mortality_rate < 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ number_format($mortality->mortality_rate, 1) }}%
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($mortality->cause)
                            <div class="text-sm text-gray-900">{{ Str::limit($mortality->cause, 30) }}</div>
                            @else
                            <span class="text-xs text-gray-400">Tidak ada catatan</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $mortality->batch_age_days }} hari</div>
                            <div class="text-xs text-gray-500">{{ floor($mortality->batch_age_days / 7) }} minggu</div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <button onclick="editMortality({{ $mortality->id }})" class="text-blue-600 hover:text-blue-900 p-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteMortality({{ $mortality->id }}, '{{ \Carbon\Carbon::parse($mortality->date)->format('d M Y') }}')" class="text-red-600 hover:text-red-900 p-1" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-skull-crossbones text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada catatan mortalitas</h3>
                                <p class="text-gray-500 mb-4">Mulai dengan menambahkan catatan mortalitas pertama untuk cabang ini.</p>
                                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Tambah Mortalitas
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="mortalityModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Tambah Mortalitas</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="mortalityForm" class="space-y-4">
                <input type="hidden" id="mortalityId" name="id">

                <div>
                    <label for="fish_batch_id" class="block text-sm font-medium text-gray-700 mb-1">Batch Ikan *</label>
                    <select id="fish_batch_id" name="fish_batch_id" required onchange="updateStockInfo()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Batch Ikan</option>
                        @foreach($fishBatches as $batch)
                        <option value="{{ $batch->id }}">
                            Batch #{{ $batch->id }} - {{ $batch->pond_name }} ({{ $batch->fish_type_name }})
                        </option>
                        @endforeach
                    </select>
                    <div id="stockInfo" class="text-xs text-gray-500 mt-1 hidden">
                        Stok saat ini: <span id="currentStock">0</span> ekor
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
                        <input type="date" id="date" name="date" required max="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="dead_count" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Mati *</label>
                        <input type="number" id="dead_count" name="dead_count" required min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="0">
                    </div>
                </div>

                <div>
                    <label for="cause" class="block text-sm font-medium text-gray-700 mb-1">Penyebab Kematian</label>
                    <textarea id="cause" name="cause" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Jelaskan penyebab kematian (opsional)..."></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit" id="submitBtn"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                        <span id="submitText">Simpan</span>
                        <i id="submitLoader" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <i class="fas fa-exclamation-triangle text-red-600"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Hapus Data Mortalitas</h3>
            <p class="text-sm text-gray-500 mb-4">
                Apakah Anda yakin ingin menghapus data mortalitas tanggal <strong id="deleteMortalityDate"></strong>?
                Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="flex items-center justify-center space-x-3">
                <button onclick="closeDeleteModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Batal
                </button>
                <button onclick="confirmDelete()" id="deleteBtn"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors">
                    <span id="deleteText">Hapus</span>
                    <i id="deleteLoader" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentMortalityId = null;
let deleteId = null;

// Modal functions
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Mortalitas';
    document.getElementById('submitText').textContent = 'Simpan';
    document.getElementById('mortalityForm').reset();
    document.getElementById('mortalityId').value = '';
    document.getElementById('stockInfo').classList.add('hidden');
    currentMortalityId = null;
    document.getElementById('mortalityModal').classList.remove('hidden');
    document.getElementById('fish_batch_id').focus();
}

function closeModal() {
    document.getElementById('mortalityModal').classList.add('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    deleteId = null;
}

// Update stock info when batch is selected
function updateStockInfo() {
    const batchId = document.getElementById('fish_batch_id').value;
    const stockInfo = document.getElementById('stockInfo');
    const currentStockSpan = document.getElementById('currentStock');

    if (!batchId) {
        stockInfo.classList.add('hidden');
        return;
    }

    // Show loading
    currentStockSpan.textContent = 'Loading...';
    stockInfo.classList.remove('hidden');

    fetch(`/mortalities/batch-stock/${batchId}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                currentStockSpan.textContent = new Intl.NumberFormat().format(result.current_stock);

                // Update max value for dead_count input
                document.getElementById('dead_count').max = result.current_stock;

                if (result.current_stock === 0) {
                    currentStockSpan.innerHTML = '<span class="text-red-600">0 (Tidak ada stok)</span>';
                }
            } else {
                currentStockSpan.textContent = 'Error';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            currentStockSpan.textContent = 'Error';
        });
}

// CRUD functions
function editMortality(id) {
    currentMortalityId = id;
    document.getElementById('modalTitle').textContent = 'Edit Mortalitas';
    document.getElementById('submitText').textContent = 'Perbarui';

    document.getElementById('mortalityModal').classList.remove('hidden');

    fetch(`/mortalities/${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                document.getElementById('mortalityId').value = result.data.id;
                document.getElementById('fish_batch_id').value = result.data.fish_batch_id;
                document.getElementById('date').value = result.data.date;
                document.getElementById('dead_count').value = result.data.dead_count;
                document.getElementById('cause').value = result.data.cause || '';

                // Update stock info
                updateStockInfo();

                document.getElementById('fish_batch_id').focus();
            } else {
                showNotification('Error: ' + result.message, 'error');
                closeModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Gagal memuat data mortalitas', 'error');
            closeModal();
        });
}

function deleteMortality(id, date) {
    deleteId = id;
    document.getElementById('deleteMortalityDate').textContent = date;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function confirmDelete() {
    if (!deleteId) return;

    const deleteBtn = document.getElementById('deleteBtn');
    const deleteText = document.getElementById('deleteText');
    const deleteLoader = document.getElementById('deleteLoader');

    deleteText.textContent = 'Menghapus...';
    deleteLoader.classList.remove('hidden');
    deleteBtn.disabled = true;

    fetch(`/mortalities/${deleteId}`, {
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
        showNotification('Gagal menghapus data mortalitas', 'error');
    })
    .finally(() => {
        deleteText.textContent = 'Hapus';
        deleteLoader.classList.add('hidden');
        deleteBtn.disabled = false;
        closeDeleteModal();
    });
}

// Form submission
document.getElementById('mortalityForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitLoader = document.getElementById('submitLoader');

    const formData = new FormData(this);
    const isEdit = currentMortalityId !== null;

    submitText.textContent = isEdit ? 'Memperbarui...' : 'Menyimpan...';
    submitLoader.classList.remove('hidden');
    submitBtn.disabled = true;

    const url = isEdit ? `/mortalities/${currentMortalityId}` : '/mortalities';

    const data = {
        fish_batch_id: formData.get('fish_batch_id'),
        date: formData.get('date'),
        dead_count: formData.get('dead_count'),
        cause: formData.get('cause')
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
    }, 4000);
}

// Close modals on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
        closeDeleteModal();
    }
});

// Close modals when clicking outside
document.getElementById('mortalityModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

// Validate dead count doesn't exceed current stock
document.getElementById('dead_count').addEventListener('input', function() {
    const maxStock = parseInt(this.max);
    const inputValue = parseInt(this.value);

    if (inputValue > maxStock && maxStock > 0) {
        this.setCustomValidity(`Jumlah kematian tidak boleh melebihi stok yang tersedia (${maxStock})`);
    } else {
        this.setCustomValidity('');
    }
});
</script>
@endsection
