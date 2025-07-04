@extends('user.layouts.app')

@section('page-title', 'Kolam')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <i class="fas fa-swimming-pool text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Kolam</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_ponds'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full" style="background-color: #dcfce7;">
                    <i class="fas fa-play-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Kolam Aktif</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active_ponds'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <i class="fas fa-tint text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Volume</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_volume']) }}</p>
                    <p class="text-xs text-gray-500">Liter</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Ponds Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Daftar Kolam</h3>
                    <p class="text-sm text-gray-600 mt-1">Kelola kolam di cabang {{ $branchInfo->name }}</p>
                </div>
                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Kolam
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kolam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe & Volume</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Ikan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dibuat</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($ponds as $pond)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex-shrink-0 h-16 w-16">
                                @if($pond->image_url)
                                <img class="h-16 w-16 rounded-lg object-cover border border-gray-200 cursor-pointer hover:opacity-75 transition-opacity"
                                     src="{{ $pond->image_url }}"
                                     alt="Foto {{ $pond->name }}"
                                     onclick="showImageModal('{{ $pond->image_url }}', '{{ $pond->name }}')"
                                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiByeD0iOCIgZmlsbD0iI0Y5RkFGQiIvPgo8cGF0aCBkPSJNMjQgMjRIMTZWMzJIMjRWMjRaIiBmaWxsPSIjRDFENU5CIi8+CjxwYXRoIGQ9Ik00MCAyNEgzMlYzMkg0MFYyNFoiIGZpbGw9IiNEMUQ1REIiLz4KPHBhdGggZD0iTTI0IDQwSDE2VjQ4SDI0VjQwWiIgZmlsbD0iI0QxRDVEQiIvPgo8cGF0aCBkPSJNNDAgNDBIMzJWNDhINDBWNDBaIiBmaWxsPSIjRDFENURCIi8+CjxwYXRoIGQ9Ik0zMiAzMkgyNFY0MEgzMlYzMloiIGZpbGw9IiNEMUQ1REIiLz4KPC9zdmc+'; this.classList.add('opacity-50');">
                            @else
                                <div class="h-16 w-16 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center">
                                    <i class="fas fa-image text-gray-400 text-xl"></i>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $pond->name }}</div>
                            <div class="text-sm text-gray-500">{{ $pond->code }}</div>
                            @if($pond->description)
                            <div class="text-xs text-gray-400 mt-1">{{ Str::limit($pond->description, 30) }}</div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ ucfirst($pond->type) }}
                        </span>
                    </div>
                    <div class="text-xs text-gray-500 mt-1">{{ number_format($pond->volume_liters) }} L</div>
                </td>
                <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        style="{{ $pond->status === 'active' ? 'background-color: #dcfce7; color: #15803d;' : 'background-color: #f3f4f6; color: #374151;' }}">
                        <div class="w-1.5 h-1.5 rounded-full mr-1.5"
                            style="{{ $pond->status === 'active' ? 'background-color: #22c55e;' : 'background-color: #f3f4f6;' }}"></div>
                        {{ $pond->status === 'active' ? 'Aktif' : 'Kosong' }}
                    </span>
                    @if($pond->active_batches > 0)
                    <div class="text-xs text-gray-500 mt-1">{{ $pond->active_batches }} batch</div>
                    @endif
                </td>
                <td class="px-6 py-4">
                    @if(count($pond->fish_types) > 0)
                        <div class="flex flex-wrap gap-1">
                            @foreach(array_slice($pond->fish_types, 0, 2) as $fishType)
                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700">
                                {{ $fishType }}
                            </span>
                            @endforeach
                            @if(count($pond->fish_types) > 2)
                            <span class="text-xs text-gray-500">+{{ count($pond->fish_types) - 2 }} lagi</span>
                            @endif
                        </div>
                    @else
                    <span class="text-sm text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                    {{ \Carbon\Carbon::parse($pond->created_at)->format('d M Y') }}
                </td>
                <td class="px-6 py-4 text-right text-sm font-medium">
                    <div class="flex items-center justify-end space-x-2">
                        <button onclick="editPond({{ $pond->id }})" class="text-blue-600 hover:text-blue-900 p-1">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="deletePond({{ $pond->id }}, '{{ $pond->name }}')" class="text-red-600 hover:text-red-900 p-1">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center">
                        <i class="fas fa-swimming-pool text-gray-300 text-4xl mb-4"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada kolam</h3>
                        <p class="text-gray-500 mb-4">Mulai dengan menambahkan kolam pertama untuk cabang ini.</p>
                        <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <i class="fas fa-plus mr-2"></i>
                            Tambah Kolam
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
<div id="pondModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
<div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-lg bg-white">
<div class="mt-3">
    <div class="flex items-center justify-between mb-4">
        <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Tambah Kolam</h3>
        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
        </button>
    </div>

    <form id="pondForm" class="space-y-4" enctype="multipart/form-data">
        <input type="hidden" id="pondId" name="id">

        <!-- Image Upload Section -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Foto Kolam</label>
            <div class="flex items-center space-x-4">
                <div id="imagePreview" class="hidden">
                    <img id="previewImg" class="h-20 w-20 rounded-lg object-cover border border-gray-200" src="" alt="Preview">
                </div>
                <div class="flex-1">
                    <input type="file" id="documentation_file" name="documentation_file" accept="image/*"
                           class="hidden" onchange="previewImage(this)">
                    <button type="button" onclick="document.getElementById('documentation_file').click()"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 bg-white hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <i class="fas fa-camera mr-2"></i>
                        <span id="uploadText">Pilih Foto</span>
                    </button>
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, GIF. Maksimal 2MB</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Kolam *</label>
                <input type="text" id="name" name="name" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Contoh: Kolam A1">
            </div>

            <div>
                <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Kode Kolam *</label>
                <input type="text" id="code" name="code" required
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="Contoh: KA001">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipe Kolam *</label>
                <select id="type" name="type" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Pilih Tipe</option>
                    <option value="tanah">Tanah</option>
                    <option value="beton">Beton</option>
                    <option value="viber">Viber</option>
                    <option value="terpal">Terpal</option>
                </select>
            </div>

            <div>
                <label for="volume_liters" class="block text-sm font-medium text-gray-700 mb-1">Volume (Liter) *</label>
                <input type="number" id="volume_liters" name="volume_liters" required min="1" step="0.01"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="1000">
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea id="description" name="description" rows="3"
                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Deskripsi singkat tentang kolam ini..."></textarea>
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

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 overflow-y-auto h-full w-full z-50 hidden">
<div class="relative min-h-screen flex items-center justify-center p-4">
    <div class="relative max-w-4xl max-h-full">
        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
            <i class="fas fa-times text-2xl"></i>
        </button>
        <img id="modalImage" class="max-w-full max-h-screen object-contain rounded-lg" src="" alt="">
        <div class="absolute bottom-4 left-4 bg-black bg-opacity-50 text-white px-3 py-2 rounded">
            <span id="modalImageTitle"></span>
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
            <h3 class="text-lg font-medium text-gray-900 mb-2">Hapus Kolam</h3>
            <p class="text-sm text-gray-500 mb-4">
                Apakah Anda yakin ingin menghapus kolam <strong id="deletePondName"></strong>?
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
    let currentPondId = null;
    let deleteId = null;

    // Image preview function
    function previewImage(input) {
        const file = input.files[0];
        const preview = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');
        const uploadText = document.getElementById('uploadText');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                preview.classList.remove('hidden');
                uploadText.textContent = 'Ganti Foto';
            };
            reader.readAsDataURL(file);
        } else {
            preview.classList.add('hidden');
            uploadText.textContent = 'Pilih Foto';
        }
    }

    // Image modal functions
    function showImageModal(imageUrl, title) {
        document.getElementById('modalImage').src = imageUrl;
        document.getElementById('modalImageTitle').textContent = title;
        document.getElementById('imageModal').classList.remove('hidden');
    }

    function closeImageModal() {
        document.getElementById('imageModal').classList.add('hidden');
    }

    // Modal functions
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Tambah Kolam';
        document.getElementById('submitText').textContent = 'Simpan';
        document.getElementById('pondForm').reset();
        document.getElementById('pondId').value = '';
        document.getElementById('imagePreview').classList.add('hidden');
        document.getElementById('uploadText').textContent = 'Pilih Foto';
        currentPondId = null;
        document.getElementById('pondModal').classList.remove('hidden');
        document.getElementById('name').focus();
    }

    function closeModal() {
        document.getElementById('pondModal').classList.add('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        deleteId = null;
    }

    // CRUD functions
    function editPond(id) {
        currentPondId = id;
        document.getElementById('modalTitle').textContent = 'Edit Kolam';
        document.getElementById('submitText').textContent = 'Perbarui';

        document.getElementById('pondModal').classList.remove('hidden');

        fetch(`/ponds/${id}`)
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    document.getElementById('pondId').value = result.data.id;
                    document.getElementById('name').value = result.data.name;
                    document.getElementById('code').value = result.data.code;
                    document.getElementById('type').value = result.data.type;
                    document.getElementById('volume_liters').value = result.data.volume_liters;
                    document.getElementById('description').value = result.data.description || '';

                    // Handle existing image
                    if (result.data.image_url) {
                        document.getElementById('previewImg').src = result.data.image_url;
                        document.getElementById('imagePreview').classList.remove('hidden');
                        document.getElementById('uploadText').textContent = 'Ganti Foto';
                    } else {
                        document.getElementById('imagePreview').classList.add('hidden');
                        document.getElementById('uploadText').textContent = 'Pilih Foto';
                    }

                    document.getElementById('name').focus();
                } else {
                    showNotification('Error: ' + result.message, 'error');
                    closeModal();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Gagal memuat data kolam', 'error');
                closeModal();
            });
    }

    function deletePond(id, name) {
        deleteId = id;
        document.getElementById('deletePondName').textContent = name;
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

        fetch(`/ponds/${deleteId}`, {
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
            showNotification('Gagal menghapus kolam', 'error');
        })
        .finally(() => {
            deleteText.textContent = 'Hapus';
            deleteLoader.classList.add('hidden');
            deleteBtn.disabled = false;
            closeDeleteModal();
        });
    }

    // Form submission
    document.getElementById('pondForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitLoader = document.getElementById('submitLoader');

        const formData = new FormData(this);
        const isEdit = currentPondId !== null;

        submitText.textContent = isEdit ? 'Memperbarui...' : 'Menyimpan...';
        submitLoader.classList.remove('hidden');
        submitBtn.disabled = true;

        const url = isEdit ? `/ponds/${currentPondId}` : '/ponds';

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
            closeImageModal();
        }
    });

    // Close modals when clicking outside
    document.getElementById('pondModal').addEventListener('click', function(e) {
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
    </script>
    @endsection

