// Auto Slideshow functionality
let slideIndex = 0;

// Initialize slideshow when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelector('.slideshow-container')) {
        initSlideshow();
        showSlides();
    }
});

// Initialize slides positioning
function initSlideshow() {
    let slides = document.getElementsByClassName("slide");
    
    if (slides.length > 0) {
        // Set first slide as active
        slides[0].classList.add("active");
        slides[0].style.left = "0";
        
        // Position remaining slides to the right
        for (let i = 1; i < slides.length; i++) {
            slides[i].style.left = "100%";
        }
    }
}

// Auto advance slides with horizontal scroll
function showSlides() {
    let slides = document.getElementsByClassName("slide");
    
    if (slides.length === 0) return;
    
    // Get current active slide
    let currentSlide = slides[slideIndex];
    
    // Calculate next slide index
    slideIndex++;
    if (slideIndex >= slides.length) {
        slideIndex = 0;
    }
    
    let nextSlide = slides[slideIndex];
    
    // Animate transition
    currentSlide.classList.remove("active");
    currentSlide.classList.add("prev");
    currentSlide.style.left = "-100%";
    
    nextSlide.classList.add("active");
    nextSlide.style.left = "0";
    
    // Reset previous slide position after transition
    setTimeout(function() {
        currentSlide.classList.remove("prev");
        currentSlide.style.left = "100%";
    }, 800);
    
    // Change slide every 5 seconds
    setTimeout(showSlides, 5000);
}