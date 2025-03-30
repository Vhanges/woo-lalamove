<?php
namespace Sevhen\WooLalamove;

if (!defined('ABSPATH'))
    exit;

use Sevhen\WooLalamove\Class_Lalamove_Api;

/**
 * Class_Lalamove_Settings
 *
 * This class handles the creation of the WooCommerce settings page for the Lalamove API.
 *
 * Use get_option() to retrieve the following stored settings (all keys are in lowercase):
 *   - lalamove_sandbox_api_key: Sandbox API Key.
 *   - lalamove_sandbox_api_secret: Sandbox API Secret.
 *   - lalamove_sandbox_url: Sandbox API URL.
 *   - lalamove_production_api_key: Production API Key.
 *   - lalamove_production_api_secret: Production API Secret.
 *   - lalamove_production_url: Production API URL.
 *   - lalamove_environment: Current environment (sandbox or production).
 *   - lalamove_market: Market selection.
 *   - lalamove_shipping_address: The shipping address entered by the user.
 *   - lalamove_shipping_lat: Latitude coordinate for the shipping address.
 *   - lalamove_shipping_lng: Longitude coordinate for the shipping address.
 *   - lalamove_phone_number: The phone number (in E.164 format).
 *
 * Example usage:
 *   $sandbox_api_key = get_option('lalamove_sandbox_api_key', '');
 *   $shipping_address = get_option('lalamove_shipping_address', '');
 *   $shipping_lat = get_option('lalamove_shipping_lat', '');
 *   $shipping_lng = get_option('lalamove_shipping_lng', '');
 *
 * This ensures that all settings are retrieved safely with a fallback value.
 */

class Class_Lalamove_Settings 
{
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
        // Register existing settings
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
        
