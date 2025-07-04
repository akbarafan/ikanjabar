<nav class="gradient-bg shadow-xl sticky top-0 z-50">
    <div class="px-3 sm:px-6">
        <div class="flex items-center justify-between h-14 sm:h-16">
            <!-- Left Section - Title -->
            <div class="flex items-center min-w-0 flex-1">
                <!-- Mobile Menu Button (if needed) -->
                <button class="sm:hidden mr-2 p-2 rounded-md text-white hover:bg-white hover:bg-opacity-20 transition-all duration-200"
                        onclick="toggleMobileMenu()"
                        aria-label="Toggle menu">
                    <i class="fas fa-bars text-sm"></i>
                </button>

                <h2 class="text-white text-sm sm:text-lg font-semibold truncate">
                    <span class="hidden sm:inline">@yield('page-title', 'Dashboard')</span>
                    <span class="sm:hidden">@yield('page-title-short', 'Dashboard')</span>
                </h2>
            </div>

            <!-- Right Section - Actions -->
            <div class="flex items-center space-x-2 sm:space-x-4 flex-shrink-0">
                <!-- Notifications -->
                <div class="relative">
                    <!-- Notification Dropdown (Hidden by default) -->
                    <div class="absolute right-0 mt-2 w-64 sm:w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50 hidden"
                         id="notificationDropdown">
                        <div class="p-3 border-b border-gray-200">
                            <h3 class="text-sm font-semibold text-gray-800">Notifications</h3>
                        </div>
                        <div class="max-h-64 overflow-y-auto">
                            <div class="p-3 hover:bg-gray-50 border-b border-gray-100 cursor-pointer">
                                <p class="text-xs sm:text-sm text-gray-800 font-medium">New batch created</p>
                                <p class="text-xs text-gray-500 mt-1">2 minutes ago</p>
                            </div>
                            <div class="p-3 hover:bg-gray-50 border-b border-gray-100 cursor-pointer">
                                <p class="text-xs sm:text-sm text-gray-800 font-medium">Water quality alert</p>
                                <p class="text-xs text-gray-500 mt-1">5 minutes ago</p>
                            </div>
                            <div class="p-3 hover:bg-gray-50 cursor-pointer">
                                <p class="text-xs sm:text-sm text-gray-800 font-medium">New user registered</p>
                                <p class="text-xs text-gray-500 mt-1">10 minutes ago</p>
                            </div>
                        </div>
                        <div class="p-3 border-t border-gray-200">
                            <a href="#" class="text-xs sm:text-sm text-blue-600 hover:text-blue-800 font-medium">View all notifications</a>
                        </div>
                    </div>
                </div>

                <!-- User Profile -->
                <div class="flex items-center cursor-pointer hover:bg-white hover:bg-opacity-10 rounded-lg p-1 sm:p-2 transition-all duration-200"
                     onclick="toggleProfileDropdown()">
                    <img class="h-6 w-6 sm:h-8 sm:w-8 rounded-full border-2 border-white flex-shrink-0"
                         src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->full_name) }}&background=1e3a8a&color=fff"
                         alt="Profile"
                         loading="lazy">
                    <div class="ml-2 text-white hidden sm:block min-w-0">
                        <p class="text-sm font-medium truncate">{{ auth()->user()->full_name }}</p>
                        <p class="text-xs opacity-75 truncate">
                            @if(auth()->user()->hasRole('admin'))
                                Super Admin
                            @elseif(auth()->user()->hasRole('branches'))
                                Branch Manager
                            @elseif(auth()->user()->hasRole('student'))
                                Student
                            @else
                                User
                            @endif
                        </p>
                    </div>
                    <i class="fas fa-chevron-down text-white text-xs ml-1 sm:ml-2 hidden sm:inline"></i>
                </div>

                <!-- Profile Dropdown -->
                <div class="absolute right-3 sm:right-6 top-12 sm:top-14 mt-2 w-48 sm:w-56 bg-white rounded-lg shadow-xl border border-gray-200 z-50 hidden"
                     id="profileDropdown">
                    <div class="p-3 border-b border-gray-200">
                        <div class="flex items-center">
                            <img class="h-10 w-10 rounded-full"
                                 src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->full_name) }}&background=1e3a8a&color=fff"
                                 alt="Profile">
                            <div class="ml-3 min-w-0">
                                <p class="text-sm font-medium text-gray-800 truncate">{{ auth()->user()->full_name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="py-2">
                        <a href="#" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-user mr-3 text-gray-400"></i>
                            Profile Settings
                        </a>
                        <a href="#" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-cog mr-3 text-gray-400"></i>
                            Account Settings
                        </a>
                        <a href="#" class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                            <i class="fas fa-bell mr-3 text-gray-400"></i>
                            Notification Settings
                        </a>
                    </div>
                    <div class="border-t border-gray-200 py-2">
                        <form method="POST" action="{{ route('logout') }}" class="block">
                            @csrf
                            <button type="submit" class="flex items-center w-full px-3 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <i class="fas fa-sign-out-alt mr-3 text-red-400"></i>
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Settings Button (Desktop Only) -->
                <button class="hidden sm:block bg-white bg-opacity-20 p-2 rounded-full text-white hover:bg-opacity-30 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                        aria-label="Settings">
                    <i class="fas fa-cog text-base"></i>
                </button>

                <!-- Mobile Settings/Menu Button -->
                <button class="sm:hidden bg-white bg-opacity-20 p-1.5 rounded-full text-white hover:bg-opacity-30 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50"
                        onclick="toggleMobileSettings()"
                        aria-label="Mobile menu">
                    <i class="fas fa-ellipsis-v text-sm"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Settings Dropdown -->
    <div class="sm:hidden bg-white bg-opacity-10 border-t border-white border-opacity-20 hidden" id="mobileSettings">
        <div class="px-3 py-2 space-y-1">
            <a href="#" class="flex items-center px-3 py-2 text-sm text-white hover:bg-white hover:bg-opacity-10 rounded-md transition-colors">
                <i class="fas fa-user mr-3"></i>
                Profile
            </a>
            <a href="#" class="flex items-center px-3 py-2 text-sm text-white hover:bg-white hover:bg-opacity-10 rounded-md transition-colors">
                <i class="fas fa-cog mr-3"></i>
                Settings
            </a>
            <a href="#" class="flex items-center px-3 py-2 text-sm text-white hover:bg-white hover:bg-opacity-10 rounded-md transition-colors">
                <i class="fas fa-bell mr-3"></i>
                Notifications
            </a>
            <form method="POST" action="{{ route('logout') }}" class="block">
                @csrf
                <button type="submit" class="flex items-center w-full px-3 py-2 text-sm text-white hover:bg-white hover:bg-opacity-10 rounded-md transition-colors">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    Sign Out
                </button>
            </form>
        </div>
    </div>

    <!-- Mobile Navigation Overlay (if needed for sidebar toggle) -->
    <div class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" id="mobileOverlay" onclick="closeMobileMenu()"></div>
</nav>

<!-- JavaScript tetap sama seperti sebelumnya -->
<script>
// Toggle notification dropdown
function toggleNotificationDropdown() {
    const dropdown = document.getElementById('notificationDropdown');
    const profileDropdown = document.getElementById('profileDropdown');

    // Close profile dropdown if open
    if (profileDropdown && !profileDropdown.classList.contains('hidden')) {
        profileDropdown.classList.add('hidden');
    }

    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

// Toggle profile dropdown
function toggleProfileDropdown() {
    const dropdown = document.getElementById('profileDropdown');
    const notificationDropdown = document.getElementById('notificationDropdown');

    // Close notification dropdown if open
    if (notificationDropdown && !notificationDropdown.classList.contains('hidden')) {
        notificationDropdown.classList.add('hidden');
    }

    if (dropdown) {
        dropdown.classList.toggle('hidden');
    }
}

// Toggle mobile settings
function toggleMobileSettings() {
    const mobileSettings = document.getElementById('mobileSettings');
    if (mobileSettings) {
        mobileSettings.classList.toggle('hidden');
    }
}

// Toggle mobile menu (for sidebar if exists)
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobileOverlay');

    if (sidebar) {
        sidebar.classList.toggle('-translate-x-full');
        sidebar.classList.toggle('translate-x-0');
    }

    if (overlay) {
        overlay.classList.toggle('hidden');
    }
}

// Close mobile menu
function closeMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('mobileOverlay');

    if (sidebar) {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
    }

    if (overlay) {
        overlay.classList.add('hidden');
    }
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(event) {
    const notificationBtn = event.target.closest('[aria-label="Notifications"]');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const profileArea = event.target.closest('[onclick="toggleProfileDropdown()"]');
    const profileDropdown = document.getElementById('profileDropdown');
    const mobileSettingsBtn = event.target.closest('[onclick="toggleMobileSettings()"]');
    const mobileSettings = document.getElementById('mobileSettings');

    // Close notification dropdown
    if (!notificationBtn && notificationDropdown && !notificationDropdown.classList.contains('hidden')) {
        if (!notificationDropdown.contains(event.target)) {
            notificationDropdown.classList.add('hidden');
        }
    }

    // Close profile dropdown
    if (!profileArea && profileDropdown && !profileDropdown.classList.contains('hidden')) {
        if (!profileDropdown.contains(event.target)) {
            profileDropdown.classList.add('hidden');
        }
    }

    // Close mobile settings
    if (!mobileSettingsBtn && mobileSettings && !mobileSettings.classList.contains('hidden')) {
        if (!mobileSettings.contains(event.target)) {
            mobileSettings.classList.add('hidden');
        }
    }
});

// Handle escape key to close dropdowns
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const notificationDropdown = document.getElementById('notificationDropdown');
        const profileDropdown = document.getElementById('profileDropdown');
        const mobileSettings = document.getElementById('mobileSettings');

        if (notificationDropdown && !notificationDropdown.classList.contains('hidden')) {
            notificationDropdown.classList.add('hidden');
        }

        if (profileDropdown && !profileDropdown.classList.contains('hidden')) {
            profileDropdown.classList.add('hidden');
        }

        if (mobileSettings && !mobileSettings.classList.contains('hidden')) {
            mobileSettings.classList.add('hidden');
        }

        closeMobileMenu();
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    const mobileSettings = document.getElementById('mobileSettings');

    // Hide mobile settings on desktop
    if (window.innerWidth >= 640 && mobileSettings && !mobileSettings.classList.contains('hidden')) {
        mobileSettings.classList.add('hidden');
    }

    // Close mobile menu on desktop
    if (window.innerWidth >= 768) {
        closeMobileMenu();
    }
});
</script>

