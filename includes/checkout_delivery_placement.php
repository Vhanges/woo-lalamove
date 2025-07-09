<?php

if (!defined('ABSPATH')) {
    exit;
}

use Sevhen\WooLalamove\Class_Lalamove_Api;

add_action('woocommerce_checkout_order_processed', 'handle_lalamove_order', 10, 1);
add_action('woocommerce_store_api_checkout_order_processed', 'handle_lalamove_order', 10, 1);

function handle_lalamove_order($order_id)
{
    $order = wc_get_order($order_id);

    if ( ! $order ) {
        error_log("Order not found: $order_id");
        return;
    }

    $shipping_methods = $order->get_shipping_methods();

    foreach ( $shipping_methods as $item ) {
        $method_id = $item->get_method_id();

        if ($method_id === 'your_shipping_method') {
            set_lalamove_order($order_id, $order);
            return;
        } 
        
        if($method_id === 'free_shipping') {
            set_lalamove_coordinates_in_order($order_id);
        }
    }
 
}
function set_lalamove_coordinates_in_order( $order_id ) {
    start_secure_session(); 
    $lat = $_SESSION['lat'] ?? null;
    $lng = $_SESSION['lng'] ?? null;

    if ( ! $lat || ! $lng ) {
        error_log("⚠️ Lalamove coordinates missing in session for order ID: $order_id");
        return;
    }

    update_post_meta( $order_id, '_lalamove_lat', sanitize_text_field( $lat ) );
    update_post_meta( $order_id, '_lalamove_lng', sanitize_text_field( $lng ) );

    error_log("✅ SAVED COORDINATES for Order #$order_id — Lat: $lat, Lng: $lng");

    clear_lalamove_session_data();
}



function set_lalamove_order($order_id, $order)
{
    global $wpdb;

    WC()->session->__unset('shipment_cost');

    $table = (object) [
        'orders'       => $wpdb->prefix . 'wc_lalamove_orders',
        'transactions' => $wpdb->prefix . 'wc_lalamove_transaction',
        'cost_details' => $wpdb->prefix . 'wc_lalamove_cost_details',
    ];

    if ((int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table->orders} WHERE wc_order_id = %d", $order_id))) {
        return;
    }

    start_secure_session();
    $session = load_lalamove_session_data();

    if (empty($session['quotationID'])) {
        error_log('Lalamove: Quotation ID missing.');
        return;
    }

    $scheduledOn = format_schedule_datetime($session['scheduledOn'] ?? null);
    $dropOffLocation = format_shipping_address($order->get_address('shipping'));

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
        $session['additionalNotes'] ?? 'none',
        !empty($session['proofOfDelivery'])
    );

    $lalamove_order_id = $lalamove_order['data']['orderId'] ?? null;

    if (!$lalamove_order_id ||
        (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table->orders} WHERE lalamove_order_id = %d", $lalamove_order_id))) {
        error_log("Lalamove Order ID already exists or failed.");
        return;
    }

    try {
        $wpdb->query('START TRANSACTION');

        $breakdown = $session['priceBreakdown'] ?? [];

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
            throw new Exception("Cost details insert failed.");
        }

        $wpdb->insert($table->transactions, [
            'cost_details_id' => $cost_id,
            'ordered_by'     => $orderedBy,
            'service_type'   => $session['serviceType'],
        ]);

        $txn_id = $wpdb->insert_id;
        if (!$txn_id) {
            throw new Exception("Transaction insert failed.");
        }

        $wpdb->insert($table->orders, [
            'transaction_id'     => $txn_id,
            'wc_order_id'        => $order_id,
            'status_id'          => 1,
            'lalamove_order_id'  => $lalamove_order_id,
            'ordered_on'         => current_time('mysql'),
            'scheduled_on'       => $scheduledOn,
            'drop_off_location'  => $dropOffLocation,
            'order_json_body'    => json_encode($session['quotationBody']),
        ]);

        $wpdb->query('COMMIT');
        clear_lalamove_session_data();
        echo '<script>sessionStorage.removeItem("SessionData");</script>';
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        error_log("Transaction failed: {$e->getMessage()}");
        echo '<script>sessionStorage.removeItem("SessionData");</script>';
    }

    error_log("Order completed: {$order_id}");
}

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start([
            'cookie_lifetime' => 3600,
            'read_and_close'  => false,
        ]);
    }
}

function load_lalamove_session_data(): array
{
    return array_map('sanitize_text_field', [
        'quotationBody'     => $_SESSION['quotationBody'] ?? '',
        'quotationID'       => $_SESSION['quotationID'] ?? '',
        'stopId0'           => $_SESSION['stopId0'] ?? '',
        'stopId1'           => $_SESSION['stopId1'] ?? '',
        'customerFName'     => $_SESSION['customerFName'] ?? '',
        'customerLName'     => $_SESSION['customerLName'] ?? '',
        'customerPhoneNo'   => $_SESSION['customerPhoneNo'] ?? '',
        'scheduledOn'       => $_SESSION['scheduledOn'] ?? '',
        'additionalNotes'   => $_SESSION['additionalNotes'] ?? '',
        'proofOfDelivery'   => $_SESSION['proofOfDelivery'] ?? '',
        'serviceType'       => $_SESSION['serviceType'] ?? '',
        'priceBreakdown'    => json_decode($_SESSION['priceBreakdown'] ?? '{}', true),
    ]);
}

function format_schedule_datetime(?string $datetime): ?string
{
    if (!$datetime) return null;
    try {
        $timezone = get_option('timezone_string') ?: 'Asia/Singapore';
        $date = new DateTime($datetime, new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone($timezone));
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
        'quotationID', 'stopId0', 'stopId1', 'customerFName', 'customerLName',
        'customerPhoneNo', 'scheduledOn', 'additionalNotes', 'proofOfDelivery',
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

    $_SESSION['freeShipping'] = sanitize_text_field($_POST['freeShipping']);
    $_SESSION['lat'] = sanitize_text_field($_POST['lat']);
    $_SESSION['lng'] = sanitize_text_field($_POST['lng']);

    wp_send_json_success(['message' => 'Free shipping session saved.', 'LAT' => $_SESSION['lat'], 'LNG' => $_SESSION['lng']]);
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
    $_SESSION['additionalNotes'] = sanitize_text_field($_POST['additionalNotes'] ?? '');
    $_SESSION['proofOfDelivery'] = sanitize_text_field($_POST['proofOfDelivery'] ?? '');
    $_SESSION['serviceType'] = sanitize_text_field($_POST['serviceType'] ?? '');
    $_SESSION['priceBreakdown'] = stripslashes($_POST['priceBreakdown'] ?? '{}');
    $_SESSION['expiry'] = time() + 1200;

    wp_send_json_success(['message' => 'Quotation session saved.']);
    wp_die();
}
