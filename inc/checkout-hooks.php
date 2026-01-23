<?php
// prevent direct access
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 1️⃣ Add custom checkout fields (Business Type + VAT)
 */
add_filter( 'woocommerce_checkout_fields', 'theme_add_checkout_fields', 20 );
function theme_add_checkout_fields( $fields ) {
    
    
    // Business Type Field - Place after email field
    $fields['billing']['billing_business_type'] = array(
        'type'        => 'select',
        'label'       => __('Business Type', 'mytheme'),
        'options'     => array(
            ''           => __('Select Type', 'mytheme'),
            'individual' => __('Individual', 'mytheme'),
            'company'    => __('Company', 'mytheme'),
        ),
        'required'    => true,
        'class'       => array('form-row-wide', 'update_totals_on_change'),
        'clear'       => true,
        'priority'    => 25,
    );

    // VAT Number Field 
    $fields['billing']['billing_vat_number'] = array(
        'type'        => 'text',
        'label'       => __('VAT Number', 'mytheme'),
        'placeholder' => __('Enter VAT Number', 'mytheme'),
        'required'    => false,
        'class'       => array('form-row-wide', 'vat-number-field'),
        'clear'       => true,
        'priority'    => 26,
    );

    error_log('Custom fields added successfully');
    error_log('Billing fields count: ' . count($fields['billing']));
    error_log('Billing field keys: ' . implode(', ', array_keys($fields['billing'])));
    
    return $fields;
}

/**
 * 2️⃣ Conditional Validation for VAT Number
 */
add_action( 'woocommerce_checkout_process', 'theme_validate_checkout_fields' );
function theme_validate_checkout_fields() {
    error_log('=== Validation Hook Called ===');
    
    $business_type = isset($_POST['billing_business_type']) ? sanitize_text_field($_POST['billing_business_type']) : '';
    $vat_number    = isset($_POST['billing_vat_number']) ? sanitize_text_field($_POST['billing_vat_number']) : '';
    $country       = isset($_POST['billing_country']) ? sanitize_text_field($_POST['billing_country']) : '';
    
    error_log('Business Type: ' . $business_type);
    error_log('VAT Number: ' . $vat_number);
    error_log('Country: ' . $country);
    
    // Validate Business Type is selected
    if ( empty($business_type) ) {
        wc_add_notice( __( 'Please select a Business Type.', 'mytheme' ), 'error' );
        error_log('Validation Error: Business Type not selected');
    }
    
    // Validate VAT Number when Country is selected AND Business Type is Company
    if ( ! empty($country) && $business_type === 'company' ) {
        if ( empty($vat_number) ) {
            wc_add_notice( __( 'VAT Number is required for companies.', 'mytheme' ), 'error' );
            error_log('Validation Error: VAT Number required for company');
        } elseif ( strlen($vat_number) < 8 ) { //strlen = string length gets number of characters
            wc_add_notice( __( 'Please enter a valid VAT Number (minimum 8 characters).', 'mytheme' ), 'error' );
            error_log('Validation Error: VAT Number too short');
        }
    }
    
    error_log('=== Validation Complete ===');
}

/**
 * 3️⃣ Save custom fields to order meta
 */
add_action( 'woocommerce_checkout_update_order_meta', 'theme_save_checkout_fields' );
function theme_save_checkout_fields( $order_id ) {
    error_log('=== Saving Custom Fields for Order #' . $order_id . ' ===');
    
    if ( ! empty( $_POST['billing_business_type'] ) ) {
        $business_type = sanitize_text_field( $_POST['billing_business_type'] );
        update_post_meta( $order_id, '_billing_business_type', $business_type );
        error_log('Saved Business Type: ' . $business_type);
    }
    
    if ( ! empty( $_POST['billing_vat_number'] ) ) {
        $vat_number = sanitize_text_field( $_POST['billing_vat_number'] );
        update_post_meta( $order_id, '_billing_vat_number', $vat_number );
        error_log('Saved VAT Number: ' . $vat_number);
    }
    
    error_log('=== Save Complete ===');
}

/**
 * 4️⃣ Display fields in Admin Order screen
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'theme_display_checkout_fields_in_admin', 10, 1 );
function theme_display_checkout_fields_in_admin( $order ){ //$order is a instance of WC_Order class
    $business_type = get_post_meta( $order->get_id(), '_billing_business_type', true );
    $vat_number    = get_post_meta( $order->get_id(), '_billing_vat_number', true );

    if ( $business_type ) {
        echo '<p><strong>'.__('Business Type', 'mytheme').':</strong> ' . esc_html( ucfirst($business_type) ) . '</p>';
    }
    
    if ( $vat_number ) {
        echo '<p><strong>'.__('VAT Number', 'mytheme').':</strong> ' . esc_html( $vat_number ) . '</p>';
    }
}

/**
 * 5️⃣ Display fields in Emails
 */
