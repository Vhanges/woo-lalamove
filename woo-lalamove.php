<?php

/**
 * Plugin Name: WooCommerce Lalamove Extension
 * Text Domain: woocommerce-lalamove-extension
 * Description: A WooCommerce extension that integrates Lalamove delivery services.
 * Version: 1.0
 * Author: Angelo Sevhen
 */

if (!defined('ABSPATH'))
    exit;

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




if (!class_exists('Woo_Lalamove')) {
    class Woo_Lalamove
    {

        public function __construct()
        {
            if (lalamove_check_is_woocommerce_active()) {
                // Enable CORS for all requests
                add_action('rest_api_init', function () {
                    enableCORS();
                });
                add_action('admin_enqueue_scripts', [$this, 'enqueue_vue_assets']);
                add_action('admin_menu', [$this, 'woo_lalamove_add_admin_page']);


                add_action('wp_enqueue_scripts', [$this, 'enqueue_custom_plugin_scripts']);


                add_action('woocommerce_shipping_init', 'your_shipping_method_init');
                add_filter('woocommerce_shipping_methods', [$this, 'add_your_shipping_method']);

                add_filter('woocommerce_my_account_my_orders_actions', [$this, 'my_custom_order_button'], 10, 2);

                register_activation_hook(__FILE__, callback: [$this, 'my_plugin_create_custom_table']);

                add_filter('woocommerce_billing_fields', [$this, 'make_phone_field_required'], 10, 2);

                add_action('woocommerce_blocks_loaded', 'register_custom_cart_update_callback');
            }
        }
        function make_phone_field_required($fields)
        {
            $fields['billing_phone']['required'] = true; // Set to true to make it mandatory
            return $fields;
        }

        function my_plugin_create_custom_table()
        {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();

            // Define table names with dynamic prefix
            $cost_details_table = "{$wpdb->prefix}wc_lalamove_cost_details";
            $status_table = "{$wpdb->prefix}wc_lalamove_status";
            $transaction_table = "{$wpdb->prefix}wc_lalamove_transaction";
            $orders_table = "{$wpdb->prefix}wc_lalamove_orders";

            // Check if tables already exist
            if (
                $wpdb->get_var("SHOW TABLES LIKE '$cost_details_table'") == $cost_details_table &&
                $wpdb->get_var("SHOW TABLES LIKE '$status_table'") == $status_table &&
                $wpdb->get_var("SHOW TABLES LIKE '$transaction_table'") == $transaction_table &&
                $wpdb->get_var("SHOW TABLES LIKE '$orders_table'") == $orders_table
            ) {
                return;
            }

            $sql_cost_details = "CREATE TABLE $cost_details_table (
                cost_details_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                currency CHAR(10) NOT NULL,
                base DOUBLE NOT NULL,
                extra_mileage DOUBLE,
                surcharge DOUBLE,
                total DOUBLE NOT NULL,
                priority_fee DOUBLE,
                PRIMARY KEY (cost_details_id)
            ) $charset_collate;";

            $sql_status = "CREATE TABLE $status_table (
                status_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                status_name VARCHAR(10) NOT NULL,
                description TEXT,
                PRIMARY KEY (status_id)
            ) $charset_collate;";

            $sql_transaction = "CREATE TABLE $transaction_table (
                transaction_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                cost_details_id BIGINT UNSIGNED NOT NULL,
                ordered_by VARCHAR(200) NOT NULL,
                service_type VARCHAR(200) NOT NULL,
                PRIMARY KEY (transaction_id),
                FOREIGN KEY (cost_details_id) REFERENCES $cost_details_table(cost_details_id)
            ) $charset_collate;";

            $sql_orders = "CREATE TABLE $orders_table (
                integration_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                transaction_id BIGINT UNSIGNED NOT NULL,
                wc_order_id BIGINT UNSIGNED,
                order_status_id INT UNSIGNED NOT NULL,
                lalamove_order_id BIGINT NOT NULL,
                ordered_on DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                scheduled_on DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                drop_off_location TEXT NOT NULL,
                PRIMARY KEY (integration_id),
                UNIQUE KEY unique_lalamove_order (lalamove_order_id),
                FOREIGN KEY (transaction_id) REFERENCES $transaction_table(transaction_id),
                FOREIGN KEY (wc_order_id) REFERENCES {$wpdb->prefix}posts(ID) ON DELETE SET NULL
            ) $charset_collate;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            dbDelta($sql_cost_details);
            dbDelta($sql_status);
            dbDelta($sql_transaction);
            dbDelta($sql_orders);

            $tables_created = [$cost_details_table, $status_table, $transaction_table, $orders_table];
            $success = array_filter($tables_created, function ($table) use ($wpdb) {
                return $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
            });

            if (count($success) === count($tables_created)) {
                set_transient('wc_lalamove_table_created', true, 5); // Notify admin
            } else {
                error_log('Some tables were not created successfully.');
            }
        }


        function my_custom_order_button($actions, $order)
        {
            $order_id = $order->get_id();
            // Build URL to a custom order details page, e.g. with the slug "order-details"
            $url = add_query_arg('order_id', $order_id, site_url('/delivery-details/'));

            $actions['custom_button'] = array(
                'url' => $url,
                'name' => __('Track Order', 'woocommerce-lalamove-extension'),
            );
            return $actions;
        }


        public function add_your_shipping_method($methods)
        {
            $methods['your_shipping_method'] = 'Class_Lalamove_Shipping_Method';
            return $methods;
        }

        public function enqueue_vue_assets($hook)
        {
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
                'nonce' => wp_create_nonce('woo_lalamove_nonce')
            ]);

            // Enqueue CSS only on this admin page
            wp_enqueue_style(
                'woo-lalamove-styles',
                plugin_dir_url(__FILE__) . 'assets/css/admin.css',
                [],
                filemtime(plugin_dir_path(__FILE__) . 'assets/css/admin.css')
            );
        }

        public function woo_lalamove_add_admin_page()
        {
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

        public function woo_lalamove_render_admin_page()
        {
            ?>

            <div id="lalamove-app">
                <!-- Content goes here -->
            </div>
            <!-- Add this hidden nonce field -->
            <input type="hidden" id="woo_lalamove_form_nonce" value="<?php echo wp_create_nonce('woo_lalamove_form_action'); ?>">
            <?php
        }




        function sevhen_fetch_lalamove_quotation()
        {
            check_ajax_referer('sevhen_lalamove_nonce', 'nonce');

            // Sample API Call Response Price (Replace this with your API logic)
            $quotation_price = $_POST['price'] ?? 100;

            // Save Price in WooCommerce Session
            WC()->session->set('sevhen_lalamove_quotation_price', $quotation_price);

            wp_send_json_success(['price' => $quotation_price]);
        }

        public function enqueue_custom_plugin_scripts()
        {
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
    add_action('wp_ajax_get_shipping_data', 'get_shipping_data');
    add_action('wp_ajax_nopriv_get_shipping_data', 'get_shipping_data');
    function get_shipping_data()
    {
        // Get cart items
        $cart_items = WC()->cart->get_cart();
        $products = array();

        foreach ($cart_items as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $products[] = array(
                'name' => $product->get_name(),
                'quantity' => $cart_item['quantity'],
                'weight' => $product->get_weight(),
            );
        }

        // Get user address
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $shipping_address = array(
                'address' => get_user_meta($user_id, 'shipping_address_1', true),
                'city' => get_user_meta($user_id, 'shipping_city', true),
                'state' => get_user_meta($user_id, 'shipping_state', true),
                'postcode' => get_user_meta($user_id, 'shipping_postcode', true),
                'country' => get_user_meta($user_id, 'shipping_country', true),
            );
        } else {
            $shipping_address = array(
                'address' => WC()->session->get('shipping_address_1'),
                'city' => WC()->session->get('shipping_city'),
                'state' => WC()->session->get('shipping_state'),
                'postcode' => WC()->session->get('shipping_postcode'),
                'country' => WC()->session->get('shipping_country'),
            );
        }

        // Get store address and contact
        $store = array(
            'address' => get_option('lalamove_shipping_address', ''),
            'lat' => get_option('lalamove_shipping_lat', ''),
            'lng' => get_option('lalamove_shipping_lng', ''),
            'phone_number' => get_option('lalamove_phone_number', ''),
        );

        // Add the shipping address and store details to the response
        $response = array(
            'products' => $products,
            'customer' => $shipping_address,
            'store' => $store
        );

        wp_send_json_success($response);
    }

    // Enqueue custom scripts for AJAX
    add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');
    function enqueue_custom_scripts()
    {

        // Localize script to pass AJAX URL and nonce to JavaScript
        wp_localize_script('custom-plugin-script', 'pluginAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('custom_plugin_nonce'),
        ));
    }

    // Update shipping rate dynamically
    add_action('wp_ajax_update_shipping_rate', 'update_shipping_rate');
    add_action('wp_ajax_nopriv_update_shipping_rate', 'update_shipping_rate');
    function update_shipping_rate()
    {

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

    function register_custom_cart_update_callback()
    {
        woocommerce_store_api_register_update_callback(
            array(
                'namespace' => 'your_custom_namespace',
                'callback' => 'handle_custom_cart_update',
            )
        );
    }

    function handle_custom_cart_update($data)
    {
        error_log('BOOOOOOOOOOOM');
    }



    add_filter('woocommerce_cart_shipping_packages', 'disable_shipping_rate_cache', 100);

    function disable_shipping_rate_cache($packages)
    {
        foreach ($packages as &$package) {
            // Invalidate the cache by assigning a unique value
            $package['rate_cache'] = wp_rand(); // Generates a random value for every package
        }

        return $packages;
    }

    add_action('admin_notices', 'wc_lalamove_admin_notice');
    function wc_lalamove_admin_notice()
    {
        if (get_transient('wc_lalamove_table_created')) {
            echo '<div class="notice notice-success is-dismissible">
                    <p>WooCommerce Lalamove table was successfully created.</p>
                </div>';
            delete_transient('wc_lalamove_table_created');
        }
    }

    add_action('woocommerce_thankyou', 'set_lalamove_order', 10, 1);
    function set_lalamove_order($order_id)
    {

        WC()->session->__unset('shipment_cost');

        // Start a secure session
        session_start([
            'cookie_lifetime' => 3600,
            'read_and_close'  => false,
        ]);

        $current_user = wp_get_current_user();

        global $wpdb;

        // Retrieve data from $_SESSION
        $quotationID = $_SESSION['quotationID'] ?? null;
        echo '<pre>Quotation ID: ' . $quotationID . '</pre>';

        $stopId0 = $_SESSION['stopId0'] ?? null;
        echo '<pre>Stop ID 0: ' . $stopId0 . '</pre>';

        $stopId1 = $_SESSION['stopId1'] ?? null;
        echo '<pre>Stop ID 1: ' . $stopId1 . '</pre>';

        $customerFName = $_SESSION['customerFName'] ?? null;
        echo '<pre>Customer First Name: ' . $customerFName . '</pre>';

        $customerLName = $_SESSION['customerLName'] ?? null;
        echo '<pre>Customer Last Name: ' . $customerLName . '</pre>';

        $customerPhoneNo = $_SESSION['customerPhoneNo'] ?? null;
        echo '<pre>Customer Phone Number: ' . $customerPhoneNo . '</pre>';

        $additionalNotes = $_SESSION['additionalNotes'] ?? null;
        echo '<pre>Additional Notes: ' . $additionalNotes . '</pre>';

        $proofOfDelivery = $_SESSION['proofOfDelivery'] ?? null;
        echo '<pre>Proof of Delivery: ' . $proofOfDelivery . '</pre>';

        $vehicleType = $_SESSION['serviceType'] ?? null;
        echo '<pre>Vehicle Type: ' . $vehicleType . '</pre>';

        $priceBreakdownData = $_SESSION['priceBreakdown'] ?? null;
        $priceBreakdown = json_decode($priceBreakdownData, true);
        var_dump($priceBreakdown);

        $scheduledOn = $_SESSION['scheduledOn'] ?? null;
        echo '<pre>Schedule: ' . $scheduledOn . '</pre>';

        $dropOffLocation = $_SESSION['dropOffLocation'] ?? null;
        echo '<pre>Drop Off Location: ' . $dropOffLocation . '</pre>';



        $customerFullName = $customerFName . " " . $customerLName;

        $lalamove_api = new Class_Lalamove_Api();

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

        if (!isset($lalamove_orderId['data']['quotationId'])) {
            error_log("TIGNAN MO TO ANGELO: " . var_dump($lalamove_orderId));
            return;
        }
        
        $name = $current_user->display_name;
        $role = $current_user->roles[0];

        $orders_table = $wpdb->prefix . 'wc_lalamove_orders';
        $transaction_table = $wpdb->prefix . 'wc_lalamove_transaction';
        $cost_details_table = $wpdb->prefix . 'wc_lalamove_cost_details';
        $status_table = $wpdb->prefix . 'wc_lalamove_status';


        var_dump($lalamove_orderId );

        try {
            $wpdb->query('START TRANSACTION');
            
            $wpdb->insert($cost_details_table, [
                'currency' => $priceBreakdown['currency'],
                'base' => $priceBreakdown['base'] ?? 0,
                'extra_mileage' => $priceBreakdown['extraMileage'] ?? 0,
                'surcharge' => $priceBreakdown['surcharge'] ?? 0,
                'total' => $priceBreakdown['total'] ?? 0,
                'priority_fee' => $priceBreakdown['priorityFee'] ?? 0,
            ]);

            $cost_details_id = $wpdb->insert_id;
            
            if (!$cost_details_id) {
                throw new Exception("Failed to insert into cost_details_table.");
            }

            $wpdb->insert($transaction_table, [
                'cost_details_id' => $cost_details_id,
                'ordered_by' => "$name($role)",
                'service_type' => $vehicleType,
            ]);

            $transaction_id = $wpdb->insert_id;

            if (!$transaction_id) {
                throw new Exception("Failed to insert into transaction_table.");
            }

            $result = $wpdb->insert($orders_table, [
                'transaction_id' => $transaction_id,
                'wc_order_id' => $order_id,
                'status_id' => 1,
                'lalamove_order_id' => $lalamove_orderId,
                'ordered_on' => current_time('mysql'),
                'scheduled_on' => $scheduledOn,
                'drop_off_location' => $dropOffLocation
            ]);

            if ($result === false) {

                $error_message = $wpdb->last_error;
                throw new Exception("Database insert error: " . $error_message);
            
            }
            
            $wpdb->query('COMMIT');
            
        } catch(Exception $e) {
            // Rollback the transaction if an error occurs
            $wpdb->query('ROLLBACK');
                
            // Log the error and return a failure response
            error_log('Transaction failed: ' . $e->getMessage());
        }

        // error_log('Quotation Body' . )
        error_log('Order completed: ' . $order_id);
    }

    add_action('wp_ajax_set_quotation_data_session', 'set_quotation_data_session');
    add_action('wp_ajax_nopriv_set_quotation_data_session', 'set_quotation_data_session');
    function set_quotation_data_session()
    {
        if (isset($_POST['quotationID'])) {

            // Secure session settings
            ini_set('session.cookie_secure', '1');
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.cookie_samesite', 'Strict');

            // Start a secure session
            session_start([
                'cookie_lifetime' => 3600,
                'read_and_close'  => false,
            ]);
            $_SESSION['dummy'] ='dummy';

            $_SESSION['quotationID'] = sanitize_text_field($_POST['quotationID']);
            $_SESSION['stopId0'] = sanitize_text_field($_POST['stopId0']);
            $_SESSION['stopId1'] = sanitize_text_field($_POST['stopId1']);
            $_SESSION['customerFName'] = sanitize_text_field($_POST['customerFName']);
            $_SESSION['customerLName'] = sanitize_text_field($_POST['customerLName']);
            $_SESSION['customerPhoneNo'] = sanitize_text_field($_POST['customerPhoneNo']);
            $_SESSION['scheduledOn'] = sanitize_text_field($_POST['scheduledOn']) ?? "";
            $_SESSION['dropOffLocation'] = sanitize_text_field($_POST['dropOffLocation']) ?? "";
            $_SESSION['additionalNotes'] = sanitize_text_field($_POST['additionalNotes']);
            $_SESSION['proofOfDelivery'] = sanitize_text_field($_POST['proofOfDelivery']);
            $_SESSION['serviceType'] = sanitize_text_field($_POST['serviceType']);
            $_SESSION['priceBreakdown'] = stripslashes($_POST['priceBreakdown']);
    
            // Set expiration for the session
            $_SESSION['expiry'] = time() + 3600; // Session expires after 1 hour
    
            wp_send_json_success(['message' => 'Quotation updated securely.']);
        } else {
            wp_send_json_error(['message' => 'Quotation ID is missing.']);
        }
        wp_die();
    }
}
