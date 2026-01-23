<?php

function mytheme_add_woocommerce_support() {
    
    //woo commerce support
    add_theme_support( 'woocommerce' );
    
    //set image sizes , also can set in WooCommerce > Product Images section
    add_theme_support( 'post-thumbnails' );

    //register nav menu
    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'mytheme' ),
    ) );

}add_action( 'after_setup_theme', 'mytheme_add_woocommerce_support' );


function mytheme_assets (){

     //css
     wp_enqueue_style( 'mytheme-style', get_stylesheet_uri() );
     wp_enqueue_style( 'mytheme-custom-style', get_template_directory_uri() . '/assets/css/style.css' );

     //js
     wp_enqueue_script( 'mytheme-script', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), null, true );

     // Enqueue checkout fields script on checkout page only
     if ( is_checkout() ) {
         wp_enqueue_script( 
             'mytheme-checkout-fields', 
             get_template_directory_uri() . '/assets/js/checkout-fields.js', 
             array('jquery', 'wc-checkout'), 
             '1.0.0', 
             true 
         );
     }
    
    // Enqueue variation buttons script on single product pages
     if ( is_product() ) {
         wp_enqueue_script( 
             'variation-buttons', 
             get_template_directory_uri() . '/assets/js/variation-buttons.js', 
             array('jquery', 'wc-add-to-cart-variation'), // Added proper dependency
             filemtime( get_template_directory() . '/assets/js/variation-buttons.js' ), // Cache busting
             true 
         );
     }
     


}
add_action( 'wp_enqueue_scripts', 'mytheme_assets' );

