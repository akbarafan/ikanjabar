<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-40 sm:hidden">
    <div class="grid grid-cols-3 h-16">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}"
           class="flex flex-col items-center justify-center text-xs {{ request()->routeIs('admin.dashboard') ? 'text-blue-600' : 'text-gray-600' }}">
            <i class="fas fa-home text-lg mb-1"></i>
            <span>Dashboard</span>
        </a>

        <!-- Branches -->
        <a href="{{ route('admin.branches.index') }}"
           class="flex flex-col items-center justify-center text-xs {{ request()->routeIs('admin.branches.*') ? 'text-blue-600' : 'text-gray-600' }}">
            <i class="fas fa-building text-lg mb-1"></i>
            <span>Cabang</span>
        </a>

        <!-- Users -->
        <a href="{{ route('admin.users.index') }}"
           class="flex flex-col items-center justify-center text-xs {{ request()->routeIs('admin.users.*') ? 'text-blue-600' : 'text-gray-600' }}">
            <i class="fas fa-users text-lg mb-1"></i>
            <span>Users</span>
        </a>
    </div>
</nav>
