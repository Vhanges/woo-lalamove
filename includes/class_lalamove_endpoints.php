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

        // Waybill QR Code Order Details Link
        register_rest_route('woo-lalamove/v1', '/order-details', [
            'methods' => ['GET'],
            'callback' => [$this, 'lalamove_webhook'],
            'permission_callback' => '__return_true'
        ]);
    }

    /**
     * Callback for QR Code Link Order Details Link
     * 
     * 
     */

    public function render_order_details(WP_REST_Request $request)
    {
        $order_id = $request->get_param('order_id');
        $order = \wc_get_order($order_id);
        if (!$order) {
            return new WP_REST_Response(['message' => 'Order not found'], 404);
        }else{
            echo "HFSDLFJKJDF";
        }

        // $tracking_url = $this->lalamove_api->get_tracking_url($order_id);
        // if (!$tracking_url) {
        //     return new WP_REST_Response(['message' => 'Tracking URL not found'], 404);
        // }

        // return rest_ensure_response($tracking_url);
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
