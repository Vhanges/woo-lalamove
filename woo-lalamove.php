<?php 
/**
 * Plugin Name: WooCommerce Lalamove Extension
 * Version: 1.0
 * Author: Angelo Sevhen
 */

if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'vendor/autoload.php';
require_once plugin_dir_path(__FILE__) . 'cors.php';


use Sevhen\WooLalamove\Class_Lalamove_Settings;
use Sevhen\WooLalamove\Class_Lalamove_Endpoints;
use Sevhen\WooLalamove\Class_Lalamove_Api;

new Class_Lalamove_Settings();
new Class_Lalamove_Endpoints();
new Class_Lalamove_Api();

// Enable CORS for all requests
add_action('rest_api_init', function () {
    enableCORS();
});

function enqueue_vue_assets($hook) {
    if ($hook !== 'toplevel_page_woo-lalamove') {
        return;
    }

    //Enqueue main script with dependencies
    // wp_enqueue_script(
    //     'woo-lalamove',
    //     plugin_dir_url(__FILE__) . 'assets/js/dist/bundle.js',
    //     ['vue', 'jquery', 'vue-router'],
    //     filemtime(plugin_dir_path(__FILE__) . 'assets/js/dist/bundle.js'),
    //     true
    // );

    // wp_enqueue_script(
    //     'test-script',
    //     plugin_dir_url(__FILE__) . 'assets/js/dist/bundle.js',
    //     [],
    //     null,
    //     true
    // );

    wp_enqueue_script(
        'woo-lalamove',
        plugin_dir_url(__FILE__) . 'assets/js/dist/bundle.js',
        [],
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/dist/bundle.js'),
        true
    );
    
    // Enqueue Material Symbols font
    wp_enqueue_style('material-symbols', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined');

    // Critical security nonce - MUST stay in
    wp_localize_script('woo-lalamove', 'wooLalamoveData', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('woo_lalamove_nonce') // Restored nonce
    ]);

    // Enqueue CSS only on this admin page
    wp_enqueue_style(
        'woo-lalamove-styles',
        plugin_dir_url(__FILE__) . 'assets/css/admin.css',
        [],
        filemtime(plugin_dir_path(__FILE__) . 'assets/css/admin.css')
    );
}
add_action('admin_enqueue_scripts', 'enqueue_vue_assets');

function woo_lalamove_add_admin_page() {
    add_menu_page(
        'Lalamove Settings',
        'Lalamove',
        'manage_options',
        'woo-lalamove',
        'woo_lalamove_render_admin_page',
        'dashicons-admin-site',
        25
    );
}
add_action('admin_menu', 'woo_lalamove_add_admin_page');


function woo_lalamove_render_admin_page() {
    ?>

    <div id="lalamove-app">

        
    </div>
    <!-- Add this hidden nonce field -->
    <input type="hidden" 
           id="woo_lalamove_form_nonce" 
           value="<?php echo wp_create_nonce('woo_lalamove_form_action'); ?>">
    <?php
}


