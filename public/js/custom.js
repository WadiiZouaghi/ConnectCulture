$(function() {
"use strict";
    
    // Disable singlePageNav for now
    // $('#nav').singlePageNav();
    
    // Function to ensure background image is visible
    function ensureBackgroundVisible() {
        // Make sure the Diversity.jpg image is visible
        $('.slider_bg_box img').css({
            'opacity': '1',
            'visibility': 'visible',
            'display': 'block'
        });
    }
    
    // Initialize Bootstrap carousel with explicit options
    $('#customCarousel1').carousel({
        interval: 5000,  // Change slide every 5 seconds
        wrap: true,      // Loop back to the first slide
        ride: 'carousel', // Auto-start cycling
        pause: false     // Don't pause on hover
    });
    
    // Explicitly start the carousel
    $('#customCarousel1').carousel('cycle');
    
    // Force a slide after 1 second to ensure it's working
    setTimeout(function() {
        $('#customCarousel1').carousel('next');
    }, 1000);
    
    // Make sure the background image is visible
    ensureBackgroundVisible();
    
    // Apply multiple times to override any other scripts
    setTimeout(ensureBackgroundVisible, 100);
    setTimeout(ensureBackgroundVisible, 500);
    setTimeout(ensureBackgroundVisible, 1000);
    
    // Ensure background is visible after carousel events
    $('#customCarousel1').on('slid.bs.carousel', ensureBackgroundVisible);
});