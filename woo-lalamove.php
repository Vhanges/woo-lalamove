<?php 
/**
 * Plugin Name: WooCommerce Lalamove Extension
 * Text Domain: woocommerce-lalamove-extension
 * Description: A WooCommerce extension that integrates Lalamove delivery services.
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

New Class_Lalamove_Settings();
New Class_Lalamove_Endpoints();
New Class_Lalamove_Api();
New Class_Lalamove_Shortcode();




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

                add_filter( 'woocommerce_my_account_my_orders_actions', [$this ,'my_custom_order_button'], 10, 2 );

                register_activation_hook( __FILE__, callback: [$this, 'my_plugin_create_custom_table']);

                add_filter( 'woocommerce_billing_fields', [$this, 'make_phone_field_required'], 10, 2 );
           

            }   
        }
        function make_phone_field_required( $fields ) {
            $fields['billing_phone']['required'] = true; // Set to true to make it mandatory
            return $fields;
        }

        function my_plugin_create_custom_table() {
            global $wpdb;
            $table_name = $wpdb->prefix . 'wc_lalamove_orders';
            $charset_collate = $wpdb->get_charset_collate();
        
            // Check if the table already exists
            if($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) {
                $sql = "CREATE TABLE {$wpdb->prefix}wc_lalamove_orders (
                    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    lalamove_order_id VARCHAR(100) NOT NULL,
                    wc_order_id INT UNSIGNED NOT NULL,  
                    lalamove_order_status VARCHAR(100) NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                    PRIMARY KEY (id),
                    UNIQUE KEY unique_lalamove_order (lalamove_order_id)
                ) {$charset_collate};
                ";
        
                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                $result = dbDelta( $sql );

                // If table creation returns any SQL statements, set a transient for admin notice
                if ( ! empty( $result ) ) {
                    set_transient( 'wc_lalamove_table_created', true, 5 );
                }
            }

        }
        
        
        function my_custom_order_button( $actions, $order ) {
            $order_id = $order->get_id();
            // Build URL to a custom order details page, e.g. with the slug "order-details"
            $url = add_query_arg( 'order_id', $order_id, site_url( '/delivery-details/' ) );
            
            $actions['custom_button'] = array(
                'url'  => $url,
                'name' => __( 'Track Order', 'woocommerce-lalamove-extension' ),
            );
            return $actions;
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
                wp_enqueue_script('custom-plugin-script', plugin_dir_url(__FILE__) . 'assets/js/lalamove-modal.js', array('jquery'), 1.0, true);
            
                // Enqueue Bootstrap JS
                wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js', array('jquery'), null, true);
            
                // Enqueue Bootstrap CSS
                wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css', array(), null);
        
                // Enqueue Bootstrap Icons CSS
                wp_enqueue_style('bootstrap-icons', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css', array(), null);
    
                // Enqueue Leaflet CSS
                wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), null);

                // Enqueue Leaflet JS
                wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), null, true);
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
                wp_dequeue_style('leaflet-js');
                wp_dequeue_style('leaflet-css');
            }
        }
        
        
    }
    
    new Woo_Lalamove();
    
    // Fetch checkout product data for Lalamove modal order detail reference
    add_action( 'wp_ajax_get_checkout_product_data', 'get_checkout_product_data' );
    add_action( 'wp_ajax_nopriv_get_checkout_product_data', 'get_checkout_product_data' );
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

    // Enqueue custom scripts for AJAX
    add_action( 'wp_enqueue_scripts', 'enqueue_custom_scripts' );
    function enqueue_custom_scripts() {

        // Localize script to pass AJAX URL and nonce to JavaScript
        wp_localize_script('custom-plugin-script', 'pluginAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('custom_plugin_nonce'),
        ));

    }

    // Update shipping rate dynamically
    add_action('wp_ajax_update_shipping_rate', 'update_shipping_rate');
    add_action('wp_ajax_nopriv_update_shipping_rate', 'update_shipping_rate');
    function update_shipping_rate() {
            
        if (isset($_POST['shipping_cost'])) {
            $shipping_cost = sanitize_text_field($_POST['shipping_cost']);

            // Save the shipping cost in a WooCommerce session
            WC()->session->set('shipment_cost', floatval($shipping_cost));
            error_log('Shipping cost session value updated: ' . WC()->session->get('shipment_cost'));
            wp_send_json_success(array('message' => 'Shipping rate updated.'));
        } else {
            error_log('It did not work');
            wp_send_json_error(array('message' => 'Shipping cost is missing.'));
        }
    }

    add_action('woocommerce_blocks_loaded', 'register_custom_cart_update_callback');

    function register_custom_cart_update_callback() {
        woocommerce_store_api_register_update_callback(
            array(
                'namespace' => 'your_custom_namespace',
                'callback'  => 'handle_custom_cart_update',
            )
        );
    }

    function handle_custom_cart_update( $data ) {
        error_log('BOOOOOOOOOOOM');
    }


    
    add_filter('woocommerce_cart_shipping_packages', 'disable_shipping_rate_cache', 100);

    function disable_shipping_rate_cache($packages) {
        foreach ($packages as &$package) {
            // Invalidate the cache by assigning a unique value
            $package['rate_cache'] = wp_rand(); // Generates a random value for every package
        }

        return $packages;
    }

    add_action( 'admin_notices', 'wc_lalamove_admin_notice' );
    function wc_lalamove_admin_notice() {
        if ( get_transient( 'wc_lalamove_table_created' ) ) {
            echo '<div class="notice notice-success is-dismissible">
                    <p>WooCommerce Lalamove table was successfully created.</p>
                </div>';
            delete_transient( 'wc_lalamove_table_created' );
        }
    }

    add_action( 'woocommerce_thankyou', 'set_lalamove_order', 10, 1 );
    function set_lalamove_order( $order_id ) {
        // Custom code to run after the order is fully processed.

        WC()->session->__unset( 'shipment_cost' );

        global $wpdb;
        $table_name = $wpdb->prefix . 'wc_lalamove_orders';

        // Get the data from WooCommerce session
        $quotationID = WC()->session->get('quotationID');
        echo '<pre>Quotation ID: ' . $quotationID . '</pre>';

        $stopId0 = WC()->session->get('stopId0');
        echo '<pre>Stop ID 0: ' . $stopId0 . '</pre>';

        $stopId1 = WC()->session->get('stopId1');
        echo '<pre>Stop ID 1: ' . $stopId1 . '</pre>';

        $customerFName = WC()->session->get('customerFName');
        echo '<pre>Customer First Name: ' . $customerFName . '</pre>';

        $customerLName = WC()->session->get('customerLName');
        echo '<pre>Customer Last Name: ' . $customerLName . '</pre>';

        $customerPhoneNo = WC()->session->get('customerPhoneNo');
        echo '<pre>Customer Phone Number: ' . $customerPhoneNo . '</pre>';

        $additionalNotes = WC()->session->get('additionalNotes');
        echo '<pre>Additional Notes: ' . $additionalNotes . '</pre>';

        $proofOfDelivery = WC()->session->get('proofOfDelivery');
        echo '<pre>Optimize Route: ' . $proofOfDelivery . '</pre>';

        $customerFullName = $customerFName. " " .$customerLName; 
        
        $lalamove_api = New Class_Lalamove_Api();
        
        $lalamove_orderId = $lalamove_api->place_order(
            $quotationID,
            $stopId0,
            $stopId1,
            'babowm',
            "+634315873",
            $customerFullName,
            "+6307457184",
            "UWOWERs",
            true
        );


        $lalamove_orderId = $lalamove_orderId['data']['orderId'];
        echo '<pre>Lalamove Order ID: ' . $lalamove_orderId . '</pre>';


        
      

        $data = array(
            'lalamove_order_id'    => $lalamove_orderId,     // For example, a string
            'wc_order_id'          => $order_id,          // An integer value
            'lalamove_order_status'=> 'pending',    // A string status
            'created_at'           => current_time( 'mysql' ) // Current timestamp in MySQL format
        );

        // Specify the data types for each field (optional but recommended)
        $format = array('%s', '%d', '%s', '%s');

        // Insert the data into the table
        // Check if the lalamove_order_id already exists
        $existing_order = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE lalamove_order_id = %s",
            $data['lalamove_order_id']
        ));

        if ( $existing_order == 0 ) {
            $inserted = $wpdb->insert( $table_name, $data, $format );

            if ( false === $inserted ) {
            // There was an error inserting the data
            error_log( 'Insert error: ' . $wpdb->last_error );
            } else {
            // The insert was successful, and $wpdb->insert_id contains the new record's ID.
            echo 'Record inserted with ID: ' . $wpdb->insert_id;
            }
        } else {
            // The lalamove_order_id already exists
            error_log( 'Lalamove order ID already exists: ' . $data['lalamove_order_id'] );
        }

        if ( false === $inserted ) {
            // There was an error inserting the data
            error_log( 'Insert error: ' . $wpdb->last_error );
        } else {
            // The insert was successful, and $wpdb->insert_id contains the new record's ID.
            echo 'Record inserted with ID: ' . $wpdb->insert_id;

        }

        
        // var_dump($lalamove_orderId);
        // var_dump($body);



        // print_r(json_encode($quotationBody));
        var_dump($lalamove_orderId, JSON_PRETTY_PRINT);

        // error_log('Quotation Body' . )
        error_log( 'Order completed: ' . $order_id );
    }


    add_action('wp_ajax_set_quotation_data_session', 'set_quotation_data_session');
    add_action('wp_ajax_nopriv_set_quotation_data_session', 'set_quotation_data_session');
    function set_quotation_data_session() {
        if (isset($_POST['quotationID'])) {
            $quotationID = sanitize_text_field($_POST['quotationID']);
            $stopId0 = sanitize_text_field($_POST['stopId0']);
            $stopId1 = sanitize_text_field($_POST['stopId1']);
            $customerFName = sanitize_text_field($_POST['customerFName']);
            $customerLName = sanitize_text_field($_POST['customerLName']);
            $customerPhoneNo = sanitize_text_field($_POST['customerPhoneNo']);
            $additionalNotes = sanitize_text_field($_POST['additionalNotes']);
            $proofOfDelivery = sanitize_text_field($_POST['proofOfDelivery']);

            // Save the data in WooCommerce session
            WC()->session->set('quotationID', $quotationID);
            WC()->session->set('stopId0', $stopId0);
            WC()->session->set('stopId1', $stopId1);
            WC()->session->set('customerFName', $customerFName);
            WC()->session->set('customerLName', $customerLName);
            WC()->session->set('customerPhoneNo', $customerPhoneNo);
            WC()->session->set('additionalNotes', $additionalNotes);
            WC()->session->set('proofOfDelivery', $proofOfDelivery);

            error_log('Quotation data session values updated: ' . print_r($_POST, true));


            error_log('Quotation body session value updated: ' . var_dump(WC()->session->get('quotationBody')));

            wp_send_json_success(array('message' => 'Quotation ID updated.'));
        } else {
            wp_send_json_error(array('message' => 'Quotation ID is missing.'));
        }
        wp_die();
    }


}
