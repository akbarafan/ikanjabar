@extends('admin.layouts.app')

@section('title', 'Daftar Cabang')
@section('page-title', 'Manajemen Cabang')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Cabang</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $branches->total() }}</h3>
            </div>
            <div class="bg-blue-100 p-3 rounded-full">
                <i class="fas fa-building text-blue-600"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ $branches->total() }}</span> cabang terdaftar
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Kolam</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $branches->sum('ponds_count') }}</h3>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
                <i class="fas fa-swimming-pool text-green-600"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ $branches->sum('ponds_count') }}</span> kolam aktif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Total Pengguna</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $branches->sum('users_count') }}</h3>
            </div>
            <div class="bg-purple-100 p-3 rounded-full">
                <i class="fas fa-users text-purple-600"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ $branches->sum('users_count') }}</span> pengguna aktif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">Batch Aktif</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $branches->sum(function($branch) { return $branch->statistics['total_active_batches'] ?? 0; }) }}</h3>
            </div>
            <div class="bg-green-100 p-3 rounded-full">
                <i class="fas fa-fish text-green-600"></i>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600">
            <span class="text-green-500"><i class="fas fa-arrow-up"></i> {{ $branches->sum(function($branch) { return $branch->statistics['total_active_batches'] ?? 0; }) }}</span> batch aktif
        </div>
    </div>
</div>

