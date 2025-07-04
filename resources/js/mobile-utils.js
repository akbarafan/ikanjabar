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
        window.addEventListener(
            "resize",
            this.debounce(() => {
                this.handleResize();
            }, 250)
        );

        // Orientation change
        window.addEventListener("orientationchange", () => {
            setTimeout(() => {
                this.handleOrientationChange();
            }, 100);
        });

        // Touch events for better mobile interaction
        document.addEventListener(
            "touchstart",
            this.handleTouchStart.bind(this),
            { passive: true }
        );
        document.addEventListener(
            "touchmove",
            this.handleTouchMove.bind(this),
            { passive: false }
        );
        document.addEventListener("touchend", this.handleTouchEnd.bind(this), {
            passive: true,
        });

        // Prevent zoom on double tap for specific elements
        document.addEventListener(
            "touchend",
            this.preventDoubleTabZoom.bind(this)
        );

        // Handle back button on mobile
        window.addEventListener("popstate", this.handleBackButton.bind(this));

        // Handle app state changes (background/foreground)
        document.addEventListener(
            "visibilitychange",
            this.handleVisibilityChange.bind(this)
        );

        // Handle network status changes
        window.addEventListener("online", this.handleOnline.bind(this));
        window.addEventListener("offline", this.handleOffline.bind(this));
    }

    optimizeForMobile() {
        if (!this.isMobile) return;

        // Add mobile-specific classes
        document.body.classList.add("mobile-optimized");

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

        document.addEventListener("touchstart", (e) => {
            this.touchStartX = e.touches[0].clientX;
            this.touchStartY = e.touches[0].clientY;
            this.touchStartTime = Date.now();
        });

        document.addEventListener("touchend", (e) => {
            if (!this.touchStartX || !this.touchStartY) return;

            const touchEndX = e.changedTouches[0].clientX;
            const touchEndY = e.changedTouches[0].clientY;
            const touchEndTime = Date.now();

            const deltaX = touchEndX - this.touchStartX;
            const deltaY = touchEndY - this.touchStartY;
            const deltaTime = touchEndTime - this.touchStartTime;

            if (deltaTime > swipeTimeout) return;

            if (
                Math.abs(deltaX) > Math.abs(deltaY) &&
                Math.abs(deltaX) > swipeThreshold
            ) {
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

        window.addEventListener("resize", () => {
            const currentViewportHeight = window.innerHeight;
            const heightDifference =
                initialViewportHeight - currentViewportHeight;

            if (heightDifference > 150) {
                // Virtual keyboard is likely open
                document.body.classList.add("keyboard-open");
                this.handleKeyboardOpen();
            } else {
                // Virtual keyboard is likely closed
                document.body.classList.remove("keyboard-open");
                this.handleKeyboardClose();
            }
        });

        // Handle input focus for better UX
        document.addEventListener("focusin", (e) => {
            if (e.target.matches("input, textarea, select")) {
                setTimeout(() => {
                    e.target.scrollIntoView({
                        behavior: "smooth",
                        block: "center",
                    });
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

        document.addEventListener("touchstart", (e) => {
            if (window.scrollY === 0) {
                startY = e.touches[0].clientY;
                isPulling = true;
            }
        });

        document.addEventListener("touchmove", (e) => {
            if (!isPulling) return;

            currentY = e.touches[0].clientY;
            pullDistance = currentY - startY;

            if (pullDistance > 0 && window.scrollY === 0) {
                e.preventDefault();

                const opacity = Math.min(pullDistance / threshold, 1);
                const scale = Math.min(
                    0.5 + (pullDistance / threshold) * 0.5,
                    1
                );

                refreshIndicator.style.opacity = opacity;
                refreshIndicator.style.transform = `translateY(${
                    pullDistance * 0.5
                }px) scale(${scale})`;

                if (pullDistance > threshold) {
                    refreshIndicator.classList.add("ready");
                } else {
                    refreshIndicator.classList.remove("ready");
                }
            }
        });

        document.addEventListener("touchend", () => {
            if (!isPulling) return;

            if (pullDistance > threshold) {
                this.handlePullToRefresh();
            }

            // Reset
            refreshIndicator.style.opacity = "0";
            refreshIndicator.style.transform = "translateY(0) scale(0.5)";
            refreshIndicator.classList.remove("ready");

            isPulling = false;
            pullDistance = 0;
        });
    }

    createRefreshIndicator() {
        const indicator = document.createElement("div");
        indicator.className = "pull-refresh-indicator";
        indicator.innerHTML =
            '<div class="refresh-spinner"><i class="fas fa-sync-alt"></i></div><span class="refresh-text">Pull to refresh</span>';

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

    // Sisanya dari method-method lain tetap sama...
    // [Saya potong untuk menghemat space, tapi struktur sama]

    showNotification(message, type = "info", duration = 3000) {
        const notification = document.createElement("div");
        notification.className = `notification-mobile ${type} show`;

        const iconClass = this.getNotificationIcon(type);
        notification.innerHTML = `
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fas fa-${iconClass} mr-2"></i>
                    <span>${message}</span>
                </div>
                <button class="ml-4 text-gray-400 hover:text-gray-600" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        document.body.appendChild(notification);

        setTimeout(() => {
            if (notification.parentElement) {
                notification.classList.remove("show");
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }
        }, duration);
    }

    getNotificationIcon(type) {
        const icons = {
            success: "check-circle",
            error: "exclamation-circle",
            warning: "exclamation-triangle",
            info: "info-circle",
        };
        return icons[type] || icons.info;
    }

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
    }s
}

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
    window.mobileDashboard = new MobileDashboard();
});
