<?php
namespace Sevhen\WooLalamove;

if (!defined('ABSPATH'))
    exit;

use WP_REST_Request;
use WP_REST_Response;

class Class_Lalamove_Endpoints extends Class_Lalamove_Model
{
    private $lalamove_api;
    private $model;

    public function __construct()
    {
        $this->model = new Class_Lalamove_Model();
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

        register_rest_route('woo-lalamove/v1', '/place-order', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'place_order'],
            'permission_callback' => '__return_true'
        ]); 

        register_rest_route('woo-lalamove/v1', '/store-order', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'store_order'],
            'permission_callback' => '__return_true'
        ]); 

        // Get Quotation Details
        register_rest_route('woo-lalamove/v1', '/get-quotation-details', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'get_quotation_details'],
            'args' => [
                'id' => [
                    'validate_callback' => function($param, $request, $key) {
                        return  $param; 
                    },
                ],
            ],
            'permission_callback' => '__return_true'
        ]); 

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

        register_rest_route('woo-lalamove/v1', '/dashboard-orders-data', [
            'methods' => ['GET'],
            'callback' => [$this, 'dashboard_orders_data'],
            'args' => [
                    'from' => [
                        'validate_callback' => function($param, $request, $key) {
                            return strtotime($param) !== false; 
                        },
                    ],
                    'to' => [
                            'validate_callback' => function($param, $request, $key) {
                                return strtotime($param) !== false; 
                            },
                        ],
                    'status' => [
                        'validate_callback' => function($param, $request, $key) {
                            return strtotime($param) !== false; 
                        },
                    ],
                    'search_input' => [
                            'validate_callback' => function($param, $request, $key) {
                                return strtotime($param) !== false; 
                            },
                        ],
            ],
            'permission_callback' => '__return_true'

        ]); 

        register_rest_route('woo-lalamove/v1', '/dashboard-spending-data', [
            'methods' => ['GET'],
            'callback' => [$this, 'dashboard_spending_data'],
            'args' => [
                    'from' => [
                        'validate_callback' => function($param, $request, $key) {
                            return strtotime($param) !== false; 
                        },
                    ],
                    'to' => [
                            'validate_callback' => function($param, $request, $key): bool {
                                return strtotime($param) !== false; 
                            },
                        ],
            ],
            'permission_callback' => '__return_true'
        ]); 

        register_rest_route('woo-lalamove/v1', '/get-lala-order-details', [
            'methods' => ['GET'],
            'callback' => [$this, 'get_lala_order_details'],
            'args' => [
                    'lala_id' => [
                        'validate_callback' => function($param, $request, $key) {
                            return  $param; 
                        },
                    ],
            ],
            'permission_callback' => '__return_true'
        ]); 
        register_rest_route('woo-lalamove/v1', '/get-lala-driver-details', [
            'methods' => ['GET'],
            'callback' => [$this, 'get_lala_driver_details'],
            'args' => [
                    'lala_id' => [
                        'validate_callback' => function($param, $request, $key) {
                            return  $param; 
                        },
                    ],
                    'driver_id' => [
                        'validate_callback' => function($param, $request, $key) {
                            return  $param; 
                        },
                    ],
            ],
            'permission_callback' => '__return_true'
        ]); 
        register_rest_route('woo-lalamove/v1', '/records-data', [
            'methods' => ['GET'],
            'callback' => [$this, 'records_data'],
            'args' => [
                    'from' => [
                        'validate_callback' => function($param, $request, $key) {
                            return  $param; 
                        },
                    ],
                    'to' => [
                        'validate_callback' => function($param, $request, $key) {
                            return  $param; 
                        },
                        ],
                    'status' => [
                        'validate_callback' => function($param, $request, $key) {
                            return  $param; 
                        },
                    ],
                    'search_input' => [
                        'validate_callback' => function($param, $request, $key) {
                            return  $param; 
                        },
                    ],
            ],
            'permission_callback' => '__return_true'
        ]); 
        register_rest_route('woo-lalamove/v1', '/cancel-order', [
            'methods' => ['DELETE'],
            'callback' => [$this, 'cancel_order'],
            'args' => [
                    'lala_id' => [
                        'validate_callback' => function($param, $request, $key) {
                            return  $param; 
                        },
                    ],
                    'body' => [
                        'validate_callback' => function($param, $request, $key) {
                            return  $param; 
                        },
                    ],
            ],
            'permission_callback' => '__return_true'
        ]); 
        register_rest_route('woo-lalamove/v1', '/lalamove-order-body', [
            'methods' => ['GET'],
            'callback' => [$this, 'lalamove_order_body'],
            'args' => [
                    'lala_id' => [
                        'validate_callback' => function($param, $request, $key) {
                            return  $param; 
                        },
                    ],
            ],
            'permission_callback' => '__return_true'
        ]); 
    }

    /**
     * 
     * Callback for dashboard_orders_data route
     * 
     * @param  $data
     */

    public function dashboard_orders_data($data)
    {
        global $wpdb;

        $response = $this->model->get_dashboard_orders_data($wpdb, $data);
        return rest_ensure_response($response);

    }

    /**
     * 
     * Callback for dashboard_spending_data route
     * 
     * @param  $data
     */

    public function dashboard_spending_data($data)
    {
        global $wpdb;

        $response = $this->model->get_dashboard_spending_data($wpdb,$data);
        return rest_ensure_response($response);


    }
    /**
     * 
     * Callback for records_data route
     * 
     * @param  $data
     */

    public function records_data($data)
    {
        global $wpdb;

        $response = $this->model->get_records_data($wpdb,$data);
        error_log("LALAMOVE". var_dump($response));
        return rest_ensure_response($response);


    }
    /**
     * 
     * Callback for lalamove_order_body route
     * 
     * @param  $data
     */

    public function lalamove_order_body($data)
    {
        global $wpdb;

        $response = $this->model->get_lalamove_order_body($wpdb,$data);
        error_log("LALAMOVE". var_dump($response));
        return rest_ensure_response($response);


    }
    /**
     * 
     * Callback for cancel_order route
     * 
     * @param  $data
     */

    public function cancel_order($data)
    {
        $lala_id = $data['lala_id'];
        $body = $data['body'];

        $response = $this->lalamove_api->cancel_order($lala_id, $body);
        error_log("LALAMOVE". var_dump($response));
        return rest_ensure_response($response);


    }

    /**
     * Callback for get_lalamove_orders
     * 
     */

    public function get_lalamove_orders()
    {
        global $wpdb;

        $response = $this->model->get_orders($wpdb);
        return rest_ensure_response($response);
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
     * Callback for place_order route
     * 
     * @return $res
     */
    public function place_order(WP_REST_Request $request)
    {   

        $body = $request->get_json_params();

        $response = $this->lalamove_api->place_order($body);
        return rest_ensure_response($response);

    }
    public function store_order(WP_REST_Request $request)
    {   

        $body = $request->get_body();

        $this->model->set_order($body);
    }
    public function get_quotation_details($data)
    {   
        $quotationID = $data['id'];

        $response = $this->lalamove_api->get_quotation_details($quotationID);
        return rest_ensure_response($response);
    }

    /**
     * Callback for get_quotation route
     * 
     * @return $res
     */
    public function lalamove_webhook(WP_REST_Request $request)
    {
        // Acknowledge webhook request immediately
        header('HTTP/1.1 200 OK');
        flush();
        ignore_user_abort(true);
    
        global $wpdb;

        $this->model->handle_webhook($wpdb, $request);

    }
    

    /**
     * Callback for get_lala_order_details
     * 
     * @return $res
     */
    public function get_lala_order_details($data) {

        $lala_id = $data['lala_id'];

        $response = $this->lalamove_api->get_order_details($lala_id);

        return $response ; 

    }
    
    /**
     * Callback for get_lala_driver_details
     * 
     * @return $res
     */
    public function get_lala_driver_details($data) {

        $lala_id = $data['lala_id'];
        $driver_id = $data['driver_id'];

        $response = $this->lalamove_api->get_driver_details($lala_id, $driver_id);

        return $response ; 

    }
    

}


