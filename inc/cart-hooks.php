<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Update mini cart fragments via AJAX to update only the specified parts of the page without realoading the entire page.
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'theme_update_header_mini_cart' );

function theme_update_header_mini_cart( $fragments ) {
    //fragemnt - small pieces of HTML that woocommerce can update via ajax
    // two fragments- 1: get cart count, 2: get mini cart content
    // Update cart count 
    ob_start(); //starts output buffering. before sending output to browser html stores it as a string in buffer
    ?>
    <span class="mini-cart-count">
        <?php echo WC()->cart->get_cart_contents_count(); ?>
    </span>
    <?php
    $fragments['.mini-cart-count'] = ob_get_clean(); //collects the buffered HTML and clears the buffer

    // Update mini cart content
    ob_start();
    ?>
    <div class="mini-cart-content">
        <?php woocommerce_mini_cart(); ?>
    </div>
    <?php
    $fragments['.mini-cart-content'] = ob_get_clean();

    return $fragments;// sends the updated HTML fragments back to WooCommerce

    //css selectors are used to identify the elements and change them dynamically
}


/**
 * Apply automatic 20% discount when cart subtotal exceeds 20,000 LKR
 * add_action(hook name, function_name, priority, accepted_args)
 * 1= how many arguments the function accepts
 * 10= priority of execution (lower number = higher priority)
 */
add_action( 'woocommerce_cart_calculate_fees', 'theme_auto_discount_on_subtotal', 10, 1 );
function theme_auto_discount_on_subtotal( $cart ) {

    // Avoid running on admin area or during AJAX except cart/checkout updates
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }

    // Get cart subtotal (before discounts and shipping)
    $subtotal = $cart->get_subtotal();
    
    // Discount threshold (20,000 LKR) , minimum subtotal required for discount
    $threshold = 20000;
    
    // Discount percentage
    $discount_percentage = 20;
    
    // Check if subtotal exceeds threshold
    if ( $subtotal > $threshold ) {
        // Calculate discount amount (20% of subtotal)
        $discount_amount = ( $subtotal * $discount_percentage ) / 100;
        
        // Add discount as a negative fee
        $cart->add_fee( 
            //__() is for translation to other languages
            sprintf( __( 'Automatic Discount (%d%% off)', 'mytheme' ), $discount_percentage ), 
            -$discount_amount, 
            false 
        );
        
        error_log( sprintf( 
            'Auto Discount Applied: Subtotal: %s LKR, Discount: %s LKR', 
            number_format( $subtotal, 2 ), 
            number_format( $discount_amount, 2 ) 
        ) );
    }
}

/**
 * Display discount message in cart
 */
add_action( 'woocommerce_before_cart', 'theme_display_discount_message' );
function theme_display_discount_message() {
    $subtotal = WC()->cart->get_subtotal();
    $threshold = 20000;
    
    if ( $subtotal > $threshold ) {
        $discount_percentage = 20;
        wc_print_notice( 
            sprintf( 
                __( '🎉 Congratulations! You received a %d%% automatic discount on orders above %s LKR!', 'mytheme' ), 
                $discount_percentage, 
                number_format( $threshold, 2 ) 
            ), 
            'success' 
        );
    } else {
        $remaining = $threshold - $subtotal;
        wc_print_notice( 
            sprintf( 
                __( 'Add %s LKR more to your cart to get 20%% automatic discount!', 'mytheme' ), 
                number_format( $remaining, 2 ) 
            ), 
            'notice' 
        );
    }
}

/**
 * Display discount message on checkout page
 */
add_action( 'woocommerce_before_checkout_form', 'theme_display_checkout_discount_message', 5 );
function theme_display_checkout_discount_message() {
    $subtotal = WC()->cart->get_subtotal();
    $threshold = 20000;
    
    if ( $subtotal > $threshold ) {
        $discount_percentage = 20;
        wc_print_notice( 
            sprintf( 
                __( '🎉 Your order qualifies for a %d%% automatic discount!', 'mytheme' ), 
                $discount_percentage 
            ), 
            'success' 
        );
    }
}