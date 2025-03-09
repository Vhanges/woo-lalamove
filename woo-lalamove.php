<?php 
/**
 * Plugin Name: WooCommerce Lalamove Extension
 * Version: 1.0
 * Author: Angelo Sevhen
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
require_once plugin_dir_path(__FILE__) . 'includes/utility-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/class_lalamove_shipping_method.php';
require_once plugin_dir_path(__FILE__) . 'cors.php';

use Sevhen\WooLalamove\Class_Lalamove_Settings;
use Sevhen\WooLalamove\Class_Lalamove_Endpoints;
use Sevhen\WooLalamove\Class_Lalamove_Api;
use Sevhen\WooLalamove\Class_Lalamove_Shortcode;

new Class_Lalamove_Settings();
new Class_Lalamove_Endpoints();
new Class_Lalamove_Api();
new Class_Lalamove_Shortcode();




if ( ! class_exists('Woo_Lalamove') ) {
    class Woo_Lalamove {

        public function __construct() {
            if(lalamove_check_is_woocommerce_active()){
                // Enable CORS for all requests
                add_action('rest_api_init', function () {
                    enableCORS();
                });
                add_action('admin_enqueue_scripts', [$this, 'enqueue_vue_assets']);
                add_action('admin_menu', [$this, 'woo_lalamove_add_admin_page']);


                add_action('wp_enqueue_scripts', [$this, 'enqueue_custom_plugin_scripts']);


                add_action('woocommerce_shipping_init','your_shipping_method_init');
                add_filter('woocommerce_shipping_methods', [$this, 'add_your_shipping_method']);

                
            }   
        }
        


        public function add_your_shipping_method($methods) {
            $methods['your_shipping_method'] = 'Class_Lalamove_Shipping_Method';
            return $methods;
        }
        
        public function enqueue_vue_assets($hook) {
            if ($hook !== 'toplevel_page_woo-lalamove') {
                return;
            }

            wp_enqueue_script(
                'woo-lalamove',
                plugin_dir_url(__FILE__) . 'assets/js/dist/bundle.js',
                [],
                filemtime(plugin_dir_path(__FILE__) . 'assets/js/dist/bundle.js'),
                true
            );

            // Enqueue Material Symbols font
            wp_enqueue_style('material-symbols', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined');

            // Enqueue MapTiler SDK
            wp_enqueue_script(
                'maptiler-sdk',
                'https://cdn.maptiler.com/maptiler-sdk-js/v3.0.0/maptiler-sdk.umd.min.js',
                [],
                '3.0.0',
                true
            );

            // Enqueue MapTiler SDK CSS
            wp_enqueue_style(
                'maptiler-sdk-css',
                'https://cdn.maptiler.com/maptiler-sdk-js/v3.0.0/maptiler-sdk.css',
                [],
                '3.0.0'
            );

            // Enqueue Leaflet plugin for MapTiler SDK Layers
            wp_enqueue_script(
                'leaflet-maptilersdk',
                'https://cdn.maptiler.com/leaflet-maptilersdk/v3.0.0/leaflet-maptilersdk.js',
                ['maptiler-sdk'],
                '3.0.0',
                true
            );

            // Critical security nonce - MUST stay in
            wp_localize_script('woo-lalamove', 'wooLalamoveData', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('woo_lalamove_nonce')
            ]);

            // Enqueue CSS only on this admin page
            wp_enqueue_style(
                'woo-lalamove-styles',
                plugin_dir_url(__FILE__) . 'assets/css/admin.css',
                [],
                filemtime(plugin_dir_path(__FILE__) . 'assets/css/admin.css')
            );
        }

        public function woo_lalamove_add_admin_page() {
            add_menu_page(
                'Lalamove Settings',
                'Lalamove',
                'manage_options',
                'woo-lalamove',
                [$this, 'woo_lalamove_render_admin_page'],
                'dashicons-admin-site',
                25
            );
        }

        public function woo_lalamove_render_admin_page() {
            ?>

            <div id="lalamove-app">
                <!-- Content goes here -->
            </div>
            <!-- Add this hidden nonce field -->
            <input type="hidden" 
                id="woo_lalamove_form_nonce" 
                value="<?php echo wp_create_nonce('woo_lalamove_form_action'); ?>">
            <?php
        }



        
        function sevhen_fetch_lalamove_quotation() {
            check_ajax_referer('sevhen_lalamove_nonce', 'nonce');
        
            // Sample API Call Response Price (Replace this with your API logic)
            $quotation_price = $_POST['price'] ?? 100;
        
            // Save Price in WooCommerce Session
            WC()->session->set('sevhen_lalamove_quotation_price', $quotation_price);
        
            wp_send_json_success(['price' => $quotation_price]);
        }

        public function enqueue_custom_plugin_scripts() {
            if (is_checkout()) {
                wp_enqueue_script('jquery'); // Enqueue jQuery
                // Enqueue your custom script that depends on jQuery
                wp_enqueue_script('custom-plugin-script', plugin_dir_url(__FILE__) . 'assets/js/lalamove-modal.js', array('jquery'), null, true);
            
                // Enqueue Bootstrap JS
                wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), null, true);
            
                // Enqueue Bootstrap CSS
                wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css', array(), null);
        
                // Enqueue Bootstrap Icons CSS
                wp_enqueue_style('bootstrap-icons', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css', array(), null);
        
                // Enqueue Moment.js
                wp_enqueue_script('moment-js', 'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js', array('jquery'), null, true);
        
                // Enqueue Date Range Picker JS
                wp_enqueue_script('daterangepicker-js', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js', array('jquery', 'moment-js'), null, true);
        
                // Enqueue Date Range Picker CSS
                wp_enqueue_style('daterangepicker-css', 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css', array(), null);
            
                // Localize script to pass AJAX URL and nonce to JavaScript
                wp_localize_script('custom-plugin-script', 'lalamoveAjax', array(
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('load_modal_content_nonce')
                ));
            } else {
                // Dequeue Bootstrap JS and CSS if not on the checkout page
                wp_dequeue_script('bootstrap-js');
                wp_dequeue_style('bootstrap-css');
                wp_dequeue_style('bootstrap-icons');
                wp_dequeue_script('moment-js');
                wp_dequeue_script('daterangepicker-js');
                wp_dequeue_style('daterangepicker-css');
            }
        }
        
        
    }
    
    new Woo_Lalamove();

    // Fetch checkout product data for Lalamove modal order detail reference
    function get_checkout_product_data() {
        // Get cart items
        $cart_items = WC()->cart->get_cart();
        $products = array();

        foreach ( $cart_items as $cart_item_key => $cart_item ) {
            $product = $cart_item['data'];
            $products[] = array(
                'name' => $product->get_name(),
                'quantity' => $cart_item['quantity'],
                'weight' => $product->get_weight(),
            );
        }

        // Get user address
        if ( is_user_logged_in() ) {
            $user_id = get_current_user_id();
            $shipping_address = array(
                'address_1' => get_user_meta( $user_id, 'shipping_address_1', true ),
                'address_2' => get_user_meta( $user_id, 'shipping_address_2', true ),
                'city'      => get_user_meta( $user_id, 'shipping_city', true ),
                'state'     => get_user_meta( $user_id, 'shipping_state', true ),
                'postcode'  => get_user_meta( $user_id, 'shipping_postcode', true ),
                'country'   => get_user_meta( $user_id, 'shipping_country', true ),
            );
        } else {
            $shipping_address = array(
                'address_1' => WC()->session->get( 'shipping_address_1' ),
                'address_2' => WC()->session->get( 'shipping_address_2' ),
                'city'      => WC()->session->get( 'shipping_city' ),
                'state'     => WC()->session->get( 'shipping_state' ),
                'postcode'  => WC()->session->get( 'shipping_postcode' ),
                'country'   => WC()->session->get( 'shipping_country' ),
            );
        }

        // Get store address and contact
        $store_address = array(
            'address_1' => get_option( 'woocommerce_store_address' ),
            'address_2' => get_option( 'woocommerce_store_address_2' ),
            'city'      => get_option( 'woocommerce_store_city' ),
            'state'     => get_option( 'woocommerce_store_state' ),
            'postcode'  => get_option( 'woocommerce_store_postcode' ),
            'country'   => get_option( 'woocommerce_default_country' ),
        );

        $store_contact = array(
            'email' => get_option( 'woocommerce_email_from_address' ),
            'phone' => get_option( 'woocommerce_store_phone' ), // Custom field if defined in Woo settings
        );

        // Add the shipping address and store details to the response
        $response = array(
            'products' => $products,
            'shipping_address' => $shipping_address,
            'store_address' => $store_address,
            'store_contact' => $store_contact,
        );

        wp_send_json_success( $response );
    }
    add_action( 'wp_ajax_get_checkout_product_data', 'get_checkout_product_data' );
    add_action( 'wp_ajax_nopriv_get_checkout_product_data', 'get_checkout_product_data' );

    // Enqueue custom scripts for AJAX
    function enqueue_custom_scripts() {
        wp_enqueue_script('custom-plugin-script', get_template_directory_uri() . '/assets/js/custom.js', array('jquery'), null, true);

        // Localize script to pass AJAX URL and nonce to JavaScript
        wp_localize_script('custom-plugin-script', 'pluginAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('custom_plugin_nonce'),
        ));
    }
    add_action( 'wp_enqueue_scripts', 'enqueue_custom_scripts' );

    // Update shipping rate dynamically
    function update_shipping_rate() {
        if (isset($_POST['shipping_cost'])) {
            $shipping_cost = sanitize_text_field($_POST['shipping_cost']);

            // Save the shipping cost in a WooCommerce session
            WC()->session->set('shipment_cost', floatval($shipping_cost));
            error_log('Shipping cost session value updated: ' . WC()->session->get('shipment_cost'));

            wp_send_json_success(array('message' => 'Shipping rate updated.'));
        } else {
            wp_send_json_error(array('message' => 'Shipping cost is missing.'));
        }
        wp_die();
    }
    add_action('wp_ajax_update_shipping_rate', 'update_shipping_rate');
    add_action('wp_ajax_nopriv_update_shipping_rate', 'update_shipping_rate');

    // Refresh cached shipping methods
    add_filter( 'woocommerce_package_rates', 'custom_modify_shipping_costs', 10, 2 );
    function custom_modify_shipping_costs( $rates, $package ) {
        $shipment_cost = WC()->session->get('shipment_cost');
        if ( !empty( $shipment_cost ) ) {
            foreach ( $rates as $rate_key => $rate ) {
                if ( 'your_shipping_method' === $rate->method_id ) {
                    $rates[ $rate_key ]->cost = $shipment_cost;
                    // Optionally, update taxes if needed.
                }
            }
        }
        return $rates;
    }
    

    // Reset shipping session variable after checkout
    add_action('wp_footer', 'reset_wc_session_variable');
    function reset_wc_session_variable() {
        if (is_checkout() && WC()->session->get('shipment_cost')) {
            error_log('Resetting shipping cost session value...');
            WC()->session->__unset('shipment_cost');
        }
    }



    
    



}