<!-- CSS tetap sama seperti sebelumnya -->
<style>
    /* CSS styles tetap sama seperti file asli */
    @media (max-width: 640px) {
        .gradient-bg {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 50%, #06b6d4 100%);
        }

        button, a {
            min-height: 44px;
            min-width: 44px;
        }

        #notificationDropdown,
        #profileDropdown {
            position: fixed !important;
            top: 60px !important;
            left: 1rem !important;
            right: 1rem !important;
            width: auto !important;
            max-width: none !important;
        }

        #mobileSettings {
            animation: slideDown 0.2s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    }

    @media (max-width: 480px) {
        .gradient-bg .px-3 {
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .space-x-2 > * + * {
            margin-left: 0.375rem;
        }
    }

    @media (prefers-contrast: high) {
        .gradient-bg {
            background: #1e3a8a !important;
        }

        .bg-white.bg-opacity-20 {
            background-color: rgba(255, 255, 255, 0.3) !important;
        }

        .hover\:bg-opacity-30:hover {
            background-color: rgba(255, 255, 255, 0.4) !important;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .transition-all,
        .transition-colors {
            transition: none !important;
        }

        #mobileSettings {
            animation: none !important;
        }
    }

    button:focus,
    a:focus {
        outline: 2px solid rgba(255, 255, 255, 0.5);
        outline-offset: 2px;
    }

    .loading {
        opacity: 0.6;
        pointer-events: none;
    }

    .loading::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 16px;
        height: 16px;
        margin: -8px 0 0 -8px;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .notification-badge {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.7;
        }
    }

    #notificationDropdown,
    #profileDropdown {
        transform-origin: top right;
        transition: all 0.2s ease-out;
    }

    #notificationDropdown.hidden,
    #profileDropdown.hidden {
        opacity: 0;
        transform: scale(0.95) translateY(-10px);
        pointer-events: none;
    }

    #notificationDropdown:not(.hidden),
    #profileDropdown:not(.hidden) {
        opacity: 1;
        transform: scale(1) translateY(0);
        pointer-events: auto;
    }

    #mobileOverlay {
        backdrop-filter: blur(4px);
        transition: opacity 0.3s ease-out;
    }

    #mobileOverlay.hidden {
        opacity: 0;
        pointer-events: none;
    }

    #mobileOverlay:not(.hidden) {
        opacity: 1;
        pointer-events: auto;
    }
</style>

