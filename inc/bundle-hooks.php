<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Product Bundle Discounts - Category Specific
 */

/**
 * Add bundle discount settings to WooCommerce > Settings > Products
 */
add_filter( 'woocommerce_get_sections_products', 'theme_add_bundle_section' );
function theme_add_bundle_section( $sections ) {
    $sections['bundle_discounts'] = __( 'Bundle Discounts', 'mytheme' );
    return $sections;
}

add_filter( 'woocommerce_get_settings_products', 'theme_add_bundle_settings', 10, 2 );
function theme_add_bundle_settings( $settings, $current_section ) {
    
    if ( 'bundle_discounts' !== $current_section ) {
        return $settings;
    }
    
    $bundle_settings = array();
    
    // Section Title
    $bundle_settings[] = array(
        'name' => __( 'Bundle Discount Settings', 'mytheme' ),
        'type' => 'title',
        'desc' => __( 'Configure quantity-based bundle discounts for specific categories', 'mytheme' ),
        'id'   => 'bundle_discount_settings',
    );
    
    // Enable Bundle Discounts
    $bundle_settings[] = array(
        'name'    => __( 'Enable Bundle Discounts', 'mytheme' ),
        'desc'    => __( 'Enable automatic discounts based on quantity', 'mytheme' ),
        'id'      => 'bundle_discount_enable',
        'type'    => 'checkbox',
        'default' => 'yes',
    );
    
    //  Category Selection**
    $bundle_settings[] = array(
        'name'    => __( 'Apply to Categories', 'mytheme' ),
        'desc'    => __( 'Select categories where bundle discounts apply (leave empty for all products)', 'mytheme' ),
        'id'      => 'bundle_discount_categories',
        'type'    => 'multiselect',
        'class'   => 'wc-enhanced-select',
        'options' => theme_get_product_categories(),
        'default' => array(),
    );
    
    // Minimum Quantity
    $bundle_settings[] = array(
        'name'              => __( 'Minimum Quantity', 'mytheme' ),
        'desc'              => __( 'Minimum quantity to qualify for bundle discount', 'mytheme' ),
        'id'                => 'bundle_min_quantity',
        'type'              => 'number',
        'default'           => '3',
        'custom_attributes' => array(
            'min'  => '2',
            'step' => '1',
        ),
    );
    
    // Bundle Discount Percentage
    $bundle_settings[] = array(
        'name'              => __( 'Bundle Discount (%)', 'mytheme' ),
        'desc'              => __( 'Percentage discount for bundles (e.g., 20 for 20% off)', 'mytheme' ),
        'id'                => 'bundle_discount_percentage',
        'type'              => 'number',
        'default'           => '20',
        'custom_attributes' => array(
            'min'  => '0',//minimum value admin can enter
            'max'  => '100',//maximum value admin can enter
            'step' => '1',//increment by 1
        ),
    );
    
    // Section End
    $bundle_settings[] = array(
        'type' => 'sectionend',
        'id'   => 'bundle_discount_settings',
    );
    
    return $bundle_settings;
}

/**
 * Get all product categories for settings dropdown
 * WooCommerce, product categories are stored in the taxonomy called 'product_cat'.
 * $category - term_id , name , slug , term_group , term_taxonomy_id
 */
function theme_get_product_categories() {
    $categories = get_terms( array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,// Show all categories, even if empty
    ) );
    
    $category_options = array();
    
    if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
        foreach ( $categories as $category ) {
            $category_options[ $category->term_id ] = $category->name;
        }
    }
    
    return $category_options;
}

/**
 * Check if product belongs to bundle discount categories
 */
function theme_is_product_in_bundle_categories( $product_id ) {
    $selected_categories = get_option( 'bundle_discount_categories', array() );
    
    // If no categories selected, apply to all products
    if ( empty( $selected_categories ) ) {
        return true;
    }
    
    // Get product categories
    $product_categories = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
    
    // Check if product is in any of the selected categories
    foreach ( $product_categories as $cat_id ) {
        if ( in_array( $cat_id, $selected_categories ) ) {
            return true;
        }
    }
    
    return false;
}

/**
 * Apply bundle discount based on cart item quantities (Category Specific)
 */
