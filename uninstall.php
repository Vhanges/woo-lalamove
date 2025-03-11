<?php
// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'wc_lalamove_orders';

// Drop the table.
$wpdb->query("DROP TABLE IF EXISTS $table_name");
