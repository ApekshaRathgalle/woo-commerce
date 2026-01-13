<?php

function mytheme_add_woocommerce_support() {
    
    //woo commerce support
    add_theme_support( 'woocommerce' );
    
    //set image sizes , also can set in WooCommerce > Product Images section
    add_theme_support( 'post-thumbnails' );

}add_action( 'after_setup_theme', 'mytheme_add_woocommerce_support' );


function mytheme_assets (){

     //css
     wp_enqueue_style( 'mytheme-style', get_stylesheet_uri() );

     //js
     wp_enqueue_script( 'mytheme-script', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), null, true );

}
