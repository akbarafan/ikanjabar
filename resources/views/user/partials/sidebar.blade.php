<div class="fixed left-0 top-0 w-64 h-full bg-white shadow-xl z-50">
    <!-- Logo -->
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center">
            <i class="fas fa-fish text-blue-500 text-2xl mr-3"></i>
            <div>
                <h1 class="text-xl font-bold text-gray-900">AquaCulture</h1>
                <p class="text-sm text-gray-500">Monitor System</p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="mt-6">
        <div class="px-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Menu Utama</p>
        </div>

        <!-- Dashboard -->
        <a href="{{ route('user.dashboard') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors {{ request()->routeIs('user.dashboard') ? 'sidebar-active' : '' }}">
            <i class="fas fa-tachometer-alt mr-3"></i>
            <span>Dashboard</span>
        </a>

        <!-- Kolam -->
        <div class="mt-2">
            <div class="px-6 py-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Manajemen Kolam</p>
            </div>
            <a href="#" class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                <i class="fas fa-swimming-pool mr-3"></i>
                <span>Data Kolam</span>
            </a>
            <a href="#" class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                <i class="fas fa-fish mr-3"></i>
                <span>Batch Ikan</span>
            </a>
        </div>

        <!-- Monitoring -->
        <div class="mt-4">
            <div class="px-6 py-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Monitoring</p>
            </div>
            <a href="#" class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                <i class="fas fa-tint mr-3"></i>
                <span>Kualitas Air</span>
            </a>
            <a href="#" class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                <i class="fas fa-seedling mr-3"></i>
                <span>Pertumbuhan</span>
            </a>
            <a href="#" class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                <i class="fas fa-utensils mr-3"></i>
                <span>Pemberian Pakan</span>
            </a>
            <a href="#" class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                <i class="fas fa-skull-crossbones mr-3"></i>
                <span>Mortalitas</span>
            </a>
        </div>

        <!-- Transaksi -->
        <div class="mt-4">
            <div class="px-6 py-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Transaksi</p>
            </div>
            <a href="#" class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                <i class="fas fa-shopping-cart mr-3"></i>
                <span>Penjualan</span>
            </a>
            <a href="#" class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                <i class="fas fa-exchange-alt mr-3"></i>
                <span>Transfer Batch</span>
            </a>
        </div>

        <!-- Laporan -->
        <div class="mt-4">
            <div class="px-6 py-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Laporan</p>
            </div>
            <a href="#" class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                <i class="fas fa-chart-bar mr-3"></i>
                <span>Laporan Produksi</span>
            </a>
            <a href="#" class="flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                <i class="fas fa-file-alt mr-3"></i>
                <span>Laporan Keuangan</span>
            </a>
        </div>
    </nav>
</div>