add_filter( 'woocommerce_email_order_meta_fields', 'theme_add_checkout_fields_to_emails', 10, 3 );
function theme_add_checkout_fields_to_emails( $fields, $sent_to_admin, $order ) {
    $business_type = get_post_meta( $order->get_id(), '_billing_business_type', true );
    $vat_number    = get_post_meta( $order->get_id(), '_billing_vat_number', true );
    
    if ( $business_type ) {
        $fields['billing_business_type'] = array(
            'label' => __('Business Type', 'mytheme'),
            'value' => ucfirst($business_type),
        );
    }
    
    if ( $vat_number ) {
        $fields['billing_vat_number'] = array(
            'label' => __('VAT Number', 'mytheme'),
            'value' => $vat_number,
        );
    }
    
    return $fields;
}

/**
 * 7️⃣ Remove default WooCommerce review order table
 */
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 );
remove_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );

/**
 * 8️⃣ Add our custom order review
 */
add_action( 'woocommerce_checkout_order_review', 'custom_checkout_order_review', 10 );
function custom_checkout_order_review() {
    ?>
    <div class="checkout-review-order-container">
        <h3 class="checkout-review-heading"><?php esc_html_e( 'Your Order', 'mytheme' ); ?></h3>
        
        <!-- Custom Cart Items Display -->
        <div class="checkout-cart-items">
            <?php
            do_action( 'woocommerce_review_order_before_cart_contents' );

            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

                if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                    ?>
                    <div class="checkout-cart-item">
                        <div class="item-image">
                            <?php
                            $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                            echo $thumbnail;
                            ?>
                            <span class="item-quantity"><?php echo esc_html( $cart_item['quantity'] ); ?></span>
                        </div>
                        
                        <div class="item-details">
                            <h4 class="item-name">
                                <?php echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) ); ?>
                            </h4>
                            
                            <?php
                            // Variation details
                            if ( $_product->is_type( 'variation' ) ) {
                                echo '<div class="item-variation">';
                                echo wc_get_formatted_cart_item_data( $cart_item );
                                echo '</div>';
                            }
                            
                            // Product meta (badge)
                            $badge = get_post_meta( $_product->get_id(), '_product_badge', true );
                            if ( $badge ) {
                                echo '<span class="item-badge badge-' . esc_attr( $badge ) . '">' . esc_html( ucfirst( $badge ) ) . '</span>';
                            }
                            ?>
                            
                            <div class="item-price-qty">
                                <span class="qty-label"><?php esc_html_e( 'Qty:', 'mytheme' ); ?></span>
                                <span class="qty-value"><?php echo esc_html( $cart_item['quantity'] ); ?></span>
                                <span class="price-separator">×</span>
                                <span class="unit-price"><?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); ?></span>
                            </div>
                        </div>
                        
                        <div class="item-total">
                            <?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
                        </div>
                    </div>
                    <?php
                }
            }

            do_action( 'woocommerce_review_order_after_cart_contents' );
            ?>
        </div>

        <!-- Order Totals Table -->
        <table class="shop_table woocommerce-checkout-review-order-table">
            <tfoot>
                <tr class="cart-subtotal">
                    <th><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
                    <td><?php wc_cart_totals_subtotal_html(); ?></td>
                </tr>

                <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
                    <tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                        <th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
                        <td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
                    <?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
                    <?php wc_cart_totals_shipping_html(); ?>
                    <?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
                <?php endif; ?>

                <?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
                    <tr class="fee">
                        <th><?php echo esc_html( $fee->name ); ?></th>
                        <td><?php wc_cart_totals_fee_html( $fee ); ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
                    <?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
                        <?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
                            <tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                                <th><?php echo esc_html( $tax->label ); ?></th>
                                <td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr class="tax-total">
                            <th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
                            <td><?php wc_cart_totals_taxes_total_html(); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endif; ?>

                <?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

                <tr class="order-total">
                    <th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
                    <td><?php wc_cart_totals_order_total_html(); ?></td>
                </tr>

                <?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
            </tfoot>
        </table>
    </div>
    <?php
}

/**
 * 9️⃣ Re-add payment section after our custom review
 */
add_action( 'woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20 );