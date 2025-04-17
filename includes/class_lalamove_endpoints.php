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
            ],
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
                            'validate_callback' => function($param, $request, $key) {
                                return strtotime($param) !== false; 
                            },
                        ],
            ],
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
        $order_table = $wpdb->prefix . 'wc_lalamove_orders';
        $status_table = $wpdb->prefix . 'wc_lalamove_status';
        $transaction_table = $wpdb->prefix . 'wc_lalamove_transaction';
        $cost_details = $wpdb->prefix . 'wc_cost_details';

        $wpdb->query('START TRANSACTION');  

        try{

            $from = $data['from'];
            $to = $data['to'];
            $results = [];

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
                    ON o.transaction_id = t.transaction_id
                    WHERE o.ordered_on BETWEEN '$from' AND '$to'
                    ORDER BY o.ordered_on DESC
                    ";

            $results['table'] = $wpdb->get_results($query, ARRAY_A);

            // Initialize counters
            $totalOrders = 0;
            $completedDeliveries = 0;
            $activeDeliveries = 0;
            $pendingOrders = 0;
            $deliveryCanceled = 0;
            $rejectedOrders = 0;

            foreach ($results['table'] as &$result) {
                $orderedOn = new \DateTime($result['ordered_on']);
                $scheduledOn = new \DateTime($result['scheduled_on']);

                $result['ordered_on'] = $orderedOn->format('F j, Y') . '<br>' . $orderedOn->format('g:i A');
                $result['scheduled_on'] = $scheduledOn->format('F j, Y') . '<br>' . $scheduledOn->format('g:i A');

                $wc_order = \wc_get_order($result['wc_order_id']);
                if ($wc_order) {
                    $result['customer_email'] = $wc_order->get_billing_email();
                    $result['customer_phone'] = $wc_order->get_billing_phone();
                } else {
                    $result['customer_email'] = null;
                    $result['customer_phone'] = null;
                }

                // Increment total orders
                $totalOrders++;

                // Count based on status
                switch (strtolower($result['status_name'])) {
                    case 'delivered successfully':
                        $completedDeliveries++;
                        break;
                    case 'assigning driver':
                        $activeDeliveries++;
                        break;
                    case 'in transit':
                        $activeDeliveries++;
                        break;
                    case 'item collected':
                        $activeDeliveries++;
                        break;
                    case 'processed':
                        $activeDeliveries++;
                        break;
                    case 'pending':
                        $pendingOrders++;
                        break;
                    case 'order canceled':
                        $deliveryCanceled++;
                        break;
                    case 'expired':
                        $deliveryCanceled++;
                        break;
                    case 'rejected':
                        $rejectedOrders++;
                        break;
                }
            }

            // Add summary data to the response
            $results['kpi'] = [
                'total_orders' => $totalOrders,
                'completed_deliveries' => $completedDeliveries,
                'active_deliveries' => $activeDeliveries,
                'pending_orders' => $pendingOrders,
                'failed_deliveries' => $deliveryCanceled,
                'rejected_orders' => $rejectedOrders,
            ];

            $query = $wpdb->prepare(
                "SELECT
                    COUNT(s.status_name) AS status_count,
                    SUM(CASE WHEN s.status_name = 'Pending' THEN 1 ELSE 0 END) AS pending_count,  
                    SUM(CASE WHEN s.status_name IN ('Expired', 'Delivery Canceled') THEN 1 ELSE 0 END) AS failed_count,
                    SUM(CASE WHEN s.status_name = 'Rejected' THEN 1 ELSE 0 END) AS rejected_count,  
                    SUM(CASE WHEN s.status_name = 'Delivered Successfully' THEN 1 ELSE 0 END) AS completed_count,
                    SUM(CASE WHEN s.status_name = 'Processed' THEN 1 ELSE 0 END) AS processed_count,  
                    DATE_FORMAT(o.ordered_on, '%%M %%Y') AS chart_label
                FROM {$order_table} AS o
                INNER JOIN {$status_table} AS s 
                ON o.status_id = s.status_id
                INNER JOIN {$transaction_table} AS t 
                ON o.transaction_id = t.transaction_id
                WHERE o.ordered_on BETWEEN %s AND %s
                GROUP BY chart_label
                ORDER BY o.ordered_on ASC",
                $from,
                $to
            );

            $chartData = $wpdb->get_results($query, ARRAY_A);
            $results['chart_data'] = $chartData;


            // Commit transaction
            $wpdb->query('COMMIT');

            return rest_ensure_response($results);
        } catch (\Exception $e) {
            // Rollback transaction on error
            $wpdb->query('ROLLBACK');
            // Log the error
            error_log('Error fetching dashboard data ' . $e->getMessage());
            return new WP_REST_Response(['message' => 'An error occurred while fetching data.'], 500);
        }


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
        $order_table = $wpdb->prefix . 'wc_lalamove_orders';
        $status_table = $wpdb->prefix . 'wc_lalamove_status';
        $transaction_table = $wpdb->prefix . 'wc_lalamove_transaction';
        $cost_details_table = $wpdb->prefix . 'wc_lalamove_cost_details';
        $balance_table = $wpdb->prefix . 'wc_lalamove_balance';

        $wpdb->query('START TRANSACTION');  

        try{

            $from = $data['from'];
            $to = $data['to'];
            $results = [];

            $query = "SELECT
                        o.wc_order_id,
                        o.lalamove_order_id,
                        t.ordered_by,
                        t.service_type,
                        CONCAT(c.currency, ' ', c.total) AS overall_expense,
                        s.status_name
                        FROM $order_table AS o
                        INNER JOIN $status_table AS s 
                            ON o.status_id = s.status_id
                        INNER JOIN $transaction_table AS t 
                            ON o.transaction_id = t.transaction_id
                        INNER JOIN $cost_details_table AS c 
                            ON t.cost_details_id = c.cost_details_id
                        WHERE o.ordered_on BETWEEN '$from' AND '$to'
                        ORDER BY o.ordered_on DESC
            ";
            $results['table'] = $wpdb->get_results($query, ARRAY_A);

            foreach ($results['table'] as &$row) {
                $order = \wc_get_order($row['wc_order_id']);
            
                $row['payment_details'] = ($order ? $order->get_payment_method() : 'Unknown') . '<br>' . ($order ? $order->get_payment_method_title() : 'Unknown');

            }

            unset($row);
            
            $query = "SELECT
                        SUM(c.total) + SUM(c.subsidy) + SUM(c.priority_fee) AS total_spending,
                        SUM(c.subsidy) + SUM(c.priority_fee) AS net_spending,
                        SUM(c.total) AS total_customer_spending,
                        SUM(c.base) AS base_delivery_cost,
                        SUM(c.subsidy) AS total_subsidy_spending,
                        SUM(c.priority_fee) AS priority_fee_spending
                        FROM $order_table AS o
                        INNER JOIN $status_table AS s 
                            ON o.status_id = s.status_id
                        INNER JOIN $transaction_table AS t 
                            ON o.transaction_id = t.transaction_id
                        INNER JOIN $cost_details_table AS c 
                            ON t.cost_details_id = c.cost_details_id
                        WHERE o.ordered_on BETWEEN '$from' AND '$to'
                        ORDER BY o.ordered_on DESC
            ";

            $results['kpi'] = $wpdb->get_results($query, ARRAY_A);


            $query = $wpdb->prepare(
                "SELECT
                    SUM(CASE 
                        WHEN t.service_type IN ('MOTORCYCLE') THEN 1 
                        ELSE 0 
                    END) AS motorcycle_count,
                    SUM(CASE 
                        WHEN t.service_type IN ('SEDAN', 'SEDAN_INTERCITY', 'MPV', 'MPV_INTERCITY') THEN 1 
                        ELSE 0 
                    END) AS motor_vehicle_count,
                    SUM(CASE 
                        WHEN t.service_type IN ('VAN', 'VAN_INTERCITY', 'VAN1000') THEN 1 
                        ELSE 0 
                    END) AS van_count,
                    SUM(CASE 
                        WHEN t.service_type IN ('2000KG_ALUMINUM_LD', '2000KG_FB_LD') THEN 1 
                        ELSE 0 
                    END) AS heavy_truck_count,
                    SUM(CASE 
                        WHEN t.service_type IN ('TRUCK550', '10WHEEL_TRUCK', 'LD_10WHEEL_TRUCK') THEN 1 
                        ELSE 0 
                    END) AS truck_count,
                    SUM(c.total) + SUM(c.subsidy) + SUM(c.priority_fee) AS total_spending,
                    SUM(c.subsidy) + SUM(c.priority_fee) AS net_spending,
                    SUM(c.total) AS total_customer_spending,
                    SUM(c.subsidy) AS total_subsidy_spending,
                    SUM(c.base) AS base_delivery_cost,
                    SUM(c.priority_fee) AS priority_fee_spending,
                    SUM(c.surcharge) AS surcharge_spending,
                    (SELECT SUM(wallet_balance) 
                     FROM {$balance_table} 
                     WHERE updated_on BETWEEN %s AND %s) AS wallet_balance,
                    DATE_FORMAT(o.ordered_on, '%M %Y') AS chart_label
                FROM {$order_table} AS o
                INNER JOIN {$transaction_table} AS t 
                    ON o.transaction_id = t.transaction_id
                INNER JOIN {$cost_details_table} AS c 
                    ON t.cost_details_id = c.cost_details_id
                WHERE o.ordered_on BETWEEN %s AND %s
                GROUP BY chart_label
                ORDER BY o.ordered_on ASC",
                $from, $to, $from, $to
            );
            
            

            $chartData = $wpdb->get_results($query, ARRAY_A);
            $results['chart_data'] = $chartData;
            


            // Commit transaction
            $wpdb->query('COMMIT');

            return rest_ensure_response($results);
        } catch (\Exception $e) {
            // Rollback transaction on error
            $wpdb->query('ROLLBACK');
            // Log the error
            error_log('Error fetching dashboard data ' . $e->getMessage());
            return new WP_REST_Response(['message' => 'An error occurred while fetching data.'], 500);
        }


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
                    
                $orderedOn = new \DateTime($result['ordered_on']);
                $scheduledOn = new \DateTime($result['scheduled_on']);

                $result['ordered_on'] = $orderedOn->format('F j, Y') . '<br>' . $orderedOn->format('g:i A');
                $result['scheduled_on'] = $scheduledOn->format('F j, Y') . '<br>' . $scheduledOn->format('g:i A');


                $wc_order = \wc_get_order($result['wc_order_id']);
                if ($wc_order) {
                    $result['customer_email'] = $wc_order->get_billing_email();
                    $result['customer_phone'] = $wc_order->get_billing_phone();
                    $result['quantity'] = $wc_order->get_item_count();
                } else {
                    $result['customer_email'] = null;
                    $result['customer_phone'] = null;
                    $result['quantity'] = null;
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
        $environment = get_option('lalamove_environment', 'sandbox');

        if ($environment === 'production') 
        {
            $secret = get_option('lalamove_production_api_secret', '');
        } 
        else
        {
            $secret = get_option('lalamove_sandbox_api_secret', '');
        }

        // Ensure the secret key is not empty
        if (empty($secret)) {
            return new WP_REST_Response(['message' => 'Invalid secret configuration'], 500);
        }

        // Get the raw body of the request
        $payload = $request->get_body();
        $requestBody = json_decode($payload, true);

        // Validate JSON payload
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_REST_Response(['message' => 'Invalid JSON'], 400);
        }

        // Ensure required fields are present
        if (!isset($requestBody['signature'], $requestBody['timestamp'])) {
            return new WP_REST_Response(['message' => 'Missing required fields'], 400);
        }

        $signature = $requestBody['signature'];
        $timestamp = $requestBody['timestamp'];
        $body = json_encode($requestBody['data'], JSON_UNESCAPED_SLASHES);
        // Construct the rawSignature string

        $httpVerb = 'POST'; // Assuming POST method is used
        $path = '/wp-json/woo-lalamove/v1/lalamove-webhook'; 
        $rawSignature = "{$timestamp}\r\n{$httpVerb}\r\n{$path}\r\n\r\n{$body}";

        // Debugging log for rawSignature
        error_log('Raw signature: ' . $rawSignature);

        // Compute the HMAC-SHA256 signature
        $computedSignature = hash_hmac('sha256', $rawSignature, $secret);

        // Compare the provided signature with the computed signature
        if ($signature !== $computedSignature) {
            return new WP_REST_Response(
                [
                    'signature' => $signature,
                    'DATA' => $requestBody['data'],
                    'computedSignature' => $computedSignature,
                    'message' => 'Invalid signature',
                ], 
                403
            );
        }

        // Process the webhook event and return a successful response
        return new WP_REST_Response(['message' => 'Webhook received successfully'], 200);
    }

}
