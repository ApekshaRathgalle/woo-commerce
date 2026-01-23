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

/**
 * $cart (WooCommerce cart object) -It contains:
*Products , Subtotal , Taxes , Fees , Totals
 */
add_action( 'woocommerce_cart_calculate_fees', 'theme_auto_discount_on_subtotal', 10, 1 );
function theme_auto_discount_on_subtotal( $cart ) {

    // Avoid running on admin area or during AJAX except cart/checkout updates
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }

    // Check if discount is enabled
    $discount_enabled = get_option( 'auto_discount_enable', 'yes' );
    if ( $discount_enabled !== 'yes' ) {
        return;
    }

    // Get cart subtotal (before discounts and shipping)
    $subtotal = $cart->get_subtotal();
    //get_subtotal() - encapsulated method of WC_Cart class
    
    // Get settings from admin
    $threshold = (float) get_option( 'auto_discount_threshold', 20000 );
    $discount_type = get_option( 'auto_discount_type', 'percentage' );
    $discount_percentage = (float) get_option( 'auto_discount_percentage', 20 );
    $discount_fixed = (float) get_option( 'auto_discount_fixed_amount', 5000 );
    
    // Check if subtotal exceeds threshold
    if ( $subtotal > $threshold ) {
        
        // Calculate discount based on type
        if ( $discount_type === 'percentage' ) {
            $discount_amount = ( $subtotal * $discount_percentage ) / 100;
            $discount_label = sprintf( 
                __( 'Special offer (%s%%)', 'mytheme' ), 
                $discount_percentage 
            );
        } else {
            $discount_amount = $discount_fixed;
            $discount_label = __( 'Special offer', 'mytheme' );
        }
        
        // Add discount as a negative fee bcs by default woocommerce only supports fees
        //add_fee() normally adds extra charges, but by passing a negative value create a discount
        //false = tax status (not taxable)

        $cart->add_fee( $discount_label, -$discount_amount, false );
        //add fee - abstract method of WC_Cart class
        
        error_log( sprintf( 
            'Auto Discount Applied: Type: %s, Subtotal: %s LKR, Discount: %s LKR', 
            $discount_type,
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
    
    // Check if discount is enabled
    if ( get_option( 'auto_discount_enable', 'yes' ) !== 'yes' ) {
        return;
    }
    
    $subtotal = WC()->cart->get_subtotal();
    $threshold = (float) get_option( 'auto_discount_threshold', 20000 );
    $discount_type = get_option( 'auto_discount_type', 'percentage' );
    $discount_percentage = (float) get_option( 'auto_discount_percentage', 20 );
    $discount_fixed = (float) get_option( 'auto_discount_fixed_amount', 5000 );
    
    if ( $subtotal > $threshold ) {
        // Show success message
        if ( $discount_type === 'percentage' ) {
            $message = sprintf( 
                __( '🎉 Congratulations! You received a %s%%  discount on orders above %s LKR!', 'mytheme' ), 
                $discount_percentage, 
                number_format( $threshold, 2 ) 
            );
        } else {
            $message = sprintf( 
                __( '🎉 Congratulations! You received a %s LKR discount on orders above %s LKR!', 'mytheme' ), 
                number_format( $discount_fixed, 2 ),
                number_format( $threshold, 2 ) 
            );
        }
        wc_print_notice( $message, 'success' );
    } else {
        // Show notice about remaining amount
        $remaining = $threshold - $subtotal;
        if ( $discount_type === 'percentage' ) {
            $benefit = sprintf( __( 'get %s%% off', 'mytheme' ), $discount_percentage );
        } else {
            $benefit = sprintf( __( 'save %s LKR', 'mytheme' ), number_format( $discount_fixed, 2 ) );
        }
        
        wc_print_notice( 
            sprintf( 
                __( 'Add %s LKR more to your cart to %s!', 'mytheme' ), 
                number_format( $remaining, 2 ),
                $benefit
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
    
    // Check if discount is enabled
    if ( get_option( 'auto_discount_enable', 'yes' ) !== 'yes' ) {
        return;
    }
    
    $subtotal = WC()->cart->get_subtotal();
    $threshold = (float) get_option( 'auto_discount_threshold', 20000 );
    $discount_type = get_option( 'auto_discount_type', 'percentage' );
    $discount_percentage = (float) get_option( 'auto_discount_percentage', 20 );
    
    if ( $subtotal > $threshold ) {
        if ( $discount_type === 'percentage' ) {
            $message = sprintf( 
                __( '🎉 Your order qualifies for a %s%%  discount!', 'mytheme' ), 
                $discount_percentage 
            );
        } else {
            $message = __( '🎉 Your order qualifies for an  discount!', 'mytheme' );
        }
        
        wc_print_notice( $message, 'success' );
    }
}


/**
 * Add settings section for  discounts
 */
add_filter( 'woocommerce_get_sections_products', 'theme_add_discount_section' );
function theme_add_discount_section( $sections ) {
    $sections['auto_discount'] = __( ' Discounts', 'mytheme' );
    return $sections;
}

/**
 * Add settings fields for  discounts
 */
add_filter( 'woocommerce_get_settings_products', 'theme_add_discount_settings', 10, 2 );
function theme_add_discount_settings( $settings, $current_section ) {
    
    if ( 'auto_discount' !== $current_section ) {
        return $settings;
    }
    
    $custom_settings = array();
    
    // Section Title
    $custom_settings[] = array(
        'name' => __( 'Automatic Discount Settings', 'mytheme' ),
        'type' => 'title',
        'desc' => __( 'Configure automatic discounts based on cart subtotal', 'mytheme' ),
        'id'   => 'auto_discount_settings',
    );
    
    // Enable/Disable Discount
    $custom_settings[] = array(
        'name'    => __( 'Enable Automatic Discount', 'mytheme' ),
        'desc'    => __( 'Enable automatic discount when cart subtotal exceeds threshold', 'mytheme' ),
        'id'      => 'auto_discount_enable',
        'type'    => 'checkbox',
        'default' => 'yes',
    );
    
    // Discount Threshold
    $custom_settings[] = array(
        'name'              => __( 'Discount Threshold', 'mytheme' ),
        'desc'              => __( 'Minimum cart subtotal (in LKR) required to apply discount', 'mytheme' ),
        'id'                => 'auto_discount_threshold',
        'type'              => 'number',
        'default'           => '20000',
        'custom_attributes' => array(
            'min'  => '0',
            'step' => '100',
        ),
    );
    
    // Discount Percentage
    $custom_settings[] = array(
        'name'              => __( 'Discount Percentage', 'mytheme' ),
        'desc'              => __( 'Percentage discount to apply (e.g., 20 for 20%)', 'mytheme' ),
        'id'                => 'auto_discount_percentage',
        'type'              => 'number',
        'default'           => '20',
        'custom_attributes' => array(
            'min'  => '0',
            'max'  => '100',
            'step' => '1',
        ),
    );
    
    // Discount Type
    $custom_settings[] = array(
        'name'    => __( 'Discount Type', 'mytheme' ),
        'desc'    => __( 'Choose between percentage or fixed amount discount', 'mytheme' ),
        'id'      => 'auto_discount_type',
        'type'    => 'select',
        'options' => array(
            'percentage' => __( 'Percentage (%)', 'mytheme' ),
            'fixed'      => __( 'Fixed Amount (LKR)', 'mytheme' ),
        ),
        'default' => 'percentage',
    );
    
    // Fixed Discount Amount
    $custom_settings[] = array(
        'name'              => __( 'Fixed Discount Amount', 'mytheme' ),
        'desc'              => __( 'Fixed discount amount in LKR (only if Fixed Amount is selected)', 'mytheme' ),
        'id'                => 'auto_discount_fixed_amount',
        'type'              => 'number',
        'default'           => '5000',
        'custom_attributes' => array(
            'min'  => '0',
            'step' => '100',
        ),
    );
    
    // Section End
    $custom_settings[] = array(
        'type' => 'sectionend',
        'id'   => 'auto_discount_settings',
    );
    
    return $custom_settings;
}
