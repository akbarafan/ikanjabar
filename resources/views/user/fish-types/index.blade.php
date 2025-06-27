@extends('user.layouts.app')

@section('page-title', 'Jenis Ikan')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <i class="fas fa-fish text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Jenis</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_types'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <i class="fas fa-layer-group text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Stok Saat Ini</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_current_stock']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100">
                    <i class="fas fa-swimming-pool text-orange-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Kolam Terpakai</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['used_ponds'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Fish Types Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Daftar Jenis Ikan</h3>
                    <p class="text-sm text-gray-600 mt-1">Kelola jenis ikan di cabang {{ $branchInfo->name }}</p>
                </div>
                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Jenis Ikan
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Ikan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Saat Ini</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kolam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($fishTypes as $fishType)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $fishType->name }}</div>
                                @if($fishType->description)
                                <div class="text-sm text-gray-500 mt-1">{{ Str::limit($fishType->description, 50) }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $fishType->current_stock > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ number_format($fishType->current_stock) }} ekor
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $fishType->used_ponds }} kolam
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($fishType->created_at)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <button onclick="editFishType({{ $fishType->id }})" class="text-blue-600 hover:text-blue-900 p-1">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteFishType({{ $fishType->id }}, '{{ $fishType->name }}')" class="text-red-600 hover:text-red-900 p-1">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-fish text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada jenis ikan</h3>
                                <p class="text-gray-500 mb-4">Mulai dengan menambahkan jenis ikan pertama untuk cabang ini.</p>
                                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Tambah Jenis Ikan
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
<div id="fishTypeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Tambah Jenis Ikan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="fishTypeForm" class="space-y-4">
                <input type="hidden" id="fishTypeId" name="id">

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Jenis Ikan *</label>
                    <input type="text" id="name" name="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Contoh: Lele Sangkuriang">
                    <div id="nameError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <textarea id="description" name="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Deskripsi singkat tentang jenis ikan ini..."></textarea>
                    <div id="descriptionError" class="text-red-500 text-sm mt-1 hidden"></div>
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
            <h3 class="text-lg font-medium text-gray-900 mb-2">Hapus Jenis Ikan</h3>
            <p class="text-sm text-gray-500 mb-4">
                Apakah Anda yakin ingin menghapus jenis ikan <strong id="deleteFishTypeName"></strong>?
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
let currentFishTypeId = null;
let deleteId = null;

// Modal functions
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Jenis Ikan';
    document.getElementById('submitText').textContent = 'Simpan';
    document.getElementById('fishTypeForm').reset();
    document.getElementById('fishTypeId').value = '';
    currentFishTypeId = null;
    clearErrors();
    document.getElementById('fishTypeModal').classList.remove('hidden');
    document.getElementById('name').focus();
}

function closeModal() {
    document.getElementById('fishTypeModal').classList.add('hidden');
    clearErrors();
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    deleteId = null;
}

function clearErrors() {
    document.getElementById('nameError').classList.add('hidden');
    document.getElementById('descriptionError').classList.add('hidden');
}

// CRUD functions
function editFishType(id) {
    currentFishTypeId = id;
    document.getElementById('modalTitle').textContent = 'Edit Jenis Ikan';
    document.getElementById('submitText').textContent = 'Perbarui';

    // Show loading state
    document.getElementById('fishTypeModal').classList.remove('hidden');

    fetch(`/fish-types/${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                document.getElementById('fishTypeId').value = result.data.id;
                document.getElementById('name').value = result.data.name;
                document.getElementById('description').value = result.data.description || '';
                document.getElementById('name').focus();
            } else {
                showNotification('Error: ' + result.message, 'error');
                closeModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Gagal memuat data jenis ikan', 'error');
            closeModal();
        });
}

function deleteFishType(id, name) {
    deleteId = id;
    document.getElementById('deleteFishTypeName').textContent = name;
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

    fetch(`/fish-types/${deleteId}`, {
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
        showNotification('Gagal menghapus jenis ikan', 'error');
    })
    .finally(() => {
        deleteText.textContent = 'Hapus';
        deleteLoader.classList.add('hidden');
        deleteBtn.disabled = false;
        closeDeleteModal();
    });
}

// Form submission
document.getElementById('fishTypeForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitLoader = document.getElementById('submitLoader');

    const formData = new FormData(this);
    const isEdit = currentFishTypeId !== null;

    submitText.textContent = isEdit ? 'Memperbarui...' : 'Menyimpan...';
    submitLoader.classList.remove('hidden');
    submitBtn.disabled = true;
    clearErrors();

    const url = isEdit ? `/fish-types/${currentFishTypeId}` : '/fish-types';
    const method = isEdit ? 'PUT' : 'POST';

    const data = {
        name: formData.get('name'),
        description: formData.get('description')
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
    // Create notification element
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

    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    // Auto remove after 4 seconds
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
document.getElementById('fishTypeModal').addEventListener('click', function(e) {
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
