<nav class="gradient-bg shadow-xl">
    <div class="px-6">
        <div class="flex items-center justify-between h-16">
            <div class="flex items-center">
                <h2 class="text-white text-lg font-semibold">@yield('page-title', 'Admin Dashboard')</h2>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <div class="relative">
                    <button class="bg-white bg-opacity-20 p-2 rounded-full text-white hover:bg-opacity-30 transition-all">
                        <i class="fas fa-bell"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">5</span>
                    </button>
                </div>

                <!-- User Profile -->
                <div class="flex items-center">
                    <img class="h-8 w-8 rounded-full border-2 border-white" src="https://ui-avatars.com/api/?name=Admin&background=1e3a8a&color=fff" alt="Profile">
                    <div class="ml-2 text-white">
                        <p class="text-sm font-medium">Administrator</p>
                        <p class="text-xs opacity-75">Super Admin</p>
                    </div>
                </div>

                <!-- Settings -->
                <button class="bg-white bg-opacity-20 p-2 rounded-full text-white hover:bg-opacity-30 transition-all">
                    <i class="fas fa-cog"></i>
                </button>

                <!-- Logout -->
                <form method="POST" action="#" class="inline">
                    @csrf
                    <button type="submit" class="bg-white bg-opacity-20 p-2 rounded-full text-white hover:bg-opacity-30 transition-all">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