add_action( 'woocommerce_cart_calculate_fees', 'theme_apply_bundle_discount', 20, 1 );
function theme_apply_bundle_discount( $cart ) {
    
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) { //DOING_AJAX = BUT continue if it's an AJAX request 
        return;
    }
    
    // Check if bundle discounts are enabled
    if ( get_option( 'bundle_discount_enable', 'yes' ) !== 'yes' ) {
        return;
    }
    
    $min_quantity = (int) get_option( 'bundle_min_quantity', 3 );
    $discount_percentage = (float) get_option( 'bundle_discount_percentage', 20 );
    
    $total_discount = 0;
    $qualifying_items = array();
    
    // Loop through cart items
    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
        $product = $cart_item['data'];
        $product_id = $cart_item['product_id'];
        $quantity = $cart_item['quantity'];
        
        // **CHECK IF PRODUCT IS IN BUNDLE CATEGORIES**
        if ( ! theme_is_product_in_bundle_categories( $product_id ) ) {
            continue; // Skip products not in selected categories
        }
        
        // Check if quantity qualifies for bundle discount
        if ( $quantity >= $min_quantity ) {
            $item_total = $product->get_price() * $quantity;
            $item_discount = ( $item_total * $discount_percentage ) / 100;
            $total_discount += $item_discount;
            
            $qualifying_items[] = array(
                'name'     => $product->get_name(),
                'qty'      => $quantity,
                'discount' => $item_discount,
            );
            
            error_log( sprintf(
                'Bundle Discount: Product %s (ID: %d), Qty: %d, Discount: %s LKR',
                $product->get_name(),
                $product_id,
                $quantity,
                number_format( $item_discount, 2 )
            ) );
        }
    }
    
    // Apply total bundle discount
    if ( $total_discount > 0 ) {
        $discount_label = sprintf( 
            __( 'Bundle Discount (%d+ items, %s%% off)', 'mytheme' ),
            $min_quantity,
            $discount_percentage
        );
        
        $cart->add_fee( $discount_label, -$total_discount, false );
        
        error_log( sprintf(
            'Total Bundle Discount Applied: %s LKR on %d qualifying items',
            number_format( $total_discount, 2 ),
            count( $qualifying_items )
        ) );
    }
}

/**
 * Display bundle discount notice on product page (Category Specific)
 */
add_action( 'woocommerce_before_add_to_cart_button', 'theme_display_bundle_notice' );
function theme_display_bundle_notice() {
    
    if ( get_option( 'bundle_discount_enable', 'yes' ) !== 'yes' ) {
        return;
    }
    
    global $product;
    
    // Only show for simple and variable products
    if ( ! $product || ! $product->is_purchasable() ) {
        return;
    }
    
    $product_id = $product->get_id();
    
    // **CHECK IF PRODUCT IS IN BUNDLE CATEGORIES**
    if ( ! theme_is_product_in_bundle_categories( $product_id ) ) {
        return; // Don't show notice if product not in bundle categories
    }
    
    $min_quantity = (int) get_option( 'bundle_min_quantity', 3 );
    $discount_percentage = (float) get_option( 'bundle_discount_percentage', 20 );
    
    ?>
    <div class="bundle-discount-notice">
        <span class="bundle-icon">🎁</span>
        <strong><?php esc_html_e( 'Bundle Deal:', 'mytheme' ); ?></strong>
        <?php 
        printf( 
            __( 'Buy %d or more and save %s%%!', 'mytheme' ),
            $min_quantity,
            $discount_percentage
        ); 
        ?>
    </div>
    <?php
}

/**
 * Display bundle discount message in cart (Category Specific)
 */
add_action( 'woocommerce_before_cart', 'theme_display_bundle_cart_notice' );
function theme_display_bundle_cart_notice() {
    
    if ( get_option( 'bundle_discount_enable', 'yes' ) !== 'yes' ) {
        return;
    }
    
    $min_quantity = (int) get_option( 'bundle_min_quantity', 3 );
    $discount_percentage = (float) get_option( 'bundle_discount_percentage', 20 );
    
    $has_bundle = false;
    $has_qualifying_products = false;
    
    // Check if any cart item qualifies
    foreach ( WC()->cart->get_cart() as $cart_item ) {
        $product_id = $cart_item['product_id'];
        
        // Check if product is in bundle categories
        if ( ! theme_is_product_in_bundle_categories( $product_id ) ) {
            continue;
        }
        
        $has_qualifying_products = true;
        
        if ( $cart_item['quantity'] >= $min_quantity ) {
            $has_bundle = true;
            break;
        }
    }
    
    if ( $has_bundle ) {
        wc_print_notice(
            sprintf(
                __( '🎉 Bundle discount applied! You\'re saving %s%% on qualifying items with %d+ quantity.', 'mytheme' ),
                $discount_percentage,
                $min_quantity
            ),
            'success'
        );
    } elseif ( $has_qualifying_products ) {
        wc_print_notice(
            sprintf(
                __( '💡 Tip: Buy %d or more of qualifying products to save %s%%!', 'mytheme' ),
                $min_quantity,
                $discount_percentage
            ),
            'notice'
        );
    }
}

/**
 * Display bundle badge on shop page (Category Specific)
 */
/* 
add_action( 'woocommerce_before_shop_loop_item_title', 'theme_display_bundle_badge', 15 );
function theme_display_bundle_badge() {
    
    if ( get_option( 'bundle_discount_enable', 'yes' ) !== 'yes' ) {
        return;
    }
    
    global $product;
    
    $product_id = $product->get_id();
    
    // **CHECK IF PRODUCT IS IN BUNDLE CATEGORIES**
    if ( ! theme_is_product_in_bundle_categories( $product_id ) ) {
        return;
    }
    
    $discount_percentage = (int) get_option( 'bundle_discount_percentage', 20 );
    
    echo '<span class="bundle-badge">Bundle: Save ' . esc_html( $discount_percentage ) . '%</span>';
}
**/