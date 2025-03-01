<?php
namespace Sevhen\WooLalamove;

class Class_Lalamove_Shortcode {
    public function __construct() {
        add_shortcode('lalamove_modal', [$this, 'render_modal']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_modal_scripts']);
        add_action('woocommerce_after_checkout_form', [$this, 'inject_modal']);
    }

    /**
     * Render Modal HTML
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
            </div>
        </div>
        <?php return ob_get_clean();
    }

    /**
     * Inject the modal automatically into the WooCommerce checkout page
     */
    public function inject_modal() {
        echo do_shortcode('[lalamove_modal]');
    }

    /**
     * Enqueue Modal Scripts & Styles
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

