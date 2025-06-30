<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1e3a8a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="IkanJabar Admin">
    
    <title>@yield('title', 'Admin Dashboard') - IkanJabar</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="{{ asset('css/mobile-dashboard.css') }}" rel="stylesheet">
    
    @stack('styles')
    
    <style>
        :root {
            --vh: 1vh;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            height: calc(var(--vh, 1vh) * 100);
            overflow-x: hidden;
        }
        
        .container-mobile {
            max-width: 100%;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        @media (min-width: 640px) {
            .container-mobile {
                max-width: 640px;
                padding: 0 1.5rem;
            }
        }
        
        input, select, textarea {
            font-size: 16px;
        }
        
        html {
            scroll-behavior: smooth;
        }
        
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #06b6d4 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }
        
        .loading-screen.hidden {
            opacity: 0;
            pointer-events: none;
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
    </style>
</head>
<body class="bg-gray-50 antialiased">
    <!-- Loading Screen -->
    <div id="loadingScreen" class="loading-screen">
        <div class="text-center">
            <div class="loading-spinner mx-auto mb-4"></div>
            <p class="text-white text-sm">Loading...</p>
        </div>
    </div>
    
    <!-- Mobile Header -->
    <header class="bg-gradient-to-r from-blue-800 via-blue-600 to-cyan-500 shadow-lg sticky top-0 z-40">
        <div class="container-mobile">
            <div class="flex items-center justify-between h-14">
                <div class="flex items-center">
                    <button id="mobileMenuToggle" class="text-white p-2 rounded-md hover:bg-white hover:bg-opacity-20 transition-colors md:hidden">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="text-white text-lg font-semibold ml-2 truncate">
                        @yield('page-title', 'Dashboard')
                    </h1>
                </div>
                
                <div class="flex items-center space-x-2">
                    <!-- Notifications -->
                    <button class="text-white p-2 rounded-md hover:bg-white hover:bg-opacity-20 transition-colors relative">
                        <i class="fas fa-bell text-sm"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 flex items-center justify-center text-xs">3</span>
                    </button>
                    
                    <!-- Profile -->
                    <button class="flex items-center text-white p-1 rounded-md hover:bg-white hover:bg-opacity-20 transition-colors">
                        <img class="h-6 w-6 rounded-full border border-white" src="https://ui-avatars.com/api/?name=Admin&background=1e3a8a&color=fff" alt="Profile">
                    </button>
                </div>
            </div>
        </div>
    </header>

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
                    </li>fjy
                    <li>
                        <a href="{{ route('admin.branches.index') }}" class="flex items-center p-3 rounded-md hover:bg-gray-100 transition-colors">
                            <i class="fas fa-building text-gray-600 mr-3"></i>
                            <span class="text-gray-800">Monitoring</span>
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

    <!-- Main Content -->
    <main class="container-mobile py-4 pb-20">
        @yield('content')
    </main>

    <!-- Mobile Bottom Navigation -->
    @include('admin.components.mobile-nav')

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/mobile-utils.js') }}"></script>
    
    <script>
        // Mobile sidebar toggle
        document.getElementById('mobileMenuToggle').addEventListener('click', function() {
            const overlay = document.getElementById('mobileSidebarOverlay');
            const sidebar = document.getElementById('mobileSidebar');
            
            overlay.classList.remove('hidden');
            setTimeout(() => {
                sidebar.classList.remove('-translate-x-full');
            }, 10);
        });

        document.getElementById('closeMobileSidebar').addEventListener('click', closeSidebar);
        document.getElementById('mobileSidebarOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSidebar();
            }
        });

        function closeSidebar() {
            const overlay = document.getElementById('mobileSidebarOverlay');
            const sidebar = document.getElementById('mobileSidebar');
            
            sidebar.classList.add('-translate-x-full');
            setTimeout(() => {
                overlay.classList.add('hidden');
            }, 300);
        }

        // Hide loading screen
        window.addEventListener('load', function() {
            const loadingScreen = document.getElementById('loadingScreen');
            loadingScreen.classList.add('hidden');
        });

        // Fix viewport height
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