<!-- Branches List -->
<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <div class="flex justify-between items-center mb-6">
        <h3 class="text-lg font-semibold text-gray-800">Daftar Cabang</h3>
        <a href="{{ route('admin.branches.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
            <i class="fas fa-plus mr-2"></i> Tambah Cabang
        </a>
    </div>

    <!-- Search Form -->
    <div class="mb-6">
        <form id="searchForm" class="flex flex-col sm:flex-row gap-4">
            @csrf
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" 
                           id="searchInput"
                           name="search" 
                           value="{{ $searchTerm ?? '' }}" 
                           placeholder="Cari nama cabang..." 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
            </div>
            <div class="flex gap-2">
                <button type="submit" id="searchBtn" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
                    <i class="fas fa-search mr-2"></i> <span>Cari</span>
                </button>
                <button type="button" id="resetBtn" class="bg-gray-500 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-600 transition-colors" style="display: none;">
                    <i class="fas fa-times mr-2"></i> Reset
                </button>
            </div>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-400">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Search Results Info -->
    <div id="searchInfo">
        @include('admin.branches.partials.search-info', [
            'searchTerm' => $searchTerm ?? null,
            'total' => $branches->total(),
            'hasResults' => $branches->count() > 0
        ])
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="hidden mb-4 p-3 bg-blue-50 border-l-4 border-blue-400">
        <div class="flex items-center">
            <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>
            <span class="text-sm text-blue-700">Mencari cabang...</span>
        </div>
    </div>

    <!-- Table Container -->
    <div id="tableContainer">
        @include('admin.branches.partials.table', [
            'branches' => $branches,
            'searchTerm' => $searchTerm ?? null
        ])
    </div>

    <!-- Pagination Container -->
    <div id="paginationContainer" class="mt-4">
        @if($branches->hasPages())
            {{ $branches->appends(['search' => $searchTerm])->links() }}
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('searchForm');
    const searchInput = document.getElementById('searchInput');
    const searchBtn = document.getElementById('searchBtn');
    const resetBtn = document.getElementById('resetBtn');
    const loadingIndicator = document.getElementById('loadingIndicator');
    const searchInfo = document.getElementById('searchInfo');
    const tableContainer = document.getElementById('tableContainer');
    const paginationContainer = document.getElementById('paginationContainer');

    // Show/hide reset button based on search input
    function toggleResetButton() {
        if (searchInput.value.trim() !== '') {
            resetBtn.style.display = 'block';
        } else {
            resetBtn.style.display = 'none';
        }
    }

    // Initial check
    toggleResetButton();

    // Listen for input changes
    searchInput.addEventListener('input', function() {
        toggleResetButton();
    });

    // Handle search form submission
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        performSearch();
    });

    // Handle reset button click
    resetBtn.addEventListener('click', function() {
        resetSearch();
    });

    // Real-time search with debounce
    let searchTimeout;
       searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            if (searchInput.value.trim() !== '') {
                performSearch();
            }
        }, 500); // 500ms delay
    });

    // Perform search function
    function performSearch() {
        const searchTerm = searchInput.value.trim();
        
        // Show loading indicator
        loadingIndicator.classList.remove('hidden');
        searchBtn.disabled = true;
        searchBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> <span>Mencari...</span>';

        // Prepare form data
        const formData = new FormData();
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        formData.append('search', searchTerm);

        // Make AJAX request
        fetch('{{ route("admin.branches.search") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update search info
                searchInfo.innerHTML = data.search_info;
                
                // Update table
                tableContainer.innerHTML = data.html;
                
                // Update pagination
                paginationContainer.innerHTML = data.pagination;
                
                // Update URL without page reload
                const url = new URL(window.location);
                if (searchTerm) {
                    url.searchParams.set('search', searchTerm);
                } else {
                    url.searchParams.delete('search');
                }
                window.history.pushState({}, '', url);
            }
        })
        .catch(error => {
            console.error('Search error:', error);
            showErrorMessage('Terjadi kesalahan saat mencari data. Silakan coba lagi.');
        })
        .finally(() => {
            // Hide loading indicator
            loadingIndicator.classList.add('hidden');
            searchBtn.disabled = false;
            searchBtn.innerHTML = '<i class="fas fa-search mr-2"></i> <span>Cari</span>';
        });
    }

    // Reset search function
    function resetSearch() {
        searchInput.value = '';
        toggleResetButton();
        performSearch();
    }

    // Global reset function for use in partials
    window.resetSearch = resetSearch;

    // Handle pagination clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.pagination a')) {
            e.preventDefault();
            const url = e.target.closest('.pagination a').href;
            const urlParams = new URLSearchParams(url.split('?')[1]);
            const page = urlParams.get('page');
            const currentSearch = searchInput.value.trim();
            
            loadPage(page, currentSearch);
        }
    });

    // Load specific page
    function loadPage(page, searchTerm = '') {
        loadingIndicator.classList.remove('hidden');
        
        const formData = new FormData();
        formData.append('_token', document.querySelector('input[name="_token"]').value);
        formData.append('search', searchTerm);
        formData.append('page', page);

        fetch('{{ route("admin.branches.search") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                searchInfo.innerHTML = data.search_info;
                tableContainer.innerHTML = data.html;
                paginationContainer.innerHTML = data.pagination;
                
                // Update URL
                const url = new URL(window.location);
                if (searchTerm) {
                    url.searchParams.set('search', searchTerm);
                } else {
                    url.searchParams.delete('search');
                }
                url.searchParams.set('page', page);
                window.history.pushState({}, '', url);
                
                // Scroll to top of table
                tableContainer.scrollIntoView({ behavior: 'smooth' });
            }
        })
        .catch(error => {
            console.error('Pagination error:', error);
            showErrorMessage('Terjadi kesalahan saat memuat halaman. Silakan coba lagi.');
        })
        .finally(() => {
            loadingIndicator.classList.add('hidden');
        });
    }

    // Show error message
    function showErrorMessage(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'mb-4 p-4 bg-red-50 border-l-4 border-red-400';
        errorDiv.innerHTML = `
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">${message}</p>
                </div>
            </div>
        `;
        
        searchInfo.insertAdjacentElement('afterend', errorDiv);
        
        // Remove error message after 5 seconds
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function(e) {
        const urlParams = new URLSearchParams(window.location.search);
        const searchTerm = urlParams.get('search') || '';
        searchInput.value = searchTerm;
        toggleResetButton();
        performSearch();
    });
});
</script>
@endpush
@endsection
