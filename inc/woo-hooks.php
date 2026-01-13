<?php

//prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

/**
 * WooCommerce Hooks
 */

//Add custom product fields
function add_custom_product_badge (){
     
    // 
     global $woocommerce, $post;

     echo '<div class ="options group">';

     woocommerce_wp_select(
            array(
                'id' => '_product_badge',
                'label' => __('Product Badge', 'woocommerce'),
                'options' => array(

                          ''          => __('Select Badge', 'mytheme'),
                          'local'     => __('Local', 'mytheme'),
                          'imported'  => __('Imported', 'mytheme')
                ),
            )
     );
        echo '</div>';
}add_action( 'woocommerce_product_options_general_product_data', 'add_custom_product_badge' );

//Save custom product fields
function save_custom_product_badge ( $post_id ){

       $product_badge = isset($_POST['_product_badge']) ? sanitize_text_field( $_POST['_product_badge'] ) : '';
       update_post_meta( $post_id, '_product_badge', $product_badge );
}add_action( 'woocommerce_process_product_meta', 'save_custom_product_badge' );

//Display badge on shop/category pages

function display_product_badge(){

         global $product;

         $badge = get_post_meta($product -> get_id() , '_product_badge', true );

            if ( $badge ) {
                echo '<span class="custom-product-badge">' . esc_html(ucfirst( $badge ) ). '</span>';

            }
}add_action( 'woocommerce_before_shop_loop_item_title', 'display_product_badge');