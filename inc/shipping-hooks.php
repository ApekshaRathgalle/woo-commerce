<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Custom Location-Based Shipping Rates
 * Based on customer's billing country
 */

/**
 * Register custom shipping method
 */
add_action( 'woocommerce_shipping_init', 'theme_custom_shipping_method_init' );
function theme_custom_shipping_method_init() {
    
    if ( ! class_exists( 'WC_Theme_Location_Shipping' ) ) {
        
    //this class extends the WC_Shipping_Method class provided by WooCommerce is inheritance
        class WC_Theme_Location_Shipping extends WC_Shipping_Method {
            
            /**
             * Constructor
             */
            public function __construct( $instance_id = 0 ) {
                $this->id                 = 'theme_location_shipping';
                $this->instance_id        = absint( $instance_id );
                $this->method_title       = __( 'Location-Based Shipping', 'mytheme' );
                $this->method_description = __( 'Custom shipping rates based on customer location', 'mytheme' );
                $this->supports           = array(
                    'shipping-zones',
                    'instance-settings',
                );
                
                $this->init();
            }
            
            /**
             * Initialize settings
             */
            function init() {
                $this->init_form_fields();
                $this->init_settings();
                
                $this->enabled = $this->get_option( 'enabled' );
                $this->title   = $this->get_option( 'title' );
                
                add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
            }
            
            /**
             * Define settings form fields
             */
            function init_form_fields() {
                $this->instance_form_fields = array(
                    'enabled' => array(
                        'title'   => __( 'Enable/Disable', 'mytheme' ),
                        'type'    => 'checkbox',
                        'label'   => __( 'Enable this shipping method', 'mytheme' ),
                        'default' => 'yes',
                    ),
                    'title' => array(
                        'title'       => __( 'Method Title', 'mytheme' ),
                        'type'        => 'text',
                        'description' => __( 'This controls the title which the user sees during checkout.', 'mytheme' ),
                        'default'     => __( 'Location-Based Shipping', 'mytheme' ),
                        'desc_tip'    => true,
                    ),
                    'sri_lanka_cost' => array(
                        'title'       => __( 'Sri Lanka Cost (LKR)', 'mytheme' ),
                        'type'        => 'number',
                        'description' => __( 'Shipping cost for Sri Lanka', 'mytheme' ),
                        'default'     => '500',
                        'desc_tip'    => true,
                        'custom_attributes' => array(
                            'min'  => '0',
                            'step' => '1',
                        ),
                    ),
                    'asia_cost' => array(
                        'title'       => __( 'Asia Cost (LKR)', 'mytheme' ),
                        'type'        => 'number',
                        'description' => __( 'Shipping cost for Asian countries (excluding Sri Lanka)', 'mytheme' ),
                        'default'     => '1500',
                        'desc_tip'    => true,
                        'custom_attributes' => array(
                            'min'  => '0',
                            'step' => '1',
                        ),
                    ),
                    'other_cost' => array(
                        'title'       => __( 'Other Countries Cost (LKR)', 'mytheme' ),
                        'type'        => 'number',
                        'description' => __( 'Shipping cost for other countries', 'mytheme' ),
                        'default'     => '3000',
                        'desc_tip'    => true,
                        'custom_attributes' => array(
                            'min'  => '0',
                            'step' => '1',
                        ),
                    ),
                );
            }
            
            /**
             * Calculate shipping cost
             */
            public function calculate_shipping( $package = array() ) {
                
                // Get customer's country
                $country = $package['destination']['country'];
                
                error_log( '=== Calculating Shipping for Country: ' . $country . ' ===' );
                
                // Get configured rates
                $sri_lanka_cost = $this->get_option( 'sri_lanka_cost', 500 );
                $asia_cost      = $this->get_option( 'asia_cost', 1500 );
                $other_cost     = $this->get_option( 'other_cost', 3000 );
                
                // Asian countries (excluding Sri Lanka)
                $asian_countries = array(
                    'AF', 'AM', 'AZ', 'BH', 'BD', 'BT', 'BN', 'KH', 'CN', 
                    'GE', 'IN', 'ID', 'IR', 'IQ', 'IL', 'JP', 'JO', 'KZ', 
                    'KW', 'KG', 'LA', 'MY', 'MV', 'MN', 'MM', 'NP', 'KP', 
                    'OM', 'PK', 'PS', 'PH', 'QA', 'SA', 'SG', 'KR', 'SY', 
                    'TJ', 'TH', 'TR', 'TM', 'AE', 'UZ', 'VN', 'YE'
                );
                
                // Determine shipping cost based on location
                if ( $country === 'LK' ) {
                    $cost = $sri_lanka_cost;
                    $label = __( 'Sri Lanka Shipping', 'mytheme' );
                } elseif ( in_array( $country, $asian_countries ) ) {
                    $cost = $asia_cost;
                    $label = __( 'Asia Shipping', 'mytheme' );
                } else {
                    $cost = $other_cost;
                    $label = __( 'International Shipping', 'mytheme' );
                }
                
                error_log( 'Shipping Cost: ' . $cost . ' LKR' );
                
                // Add shipping rate
                $rate = array(
                    'id'    => $this->get_rate_id(),
                    'label' => $label,
                    'cost'  => $cost,
                    'taxes' => false,
                );
                
                $this->add_rate( $rate );
            }
        }
    }
}

/**
 * Add custom shipping method to WooCommerce
 */
add_filter( 'woocommerce_shipping_methods', 'theme_add_location_shipping_method' );
function theme_add_location_shipping_method( $methods ) {
    $methods['theme_location_shipping'] = 'WC_Theme_Location_Shipping';
    return $methods;
}

/**
 * Display shipping information on checkout page
 */
add_action( 'woocommerce_review_order_before_shipping', 'theme_display_shipping_info' );
function theme_display_shipping_info() {
    $country = WC()->customer->get_shipping_country();
    
    if ( empty( $country ) ) {
        $country = WC()->customer->get_billing_country();
    }
    
    if ( $country ) {
        $country_name = WC()->countries->countries[ $country ];
        echo '<tr class="shipping-info">';
        echo '<th>' . __( 'Shipping To:', 'mytheme' ) . '</th>';
        echo '<td><strong>' . esc_html( $country_name ) . '</strong></td>';
        echo '</tr>';
    }
}