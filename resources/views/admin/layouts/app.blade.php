<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e3a8a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    
    <title>@yield('title', 'Admin Dashboard') - Sistem Monitoring Perikanan</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Mobile CSS -->
    <link href="{{ asset('css/mobile-dashboard.css') }}" rel="stylesheet">
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        }
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
        }
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }
        .status-healthy { @apply bg-green-100 text-green-800 border-green-200; }
        .status-warning { @apply bg-yellow-100 text-yellow-800 border-yellow-200; }
        .status-danger { @apply bg-red-100 text-red-800 border-red-200; }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
        .sidebar-active {
            @apply bg-blue-50 text-blue-700 border-r-4 border-blue-500;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .ml-64 {
                margin-left: 0 !important;
            }
            
            .sidebar-desktop {
                display: none;
            }
            
            .mobile-header {
                display: block;
            }
            
            .p-6 {
                padding: 1rem;
            }
            
            .gap-6 {
                gap: 1rem;
            }
            
            .grid-cols-4 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            
            .lg\:grid-cols-2 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
            
            .text-2xl {
                font-size: 1.25rem;
            }
            
            .text-lg {
                font-size: 1rem;
            }
            
            /* Chart mobile optimization */
            .bar-chart-container,
            .doughnut-chart-container {
                height: 250px;
                padding: 10px;
            }
            
            /* Table mobile scroll */
            .overflow-x-auto {
                position: relative;
            }
            
            .overflow-x-auto::after {
                content: '→';
                position: absolute;
                right: 10px;
                top: 50%;
                transform: translateY(-50%);
                background: rgba(59, 130, 246, 0.8);
                color: white;
                padding: 2px 6px;
                border-radius: 4px;
                font-size: 12px;
                pointer-events: none;
                opacity: 0.7;
            }
        }
        
        @media (min-width: 769px) {
            .mobile-header {
                display: none;
            }
            
            .sidebar-desktop {
                display: block;
            }
        }
        
        /* Mobile Navigation */
        .mobile-nav {
            display: none;
        }
        
        @media (max-width: 768px) {
            .mobile-nav {
                display: block;
                padding-bottom: env(safe-area-inset-bottom, 0);
            }
            
            body {
                padding-bottom: 70px;
            }
        }
        
        /* Loading states */
        .mobile-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.6;
        }
        
        .mobile-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 min-h-screen">
    <div class="flex">
        <!-- Desktop Sidebar -->
        <div class="sidebar-desktop">
            @include('admin.partials.sidebar')
        </div>

        <!-- Main Content -->
        <div class="flex-1 ml-64">
            <!-- Desktop Navbar -->
            <div class="sidebar-desktop">
                @include('admin.partials.navbar')
            </div>
            
            <!-- Mobile Header -->
            <header class="mobile-header bg-gradient-to-r from-blue-800 via-blue-600 to-cyan-500 shadow-lg sticky top-0 z-40" style="display: none;">
                <div class="px-4">
                    <div class="flex items-center justify-between h-14">
                        <div class="flex items-center">
                            <button id="mobileMenuToggle" class="text-white p-2 rounded-md hover:bg-white hover:bg-opacity-20 transition-colors">
                                <i class="fas fa-bars"></i>
                            </button>
                            <h1 class="text-white text-lg font-semibold ml-2 truncate">
                                @yield('page-title', 'Dashboard')
                            </h1>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <!-- Mobile Notifications -->
                            <button class="text-white p-2 rounded-md hover:bg-white hover:bg-opacity-20 transition-colors relative">
                                <i class="fas fa-bell text-sm"></i>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center">5</span>
                            </button>
                            
                            <!-- Mobile Profile -->
                            <button class="flex items-center text-white p-1 rounded-md hover:bg-white hover:bg-opacity-20 transition-colors">
                                <img class="h-6 w-6 rounded-full border border-white" src="https://ui-avatars.com/api/?name=Admin&background=1e3a8a&color=fff" alt="Profile">
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6">
                @yield('content')
            </main>

            <!-- Desktop Footer -->
            <div class="sidebar-desktop">
                @include('admin.partials.footer')
            </div>
        </div>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div id="mobileSidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div id="mobileSidebar" class="fixed left-0 top-0 bottom-0 w-64 bg-white shadow-xl transform -translate-x-full transition-transform duration-300">
            <div class="p-4 border-b">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800">Menu</h2>
                    <button id="closeMobileSidebar" class="p-2 rounded-md hover:bg-gray-100">
                        <i class="fas fa-times text-gray-600"></i>
                    </button>
                </div>
            </div>
            
            <nav class="p-4">
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center p-3 rounded-md hover:bg-gray-100 transition-colors">
                            <i class="fas fa-home text-gray-600 mr-3"></i>
                            <span class="text-gray-800">Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.branches.index') }}" class="flex items-center p-3 rounded-md hover:bg-gray-100 transition-colors">
                            <i class="fas fa-building text-gray-600 mr-3"></i>
                            <span class="text-gray-800">Cabang</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.ponds.index') }}" class="flex items-center p-3 rounded-md hover:bg-gray-100 transition-colors">
                            <i class="fas fa-water text-gray-600 mr-3"></i>
                            <span class="text-gray-800">Kolam</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.fish-batches.index') }}" class="flex items-center p-3 rounded-md hover:bg-gray-100 transition-colors">
                            <i class="fas fa-fish text-gray-600 mr-3"></i>
                            <span class="text-gray-800">Batch Ikan</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.users.index') }}" class="flex items-center p-3 rounded-md hover:bg-gray-100 transition-colors">
                            <i class="fas fa-users text-gray-600 mr-3"></i>
                            <span class="text-gray-800">Pengguna</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <!-- Mobile Bottom Navigation -->
    @include('admin.components.mobile-nav', ['active' => Request::segment(2) ?? 'dashboard'])

    <!-- Mobile JavaScript -->
    <script src="{{ asset('js/mobile-utils.js') }}"></script>
    
    <script>
        // Mobile sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mobileSidebarOverlay = document.getElementById('mobileSidebarOverlay');
            const mobileSidebar = document.getElementById('mobileSidebar');
            const closeMobileSidebar = document.getElementById('closeMobileSidebar');
            
            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    mobileSidebarOverlay.classList.remove('hidden');
                    setTimeout(() => {
                        mobileSidebar.classList.remove('-translate-x-full');
                    }, 10);
                });
            }

            if (closeMobileSidebar) {
                closeMobileSidebar.addEventListener('click', closeSidebar);
            }
            
            if (mobileSidebarOverlay) {
                mobileSidebarOverlay.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeSidebar();
                    }
                });
            }

            function closeSidebar() {
                if (mobileSidebar && mobileSidebarOverlay) {
                    mobileSidebar.classList.add('-translate-x-full');
                    setTimeout(() => {
                        mobileSidebarOverlay.classList.add('hidden');
                    }, 300);
                }
            }
            
            // Auto-hide mobile nav on scroll
            let lastScrollTop = 0;
            const mobileNav = document.querySelector('.mobile-nav');
            
            if (mobileNav && window.innerWidth <= 768) {
                window.addEventListener('scroll', function() {
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    
                    if (scrollTop > lastScrollTop && scrollTop > 100) {
                        // Scrolling down
                        mobileNav.style.transform = 'translateY(100%)';
                    } else {
                        // Scrolling up
                        mobileNav.style.transform = 'translateY(0)';
                    }
                    
                    lastScrollTop = scrollTop;
                });
            }
        });
        
        // Fix viewport height for mobile
        function setVH() {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }
        
        setVH();
        window.addEventListener('resize', setVH);
        window.addEventListener('orientationchange', () => {
            setTimeout(setVH, 100);
        });
    </script>
    
    @stack('scripts')
</body>
</html>
