@extends('admin.layouts.app')

@section('title', 'Manajemen Pengguna')
@section('page-title', 'Manajemen Pengguna')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Pengguna</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <i class="fas fa-user-check text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Terverifikasi</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['verified_users'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100">
                    <i class="fas fa-user-clock text-orange-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Belum Verifikasi</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['unverified_users'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <i class="fas fa-building text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Cabang</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_branches'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Daftar Pengguna</h3>
                    <p class="text-sm text-gray-600 mt-1">Kelola semua pengguna sistem</p>
                </div>
                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Pengguna
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cabang</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bergabung</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $user)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center">
                                        <span class="text-white font-medium text-sm">
                                            {{ strtoupper(substr($user->full_name, 0, 2)) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->full_name }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $user->phone_number }}</div>
                            <div class="text-sm text-gray-500">{{ Str::limit($user->address, 30) }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ $user->branch->name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_verified ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                <i class="fas {{ $user->is_verified ? 'fa-check-circle' : 'fa-clock' }} mr-1"></i>
                                {{ $user->is_verified ? 'Terverifikasi' : 'Belum Verifikasi' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $user->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <button onclick="viewUser('{{ $user->id }}')" class="text-green-600 hover:text-green-900 p-1" title="Lihat Detail">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="toggleVerification('{{ $user->id }}', {{ $user->is_verified ? 'false' : 'true' }})"
                                        class="text-{{ $user->is_verified ? 'orange' : 'green' }}-600 hover:text-{{ $user->is_verified ? 'orange' : 'green' }}-900 p-1"
                                        title="{{ $user->is_verified ? 'Batalkan Verifikasi' : 'Verifikasi' }}">
                                    <i class="fas fa-{{ $user->is_verified ? 'user-times' : 'user-check' }}"></i>
                                </button>
                                <button onclick="editUser('{{ $user->id }}')" class="text-blue-600 hover:text-blue-900 p-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteUser('{{ $user->id }}', '{{ $user->full_name }}')" class="text-red-600 hover:text-red-900 p-1" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-users text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada pengguna</h3>
                                <p class="text-gray-500 mb-4">Mulai dengan menambahkan pengguna pertama untuk sistem Anda.</p>
                                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Tambah Pengguna
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
<div id="userModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Tambah Pengguna</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="userForm" class="space-y-4">
                <input type="hidden" id="userId" name="id">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                        <input type="text" id="full_name" name="full_name" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Contoh: John Doe">
                        <div id="fullNameError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input type="email" id="email" name="email" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Contoh: john@example.com">
                        <div id="emailError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-1">Nomor Telepon *</label>
                        <input type="text" id="phone_number" name="phone_number" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Contoh: +62 812-3456-7890">
                        <div id="phoneNumberError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <div>
                        <label for="branch_id" class="block text-sm font-medium text-gray-700 mb-1">Cabang *</label>
                        <select id="branch_id" name="branch_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Cabang</option>
                            @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        <div id="branchIdError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Alamat *</label>
                    <textarea id="address" name="address" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Masukkan alamat lengkap..."></textarea>
                    <div id="addressError" class="text-red-500 text-sm mt-1 hidden"></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password <span id="passwordRequired">*</span></label>
                        <input type="password" id="password" name="password"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Minimal 8 karakter">
                        <div id="passwordError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password <span id="passwordConfirmRequired">*</span></label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ulangi password">
                        <div id="passwordConfirmationError" class="text-red-500 text-sm mt-1 hidden"></div>
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="is_verified" name="is_verified" value="1"
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_verified" class="ml-2 block text-sm text-gray-900">
                        Verifikasi pengguna
                    </label>
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

<!-- View Modal -->
<div id="viewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-10 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Detail Pengguna</h3>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="viewContent" class="space-y-4">
                <!-- Content will be loaded here -->
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4">
                <button onclick="closeViewModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
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
            <h3 class="text-lg font-medium text-gray-900 mb-2">Hapus Pengguna</h3>
            <p class="text-sm text-gray-500 mb-4">
                Apakah Anda yakin ingin menghapus pengguna <strong id="deleteUserName"></strong>?
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

<!-- Verification Toggle Modal -->
<div id="verificationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-lg bg-white">
        <div class="mt-3 text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full mb-4" id="verificationIcon">
                <!-- Icon will be set by JavaScript -->
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2" id="verificationTitle">Verifikasi Pengguna</h3>
            <p class="text-sm text-gray-500 mb-4" id="verificationMessage">
                <!-- Message will be set by JavaScript -->
            </p>
            <div class="flex items-center justify-center space-x-3">
                <button onclick="closeVerificationModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Batal
                </button>
                <button onclick="confirmToggleVerification()" id="verificationBtn"
                        class="px-4 py-2 text-sm font-medium text-white rounded-lg transition-colors">
                    <span id="verificationText">Konfirmasi</span>
                    <i id="verificationLoader" class="fas fa-spinner fa-spin ml-2 hidden"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentUserId = null;
let deleteId = null;
let verificationUserId = null;
let verificationStatus = null;

// Modal functions
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Pengguna';
    document.getElementById('submitText').textContent = 'Simpan';
    document.getElementById('userForm').reset();
    document.getElementById('userId').value = '';
    document.getElementById('password').required = true;
    document.getElementById('password_confirmation').required = true;
    document.getElementById('passwordRequired').style.display = 'inline';
    document.getElementById('passwordConfirmRequired').style.display = 'inline';
    currentUserId = null;
    clearErrors();
    document.getElementById('userModal').classList.remove('hidden');
    document.getElementById('full_name').focus();
}

function closeModal() {
    document.getElementById('userModal').classList.add('hidden');
    clearErrors();
}

function closeViewModal() {
    document.getElementById('viewModal').classList.add('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    deleteId = null;
}

function closeVerificationModal() {
    document.getElementById('verificationModal').classList.add('hidden');
    verificationUserId = null;
    verificationStatus = null;
}

function clearErrors() {
    const errorElements = ['fullNameError', 'emailError', 'phoneNumberError', 'branchIdError', 'addressError', 'passwordError', 'passwordConfirmationError'];
    errorElements.forEach(id => {
        document.getElementById(id).classList.add('hidden');
    });
}

// CRUD functions
function viewUser(id) {
    fetch(`/admin/users/${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const user = result.data;
                const content = `
                    <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                        <div class="flex items-center space-x-4 mb-4">
                            <div class="h-16 w-16 rounded-full bg-gradient-to-r from-blue-400 to-blue-600 flex items-center justify-center">
                                <span class="text-white font-medium text-xl">
                                    ${user.full_name.substring(0, 2).toUpperCase()}
                                </span>
                            </div>
                            <div>
                                <h4 class="text-lg font-medium text-gray-900">${user.full_name}</h4>
                                <p class="text-sm text-gray-500">${user.email}</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${user.is_verified ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800'}">
                                    <i class="fas ${user.is_verified ? 'fa-check-circle' : 'fa-clock'} mr-1"></i>
                                    ${user.is_verified ? 'Terverifikasi' : 'Belum Verifikasi'}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nomor Telepon</label>
                                <p class="mt-1 text-sm text-gray-900">${user.phone_number}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Cabang</label>
                                <p class="mt-1 text-sm text-gray-900">${user.branch.name}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Alamat</label>
                            <p class="mt-1 text-sm text-gray-900">${user.address}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Bergabung</label>
                                <p class="mt-1 text-sm text-gray-900">${new Date(user.created_at).toLocaleDateString('id-ID')}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Terakhir Diperbarui</label>
                                <p class="mt-1 text-sm text-gray-900">${new Date(user.updated_at).toLocaleDateString('id-ID')}</p>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('viewContent').innerHTML = content;
                document.getElementById('viewModal').classList.remove('hidden');
            } else {
                showNotification('Error: ' + result.message, 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Gagal memuat data pengguna', 'error');
        });
}

function editUser(id) {
    currentUserId = id;
    document.getElementById('modalTitle').textContent = 'Edit Pengguna';
    document.getElementById('submitText').textContent = 'Perbarui';
    document.getElementById('password').required = false;
    document.getElementById('password_confirmation').required = false;
    document.getElementById('passwordRequired').style.display = 'none';
    document.getElementById('passwordConfirmRequired').style.display = 'none';

    // Show loading state
    document.getElementById('userModal').classList.remove('hidden');

    fetch(`/admin/users/${id}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                document.getElementById('userId').value = result.data.id;
                document.getElementById('full_name').value = result.data.full_name;
                document.getElementById('email').value = result.data.email;
                document.getElementById('phone_number').value = result.data.phone_number;
                document.getElementById('branch_id').value = result.data.branch_id;
                document.getElementById('address').value = result.data.address;
                document.getElementById('is_verified').checked = result.data.is_verified;
                document.getElementById('full_name').focus();
            } else {
                showNotification('Error: ' + result.message, 'error');
                closeModal();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Gagal memuat data pengguna', 'error');
            closeModal();
        });
}

function deleteUser(id, name) {
    deleteId = id;
    document.getElementById('deleteUserName').textContent = name;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function toggleVerification(id, newStatus) {
    verificationUserId = id;
    verificationStatus = newStatus;

    const iconDiv = document.getElementById('verificationIcon');
    const title = document.getElementById('verificationTitle');
    const message = document.getElementById('verificationMessage');
    const btn = document.getElementById('verificationBtn');
    const btnText = document.getElementById('verificationText');

    if (newStatus === 'true') {
        iconDiv.className = 'mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4';
        iconDiv.innerHTML = '<i class="fas fa-user-check text-green-600"></i>';
        title.textContent = 'Verifikasi Pengguna';
        message.textContent = 'Apakah Anda yakin ingin memverifikasi pengguna ini?';
        btn.className = 'px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors';
        btnText.textContent = 'Verifikasi';
    } else {
        iconDiv.className = 'mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 mb-4';
        iconDiv.innerHTML = '<i class="fas fa-user-times text-orange-600"></i>';
        title.textContent = 'Batalkan Verifikasi';
        message.textContent = 'Apakah Anda yakin ingin membatalkan verifikasi pengguna ini?';
        btn.className = 'px-4 py-2 text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 rounded-lg transition-colors';
        btnText.textContent = 'Batalkan';
    }

    document.getElementById('verificationModal').classList.remove('hidden');
}

function confirmDelete() {
    if (!deleteId) return;

    const deleteBtn = document.getElementById('deleteBtn');
    const deleteText = document.getElementById('deleteText');
    const deleteLoader = document.getElementById('deleteLoader');

    deleteText.textContent = 'Menghapus...';
    deleteLoader.classList.remove('hidden');
    deleteBtn.disabled = true;

    fetch(`/admin/users/${deleteId}`, {
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
        showNotification('Gagal menghapus pengguna', 'error');
    })
    .finally(() => {
        deleteText.textContent = 'Hapus';
        deleteLoader.classList.add('hidden');
        deleteBtn.disabled = false;
        closeDeleteModal();
    });
}

function confirmToggleVerification() {
    if (!verificationUserId) return;

    const verificationBtn = document.getElementById('verificationBtn');
    const verificationText = document.getElementById('verificationText');
    const verificationLoader = document.getElementById('verificationLoader');

    verificationText.textContent = 'Memproses...';
    verificationLoader.classList.remove('hidden');
    verificationBtn.disabled = true;

    fetch(`/admin/users/${verificationUserId}/toggle-verification`, {
        method: 'POST',
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
        showNotification('Gagal mengubah status verifikasi', 'error');
    })
    .finally(() => {
        verificationText.textContent = 'Konfirmasi';
        verificationLoader.classList.add('hidden');
        verificationBtn.disabled = false;
        closeVerificationModal();
    });
}

// Form submission
document.getElementById('userForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitLoader = document.getElementById('submitLoader');

    const formData = new FormData(this);
    const isEdit = currentUserId !== null;

    submitText.textContent = isEdit ? 'Memperbarui...' : 'Menyimpan...';
    submitLoader.classList.remove('hidden');
    submitBtn.disabled = true;
    clearErrors();

    const url = isEdit ? `/admin/users/${currentUserId}` : '/admin/users';
    const method = isEdit ? 'PUT' : 'POST';

    const data = {
        full_name: formData.get('full_name'),
        email: formData.get('email'),
        phone_number: formData.get('phone_number'),
        branch_id: formData.get('branch_id'),
        address: formData.get('address'),
        is_verified: formData.get('is_verified') ? true : false
    };

    // Only include password if provided
    if (formData.get('password')) {
        data.password = formData.get('password');
        data.password_confirmation = formData.get('password_confirmation');
    }

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
            if (result.errors) {
                // Show validation errors
                Object.keys(result.errors).forEach(field => {
                    let errorElementId = field + 'Error';
                    // Handle snake_case to camelCase conversion
                    if (field === 'full_name') errorElementId = 'fullNameError';
                    if (field === 'phone_number') errorElementId = 'phoneNumberError';
                    if (field === 'branch_id') errorElementId = 'branchIdError';
                    if (field === 'password_confirmation') errorElementId = 'passwordConfirmationError';

                    const errorElement = document.getElementById(errorElementId);
                    if (errorElement) {
                        errorElement.textContent = result.errors[field][0];
                        errorElement.classList.remove('hidden');
                    }
                });
            }
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
        closeViewModal();
        closeDeleteModal();
        closeVerificationModal();
    }
});

// Close modals when clicking outside
document.getElementById('userModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

document.getElementById('viewModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeViewModal();
    }
});

document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});

document.getElementById('verificationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeVerificationModal();
    }
});

// Auto-resize textarea
document.getElementById('address').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});

// Password confirmation validation
document.getElementById('password_confirmation').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmation = this.value;

    if (password && confirmation && password !== confirmation) {
        this.setCustomValidity('Password tidak cocok');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('password').addEventListener('input', function() {
    const confirmation = document.getElementById('password_confirmation');
    if (confirmation.value) {
        confirmation.dispatchEvent(new Event('input'));
    }
});
</script>
@endsection
