<nav class="gradient-bg shadow-xl sticky top-0 z-30">
    <div class="px-4 lg:px-6">
        <div class="flex items-center justify-between h-14 sm:h-16">
            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="lg:hidden p-2 rounded-lg text-white hover:bg-white hover:bg-opacity-20 transition-all duration-200">
                <i class="fas fa-bars text-lg"></i>
            </button>

            <!-- Page Title -->
            <div class="flex-1 lg:flex-none lg:ml-0 ml-4">
                <h2 class="text-white text-base sm:text-lg font-semibold truncate">
                    @yield('page-title', 'Dashboard')
                </h2>
            </div>

            <!-- Right Actions -->
            <div class="flex items-center space-x-2 sm:space-x-4">
                <!-- Notifications -->
                <div class="relative">
                    <button class="bg-white bg-opacity-20 p-2 rounded-full text-white hover:bg-opacity-30 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50">
                        <i class="fas fa-bell text-sm sm:text-base"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-4 w-4 sm:h-5 sm:w-5 flex items-center justify-center font-bold">3</span>
                    </button>
                </div>

                <!-- User Profile Dropdown -->
                <div class="relative">
                    <button id="user-menu-button" class="flex items-center space-x-2 bg-white bg-opacity-20 p-2 rounded-lg text-white hover:bg-opacity-30 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50">
                        <img class="h-7 w-7 sm:h-8 sm:w-8 rounded-full border-2 border-white object-cover"
                             src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->full_name) }}&background=667eea&color=fff&size=128"
                             alt="Profile">
                        <div class="text-white hidden sm:block text-left">
                            <p class="text-sm font-medium leading-tight">{{ Auth::user()->full_name }}</p>
                            <p class="text-xs opacity-75 leading-tight">
                                {{ Auth::user()->branch->name ?? 'Cabang Jakarta' }}
                            </p>
                        </div>
                        <i class="fas fa-chevron-down text-xs ml-1"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="user-dropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 z-50 opacity-0 invisible transform scale-95 transition-all duration-200 origin-top-right">
                        <!-- User Info (Mobile) -->
                        <div class="px-4 py-2 border-b border-gray-200 sm:hidden">
                            <p class="text-sm font-medium text-gray-900">{{ Auth::user()->full_name }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->branch->name ?? 'Cabang Jakarta' }}</p>
                        </div>

                        <!-- Role Badge -->
                        <div class="px-4 py-2 border-b border-gray-200">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if(Auth::user()->hasRole('admin')) bg-red-100 text-red-800
                                @elseif(Auth::user()->hasRole('branches')) bg-blue-100 text-blue-800
                                @elseif(Auth::user()->hasRole('student')) bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                <i class="fas fa-user-tag mr-1"></i>
                                @if(Auth::user()->hasRole('admin'))
                                    Administrator
                                @elseif(Auth::user()->hasRole('branches'))
                                    Manager Cabang
                                @elseif(Auth::user()->hasRole('student'))
                                    Student
                                @else
                                    User
                                @endif
                            </span>
                        </div>

                        <!-- Menu Items -->
                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-user mr-3 w-4 text-center"></i>
                            Profile Saya
                        </a>

                        <a href="#" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition-colors">
                            <i class="fas fa-cog mr-3 w-4 text-center"></i>
                            Pengaturan
                        </a>

                        <div class="border-t border-gray-200 my-1"></div>

                        <!-- Logout -->
                        <form method="POST" action="{{ route('logout') }}" class="block">
                            @csrf
                            <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <i class="fas fa-sign-out-alt mr-3 w-4 text-center"></i>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
.gradient-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userMenuButton = document.getElementById('user-menu-button');
    const userDropdown = document.getElementById('user-dropdown');

    if (userMenuButton && userDropdown) {
        userMenuButton.addEventListener('click', function(e) {
            e.stopPropagation();

            const isVisible = !userDropdown.classList.contains('opacity-0');

            if (isVisible) {
                // Hide dropdown
                userDropdown.classList.add('opacity-0', 'invisible', 'scale-95');
                userDropdown.classList.remove('opacity-100', 'visible', 'scale-100');
            } else {
                // Show dropdown
                userDropdown.classList.remove('opacity-0', 'invisible', 'scale-95');
                userDropdown.classList.add('opacity-100', 'visible', 'scale-100');
            }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userMenuButton.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.add('opacity-0', 'invisible', 'scale-95');
                userDropdown.classList.remove('opacity-100', 'visible', 'scale-100');
            }
        });

        // Close dropdown on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                userDropdown.classList.add('opacity-0', 'invisible', 'scale-95');
                userDropdown.classList.remove('opacity-100', 'visible', 'scale-100');
            }
        });
    }
});
</script>
