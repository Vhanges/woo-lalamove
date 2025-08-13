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
require_once plugin_dir_path(__FILE__) . 'cors.php';

use Sevhen\WooLalamove\Class_Lalamove_Model;
use Sevhen\WooLalamove\Class_Lalamove_Settings;
use Sevhen\WooLalamove\Class_Lalamove_Api;
use Sevhen\WooLalamove\Class_Lalamove_Endpoints;
use Sevhen\WooLalamove\Class_Lalamove_Shortcode;

new Class_Lalamove_Model();
new Class_Lalamove_Settings();
new Class_Lalamove_Api();
new Class_Lalamove_Endpoints();
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
                add_action('admin_enqueue_scripts', [$this, 'enqueue_lalamove_metabox']);
                add_action('admin_menu', [$this, 'woo_lalamove_add_admin_page']);
                add_action('admin_menu', [$this, 'woo_lalamove_add_shipping_analytics_page']);


                add_action('wp_enqueue_scripts', [$this, 'enqueue_custom_plugin_scripts']);

                add_filter('woocommerce_my_account_my_orders_actions', [$this, 'customer_delivery_status_button'], 10, 2);

                require_once plugin_dir_path(__FILE__) . 'includes/Class_Lalamove_Shipping_Method.php';



                register_activation_hook(__FILE__, callback: [$this, 'create_lalamove_tables']);

                add_filter('woocommerce_billing_fields', [$this, 'make_phone_field_required'], 10, 2);

                add_action('woocommerce_blocks_loaded', 'register_custom_cart_update_callback');
                add_filter('woocommerce_checkout_fields', [$this, 'modify_checkout_phone_field'], 10, 1);

                add_action('add_meta_boxes', [$this, 'register_meta_box_push_order_to_lalamove']);
                add_action('woocommerce_checkout_order_processed', [$this, 'save_shipping_cost_breakdown'], 10, 3);
                add_action('woocommerce_admin_order_data_after_shipping_address', [$this, 'display_shipping_cost_breakdown']);

            }
        }

         /**
         * Registers the custom meta box in WooCommerce admin order page.
         */
        public function register_meta_box_push_order_to_lalamove() {
            $screen = get_screen_id();
            
            add_meta_box(
                'lalamove-meta-box',
                __('Lalamove', 'woocommerce-lalamove-extension'),
                [$this, 'render_meta_box'],
                $screen,
                'normal',
                'core'
            );
        }
        
        /**
         * Adds the shipping analytics admin page.
         */
        public function woo_lalamove_add_shipping_analytics_page() {
            add_submenu_page(
                'woocommerce',
                __('Lalamove Shipping Analytics', 'woocommerce-lalamove-extension'),
                __('Lalamove Analytics', 'woocommerce-lalamove-extension'),
                'manage_woocommerce',
                'lalamove-shipping-analytics',
                [$this, 'render_shipping_analytics_page']
            );
        }
        
        
        /**
         * Renders the cancel request meta box inside WooCommerce admin order page.
         */
        public function render_meta_box($post) {
            $order = get_order_object($post);
            if (!$order) {
                return; 
            }

            $shipping_methods = $order->get_shipping_methods();
            $is_lalamove = false;

            foreach ($shipping_methods as $method) {
                if (strpos($method->get_method_id(), 'your_shipping_method') !== false) {
                    $is_lalamove = true;
                    break;
                }
            }

            if (!$is_lalamove) {
                return; // Don't render the box if Lalamove isn't used
            }

            $order_id = $order->get_id();
            $nonce    = wp_create_nonce("send_to_lalamove_nonce_{$order_id}");

            echo '<button type="button" 
                    class="button transfer-to-lalamove-btn" 
                    data-action="transfer-to-lalamove-btn"  
                    data-order_id="' . $order_id . '" 
                    data-nonce="' . $nonce . '" 
                    style="
                        background-color: #ff6600; 
                        border-color: #ff6600; 
                        color: #fff; 
                        width: 100%; 
                        padding: 6px 10px;
                        margin: 5px auto;
                        display: block;
                        text-align: center;
                    ">
                    Send to lalamove
                </button>';
        }


        function modify_checkout_phone_field($fields)
        {
            $fields['billing']['billing_phone']['placeholder'] = 'Ex. +6343554325';
            $fields['billing']['billing_phone']['custom_attributes']['pattern'] = '^\\+[1-9][0-9]{1,14}$';
            return $fields;
        }
        function make_phone_field_required($fields)
        {
            $fields['billing_phone']['required'] = true; // Set to true to make it mandatory
            return $fields;
        }

        function create_lalamove_tables()
        {
            global $wpdb;
            $charset_collate = $wpdb->get_charset_collate();

            // Define table names with dynamic prefix
            $balance_table = "{$wpdb->prefix}wc_lalamove_balance";
            $cost_details_table = "{$wpdb->prefix}wc_lalamove_cost_details";
            $status_table = "{$wpdb->prefix}wc_lalamove_status";
            $transaction_table = "{$wpdb->prefix}wc_lalamove_transaction";
            $orders_table = "{$wpdb->prefix}wc_lalamove_orders";

            // Check if tables already exist
            if (
                $wpdb->get_var("SHOW TABLES LIKE '$balance_table'") == $balance_table &&
                $wpdb->get_var("SHOW TABLES LIKE '$cost_details_table'") == $cost_details_table &&
                $wpdb->get_var("SHOW TABLES LIKE '$status_table'") == $status_table &&
                $wpdb->get_var("SHOW TABLES LIKE '$transaction_table'") == $transaction_table &&
                $wpdb->get_var("SHOW TABLES LIKE '$orders_table'") == $orders_table
            ) {
                // Check if we need to add new columns to existing orders table
                $this->migrate_orders_table($orders_table);
                return;
            }

            $sql_balance = "CREATE TABLE $balance_table (
                balance_id BIGINT AUTO_INCREMENT NOT NULL,
                balance_currency CHAR(10) NOT NULL,
                wallet_balance DOUBLE NOT NULL,
                update_on DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY (balance_id)
            ) $charset_collate;";


            $sql_cost_details = "CREATE TABLE $cost_details_table ( 
                cost_details_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                currency CHAR(10) NOT NULL,
                base DOUBLE NOT NULL,
                extra_mileage DOUBLE,
                surcharge DOUBLE,
                total DOUBLE NOT NULL,
                priority_fee DOUBLE,
                subsidy DOUBLE,
                PRIMARY KEY (cost_details_id)
            ) $charset_collate;";

            $sql_status = "CREATE TABLE $status_table (
                status_id INT NOT NULL,
                status_name VARCHAR(100) NOT NULL,
                description TEXT,
                PRIMARY KEY (status_id)
            ) $charset_collate;";

            $sql_transaction = "CREATE TABLE $transaction_table (
                transaction_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                cost_details_id BIGINT UNSIGNED NULL,
                ordered_by VARCHAR(200) NOT NULL,
                service_type VARCHAR(200) NOT NULL,
                PRIMARY KEY (transaction_id)
            ) $charset_collate;";

            $sql_orders = "CREATE TABLE $orders_table (
                integration_id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                transaction_id BIGINT UNSIGNED NOT NULL,
                wc_order_id BIGINT UNSIGNED,
                status_id INT NOT NULL,
                lalamove_order_id BIGINT NOT NULL,
                ordered_on DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                scheduled_on DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                drop_off_location TEXT NOT NULL,
                remarks TEXT NOT NULL,
                free_shipping INT,
                order_json_body JSON NOT NULL,
                shipping_cost_customer DOUBLE DEFAULT 0.00,
                shipping_cost_admin DOUBLE DEFAULT 0.00,
                shipping_paid_by ENUM('customer', 'admin', 'split') DEFAULT 'customer',
                lalamove_actual_cost DOUBLE DEFAULT 0.00,
                profit_loss DOUBLE DEFAULT 0.00,
                PRIMARY KEY (integration_id),
                FOREIGN KEY (transaction_id) REFERENCES $transaction_table(transaction_id)
            ) $charset_collate;";


            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            dbDelta($sql_balance);
            dbDelta($sql_cost_details);
            dbDelta($sql_status);
            dbDelta($sql_transaction);
            dbDelta($sql_orders);

            $tables_created = [$cost_details_table, $status_table, $transaction_table, $orders_table];
            $success = array_filter($tables_created, function ($table) use ($wpdb) {
                return $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;
            });


            $sql_status_insert = "INSERT INTO $status_table (status_id, status_name, description) VALUES
                ('0', 'Pending', 'The order has been created but is waiting for further action or confirmation.'),
                ('1', 'Processed', 'The order has been reviewed and confirmed for fulfillment.'),
                ('2', 'Assigning Driver', 'A driver is being assigned to handle the delivery.'),
                ('3', 'Awaiting Driver', 'The driver is on its way to pick up the order.'),
                ('4', 'Item Collected', 'The driver has successfully picked up the item from the origin location.'),
                ('5', 'Delivered Successfully', 'The delivery has been completed, and the item has reached its destination.'),
                ('6', 'Rejected', 'The order was rejected by the drivers'),
                ('7', 'Order Canceled', 'The order was canceled before the delivery process began.'),
                ('8', 'Expired', 'The order\'s processing timeline has exceeded its limit and is no longer valid.'),
                ('9', 'Needs Manual Review', 'The order encountered an error and requires admin attention.');
            ";

            $wpdb->query($sql_status_insert);

            if (count($success) === count($tables_created)) {
                set_transient('wc_lalamove_table_created', true, 5); // Notify admin
            } else {
                error_log('Some tables were not created successfully.');
            }
        }

        function migrate_orders_table($orders_table)
        {
            global $wpdb;
            
            // Add new columns if they don't exist
            $columns = $wpdb->get_col("SHOW COLUMNS FROM $orders_table");
            
            if (!in_array('shipping_cost_customer', $columns)) {
                $wpdb->query("ALTER TABLE $orders_table ADD COLUMN shipping_cost_customer DOUBLE DEFAULT 0.00");
            }
            if (!in_array('shipping_cost_admin', $columns)) {
                $wpdb->query("ALTER TABLE $orders_table ADD COLUMN shipping_cost_admin DOUBLE DEFAULT 0.00");
            }
            if (!in_array('shipping_paid_by', $columns)) {
                $wpdb->query("ALTER TABLE $orders_table ADD COLUMN shipping_paid_by ENUM('customer', 'admin', 'split') DEFAULT 'customer'");
            }
            if (!in_array('lalamove_actual_cost', $columns)) {
                $wpdb->query("ALTER TABLE $orders_table ADD COLUMN lalamove_actual_cost DOUBLE DEFAULT 0.00");
            }
            if (!in_array('profit_loss', $columns)) {
                $wpdb->query("ALTER TABLE $orders_table ADD COLUMN profit_loss DOUBLE DEFAULT 0.00");
            }
        }

        function customer_delivery_status_button($actions, $order)
        {
            // Ensure $order is a valid WC_Order object
            if (!($order instanceof WC_Order)) {
                return $actions;
            }

            $order_id = $order->get_id();

            // Check if Lalamove ID exists
            if (get_lala_id($order_id) === null) {
                return $actions;
            }

            // Build URL to the custom order details page
            $url = add_query_arg('order_id', $order_id, site_url('/delivery-status/'));

            // Add custom button action
            $actions['custom_button'] = array(
                'url'  => $url,
                'name' => __('Track Order', 'woocommerce-lalamove-extension'),
            );

            return $actions;
        }

        function enqueue_lalamove_metabox() {
            // Only on the WooCommerce order edit screen
            $screen = get_current_screen();

            // Only run on the Shop Order edit screen (even under admin.php?page=wc-orders…)
            if ( ! $screen || 'shop_order' !== $screen->post_type ) {
            error_log("NOT METABOX ENQUEUED");

                return;
            }

            error_log("METABOX ENQUEUED");


            wp_enqueue_script(
                'lalamove-metabox',
                plugin_dir_url( __FILE__ ) . 'assets/js/lalamove-metabox.js',
                [ 'jquery' ],
                '1.0.0',
                true
            );

            wp_localize_script(
                'lalamove-metabox',
                'wooLalamoveMetabox',
                [
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce'    => wp_create_nonce( 'custom_plugin_nonce' ),
                ]
            );

        }


        public function enqueue_vue_assets($hook)
        {
            if ($hook !== 'toplevel_page_woo-lalamove') {
                return;
            }

            // Core Plugin JS
            wp_enqueue_script(
                'woo-lalamove',
                plugin_dir_url(__FILE__) . 'assets/js/dist/bundle.js',
                [],
                filemtime(plugin_dir_path(__FILE__) . 'assets/js/dist/bundle.js'),
                true
            );

            wp_localize_script(
                'woo-lalamove',
                'wpApiSettings',
                [
                    'root'  => esc_url_raw(rest_url()),
                    'nonce' => wp_create_nonce('wp_rest')
                ]
            );


            // Leaflet Core Styles & JS
            wp_enqueue_style(
                'leaflet-css',
                'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
                [],
                '1.9.4'
            );
            wp_enqueue_script(
                'leaflet-js',
                'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
                [],
                '1.9.4',
                true
            );

            // Leaflet Control Geocoder
            wp_enqueue_style(
                'leaflet-geocoder-css',
                'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css',
                [],
                null
            );
            wp_enqueue_script(
                'leaflet-geocoder-js',
                'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js',
                ['leaflet-js'],
                null,
                true
            );

            // Optional: Material Symbols font (used in UI buttons or icons)
            wp_enqueue_style(
                'material-symbols',
                'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined'
            );

            // Moment.js (if needed in bundle)
            wp_enqueue_script(
                'moment-js',
                'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js',
                [],
                null,
                true
            );

            // Date Range Picker
            wp_enqueue_script(
                'daterangepicker-js',
                'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js',
                ['jquery', 'moment-js'],
                null,
                true
            );
            wp_enqueue_style(
                'daterangepicker-css',
                'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css',
                [],
                null
            );

            // Security Nonce & AJAX variables
            wp_localize_script('woo-lalamove', 'wooLalamoveAdmin', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('woo_lalamove_nonce'),
                'root' => esc_url_raw(rest_url('/')),
                'api_nonce' => wp_create_nonce('wp_rest'),
            ]);

            // Admin page styles
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
                'Lalamove',
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

                // Enqueue Leaflet CSS
                wp_enqueue_style('leaflet-css', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), null);

                // Enqueue Leaflet JS
                wp_enqueue_script('leaflet-js', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), null, true);
                // Enqueue Moment.js
                wp_enqueue_script('moment-js', 'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js', array('jquery'), null, true);

                wp_enqueue_style(
                    'leaflet-geocoder-css',
                    'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css',
                    [],
                    null
                );
                wp_enqueue_script(
                    'leaflet-geocoder-js',
                    'https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js',
                    ['leaflet-js'],
                    null,
                    true
                );

                // Enqueue Phone Number Validation JS
                wp_enqueue_script('libphonenumber-js', 'https://cdn.jsdelivr.net/npm/libphonenumber-js@1.10.24/bundle/libphonenumber{-max.js', array(), null, true);

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
                wp_dequeue_script('jquery');
                wp_dequeue_script('custom-plugin-script');
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

        /**
         * Renders the shipping analytics page.
         */
        public function render_shipping_analytics_page() {
            global $wpdb;
            
            // Get analytics data
            $table = $wpdb->prefix . 'wc_lalamove_orders';
            
            // Total orders
            $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM $table");
            
            // Total shipping costs
            $total_customer_shipping = $wpdb->get_var("SELECT SUM(shipping_cost_customer) FROM $table");
            $total_admin_shipping = $wpdb->get_var("SELECT SUM(shipping_cost_admin) FROM $table");
            $total_lalamove_cost = $wpdb->get_var("SELECT SUM(lalamove_actual_cost) FROM $table");
            $total_profit_loss = $wpdb->get_var("SELECT SUM(profit_loss) FROM $table");
            
            // Orders by payment responsibility
            $customer_paid_orders = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE shipping_paid_by = 'customer'");
            $admin_paid_orders = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE shipping_paid_by = 'admin'");
            $split_paid_orders = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE shipping_paid_by = 'split'");
            
            // Recent orders with shipping details
            $recent_orders = $wpdb->get_results("
                SELECT o.*, t.ordered_by 
                FROM $table o 
                LEFT JOIN {$wpdb->prefix}wc_lalamove_transaction t ON o.transaction_id = t.transaction_id
                ORDER BY o.ordered_on DESC 
                LIMIT 20
            ");
            
            ?>
            <div class="wrap">
                <h1><?php _e('Lalamove Shipping Analytics', 'woocommerce-lalamove-extension'); ?></h1>
                
                <!-- Summary Cards -->
                <div class="lalamove-analytics-summary">
                    <div class="analytics-card">
                        <h3><?php _e('Total Orders', 'woocommerce-lalamove-extension'); ?></h3>
                        <p class="analytics-number"><?php echo number_format($total_orders); ?></p>
                    </div>
                    
                    <div class="analytics-card">
                        <h3><?php _e('Customer Shipping Revenue', 'woocommerce-lalamove-extension'); ?></h3>
                        <p class="analytics-number">₱<?php echo number_format($total_customer_shipping, 2); ?></p>
                    </div>
                    
                    <div class="analytics-card">
                        <h3><?php _e('Admin Shipping Costs', 'woocommerce-lalamove-extension'); ?></h3>
                        <p class="analytics-number">₱<?php echo number_format($total_admin_shipping, 2); ?></p>
                    </div>
                    
                    <div class="analytics-card">
                        <h3><?php _e('Total Lalamove Costs', 'woocommerce-lalamove-extension'); ?></h3>
                        <p class="analytics-number">₱<?php echo number_format($total_lalamove_cost, 2); ?></p>
                    </div>
                    
                    <div class="analytics-card">
                        <h3><?php _e('Net Profit/Loss', 'woocommerce-lalamove-extension'); ?></h3>
                        <p class="analytics-number <?php echo $total_profit_loss >= 0 ? 'positive' : 'negative'; ?>">
                            ₱<?php echo number_format($total_profit_loss, 2); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Payment Responsibility Breakdown -->
                <div class="lalamove-analytics-section">
                    <h2><?php _e('Shipping Payment Responsibility', 'woocommerce-lalamove-extension'); ?></h2>
                    <div class="payment-breakdown">
                        <div class="breakdown-item">
                            <span class="label"><?php _e('Customer Paid:', 'woocommerce-lalamove-extension'); ?></span>
                            <span class="value"><?php echo number_format($customer_paid_orders); ?> orders</span>
                        </div>
                        <div class="breakdown-item">
                            <span class="label"><?php _e('Admin Paid:', 'woocommerce-lalamove-extension'); ?></span>
                            <span class="value"><?php echo number_format($admin_paid_orders); ?> orders</span>
                        </div>
                        <div class="breakdown-item">
                            <span class="label"><?php _e('Split Payment:', 'woocommerce-lalamove-extension'); ?></span>
                            <span class="value"><?php echo number_format($split_paid_orders); ?> orders</span>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders Table -->
                <div class="lalamove-analytics-section">
                    <h2><?php _e('Recent Orders', 'woocommerce-lalamove-extension'); ?></h2>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('Order ID', 'woocommerce-lalamove-extension'); ?></th>
                                <th><?php _e('Date', 'woocommerce-lalamove-extension'); ?></th>
                                <th><?php _e('Customer', 'woocommerce-lalamove-extension'); ?></th>
                                <th><?php _e('Payment Responsibility', 'woocommerce-lalamove-extension'); ?></th>
                                <th><?php _e('Customer Cost', 'woocommerce-lalamove-extension'); ?></th>
                                <th><?php _e('Admin Cost', 'woocommerce-lalamove-extension'); ?></th>
                                <th><?php _e('Lalamove Cost', 'woocommerce-lalamove-extension'); ?></th>
                                <th><?php _e('Profit/Loss', 'woocommerce-lalamove-extension'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo admin_url('post.php?post=' . $order->wc_order_id . '&action=edit'); ?>">
                                            #<?php echo $order->wc_order_id; ?>
                                        </a>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($order->ordered_on)); ?></td>
                                    <td><?php echo esc_html($order->ordered_by); ?></td>
                                    <td>
                                        <span class="payment-badge payment-<?php echo $order->shipping_paid_by; ?>">
                                            <?php echo ucfirst($order->shipping_paid_by); ?>
                                        </span>
                                    </td>
                                    <td>₱<?php echo number_format($order->shipping_cost_customer, 2); ?></td>
                                    <td>₱<?php echo number_format($order->shipping_cost_admin, 2); ?></td>
                                    <td>₱<?php echo number_format($order->lalamove_actual_cost, 2); ?></td>
                                    <td class="<?php echo $order->profit_loss >= 0 ? 'positive' : 'negative'; ?>">
                                        ₱<?php echo number_format($order->profit_loss, 2); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <style>
                    .lalamove-analytics-summary {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                        gap: 20px;
                        margin: 20px 0;
                    }
                    
                    .analytics-card {
                        background: #fff;
                        padding: 20px;
                        border-radius: 8px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                        text-align: center;
                    }
                    
                    .analytics-card h3 {
                        margin: 0 0 10px 0;
                        color: #23282d;
                        font-size: 14px;
                    }
                    
                    .analytics-number {
                        font-size: 24px;
                        font-weight: bold;
                        margin: 0;
                        color: #0073aa;
                    }
                    
                    .analytics-number.positive { color: #46b450; }
                    .analytics-number.negative { color: #dc3232; }
                    
                    .lalamove-analytics-section {
                        background: #fff;
                        padding: 20px;
                        margin: 20px 0;
                        border-radius: 8px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }
                    
                    .payment-breakdown {
                        display: flex;
                        gap: 30px;
                        margin-top: 15px;
                    }
                    
                    .breakdown-item {
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                    }
                    
                    .breakdown-item .label {
                        font-size: 12px;
                        color: #666;
                        margin-bottom: 5px;
                    }
                    
                    .breakdown-item .value {
                        font-size: 18px;
                        font-weight: bold;
                        color: #0073aa;
                    }
                    
                    .payment-badge {
                        padding: 4px 8px;
                        border-radius: 4px;
                        font-size: 11px;
                        font-weight: bold;
                        text-transform: uppercase;
                    }
                    
                    .payment-customer { background: #e7f5ff; color: #0066cc; }
                    .payment-admin { background: #fff3cd; color: #856404; }
                    .payment-split { background: #d4edda; color: #155724; }
                    
                    .positive { color: #46b450; }
                    .negative { color: #dc3232; }
                </style>
            </div>
            <?php
        }

        /**
         * Saves shipping cost breakdown to order meta.
         */
        public function save_shipping_cost_breakdown($order_id, $posted_data, $order) {
            $cost_breakdown = WC()->session->get('lalamove_cost_breakdown', array());
            
            if (!empty($cost_breakdown)) {
                update_post_meta($order_id, '_lalamove_shipping_cost_breakdown', $cost_breakdown);
                update_post_meta($order_id, '_lalamove_shipping_paid_by', $cost_breakdown['strategy'] ?? 'customer');
                update_post_meta($order_id, '_lalamove_actual_cost', $cost_breakdown['lalamove_actual_cost'] ?? 0);
                update_post_meta($order_id, '_lalamove_admin_cost', $cost_breakdown['admin_cost'] ?? 0);
                update_post_meta($order_id, '_lalamove_customer_cost', $cost_breakdown['customer_cost'] ?? 0);
            }
        }
        
        /**
         * Displays shipping cost breakdown in admin order page.
         */
        public function display_shipping_cost_breakdown($order) {
            $cost_breakdown = get_post_meta($order->get_id(), '_lalamove_shipping_cost_breakdown', true);
            
            if (!empty($cost_breakdown)) {
                echo '<div class="lalamove-shipping-breakdown">';
                echo '<h3>' . __('Lalamove Shipping Cost Breakdown', 'woocommerce-lalamove-extension') . '</h3>';
                echo '<table class="widefat">';
                echo '<tr>';
                echo '<td><strong>' . __('Payment Responsibility:', 'woocommerce-lalamove-extension') . '</strong></td>';
                echo '<td>' . ucfirst($cost_breakdown['strategy']) . '</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td><strong>' . __('Lalamove Actual Cost:', 'woocommerce-lalamove-extension') . '</strong></td>';
                echo '<td>₱' . number_format($cost_breakdown['lalamove_actual_cost'], 2) . '</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td><strong>' . __('Customer Paid:', 'woocommerce-lalamove-extension') . '</strong></td>';
                echo '<td>₱' . number_format($cost_breakdown['customer_cost'], 2) . '</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td><strong>' . __('Admin Paid:', 'woocommerce-lalamove-extension') . '</strong></td>';
                echo '<td>₱' . number_format($cost_breakdown['admin_cost'], 2) . '</td>';
                echo '</tr>';
                echo '<tr>';
                echo '<td><strong>' . __('Profit/Loss:', 'woocommerce-lalamove-extension') . '</strong></td>';
                $profit_loss = $cost_breakdown['customer_cost'] - $cost_breakdown['lalamove_actual_cost'];
                $profit_class = $profit_loss >= 0 ? 'positive' : 'negative';
                echo '<td class="' . $profit_class . '">₱' . number_format($profit_loss, 2) . '</td>';
                echo '</tr>';
                echo '</table>';
                echo '</div>';
                
                echo '<style>
                    .lalamove-shipping-breakdown {
                        background: #f9f9f9;
                        padding: 15px;
                        margin: 15px 0;
                        border: 1px solid #ddd;
                        border-radius: 4px;
                    }
                    .lalamove-shipping-breakdown h3 {
                        margin-top: 0;
                        color: #23282d;
                    }
                    .lalamove-shipping-breakdown table {
                        margin: 0;
                    }
                    .lalamove-shipping-breakdown td {
                        padding: 8px;
                        border: none;
                    }
                    .lalamove-shipping-breakdown .positive {
                        color: #46b450;
                        font-weight: bold;
                    }
                    .lalamove-shipping-breakdown .negative {
                        color: #dc3232;
                        font-weight: bold;
                    }
                </style>';
            }
        }
    }

    new Woo_Lalamove();

    // add_filter('script_loader_tag', function($tag, $handle, $src) {
    //     if ($handle === 'custom-plugin-script') {
    //         return '<script type="module" src="' . esc_url($src) . '"></script>';
    //     }
    //     return $tag;
    // }, 10, 3);

    // add_action( 'woocommerce_admin_order_data_after_shipping_address', function( $order ) {

    //     $lat = get_post_meta( $order->get_id(), '_lalamove_lat', true );
    //     $lng = get_post_meta( $order->get_id(), '_lalamove_lng', true );

    //     if ( $lat && $lng ) {
    //         echo '<p><strong>Delivery Coordinates:</strong><br>';
    //         echo 'Latitude: ' . esc_html( $lat ) . '<br>';
    //         echo 'Longitude: ' . esc_html( $lng ) . '</p>';
    //     } else {
    //         echo '<p><strong>Delivery Coordinates:</strong> Not available</p>';
    //     }
        
    // });

    add_action('woocommerce_before_cart_totals', 'free_shipping_message');
    add_action('woocommerce_before_checkout_form', 'free_shipping_message');

    function free_shipping_message() {
        $threshold = get_free_shipping_threshold();
        $subtotal = WC()->cart->get_subtotal();

        if ( $threshold && $subtotal < $threshold ) {
            $remaining = wc_price( $threshold - $subtotal );
            echo "<div class='woocommerce-message'>Add {$remaining} more to get <strong>Free Shipping</strong>!</div>";
        } elseif ( $threshold ) {
            echo "<div class='woocommerce-message'><strong>Congrats!</strong> You’ve unlocked Free Shipping 🎉</div>";
        }
    }


    function get_free_shipping_threshold() {
        $packages = WC()->shipping()->get_packages();

        foreach ( $packages as $package ) {
            $zone = WC_Shipping_Zones::get_zone_matching_package( $package );
            $methods = $zone->get_shipping_methods();

            foreach ( $methods as $method ) {
                if ( $method->id === 'free_shipping' && isset( $method->min_amount ) ) {
                    return (float) $method->min_amount;
                }
            }
        }

        return null; // fallback if not found
    }


    add_filter( 'woocommerce_package_rates', 'vhanges_hide_other_shipping_when_free', 100, 2 );
    function vhanges_hide_other_shipping_when_free( $rates, $package ) {
        $free = [];

        foreach ( $rates as $rate_id => $rate ) {
            if ( 'free_shipping' === $rate->method_id ) {
                $free[ $rate_id ] = $rate;
                break;
            }
        }

        return ! empty( $free ) ? $free : $rates;
    }

    // Only for logged‐in users (admin screens)
    add_action( 'wp_ajax_push_send_to_lalamove', 'send_to_lalamove_handler' );
    /**
     * Handle the AJAX request: send order to Lalamove
     */
    function send_to_lalamove_handler() {
        // Sanitize inputs
        $order_id = isset( $_POST['order_id'] ) 
            ? absint( $_POST['order_id'] ) 
            : wp_send_json_error( 'Missing order ID.' );

        $model = new Class_Lalamove_Model();

        // Your business logic: push to Lalamove, log errors, etc.
        $result = $model->push_pos_order_to_lalamove( $order_id );

        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result->get_error_message() );
        }

        // Everything went fine
        wp_send_json_success( "Order #{$order_id} queued for Lalamove." );
    }


    add_action('wp_ajax_get_seller_delivery_address', 'get_seller_delivery_address');
    add_action('wp_ajax_nopriv_get_seller_delivery_address', 'get_seller_delivery_address');

    function get_seller_delivery_address(){
        $store = array(
            'name' => get_bloginfo('name'),
            'address' => get_option('lalamove_shipping_address', ''),
            'lat' => get_option('lalamove_shipping_lat', ''),
            'lng' => get_option('lalamove_shipping_lng', ''),
            'phone_number' => get_option('lalamove_phone_number', ''),
        );

        if(!empty($store['address']) && !empty($store['lat']) && !empty($store['lng'])) {
            wp_send_json_success($store);
        } else {
            wp_send_json_error('Please configure the pickup address properly.');
        }

    }


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
                'address' => WC()->session->get('shipping_address_1') ?? "",
                'city' => WC()->session->get('shipping_city' ?? ""),
                'state' => WC()->session->get('shipping_state') ?? "",
                'postcode' => WC()->session->get('shipping_postcode') ?? "",
                'country' => WC()->session->get('shipping_country') ?? "",
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

        /**
         * TODO: 
         * 
         * ** Make sure to add a constraint here for it to be only visible to specific page such as lalamove modal
         * 
         */

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

    $add_custom_bulk_action = function (array $bulk_actions) {
        return array_merge($bulk_actions, ['bulk_print_waybill' => 'Print Waybill']);
    };


    $custom_bulk_action_handler = function (string $redirect_to, string $action, array $ids) {
        if ($action !== 'bulk_print_waybill') {
            return $redirect_to;
        }

        // Generate URL to pass the IDs to a new page
        $url = admin_url('admin-ajax.php?action=bulk_print-waybill&ids=' . implode(',', $ids));
        return $url;
    };

    // AJAX handler
    add_action('wp_ajax_bulk_print-waybill', function () {

        $ids = explode(',', sanitize_text_field($_GET['ids']));
        print_waybill($ids, true);
    });

    $custom_bulk_action_notice = function () {
        if (isset($_GET['bulk_action']) && 'custom-bulk-action-notice' === $_GET['bulk_action']) {
            print '<div class="updated" style="border-left-color: #d7f"><p>Custom Bulk Action Handler Fired</p></div>';
        }
    };

    add_filter('bulk_actions-woocommerce_page_wc-orders', $add_custom_bulk_action);
    add_filter('handle_bulk_actions-woocommerce_page_wc-orders', $custom_bulk_action_handler, 10, 3);
    add_action('admin_notices', $custom_bulk_action_notice);


    add_action('woocommerce_admin_order_actions', function ($actions, $order) {
        $order_id = $order->get_id();
        $waybill_order_action = 'print-waybill';

        $actions[$waybill_order_action] = [
            'url'  => admin_url("admin-ajax.php?action=print-waybill&order_id={$order_id}"),
            'name' => 'Print Waybill',
            'action' => $waybill_order_action,
            'target' => '_blank',
        ];
        return $actions;
    }, 10, 2);

    // Register the AJAX action for logged-in users
    add_action('wp_ajax_print-waybill', 'handle_print_waybill_action');

    // Define the handler function
    function handle_print_waybill_action()
    {

        // Check if order_id is provided
        if (!isset($_GET['order_id'])) {
            wp_send_json_error(['message' => 'Order ID is missing.']);
            return;
        }


        $order_id = intval($_GET['order_id']);
        print_waybill($order_id, false);


        $referrer = wp_get_referer();

        return;

        if ($referrer) {
            echo '<script>';
            echo 'setTimeout(function() { window.location.href = "' . esc_url($referrer) . '"; }, 1000);'; // Redirect after 5 seconds
            echo '</script>';
        }

        wp_die();
    }

    add_action('admin_head', 'waybill_css');
    function waybill_css()
    {
        wp_enqueue_style('material-symbols', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined', [], null);
        $waybill_order_action = 'print-waybill';

        echo '
            <style>
                .wc-action-button-' . $waybill_order_action . '::after {
                    font-family: "Material Symbols Outlined" !important;
                    content: "print"; 
                    font-size: 16px; 
                    background-color: #FF7937;
                    margin: 0 !important;
                    color: white;
                }
                .wc-action-button-' . $waybill_order_action . '.' . $waybill_order_action . ' {
                    border-color: #FF7937;
                }
                .wc-action-button-' . $waybill_order_action . '.' . $waybill_order_action . ':hover {
                    border-color: #FF7937;
                }
            </style>
        ';
    }

    add_action('woocommerce_checkout_process', 'validate_phone_field');

    function validate_phone_field()
    {
        $phone = isset($_POST['billing_phone']) ? preg_replace('/\s+/', '', trim($_POST['billing_phone'])) : '';

        // Lalamove E.164 validation regex (supports international phone numbers)
        $pattern = '/^\+[1-9]\d{1,14}$/';

        if (empty($phone) || !preg_match($pattern, $phone)) {
            wc_add_notice(__('Please enter a valid phone number in E.164 format, e.g., +6312345678.'), 'error');
        }
    }


    add_action('wp_ajax_my_webhook', 'log_webhook_data'); // For logged-in users
    add_action('wp_ajax_nopriv_my_webhook', 'log_webhook_data'); // For public access

    function log_webhook_data()
    {
        $data = json_decode(file_get_contents('php://input'), true);
        error_log('Webhook received: ' . print_r($data, true));
        wp_die(); // End execution gracefully
    }

    require_once plugin_dir_path(__FILE__) . 'includes/Checkout_Delivery_Placement.php';
}
