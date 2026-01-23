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
                // Add specific class for each badge type
                $badge_class = 'custom-product-badge badge-' . esc_attr($badge);
                echo '<span class="' . $badge_class . '">' . esc_html(ucfirst( $badge ) ). '</span>';
            }
}add_action( 'woocommerce_before_shop_loop_item_title', 'display_product_badge');



/**
 * Remove default WooCommerce catalog ordering
 */
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

/**
 * Change default sorting dropdown text
 */
add_filter( 'woocommerce_catalog_orderby', 'theme_custom_sorting_options' );
function theme_custom_sorting_options( $options ) {
    $options = array(
        'menu_order' => __( 'Recommended', 'mytheme' ),
        'popularity' => __( 'Best Selling', 'mytheme' ),
        'rating'     => __( 'Top Rated', 'mytheme' ),
        'date'       => __( 'Newest Arrivals', 'mytheme' ),
        'price'      => __( 'Price: Low to High', 'mytheme' ),
        'price-desc' => __( 'Price: High to Low', 'mytheme' ),
    );
    return $options;
}

/**
 * Change "Default sorting" label to "Sort By"
 */
add_filter( 'woocommerce_default_catalog_orderby_options', 'theme_change_default_sorting_text' );
function theme_change_default_sorting_text( $options ) {
    $options['menu_order'] = __( 'Sort By: Recommended', 'mytheme' );
    return $options;
}

/**
 * Custom Shop Header with Search and Filters
 */
add_action( 'woocommerce_before_shop_loop', 'theme_custom_shop_header', 15 );
function theme_custom_shop_header() {
    ?>
    <div class="shop-filters-wrapper">
        <div class="shop-filters-inner">
            
            <!-- Search Bar -->
            <div class="shop-search">
                <form role="search" method="get" class="product-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
                    <input type="search" 
                           class="search-input" 
                           placeholder="<?php echo esc_attr__( 'Search products...', 'mytheme' ); ?>" 
                           value="<?php echo get_search_query(); ?>" 
                           name="s" />
                    <button type="submit" class="search-button">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                            <path d="M9 17A8 8 0 1 0 9 1a8 8 0 0 0 0 16zM19 19l-4.35-4.35" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <input type="hidden" name="post_type" value="product" />
                </form>
            </div>

            <div class="shop-filters-right">
                <!-- Category Filter -->
                <div class="shop-category">
                    <?php
                    $current_cat = isset($_GET['product_cat']) ? sanitize_text_field($_GET['product_cat']) : '';
                    
                    $categories = get_terms( array(
                        'taxonomy'   => 'product_cat',
                        'hide_empty' => true,
                    ) );
                    
                    if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
                        ?>
                        <select name="product_cat" class="category-select" onchange="if(this.value) window.location.href='<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>?product_cat=' + this.value;">
                            <option value=""><?php esc_html_e( 'All Categories', 'mytheme' ); ?></option>
                            <?php foreach ( $categories as $category ) : ?>
                                <option value="<?php echo esc_attr( $category->slug ); ?>" <?php selected( $current_cat, $category->slug ); ?>>
                                    <?php echo esc_html( $category->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php
                    }
                    ?>
                </div>

                <!-- Clear Filters -->
                <?php if ( isset($_GET['product_cat']) || isset($_GET['s']) ) : ?>
                    <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) ); ?>" class="clear-filters-btn">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                            <path d="M12 4L4 12M4 4l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                        Clear
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
    <?php
}




/**
 * Remove "Search results:" header from search page
 */
add_filter( 'woocommerce_page_title', 'theme_remove_search_results_title', 10, 1 );
function theme_remove_search_results_title( $page_title ) {
    // Remove "Search results:" title when on search page
    if ( is_search() && get_query_var( 's' ) ) {
        return '';
    }
    return $page_title;
}

/**
 * Alternatively, customize the search results title
 */
add_filter( 'get_the_archive_title', 'theme_customize_search_title' );
function theme_customize_search_title( $title ) {
    if ( is_search() ) {
        $title = sprintf( __( 'Showing results for: "%s"', 'mytheme' ), get_search_query() );
    }
    return $title;
}


/**
 * Replace "Add to Cart" button with cart icon on shop page
 */
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

add_action( 'woocommerce_after_shop_loop_item', 'theme_custom_cart_icon_button', 10 );

function theme_custom_cart_icon_button() {
    global $product;
    
    $product_id = $product->get_id();
    $product_type = $product->get_type();
    
    // Get the add to cart URL
    $cart_url = $product->add_to_cart_url();
    
    // Different behavior for simple vs variable products
    if ( $product_type === 'simple' ) {
        // Simple product - add to cart directly with AJAX
        echo sprintf(
            '<a href="%s" data-quantity="1" class="product-cart-icon ajax_add_to_cart" data-product_id="%s" data-product_sku="%s" aria-label="%s" rel="nofollow">
                🛒
            </a>',
            esc_url( $cart_url ),
            esc_attr( $product_id ),
            esc_attr( $product->get_sku() ),
            esc_attr__( 'Add to cart', 'mytheme' )
        );
    } else {
        // Variable product - link to product page
        echo sprintf(
            '<a href="%s" class="product-cart-icon" aria-label="%s">
                🛒
            </a>',
            esc_url( get_permalink( $product_id ) ),
            esc_attr__( 'Select options', 'mytheme' )
        );
    }
}

/**
 * Variation Swatches Data
 * 
 */
function add_variation_swatch_color_data() {
    
    $color_map = array(
        'red' => '#ff0000',
        'blue' => '#0000ff',
        'green' => '#00ff00',
        'black' => '#000000',
        'white' => '#ffffff',
        'yellow' => '#ffff00',
        'pink' => '#ff69b4',
        'purple' => '#800080',
        'orange' => '#ffa500',
        'brown' => '#8b4513',
        'gray' => '#808080',
        'grey' => '#808080',
        'navy' => '#000080',
        'beige' => '#f5f5dc',
        'cream' => '#fffdd0',
        'gold' => '#ffd700',
        'silver' => '#c0c0c0',
        'maroon' => '#800000',
        'olive' => '#808000',
        'lime' => '#00ff00',
        'teal' => '#008080',
        'aqua' => '#00ffff',
        'cyan' => '#00ffff',
        'magenta' => '#ff00ff',
        'coral' => '#ff7f50',
        'khaki' => '#f0e68c',
        'lavender' => '#e6e6fa',
    );
    
    wp_localize_script( 'variation-swatches', 'variationSwatchesData', array(
        'colorMap' => $color_map,
    ));
}
add_action( 'wp_enqueue_scripts', 'add_variation_swatch_color_data' );