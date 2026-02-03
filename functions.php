<?php

//prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

//theme setup file
require_once get_template_directory() . '/inc/theme-setup.php';

//hooks file
require_once get_template_directory() . '/inc/woo-hooks.php';
require_once get_template_directory() . '/inc/cart-hooks.php';
require_once get_template_directory() . '/inc/checkout-hooks.php';
require_once get_template_directory() . '/inc/shipping-hooks.php';
require_once get_template_directory() . '/inc/currency-hooks.php';
require_once get_template_directory() . '/inc/bundle-hooks.php';


//enqueue scripts and styles
function mytheme_enqueue_scripts() {
    wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'mytheme_enqueue_scripts' );



// Allow HTTP for local development
add_filter('woocommerce_api_check_authentication', function($user) {
    return $user;
}, 10, 1);



/**
 * Enable WooCommerce REST API authentication
 * Allows external applications to access WooCommerce products
 */
add_filter('woocommerce_rest_check_permissions', function($permission, $context, $object_id, $post_type) {
    // Allow read access to products
    if ($context === 'read' && $post_type === 'product') {
        return true;
    }
    return $permission;
}, 10, 4);

/**
 * Disable REST API authentication requirement for product endpoints
 * WARNING: Only use in development. In production, use proper authentication.
 */

/*add_filter('rest_authentication_errors', function($result) {
    // If already authenticated, return result
    if (!empty($result)) {
        return $result;
    }
    
    // Allow access to WooCommerce product endpoints
    if (strpos($_SERVER['REQUEST_URI'], '/wp-json/wc/v3/products') !== false) {
        return true;
    }
    
    return $result;
});*/