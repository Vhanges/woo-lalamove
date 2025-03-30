<?php 
if (!defined('ABSPATH'))
exit;

use Sevhen\WooLalamove\Class_Lalamove_Api;

add_action('woocommerce_thankyou', 'set_lalamove_order', 10, 1);
function set_lalamove_order($order_id)
{

    global $wpdb;


    WC()->session->__unset('shipment_cost');



    $orders_table = $wpdb->prefix . 'wc_lalamove_orders';
    $transaction_table = $wpdb->prefix . 'wc_lalamove_transaction';
    $cost_details_table = $wpdb->prefix . 'wc_lalamove_cost_details';
    $status_table = $wpdb->prefix . 'wc_lalamove_status';

    $isLalamoveOrderSet = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$orders_table} WHERE wc_order_id = %d", $order_id 
    ));

    if($isLalamoveOrderSet > 0){
        return;
    }

    // Start a secure session
    session_start([
        'cookie_lifetime' => 20 * 60,
        'read_and_close'  => false,
    ]);


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
    $proofOfDelivery = $proofOfDelivery ? true : false;
    echo '<pre>Proof of Delivery: ' . $proofOfDelivery . '</pre>';

    $vehicleType = $_SESSION['serviceType'] ?? null;
    echo '<pre>Vehicle Type: ' . $vehicleType . '</pre>';

    $priceBreakdownData = $_SESSION['priceBreakdown'] ?? null;

    if($priceBreakdownData != null){
        $priceBreakdown = json_decode($priceBreakdownData, true);
    }

    var_dump($priceBreakdown);

    $scheduledOn = $_SESSION['scheduledOn'] ?? null;
    echo '<pre>Schedule: ' . $scheduledOn . '</pre>';

    $dropOffLocation = $_SESSION['dropOffLocation'] ?? null;
    echo '<pre>Drop Off Location: ' . $dropOffLocation . '</pre>';

    $customerFullName = $customerFName . " " . $customerLName;

    $lalamove_api = new Class_Lalamove_Api();

    $current_user = wp_get_current_user();

    if ($current_user->exists() && !empty($current_user->roles)) {
        $name = $current_user->display_name;
        $role = $current_user->roles[0]; 
    } else {
        $name = $customerFullName;
        $role = 'Guest';
    }

    $lalamove_order = $lalamove_api->place_order(
        $quotationID,
        $stopId0,
        $stopId1,
        get_bloginfo('name'),
        get_option("lalamove_phone_number", "+634315873"),
        $customerFullName,
        "+6307457184",
        $additionalNotes,
        $proofOfDelivery
    );

    var_dump($lalamove_order );

    if (!isset($lalamove_order['data']['orderId'])) {
        error_log("[Lalamove] There's an error on placing an order: " . var_dump($lalamove_order));
        return;
    }
    
    $lalamove_orderId = $lalamove_order['data']['orderId'];

    var_dump($lalamove_orderId );


    $doesItExist = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$orders_table} WHERE lalamove_order_id = %d", $lalamove_orderId 
    ));

    if($doesItExist > 0){
        error_log("Lalamove Order ID Already Exist");
        return;
    }
    

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

        unset($_SESSION['quotationID']);
        unset($_SESSION['stopId0']);
        unset($_SESSION['stopId1']);
        unset($_SESSION['customerFName']);
        unset($_SESSION['customerLName']);
        unset($_SESSION['customerPhoneNo']);
        unset($_SESSION['scheduledOn']);
        unset($_SESSION['dropOffLocation']);
        unset($_SESSION['additionalNotes']);
        unset($_SESSION['proofOfDelivery']);
        unset($_SESSION['serviceType']);
        unset($_SESSION['priceBreakdown']);

        ?> 
        <script>
        var storedState = sessionStorage.getItem("SessionData"); 
        var SessionData = JSON.parse(storedState);
        console.log("SessionData loaded from sessionStorage:", SessionData);

     
        sessionStorage.removeItem("SessionData");
        console.log("SessionData has been removed from sessionStorage.");
        </script>
        <?php

    } catch(Exception $e) {
        // Rollback the transaction if an error occurs
        $wpdb->query('ROLLBACK');
        // Log the error and return a failure response
        error_log('Transaction failed: ' . $e->getMessage());
        echo "
        <div class='alert alert-danger d-flex align-items-center' role='alert' style='font-size: 1.2rem;'>
            <div>
                <strong>Please Contact Us.</strong> There's a problem on placing your shipment order
                <br>
                <i class='bi bi-telephone-fill'></i> <strong>Phone:</strong> <a href='tel:+1234567890'>+1234567890</a><br>
                <i class='bi bi-envelope-fill'></i> <strong>Email:</strong> <a href='mailto:support@example.com'>support@example.com</a>
            </div>
        </div>
       ";
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

        $_SESSION['expiry'] = time() + 20 * 60; // Session expires after 20 minutes

        wp_send_json_success(['message' => 'Quotation updated securely.']);
    } else {
        wp_send_json_error(['message' => 'Quotation ID is missing.']);
    }
    wp_die();
}