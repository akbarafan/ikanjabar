@extends('user.layouts.app')

@section('page-title', 'Penjualan')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <i class="fas fa-shopping-cart text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Penjualan</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_sales'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <i class="fas fa-money-bill-wave text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Pendapatan</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($stats['total_revenue']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <i class="fas fa-fish text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Ikan Terjual</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_fish_sold']) }}</p>
                    <p class="text-xs text-gray-500">Ekor</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-orange-100">
                    <i class="fas fa-tag text-orange-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Harga Rata-rata</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['avg_price_per_kg']) }}</p>
                    <p class="text-xs text-gray-500">Per Kg</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Data Penjualan</h3>
                    <p class="text-sm text-gray-600 mt-1">Kelola data penjualan di cabang {{ $branchInfo->name }}</p>
                </div>
                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Tambah Penjualan
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch Info</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembeli</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Berat & Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($sale->created_at)->format('H:i') }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $sale->fish_type_name }}</div>
                                <div class="text-sm text-gray-500">{{ $sale->pond_name }} ({{ $sale->pond_code }})</div>
                                <div class="text-xs text-gray-400">Batch #{{ $sale->batch_id }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $sale->buyer_name }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ number_format($sale->quantity_fish) }} ekor</div>
                            <div class="text-xs text-gray-500">{{ number_format($sale->total_weight_kg, 1) }} kg</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900">{{ number_format($sale->avg_weight_per_fish_kg, 2) }} kg/ekor</div>
                            <div class="text-xs text-gray-500">Rp {{ number_format($sale->price_per_kg) }}/kg</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">Rp {{ number_format($sale->total_price) }}</div>
                            <div class="text-xs text-gray-500">Rp {{ number_format($sale->price_per_fish) }}/ekor</div>
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium">
                            <div class="flex items-center justify-end space-x-2">
                                <button onclick="editSale({{ $sale->id }})" class="text-blue-600 hover:text-blue-900 p-1">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteSale({{ $sale->id }}, '{{ $sale->buyer_name }} - {{ \Carbon\Carbon::parse($sale->date)->format('d M Y') }}')" class="text-red-600 hover:text-red-900 p-1">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-shopping-cart text-gray-300 text-4xl mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada data penjualan</h3>
                                <p class="text-gray-500 mb-4">Mulai dengan menambahkan data penjualan pertama untuk cabang ini.</p>
                                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Tambah Penjualan
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
<div id="saleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-lg bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 id="modalTitle" class="text-lg font-medium text-gray-900">Tambah Penjualan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="saleForm" class="space-y-4">
                <input type="hidden" id="saleId" name="id">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="fish_batch_id" class="block text-sm font-medium text-gray-700 mb-1">Batch Ikan *</label>
                        <select id="fish_batch_id" name="fish_batch_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih Batch</option>
                            @foreach($fishBatches as $batch)
                            <option value="{{ $batch->id }}">{{ $batch->fish_type_name }} - {{ $batch->pond_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal *</label>
                        <input type="date" id="date" name="date" required max="{{ date('Y-m-d') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div>
                    <label for="buyer_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Pembeli *</label>
                    <input type="text" id="buyer_name" name="buyer_name" required maxlength="100"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Nama pembeli">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="quantity_fish" class="block text-sm font-medium text-gray-700 mb-1">Jumlah Ikan *</label>
                        <input type="number" id="quantity_fish" name="quantity_fish" required min="1"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="100" onchange="calculateTotal()">
                    </div>

                    <div>
                        <label for="avg_weight_per_fish_kg" class="block text-sm font-medium text-gray-700 mb-1">Berat/Ekor (kg) *</label>
                        <input type="number" id="avg_weight_per_fish_kg" name="avg_weight_per_fish_kg" required min="0.01" step="0.01"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="0.5" onchange="calculateTotal()">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="price_per_kg" class="block text-sm font-medium text-gray-700 mb-1">Harga/Kg *</label>
                        <input type="number" id="price_per_kg" name="price_per_kg" required min="0.01" step="0.01"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="25000" onchange="calculateTotal()">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Harga</label>
                        <div id="total_price_display" class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-gray-900 font-medium">
                            Rp 0
                        </div>
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
            <h3 class="text-lg font-medium text-gray-900 mb-2">Hapus Data Penjualan</h3>
            <p class="text-sm text-gray-500 mb-4">
                Apakah Anda yakin ingin menghapus data penjualan <strong id="deleteSaleName"></strong>?
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
let currentSaleId = null;
let deleteId = null;

// Modal functions
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Tambah Penjualan';
    document.getElementById('submitText').textContent = 'Simpan';
    document.getElementById('saleForm').reset();
    document.getElementById('saleId').value = '';
    currentSaleId = null;
    document.getElementById('total_price_display').textContent = 'Rp 0';
    document.getElementById('saleModal').classList.remove('hidden');
    document.getElementById('fish_batch_id').focus();
}

function closeModal() {
    document.getElementById('saleModal').classList.add('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    deleteId = null;
}

// Calculate total price
function calculateTotal() {
    const quantity = parseFloat(document.getElementById('quantity_fish').value) || 0;
    const weight = parseFloat(document.getElementById('avg_weight_per_fish_kg').value) || 0;
    const price = parseFloat(document.getElementById('price_per_kg').value) || 0;

    const total = quantity * weight * price;
    document.getElementById('total_price_display').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

// CRUD functions
function editSale(id) {
    currentSaleId = id;
    document.getElementById('modalTitle').textContent = 'Edit Penjualan';
    document.getElementById('submitText').textContent = 'Perbarui';

document.getElementById('saleModal').classList.remove('hidden');

fetch(`/sales/${id}`)
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            document.getElementById('saleId').value = result.data.id;
            document.getElementById('fish_batch_id').value = result.data.fish_batch_id;
            document.getElementById('date').value = result.data.date;
            document.getElementById('buyer_name').value = result.data.buyer_name;
            document.getElementById('quantity_fish').value = result.data.quantity_fish;
            document.getElementById('avg_weight_per_fish_kg').value = result.data.avg_weight_per_fish_kg;
            document.getElementById('price_per_kg').value = result.data.price_per_kg;
            calculateTotal();
            document.getElementById('fish_batch_id').focus();
        } else {
            showNotification('Error: ' + result.message, 'error');
            closeModal();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Gagal memuat data penjualan', 'error');
        closeModal();
    });
}

function deleteSale(id, name) {
deleteId = id;
document.getElementById('deleteSaleName').textContent = name;
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

fetch(`/sales/${deleteId}`, {
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
    showNotification('Gagal menghapus data penjualan', 'error');
})
.finally(() => {
    deleteText.textContent = 'Hapus';
    deleteLoader.classList.add('hidden');
    deleteBtn.disabled = false;
    closeDeleteModal();
});
}

// Form submission
document.getElementById('saleForm').addEventListener('submit', function(e) {
e.preventDefault();

const submitBtn = document.getElementById('submitBtn');
const submitText = document.getElementById('submitText');
const submitLoader = document.getElementById('submitLoader');

const formData = new FormData(this);
const isEdit = currentSaleId !== null;

submitText.textContent = isEdit ? 'Memperbarui...' : 'Menyimpan...';
submitLoader.classList.remove('hidden');
submitBtn.disabled = true;

const url = isEdit ? `/sales/${currentSaleId}` : '/sales';

const data = {
    fish_batch_id: formData.get('fish_batch_id'),
    date: formData.get('date'),
    buyer_name: formData.get('buyer_name'),
    quantity_fish: formData.get('quantity_fish'),
    avg_weight_per_fish_kg: formData.get('avg_weight_per_fish_kg'),
    price_per_kg: formData.get('price_per_kg')
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
document.getElementById('saleModal').addEventListener('click', function(e) {
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
