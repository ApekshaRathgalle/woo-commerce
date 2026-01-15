<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Update mini cart fragments via AJAX
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'theme_update_header_mini_cart' );

function theme_update_header_mini_cart( $fragments ) {

    // Update cart count
    ob_start();
    ?>
    <span class="mini-cart-count">
        <?php echo WC()->cart->get_cart_contents_count(); ?>
    </span>
    <?php
    $fragments['.mini-cart-count'] = ob_get_clean();

    // Update mini cart content
    ob_start();
    ?>
    <div class="mini-cart-content">
        <?php woocommerce_mini_cart(); ?>
    </div>
    <?php
    $fragments['.mini-cart-content'] = ob_get_clean();

    return $fragments;
}


/**
 * Apply automatic 20% discount when cart subtotal exceeds 20,000 LKR
 */
add_action( 'woocommerce_cart_calculate_fees', 'theme_auto_discount_on_subtotal', 10, 1 );
function theme_auto_discount_on_subtotal( $cart ) {
    // Avoid running on admin or during AJAX except cart/checkout updates
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }

    // Get cart subtotal (before discounts and shipping)
    $subtotal = $cart->get_subtotal();
    
    // Discount threshold (20,000 LKR)
    $threshold = 20000;
    
    // Discount percentage
    $discount_percentage = 20;
    
    // Check if subtotal exceeds threshold
    if ( $subtotal > $threshold ) {
        // Calculate discount amount (20% of subtotal)
        $discount_amount = ( $subtotal * $discount_percentage ) / 100;
        
        // Add discount as a negative fee
        $cart->add_fee( 
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