// Add mobile-friendly enhancements
(function($) {
    'use strict';
    
    // Document ready
    $(function() {
        // Create a modal backdrop for mobile devices
        $('body').append('<div class="seed-catalog-modal-backdrop"></div>');
        const backdrop = $('.seed-catalog-modal-backdrop');
        
        // Update the variety list display function to show backdrop on mobile
        const originalSearchVarieties = window.seedCatalogDebug?.searchVarieties;
        if (typeof originalSearchVarieties === 'function') {
            window.seedCatalogDebug.searchVarieties = function() {
                // Call the original function first
                originalSearchVarieties.apply(this, arguments);
                
                // Show backdrop on mobile devices
                if (window.innerWidth <= 768) {
                    backdrop.addClass('visible');
                }
            };
        }
        
        // Hide the variety list and backdrop when clicking outside
        backdrop.on('click', function() {
            $('.seed-catalog-variety-list').hide();
            backdrop.removeClass('visible');
        });
        
        // Enhanced filter toggle for mobile
        $('.seed-catalog-filter-toggle').on('click', function() {
            $(this).toggleClass('active');
            $('.seed-catalog-filters-container').toggleClass('filters-visible');
        });
        
        // Make sure the variety list is hidden when clicking a variety
        $(document).on('click', '.variety-item', function() {
            backdrop.removeClass('visible');
        });
        
        // Fix positioning issue if item is at bottom of page
        const seedNameInput = $('#seed_name');
        if (seedNameInput.length) {
            seedNameInput.on('focus', function() {
                if (window.innerWidth <= 768) {
                    $('html, body').animate({
                        scrollTop: seedNameInput.offset().top - 100
                    }, 300);
                }
            });
        }

        // Improve touch experience for checkboxes and selects
        $('.seed-catalog-checkbox').on('click', function(e) {
            if (e.target.tagName !== 'INPUT') {
                const checkbox = $(this).find('input[type="checkbox"]');
                checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
            }
        });
        
        // Add box-sizing to all elements for consistent layouts
        $('<style>')
            .prop('type', 'text/css')
            .html('*, *:before, *:after { box-sizing: border-box; }')
            .appendTo('head');
        
        // Make images responsive
        $('.seed-catalog-item-image img, .seed-catalog-result-image img').attr('loading', 'lazy');
    });
})(jQuery);