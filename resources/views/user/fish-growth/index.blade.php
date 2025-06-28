@extends('user.layouts.app')

@section('page-title', 'Pertumbuhan Ikan')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <i class="fas fa-chart-line text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Record</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_records'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <i class="fas fa-weight text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Rata-rata Berat</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['avg_weight'], 1) }}</p>
                    <p class="text-xs text-gray-500">Gram</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <i class="fas fa-ruler text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Rata-rata Panjang</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['avg_length'], 1) }}</p>
                    <p class="text-xs text-gray-500">CM</p>
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

    <!-- Fish Growth Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Data Pertumbuhan Ikan</h3>
                    <p class="text-sm text-gray-600 mt-1">Monitor pertumbuhan ikan di cabang {{ $branchInfo->name }}</p>
                </div>
                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Data
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Info</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Minggu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Berat (g)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Panjang (cm)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pertumbuhan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($growthLogs as $log)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $log->fish_type_name }}</div>
                                <div class="text-sm text-gray-500">{{ $log->pond_name }} ({{ $log->pond_code }})</div>
                                <div class="text-xs text-gray-400">Batch #{{ $log->batch_id }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Minggu {{ $log->week_number }}
                            </span>
                            <div class="text-xs text-gray-500 mt-1">{{ $log->batch_age_days }} hari</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ number_format($log->avg_weight_gram, 1) }}g</div>
                            @if($log->weight_growth != 0)
                            <div class="text-xs {{ $log->weight_growth > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $log->weight_growth > 0 ? '+' : '' }}{{ number_format($log->weight_growth, 1) }}g
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ number_format($log->avg_length_cm, 1) }}cm</div>
                            @if($log->length_growth != 0)
                            <div class="text-xs {{ $log->length_growth > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $log->length_growth > 0 ? '+' : '' }}{{ number_format($log->length_growth, 1) }}cm
                            </div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($log->weight_growth > 0 && $log->length_growth > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-arrow-up mr-1"></i>Baik
                            </span>
                            @elseif($log->weight_growth > 0 || $log->length_growth > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                <i class="fas fa-minus mr-1"></i>Normal
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                <i class="fas fa-circle mr-1"></i>Awal
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($log->date_recorded)->format('d M Y') }}
                            @if($log->created_by_name)
                            <div class="text-xs text-gray-400">{{ $log->created_by_name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <button onclick="editGrowth({{ $log->id }})" class="text-blue-600 hover:text-blue-900 p-1">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteGrowth({{ $log->id }}, 'Minggu {{ $log->week_number }} - {{ $log->fish_type_name }}')" class="text-red-600 hover:text-red-900 p-1">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-chart-line text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada data pertumbuhan</h3>
                                <p class="text-gray-500 mb-4">Mulai dengan menambahkan data pertumbuhan ikan pertama.</p>
                                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Tambah Data
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
<div id="growthModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Tambah Data Pertumbuhan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="growthForm" class="space-y-4">
                <input type="hidden" id="growthId" name="id">

                <div>
                    <label for="fish_batch_id" class="block text-sm font-medium text-gray-700 mb-1">Batch Ikan *</label>
                    <select id="fish_batch_id" name="fish_batch_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Batch Ikan</option>
                        @foreach($fishBatches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->fish_type_name }} - {{ $batch->pond_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="week_number" class="block text-sm font-medium text-gray-700 mb-1">Minggu Ke *</label>
                        <input type="number" id="week_number" name="week_number" required min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="1">
                    </div>

                    <div>
                        <label for="date_recorded" class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
                        <input type="date" id="date_recorded" name="date_recorded" required max="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="avg_weight_gram" class="block text-sm font-medium text-gray-700 mb-1">Berat Rata-rata (g) *</label>
                        <input type="number" id="avg_weight_gram" name="avg_weight_gram" required min="0" step="0.1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="50.5">
                    </div>

                    <div>
                        <label for="avg_length_cm" class="block text-sm font-medium text-gray-700 mb-1">Panjang Rata-rata (cm) *</label>
                        <input type="number" id="avg_length_cm" name="avg_length_cm" required min="0" step="0.1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="12.5">
                    </div>
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
            <h3 class="text-lg font-medium text-gray-900 mb-2">Hapus Data Pertumbuhan</h3>
            <p class="text-sm text-gray-500 mb-4">
                Apakah Anda yakin ingin menghapus data <strong id="deleteGrowthName"></strong>?
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
let currentGrowthId = null;
let deleteId = null;

// Modal functions
function openAddModal() {
document.getElementById('modalTitle').textContent = 'Tambah Data Pertumbuhan';
document.getElementById('submitText').textContent = 'Simpan';
document.getElementById('growthForm').reset();
document.getElementById('growthId').value = '';
currentGrowthId = null;
document.getElementById('growthModal').classList.remove('hidden');
document.getElementById('fish_batch_id').focus();
}

function closeModal() {
document.getElementById('growthModal').classList.add('hidden');
}

function closeDeleteModal() {
document.getElementById('deleteModal').classList.add('hidden');
deleteId = null;
}

// CRUD functions
function editGrowth(id) {
currentGrowthId = id;
document.getElementById('modalTitle').textContent = 'Edit Data Pertumbuhan';
document.getElementById('submitText').textContent = 'Perbarui';

document.getElementById('growthModal').classList.remove('hidden');

fetch(`/fish-growth/${id}`)
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            document.getElementById('growthId').value = result.data.id;
            document.getElementById('fish_batch_id').value = result.data.fish_batch_id;
            document.getElementById('week_number').value = result.data.week_number;
            document.getElementById('avg_weight_gram').value = result.data.avg_weight_gram;
            document.getElementById('avg_length_cm').value = result.data.avg_length_cm;
            document.getElementById('date_recorded').value = result.data.date_recorded;
            document.getElementById('fish_batch_id').focus();
        } else {
            showNotification('Error: ' + result.message, 'error');
            closeModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Gagal memuat data pertumbuhan', 'error');
        closeModal();
    });
}

function deleteGrowth(id, name) {
deleteId = id;
document.getElementById('deleteGrowthName').textContent = name;
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

fetch(`/fish-growth/${deleteId}`, {
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
    showNotification('Gagal menghapus data pertumbuhan', 'error');
})
.finally(() => {
    deleteText.textContent = 'Hapus';
    deleteLoader.classList.add('hidden');
    deleteBtn.disabled = false;
    closeDeleteModal();
});
}

// Form submission
document.getElementById('growthForm').addEventListener('submit', function(e) {
e.preventDefault();

const submitBtn = document.getElementById('submitBtn');
const submitText = document.getElementById('submitText');
const submitLoader = document.getElementById('submitLoader');

const formData = new FormData(this);
const isEdit = currentGrowthId !== null;

submitText.textContent = isEdit ? 'Memperbarui...' : 'Menyimpan...';
submitLoader.classList.remove('hidden');
submitBtn.disabled = true;

const url = isEdit ? `/fish-growth/${currentGrowthId}` : '/fish-growth';

const data = {
    fish_batch_id: formData.get('fish_batch_id'),
    week_number: formData.get('week_number'),
    avg_weight_gram: formData.get('avg_weight_gram'),
    avg_length_cm: formData.get('avg_length_cm'),
    date_recorded: formData.get('date_recorded')
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
document.getElementById('growthModal').addEventListener('click', function(e) {
if (e.target === this) {
    closeModal();
}
});

document.getElementById('deleteModal').addEventListener('click', function(e) {
if (e.target === this) {
    closeDeleteModal();
}
});
</script>
@endsection