        // Register new settings for Shipping Address, Phone Number, and Coordinates
        register_setting('woo_lalamove_settings_group', 'lalamove_shipping_address', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('woo_lalamove_settings_group', 'lalamove_phone_number', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('woo_lalamove_settings_group', 'lalamove_shipping_lat', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('woo_lalamove_settings_group', 'lalamove_shipping_lng', ['sanitize_callback' => 'sanitize_text_field']);

        // Existing sections
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
        add_settings_section(
            'woo_lalamove_market_section',
            'Market Selection',
            function() { echo '<p>Select the market provided by Lalamove. Choose "Other" to enter a custom value.</p>'; },
            'woo-lalamove-settings'
        );
        
        // New section for Shipping Settings
        add_settings_section(
            'woo_lalamove_shipping_section',
            'Shipping Settings',
            function() { 
                echo '<p>Enter your shipping address and phone number. The phone number must be in E.164 format (e.g., +6512345678).</p>'; 
            },
            'woo-lalamove-settings'
        );

        // Add fields to their respective sections

        // Sandbox fields
        add_settings_field('lalamove_sandbox_api_key', 'Sandbox API Key', [$this, 'sandbox_api_key_callback'], 'woo-lalamove-settings', 'woo_lalamove_sandbox_section');
        add_settings_field('lalamove_sandbox_api_secret', 'Sandbox API Secret', [$this, 'sandbox_api_secret_callback'], 'woo-lalamove-settings', 'woo_lalamove_sandbox_section');
        add_settings_field('lalamove_sandbox_url', 'Sandbox API URL', [$this, 'sandbox_url_callback'], 'woo-lalamove-settings', 'woo_lalamove_sandbox_section');

        // Production fields
        add_settings_field('lalamove_production_api_key', 'Production API Key', [$this, 'production_api_key_callback'], 'woo-lalamove-settings', 'woo_lalamove_production_section');
        add_settings_field('lalamove_production_api_secret', 'Production API Secret', [$this, 'production_api_secret_callback'], 'woo-lalamove-settings', 'woo_lalamove_production_section');
        add_settings_field('lalamove_production_url', 'Production API URL', [$this, 'production_url_callback'], 'woo-lalamove-settings', 'woo_lalamove_production_section');

        // Environment field
        add_settings_field('lalamove_environment', 'Environment', [$this, 'environment_callback'], 'woo-lalamove-settings', 'woo_lalamove_environment_section');

        // Market field
        add_settings_field('lalamove_market', 'Market', [$this, 'market_callback'], 'woo-lalamove-settings', 'woo_lalamove_market_section');

        // Shipping Settings fields: Shipping Address, Phone Number, and Coordinates
        add_settings_field('lalamove_shipping_address', 'Shipping Address', [$this, 'shipping_address_callback'], 'woo-lalamove-settings', 'woo_lalamove_shipping_section');
        add_settings_field('lalamove_phone_number', 'Phone Number', [$this, 'phone_number_callback'], 'woo-lalamove-settings', 'woo_lalamove_shipping_section');
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
    
    /**
     * Callback for Shipping Address field using Leaflet JS with Geolocation.
     * It stores the shipping address along with latitude and longitude.
     *
     * get_option() values used:
     *   - lalamove_shipping_address: The user's shipping address.
     *   - lalamove_shipping_lat: The saved latitude coordinate.
     *   - lalamove_shipping_lng: The saved longitude coordinate.
     */
    public function shipping_address_callback() {
        $lat      = get_option('lalamove_shipping_lat', '');
        $lng      = get_option('lalamove_shipping_lng', '');
        $address1 = get_option('woocommerce_store_address');
        $address2 = get_option('woocommerce_store_address_2');
        ?>
        <select name="lalamove_shipping_address" id="lalamove_shipping_address">
            <?php if ( ! empty($address1) ) : ?>
                <option value="<?php echo esc_attr($address1); ?>"><?php echo esc_html($address1); ?></option>
            <?php endif; ?>
            <?php if ( ! empty($address2) ) : ?>
                <option value="<?php echo esc_attr($address2); ?>"><?php echo esc_html($address2); ?></option>
            <?php endif; ?>
            <?php if ( empty($address1) && empty($address2) ) : ?>
                <option value="">No store address set</option>
            <?php endif; ?>
        </select>
        <!-- Hidden inputs to store latitude and longitude -->
        <input type="hidden" id="lalamove_shipping_lat" name="lalamove_shipping_lat" value="<?php echo esc_attr($lat); ?>">
        <input type="hidden" id="lalamove_shipping_lng" name="lalamove_shipping_lng" value="<?php echo esc_attr($lng); ?>">
        <div id="lalamove_map" style="width: 100%; height: 300px; margin-top: 10px;"></div>
        
        <!-- Load Leaflet CSS and JS -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
        crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

        <script>
            // Retrieve saved latitude and longitude from PHP (if any)
            var savedLat = "<?php echo esc_js($lat); ?>";
            var savedLng = "<?php echo esc_js($lng); ?>";
            
            // Use saved coordinates if they exist; otherwise, fall back to default values
            var defaultLat = savedLat ? parseFloat(savedLat) : 51.505;
            var defaultLng = savedLng ? parseFloat(savedLng) : -0.09;
            
            var map = L.map('lalamove_map').setView([defaultLat, defaultLng], 13);

            // Add OpenStreetMap tile layer
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Add a draggable marker on the map at the default/saved location
            var marker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(map);

            // When the marker is dragged, update the hidden fields
            marker.on('dragend', function(e) {
                var latlng = marker.getLatLng();
                document.getElementById('lalamove_shipping_lat').value = latlng.lat;
                document.getElementById('lalamove_shipping_lng').value = latlng.lng;
                console.log("Marker moved to:", latlng);
                // Optionally, you can call a reverse geocoding service here to update the address field.
            });

            // Allow repositioning the marker by clicking on the map
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                document.getElementById('lalamove_shipping_lat').value = e.latlng.lat;
                document.getElementById('lalamove_shipping_lng').value = e.latlng.lng;
                console.log("Map clicked at:", e.latlng);
                // Optionally, update the address field or perform reverse geocoding.
            });

            // Only use geolocation if no saved coordinates exist
            if (!savedLat && !savedLng && navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    map.setView([lat, lng], 13);
                    marker.setLatLng([lat, lng]);
                    document.getElementById('lalamove_shipping_lat').value = lat;
                    document.getElementById('lalamove_shipping_lng').value = lng;
                    console.log("Centered on user's location:", lat, lng);
                }, function(error) {
                    console.log("Geolocation error:", error);
                });
            } else {
                console.log("Using saved coordinates, geolocation skipped.");
            }
        </script>
        <?php
    }

    /**
     * Callback for Phone Number field.
     */
    public function phone_number_callback() {
        $value = get_option('lalamove_phone_number', '');
        echo '<input type="tel" name="lalamove_phone_number" value="' . esc_attr($value) . '" class="regular-text" 
              pattern="^\+[1-9]\d{1,14}$" 
              title="Please enter a valid phone number in E.164 format (e.g., +6512345678)">';
    }
}
