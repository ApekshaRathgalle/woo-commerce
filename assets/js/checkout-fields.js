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
                $('#billing_vat_number_field label').addClass('required');
                console.log('✓ VAT shown');
            } else {
                $('#billing_vat_number_field').slideUp(300).removeClass('validate-required');
                $('#billing_vat_number_field label').removeClass('required');
                $('#billing_vat_number').val('');
                console.log('✗ VAT hidden');
            }
        },
        
        bindEvents: function() {
            var self = this;
            
            $(document.body).on('change', '#billing_business_type, #billing_country', function() {
                console.log('Field changed:', this.id);
                self.toggleVATField();
            });
            
            $(document.body).on('updated_checkout', function() {
                console.log('updated_checkout event');
                setTimeout(function() { self.toggleVATField(); }, 100);
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
        
        // Try multiple times to ensure fields are loaded
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