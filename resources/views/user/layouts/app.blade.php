<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AquaCulture Dashboard') - Sistem Monitoring Perikanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

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
        .sidebar-active {
            @apply bg-blue-50 text-blue-700 border-r-4 border-blue-500;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50">
        <!-- Sidebar -->
        @include('user.partials.sidebar')

        <!-- Main Content -->
        <div class="lg:ml-64 min-h-screen flex flex-col">
            <!-- Navbar -->
            @include('user.partials.navbar')

            <!-- Page Content -->
            <main class="p-6">
                @yield('content')
            </main>

            <!-- Footer -->
            @include('user.partials.footer')
        </div>

    @stack('scripts')
</body>
</html>
