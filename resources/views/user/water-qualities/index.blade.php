@extends('user.layouts.app')

@section('page-title', 'Kualitas Air')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <i class="fas fa-tint text-blue-600 text-xl"></i>
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
                    <i class="fas fa-calendar-day text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['records_today'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-emerald-100">
                    <i class="fas fa-check-circle text-emerald-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Kualitas Baik</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['good_quality'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <i class="fas fa-swimming-pool text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Kolam Terpantau</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['monitored_ponds'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Water Quality Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Data Kualitas Air</h3>
                    <p class="text-sm text-gray-600 mt-1">Monitor kualitas air kolam di cabang {{ $branchInfo->name }}</p>
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kolam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">pH</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suhu (°C)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DO (mg/L)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amonia</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($waterQualities as $wq)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $wq->pond_name }}</div>
                                <div class="text-sm text-gray-500">{{ $wq->pond_code }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($wq->date_recorded)->format('d M Y') }}
                            @if($wq->created_by_name)
                            <div class="text-xs text-gray-400">{{ $wq->created_by_name }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $wq->ph_status === 'good' ? 'bg-green-100 text-green-800' :
                                   ($wq->ph_status === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $wq->ph }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $wq->temp_status === 'good' ? 'bg-green-100 text-green-800' :
                                   ($wq->temp_status === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $wq->temperature_c }}°
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $wq->do_status === 'good' ? 'bg-green-100 text-green-800' :
                                   ($wq->do_status === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $wq->do_mg_l }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            @if($wq->ammonia_mg_l !== null)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $wq->ammonia_status === 'good' ? 'bg-green-100 text-green-800' :
                                   ($wq->ammonia_status === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                {{ $wq->ammonia_mg_l }}
                            </span>
                            @else
                            <span class="text-gray-400 text-sm">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $wq->overall_status === 'good' ? 'bg-green-100 text-green-800' :
                                   ($wq->overall_status === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                <div class="w-1.5 h-1.5 rounded-full mr-1.5
                                    {{ $wq->overall_status === 'good' ? 'bg-green-400' :
                                       ($wq->overall_status === 'warning' ? 'bg-yellow-400' : 'bg-red-400') }}"></div>
                                {{ $wq->overall_status === 'good' ? 'Baik' :
                                   ($wq->overall_status === 'warning' ? 'Perhatian' : 'Buruk') }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <button onclick="editWaterQuality({{ $wq->id }})" class="text-blue-600 hover:text-blue-900 p-1">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteWaterQuality({{ $wq->id }}, '{{ $wq->pond_name }} - {{ \Carbon\Carbon::parse($wq->date_recorded)->format('d M Y') }}')" class="text-red-600 hover:text-red-900 p-1">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-tint text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada data kualitas air</h3>
                                <p class="text-gray-500 mb-4">Mulai dengan menambahkan data kualitas air pertama untuk cabang ini.</p>
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
<div id="waterQualityModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Tambah Data Kualitas Air</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="waterQualityForm" class="space-y-4">
                <input type="hidden" id="waterQualityId" name="id">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="pond_id" class="block text-sm font-medium text-gray-700 mb-1">Kolam *</label>
                        <select id="pond_id" name="pond_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Kolam</option>
                            @foreach($ponds as $pond)
                            <option value="{{ $pond->id }}">{{ $pond->name }} ({{ $pond->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="date_recorded" class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
                        <input type="date" id="date_recorded" name="date_recorded" required max="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="ph" class="block text-sm font-medium text-gray-700 mb-1">pH *</label>
                        <input type="number" id="ph" name="ph" required min="0" max="14" step="0.1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="7.0">
                        <p class="text-xs text-gray-500 mt-1">Ideal: 6.5 - 8.5</p>
                    </div>

                    <div>
                        <label for="temperature_c" class="block text-sm font-medium text-gray-700 mb-1">Suhu (°C) *</label>
                        <input type="number" id="temperature_c" name="temperature_c" required min="0" max="50" step="0.1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="28.0">
                        <p class="text-xs text-gray-500 mt-1">Ideal: 25 - 30°C</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="do_mg_l" class="block text-sm font-medium text-gray-700 mb-1">DO (mg/L) *</label>
                        <input type="number" id="do_mg_l" name="do_mg_l" required min="0" max="20" step="0.1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="6.0">
                        <p class="text-xs text-gray-500 mt-1">Minimal: 5 mg/L</p>
                    </div>

                    <div>
                        <label for="ammonia_mg_l" class="block text-sm font-medium text-gray-700 mb-1">Amonia (mg/L)</label>
                        <input type="number" id="ammonia_mg_l" name="ammonia_mg_l" min="0" max="10" step="0.01"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="0.05">
                        <p class="text-xs text-gray-500 mt-1">Maksimal: 0.1 mg/L</p>
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
            <h3 class="text-lg font-medium text-gray-900 mb-2">Hapus Data Kualitas Air</h3>
            <p class="text-sm text-gray-500 mb-4">
                Apakah Anda yakin ingin menghapus data <strong id="deleteWaterQualityName"></strong>?
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
let currentWaterQualityId = null;
let deleteId = null;

// Modal functions
function openAddModal() {
document.getElementById('modalTitle').textContent = 'Tambah Data Kualitas Air';
document.getElementById('submitText').textContent = 'Simpan';
document.getElementById('waterQualityForm').reset();
document.getElementById('waterQualityId').value = '';
document.getElementById('date_recorded').value = new Date().toISOString().split('T')[0];
currentWaterQualityId = null;
document.getElementById('waterQualityModal').classList.remove('hidden');
document.getElementById('pond_id').focus();
}

function closeModal() {
document.getElementById('waterQualityModal').classList.add('hidden');
}

function closeDeleteModal() {
document.getElementById('deleteModal').classList.add('hidden');
deleteId = null;
}

// CRUD functions
function editWaterQuality(id) {
currentWaterQualityId = id;
document.getElementById('modalTitle').textContent = 'Edit Data Kualitas Air';
document.getElementById('submitText').textContent = 'Perbarui';

document.getElementById('waterQualityModal').classList.remove('hidden');

fetch(`/water-qualities/${id}`)
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            document.getElementById('waterQualityId').value = result.data.id;
            document.getElementById('pond_id').value = result.data.pond_id;
            document.getElementById('date_recorded').value = result.data.date_recorded;
            document.getElementById('ph').value = result.data.ph;
            document.getElementById('temperature_c').value = result.data.temperature_c;
            document.getElementById('do_mg_l').value = result.data.do_mg_l;
            document.getElementById('ammonia_mg_l').value = result.data.ammonia_mg_l || '';
            document.getElementById('pond_id').focus();
        } else {
            showNotification('Error: ' + result.message, 'error');
            closeModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Gagal memuat data kualitas air', 'error');
        closeModal();
    });
}

function deleteWaterQuality(id, name) {
deleteId = id;
document.getElementById('deleteWaterQualityName').textContent = name;
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
</script>
@endsection
