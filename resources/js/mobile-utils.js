// Mobile Dashboard Utilities

class MobileDashboard {
    constructor() {
        this.isMobile = window.innerWidth < 768;
        this.isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
        this.touchStartX = 0;
        this.touchStartY = 0;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.optimizeForMobile();
        this.setupSwipeGestures();
        this.setupVirtualKeyboard();
        this.setupPullToRefresh();
    }

    setupEventListeners() {
        // Resize handler
        window.addEventListener('resize', this.debounce(() => {
            this.handleResize();
        }, 250));

        // Orientation change
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                this.handleOrientationChange();
            }, 100);
        });

        // Touch events for better mobile interaction
        document.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: true });
        document.addEventListener('touchmove', this.handleTouchMove.bind(this), { passive: false });
        document.addEventListener('touchend', this.handleTouchEnd.bind(this), { passive: true });

        // Prevent zoom on double tap for specific elements
        document.addEventListener('touchend', this.preventDoubleTabZoom.bind(this));
                // Handle back button on mobile
                window.addEventListener('popstate', this.handleBackButton.bind(this));

                // Handle app state changes (background/foreground)
                document.addEventListener('visibilitychange', this.handleVisibilityChange.bind(this));
        
                // Handle network status changes
                window.addEventListener('online', this.handleOnline.bind(this));
                window.addEventListener('offline', this.handleOffline.bind(this));
            }
        
            optimizeForMobile() {
                if (!this.isMobile) return;
        
                // Add mobile-specific classes
                document.body.classList.add('mobile-optimized');
        
                // Optimize images for mobile
                this.optimizeImages();
        
                // Setup lazy loading
                this.setupLazyLoading();
        
                // Optimize animations
                this.optimizeAnimations();
        
                // Setup touch feedback
                this.setupTouchFeedback();
            }
        
            setupSwipeGestures() {
                const swipeThreshold = 50;
                const swipeTimeout = 300;
        
                document.addEventListener('touchstart', (e) => {
                    this.touchStartX = e.touches[0].clientX;
                    this.touchStartY = e.touches[0].clientY;
                    this.touchStartTime = Date.now();
                });
        
                document.addEventListener('touchend', (e) => {
                    if (!this.touchStartX || !this.touchStartY) return;
        
                    const touchEndX = e.changedTouches[0].clientX;
                    const touchEndY = e.changedTouches[0].clientY;
                    const touchEndTime = Date.now();
        
                    const deltaX = touchEndX - this.touchStartX;
                    const deltaY = touchEndY - this.touchStartY;
                    const deltaTime = touchEndTime - this.touchStartTime;
        
                    if (deltaTime > swipeTimeout) return;
        
                    if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > swipeThreshold) {
                        if (deltaX > 0) {
                            this.handleSwipeRight(e);
                        } else {
                            this.handleSwipeLeft(e);
                        }
                    } else if (Math.abs(deltaY) > swipeThreshold) {
                        if (deltaY > 0) {
                            this.handleSwipeDown(e);
                        } else {
                            this.handleSwipeUp(e);
                        }
                    }
        
                    this.touchStartX = 0;
                    this.touchStartY = 0;
                });
            }
        
            setupVirtualKeyboard() {
                if (!this.isMobile) return;
        
                let initialViewportHeight = window.innerHeight;
        
                window.addEventListener('resize', () => {
                    const currentViewportHeight = window.innerHeight;
                    const heightDifference = initialViewportHeight - currentViewportHeight;
        
                    if (heightDifference > 150) {
                        // Virtual keyboard is likely open
                        document.body.classList.add('keyboard-open');
                        this.handleKeyboardOpen();
                    } else {
                        // Virtual keyboard is likely closed
                        document.body.classList.remove('keyboard-open');
                        this.handleKeyboardClose();
                    }
                });
        
                // Handle input focus for better UX
                document.addEventListener('focusin', (e) => {
                    if (e.target.matches('input, textarea, select')) {
                        setTimeout(() => {
                            e.target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }, 300);
                    }
                });
            }
        
            setupPullToRefresh() {
                if (!this.isMobile) return;
        
                let startY = 0;
                let currentY = 0;
                let pullDistance = 0;
                const threshold = 100;
                let isPulling = false;
        
                const refreshIndicator = this.createRefreshIndicator();
        
                document.addEventListener('touchstart', (e) => {
                    if (window.scrollY === 0) {
                        startY = e.touches[0].clientY;
                        isPulling = true;
                    }
                });
        
                document.addEventListener('touchmove', (e) => {
                    if (!isPulling) return;
        
                    currentY = e.touches[0].clientY;
                    pullDistance = currentY - startY;
        
                    if (pullDistance > 0 && window.scrollY === 0) {
                        e.preventDefault();
                        
                        const opacity = Math.min(pullDistance / threshold, 1);
                        const scale = Math.min(0.5 + (pullDistance / threshold) * 0.5, 1);
                        
                        refreshIndicator.style.opacity = opacity;
                        refreshIndicator.style.transform = `translateY(${pullDistance * 0.5}px) scale(${scale})`;
                        
                        if (pullDistance > threshold) {
                            refreshIndicator.classList.add('ready');
                        } else {
                            refreshIndicator.classList.remove('ready');
                        }
                    }
                });
        
                document.addEventListener('touchend', () => {
                    if (!isPulling) return;
        
                    if (pullDistance > threshold) {
                        this.handlePullToRefresh();
                    }
        
                    // Reset
                    refreshIndicator.style.opacity = '0';
                    refreshIndicator.style.transform = 'translateY(0) scale(0.5)';
                    refreshIndicator.classList.remove('ready');
                    
                    isPulling = false;
                    pullDistance = 0;
                });
            }
        
            createRefreshIndicator() {
                const indicator = document.createElement('div');
                indicator.className = 'pull-refresh-indicator';
                indicator.innerHTML = `
                    <div class="refresh-spinner">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <span class="refresh-text">Pull to refresh</span>
                `;
                
                indicator.style.cssText = `
                    position: fixed;
                    top: 60px;
                    left: 50%;
                    transform: translateX(-50%) translateY(0) scale(0.5);
                    background: white;
                    border-radius: 20px;
                    padding: 10px 20px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    z-index: 1000;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    font-size: 14px;
                    color: #374151;
                `;
        
                document.body.appendChild(indicator);
                return indicator;
            }
        
            optimizeImages() {
                const images = document.querySelectorAll('img');
                images.forEach(img => {
                    // Add loading="lazy" for better performance
                    if (!img.hasAttribute('loading')) {
                        img.setAttribute('loading', 'lazy');
                    }
        
                    // Add error handling
                    img.addEventListener('error', () => {
                        img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjI0IiBoZWlnaHQ9IjI0IiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0xMiA5QzEwLjM0IDkgOSAxMC4zNCA5IDEyUzEwLjM0IDE1IDEyIDE1UzE1IDEzLjY2IDE1IDEyUzEzLjY2IDkgMTIgOVpNMTIgMTNDMTEuNDUgMTMgMTEgMTIuNTUgMTEgMTJTMTEuNDUgMTEgMTIgMTFTMTMgMTEuNDUgMTMgMTJTMTIuNTUgMTMgMTIgMTNaIiBmaWxsPSIjOUM5Q0EzIi8+Cjwvc3ZnPgo=';
                    });
                });
            }
        
            setupLazyLoading() {
                if ('IntersectionObserver' in window) {
                    const lazyElements = document.querySelectorAll('[data-lazy]');
                    const lazyObserver = new IntersectionObserver((entries) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                const element = entry.target;
                                if (element.dataset.lazy) {
                                    element.src = element.dataset.lazy;
                                    element.removeAttribute('data-lazy');
                                    lazyObserver.unobserve(element);
                                }
                            }
                        });
                    });
        
                    lazyElements.forEach(element => {
                        lazyObserver.observe(element);
                    });
                }
            }
        
            optimizeAnimations() {
                if (this.isMobile) {
                    // Reduce animation duration on mobile
                    const style = document.createElement('style');
                    style.textContent = `
                        @media (max-width: 768px) {
                            * {
                                animation-duration: 0.3s !important;
                                transition-duration: 0.2s !important;
                            }
                        }
                    `;
                    document.head.appendChild(style);
                }
        
                // Disable animations if user prefers reduced motion
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    const style = document.createElement('style');
                    style.textContent = `
                        *, *::before, *::after {
                            animation-duration: 0.01ms !important;
                            animation-iteration-count: 1 !important;
                            transition-duration: 0.01ms !important;
                        }
                    `;
                    document.head.appendChild(style);
                }
            }
        
            setupTouchFeedback() {
                const touchElements = document.querySelectorAll('button, a, [role="button"]');
                
                touchElements.forEach(element => {
                    element.addEventListener('touchstart', () => {
                        element.classList.add('touch-active');
                    });
        
                    element.addEventListener('touchend', () => {
                        setTimeout(() => {
                            element.classList.remove('touch-active');
                        }, 150);
                    });
        
                    element.addEventListener('touchcancel', () => {
                        element.classList.remove('touch-active');
                    });
                });
        
                // Add CSS for touch feedback
                const style = document.createElement('style');
                style.textContent = `
                    .touch-active {
                        opacity: 0.7 !important;
                        transform: scale(0.98) !important;
                    }
                `;
                document.head.appendChild(style);
            }
        
            handleTouchStart(e) {
                this.touchStartX = e.touches[0].clientX;
                this.touchStartY = e.touches[0].clientY;
            }
        
            handleTouchMove(e) {
                // Prevent overscroll bounce on iOS
                if (e.target.closest('.prevent-overscroll')) {
                    const element = e.target.closest('.prevent-overscroll');
                    const scrollTop = element.scrollTop;
                    const scrollHeight = element.scrollHeight;
                    const height = element.clientHeight;
                    const deltaY = e.touches[0].clientY - this.touchStartY;
        
                    if ((scrollTop === 0 && deltaY > 0) || 
                        (scrollTop === scrollHeight - height && deltaY < 0)) {
                        e.preventDefault();
                    }
                }
            }
        
            handleTouchEnd(e) {
                // Reset touch coordinates
                this.touchStartX = 0;
                this.touchStartY = 0;
            }
        
            preventDoubleTabZoom(e) {
                const clickTime = Date.now();
                const timeDiff = clickTime - (this.lastClickTime || 0);
                
                if (timeDiff < 300 && timeDiff > 0) {
                    if (e.target.matches('button, a, input, select, textarea, [role="button"]')) {
                        e.preventDefault();
                    }
                }
                
                this.lastClickTime = clickTime;
            }
        
            handleSwipeLeft(e) {
                // Handle swipe left gesture
                const event = new CustomEvent('swipeLeft', { detail: { originalEvent: e } });
                document.dispatchEvent(event);
            }
        
            handleSwipeRight(e) {
                // Handle swipe right gesture
                const event = new CustomEvent('swipeRight', { detail: { originalEvent: e } });
                document.dispatchEvent(event);
            }
        
            handleSwipeUp(e) {
                // Handle swipe up gesture
                const event = new CustomEvent('swipeUp', { detail: { originalEvent: e } });
                document.dispatchEvent(event);
            }
        
            handleSwipeDown(e) {
                // Handle swipe down gesture
                const event = new CustomEvent('swipeDown', { detail: { originalEvent: e } });
                document.dispatchEvent(event);
            }
        
            handleResize() {
                this.isMobile = window.innerWidth < 768;
                this.isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
                
                // Trigger custom resize event
                const event = new CustomEvent('mobileResize', { 
                    detail: { 
                        isMobile: this.isMobile, 
                        isTablet: this.isTablet,
                        width: window.innerWidth,
                        height: window.innerHeight
                    } 
                });
                document.dispatchEvent(event);
            }
        
            handleOrientationChange() {
                // Handle orientation change
                const orientation = window.orientation || 0;
                const isLandscape = Math.abs(orientation) === 90;
                
                document.body.classList.toggle('landscape', isLandscape);
                document.body.classList.toggle('portrait', !isLandscape);
        
                // Trigger custom orientation change event
                const event = new CustomEvent('orientationChange', { 
                    detail: { 
                        orientation: orientation,
                        isLandscape: isLandscape
                    } 
                });
                document.dispatchEvent(event);
        
                // Fix viewport height on mobile browsers
                if (this.isMobile) {
                    this.fixViewportHeight();
                }
            }
        
            handleKeyboardOpen() {
                // Adjust layout when virtual keyboard opens
                const activeElement = document.activeElement;
                if (activeElement && activeElement.matches('input, textarea')) {
                    setTimeout(() => {
                        activeElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 300);
                }
            }
        
            handleKeyboardClose() {
                // Restore layout when virtual keyboard closes
                window.scrollTo(0, 0);
            }
        
            handleBackButton(e) {
                // Handle browser back button on mobile
                const event = new CustomEvent('mobileBack', { detail: { originalEvent: e } });
                document.dispatchEvent(event);
            }
        
            handleVisibilityChange() {
                if (document.hidden) {
                    // App went to background
                    this.handleAppBackground();
                } else {
                    // App came to foreground
                    this.handleAppForeground();
                }
            }
        
            handleAppBackground() {
                // Pause animations, timers, etc.
                const event = new CustomEvent('appBackground');
                document.dispatchEvent(event);
                
                // Pause any running animations or timers
                this.pauseAnimations();
                this.pauseTimers();
            }
        
            handleAppForeground() {
                // Resume animations, refresh data, etc.
                const event = new CustomEvent('appForeground');
                document.dispatchEvent(event);
                
                // Resume animations and timers
                this.resumeAnimations();
                this.resumeTimers();
                
                // Optionally refresh data
                this.refreshData();
            }
        
            handleOnline() {
                document.body.classList.remove('offline');
                document.body.classList.add('online');
                
                this.showNotification('Connection restored', 'success');
                
                // Sync any pending data
                this.syncPendingData();
            }
        
            handleOffline() {
                document.body.classList.remove('online');
                document.body.classList.add('offline');
                
                this.showNotification('No internet connection', 'warning');
            }
        
            handlePullToRefresh() {
                const refreshIndicator = document.querySelector('.pull-refresh-indicator');
                if (refreshIndicator) {
                    refreshIndicator.querySelector('.refresh-text').textContent = 'Refreshing...';
                    refreshIndicator.querySelector('.refresh-spinner i').style.animation = 'spin 1s linear infinite';
                }
        
                // Trigger refresh
                this.refreshData().finally(() => {
                    setTimeout(() => {
                        if (refreshIndicator) {
                            refreshIndicator.style.opacity = '0';
                            refreshIndicator.querySelector('.refresh-text').textContent = 'Pull to refresh';
                            refreshIndicator.querySelector('.refresh-spinner i').style.animation = '';
                        }
                    }, 1000);
                });
            }
        
            fixViewportHeight() {
                // Fix viewport height issues on mobile browsers
                const vh = window.innerHeight * 0.01;
                document.documentElement.style.setProperty('--vh', `${vh}px`);
            }
        
            pauseAnimations() {
                const animatedElements = document.querySelectorAll('[class*="animate-"]');
                animatedElements.forEach(element => {
                    element.style.animationPlayState = 'paused';
                });
            }
        
            resumeAnimations() {
                const animatedElements = document.querySelectorAll('[class*="animate-"]');
                animatedElements.forEach(element => {
                    element.style.animationPlayState = 'running';
                });
            }
        
            pauseTimers() {
                // Store reference to active timers for pausing
                if (window.dashboardTimers) {
                    window.dashboardTimers.forEach(timer => {
                        if (timer.pause) timer.pause();
                    });
                }
            }
        
            resumeTimers() {
                // Resume paused timers
                if (window.dashboardTimers) {
                    window.dashboardTimers.forEach(timer => {
                        if (timer.resume) timer.resume();
                    });
                }
            }
        
            async refreshData() {
                try {
                    // Refresh dashboard data
                    const response = await fetch('/admin/dashboard/refresh', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
        
                    if (response.ok) {
                        const data = await response.json();
                        this.updateDashboardData(data);
                        this.showNotification('Data refreshed', 'success');
                    }
                } catch (error) {
                    console.error('Failed to refresh data:', error);
                    this.showNotification('Failed to refresh data', 'error');
                }
            }
        
            updateDashboardData(data) {
                // Update dashboard statistics
                if (data.statistics) {
                    Object.keys(data.statistics).forEach(key => {
                        const element = document.querySelector(`[data-stat="${key}"]`);
                        if (element) {
                            this.animateCounter(element, data.statistics[key]);
                        }
                    });
                }
        
                // Update charts if data provided
                if (data.chartData && window.dashboardCharts) {
                    Object.keys(data.chartData).forEach(chartId => {
                        const chart = window.dashboardCharts[chartId];
                        if (chart) {
                            chart.data = data.chartData[chartId];
                            chart.update();
                        }
                    });
                }
            }
        
            animateCounter(element, target) {
                const start = parseInt(element.textContent.replace(/[^\d]/g, '')) || 0;
                const duration = 1000;
                const startTime = Date.now();
        
                const animate = () => {
                    const elapsed = Date.now() - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const current = Math.floor(start + (target - start) * progress);
                    
                    element.textContent = current.toLocaleString('id-ID');
                    
                    if (progress < 1) {
                        requestAnimationFrame(animate);
                    }
                };
        
                animate();
            }
        
            syncPendingData() {
                // Sync any data that was queued while offline
                const pendingData = this.getPendingData();
                if (pendingData.length > 0) {
                    pendingData.forEach(async (item) => {
                        try {
                            await this.syncDataItem(item);
                            this.removePendingData(item.id);
                        } catch (error) {
                            console.error('Failed to sync data item:', error);
                        }
                    });
                }
            }
        
            getPendingData() {
                const stored = localStorage.getItem('pendingSync');
                return stored ? JSON.parse(stored) : [];
            }
        
            addPendingData(data) {
                const pending = this.getPendingData();
                pending.push({ ...data, id: Date.now(), timestamp: new Date().toISOString() });
                localStorage.setItem('pendingSync', JSON.stringify(pending));
            }
        
            removePendingData(id) {
                const pending = this.getPendingData();
                const filtered = pending.filter(item => item.id !== id);
                localStorage.setItem('pendingSync', JSON.stringify(filtered));
            }
        
            async syncDataItem(item) {
                const response = await fetch(item.url, {
                    method: item.method || 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    },
                    body: JSON.stringify(item.data)
                });
        
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
        
                return response.json();
            }
        
            showNotification(message, type = 'info', duration = 3000) {
                const notification = document.createElement('div');
                notification.className = `notification-mobile ${type} show`;
                notification.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <i class="fas fa-${this.getNotificationIcon(type)} mr-2"></i>
                            <span>${message}</span>
                        </div>
                        <button class="ml-4 text-gray-400 hover:text-gray-600" onclick="this.parentElement.parentElement.remove()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
        
                document.body.appendChild(notification);
        
                // Auto remove after duration
                setTimeout(() => {
                    if (notification.parentElement) {
                        notification.classList.remove('show');
                        setTimeout(() => {
                            notification.remove();
                        }, 300);
                    }
                }, duration);
            }
        
            getNotificationIcon(type) {
                const icons = {
                    success: 'check-circle',
                    error: 'exclamation-circle',
                    warning: 'exclamation-triangle',
                    info: 'info-circle'
                };
                return icons[type] || icons.info;
            }
        
            // Utility methods
            debounce(func, wait) {
                let timeout;
                return function executedFunction(...args) {
                    const later = () => {
                        clearTimeout(timeout);
                        func(...args);
                    };
                    clearTimeout(timeout);
                    timeout = setTimeout(later, wait);
                };
            }
        
            throttle(func, limit) {
                let inThrottle;
                return function() {
                    const args = arguments;
                    const context = this;
                    if (!inThrottle) {
                        func.apply(context, args);
                        inThrottle = true;
                        setTimeout(() => inThrottle = false, limit);
                    }
                };
            }
        
            // Performance monitoring
            measurePerformance(name, fn) {
                const start = performance.now();
                const result = fn();
                const end = performance.now();
                console.log(`${name} took ${end - start} milliseconds`);
                return result;
            }
        
            // Memory management
            cleanup() {
                // Remove event listeners
                window.removeEventListener('resize', this.handleResize);
                window.removeEventListener('orientationchange', this.handleOrientationChange);
                document.removeEventListener('touchstart', this.handleTouchStart);
                document.removeEventListener('touchmove', this.handleTouchMove);
                document.removeEventListener('touchend', this.handleTouchEnd);
                document.removeEventListener('visibilitychange', this.handleVisibilityChange);
                window.removeEventListener('online', this.handleOnline);
                window.removeEventListener('offline', this.handleOffline);
        
                // Clear timers
                if (this.timers) {
                    this.timers.forEach(timer => clearTimeout(timer));
                }
        
                // Remove created elements
                const refreshIndicator = document.querySelector('.pull-refresh-indicator');
                if (refreshIndicator) {
                    refreshIndicator.remove();
                }
            }
        }
        
        // Mobile-specific chart optimizations
        class MobileChartOptimizer {
            constructor() {
                this.isMobile = window.innerWidth < 768;
                this.init();
            }
        
            init() {
                if (this.isMobile) {
                    this.optimizeCharts();
                }
            }
        
            optimizeCharts() {
                // Wait for Chart.js to load
                if (typeof Chart !== 'undefined') {
                    this.setupMobileDefaults();
                } else {
                    // Wait for Chart.js to load
                    const checkChart = setInterval(() => {
                        if (typeof Chart !== 'undefined') {
                            clearInterval(checkChart);
                            this.setupMobileDefaults();
                        }
                    }, 100);
                }
            }
        
            setupMobileDefaults() {
                // Set mobile-friendly defaults for Chart.js
                Chart.defaults.font.size = 10;
                Chart.defaults.elements.point.radius = 3;
                Chart.defaults.elements.point.hoverRadius = 5;
                Chart.defaults.elements.line.borderWidth = 2;
                Chart.defaults.elements.bar.borderWidth = 1;
        
                // Override chart options for mobile
                const originalUpdate = Chart.prototype.update;
                Chart.prototype.update = function(mode) {
                    if (window.innerWidth < 768) {
                        this.options = this.getMobileOptions(this.options);
                    }
                    return originalUpdate.call(this, mode);
                };
            }
        
            getMobileOptions(options) {
                return {
                    ...options,
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        ...options.plugins,
                        legend: {
                            ...options.plugins?.legend,
                            labels: {
                                ...options.plugins?.legend?.labels,
                                font: { size: 10 },
                                padding: 10,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            ...options.plugins?.tooltip,
                            titleFont: { size: 11 },
                            bodyFont: { size: 10 },
                            padding: 8,
                            cornerRadius: 4
                        }
                    },
                    scales: {
                        ...options.scales,
                        x: {
                            ...options.scales?.x,
                            ticks: {
                                ...options.scales?.x?.ticks,
                                font: { size: 9 },
                                maxRotation: 45,
                                minRotation: 0
                            }
                        },
                        y: {
                            ...options.scales?.y,
                            ticks: {
                                ...options.scales?.y?.ticks,
                                font: { size: 9 }
                            }
                        }
                    }
                };
            }
        }
        
        // Initialize mobile utilities when DOM is ready
        document.addEventListener('DOMContentLoaded', () => {
            window.mobileDashboard = new MobileDashboard();
            window.mobileChartOptimizer = new MobileChartOptimizer();
        
            // Add mobile-specific event listeners
            document.addEventListener('swipeLeft', (e) => {
                // Handle swipe left - maybe navigate to next page
                console.log('Swiped left');
            });
        
            document.addEventListener('swipeRight', (e) => {
                // Handle swipe right - maybe navigate to previous page or open sidebar
                console.log('Swiped right');
            });
        
            document.addEventListener('mobileResize', (e) => {
                // Handle mobile resize
                if (e.detail.isMobile) {
                    document.body.classList.add('mobile-layout');
                } else {
                    document.body.classList.remove('mobile-layout');
                }
            });
        
            document.addEventListener('orientationChange', (e) => {
                // Handle orientation change
                if (e.detail.isLandscape) {
                    // Optimize for landscape
                    document.body.classList.add('landscape-optimized');
                } else {
                    document.body.classList.remove('landscape-optimized');
                }
            });
        
            // Add CSS for mobile utilities
            const mobileStyles = document.createElement('style');
            mobileStyles.textContent = `
                .mobile-optimized {
                    -webkit-tap-highlight-color: transparent;
                    -webkit-touch-callout: none;
                    -webkit-user-select: none;
                    user-select: none;
                }
        
                .mobile-optimized input,
                .mobile-optimized textarea,
                .mobile-optimized select {
                    -webkit-user-select: text;
                    user-select: text;
                }
        
                .keyboard-open {
                    height: 100vh;
                    overflow: hidden;
                }
        
                .offline {
                    filter: grayscale(0.5);
                }
        
                .offline::before {
                    content: 'Offline';
                    position: fixed;
                    top: 0;
                    left: 0;
                    right: 0;
                    background: #ef4444;
                    color: white;
                    text-align: center;
                    padding: 4px;
                    font-size: 12px;
                    z-index: 9999;
                }
        
                .pull-refresh-indicator.ready .refresh-spinner {
                    color: #10b981;
                }
        
                .pull-refresh-indicator.ready .refresh-text {
                    color: #10b981;
                }
        
                .landscape-optimized .grid-cols-4 {
                    grid-template-columns: repeat(4, minmax(0, 1fr));
                }
        
                .landscape-optimized .bar-chart-container,
                      .landscape-optimized .doughnut-chart-container {
            height: 200px;
        }

        .mobile-layout .text-2xl {
            font-size: 1.25rem;
        }

        .mobile-layout .text-lg {
            font-size: 1rem;
        }

        .mobile-layout .p-6 {
            padding: 1rem;
        }

        .mobile-layout .gap-6 {
            gap: 1rem;
        }

        /* Custom scrollbar for mobile */
        .mobile-optimized ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        .mobile-optimized ::-webkit-scrollbar-track {
            background: transparent;
        }

        .mobile-optimized ::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 2px;
        }

        .mobile-optimized ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.3);
        }

        /* Fix for iOS safe areas */
        @supports (padding: max(0px)) {
            .mobile-optimized {
                padding-left: max(1rem, env(safe-area-inset-left));
                padding-right: max(1rem, env(safe-area-inset-right));
            }
        }

        /* Prevent text selection on mobile for better UX */
        .mobile-optimized .card-hover,
        .mobile-optimized button,
        .mobile-optimized .btn {
            -webkit-user-select: none;
            user-select: none;
        }

        /* Improve touch targets */
        @media (max-width: 768px) {
            button, a, [role="button"] {
                min-height: 44px;
                min-width: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }
        }

        /* Loading states */
        .mobile-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.6;
        }

        .mobile-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* Haptic feedback simulation */
        .haptic-light {
            animation: hapticLight 0.1s ease-out;
        }

        .haptic-medium {
            animation: hapticMedium 0.15s ease-out;
        }

        .haptic-heavy {
            animation: hapticHeavy 0.2s ease-out;
        }

        @keyframes hapticLight {
            0% { transform: scale(1); }
            50% { transform: scale(0.98); }
            100% { transform: scale(1); }
        }

        @keyframes hapticMedium {
            0% { transform: scale(1); }
            50% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }

        @keyframes hapticHeavy {
            0% { transform: scale(1); }
            25% { transform: scale(0.92); }
            75% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }
    `;
    document.head.appendChild(mobileStyles);
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.mobileDashboard) {
        window.mobileDashboard.cleanup();
    }
});

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = { MobileDashboard, MobileChartOptimizer };
}

         