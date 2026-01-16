<?php
// prevent direct access
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * 1️⃣ Add custom checkout fields (Business Type + VAT)
 */
add_filter( 'woocommerce_checkout_fields', 'theme_add_checkout_fields', 20 );
function theme_add_checkout_fields( $fields ) {
    error_log('=== Custom Checkout Fields: Function Called ===');
    error_log('Current URL: ' . $_SERVER['REQUEST_URI']);
    error_log('Is checkout: ' . (is_checkout() ? 'YES' : 'NO'));
    
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
        } elseif ( strlen($vat_number) < 8 ) {
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
function theme_display_checkout_fields_in_admin( $order ){
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
 * 6️⃣ Detect if page has checkout shortcode
 */
function has_checkout_shortcode() {
    global $post;
    
    if ( is_checkout() ) {
        return true;
    }
    
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'woocommerce_checkout' ) ) {
        return true;
    }
    
    return false;
}

