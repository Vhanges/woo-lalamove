<?php
if (!defined('ABSPATH')) {
    exit;
}

use Sevhen\WooLalamove\Class_Lalamove_Api;

// Remove original processing hooks
remove_action('woocommerce_checkout_order_processed', 'handle_lalamove_order', 10);
remove_action('woocommerce_store_api_checkout_order_processed', 'handle_lalamove_order', 10);

// Add validation and processing hooks
add_action('woocommerce_after_checkout_validation', 'validate_lalamove_api', 20, 2);
add_action('woocommerce_checkout_order_processed', 'process_lalamove_non_free_order', 10, 1);
add_action('woocommerce_checkout_order_processed', 'process_lalamove_free_order', 10, 1);
add_action('woocommerce_store_api_checkout_order_processed', 'process_lalamove_non_free_order', 10, 1);
add_action('woocommerce_store_api_checkout_order_processed', 'process_lalamove_free_order', 10, 1);

/**
 * Validate Lalamove API during checkout
 */


/**
 * 
 * TODO
 * * Store the order body wether the shipping is paid or not
 * 
 */
add_action( 'woocommerce_thankyou', 'show_on_hold_notice' );
function show_on_hold_notice( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( $order->get_status() === 'on-hold' ) {
        echo '<div class="woocommerce-info">Your order is awaiting manual shipment due to a courier issue. Please <a href="/contact">contact support</a> to continue.</div>';
    }
}

function validate_lalamove_api($data, $errors) {

    $chosen_methods = WC()->session->get('chosen_shipping_methods', []);
    $is_lalamove = false;

    foreach ($chosen_methods as $method_id) {
        if (strpos($method_id, 'your_shipping_method') !== false) {
            $is_lalamove = true;
            break;
        }
    }

    if (!$is_lalamove) return;

    start_secure_session();
    $session = load_lalamove_session_data();
    $quotationID = $session['quotationID'] ?? '';

    // Validate session data
    if (empty($quotationID)) {
        $errors->add('lalamove_error', 
            __('Shipping quotation missing. Please refresh the page and try again.', 'your-textdomain')
        );
        return;
    }

    try {
        
        $api = new Class_Lalamove_Api();
        
        // Verify quotation is still valid with Lalamove
        $api->get_quotation_details($quotationID); 
        
    } catch (Exception $e) {

        $errors->add('lalamove_error', 
            __($e->getMessage(), 'woo-lalamove-text-domain')
        );

    }
}

/**
 * Process non-free (Lalamove) orders
 */
function process_lalamove_non_free_order($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return;

    $is_lalamove = false;
    foreach ($order->get_shipping_methods() as $item) {
        if ($item->get_method_id() === 'your_shipping_method') {
            $is_lalamove = true;
            break;
        }
    }

    if (!$is_lalamove) return;

    start_secure_session();
    $session = load_lalamove_session_data();

    try {
        global $wpdb;

        // Verify critical session data
        if (empty($session['quotationID']) || empty($session['stopId0']) || empty($session['stopId1'])) {
            throw new Exception('Required Lalamove session data missing');
        }

        $scheduledOn = format_schedule_datetime($session['scheduledOn'] ?? null);
        $dropOffLocation = format_shipping_address($order->get_address('shipping'));
        $remarks = $order->get_customer_note();

        $current_user = wp_get_current_user();
        $orderedBy = $current_user->exists()
            ? $current_user->display_name . "(" . $current_user->roles[0] . ")"
            : $session['customerFName'] . " " . $session['customerLName'] . "(Guest)";

        $lalamove_api = new Class_Lalamove_Api();

        $lalamove_order = $lalamove_api->place_order(
            $session['quotationID'],
            $session['stopId0'],
            $session['stopId1'],
            get_bloginfo('name'),
            get_option("lalamove_phone_number", "+634315873"),
            $session['customerFName'] . " " . $session['customerLName'],
            $session['customerPhoneNo'],
            $remarks,
            !empty($session['proofOfDelivery'])
        );
            
        $lalamove_order_id = $lalamove_order['data']['orderId'] ?? null;

        if (!$lalamove_order_id) {
            throw new Exception('Lalamove order creation failed: No order ID returned');
        }

        $table = (object) [
            'orders'       => $wpdb->prefix . 'wc_lalamove_orders',
            'transactions' => $wpdb->prefix . 'wc_lalamove_transaction',
            'cost_details' => $wpdb->prefix . 'wc_lalamove_cost_details',
        ];

        // Check for existing order
        if ((int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table->orders} WHERE wc_order_id = %d", $order_id))) {
            return;
        }

        $wpdb->query('START TRANSACTION');

        $breakdown = $session['priceBreakdown'] ?? [];

        // Insert cost details
        $wpdb->insert($table->cost_details, [
            'currency'       => $breakdown['currency'] ?? '',
            'base'           => $breakdown['base'] ?? 0,
            'extra_mileage'  => $breakdown['extraMileage'] ?? 0,
            'surcharge'      => $breakdown['surcharge'] ?? 0,
            'total'          => $breakdown['total'] ?? 0,
            'priority_fee'   => $breakdown['priorityFee'] ?? 0,
        ]);

        $cost_id = $wpdb->insert_id;
        if (!$cost_id) {
            throw new Exception("Cost details insert failed");
        }

        // Insert transaction
        $wpdb->insert($table->transactions, [
            'cost_details_id' => $cost_id,
            'ordered_by'     => $orderedBy,
            'service_type'   => $session['serviceType'] ?? '',
        ]);

        $txn_id = $wpdb->insert_id;
        if (!$txn_id) {
            throw new Exception("Transaction insert failed");
        }

        // Insert order
        $wpdb->insert($table->orders, [
            'transaction_id'     => $txn_id,
            'wc_order_id'        => $order_id,
            'status_id'          => 1, // Pending status
            'lalamove_order_id'  => $lalamove_order_id,
            'ordered_on'         => current_time('mysql'),
            'scheduled_on'       => $scheduledOn,
            'drop_off_location'  => $dropOffLocation,
            'remarks'            => $remarks ?? 'none',
            'order_json_body'    => json_encode($session['quotationBody'] ?? []),
        ]);

        $wpdb->query('COMMIT');
        error_log("Lalamove order created: {$lalamove_order_id} for WC order: {$order_id}");
        
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        
        // Log error and update order status
        error_log("Lalamove Order Failed [{$order_id}]: " . $e->getMessage());
        $order->update_status('on-hold', '[Lalamove] ' . $e->getMessage());
        
        // Send admin notification
        $message = sprintf(
            __('Order #%s could not be processed by Lalamove. Reason: %s', 'your-textdomain'),
            $order_id,
            $e->getMessage()
        );
        wp_mail(get_option('admin_email'), __('Lalamove Order Failed', 'your-textdomain'), $message);
    } finally {
        // Clear session regardless of outcome
        clear_lalamove_session_data();
        echo '<script>sessionStorage.removeItem("SessionData");</script>';
    }
}

