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
