<nav class="gradient-bg shadow-xl fixed top-0 left-0 right-0 z-50">
    <div class="max-w-full mx-auto px-6">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center">
                <button id="sidebar-toggle" class="text-white mr-4 lg:hidden">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <i class="fas fa-fish text-white text-2xl mr-3"></i>
                <span class="text-white text-xl font-bold">AquaCulture Monitor</span>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <div class="relative">
                    <button class="bg-white bg-opacity-20 p-2 rounded-full text-white hover:bg-opacity-30 transition-all" id="notification-btn">
                        <i class="fas fa-bell"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                    </button>
                    <!-- Notification Dropdown -->
                    <div id="notification-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl z-50">
                        <div class="p-4 border-b">
                            <h3 class="font-semibold text-gray-900">Notifikasi Terbaru</h3>
                        </div>
                        <div class="max-h-64 overflow-y-auto">
                            <a href="{{ route('alerts.index') }}" class="block p-3 hover:bg-gray-50 border-b">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 bg-red-500 rounded-full mr-3"></div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">pH Tinggi - Cabang Bogor</p>
                                        <p class="text-xs text-gray-500">15 menit lalu</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="p-3 text-center border-t">
                            <a href="{{ route('alerts.index') }}" class="text-blue-600 text-sm hover:underline">Lihat Semua Notifikasi</a>
                        </div>
                    </div>
                </div>

                <!-- User Profile -->
                <div class="relative">
                    <button class="flex items-center space-x-2" id="profile-btn">
                        <img class="h-8 w-8 rounded-full border-2 border-white" src="https://ui-avatars.com/api/?name={{ auth()->user()->name ?? 'Admin' }}&background=667eea&color=fff" alt="Profile">
                        <span class="ml-2 text-white text-sm">{{ auth()->user()->name ?? 'Admin Pusat' }}</span>
                        <i class="fas fa-chevron-down text-white text-xs"></i>
                    </button>
                    <!-- Profile Dropdown -->
                    <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl z-50">
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user mr-2"></i>Profil Saya
                        </a>
                        <a href="{{ route('settings.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-cog mr-2"></i>Pengaturan
                        </a>
                        <hr class="my-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
