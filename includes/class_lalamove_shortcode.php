<?php
namespace Sevhen\WooLalamove;

class Class_Lalamove_Shortcode {
    public function __construct() {
        // Shortcodes for the modal and webhook data display.
        add_shortcode('lalamove_modal', [$this, 'render_modal']);
        add_shortcode('lalamove_webhook_data', [$this, 'display_webhook_data']);
        // Shortcode for displaying order details.
        add_shortcode('order_details', [$this, 'render_order_details']);
        
        add_action('wp_enqueue_scripts', [$this, 'enqueue_modal_scripts']);
        add_action('woocommerce_after_checkout_form', [$this, 'inject_modal']);
        
        // Add a custom button to each order in My Account orders table.
    }

    /**
     * Render Modal HTML.
     */
    public function render_modal() {
        ob_start(); ?>
        <div id="lalamove-modal" class="lalamove-modal-overlay" style="display: none;">
            <div class="lalamove-modal-content">
                <button id="close-lalamove-modal" class="lalamove-modal-close">&times;</button>
                <h2>Lalamove Services</h2>
                <p>Select the service you want to use:</p>
                <select id="lalamove-service">
                    <option value="">Select Service</option>
                    <option value="motorbike">Motorbike</option>
                    <option value="car">Car</option>
                    <option value="van">Van</option>
                </select>
                <br>
                <button id="confirm-lalamove-service" class="button button-primary">Confirm</button>
                <!-- Optionally, display the webhook data within the modal -->
                <div id="lalamove-webhook-display">
                    <?php echo $this->display_webhook_data(); ?>
                </div>
            </div>
        </div>
        <?php 
        return ob_get_clean();
    }

    /**
     * Display stored Lalamove webhook data.
     * This function retrieves the data from a WordPress option and formats it.
     */
    public function display_webhook_data() {
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
        if (isset($_GET['order_id'])) {
            $order_id = absint($_GET['order_id']);
            $order = wc_get_order($order_id);
            if ($order) {
                ob_start(); ?>
                <div class="order-details">
                    <h3>Order #<?php echo esc_html($order_id); ?></h3>
                    <p>Status: <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></p>
                    <p>Total: <?php echo wp_kses_post($order->get_formatted_order_total()); ?></p>
                    <!-- Add more order details here if needed -->
                </div>
                <?php
                return ob_get_clean();
            } else {
                return '<p>Order not found.</p>';
            }
        }
        return '<p>No order selected.</p>';
    }

    /**
     * Inject the modal automatically into the WooCommerce checkout page.
     */
    public function inject_modal() {
        echo do_shortcode('[lalamove_modal]');
    }

    /**
     * Enqueue Modal Scripts & Styles.
     */
    public function enqueue_modal_scripts() {
        if (is_checkout()) {
            wp_enqueue_script(
                'lalamove-modal',
                plugin_dir_url(dirname(__FILE__)) . 'assets/js/lalamove-modal.js',
                ['jquery'],
                null,
                true
            );
            wp_enqueue_style(
                'lalamove-modal-style',
                plugin_dir_url(dirname(__FILE__)) . 'assets/css/lalamove-modal.css'
            );
        }
    }

}
