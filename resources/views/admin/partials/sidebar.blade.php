<div class="fixed left-0 top-0 w-64 h-full bg-white shadow-xl z-50 flex flex-col">
    <!-- Logo -->
    <div class="p-6 border-b border-gray-200 flex-shrink-0">
        <div class="flex items-center">
            <i class="fas fa-fish text-blue-700 text-2xl mr-3 animate-pulse"></i>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Ikan Jabar</h1>
                <p class="text-sm text-gray-500">Admin Panel</p>
            </div>
        </div>
    </div>

    <!-- Navigation Menu - Scrollable -->
    <nav class="mt-6 flex-1 overflow-y-auto hidden-scrollbar">
        <div class="px-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Menu Utama</p>
        </div>

        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}" class="nav-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-all duration-300 transform hover:translate-x-2 {{ request()->routeIs('admin.dashboard') ? 'sidebar-active' : '' }}">
            <i class="fas fa-tachometer-alt mr-3 transition-transform duration-300 hover:rotate-12"></i>
            <span class="transition-all duration-300">Dashboard</span>
        </a>

        <!-- Branches -->
        <a href="{{ route('admin.branches.index') }}" class="nav-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-all duration-300 transform hover:translate-x-2 {{ request()->routeIs('admin.branches.*') ? 'sidebar-active' : '' }}">
            <i class="fas fa-building mr-3 transition-transform duration-300 hover:scale-110"></i>
            <span class="transition-all duration-300">Monitoring</span>
        </a>

        <!-- Users -->
        <a href="{{ route('admin.users.index') }}" class="nav-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-all duration-300 transform hover:translate-x-2 {{ request()->routeIs('admin.users.*') ? 'sidebar-active' : '' }}">
            <i class="fas fa-users mr-3 transition-transform duration-300 hover:scale-110"></i>
            <span class="transition-all duration-300">Pengguna</span>
        </a>


        <!-- Transaksi -->
        <div class="mt-4">
            <div class="px-6 py-2">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider animate-fade-in">Transaksi</p>
            </div>
            {{-- <a href="{{ route('admin.sales.index') }}" class="nav-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-all duration-300 transform hover:translate-x-2 {{ request()->routeIs('admin.sales.*') ? 'sidebar-active' : '' }}">
                <i class="fas fa-shopping-cart mr-3 transition-transform duration-300 hover:scale-110"></i>
                <span class="transition-all duration-300">Penjualan</span>
            </a> --}}
            {{-- <a href="{{ route('admin.fish-batches.transfer.form', 1) }}" class="nav-item flex items-center px-6 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-all duration-300 transform hover:translate-x-2">
                <i class="fas fa-exchange-alt mr-3 transition-transform duration-300 hover:rotate-180"></i>
                <span class="transition-all duration-300">Transfer Batch</span>
            </a> --}}
        </div>

    </nav>
</div>

<style>
/* Hidden Scrollbar - Tetap bisa scroll tapi scrollbar tidak terlihat */
.hidden-scrollbar {
    /* Untuk Webkit browsers (Chrome, Safari, Edge) */
    -ms-overflow-style: none;  /* Internet Explorer 10+ */
    scrollbar-width: none;  /* Firefox */
}

.hidden-scrollbar::-webkit-scrollbar {
    display: none;  /* Webkit browsers */
}

/* Custom Animations */
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.6s ease-out;
}

/* Hover Effects */
.nav-item {
    position: relative;
    overflow: hidden;
}

.nav-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
    transition: left 0.5s;
}

.nav-item:hover::before {
    left: 100%;
}

/* Active State Animation */
.sidebar-active {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe) !important;
    color: #1d4ed8 !important;
    border-right: 4px solid #3b82f6;
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.sidebar-active i {
    color: #1d4ed8;
    animation: pulse 2s infinite;
}

/* Staggered Animation for Menu Items */
.nav-item:nth-child(1) { animation-delay: 0.1s; }
.nav-item:nth-child(2) { animation-delay: 0.2s; }
.nav-item:nth-child(3) { animation-delay: 0.3s; }
.nav-item:nth-child(4) { animation-delay: 0.4s; }
.nav-item:nth-child(5) { animation-delay: 0.5s; }

/* Smooth Scroll Behavior */
.hidden-scrollbar {
    scroll-behavior: smooth;
}

/* Loading Animation for Icons */
@keyframes bounce {
    0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
    60% {
        transform: translateY(-5px);
    }
}

.hover\:bounce:hover {
    animation: bounce 1s;
}
</style>
