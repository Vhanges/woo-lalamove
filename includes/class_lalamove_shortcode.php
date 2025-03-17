<?php
namespace Sevhen\WooLalamove;

class Class_Lalamove_Shortcode{

    private $lalamove_api;
    public function __construct() {

        $this->lalamove_api = New Class_Lalamove_Api();

        add_shortcode('order_details', [$this, 'render_order_details']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles'], 20); 
    }

    /**
     * Enqueue styles for the shortcode.
     */
    public function enqueue_styles() {
        if (is_page('delivery-details')) {
            // Enqueue Bootstrap CSS
            wp_enqueue_style(
            'bootstrap-css',
            'https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'
            );

            // Enqueue Material Symbols
            wp_enqueue_style(
            'material-symbols',
            'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined'
            );

            // Enqueue Leaflet CSS
            wp_enqueue_style(
            'leaflet-css',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
            array(),
            null
            );

            // Enqueue Leaflet JS
            wp_enqueue_script(
            'leaflet-js',
            'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
            array(),
            null,
            true
            );
        } 
    }

    /**
     * Display stored Lalamove webhook data.
     * This function retrieves the data from a WordPress option and formats it.
     */
    public function display_webhook_data(): bool|string {
        $data = get_option('lalamove_webhook_data');
        ob_start();
        if (empty($data)) {
            echo '<p>No Lalamove webhook data available.</p>';
        } else {
            echo '<div class="lalamove-webhook-data">';
            echo '<h3>Lalamove Webhook Data</h3>';
            echo '<ul>';
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $value = json_encode($value);
                }
                echo '<li><strong>' . esc_html($key) . ':</strong> ' . esc_html($value) . '</li>';
            }
            echo '</ul>';
            echo '</div>';
        }
        return ob_get_clean();
    }

    /**
     * Render order details shortcode.
     * Usage: [order_details]
     */
    public function render_order_details() {

        global $wpdb;

        $orderID = $_GET['order_id'];

        $lalamove_table = $wpdb->prefix . 'wc_lalamove_orders';
        $data = $wpdb->get_results($wpdb->prepare("SELECT * FROM $lalamove_table WHERE wc_order_id = %d", $orderID), ARRAY_A);
        
        $lalamove_order_id = $data[0]['lalamove_order_id'];
        $details = $this->lalamove_api->get_order_details($lalamove_order_id);


        $driverId = $details['data']['driverId'];
        $driver_data = $this->lalamove_api->get_driver_details($lalamove_order_id, $driverId);
        $driver_name = $driver_data['data']['name'];
        $driver_phone = $driver_data['data']['phone'];
        $driver_plate_number = $driver_data['data']['plateNumber'];
        $senderAddress = $details['data']['stops'][0]['address'];
        $recipientAddress = $details['data']['stops'][1]['address'];
        $podImage = $details['data']['stops'][1]['POD']['image'] ?? 'https://developers.elementor.com/docs/assets/img/elementor-placeholder-image.png';
        $shareLink = $details['data']['shareLink'];



        // <p style="color: #333;">'. var_dump($details).'</p>
        if (isset($orderID) && !empty($data)) {
          
            echo '
			<div class="delivery-details w-100 h-100 d-flex flex-column justify-content-center p-5" style="background-color: #FCFCFC; border: 1px solid #D9D9D9; padding: 10px; margin-bottom: 20px;">
                '.short_code_delivery_status().'
                '.short_code_delivery_location().'
                '.short_code_delivery_details($lalamove_order_id, $shareLink, $podImage, $senderAddress, $recipientAddress, $driver_name, $driver_phone, $driver_plate_number).'
                </div>
                ';
                // <pre>'.var_dump($driver_data).'</pre>
                
        } else {
            return "<p>$orderID</p>";    
        }
    }
}

