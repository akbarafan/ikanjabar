<div class="fixed left-0 top-16 h-full w-64 bg-gray-900 shadow-xl z-40 transform transition-transform duration-300" id="sidebar">
    <div class="p-4">
        <div class="space-y-2">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'sidebar-active' : 'sidebar-item' }}">
                <i class="fas fa-tachometer-alt mr-3"></i>
                <span>Dashboard Utama</span>
            </a>

            <!-- Manajemen Cabang -->
            <div class="space-y-1">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Manajemen Cabang
                </div>
                <a href="{{ route('branches.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('branches.*') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-building mr-3"></i>
                    <span>Daftar Cabang</span>
                </a>
                <a href="{{ route('ponds.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('ponds.*') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-water mr-3"></i>
                    <span>Manajemen Kolam</span>
                </a>
            </div>

            <!-- Budidaya Ikan -->
            <div class="space-y-1">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Budidaya Ikan
                </div>
                <a href="{{ route('fish-batches.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('fish-batches.*') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-fish mr-3"></i>
                    <span>Batch Ikan</span>
                </a>
                <a href="{{ route('fish-types.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('fish-types.*') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-list mr-3"></i>
                    <span>Jenis Ikan</span>
                </a>
                <a href="{{ route('feeding.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('feeding.*') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-seedling mr-3"></i>
                    <span>Manajemen Pakan</span>
                </a>
            </div>

            <!-- Monitoring -->
            <div class="space-y-1">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Monitoring
                </div>
                <a href="{{ route('water-quality.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('water-quality.*') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-tint mr-3"></i>
                    <span>Kualitas Air</span>
                </a>
                <a href="{{ route('mortality.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('mortality.*') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-exclamation-triangle mr-3"></i>
                    <span>Mortalitas Ikan</span>
                </a>
                <a href="{{ route('growth-monitoring.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('growth-monitoring.*') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-chart-line mr-3"></i>
                    <span>Monitoring Pertumbuhan</span>
                </a>
                <a href="{{ route('alerts.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('alerts.*') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-bell mr-3"></i>
                    <span>Peringatan & Alert</span>
                    <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">3</span>
                </a>
            </div>

            <!-- Produksi & Panen -->
            <div class="space-y-1">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Produksi & Panen
                </div>
                <a href="{{ route('harvest.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('harvest.*') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-hand-holding mr-3"></i>
                    <span>Manajemen Panen</span>
                </a>
                <a href="{{ route('harvest.prediction') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('harvest.prediction') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-calendar-alt mr-3"></i>
                    <span>Prediksi Panen</span>
                </a>
                <a href="{{ route('transfers.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('transfers.*') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-exchange-alt mr-3"></i>
                    <span>Transfer Ikan</span>
                </a>
            </div>

            <!-- Laporan & Analisis -->
            <div class="space-y-1">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Laporan & Analisis
                </div>
                <a href="{{ route('reports.production') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('reports.production') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-chart-bar mr-3"></i>
                    <span>Laporan Produksi</span>
                </a>
                <a href="{{ route('reports.financial') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('reports.financial') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-dollar-sign mr-3"></i>
                    <span>Laporan Keuangan</span>
                </a>
                <a href="{{ route('reports.comprehensive') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('reports.comprehensive') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-file-alt mr-3"></i>
                    <span>Laporan Komprehensif</span>
                </a>
                <a href="{{ route('analytics.dashboard') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('analytics.*') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-chart-pie mr-3"></i>
                    <span>Analisis Data</span>
                </a>
            </div>

            <!-- Pengaturan -->
            <div class="space-y-1">
                <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Pengaturan
                </div>
                <a href="{{ route('users.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('users.*') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-users mr-3"></i>
                    <span>Manajemen User</span>
                </a>
                <a href="{{ route('settings.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('settings.*') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-cog mr-3"></i>
                    <span>Pengaturan Sistem</span>
                </a>
                <a href="{{ route('backup.index') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('backup.*') ? 'sidebar-active' : 'sidebar-item' }}">
                    <i class="fas fa-database mr-3"></i>
                    <span>Backup & Restore</span>
                </a>
            </div>
        </div>
    </div>
</div>

