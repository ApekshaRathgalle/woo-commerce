<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<header class="site-header">

    <!-- Site Branding -->
    <div class="site-branding">
        <h1 class="site-title">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                <?php bloginfo( 'name' ); ?>
            </a>
        </h1>
    </div>

    <!-- Navigation -->
    <nav class="main-navigation">
        <?php
        if ( has_nav_menu( 'primary' ) ) {
            wp_nav_menu( array(
                'theme_location' => 'primary',
                'fallback_cb'    => false,
            ) );
        } else {
            // Fallback menu
            echo '<ul class="menu">';
            echo '<li><a href="' . esc_url( home_url( '/' ) ) . '">Home</a></li>';
            echo '<li><a href="' . esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ) . '">Shop</a></li>';
            echo '<li><a href="' . esc_url( wc_get_checkout_url() ) . '">Checkout</a></li>';
            echo '</ul>';
        }
        ?>
    </nav>

    <!-- Mini Cart Container -->
    <div class="header-mini-cart">
        <a href="<?php echo wc_get_cart_url(); ?>" class="mini-cart-link">
            🛒
            <span class="mini-cart-count">
                <?php echo WC()->cart->get_cart_contents_count(); ?>
            </span>
        </a>

        <!-- AJAX cart content  load here -->
        <div class="mini-cart-content">
            <?php woocommerce_mini_cart(); ?>
        </div>
    </div>

</header>
