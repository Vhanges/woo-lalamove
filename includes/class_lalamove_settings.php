<?php
namespace Sevhen\WooLalamove;

use Sevhen\WooLalamove\Class_Lalamove_Api;

class Class_Lalamove_Settings {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    /**
     * Adds a submenu page under the WooCommerce menu.
     */
    public function add_settings_page() {
        add_submenu_page(
            'woocommerce',                   // Parent slug (under WooCommerce)
            'Lalamove API',                  // Page title
            'Lalamove',                      // Menu title
            'manage_options',                // Capability required
            'woo-lalamove-settings',         // Menu slug
            [$this, 'settings_page_html']    // Callback to render the page
        );
    }

    /**
     * Registers settings, sections, and fields.
     */
    public function register_settings() {
        // Register settings for Sandbox and Production credentials, URLs, environment, and market.
        register_setting('woo_lalamove_settings_group', 'lalamove_sandbox_api_key');
        register_setting('woo_lalamove_settings_group', 'lalamove_sandbox_api_secret');
        register_setting('woo_lalamove_settings_group', 'lalamove_production_api_key');
        register_setting('woo_lalamove_settings_group', 'lalamove_production_api_secret');
        register_setting('woo_lalamove_settings_group', 'lalamove_sandbox_url');
        register_setting('woo_lalamove_settings_group', 'lalamove_production_url');
        register_setting('woo_lalamove_settings_group', 'lalamove_environment');
        register_setting('woo_lalamove_settings_group', 'lalamove_market', [
            'sanitize_callback' => 'sanitize_text_field',
            'default'           => '',
            'show_in_rest'      => false,
        ]);

        // Create separate sections for sandbox, production, environment, and market.
        add_settings_section(
            'woo_lalamove_sandbox_section',
            'Sandbox Settings',
            function() { echo '<p>Enter your Sandbox credentials and API URL below.</p>'; },
            'woo-lalamove-settings'
        );

        add_settings_section(
            'woo_lalamove_production_section',
            'Production Settings',
            function() { echo '<p>Enter your Production credentials and API URL below.</p>'; },
            'woo-lalamove-settings'
        );

        add_settings_section(
            'woo_lalamove_environment_section',
            'Environment Selection',
            function() { echo '<p>Select which environment to use.</p>'; },
            'woo-lalamove-settings'
        );
        
        // New section for Market selection
        add_settings_section(
            'woo_lalamove_market_section',
            'Market Selection',
            function() { echo '<p>Select the market provided by Lalamove. Choose "Other" to enter a custom value.</p>'; },
            'woo-lalamove-settings'
        );

        // Sandbox fields
        add_settings_field('lalamove_sandbox_api_key', 'Sandbox API Key', [$this, 'sandbox_api_key_callback'], 'woo-lalamove-settings', 'woo_lalamove_sandbox_section');
        add_settings_field('lalamove_sandbox_api_secret', 'Sandbox API Secret', [$this, 'sandbox_api_secret_callback'], 'woo-lalamove-settings', 'woo_lalamove_sandbox_section');
        add_settings_field('lalamove_sandbox_url', 'Sandbox API URL', [$this, 'sandbox_url_callback'], 'woo-lalamove-settings', 'woo_lalamove_sandbox_section');

        // Production fields
        add_settings_field('lalamove_production_api_key', 'Production API Key', [$this, 'production_api_key_callback'], 'woo-lalamove-settings', 'woo_lalamove_production_section');
        add_settings_field('lalamove_production_api_secret', 'Production API Secret', [$this, 'production_api_secret_callback'], 'woo-lalamove-settings', 'woo_lalamove_production_section');
        add_settings_field('lalamove_production_url', 'Production API URL', [$this, 'production_url_callback'], 'woo-lalamove-settings', 'woo_lalamove_production_section');

        // Environment selection
        add_settings_field('lalamove_environment', 'Environment', [$this, 'environment_callback'], 'woo-lalamove-settings', 'woo_lalamove_environment_section');

        // Market selection field
        add_settings_field('lalamove_market', 'Market', [$this, 'market_callback'], 'woo-lalamove-settings', 'woo_lalamove_market_section');
    }

    /**
     * Renders the settings page HTML.
     */
    public function settings_page_html() {
        if (!current_user_can('manage_options')) {
            return;
        }
        ?>
        <div class="wrap">
            <h1>Lalamove API Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('woo_lalamove_settings_group');
                do_settings_sections('woo-lalamove-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    // Callback functions for Sandbox fields
    public function sandbox_api_key_callback() {
        $value = get_option('lalamove_sandbox_api_key', '');
        echo '<input type="text" name="lalamove_sandbox_api_key" value="' . esc_attr($value) . '" class="regular-text">';
    }
    public function sandbox_api_secret_callback() {
        $value = get_option('lalamove_sandbox_api_secret', '');
        echo '<input type="text" name="lalamove_sandbox_api_secret" value="' . esc_attr($value) . '" class="regular-text">';
    }
    public function sandbox_url_callback() {
        $value = get_option('lalamove_sandbox_url', '');
        echo '<input type="text" name="lalamove_sandbox_url" value="' . esc_attr($value) . '" class="regular-text">';
    }

    // Callback functions for Production fields
    public function production_api_key_callback() {
        $value = get_option('lalamove_production_api_key', '');
        echo '<input type="text" name="lalamove_production_api_key" value="' . esc_attr($value) . '" class="regular-text">';
    }
    public function production_api_secret_callback() {
        $value = get_option('lalamove_production_api_secret', '');
        echo '<input type="password" name="lalamove_production_api_secret" value="' . esc_attr($value) . '" class="regular-text">';
    }
    public function production_url_callback() {
        $value = get_option('lalamove_production_url', '');
        echo '<input type="text" name="lalamove_production_url" value="' . esc_attr($value) . '" class="regular-text">';
    }

    // Callback for Environment selection
    public function environment_callback() {
        $selected = get_option('lalamove_environment', 'sandbox');
        ?>
        <select name="lalamove_environment">
            <option value="sandbox" <?php selected($selected, 'sandbox'); ?>>Sandbox</option>
            <option value="production" <?php selected($selected, 'production'); ?>>Production</option>
        </select>
        <?php
    }
    
    public function market_callback() {
        $value = get_option('lalamove_market', '');
        $markets = [
            'BR' => 'Brasil',
            'HK' => 'Hong Kong, China',
            'ID' => 'Indonesia',
            'MY' => 'Malaysia',
            'MX' => 'Mexico',
            'PH' => 'Philippines',
            'SG' => 'Singapore',
            'TW' => 'Taiwan, China',
            'TH' => 'Thailand',
            'VN' => 'Vietnam',
        ];
        
        echo '<select name="lalamove_market">';
        foreach ($markets as $key => $label) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($key),
                selected($value, $key, false),
                esc_html($label)
            );
        }
        echo '</select>';
    }
    
}