/**
 * Process free shipping orders
 */
function process_lalamove_free_order($order_id) {
    $order = wc_get_order($order_id);
    if (!$order) return;

    foreach ($order->get_shipping_methods() as $item) {
        if ($item->get_method_id() === 'free_shipping') {
            set_lalamove_free_shipping_order($order_id, $order);
            return;
        }
    }
}

/**
 * 
 */
function set_lalamove_free_shipping_order( $order_id, $order) {
    global $wpdb;
    
    start_secure_session(); 
    $session = load_lalamove_session_data();

    $table = (object) [
        'orders'       => $wpdb->prefix . 'wc_lalamove_orders',
        'transactions' => $wpdb->prefix . 'wc_lalamove_transaction',
        'cost_details' => $wpdb->prefix . 'wc_lalamove_cost_details',
    ];
    $current_user = wp_get_current_user();
    
    $remarks = $order->get_customer_note();
    

    $shippingContact = [
    'name'  => $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name(),
    'phone' => $order->get_billing_phone(), 
    'email' => $order->get_billing_email()
    ];
    $dropOffLocation = json_encode($shippingContact);

    $orderedBy = $current_user->exists()
    ? $current_user->display_name . "(" . $current_user->roles[0] . ")"
    : $session['customerFName'] . " " . $session['customerLName'] . "(Guest)";


    try {
        $wpdb->query('START TRANSACTION');

        // Add this check before processing
        if (empty($session['quotationBody']) || $session['quotationBody'] === '{}') {
            throw new Exception('Invalid quotation body in session data');
        }


        $wpdb->insert($table->cost_details, [
            'currency'       =>  '',
            'base'           =>  0,
            'extra_mileage'  =>  0,
            'surcharge'      =>  0,
            'total'          =>  0,
            'priority_fee'   =>  0,
        ]);

        $cost_id = $wpdb->insert_id;
        if (!$cost_id) {
            throw new Exception("Cost details insert failed.");
        }

        $wpdb->insert($table->transactions, [
            'cost_details_id' => $cost_id,
            'ordered_by'     => $orderedBy,
            'service_type'   => '',
        ]);

        $txn_id = $wpdb->insert_id;
        if (!$txn_id) {
            throw new Exception("Transaction insert failed.");
        }
        $dropOffLocation = format_shipping_address($order->get_address('shipping'));

        $wpdb->insert($table->orders, [
            'transaction_id'     => $txn_id,
            'wc_order_id'        => $order_id,
            'status_id'          => 1,
            'lalamove_order_id'  => 0,
            'ordered_on'         => current_time('mysql'),
            'scheduled_on'       => '',
            'drop_off_location'  => $dropOffLocation,
            'remarks'              => $remarks ?? 'none',
            'order_json_body'    => json_encode($_SESSION['quotationBody']),
        ]);

        $wpdb->query('COMMIT');
        clear_lalamove_session_data();
        echo '<script>sessionStorage.removeItem("SessionData");</script>';
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        error_log("Transaction failed: {$e->getMessage()}");
        echo '<script>sessionStorage.removeItem("SessionData");</script>';
    }

    clear_lalamove_session_data();
}



