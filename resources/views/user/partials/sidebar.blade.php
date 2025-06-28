<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

<!-- Sidebar -->
<div id="sidebar" class="fixed left-0 top-0 w-64 h-full bg-white shadow-xl z-50 flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out">
    <!-- Logo -->
    <div class="p-4 border-b border-gray-200 flex-shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-fish text-blue-600 text-xl mr-2"></i>
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Ikan Jabar</h1>
                    <p class="text-xs text-gray-500">{{ $branchInfo->name ?? 'Cabang Demo' }}</p>
                </div>
            </div>
            <!-- Close button for mobile -->
            <button id="sidebar-close" class="lg:hidden text-gray-500 hover:text-gray-700 p-1 rounded-md hover:bg-gray-100 transition-colors">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

        <!-- Navigation Menu -->
        <nav class="mt-4 flex-1 overflow-y-auto">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" class="nav-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                <i class="fas fa-tachometer-alt mr-3 w-5 text-center"></i>
                <span>Dashboard</span>
            </a>

            <!-- Data Master -->
            <div class="mt-6">
                <div class="px-4 py-2">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Data Master</p>
                </div>

                <a href="{{ route('fish-types.index') }}" class="nav-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 {{ request()->routeIs('fish-types.*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                    <i class="fas fa-fish mr-3 w-5 text-center"></i>
                    <span>Jenis Ikan</span>
                </a>

                <a href="{{ route('ponds.index') }}" class="nav-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 {{ request()->routeIs('ponds.*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                    <i class="fas fa-swimming-pool mr-3 w-5 text-center"></i>
                    <span>Kolam</span>
                </a>
            </div>

            <!-- Monitoring -->
            <div class="mt-6">
                <div class="px-4 py-2">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Monitoring</p>
                </div>

                <a href="{{ route('fish-batches.index') }}" class="nav-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 {{ request()->routeIs('fish-batches.*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                    <i class="fas fa-layer-group mr-3 w-5 text-center"></i>
                    <span>Batch Ikan</span>
                </a>

                <a href="{{ route('water-qualities.index') }}" class="nav-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 {{ request()->routeIs('water-qualities.*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                    <i class="fas fa-tint mr-3 w-5 text-center"></i>
                    <span>Kualitas Air</span>
                </a>

                <a href="{{ route('fish-growth.index') }}" class="nav-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 {{ request()->routeIs('fish-growth.*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                    <i class="fas fa-chart-line mr-3 w-5 text-center"></i>
                    <span>Pertumbuhan</span>
                </a>

                <a href="{{ route('feedings.index') }}" class="nav-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 {{ request()->routeIs('feedings.*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                    <i class="fas fa-utensils mr-3 w-5 text-center"></i>
                    <span>Pemberian Pakan</span>
                </a>

                <a href="{{ route('mortalities.index') }}" class="nav-item flex items-center px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 {{ request()->routeIs('mortalities.*') ? 'bg-blue-50 text-blue-600 border-r-2 border-blue-600' : '' }}">
                    <i class="fas fa-skull-crossbones mr-3 w-5 text-center"></i>
                    <span>Mortalitas</span>
                </a>
            </div>

            <!-- Transaksi -->
            <div class="mt-6">
                <div class="px-4 py-2">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Transaksi</p>
                </div>

                <a href="#" class="nav-item flex items-center px-4 py-3 text-gray-400 cursor-not-allowed opacity-60">
                    <i class="fas fa-shopping-cart mr-3 w-5 text-center"></i>
                    <span>Penjualan</span>
                    <span class="ml-auto text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full">Soon</span>
                </a>

                <a href="#" class="nav-item flex items-center px-4 py-3 text-gray-400 cursor-not-allowed opacity-60">
                    <i class="fas fa-exchange-alt mr-3 w-5 text-center"></i>
                    <span>Transfer Batch</span>
                    <span class="ml-auto text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full">Soon</span>
                </a>
            </div>

            <!-- Laporan -->
            <div class="mt-6 pb-6">
                <div class="px-4 py-2">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Laporan</p>
                </div>

                <a href="#" class="nav-item flex items-center px-4 py-3 text-gray-400 cursor-not-allowed opacity-60">
                    <i class="fas fa-chart-bar mr-3 w-5 text-center"></i>
                    <span>Laporan Produksi</span>
                    <span class="ml-auto text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full">Soon</span>
                </a>

                <a href="#" class="nav-item flex items-center px-4 py-3 text-gray-400 cursor-not-allowed opacity-60">
                    <i class="fas fa-file-invoice-dollar mr-3 w-5 text-center"></i>
                    <span>Laporan Keuangan</span>
                    <span class="ml-auto text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full">Soon</span>
                </a>
            </div>
        </nav>
    </div>

    <!-- Mobile Menu Button -->
    <button id="mobile-menu-btn" class="lg:hidden fixed top-4 left-4 z-60 bg-white p-3 rounded-lg shadow-lg border border-gray-200 hover:bg-gray-50 transition-all duration-200 hover:shadow-xl">
        <i class="fas fa-bars text-gray-600"></i>
    </button>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const sidebarClose = document.getElementById('sidebar-close');

        // Toggle sidebar on mobile
        mobileMenuBtn.addEventListener('click', function() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });

        // Close sidebar
        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.style.overflow = '';
        }

        sidebarClose.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);

        // Close sidebar on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !sidebar.classList.contains('-translate-x-full')) {
                closeSidebar();
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.style.overflow = '';
            }
        });

        // Add smooth hover effects for active nav items
        const navItems = document.querySelectorAll('.nav-item:not(.cursor-not-allowed)');
        navItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                if (!this.classList.contains('cursor-not-allowed')) {
                    this.style.transform = 'translateX(4px)';
                }
            });
            item.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });

        // Add touch support for mobile
        let touchStartX = 0;
        let touchEndX = 0;

        document.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        });

        document.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            handleSwipe();
        });

        function handleSwipe() {
            if (window.innerWidth < 1024) {
                const swipeDistance = touchEndX - touchStartX;

                // Swipe right to open sidebar
                if (swipeDistance > 50 && touchStartX < 50 && sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }

                // Swipe left to close sidebar
                if (swipeDistance < -50 && !sidebar.classList.contains('-translate-x-full')) {
                    closeSidebar();
                }
            }
        }
    });
    </script>
