<?php

namespace Sevhen\WooLalamove;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class to integrate Lalamove shipping data with WooCommerce Analytics
 */
class Class_Lalamove_Analytics {
    
    public function __construct() {
        // Hook into WooCommerce Analytics
        add_filter('woocommerce_analytics_orders_select_query', [$this, 'add_shipping_data_to_orders_query'], 10, 2);
        add_filter('woocommerce_analytics_orders_stats_select_query', [$this, 'add_shipping_data_to_stats_query'], 10, 2);
        
        // Add export columns
        add_filter('woocommerce_report_orders_export_columns', [$this, 'add_export_columns']);
        add_filter('woocommerce_report_orders_prepare_export_item', [$this, 'prepare_export_item'], 10, 2);
        
        // Enqueue analytics JavaScript
        add_action('admin_enqueue_scripts', [$this, 'enqueue_analytics_scripts']);
        
        // Add REST API endpoint for shipping analytics
        add_action('rest_api_init', [$this, 'register_analytics_endpoints']);
    }
    
    /**
     * Add shipping payment data to orders analytics query
     */
    public function add_shipping_data_to_orders_query($results, $args) {
        if (!$results || !isset($results->data) || empty($results->data)) {
            return $results;
        }
        
        foreach ($results->data as $key => $result) {
            $order_id = isset($result['order_id']) ? $result['order_id'] : (isset($result->order_id) ? $result->order_id : null);
            
            if (!$order_id) continue;
            
            // Get Lalamove shipping payment details
            $payment_details = get_shipping_payment_details($order_id);
            $order = wc_get_order($order_id);
            
            if ($order) {
                $shipping_total = $order->get_shipping_total();
                $lalamove_id = get_lala_id($order_id);
                
                // Determine shipping payment type
                $shipping_payment_type = 'none';
                $shipping_profit_loss = 0;
                $actual_shipping_cost = 0;
                
                if ($lalamove_id) {
                    if ($shipping_total == 0) {
                        $shipping_payment_type = 'free';
                    } elseif ($payment_details && $payment_details['paid_by'] === 'admin') {
                        $shipping_payment_type = 'admin_paid';
                        $actual_shipping_cost = floatval($payment_details['actual_cost']);
                        $shipping_profit_loss = floatval($payment_details['profit_loss']);
                    } elseif ($payment_details && $payment_details['paid_by'] === 'customer') {
                        $shipping_payment_type = 'customer_paid';
                        $actual_shipping_cost = floatval($payment_details['actual_cost']);
                        $shipping_profit_loss = $shipping_total - $actual_shipping_cost;
                    }
                }
                
                // Add shipping data to results
                if (is_array($results->data[$key])) {
                    $results->data[$key]['lalamove_shipping_type'] = $shipping_payment_type;
                    $results->data[$key]['lalamove_actual_cost'] = $actual_shipping_cost;
                    $results->data[$key]['lalamove_profit_loss'] = $shipping_profit_loss;
                    $results->data[$key]['lalamove_customer_paid'] = $shipping_total;
                } else {
                    $results->data[$key]->lalamove_shipping_type = $shipping_payment_type;
                    $results->data[$key]->lalamove_actual_cost = $actual_shipping_cost;
                    $results->data[$key]->lalamove_profit_loss = $shipping_profit_loss;
                    $results->data[$key]->lalamove_customer_paid = $shipping_total;
                }
            }
        }
        
        return $results;
    }
    
    /**
     * Add shipping data to stats query
     */
    public function add_shipping_data_to_stats_query($results, $args) {
        // For stats, we'll calculate totals
        if (!$results || !isset($results->data) || empty($results->data)) {
            return $results;
        }
        
        // Calculate shipping totals for the period
        $shipping_stats = $this->calculate_shipping_stats($args);
        
        foreach ($results->data as $key => $result) {
            if (is_array($results->data[$key])) {
                $results->data[$key]['lalamove_total_admin_cost'] = $shipping_stats['total_admin_cost'];
                $results->data[$key]['lalamove_total_profit_loss'] = $shipping_stats['total_profit_loss'];
                $results->data[$key]['lalamove_admin_paid_orders'] = $shipping_stats['admin_paid_orders'];
            } else {
                $results->data[$key]->lalamove_total_admin_cost = $shipping_stats['total_admin_cost'];
                $results->data[$key]->lalamove_total_profit_loss = $shipping_stats['total_profit_loss'];
                $results->data[$key]->lalamove_admin_paid_orders = $shipping_stats['admin_paid_orders'];
            }
        }
        
        return $results;
    }
    