function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start(options: [
            'cookie_lifetime' => 3600,
        ]);
    }
}

function load_lalamove_session_data(): array
{
    return [
        'quotationBody'     => $_SESSION['quotationBody'] ?? '{}' ,
        'quotationID'       => $_SESSION['quotationID'] ?? '',
        'stopId0'           => $_SESSION['stopId0'] ?? '',
        'stopId1'           => $_SESSION['stopId1'] ?? '',
        'customerFName'     => $_SESSION['customerFName'] ?? '',
        'customerLName'     => $_SESSION['customerLName'] ?? '',
        'customerPhoneNo'   => $_SESSION['customerPhoneNo'] ?? '',
        'scheduledOn'       => $_SESSION['scheduledOn'] ?? '',
        'proofOfDelivery'   => $_SESSION['proofOfDelivery'] ?? '',
        'serviceType'       => $_SESSION['serviceType'] ?? '',
        'priceBreakdown'    => json_decode($_SESSION['priceBreakdown'] ?? '{}', true),
    ];
}

function format_schedule_datetime(?string $datetime): ?string
{
    if (!$datetime) return null;
    try {
        $timezone = get_option('timezone_string') ?: 'Asia/Singapore';
        $date = new DateTime($datetime, new DateTimeZone('UTC'));
        $date->setTimezone(timezone: new DateTimeZone($timezone));
        return $date->format('Y-m-d H:i:s');
    } catch (Exception $e) {
        error_log("Invalid schedule date: {$e->getMessage()}");
        return null;
    }
}

function format_shipping_address(array $address): string
{
    return implode(', ', array_filter([
        $address['address_1'] ?? '',
        $address['city'] ?? '',
        $address['postcode'] ?? '',
        $address['country'] ?? '',
    ]));
}

function clear_lalamove_session_data(): void
{
    $keys = [
        'quotationBody', 'quotationID', 'stopId0', 'stopId1', 'customerFName', 'customerLName',
        'customerPhoneNo', 'scheduledOn', 'proofOfDelivery',
        'serviceType', 'priceBreakdown'
    ];
    foreach ($keys as $key) {
        unset($_SESSION[$key]);
    }
}

add_action('wp_ajax_set_customer_free_shipment_session', 'set_customer_free_shipment_session');
add_action('wp_ajax_nopriv_set_customer_free_shipment_session', 'set_customer_free_shipment_session');

function set_customer_free_shipment_session(): void
{
    start_secure_session();

    if (!isset($_POST['freeShipping'])) {
        wp_send_json_error(['message' => 'Free shipping flag missing.']);
        wp_die();
    }

    $_SESSION['quotationBody'] = $_POST['quotationBody'] ?? '';

    wp_send_json_success(['message' => 'Free shipping session saved.', 'BODY' => $_SESSION['quotationBody']]);
    wp_die();
}

add_action('wp_ajax_set_quotation_data_session', 'set_quotation_data_session');
add_action('wp_ajax_nopriv_set_quotation_data_session', 'set_quotation_data_session');

function set_quotation_data_session(): void
{
    start_secure_session();

    if (!isset($_POST['quotationID'])) {
        wp_send_json_error(['message' => 'Quotation ID is missing.']);
        wp_die();
    }

    $_SESSION['quotationBody'] = $_POST['quotationBody'] ?? '';
    $_SESSION['quotationID'] = sanitize_text_field($_POST['quotationID']);
    $_SESSION['stopId0'] = sanitize_text_field($_POST['stopId0']);
    $_SESSION['stopId1'] = sanitize_text_field($_POST['stopId1']);
    $_SESSION['customerFName'] = sanitize_text_field($_POST['customerFName']);
    $_SESSION['customerLName'] = sanitize_text_field($_POST['customerLName']);
    $_SESSION['customerPhoneNo'] = sanitize_text_field($_POST['customerPhoneNo']);
    $_SESSION['scheduledOn'] = sanitize_text_field($_POST['scheduledOn'] ?? '');
    $_SESSION['proofOfDelivery'] = sanitize_text_field($_POST['proofOfDelivery'] ?? '');
    $_SESSION['serviceType'] = sanitize_text_field($_POST['serviceType'] ?? '');
    $_SESSION['priceBreakdown'] = stripslashes($_POST['priceBreakdown'] ?? '{}');
    $_SESSION['expiry'] = time() + 1200;

    wp_send_json_success(['message' => 'Quotation session saved.', 'body' => $_SESSION['quotationBody']]);
    wp_die();
}
