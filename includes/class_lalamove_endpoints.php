<?php
namespace Sevhen\WooLalamove;

if (!defined('ABSPATH'))
    exit;

use WP_REST_Request;
use WP_REST_Response;

class Class_Lalamove_Endpoints
{
    private $lalamove_api;

    public function __construct()
    {
        $this->lalamove_api = new Class_Lalamove_Api();
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Rest API Routes
     */
    public function register_routes()
    {
        // Get City
        register_rest_route('woo-lalamove/v1', '/get-city', [
            'methods' => 'GET',
            'callback' => [$this, 'get_city'],
            'permission_callback' => '__return_true'
        ]);

        // Get Quotation
        register_rest_route('woo-lalamove/v1', '/get-quotation', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'get_quotation'],
            'permission_callback' => '__return_true'
        ]);

        // Checkout Package
        register_rest_route('woo-lalamove/v1', '/lalamove-webhook', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'lalamove_webhook'],
            'permission_callback' => '__return_true'
        ]);

        // Woo Lalamove Orders
        register_rest_route('woo-lalamove/v1', '/get-lalamove-orders', [
            'methods' => ['GET'],
            'callback' => [$this, 'get_lalamove_orders'],
            'permission_callback' => '__return_true'
        ]);
 

    
    }

    /**
     * Callback for get_lalamove_orders
     * 
     */

    public function get_lalamove_orders()
    {
        global $wpdb;
        $order_table = $wpdb->prefix . 'wc_lalamove_orders';
        $status_table = $wpdb->prefix . 'wc_lalamove_status';
        $transaction_table = $wpdb->prefix . 'wc_lalamove_transaction';

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            $query = "SELECT
                     o.wc_order_id,
                     o.ordered_on,
                     o.scheduled_on,
                     o.drop_off_location,
                     s.status_name,
                     t.ordered_by,
                     t.service_type
                    FROM $order_table AS o
                    INNER JOIN $status_table AS s 
                    ON o.status_id = s.status_id
                    INNER JOIN $transaction_table AS t 
                    ON o.transaction_id = t.transaction_id";
                    
            $results = $wpdb->get_results($query, ARRAY_A);

            foreach ($results as &$result) {    
                $wc_order = \wc_get_order($result['wc_order_id']);
                if ($wc_order) {
                    $result['customer_email'] = $wc_order->get_billing_email();
                    $result['customer_phone'] = $wc_order->get_billing_phone();
                } else {
                    $result['customer_email'] = null;
                    $result['customer_phone'] = null;
                }
            }

            // Commit transaction
            $wpdb->query('COMMIT');

            return rest_ensure_response($results);
        } catch (\Exception $e) {
            // Rollback transaction on error
            $wpdb->query('ROLLBACK');

            // Log the error
            error_log('Error fetching Lalamove orders: ' . $e->getMessage());

            return new WP_REST_Response(['message' => 'An error occurred while fetching orders.'], 500);
        }
    }
     

    
  

    /**
     * Callback for get_city route
     * 
     * @return $res
     */
    public function print_waybill()
    {
        $response = $this->lalamove_api->get_city();
        return rest_ensure_response($response);
    }
    /**
     * Callback for get_city route
     * 
     * @return $res
     */
    public function get_city()
    {
        $response = $this->lalamove_api->get_city();
        return rest_ensure_response($response);
    }

    /**
     * Callback for get_quotation route
     * 
     * @return $res
     */
    public function get_quotation(WP_REST_Request $request)
    {   
        $body = $request->get_json_params();

        $response = $this->lalamove_api->get_quotation($body);
        return rest_ensure_response($response);
    }

    /**
     * Callback for get_quotation route
     * 
     * @return $res
     */
    public function lalamove_webhook(WP_REST_Request $request)
    {
        $payload = $request->get_body();
        $data = json_decode( $payload, true );
    
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return new WP_REST_Response( array( 'message' => 'Invalid JSON' ), 400 );
        }
        
        // Optionally verify a signature or token here for security.
        error_log( 'Lalamove webhook data: ' . print_r( $data, true ) );
    
        return new WP_REST_Response( array( 'message' => 'Webhook received successfully' ), 200 );
    }

}
