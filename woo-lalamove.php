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


                add_action('wp_enqueue_scripts', [$this, 'enqueue_custom_plugin_scripts']);

                add_filter('woocommerce_my_account_my_orders_actions', [$this, 'customer_delivery_status_button'], 10, 2);

                require_once plugin_dir_path(__FILE__) . 'includes/Class_Lalamove_Shipping_Method.php';



                register_activation_hook(__FILE__, callback: [$this, 'create_lalamove_tables']);

                add_filter('woocommerce_billing_fields', [$this, 'make_phone_field_required'], 10, 2);

                add_action('woocommerce_blocks_loaded', 'register_custom_cart_update_callback');
                add_filter('woocommerce_checkout_fields', [$this, 'modify_checkout_phone_field'], 10, 1);

                add_action('add_meta_boxes', [$this, 'register_meta_box_push_order_to_lalamove']);
                
                // Add shipping payment status column to orders list
                add_filter('manage_woocommerce_page_wc-orders_columns', [$this, 'add_shipping_payment_column']);
                add_action('manage_woocommerce_page_wc-orders_custom_column', [$this, 'display_shipping_payment_column'], 10, 2);

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
            $nonce = wp_create_nonce("send_to_lalamove_nonce_{$order_id}");
            
            // Get shipping payment details
            $payment_details = get_shipping_payment_details($order_id);
            $shipping_total = $order->get_shipping_total();
            
            echo '<div style="margin-bottom: 15px;">';
            echo '<h4>Shipping Payment Status</h4>';
            
            if ($shipping_total == 0) {
                echo '<p><span style="color: #00a32a; font-weight: bold;">FREE SHIPPING</span> - Order qualifies for free shipping</p>';
            } elseif ($payment_details && $payment_details['paid_by'] === 'admin') {
                $profit_loss = floatval($payment_details['profit_loss']);
                $color = $profit_loss >= 0 ? '#00a32a' : '#d63638';
                echo '<p><span style="color: ' . $color . '; font-weight: bold;">ADMIN PAID</span></p>';
                echo '<p><strong>Actual Cost:</strong> ' . get_woocommerce_currency_symbol() . number_format($payment_details['actual_cost'], 2) . '</p>';
                echo '<p><strong>Customer Paid:</strong> ' . get_woocommerce_currency_symbol() . number_format($shipping_total, 2) . '</p>';
                echo '<p><strong>Profit/Loss:</strong> <span style="color: ' . $color . ';">' . get_woocommerce_currency_symbol() . number_format($profit_loss, 2) . '</span></p>';
                echo '<p><small>Payment Method: ' . esc_html($payment_details['payment_method']) . '</small></p>';
            } elseif ($payment_details && $payment_details['paid_by'] === 'customer') {
                echo '<p><span style="color: #0073aa; font-weight: bold;">CUSTOMER PAID</span></p>';
                echo '<p><strong>Amount:</strong> ' . get_woocommerce_currency_symbol() . number_format($payment_details['actual_cost'], 2) . '</p>';
            } else {
                echo '<p><span style="color: #ffb900; font-weight: bold;">PAYMENT STATUS UNKNOWN</span></p>';
                echo '<div style="margin-top: 10px;">
                        <label for="admin_shipping_cost">Mark as Admin Paid:</label><br>
                        <input type="number" id="admin_shipping_cost" step="0.01" placeholder="Actual cost paid" style="width: 120px; margin-right: 10px;">
                        <select id="admin_payment_method" style="margin-right: 10px;">
                            <option value="wallet">Wallet</option>
                            <option value="card">Credit Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cash">Cash</option>
                        </select>
                        <button type="button" class="button mark-admin-paid-btn" data-order_id="' . $order_id . '" data-nonce="' . wp_create_nonce("mark_admin_paid_nonce_{$order_id}") . '">Mark as Paid</button>
                      </div>';
            }
            echo '</div><hr style="margin: 15px 0;">';

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
                    'mark_admin_paid_action' => 'mark_admin_shipping_paid',
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
            
            // Add submenu for shipping analytics
            add_submenu_page(
                'woo-lalamove',
                'Shipping Analytics',
                'Shipping Analytics',
                'manage_woocommerce',
                'lalamove-shipping-analytics',
                [$this, 'render_shipping_analytics_page']
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
        
        /**
         * Render shipping analytics page
         */
        public function render_shipping_analytics_page() {
            // Handle date range from GET parameters
            $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : date('Y-m-01');
            $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : date('Y-m-d');
            
            // Get analytics data
            $analytics = get_shipping_analytics($start_date, $end_date);
            
            ?>
            <div class="wrap">
                <h1>Lalamove Shipping Analytics</h1>
                
                <form method="get" action="">
                    <input type="hidden" name="page" value="lalamove-shipping-analytics">
                    <table class="form-table">
                        <tr>
                            <th>Date Range:</th>
                            <td>
                                <input type="date" name="start_date" value="<?php echo esc_attr($start_date); ?>" required>
                                to
                                <input type="date" name="end_date" value="<?php echo esc_attr($end_date); ?>" required>
                                <input type="submit" class="button" value="Update">
                            </td>
                        </tr>
                    </table>
                </form>
                
                <div class="analytics-summary" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
                    <div class="analytics-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                        <h3>Total Orders</h3>
                        <p style="font-size: 24px; font-weight: bold; color: #0073aa;"><?php echo $analytics['total_orders']; ?></p>
                    </div>
                    
                    <div class="analytics-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                        <h3>Customer Paid</h3>
                        <p style="font-size: 18px; font-weight: bold; color: #0073aa;"><?php echo $analytics['customer_paid_orders']; ?> orders</p>
                        <p>Revenue: <?php echo get_woocommerce_currency_symbol() . number_format($analytics['total_customer_revenue'], 2); ?></p>
                    </div>
                    
                    <div class="analytics-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                        <h3>Admin Paid</h3>
                        <p style="font-size: 18px; font-weight: bold; color: #d63638;"><?php echo $analytics['admin_paid_orders']; ?> orders</p>
                        <p>Cost: <?php echo get_woocommerce_currency_symbol() . number_format($analytics['total_admin_cost'], 2); ?></p>
                    </div>
                    
                    <div class="analytics-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                        <h3>Free Shipping</h3>
                        <p style="font-size: 18px; font-weight: bold; color: #00a32a;"><?php echo $analytics['free_shipping_orders']; ?> orders</p>
                    </div>
                    
                    <div class="analytics-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                        <h3>Net Profit/Loss</h3>
                        <?php 
                        $profit_loss = $analytics['total_profit_loss'];
                        $color = $profit_loss >= 0 ? '#00a32a' : '#d63638';
                        ?>
                        <p style="font-size: 20px; font-weight: bold; color: <?php echo $color; ?>;">
                            <?php echo get_woocommerce_currency_symbol() . number_format($profit_loss, 2); ?>
                        </p>
                    </div>
                    
                    <div class="analytics-card" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                        <h3>Average Shipping</h3>
                        <p style="font-size: 18px; font-weight: bold; color: #666;">
                            <?php echo get_woocommerce_currency_symbol() . number_format($analytics['average_shipping_cost'], 2); ?>
                        </p>
                    </div>
                </div>
                
                <?php if (!empty($analytics['orders_breakdown'])): ?>
                <h2>Order Breakdown</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Payment Type</th>
                            <th>Customer Paid</th>
                            <th>Actual Cost</th>
                            <th>Profit/Loss</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($analytics['orders_breakdown'] as $order): ?>
                        <tr>
                            <td>
                                <a href="<?php echo admin_url('post.php?post=' . $order['order_id'] . '&action=edit'); ?>">
                                    #<?php echo $order['order_id']; ?>
                                </a>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($order['date'])); ?></td>
                            <td>
                                <?php 
                                switch($order['payment_type']) {
                                    case 'free':
                                        echo '<span style="color: #00a32a; font-weight: bold;">FREE</span>';
                                        break;
                                    case 'admin':
                                        echo '<span style="color: #d63638; font-weight: bold;">ADMIN PAID</span>';
                                        break;
                                    case 'customer':
                                        echo '<span style="color: #0073aa; font-weight: bold;">CUSTOMER PAID</span>';
                                        break;
                                    default:
                                        echo '<span style="color: #666;">Unknown</span>';
                                }
                                ?>
                            </td>
                            <td><?php echo get_woocommerce_currency_symbol() . number_format($order['customer_paid'], 2); ?></td>
                            <td><?php echo get_woocommerce_currency_symbol() . number_format($order['actual_cost'], 2); ?></td>
                            <td>
                                <?php 
                                $pl = $order['profit_loss'];
                                $color = $pl >= 0 ? '#00a32a' : '#d63638';
                                ?>
                                <span style="color: <?php echo $color; ?>; font-weight: bold;">
                                    <?php echo get_woocommerce_currency_symbol() . number_format($pl, 2); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p>No Lalamove orders found for the selected date range.</p>
                <?php endif; ?>
            </div>
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

    // AJAX handler for marking admin shipping payment
    add_action('wp_ajax_mark_admin_shipping_paid', 'mark_admin_shipping_paid_handler');
    
    /**
     * Handle marking shipping as paid by admin
     */
    function mark_admin_shipping_paid_handler() {
        // Verify nonce and capabilities
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Insufficient permissions.');
        }
        
        $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
        $shipping_cost = isset($_POST['shipping_cost']) ? floatval($_POST['shipping_cost']) : 0;
        $payment_method = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : '';
        
        if (!$order_id || $shipping_cost <= 0) {
            wp_send_json_error('Invalid order ID or shipping cost.');
        }
        
        // Verify nonce
        $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
        if (!wp_verify_nonce($nonce, "mark_admin_paid_nonce_{$order_id}")) {
            wp_send_json_error('Invalid nonce.');
        }
        
        // Mark as admin paid
        $result = set_shipping_payment_details($order_id, 'admin', $shipping_cost, $payment_method);
        
        if ($result) {
            wp_send_json_success("Shipping payment marked as admin paid: " . get_woocommerce_currency_symbol() . number_format($shipping_cost, 2));
        } else {
            wp_send_json_error('Failed to update shipping payment details.');
        }
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
        
        /**
         * Add shipping payment status column to orders list
         */
        public function add_shipping_payment_column($columns) {
            // Insert the new column after the 'order_status' column
            $new_columns = [];
            foreach ($columns as $key => $value) {
                $new_columns[$key] = $value;
                if ($key === 'order_status') {
                    $new_columns['lalamove_payment_status'] = 'Shipping Payment';
                }
            }
            return $new_columns;
        }
        
        /**
         * Display shipping payment status in orders list
         */
        public function display_shipping_payment_column($column, $order_id) {
            display_shipping_payment_column($column, $order_id);
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
