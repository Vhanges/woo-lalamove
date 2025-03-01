<?php
namespace Sevhen\WooLalamove;

use WP_REST_Request;

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
            'methods' => 'POST',
            'callback' => [$this, 'quotation'],
            'permission_callback' => '__return_true'
        ]);

        // Checkout Package
        register_rest_route('woo-lalamove/v1', '/checkout-package', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'checkout_package'],
            'permission_callback' => '__return_true'
        ]);
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
    public function get_quotation()
    {
        $response = $this->lalamove_api->get_city();
        return rest_ensure_response($response);
    }

    public function checkout_package()
    {

        // Return the data as a JSON response
        // return new \WP_REST_Response($packages, 200);
    }
}
