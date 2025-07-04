<!-- Mobile Overlay -->
<div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden opacity-0 invisible transition-all duration-300"></div>

<!-- Sidebar -->
<div id="sidebar" class="fixed left-0 top-0 w-64 h-full bg-white shadow-2xl z-50 flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-out">
    <!-- Header -->
    <div class="p-4 border-b border-gray-200 flex-shrink-0 bg-gradient-to-r from-blue-50 to-indigo-50">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Ikan Jabar</h1>
                    <p class="text-xs text-gray-500 truncate max-w-32">
                        {{ Auth::user()->branch->name ?? 'Cabang Demo' }}
                    </p>
                </div>
            </div>
            <!-- Close button for mobile -->
            <button id="sidebar-close" class="lg:hidden text-gray-500 hover:text-gray-700 p-2 rounded-lg hover:bg-gray-100 transition-colors focus:outline-none focus:ring-2 focus:ring-gray-300">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <!-- User Info Section -->
    <div class="p-4 border-b border-gray-200 bg-gray-50">
        <div class="flex items-center space-x-3">
            <img class="h-10 w-10 rounded-full border-2 border-blue-200 object-cover"
                 src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->full_name) }}&background=667eea&color=fff&size=128"
                 alt="Profile">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->full_name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mt-1
                    @if(Auth::user()->hasRole('admin')) bg-red-100 text-red-700
                    @elseif(Auth::user()->hasRole('branches')) bg-blue-100 text-blue-700
                    @elseif(Auth::user()->hasRole('student')) bg-blue-100 text-blue-700
                    @else bg-gray-100 text-gray-700
                    @endif">
                    @if(Auth::user()->hasRole('admin'))
                        <i class="fas fa-crown mr-1"></i>Admin
                    @elseif(Auth::user()->hasRole('branches'))
                        <i class="fas fa-building mr-1"></i>Manager
                    @elseif(Auth::user()->hasRole('student'))
                        <i class="fas fa-graduation-cap mr-1"></i>Student
                    @else
                        <i class="fas fa-user mr-1"></i>User
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 overflow-y-auto py-4 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" class="nav-item group flex items-center px-4 py-3 mx-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 shadow-sm' : '' }}">
            <i class="fas fa-tachometer-alt mr-3 w-5 text-center group-hover:scale-110 transition-transform"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        <!-- Data Master Section -->
        <div class="mt-6">
            <div class="px-4 py-2 mb-2">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Data Master</p>
            </div>

            <a href="{{ route('fish-types.index') }}" class="nav-item group flex items-center px-4 py-3 mx-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 {{ request()->routeIs('fish-types.*') ? 'bg-blue-50 text-blue-600 shadow-sm' : '' }}">
                <i class="fas fa-fish mr-3 w-5 text-center group-hover:scale-110 transition-transform"></i>
                <span class="font-medium">Jenis Ikan</span>
            </a>

            <a href="{{ route('ponds.index') }}" class="nav-item group flex items-center px-4 py-3 mx-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 {{ request()->routeIs('ponds.*') ? 'bg-blue-50 text-blue-600 shadow-sm' : '' }}">
                <i class="fas fa-swimming-pool mr-3 w-5 text-center group-hover:scale-110 transition-transform"></i>
                <span class="font-medium">Kolam</span>
            </a>
        </div>

        <!-- Monitoring Section -->
        <div class="mt-6">
            <div class="px-4 py-2 mb-2">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Monitoring</p>
            </div>

            @php
            $monitoringMenus = [
                ['route' => 'fish-batches.*', 'url' => route('fish-batches.index'), 'icon' => 'fas fa-layer-group', 'label' => 'Batch Ikan'],
                ['route' => 'water-qualities.*', 'url' => route('water-qualities.index'), 'icon' => 'fas fa-tint', 'label' => 'Kualitas Air'],
                ['route' => 'fish-growth.*', 'url' => route('fish-growth.index'), 'icon' => 'fas fa-chart-line', 'label' => 'Pertumbuhan'],
                ['route' => 'feedings.*', 'url' => route('feedings.index'), 'icon' => 'fas fa-utensils', 'label' => 'Pemberian Pakan'],
                ['route' => 'mortalities.*', 'url' => route('mortalities.index'), 'icon' => 'fas fa-skull-crossbones', 'label' => 'Mortalitas']
            ];
            @endphp

            @foreach($monitoringMenus as $menu)
            <a href="{{ $menu['url'] }}" class="nav-item group flex items-center px-4 py-3 mx-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 {{ request()->routeIs($menu['route']) ? 'bg-blue-50 text-blue-600 shadow-sm' : '' }}">
                <i class="{{ $menu['icon'] }} mr-3 w-5 text-center group-hover:scale-110 transition-transform"></i>
                <span class="font-medium">{{ $menu['label'] }}</span>
            </a>
            @endforeach
        </div>

        <!-- Transaksi Section -->
        <div class="mt-6">
            <div class="px-4 py-2 mb-2">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Transaksi</p>
            </div>

            <a href="{{ route('sales.index') }}" class="nav-item group flex items-center px-4 py-3 mx-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 {{ request()->routeIs('sales.*') ? 'bg-blue-50 text-blue-600 shadow-sm' : '' }}">
                <i class="fas fa-shopping-cart mr-3 w-5 text-center group-hover:scale-110 transition-transform"></i>
                <span class="font-medium">Penjualan</span>
            </a>

            <a href="{{ route('fish-transfers.index') }}" class="nav-item group flex items-center px-4 py-3 mx-2 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-all duration-200 {{ request()->routeIs('fish-transfers.*') ? 'bg-blue-50 text-blue-600 shadow-sm' : '' }}">
                <i class="fas fa-exchange-alt mr-3 w-5 text-center group-hover:scale-110 transition-transform"></i>
                <span class="font-medium">Transfer Batch</span>
            </a>
        </div>

        <!-- Laporan Section -->
        <div class="mt-6 pb-6">
            <div class="px-4 py-2 mb-2">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Laporan</p>
            </div>

            @php
            $reportMenus = [
                ['icon' => 'fas fa-chart-bar', 'label' => 'Laporan Produksi'],
                ['icon' => 'fas fa-file-invoice-dollar', 'label' => 'Laporan Keuangan']
            ];
            @endphp

            @foreach($reportMenus as $menu)
            <div class="nav-item flex items-center px-4 py-3 mx-2 rounded-lg text-gray-400 cursor-not-allowed opacity-60">
                <i class="{{ $menu['icon'] }} mr-3 w-5 text-center"></i>
                <span class="font-medium">{{ $menu['label'] }}</span>
                <span class="ml-auto text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full">Soon</span>
            </div>
            @endforeach
        </div>
    </nav>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const sidebarClose = document.getElementById('sidebar-close');

    // Utility functions
    const openSidebar = () => {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('opacity-0', 'invisible');
        document.body.classList.add('overflow-hidden', 'lg:overflow-auto');
    };

    const closeSidebar = () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('opacity-0', 'invisible');
        document.body.classList.remove('overflow-hidden');
    };

    // Event listeners
    mobileMenuBtn?.addEventListener('click', openSidebar);
    sidebarClose?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    // Keyboard navigation
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !sidebar.classList.contains('-translate-x-full')) {
            closeSidebar();
        }
    });

    // Handle window resize
    const handleResize = () => {
        if (window.innerWidth >= 1024) {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.add('opacity-0', 'invisible');
            document.body.classList.remove('overflow-hidden');
        }
    };

    window.addEventListener('resize', handleResize);

    // Touch/swipe support for mobile
    let touchStartX = 0;
    let touchStartY = 0;
    let isSwiping = false;

    const handleTouchStart = (e) => {
        touchStartX = e.touches[0].clientX;
        touchStartY = e.touches[0].clientY;
        isSwiping = false;
    };

    const handleTouchMove = (e) => {
        if (!touchStartX || !touchStartY) return;

        const touchCurrentX = e.touches[0].clientX;
        const touchCurrentY = e.touches[0].clientY;
        const diffX = touchCurrentX - touchStartX;
        const diffY = touchCurrentY - touchStartY;

        // Only consider horizontal swipes
        if (Math.abs(diffX) > Math.abs(diffY)) {
            isSwiping = true;
        }
    };

    const handleTouchEnd = (e) => {
        if (!touchStartX || !isSwiping) return;

        const touchEndX = e.changedTouches[0].clientX;
        const swipeDistance = touchEndX - touchStartX;
        const minSwipeDistance = 50;

        if (window.innerWidth < 1024) {
            // Swipe right from left edge to open
            if (swipeDistance > minSwipeDistance && touchStartX < 50 && sidebar.classList.contains('-translate-x-full')) {
                openSidebar();
            }
            // Swipe left to close
            else if (swipeDistance < -minSwipeDistance && !sidebar.classList.contains('-translate-x-full')) {
                closeSidebar();
            }
        }

        // Reset values
        touchStartX = 0;
        touchStartY = 0;
        isSwiping = false;
    };

    // Add touch event listeners
    document.addEventListener('touchstart', handleTouchStart, { passive: true });
    document.addEventListener('touchmove', handleTouchMove, { passive: true });
    document.addEventListener('touchend', handleTouchEnd, { passive: true });

    // Auto-close sidebar on mobile when clicking nav items
    const navItems = document.querySelectorAll('.nav-item:not(.cursor-not-allowed)');
    navItems.forEach(item => {
        item.addEventListener('click', () => {
            if (window.innerWidth < 1024) {
                setTimeout(closeSidebar, 150); // Small delay for better UX
            }
        });
    });
});
</script>
@endpush

