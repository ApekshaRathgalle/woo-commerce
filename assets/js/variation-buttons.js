jQuery(function($) {
    'use strict';
    
    console.log('=== Enhanced Variation Buttons Script Loaded ===');
    
    // Initialize variation buttons
    function initVariationButtons() {
        console.log('Initializing variation buttons...');
        
        const $form = $('.variations_form');
        
        if (!$form.length) {
            console.log('No variations form found');
            return;
        }
        
        // Process each variation attribute
        $('.variations select').each(function() {
            const $select = $(this);
            const attributeName = $select.attr('name');
            const $row = $select.closest('tr');
            const $td = $select.closest('td');
            
            console.log('Processing select:', attributeName);
            
            // Check if buttons already exist
            if ($td.find('.variation-buttons').length > 0) {
                console.log('Buttons already exist for:', attributeName);
                return;
            }
            
            // Create buttons container
            const $buttonsContainer = $('<div class="variation-buttons"></div>');
            
            // Get all options
            $select.find('option').each(function() {
                const $option = $(this);
                const value = $option.val();
                const text = $option.text();
                
                // Skip the default "Choose an option" option
                if (value === '') {
                    return;
                }
                
                // Determine if this is a color attribute
                const isColor = attributeName.toLowerCase().includes('color') || 
                               attributeName.toLowerCase().includes('colour');
                
                // Create button
                const $button = $('<button type="button" class="variation-button"></button>');
                $button.attr('data-value', value);
                $button.attr('data-attribute', attributeName);
                
                if (isColor) {
                    $button.addClass('color-swatch');
                    const colorValue = getColorCode(text);
                    if (colorValue) {
                        $button.css('background-color', colorValue);
                        // Add light border for light colors
                        if (isLightColor(colorValue)) {
                            $button.css('border-color', '#ddd');
                        }
                        // Add tooltip for color name
                        $button.attr('title', text);
                        $button.append('<span class="color-name">' + text + '</span>');
                    } else {
                        $button.text(text);
                    }
                } else {
                    $button.text(text);
                }
                
                // Add click handler
                $button.on('click', function(e) {
                    e.preventDefault();
                    
                    const $btn = $(this);
                    
                    // Don't do anything if disabled
                    if ($btn.hasClass('disabled')) {
                        return;
                    }
                    
                    const btnValue = $btn.attr('data-value');
                    console.log('Button clicked:', btnValue);
                    
                    // Remove selected from all buttons in this group
                    $buttonsContainer.find('.variation-button').removeClass('selected');
                    
                    // Add selected to this button
                    $btn.addClass('selected');
                    
                    // Update the select dropdown value
                    $select.val(btnValue).trigger('change');
                    
                    // Add ripple effect
                    createRipple($btn, e);
                    
                    console.log('Select value updated to:', btnValue);
                });
                
                $buttonsContainer.append($button);
            });
            
            // Insert buttons after the select
            $select.after($buttonsContainer);
            console.log('Buttons added for:', attributeName);
        });
        
        // Setup event listeners for WooCommerce updates
        setupEventListeners();
        
        // Sync initial state
        syncButtonsWithSelects();
    }
    
    // Create ripple effect
    function createRipple($button, e) {
        const $ripple = $('<span class="ripple"></span>');
        $button.append($ripple);
        
        const rect = $button[0].getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        $ripple.css({
            left: x + 'px',
            top: y + 'px'
        });
        
        setTimeout(function() {
            $ripple.remove();
        }, 600);
    }
    
    // Get color code from color name
    function getColorCode(colorName) {
        const colorMap = {
            'red': '#dc2626',
            'blue': '#2563eb',
            'green': '#16a34a',
            'black': '#000000',
            'white': '#ffffff',
            'yellow': '#eab308',
            'pink': '#ec4899',
            'purple': '#9333ea',
            'orange': '#ea580c',
            'brown': '#92400e',
            'gray': '#6b7280',
            'grey': '#6b7280',
            'navy': '#1e3a8a',
            'beige': '#f5f5dc',
            'cream': '#fffdd0',
            'gold': '#fbbf24',
            'silver': '#d1d5db',
            'maroon': '#7f1d1d',
            'olive': '#65a30d',
            'lime': '#84cc16',
            'teal': '#14b8a6',
            'aqua': '#06b6d4',
            'cyan': '#06b6d4',
            'magenta': '#c026d3',
            'coral': '#fb7185',
            'khaki': '#f0e68c',
            'lavender': '#e9d5ff',
        };
        
        const normalizedName = colorName.toLowerCase().trim();
        return colorMap[normalizedName] || null;
    }
    
    // Check if color is light
    function isLightColor(color) {
        const hex = color.replace('#', '');
        const r = parseInt(hex.substr(0, 2), 16);
        const g = parseInt(hex.substr(2, 2), 16);
        const b = parseInt(hex.substr(4, 2), 16);
        const brightness = ((r * 299) + (g * 587) + (b * 114)) / 1000;
        return brightness > 200;
    }
    
    // Setup event listeners for WooCommerce variation updates
    function setupEventListeners() {
        const $form = $('form.variations_form');
        
        if ($form.length === 0) {
            console.log('No variations form found for event listeners');
            return;
        }
        
        console.log('Setting up event listeners...');
        
        // Listen for WooCommerce variation updates
        $form.on('woocommerce_update_variation_values', function() {
            console.log('WooCommerce variation values updated');
            updateButtonStates();
        });
        
        // Listen for when a variation is found
        $form.on('found_variation', function(event, variation) {
            console.log('Variation found:', variation);
            updateImageGallery(variation);
        });
        
        // Listen for variation reset
        $form.on('reset_data', function() {
            console.log('Variation reset');
            $('.variation-button').removeClass('selected');
            resetImageGallery();
        });
        
        // Listen for select changes (to sync buttons with selects)
        $('.variations select').on('change', function() {
            syncButtonsWithSelects();
        });
    }
    
    // Sync buttons with select values
    function syncButtonsWithSelects() {
        $('.variations select').each(function() {
            const $select = $(this);
            const selectedValue = $select.val();
            const $container = $select.siblings('.variation-buttons');
            
            $container.find('.variation-button').removeClass('selected');
            if (selectedValue) {
                $container.find('.variation-button').each(function() {
                    if ($(this).attr('data-value') === selectedValue) {
                        $(this).addClass('selected');
                    }
                });
            }
        });
    }
    
    // Update button states based on available variations
    function updateButtonStates() {
        console.log('Updating button states...');
        
        $('.variations select').each(function() {
            const $select = $(this);
            const $container = $select.siblings('.variation-buttons');
            
            $select.find('option').each(function() {
                const $option = $(this);
                const optionValue = $option.val();
                
                if (optionValue === '') return;
                
                // Find matching button
                $container.find('.variation-button').each(function() {
                    const $button = $(this);
                    if ($button.attr('data-value') === optionValue) {
                        // Enable/disable based on option availability
                        if ($option.is(':disabled') || !$option.is(':enabled')) {
                            $button.addClass('disabled');
                        } else {
                            $button.removeClass('disabled');
                        }
                    }
                });
            });
        });
        
        syncButtonsWithSelects();
    }
    
    // Update image gallery when variation is selected
    function updateImageGallery(variation) {
        if (!variation.image || !variation.image.src) {
            return;
        }
        
        const $gallery = $('.woocommerce-product-gallery');
        const $mainImage = $gallery.find('.woocommerce-product-gallery__image img');
        
        if ($mainImage.length) {
            // Store original image if not stored
            if (!$mainImage.data('original-src')) {
                $mainImage.data('original-src', $mainImage.attr('src'));
                $mainImage.data('original-srcset', $mainImage.attr('srcset'));
            }
            
            // Update image with fade effect
            $mainImage.css('opacity', '0');
            setTimeout(function() {
                $mainImage.attr('src', variation.image.src);
                $mainImage.attr('srcset', variation.image.srcset || '');
                $mainImage.attr('alt', variation.image.alt || '');
                $mainImage.css('opacity', '1');
            }, 300);
        }
    }
    
    // Reset image gallery to original
    function resetImageGallery() {
        const $gallery = $('.woocommerce-product-gallery');
        const $mainImage = $gallery.find('.woocommerce-product-gallery__image img');
        
        if ($mainImage.length && $mainImage.data('original-src')) {
            $mainImage.css('opacity', '0');
            setTimeout(function() {
                $mainImage.attr('src', $mainImage.data('original-src'));
                $mainImage.attr('srcset', $mainImage.data('original-srcset') || '');
                $mainImage.css('opacity', '1');
            }, 300);
        }
    }
    
    // Initialize when document is ready
    $(document).ready(function() {
        console.log('Document ready - checking for variations form...');
        
        // Wait for WooCommerce to initialize
        setTimeout(function() {
            if ($('.variations_form').length > 0) {
                console.log('Variations form found, initializing...');
                initVariationButtons();
                
                // Initial button state update
                setTimeout(function() {
                    updateButtonStates();
                }, 200);
            } else {
                console.log('No variations form found on this page');
            }
        }, 100);
    });
    
    // Reinitialize on AJAX complete (for dynamic content)
    $(document).ajaxComplete(function(event, xhr, settings) {
        // Only reinitialize if buttons don't exist
        if ($('.variations_form').length > 0 && $('.variation-buttons').length === 0) {
            console.log('AJAX complete - reinitializing...');
            setTimeout(initVariationButtons, 100);
        }
    });
});