jQuery(function($) {
    'use strict';
    
    console.log('=== Checkout Fields JS File Loaded ===');
    
    var CheckoutFields = {
        init: function() {
            console.log('CheckoutFields.init()');
            this.toggleVATField();
            this.bindEvents();
        },
        
        toggleVATField: function() {
            var businessType = $('#billing_business_type').val();
            var country = $('#billing_country').val();
            
            console.log('Toggle - Business Type:', businessType, 'Country:', country);
            
            if (country && businessType === 'company') {
                $('#billing_vat_number_field').slideDown(300).addClass('validate-required');
                $('#billing_vat_number').prop('required', true);
                $('#billing_vat_number_field label').addClass('required');
                
                var label = $('#billing_vat_number_field label .optional');
                if (label.length) {
                    label.remove();
                }
                
                if ($('#billing_vat_number_field label abbr.required').length === 0) {
                    $('#billing_vat_number_field label').append(' <abbr class="required" title="required">*</abbr>');
                }
                
                console.log('✓ VAT shown (REQUIRED)');
            } else {
                $('#billing_vat_number_field').slideUp(300).removeClass('validate-required');
                $('#billing_vat_number').prop('required', false);
                $('#billing_vat_number_field label').removeClass('required');
                $('#billing_vat_number_field label abbr.required').remove();
                $('#billing_vat_number').val('');
                
                console.log('✗ VAT hidden (NOT required)');
            }
        },
        
        updateCurrency: function() {
            var country = $('#billing_country').val();
            
            if (country) {
                console.log('Country changed to:', country, '- Updating currency...');
                
                // Trigger WooCommerce checkout update to refresh prices
                $(document.body).trigger('update_checkout');
            }
        },
        
        bindEvents: function() {
            var self = this;
            
            $(document.body).on('change', '#billing_business_type, #billing_country', function() {
                console.log('Field changed:', this.id);
                self.toggleVATField();
                
                // Update currency when country changes
                if (this.id === 'billing_country') {
                    self.updateCurrency();
                }
            });
            
            $(document.body).on('updated_checkout', function() {
                console.log('updated_checkout event');
                setTimeout(function() { 
                    self.toggleVATField(); 
                }, 100);
            });
            
            $(document.body).on('init_checkout', function() {
                console.log('init_checkout event');
                setTimeout(function() { self.init(); }, 100);
            });
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        console.log('Document ready - waiting for fields');
        
        var attempts = 0;
        var maxAttempts = 10;
        
        var checkAndInit = setInterval(function() {
            attempts++;
            
            if ($('#billing_business_type').length > 0) {
                console.log('Fields found, initializing...');
                CheckoutFields.init();
                clearInterval(checkAndInit);
            } else if (attempts >= maxAttempts) {
                console.warn('Fields not found after ' + maxAttempts + ' attempts');
                clearInterval(checkAndInit);
            } else {
                console.log('Attempt ' + attempts + ': Fields not ready yet...');
            }
        }, 500);
    });
});

$(document.body).on('updated_checkout', function() {
    console.log('Checkout updated - custom review refreshed');
});