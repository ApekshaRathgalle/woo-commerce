<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Multi-Currency Support Based on Country Selection at Checkout Only
 */

// Define currency mappings for countries
function theme_get_country_currency_map() {
    return array(
        'LK' => array('code' => 'LKR', 'symbol' => 'Rs.', 'rate' => 1),        // Sri Lanka (base)
        'US' => array('code' => 'USD', 'symbol' => '$', 'rate' => 0.0031),     // United States
        'GB' => array('code' => 'GBP', 'symbol' => '£', 'rate' => 0.0024),     // United Kingdom
        'EU' => array('code' => 'EUR', 'symbol' => '€', 'rate' => 0.0028),     // European Union
        'IN' => array('code' => 'INR', 'symbol' => '₹', 'rate' => 0.26),       // India
        'AU' => array('code' => 'AUD', 'symbol' => 'A$', 'rate' => 0.0046),    // Australia
        'CA' => array('code' => 'CAD', 'symbol' => 'C$', 'rate' => 0.0042),    // Canada
        'SG' => array('code' => 'SGD', 'symbol' => 'S$', 'rate' => 0.0041),    // Singapore
        'AE' => array('code' => 'AED', 'symbol' => 'د.إ', 'rate' => 0.011),    // UAE
        'JP' => array('code' => 'JPY', 'symbol' => '¥', 'rate' => 0.47),       // Japan
        'CN' => array('code' => 'CNY', 'symbol' => '¥', 'rate' => 0.022),      // China
    );
}

/**
 * Get currency based on country - DEFAULT TO LKR
 */
function theme_get_currency_by_country($country_code) {
    $currency_map = theme_get_country_currency_map();
    
    // Check if country has specific currency
    if (isset($currency_map[$country_code])) {
        return $currency_map[$country_code];
    }
    
    // DEFAULT TO LKR FOR ALL OTHER COUNTRIES
    return $currency_map['LK'];
}

/**
 * Store selected currency in session ONLY during checkout
 */
add_action('woocommerce_checkout_update_order_review', 'theme_update_currency_on_country_change');
function theme_update_currency_on_country_change($post_data) {
    // Check if WooCommerce session exists
    if ( ! WC() || ! WC()->session ) {
        return;
    }
    
    parse_str($post_data, $data);// Convert serialized data (string) to array
    
    if (isset($data['billing_country'])) {
        $country = sanitize_text_field($data['billing_country']);
        $currency_data = theme_get_currency_by_country($country);
        
        //📌wc_session = WooCommerce session to store data across pages
        //creates unique session ID and store as a cookie in browser
        //sessions are stored in database table wp_woocommerce_sessions
        WC()->session->set('selected_currency', $currency_data['code']);
        WC()->session->set('selected_currency_rate', $currency_data['rate']);
        WC()->session->set('selected_currency_symbol', $currency_data['symbol']);
        
        error_log('Currency updated to: ' . $currency_data['code']);
    }
}

/**
 * Change currency  on checkout page
 */
add_filter('woocommerce_currency', 'theme_change_currency');
function theme_change_currency($currency) {
    //  apply on checkout page
    if ( ! is_checkout() ) {
        return 'LKR'; // Always LKR outside checkout
    }
    
    // Check if WooCommerce session exists
    if ( ! is_admin() && WC() && WC()->session ) {
        $selected_currency = WC()->session->get('selected_currency');
        
        if ($selected_currency) {
            return $selected_currency;
        }
    }
    
    return 'LKR'; // Default to LKR
}

/**
 * Change currency symbol ONLY on checkout page
 */
add_filter('woocommerce_currency_symbol', 'theme_change_currency_symbol', 10, 2);
function theme_change_currency_symbol($currency_symbol, $currency) {
    // ONLY apply on checkout page
    if ( ! is_checkout() ) {
        return 'Rs.'; // Always LKR symbol outside checkout
    }
    
    // Check if WooCommerce session exists
    if ( ! is_admin() && WC() && WC()->session ) {
        $custom_symbol = WC()->session->get('selected_currency_symbol');
        
        if ($custom_symbol) {
            return $custom_symbol;
        }
    }
    
    return 'Rs.'; // Default to LKR symbol
}

/**
 * Convert prices ONLY on checkout page
 */
add_filter('woocommerce_product_get_price', 'theme_convert_product_price', 10, 2);
add_filter('woocommerce_product_get_regular_price', 'theme_convert_product_price', 10, 2);
add_filter('woocommerce_product_get_sale_price', 'theme_convert_product_price', 10, 2);
add_filter('woocommerce_variation_prices_price', 'theme_convert_product_price', 10, 2);
add_filter('woocommerce_variation_prices_regular_price', 'theme_convert_product_price', 10, 2);
add_filter('woocommerce_variation_prices_sale_price', 'theme_convert_product_price', 10, 2);

function theme_convert_product_price($price, $product) {
    if (!$price) {
        return $price;
    }
    
    // ONLY convert on checkout page
    if ( ! is_checkout() ) {
        return $price; // No conversion outside checkout
    }
    
    // Check if WooCommerce session exists
    if ( ! is_admin() && WC() && WC()->session ) {
        $rate = WC()->session->get('selected_currency_rate');
        
        if ($rate && $rate != 1) {
            // Convert from LKR to selected currency
            $converted_price = $price * $rate;
            return round($converted_price, 2);
        }
    }
    
    return $price;
}

/**
 * Display currency info on checkout
 */
add_action('woocommerce_before_checkout_form', 'theme_display_currency_notice', 5);
function theme_display_currency_notice() {
    // Check if session exists
    if ( ! WC() || ! WC()->session ) {
        return;
    }
    
    $selected_currency = WC()->session->get('selected_currency');
    
    if ($selected_currency && $selected_currency !== 'LKR') {
        $symbol = WC()->session->get('selected_currency_symbol');
        
        wc_print_notice(
            sprintf(
                __('💱 Prices are displayed in %s (%s). Base currency: Sri Lankan Rupees (Rs.)', 'mytheme'),
                $selected_currency,
                $symbol
            ),
            'notice'
        );
    }
}

/**
 * Save selected currency to order meta
 */
add_action('woocommerce_checkout_update_order_meta', 'theme_save_order_currency');
function theme_save_order_currency($order_id) {
    // Check if session exists
    if ( ! WC() || ! WC()->session ) {
        return;
    }
    
    $selected_currency = WC()->session->get('selected_currency');
    $selected_rate = WC()->session->get('selected_currency_rate');
    
    if ($selected_currency) {
        update_post_meta($order_id, '_order_currency', $selected_currency);
        update_post_meta($order_id, '_order_currency_rate', $selected_rate);
        
        error_log('Saved order currency: ' . $selected_currency);
    }
}

/**
 * Display currency in admin order
 */
add_action('woocommerce_admin_order_data_after_billing_address', 'theme_display_order_currency_in_admin', 10, 1);
function theme_display_order_currency_in_admin($order) {
    $currency = get_post_meta($order->get_id(), '_order_currency', true);
    $rate = get_post_meta($order->get_id(), '_order_currency_rate', true);
    
    if ($currency) {
        echo '<p><strong>' . __('Order Currency', 'mytheme') . ':</strong> ' . esc_html($currency) . '</p>';
        
        if ($rate) {
            echo '<p><strong>' . __('Exchange Rate', 'mytheme') . ':</strong> ' . esc_html($rate) . '</p>';
        }
    }
}