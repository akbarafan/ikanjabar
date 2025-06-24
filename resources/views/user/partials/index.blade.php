<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AquaCulture Dashboard') - Sistem Monitoring Perikanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .sidebar-active { @apply bg-blue-600 text-white; }
        .sidebar-item { @apply text-gray-300 hover:bg-blue-600 hover:text-white; }
    </style>
    @stack('styles')
</head>
<body class="bg-gradient-to-br from-blue-50 via-cyan-50 to-teal-50 min-h-screen">
    <!-- Navigation -->
    @include('partials.navbar.index')

    <!-- Sidebar -->
    @include('partials.sidebar.index')

    <!-- Main Content -->
    <div class="ml-64 transition-all duration-300" id="main-content">
        <div class="max-w-7xl mx-auto py-6 px-6">
            @yield('content')
        </div>
    </div>

    <!-- Footer -->
    @include('partials.footer.index')

    @stack('scripts')
</body>
</html>
