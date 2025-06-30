<nav class="gradient-bg shadow-xl">
    <div class="px-6 lg:px-6">
        <div class="flex items-center justify-between h-16">
            <!-- Title (hidden on mobile, shown on desktop) -->
            <div class="hidden lg:block">
                <h2 class="text-white text-lg font-semibold">@yield('page-title', 'Dashboard')</h2>
            </div>

            <!-- Mobile title -->
            <div class="lg:hidden ml-12">
                <h2 class="text-white text-lg font-semibold">@yield('page-title', 'Dashboard')</h2>
            </div>

            <!-- Right side -->
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <div class="relative">
                    <button class="bg-white bg-opacity-20 p-2 rounded-full text-white hover:bg-opacity-30 transition-all">
                        <i class="fas fa-bell"></i>
                        <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">3</span>
                    </button>
                </div>

                <!-- User Profile -->
                <div class="flex items-center">
                    <img class="h-8 w-8 rounded-full border-2 border-white"
                         src="https://ui-avatars.com/api/?name=User&background=667eea&color=fff" alt="Profile">
                    <div class="ml-2 text-white hidden sm:block">
                        <p class="text-sm font-medium">User Demo</p>
                        <p class="text-xs opacity-75">{{ $branchInfo->name ?? 'Cabang Jakarta' }}</p>
                    </div>
                </div>

                <!-- Settings -->
                <button class="bg-white bg-opacity-20 p-2 rounded-full text-white hover:bg-opacity-30 transition-all">
                    <i class="fas fa-cog"></i>
                </button>
            </div>
        </div>
    </div>
</nav>

<style>
.gradient-bg {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>
