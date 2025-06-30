@props(['active' => ''])

<nav class="mobile-nav fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50">
    <div class="flex justify-around items-center py-2">
        <a href="{{ route('admin.dashboard') }}" 
           class="mobile-nav-item {{ $active === 'dashboard' ? 'active' : '' }}">
            <div class="flex flex-col items-center">
                <i class="fas fa-home text-lg mb-1"></i>
                <span class="text-xs">Dashboard</span>
            </div>
        </a>
        
        <a href="{{ route('admin.branches.index') }}" 
           class="mobile-nav-item {{ $active === 'branches' ? 'active' : '' }}">
            <div class="flex flex-col items-center">
                <i class="fas fa-building text-lg mb-1"></i>
                <span class="text-xs">Monitoring</span>
            </div>
        </a>
        
          <a href="{{ route('admin.users.index') }}" 
           class="mobile-nav-item {{ $active === 'users' ? 'active' : '' }}">
            <div class="flex flex-col items-center">
                <i class="fas fa-users text-lg mb-1"></i>
                <span class="text-xs">Pengguna</span>
            </div>
        </a>

       
        
        <a href="{{ route('admin.fish-batches.index') }}" 
           class="mobile-nav-item {{ $active === 'batches' ? 'active' : '' }}">
            <div class="flex flex-col items-center">
                <i class="fas fa-shopping-cart text-lg mb-1"></i>
                <span class="text-xs">Penjualan</span>
            </div>
        </a>
        
         <a href="{{ route('admin.ponds.index') }}" 
           class="mobile-nav-item {{ $active === 'ponds' ? 'active' : '' }}">
            <div class="flex flex-col items-center">
                <i class="fas fa-cog text-lg mb-1"></i>
                <span class="text-xs">Pengaturan Admin</span>
            </div>
        </a>
    </div>
</nav>

<style>
    .mobile-nav {
        display: none;
    }
    
    @media (max-width: 768px) {
        .mobile-nav {
            display: block;
            padding-bottom: env(safe-area-inset-bottom, 0);
        }
        
        .mobile-nav-item {
            color: #6b7280;
            transition: all 0.2s ease;
            padding: 8px 12px;
            border-radius: 8px;
            min-width: 60px;
            text-decoration: none;
        }
        
        .mobile-nav-item:hover,
        .mobile-nav-item.active {
            color: #3b82f6;
            background-color: rgba(59, 130, 246, 0.1);
        }
        
        .mobile-nav-item:active {
            transform: scale(0.95);
        }
        
        .mobile-nav-item.active i {
            color: #3b82f6;
        }
        
        .mobile-nav-item.active span {
            color: #3b82f6;
            font-weight: 600;
        }
        
        /* Add bottom padding to body to account for mobile nav */
        body {
            padding-bottom: 70px;
        }
        
        /* Haptic feedback effect */
        .mobile-nav-item:active {
            animation: hapticLight 0.1s ease-out;
        }
        
        @keyframes hapticLight {
            0% { transform: scale(1); }
            50% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }
    }
    
    /* Hide mobile nav on desktop */
    @media (min-width: 769px) {
        .mobile-nav {
            display: none !important;
        }
        
        body {
            padding-bottom: 0 !important;
        }
    }
</style>

<script>
    // Add haptic feedback for mobile nav
    document.addEventListener('DOMContentLoaded', function() {
        const navItems = document.querySelectorAll('.mobile-nav-item');
        
        navItems.forEach(item => {
            item.addEventListener('touchstart', function() {
                // Vibrate if supported
                if (navigator.vibrate) {
                    navigator.vibrate(10);
                }
                
                // Visual feedback
                this.style.transform = 'scale(0.95)';
                this.style.opacity = '0.7';
            });
            
            item.addEventListener('touchend', function() {
                // Reset visual feedback
                setTimeout(() => {
                    this.style.transform = 'scale(1)';
                    this.style.opacity = '1';
                }, 100);
            });
            
            item.addEventListener('touchcancel', function() {
                // Reset on touch cancel
                this.style.transform = 'scale(1)';
                this.style.opacity = '1';
            });
        });
    });
</script>
