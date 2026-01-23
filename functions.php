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


//enqueue scripts and styles
function mytheme_enqueue_scripts() {
    wp_enqueue_script( 'jquery' );
}
add_action( 'wp_enqueue_scripts', 'mytheme_enqueue_scripts' );
