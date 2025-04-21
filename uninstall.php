<?php
// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

$order_table = $wpdb->prefix . 'wc_lalamove_orders';
$status_table = $wpdb->prefix . 'wc_lalamove_status';
$transaction_table = $wpdb->prefix . 'wc_lalamove_transaction'; 
$cost_details_table = $wpdb->prefix . 'wc_lalamove_cost_details';
$balance_table = $wpdb->prefix . 'wc_lalamove_balance';
// Drop the table.
$wpdb->query("DROP TABLE IF EXISTS $order_table");
$wpdb->query("DROP TABLE IF EXISTS $status_table");
$wpdb->query("DROP TABLE IF EXISTS $transaction_table");
$wpdb->query("DROP TABLE IF EXISTS $cost_details_table");
$wpdb->query("DROP TABLE IF EXISTS $balance_table");
