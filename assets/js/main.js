// Enhanced Auto Slideshow with smooth transitions and controls
class SmoothSlideshow {
    constructor(containerSelector, options = {}) {
        this.container = document.querySelector(containerSelector);
        if (!this.container) return;
        
        this.slides = this.container.getElementsByClassName("slide");
        this.currentIndex = 0;
        this.isTransitioning = false;
        
        // Configuration options
        this.config = {
            interval: options.interval || 5000,
            transitionDuration: options.transitionDuration || 800,
            autoplay: options.autoplay !== false,
            pauseOnHover: options.pauseOnHover !== false,
            showControls: options.showControls !== false,
            showIndicators: options.showIndicators !== false
        };
        
        this.autoplayTimer = null;
        
        this.init();
    }
    
    init() {
        if (this.slides.length === 0) return;
        
        // Add CSS for smooth transitions
        this.injectStyles();
        
        // Initialize slide positions
        this.initializeSlides();
        
        // Add controls and indicators
        if (this.config.showControls) this.addControls();
        if (this.config.showIndicators) this.addIndicators();
        
        // Setup event listeners
        this.setupEventListeners();
        
        // Start autoplay
        if (this.config.autoplay) this.startAutoplay();
    }
    
    injectStyles() {
        if (document.getElementById('slideshow-styles')) return;
        
        const styles = document.createElement('style');
        styles.id = 'slideshow-styles';
        styles.textContent = `
            .slideshow-container {
                position: relative;
                overflow: hidden;
            }
            
            .slide {
                position: absolute;
                width: 100%;
                height: 100%;
                top: 0;
                opacity: 0;
                transition: all ${this.config.transitionDuration}ms cubic-bezier(0.4, 0, 0.2, 1);
                will-change: transform, opacity;
            }
            
            .slide.active {
                opacity: 1;
                transform: translateX(0);
                z-index: 2;
            }
            
            .slide.next {
                transform: translateX(100%);
                z-index: 1;
            }
            
            .slide.prev {
                transform: translateX(-100%);
                z-index: 1;
            }
            
            /* Navigation Controls */
            .slideshow-nav {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                background: rgba(0, 0, 0, 0.5);
                color: white;
                border: none;
                padding: 16px 12px;
                cursor: pointer;
                font-size: 24px;
                z-index: 10;
                transition: background 0.3s ease;
                user-select: none;
            }
            
            .slideshow-nav:hover {
                background: rgba(0, 0, 0, 0.8);
            }
            
            .slideshow-nav.prev-btn {
                left: 10px;
                border-radius: 0 4px 4px 0;
            }
            
            .slideshow-nav.next-btn {
                right: 10px;
                border-radius: 4px 0 0 4px;
            }
            
            /* Indicators */
            .slideshow-indicators {
                position: absolute;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
                display: flex;
                gap: 10px;
                z-index: 10;
            }
            
            .slideshow-indicator {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.5);
                border: 2px solid rgba(255, 255, 255, 0.8);
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .slideshow-indicator:hover {
                background: rgba(255, 255, 255, 0.8);
                transform: scale(1.2);
            }
            
            .slideshow-indicator.active {
                background: rgba(255, 255, 255, 1);
                transform: scale(1.3);
            }
        `;
        document.head.appendChild(styles);
    }
    
    initializeSlides() {
        Array.from(this.slides).forEach((slide, index) => {
            slide.classList.remove('active', 'next', 'prev');
            if (index === this.currentIndex) {
                slide.classList.add('active');
            } else {
                slide.classList.add('next');
            }
        });
    }
    
    addControls() {
        const prevBtn = document.createElement('button');
        prevBtn.className = 'slideshow-nav prev-btn';
        prevBtn.innerHTML = '&#10094;';
        prevBtn.setAttribute('aria-label', 'Previous slide');
        prevBtn.onclick = () => this.goToPrevious();
        
        const nextBtn = document.createElement('button');
        nextBtn.className = 'slideshow-nav next-btn';
        nextBtn.innerHTML = '&#10095;';
        nextBtn.setAttribute('aria-label', 'Next slide');
        nextBtn.onclick = () => this.goToNext();
        
        this.container.appendChild(prevBtn);
        this.container.appendChild(nextBtn);
    }
    
