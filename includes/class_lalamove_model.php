<?php 

namespace Sevhen\WooLalamove;

use Exception;

if (!defined('ABSPATH'))
    exit;

use WP_REST_Request;
use WP_REST_Response;

class Class_Lalamove_Model{
    private $order_table;
    private $status_table;
    private $transaction_table;
    private $cost_details_table ;
    private $balance_table;

    public function __construct(){
        global $wpdb;

        $this->order_table = $wpdb->prefix . 'wc_lalamove_orders';
        $this->status_table = $wpdb->prefix . 'wc_lalamove_status';
        $this->transaction_table = $wpdb->prefix . 'wc_lalamove_transaction'; 
        $this->cost_details_table = $wpdb->prefix . 'wc_lalamove_cost_details';
        $this->balance_table = $wpdb->prefix . 'wc_lalamove_balance';
    }

    protected function get_orders($wpdb){
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
                    FROM $this->order_table AS o
                    INNER JOIN $this->status_table AS s 
                    ON o.status_id = s.status_id
                    INNER JOIN $this->transaction_table AS t 
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

            $wpdb->query('COMMIT');

            return $results;
        } catch (\Exception $e) {
            $wpdb->query('ROLLBACK');

            // Log the error
            error_log('Error fetching Lalamove orders: ' . $e->getMessage());

            return new WP_REST_Response(['message' => 'An error occurred while fetching orders.'], 500);
        }
    }
    protected function get_records_data($wpdb, $data){
        $wpdb->query('START TRANSACTION');  

        try{

            $from = $data['from'];
            $to = $data['to'];
            $status = isset($data['status']) ? $data['status'] : null;
            $search_input = isset($data['search_input']) ? $data['search_input'] : null;
            $results = [];
            
            $query = "SELECT
                        o.wc_order_id,
                        o.lalamove_order_id,
                        o.ordered_on,
                        o.scheduled_on,
                        o.drop_off_location,
                        o.order_json_body,
                        s.status_name,
                        t.ordered_by,
                        t.service_type
                    FROM $this->order_table AS o
                    INNER JOIN $this->status_table AS s 
                        ON o.status_id = s.status_id
                    INNER JOIN $this->transaction_table AS t 
                        ON o.transaction_id = t.transaction_id
                    WHERE o.ordered_on BETWEEN '$from' AND '$to'";
            
            // **Conditionally apply status filtering**
            if (isset($status) && $status !== 'ALL' && $status !== '') {
                $query .= " AND s.status_name = '$status'";
            }
            
            
            // **Conditionally apply search filtering**
            if (isset($search_input)) {
                $query .= " AND (o.drop_off_location LIKE '%" . esc_sql($search_input) . "%' OR o.lalamove_order_id LIKE '%" . esc_sql($search_input) . "%')";
            }
            
            $query .= " ORDER BY o.ordered_on DESC";
            
            $results['records'] = $wpdb->get_results($query, ARRAY_A);



            // Commit transaction
            $wpdb->query('COMMIT');

            wp_send_json($wpdb->get_results($query, ARRAY_A));
            exit;
            
        } catch (\Exception $e) {
            // Rollback transaction on error
            $wpdb->query('ROLLBACK');
            // Log the error
            error_log('Error fetching records data ' . $e->getMessage());
            return new WP_REST_Response(['message' => 'An error occurred while fetching data.'], 500);
        }
    }

    protected function get_dashboard_orders_data($wpdb, $data){
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
                    FROM $this->order_table AS o
                    INNER JOIN $this->status_table AS s 
                    ON o.status_id = s.status_id
                    INNER JOIN $this->transaction_table AS t 
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
                    case 'awaiting driver':
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
                FROM {$this->order_table} AS o
                INNER JOIN {$this->status_table} AS s 
                ON o.status_id = s.status_id
                INNER JOIN {$this->transaction_table} AS t 
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

            return $results;
        } catch (\Exception $e) {
            // Rollback transaction on error
            $wpdb->query('ROLLBACK');
            // Log the error
            error_log('Error fetching dashboard data ' . $e->getMessage());
            return new WP_REST_Response(['message' => 'An error occurred while fetching data.'], 500);
        }
    }

    protected function get_dashboard_spending_data($wpdb, $data){
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
                        s.status_name,
                        DATE_FORMAT(o.ordered_on, '%M %d, %Y') AS ordered_on
                        FROM $this->order_table AS o
                        INNER JOIN $this->status_table AS s 
                            ON o.status_id = s.status_id    
                        INNER JOIN $this->transaction_table AS t 
                            ON o.transaction_id = t.transaction_id
                        INNER JOIN $this->cost_details_table AS c 
                            ON t.cost_details_id = c.cost_details_id
                        WHERE o.ordered_on BETWEEN '$from' AND '$to'
                        ORDER BY o.ordered_on DESC
            ";
            $results['table'] = $wpdb->get_results($query, ARRAY_A);

            foreach ($results['table'] as &$row) {
                $order = \wc_get_order($row['wc_order_id']);
            
                $row['payment_method'] = $order ? $order->get_payment_method_title() : 'Unknown';

            }

            unset($row);
            
            $query = "SELECT
                        SUM(c.total) + SUM(c.subsidy) + SUM(c.priority_fee) AS total_spending,
                        SUM(c.subsidy) + SUM(c.priority_fee) AS net_spending,
                        SUM(c.total) AS total_customer_spending,
                        SUM(c.base) AS base_delivery_cost,
                        SUM(c.subsidy) AS total_subsidy_spending,
                        SUM(c.priority_fee) AS priority_fee_spending
                        FROM $this->order_table AS o
                        INNER JOIN $this->status_table AS s 
                            ON o.status_id = s.status_id
                        INNER JOIN $this->transaction_table AS t 
                            ON o.transaction_id = t.transaction_id
                        INNER JOIN $this->cost_details_table AS c 
                            ON t.cost_details_id = c.cost_details_id
                        WHERE o.ordered_on BETWEEN '$from' AND '$to'
                        ORDER BY o.ordered_on DESC
            ";

            $results['kpi'] = $wpdb->get_results($query, ARRAY_A);


            $query = $wpdb->prepare(
                "SELECT
                    SUM(CASE WHEN t.service_type IN ('MOTORCYCLE') THEN 1 ELSE 0 END) AS motorcycle_count,
                    SUM(CASE WHEN t.service_type IN ('SEDAN', 'SEDAN_INTERCITY', 'MPV', 'MPV_INTERCITY') THEN 1 ELSE 0 END) AS motor_vehicle_count,
                    SUM(CASE WHEN t.service_type IN ('VAN', 'VAN_INTERCITY', 'VAN1000') THEN 1 ELSE 0 END) AS van_count,
                    SUM(CASE WHEN t.service_type IN ('2000KG_ALUMINUM_LD', '2000KG_FB_LD') THEN 1 ELSE 0 END) AS heavy_truck_count,
                    SUM(CASE WHEN t.service_type IN ('TRUCK550', '10WHEEL_TRUCK', 'LD_10WHEEL_TRUCK') THEN 1 ELSE 0 END) AS truck_count,
                    SUM(c.total) + SUM(c.subsidy) + SUM(c.priority_fee) AS total_spending,
                    SUM(c.subsidy) + SUM(c.priority_fee) AS net_spending,
                    SUM(c.total) AS total_customer_spending,
                    SUM(c.subsidy) AS total_subsidy_spending,
                    SUM(c.base) AS base_delivery_cost,
                    SUM(c.priority_fee) AS priority_fee_spending,
                    SUM(c.surcharge) AS surcharge_spending,
                    COALESCE(
                        (SELECT wallet_balance 
                         FROM {$this->balance_table} 
                         WHERE updated_on BETWEEN 
                             STR_TO_DATE(CONCAT(DATE_FORMAT(o.ordered_on, '%%M %%Y'), ' 01'), '%%M %%Y %%d') 
                             AND LAST_DAY(STR_TO_DATE(CONCAT(DATE_FORMAT(o.ordered_on, '%%M %%Y'), ' 01'), '%%M %%Y %%d'))
                         ORDER BY updated_on DESC 
                         LIMIT 1), 
                        0
                    ) AS wallet_balance,
                    DATE_FORMAT(o.ordered_on, '%%M %%Y') AS chart_label
                FROM {$this->order_table} AS o
                INNER JOIN {$this->transaction_table} AS t ON o.transaction_id = t.transaction_id
                INNER JOIN {$this->cost_details_table} AS c ON t.cost_details_id = c.cost_details_id
                WHERE o.ordered_on BETWEEN %s AND %s
                GROUP BY chart_label
                ORDER BY o.ordered_on ASC",
                $from, $to
            );
            
            

            $chartData = $wpdb->get_results($query, ARRAY_A);
            $results['chart_data'] = $chartData;
            


            // Commit transaction
            $wpdb->query('COMMIT');

            return $results;
        } catch (\Exception $e) {
            // Rollback transaction on error
            $wpdb->query('ROLLBACK');
            // Log the error
            error_log('Error fetching dashboard data ' . $e->getMessage());
            return new WP_REST_Response(['message' => 'An error occurred while fetching data.'], 500);
        } 
    }

    protected function get_lalamove_order_body ($wpdb, $data){
        
        
        $wpdb->query('START TRANSACTION');  
        
        try{
            
            $lala_id = $data['lala_id'];
            
            $results = [];
            
            $query = "SELECT
                        o.scheduled_on,
                        o.order_json_body,
                        t.service_type
                    FROM $this->order_table AS o
                    INNER JOIN $this->status_table AS s 
                        ON o.status_id = s.status_id
                    INNER JOIN $this->transaction_table AS t 
                        ON o.transaction_id = t.transaction_id
                    WHERE o.lalamove_order_id = $lala_id";
            
            // Commit transaction
            $wpdb->query('COMMIT');

            wp_send_json($wpdb->get_results($query, ARRAY_A));
            exit;
            
        } catch (\Exception $e) {
            // Rollback transaction on error
            $wpdb->query('ROLLBACK');
            // Log the error
            error_log('Error fetching records data ' . $e->getMessage());
            return new WP_REST_Response(['message' => 'An error occurred while fetching data.'], 500);
        }


    } 

    protected function handle_webhook($wpdb, $request){
          
        // Fetch secret from options
        $environment = get_option('lalamove_environment', 'sandbox');
        $secret = ($environment === 'production')
                  ? get_option('lalamove_production_api_secret', '')
                  : get_option('lalamove_sandbox_api_secret');
    
        if (empty($secret)) {
            error_log('Invalid secret configuration');
            return;
        }
    
        // Parse and validate payload
        $payload = $request->get_body();
        $requestBody = json_decode($payload, true);
    
        if (json_last_error() !== JSON_ERROR_NONE || !isset($requestBody['signature'], $requestBody['timestamp'])) {
            error_log('Invalid or incomplete webhook payload received: ' . $payload);
            return;
        }
        // Validate webhook signature
        $signature = $requestBody['signature'];
        $timestamp = $requestBody['timestamp'];
        $body = json_encode(value: $requestBody['data'] ?? [], flags: JSON_UNESCAPED_SLASHES);
        $httpVerb = 'POST';
        $path = '/wp-json/woo-lalamove/v1/lalamove-webhook';
        $rawSignature = "{$timestamp}\r\n{$httpVerb}\r\n{$path}\r\n\r\n{$body}";
        $computedSignature = hash_hmac('sha256', $rawSignature, $secret);
    
        if ($signature !== $computedSignature) {
            error_log('Signature mismatch detected. Provided: ' . $signature . ' | Computed: ' . $computedSignature);
            return;
        }
    
        error_log("Webhook validated successfully: " . print_r($requestBody, true));
    
        // Process eventType dynamically
        $eventType = $requestBody['eventType'] ?? 'UNKNOWN';
        $data = $requestBody['data'] ?? [];
    
        switch ($eventType) {
            case "ORDER_STATUS_CHANGED":
                $this->handle_order_status_changed($wpdb, $data);
                break;
    
            case "DRIVER_ASSIGNED":
                $this->handle_driver_assigned($data);
                break;
    
            case "ORDER_AMOUNT_CHANGED":
                $this->handle_order_amount_changed($data);
                break;
    
            case "ORDER_REPLACED":
                $this->handle_order_replaced($data);
                break;
    
            case "WALLET_BALANCE_CHANGED":
                $this->handle_wallet_balance_changed($wpdb, $data);
                break;
    
            default:
                error_log('Unhandled event type: ' . $eventType);
                break;
        }
    }


    private function handle_wallet_balance_changed($wpdb, $data)
    {
        $currency = $data['balance']['currency'] ?? 'Unknown';
        $balance = $data['balance']['amount'] ?? 0;
        $updated_on = $data['updatedAt'] ?? current_time('mysql');
    
        try {
            $wpdb->query('START TRANSACTION');
    
            $wpdb->insert(
                $this->balance_table,
                [
                    'balance_currency' => $currency,
                    'wallet_balance' => $balance,
                    'updated_on' => $updated_on,
                ]
            );
    
            $balance_id = $wpdb->insert_id;
    
            if (!$balance_id) {
                throw new Exception("Failed to insert balance data.");
            }
    
            $wpdb->query('COMMIT');
            error_log('Wallet balance updated successfully.');
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log('Error updating wallet balance: ' . $e->getMessage());
        }
    }

    
    private function handle_order_status_changed($wpdb, $data)
    {

        $lala_order_id = $data['order']['orderId'];
        $status_id = 0;
        $status = $data['order']['status'];
        $allow_update = true;

        switch($status){

            case 'ASSIGNING_DRIVER':
                $status_id = 2;
             break;
            case 'ON_GOING':
                $status_id = 3;
             break;
            case 'PICKED_UP':
                $status_id = 4;
             break;
            case 'COMPLETED':
                $status_id = 5;
             break;
            case 'REJECTED':
                $status_id = 6;
             break;
            case 'CANCELED':
                $status_id = 7;
             break;
            case 'EXPIRED':
                $status_id = 8;
             break;
            default:
                $allow_update = false;

        }



        try {

            $wpdb->query('START TRANSACTION');

            if(!$allow_update){
                throw new Exception("Unknown status received: ". $status);
            }

            $wpdb->update(
                $this->order_table,
                [
                    'status_id' => $status_id
                ],
                [
                    'lalamove_order_id' => $lala_order_id
                ],
                [
                    '%d'
                ],
                [
                    '%d'
                ]
            );

            $wpdb->query('COMMIT');

        } catch(Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log('LALAMOVE: ' . $e->getMessage());
        }

        error_log(message: 'Processing ORDER_STATUS_CHANGED event: ' . print_r($data, true));
    }
    
    private function handle_driver_assigned($data)
    {
        error_log('Processing DRIVER_ASSIGNED event: ' . print_r($data, true));
    }
    
    private function handle_order_amount_changed($data)
    {
        error_log('Processing ORDER_AMOUNT_CHANGED event: ' . print_r($data, true));
    }
    
    private function handle_order_replaced($data)
    {
        error_log('Processing ORDER_REPLACED event: ' . print_r($data, true));
    }
    

}

