<?php
namespace Sevhen\WooLalamove;
if (!defined('ABSPATH'))
    exit;

class Class_Lalamove_Shortcode{

    private $lalamove_api;
    public function __construct() {

        $this->lalamove_api = New Class_Lalamove_Api();

        add_shortcode('order_details', [$this, 'render_order_details']);
        add_shortcode('qr_order_details', [$this, 'render_qr_content']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles'], 20); 
    }

    /**
     * Enqueue styles for the shortcode.
     */
    public function enqueue_styles() {
        if (is_page('delivery-status')) {
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
        if(is_page('waybill-qr')){
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
        }
    }
    
/**
 * Render QR Code Content.
 * Usage: [qr_order_details]
 */
public function render_qr_content() {
    // Get the order object
    $order_id = $_GET['order_id'] ?? null;
    $order = \wc_get_order($order_id);

    // Check if the order exists
    if (!$order) {
        echo '<div class="container mt-5"><div class="alert alert-dark text-center p-3">Order not found.</div></div>';
        return;
    }

    $order_items = $order->get_items();
    $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
    $customer_contact = $order->get_billing_phone();
    $seller_name =  get_bloginfo('name');
    $seller_contact = get_option('lalamove_phone_number', ''); // Replace with actual seller contact

    ob_start();
    ?>
    
    <div class="container-lg my-4">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="btn btn-link mb-4 d-inline-flex align-items-center text-decoration-none">
            <span class="material-symbols-outlined me-2">arrow_back</span>
            <span class="text-dark">Back to Orders</span>
        </a>

        <div class="card border-0">
            <div class="card-header bg-white pb-4">
                <div class="container">
                    <h1 class="h2 mb-2 fw-bold text-dark d-flex align-items-center">
                        <span class="material-symbols-outlined me-2 mr-2">qr_code</span>
                        Order Summary
                    </h1>
                    <p class="mb-0 text-muted">Scan QR code to view delivery details</p>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="container">
                    <!-- Order ID -->
                    <div class="alert alert-light mb-4 d-flex align-items-center py-3">
                        <span class="material-symbols-outlined me-2">tag</span>
                        <strong class="text-uppercase small me-2 mr-2">ORDER ID:</strong>
                        <span class="h3 ms-2"><?php echo esc_html($order_id); ?></span>
                    </div>

                    <!-- Contact Cards -->
                    <div class="row g-4 mb-5">
                        <!-- Customer Card -->
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title mb-4 d-flex align-items-center fw-bold">
                                        <span class="material-symbols-outlined me-2">person</span>
                                        Customer Details
                                    </h5>
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4 text-muted">Name</dt>
                                        <dd class="col-sm-8 h5"><?php echo esc_html($customer_name); ?></dd>
                                        
                                        <dt class="col-sm-4 text-muted">Contact</dt>
                                        <dd class="col-sm-8 h5">
                                            <a href="tel:<?php echo esc_attr($customer_contact); ?>" class="text-dark text-decoration-none d-flex align-items-center">
                                                <span class="material-symbols-outlined me-2">call</span>
                                                <?php echo esc_html($customer_contact); ?>
                                            </a>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Seller Card -->
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body">
                                    <h5 class="card-title mb-4 d-flex align-items-center fw-bold">
                                        <span class="material-symbols-outlined me-2">store</span>
                                        Seller Details
                                    </h5>
                                    <dl class="row mb-0">
                                        <dt class="col-sm-4 text-muted">Name</dt>
                                        <dd class="col-sm-8 h5"><?php echo esc_html($seller_name); ?></dd>
                                        
                                        <dt class="col-sm-4 text-muted">Contact</dt>
                                        <dd class="col-sm-8 h5">
                                            <a href="tel:<?php echo esc_attr($seller_contact); ?>" class="text-dark text-decoration-none d-flex align-items-center">
                                                <span class="material-symbols-outlined me-2">call</span>
                                                <?php echo esc_html($seller_contact); ?>
                                            </a>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <h3 class="h4 mb-4 text-center fw-bold d-flex align-items-center justify-content-center">
                        <span class="material-symbols-outlined me-2">package</span>
                        Order Items
                    </h3>

                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <?php foreach ($order_items as $item_id => $item) : 
                            $product = $item->get_product();
                            $image_url = wp_get_attachment_url($product->get_image_id());
                            $weight = $product->get_weight();
                        ?>
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="row g-0">
                                    <div class="col-4">
                                        <img src="<?php echo esc_url($image_url); ?>" 
                                             class="img-fluid h-100 object-fit-cover" 
                                             alt="<?php echo esc_attr($product->get_name()); ?>">
                                    </div>
                                    <div class="col-8">
                                        <div class="card-body">
                                            <h5 class="card-title mb-3 fw-bold"><?php echo esc_html($item->get_name()); ?></h5>
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="text-muted d-flex align-items-center">
                                                    <span class="material-symbols-outlined me-2">numbers</span>
                                                    Quantity
                                                </span>
                                                <span class="badge bg-dark text-white rounded-pill fs-6"><?php echo esc_html($item->get_quantity()); ?></span>
                                            </div>
                                            <?php if ($weight) : ?>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted d-flex align-items-center">
                                                    <span class="material-symbols-outlined me-2">weight</span>
                                                    Weight
                                                </span>
                                                <span class="fw-bold"><?php echo esc_html($weight); ?> kg</span>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    echo ob_get_clean();
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

        $orderID = $_GET['order_id'] ?? null;

        $lalamove_table = $wpdb->prefix . 'wc_lalamove_orders';
        $data = $wpdb->get_results($wpdb->prepare("SELECT * FROM $lalamove_table WHERE wc_order_id = %d", $orderID), ARRAY_A);
        
        $lalamove_order_id = $data[0]['lalamove_order_id'] ?? null;
        $details = $this->lalamove_api->get_order_details($lalamove_order_id);


        $driverId = $details['data']['driverId'] ?? null;

        if($driverId != null){
            $driver_data = $this->lalamove_api->get_driver_details($lalamove_order_id, $driverId);
            var_dump($driver_data);
        }

        $driver_name = $driver_data['data']['name'] ?? null;
        $driver_phone = $driver_data['data']['phone'] ?? null;
        $driver_plate_number = $driver_data['data']['plateNumber'] ?? null;
        $driver_lat = $driver_data['data']['coordinates']['lat'] ?? null;
        $driver_lng = $driver_data['data']['coordinates']['lng'] ?? null;

        $senderAddress = $details['data']['stops'][0]['address'] ?? null;
        $recipientAddress = $details['data']['stops'][1]['address'] ?? null;
        $recipientLat = $details['data']['stops'][1]['coordinates']['lat'] ?? null; 
        $recipientLng = $details['data']['stops'][1]['coordinates']['lng'] ?? null;
        $podImage = $details['data']['stops'][1]['POD']['image'] ?? 'https://developers.elementor.com/docs/assets/img/elementor-placeholder-image.png';
        $shareLink = $details['data']['shareLink'] ?? null;

        $startLat = $driver_lat;
        $startLon = $driver_lng;
        $endLat   = $recipientLat;
        $endLon   = $recipientLng;

        $estimatedTime = "TBA";

        if(isset($startLat) && isset($startLon) && isset($endLat) && isset($endtLon) ){
            $estimatedTime = get_estimated_time($startLat, $startLon, $endLat, $endLon);
        }

        if (is_wp_error($estimatedTime)) {
            echo "Error: " . $estimatedTime->get_error_message();
        }


        $order = wc_get_order($orderID); // Fetch the order object

        if ($order) {
            $orderStatus = $order->get_status(); // Now safe to call get_status()
        }
        $orderStatus = $orderStatus ?? null;
    
        echo '
            <style>
                /* Tracking Status Styles */
                .tracking-container {
                    max-width: 1200px;
                    margin: 2rem auto;
                    padding: 0 1rem;
                }

                .return-link {
                    display: inline-flex;
                    align-items: center;
                    gap: 0.5rem;
                    color: #1A4DAF;
                    text-decoration: none;
                    padding: 1rem;
                    border-radius: 8px;
                    transition: background-color 0.2s;
                }

                .return-link:hover {
                    background-color: rgba(26, 77, 175, 0.1);
                }

                .tracking-title {
                    text-align: center;
                    margin: 2rem 0;
                    color: #1a1a1a;
                    font-weight: 600;
                }

                .status-container {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    position: relative;
                    padding: 2rem 0;
                }

                .status-step {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    z-index: 1;
                }

                .status-indicator {
                    width: 56px;
                    height: 56px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                    transition: transform 0.3s ease;
                }

                .status-label {
                    margin-top: 1rem;
                    font-size: 0.9rem;
                    color: #666;
                    text-align: center;
                    white-space: nowrap;
                }

                .status-connector {
                    flex: 1;
                    height: 2px;
                    background: #e0e0e0;
                    margin: 0 -1%;
                }

                /* Delivery Details Styles */
                .delivery-grid {
                    display: grid;
                    grid-template-columns: 1fr;
                    gap: 2rem;
                    max-width: 1200px;
                    margin: 2rem auto;
                    padding: 0 1rem;
                }

                .delivery-info-section {
                    background: #ffffff;
                    padding: 1.5rem;
                    border-radius: 12px;
                    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
                }

                .info-row {
                    display: flex;
                    justify-content: space-between;
                    padding: 1rem 0;
                    border-bottom: 1px solid #eee;
                }

                .address-section {
                    margin-top: 1.5rem;
                }

                .address-title {
                    color: #1a1a1a;
                    font-size: 1.1rem;
                    margin-bottom: 0.5rem;
                }

                .pod-image {
                    width: 100%;
                    height: auto;
                    border-radius: 12px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                }

                @media (min-width: 768px) {
                    .delivery-grid {
                        grid-template-columns: 1fr 1fr;
                    }
                    
                    .status-indicator {
                        width: 64px;
                        height: 64px;
                    }
                }
            </style>
        <div class="delivery-details w-100 h-100 d-flex flex-column justify-content-center p-5" style="background-color: #FCFCFC; border: 1px solid #D9D9D9; padding: 10px; margin-bottom: 20px;">
            '.short_code_delivery_status($orderStatus).'
            '.short_code_delivery_location($estimatedTime).'
            '.short_code_delivery_details($lalamove_order_id, $shareLink, $podImage, $senderAddress, $recipientAddress, $driver_name, $driver_phone, $driver_plate_number).'
        </div>
            ';
    }
}