    addIndicators() {
        const indicatorsContainer = document.createElement('div');
        indicatorsContainer.className = 'slideshow-indicators';
        
        for (let i = 0; i < this.slides.length; i++) {
            const indicator = document.createElement('span');
            indicator.className = 'slideshow-indicator';
            if (i === this.currentIndex) indicator.classList.add('active');
            indicator.setAttribute('aria-label', `Go to slide ${i + 1}`);
            indicator.onclick = () => this.goToSlide(i);
            indicatorsContainer.appendChild(indicator);
        }
        
        this.container.appendChild(indicatorsContainer);
        this.indicators = indicatorsContainer.children;
    }
    
    setupEventListeners() {
        if (this.config.pauseOnHover) {
            this.container.addEventListener('mouseenter', () => this.pauseAutoplay());
            this.container.addEventListener('mouseleave', () => this.resumeAutoplay());
        }
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') this.goToPrevious();
            if (e.key === 'ArrowRight') this.goToNext();
        });
        
        // Touch swipe support
        let touchStartX = 0;
        let touchEndX = 0;
        
        this.container.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        });
        
        this.container.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            if (touchStartX - touchEndX > 50) this.goToNext();
            if (touchEndX - touchStartX > 50) this.goToPrevious();
        });
    }
    
    goToSlide(index) {
        if (this.isTransitioning || index === this.currentIndex) return;
        
        this.isTransitioning = true;
        const previousIndex = this.currentIndex;
        const direction = index > previousIndex ? 'next' : 'prev';
        
        this.currentIndex = index;
        this.transition(previousIndex, this.currentIndex, direction);
        this.updateIndicators();
        
        if (this.config.autoplay) this.resetAutoplay();
    }
    
    goToNext() {
        const nextIndex = (this.currentIndex + 1) % this.slides.length;
        this.goToSlide(nextIndex);
    }
    
    goToPrevious() {
        const prevIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length;
        this.goToSlide(prevIndex);
    }
    
    transition(fromIndex, toIndex, direction) {
        const fromSlide = this.slides[fromIndex];
        const toSlide = this.slides[toIndex];
        
        // Prepare incoming slide
        toSlide.classList.remove('prev', 'next');
        toSlide.classList.add(direction);
        
        // Force reflow
        toSlide.offsetHeight;
        
        // Animate
        fromSlide.classList.remove('active');
        fromSlide.classList.add(direction === 'next' ? 'prev' : 'next');
        
        toSlide.classList.remove(direction);
        toSlide.classList.add('active');
        
        setTimeout(() => {
            this.isTransitioning = false;
        }, this.config.transitionDuration);
    }
    
    updateIndicators() {
        if (!this.indicators) return;
        
        Array.from(this.indicators).forEach((indicator, index) => {
            indicator.classList.toggle('active', index === this.currentIndex);
        });
    }
    
    startAutoplay() {
        this.autoplayTimer = setInterval(() => this.goToNext(), this.config.interval);
    }
    
    pauseAutoplay() {
        if (this.autoplayTimer) {
            clearInterval(this.autoplayTimer);
            this.autoplayTimer = null;
        }
    }
    
    resumeAutoplay() {
        if (this.config.autoplay && !this.autoplayTimer) {
            this.startAutoplay();
        }
    }
    
    resetAutoplay() {
        this.pauseAutoplay();
        this.resumeAutoplay();
    }
    
    // Public API methods
    destroy() {
        this.pauseAutoplay();
        // Remove event listeners and controls if needed
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize with default options
    const slideshow = new SmoothSlideshow('.slideshow-container', {
        interval: 5000,              // Time between slides (ms)
        transitionDuration: 800,      // Transition animation duration (ms)
        autoplay: true,               // Auto advance slides
        pauseOnHover: true,           // Pause when mouse over
        showControls: true,           // Show prev/next buttons
        showIndicators: false         // Show dot indicators
    });
    
    // Make it globally accessible if needed
    window.slideshow = slideshow;
});