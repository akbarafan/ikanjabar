@extends('user.layouts.app')

@section('page-title', 'Transfer Batch')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <i class="fas fa-exchange-alt text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Transfer</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_transfers'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <i class="fas fa-fish text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Ikan Dipindah</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_fish_transferred']) }}</p>
                    <p class="text-xs text-gray-500">Ekor</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <i class="fas fa-calendar-alt text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Bulan Ini</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['this_month_transfers'] }}</p>
                    <p class="text-xs text-gray-500">Transfer</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100">
                    <i class="fas fa-layer-group text-orange-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Batch Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active_batches'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Transfer Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Riwayat Transfer Batch</h3>
                    <p class="text-sm text-gray-600 mt-1">Kelola transfer batch ikan di cabang {{ $branchInfo->name }}</p>
                </div>
                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Transfer Batch
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transfer Info</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dari</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ke</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transfers as $transfer)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-medium text-gray-900">Transfer #{{ $transfer->id }}</div>
                                @if($transfer->notes)
                                <div class="text-xs text-gray-400 mt-1">{{ Str::limit($transfer->notes, 40) }}</div>
                                @endif
                                @if($transfer->created_by_name)
                                <div class="text-xs text-gray-500 mt-1">{{ $transfer->created_by_name }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $transfer->source_pond_name }}</div>
                                <div class="text-sm text-gray-500">{{ $transfer->source_pond_code }}</div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mt-1">
                                    {{ $transfer->source_fish_type }}
                                </span>
                                <div class="text-xs text-gray-400 mt-1">Batch #{{ $transfer->source_batch_id }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $transfer->target_pond_name }}</div>
                                <div class="text-sm text-gray-500">{{ $transfer->target_pond_code }}</div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 mt-1">
                                    {{ $transfer->target_fish_type }}
                                </span>
                                <div class="text-xs text-gray-400 mt-1">Batch #{{ $transfer->target_batch_id }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ number_format($transfer->transferred_count) }} ekor</div>
                            <div class="text-xs text-gray-500">
                                <i class="fas fa-arrow-right mr-1"></i>
                                {{ number_format($transfer->source_current_stock) }} → {{ number_format($transfer->target_current_stock) }}
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($transfer->date_transfer)->format('d M Y') }}
                            <div class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($transfer->created_at)->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <button onclick="editTransfer({{ $transfer->id }})" class="text-blue-600 hover:text-blue-900 p-1">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteTransfer({{ $transfer->id }}, 'Transfer #{{ $transfer->id }}')" class="text-red-600 hover:text-red-900 p-1">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-exchange-alt text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada transfer batch</h3>
                                <p class="text-gray-500 mb-4">Mulai dengan melakukan transfer batch pertama.</p>
                                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Transfer Batch
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
<div id="transferModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Transfer Batch Ikan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="transferForm" class="space-y-4">
                <input type="hidden" id="transferId" name="id">

                <div>
                    <label for="source_batch_id" class="block text-sm font-medium text-gray-700 mb-1">Batch Asal *</label>
                    <select id="source_batch_id" name="source_batch_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Batch Asal</option>
                        @foreach($activeBatches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->fish_type_name }} - {{ $batch->pond_name }} ({{ $batch->pond_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="target_batch_id" class="block text-sm font-medium text-gray-700 mb-1">Batch Tujuan *</label>
                    <select id="target_batch_id" name="target_batch_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Batch Tujuan</option>
                        @foreach($activeBatches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->fish_type_name }} - {{ $batch->pond_name }} ({{ $batch->pond_code }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="transferred_count" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Transfer *</label>
                        <input type="number" id="transferred_count" name="transferred_count" required min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="100">
                    </div>

                    <div>
                        <label for="date_transfer" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Transfer *</label>
                        <input type="date" id="date_transfer" name="date_transfer" required max="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea id="notes" name="notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Alasan transfer, kondisi ikan, dll..."></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeModal()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Batal
                </button>
                <button type="submit" id="submitBtn"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
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
        <h3 class="text-lg font-medium text-gray-900 mb-2">Hapus Transfer Batch</h3>
        <p class="text-sm text-gray-500 mb-4">
            Apakah Anda yakin ingin menghapus <strong id="deleteTransferName"></strong>?
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
let currentTransferId = null;
let deleteId = null;

// Modal functions
function openAddModal() {
document.getElementById('modalTitle').textContent = 'Transfer Batch Ikan';
document.getElementById('submitText').textContent = 'Simpan';
document.getElementById('transferForm').reset();
document.getElementById('transferId').value = '';
currentTransferId = null;
document.getElementById('transferModal').classList.remove('hidden');
document.getElementById('source_batch_id').focus();
}

function closeModal() {
document.getElementById('transferModal').classList.add('hidden');
}

function closeDeleteModal() {
document.getElementById('deleteModal').classList.add('hidden');
deleteId = null;
}

// CRUD functions
function editTransfer(id) {
currentTransferId = id;
document.getElementById('modalTitle').textContent = 'Edit Transfer Batch';
document.getElementById('submitText').textContent = 'Perbarui';

document.getElementById('transferModal').classList.remove('hidden');

fetch(`/fish-transfers/${id}`)
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            document.getElementById('transferId').value = result.data.id;
            document.getElementById('source_batch_id').value = result.data.source_batch_id;
            document.getElementById('target_batch_id').value = result.data.target_batch_id;
            document.getElementById('transferred_count').value = result.data.transferred_count;
            document.getElementById('date_transfer').value = result.data.date_transfer;
            document.getElementById('notes').value = result.data.notes || '';
            document.getElementById('source_batch_id').focus();
        } else {
            showNotification('Error: ' + result.message, 'error');
            closeModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Gagal memuat data transfer', 'error');
        closeModal();
    });
}

function deleteTransfer(id, name) {
deleteId = id;
document.getElementById('deleteTransferName').textContent = name;
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
    showNotification('Gagal menghapus transfer', 'error');
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

// Prevent selecting same batch for source and target
document.getElementById('source_batch_id').addEventListener('change', function() {
const sourceValue = this.value;
const targetSelect = document.getElementById('target_batch_id');

// Reset target if same as source
if (targetSelect.value === sourceValue) {
    targetSelect.value = '';
}
});

document.getElementById('target_batch_id').addEventListener('change', function() {
const targetValue = this.value;
const sourceSelect = document.getElementById('source_batch_id');

// Reset source if same as target
if (sourceSelect.value === targetValue) {
    sourceSelect.value = '';
}
});
</script>
@endsection