    /**
     * Calculate shipping statistics for a period
     */
    private function calculate_shipping_stats($args) {
        global $wpdb;
        
        $start_date = isset($args['after']) ? $args['after'] : date('Y-m-01');
        $end_date = isset($args['before']) ? $args['before'] : date('Y-m-d');
        
        // Get orders with Lalamove shipping in date range
        $orders = $wpdb->get_results($wpdb->prepare("
            SELECT p.ID as order_id
            FROM {$wpdb->posts} p
            WHERE p.post_type = 'shop_order'
            AND p.post_status IN ('wc-processing', 'wc-completed', 'wc-shipped')
            AND DATE(p.post_date) BETWEEN %s AND %s
            AND EXISTS (
                SELECT 1 FROM {$wpdb->prefix}wc_lalamove_orders lo 
                WHERE lo.wc_order_id = p.ID
            )
        ", $start_date, $end_date));
        
        $stats = [
            'total_admin_cost' => 0,
            'total_profit_loss' => 0,
            'admin_paid_orders' => 0,
        ];
        
        foreach ($orders as $order_data) {
            $payment_details = get_shipping_payment_details($order_data->order_id);
            
            if ($payment_details && $payment_details['paid_by'] === 'admin') {
                $stats['total_admin_cost'] += floatval($payment_details['actual_cost']);
                $stats['total_profit_loss'] += floatval($payment_details['profit_loss']);
                $stats['admin_paid_orders']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Add shipping payment columns to CSV export
     */
    public function add_export_columns($export_columns) {
        $export_columns['lalamove_shipping_type'] = 'Shipping Payment Type';
        $export_columns['lalamove_actual_cost'] = 'Actual Shipping Cost';
        $export_columns['lalamove_profit_loss'] = 'Shipping Profit/Loss';
        $export_columns['lalamove_customer_paid'] = 'Customer Paid';
        
        return $export_columns;
    }
    
    /**
     * Prepare export item data
     */
    public function prepare_export_item($export_item, $item) {
        $export_item['lalamove_shipping_type'] = isset($item['lalamove_shipping_type']) ? $item['lalamove_shipping_type'] : '';
        $export_item['lalamove_actual_cost'] = isset($item['lalamove_actual_cost']) ? $item['lalamove_actual_cost'] : 0;
        $export_item['lalamove_profit_loss'] = isset($item['lalamove_profit_loss']) ? $item['lalamove_profit_loss'] : 0;
        $export_item['lalamove_customer_paid'] = isset($item['lalamove_customer_paid']) ? $item['lalamove_customer_paid'] : 0;
        
        return $export_item;
    }
    
    /**
     * Enqueue analytics JavaScript
     */
    public function enqueue_analytics_scripts($hook) {
        // Only load on WooCommerce analytics pages
        if (strpos($hook, 'wc-admin') === false && strpos($hook, 'woocommerce') === false) {
            return;
        }
        
        $screen = get_current_screen();
        if (!$screen || !in_array($screen->id, ['woocommerce_page_wc-admin', 'toplevel_page_woocommerce'])) {
            return;
        }
        
        wp_enqueue_script(
            'lalamove-analytics',
            plugin_dir_url(dirname(__FILE__)) . 'assets/js/lalamove-analytics-simple.js',
            ['wp-hooks', 'wp-i18n'],
            '1.0.0',
            true
        );
        
        wp_localize_script('lalamove-analytics', 'lalamoveAnalytics', [
            'currency_symbol' => get_woocommerce_currency_symbol(),
            'currency_code' => get_woocommerce_currency(),
        ]);
    }
    
    /**
     * Register REST API endpoints for analytics
     */
    public function register_analytics_endpoints() {
        register_rest_route('lalamove/v1', '/analytics/shipping', [
            'methods' => 'GET',
            'callback' => [$this, 'get_shipping_analytics_endpoint'],
            'permission_callback' => function() {
                return current_user_can('view_woocommerce_reports');
            },
            'args' => [
                'after' => [
                    'description' => 'Start date',
                    'type' => 'string',
                    'format' => 'date',
                ],
                'before' => [
                    'description' => 'End date',
                    'type' => 'string',
                    'format' => 'date',
                ],
            ],
        ]);
    }
    
    /**
     * REST API endpoint for shipping analytics
     */
    public function get_shipping_analytics_endpoint($request) {
        $start_date = $request->get_param('after') ?: date('Y-m-01');
        $end_date = $request->get_param('before') ?: date('Y-m-d');
        
        $analytics = get_shipping_analytics($start_date, $end_date);
        
        return rest_ensure_response($analytics);
    }
}