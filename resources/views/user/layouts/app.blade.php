<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#667eea">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">

    <title>@yield('title', 'AquaCulture Dashboard') - Sistem Monitoring Perikanan</title>

    <!-- Preload critical resources -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">

    <!-- Font Awesome - Load synchronously to prevent icon flash -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Tailwind Config -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    screens: { 'xs': '475px' },
                    animation: {
                        'float': 'float 3s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>

    <style>
        /* Prevent FOUC (Flash of Unstyled Content) */
        .fa, .fas, .far, .fal, .fab {
            font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands" !important;
        }

        /* Custom Styles */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
        }

        /* Status Classes */
        .status-healthy { @apply bg-green-100 text-green-800 border-green-200; }
        .status-warning { @apply bg-yellow-100 text-yellow-800 border-yellow-200; }
        .status-danger { @apply bg-red-100 text-red-800 border-red-200; }

        /* Chart Container */
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        /* Custom Scrollbar */
        .scrollbar-thin {
            scrollbar-width: thin;
        }
        .scrollbar-thin::-webkit-scrollbar { width: 4px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: #f1f5f9; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 2px; }
        .scrollbar-thin::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Mobile Optimizations */
        @media (max-width: 768px) {
            .chart-container { height: 250px; }
            button, .nav-item, a { min-height: 44px; }
            .space-y-mobile > * + * { margin-top: 1rem; }
        }

        /* Loading States */
        .loading { opacity: 0.6; pointer-events: none; }

        /* Focus States */
        .focus-ring:focus {
            outline: 2px solid #667eea;
            outline-offset: 2px;
        }

        /* Smooth transitions */
        * {
            transition: color 150ms ease, background-color 150ms ease, border-color 150ms ease,
                       opacity 150ms ease, box-shadow 150ms ease, transform 150ms ease;
        }

        /* Print Styles */
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
        }
    </style>

    @stack('styles')
</head>
<body class="bg-gray-50 antialiased">
    <!-- Loading Indicator -->
    <div id="loading-indicator" class="fixed top-0 left-0 w-full h-1 bg-blue-600 transform -translate-x-full transition-transform duration-300 z-50"></div>

    <!-- Skip to main content -->
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 bg-blue-600 text-white px-4 py-2 rounded-md z-50">
        Skip to main content
    </a>

    <!-- Sidebar -->
    @include('user.partials.sidebar')

    <!-- Main Content Wrapper -->
    <div class="lg:ml-64 min-h-screen flex flex-col">
        <!-- Navbar -->
        @include('user.partials.navbar')

        <!-- Page Content -->
        <main id="main-content" class="flex-1 p-4 sm:p-6 space-y-mobile">
            <!-- Breadcrumb -->
            @if(isset($breadcrumbs))
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    @foreach($breadcrumbs as $breadcrumb)
                    <li class="inline-flex items-center">
                        @if(!$loop->last)
                        <a href="{{ $breadcrumb['url'] }}" class="text-gray-700 hover:text-blue-600 text-sm font-medium">
                            {{ $breadcrumb['label'] }}
                        </a>
                        <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                        @else
                        <span class="text-gray-500 text-sm font-medium">{{ $breadcrumb['label'] }}</span>
                        @endif
                    </li>
                    @endforeach
                </ol>
            </nav>
            @endif

            <!-- Flash Messages -->
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="ml-auto text-green-700 hover:text-green-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4 flex items-center">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="ml-auto text-red-700 hover:text-red-900">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            @endif

            <!-- Main Content -->
            @yield('content')
        </main>

        <!-- Footer -->
        @include('user.partials.footer')
    </div>

    <!-- Back to Top Button -->
    <button id="back-to-top" class="fixed bottom-4 right-4 bg-blue-600 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 transition-all duration-300 opacity-0 invisible focus-ring">
        <i class="fas fa-chevron-up"></i>
    </button>

    <!-- jQuery (only if needed) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Chart.js (only load when needed) -->
    @stack('chart-scripts')

    <!-- Global Scripts -->
    <script>
        // Global utilities
        window.AppUtils = {
            showLoading() {
                document.getElementById('loading-indicator')?.classList.remove('-translate-x-full');
            },
            hideLoading() {
                document.getElementById('loading-indicator')?.classList.add('-translate-x-full');
            },
            showToast(message, type = 'success') {
                const colors = {
                    success: 'bg-green-500',
                    error: 'bg-red-500',
                    info: 'bg-blue-500'
                };
                const icons = {
                    success: 'check',
                    error: 'times',
                    info: 'info'
                };

                const toast = document.createElement('div');
                toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 transition-all duration-300 transform translate-x-full ${colors[type]} text-white`;
                toast.innerHTML = `
                    <div class="flex items-center">
                        <i class="fas fa-${icons[type]}-circle mr-2"></i>
                        <span>${message}</span>
                    </div>
                `;

                document.body.appendChild(toast);
                setTimeout(() => toast.classList.remove('translate-x-full'), 100);
                setTimeout(() => {
                    toast.classList.add('translate-x-full');
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            },
            formatCurrency(amount) {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR'
                }).format(amount);
            },
            formatDate(date) {
                return new Intl.DateTimeFormat('id-ID').format(new Date(date));
            }
        };

        // DOM Ready
        document.addEventListener('DOMContentLoaded', function() {
            // Back to top functionality
            const backToTopBtn = document.getElementById('back-to-top');
            if (backToTopBtn) {
                window.addEventListener('scroll', function() {
                    if (window.pageYOffset > 300) {
                        backToTopBtn.classList.remove('opacity-0', 'invisible');
                    } else {
                        backToTopBtn.classList.add('opacity-0', 'invisible');
                    }
                });

                backToTopBtn.addEventListener('click', function() {
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                });
            }

            // Auto-hide flash messages
            setTimeout(() => {
                const flashMessages = document.querySelectorAll('[class*="bg-green-100"], [class*="bg-red-100"]');
                flashMessages.forEach(msg => {
                    if (msg.querySelector('button')) return;
                    msg.style.transition = 'opacity 0.5s ease-out';
                    msg.style.opacity = '0';
                    setTimeout(() => msg.remove(), 500);
                });
            }, 5000);
        });

        // Global AJAX setup (only if jQuery is loaded)
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                beforeSend: () => window.AppUtils.showLoading(),
                complete: () => window.AppUtils.hideLoading(),
                error: function(xhr) {
                    let message = 'Terjadi kesalahan. Silakan coba lagi.';
                    if (xhr.responseJSON?.message) message = xhr.responseJSON.message;
                    else if (xhr.status === 404) message = 'Data tidak ditemukan.';
                    else if (xhr.status === 403) message = 'Anda tidak memiliki akses.';
                    else if (xhr.status === 500) message = 'Kesalahan server.';
                    window.AppUtils.showToast(message, 'error');
                }
            });
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.querySelector('input[type="search"]')?.focus();
            }
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal:not(.hidden)').forEach(modal => {
                    modal.classList.add('hidden');
                });
            }
        });

        // Online/offline status
        window.addEventListener('online', () => window.AppUtils.showToast('Koneksi tersambung kembali', 'success'));
        window.addEventListener('offline', () => window.AppUtils.showToast('Koneksi terputus', 'error'));
    </script>

    @stack('scripts')
</body>
</html>
