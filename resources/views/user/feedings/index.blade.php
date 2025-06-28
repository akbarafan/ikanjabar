@extends('user.layouts.app')

@section('page-title', 'Pemberian Pakan')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <i class="fas fa-utensils text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Pemberian</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_feedings'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <i class="fas fa-weight text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Pakan</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_feed_kg'], 1) }}</p>
                    <p class="text-xs text-gray-500">KG</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <i class="fas fa-calendar-day text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Rata-rata/Hari</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['avg_feed_per_day'], 1) }}</p>
                    <p class="text-xs text-gray-500">KG</p>
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

    <!-- Feedings Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Data Pemberian Pakan</h3>
                    <p class="text-sm text-gray-600 mt-1">Kelola pemberian pakan di cabang {{ $branchInfo->name }}</p>
                </div>
                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Pemberian Pakan
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Info</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Pakan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Per Ekor</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Umur Batch</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($feedings as $feeding)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $feeding->fish_type_name }}</div>
                                <div class="text-sm text-gray-500">{{ $feeding->pond_name }} ({{ $feeding->pond_code }})</div>
                                <div class="text-xs text-gray-400">Batch #{{ $feeding->batch_id }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($feeding->date)->format('d M Y') }}</div>
                            @if($feeding->created_by_name)
                                <div class="text-xs text-gray-500">{{ $feeding->created_by_name }}</div>
                                @endif
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                {{ $feeding->feed_type }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ number_format($feeding->feed_amount_kg, 2) }} kg</div>
                            <div class="text-xs text-gray-500">{{ number_format($feeding->feed_amount_kg * 1000) }} gram</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ number_format($feeding->feed_per_fish, 1) }}g</div>
                            <div class="text-xs text-gray-500">{{ number_format($feeding->current_stock) }} ekor</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $feeding->batch_age_days }} hari</div>
                            <div class="text-xs text-gray-500">{{ floor($feeding->batch_age_days / 7) }} minggu</div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                @if($feeding->notes)
                                <button onclick="showNotes('{{ addslashes($feeding->notes) }}')" class="text-gray-600 hover:text-gray-900 p-1" title="Lihat Catatan">
                                    <i class="fas fa-sticky-note"></i>
                                </button>
                                @endif
                                <button onclick="editFeeding({{ $feeding->id }})" class="text-blue-600 hover:text-blue-900 p-1">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteFeeding({{ $feeding->id }}, '{{ $feeding->fish_type_name }} - {{ \Carbon\Carbon::parse($feeding->date)->format('d M Y') }}')" class="text-red-600 hover:text-red-900 p-1">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-utensils text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada data pemberian pakan</h3>
                                <p class="text-gray-500 mb-4">Mulai dengan menambahkan data pemberian pakan pertama.</p>
                                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
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
    </div>
</div>

<!-- Add/Edit Modal -->
<div id="feedingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Tambah Pemberian Pakan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="feedingForm" class="space-y-4">
                <input type="hidden" id="feedingId" name="id">

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
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
                        <input type="date" id="date" name="date" required max="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <label for="feed_type" class="block text-sm font-medium text-gray-700 mb-1">Jenis Pakan *</label>
                        <input type="text" id="feed_type" name="feed_type" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Contoh: Pelet 781-2">
                    </div>
                </div>

                <div>
                    <label for="feed_amount_kg" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Pakan (kg) *</label>
                    <input type="number" id="feed_amount_kg" name="feed_amount_kg" required min="0.1" step="0.1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="5.5">
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea id="notes" name="notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Catatan tambahan..."></textarea>
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

<!-- Notes Modal -->
<div id="notesModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Catatan</h3>
                <button onclick="closeNotesModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="notesContent" class="text-sm text-gray-700 whitespace-pre-wrap"></div>
            <div class="flex justify-end mt-4">
                <button onclick="closeNotesModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Tutup
                </button>
            </div>
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
            <h3 class="text-lg font-medium text-gray-900 mb-2">Hapus Data Pemberian Pakan</h3>
            <p class="text-sm text-gray-500 mb-4">
                Apakah Anda yakin ingin menghapus data <strong id="deleteFeedingName"></strong>?
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
let currentFeedingId = null;
let deleteId = null;

// Modal functions
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Pemberian Pakan';
    document.getElementById('submitText').textContent = 'Simpan';
    document.getElementById('feedingForm').reset();
    document.getElementById('feedingId').value = '';
    currentFeedingId = null;
    document.getElementById('feedingModal').classList.remove('hidden');
    document.getElementById('fish_batch_id').focus();
}

function closeModal() {
    document.getElementById('feedingModal').classList.add('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    deleteId = null;
}

function showNotes(notes) {
    document.getElementById('notesContent').textContent = notes;
    document.getElementById('notesModal').classList.remove('hidden');
}

function closeNotesModal() {
    document.getElementById('notesModal').classList.add('hidden');
}

// CRUD functions
function editFeeding(id) {
    currentFeedingId = id;
    document.getElementById('modalTitle').textContent = 'Edit Pemberian Pakan';
    document.getElementById('submitText').textContent = 'Perbarui';

    document.getElementById('feedingModal').classList.remove('hidden');

    fetch(`/feedings/${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                document.getElementById('feedingId').value = result.data.id;
                document.getElementById('fish_batch_id').value = result.data.fish_batch_id;
                document.getElementById('date').value = result.data.date;
                document.getElementById('feed_type').value = result.data.feed_type;
                document.getElementById('feed_amount_kg').value = result.data.feed_amount_kg;
                document.getElementById('notes').value = result.data.notes || '';
                document.getElementById('fish_batch_id').focus();
            } else {
                showNotification('Error: ' + result.message, 'error');
                closeModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Gagal memuat data pemberian pakan', 'error');
            closeModal();
        });
}

function deleteFeeding(id, name) {
    deleteId = id;
    document.getElementById('deleteFeedingName').textContent = name;
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
        closeNotesModal();
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

document.getElementById('notesModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeNotesModal();
    }
});
</script>
@endsection
