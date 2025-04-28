<?php

function your_shipping_method_init() {
    if (!class_exists('Class_Lalamove_Shipping_Method')) {
        class Class_Lalamove_Shipping_Method extends WC_Shipping_Method {
            /**
             * Constructor for your shipping class
             */
            public function __construct($instance_id = 0) {
                $this->instance_id        = absint($instance_id);
                $this->id                 = 'your_shipping_method';
                $this->method_title       = __('Lalamove');
                $this->method_description = __('Lalamove Shipping offers a fast, reliable delivery service');
                $this->supports          = array(
                    'shipping-zones',
                    'instance-settings',
                    'instance-settings-modal',
                );

                $this->init();
            }

            /**
             * Initialize settings
             */
            function init() {
                $this->init_form_fields();
                $this->init_settings();
                
                // Set user-defined title or default
                $this->title = $this->get_option('title', $this->method_title);

                add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
            }

            /**
             * Define instance settings fields
             */
            public function init_form_fields() {
                $this->instance_form_fields = array(
                    'title' => array(
                        'title'       => __('Title', 'your-text-domain'),
                        'type'        => 'text',
                        'description' => __('This controls the title which the user sees during checkout.', 'your-text-domain'),
                        'default'     => __('Lalamove Delivery', 'your-text-domain'),
                        'desc_tip'    => true,
                    ),
                    'base_cost' => array(
                        'title'       => __('Base Cost', 'your-text-domain'),
                        'type'        => 'number',
                        'description' => __('Base shipping cost (excluding dynamic fees)', 'your-text-domain'),
                        'default'     => 10,
                        'desc_tip'    => true,
                        'placeholder' => '0.00',
                    )
                );
            }

            /**
             * Calculate shipping
             */
            public function calculate_shipping($package = array()) {
                
                // Get dynamic cost from session
                $total_cost = (float) WC()->session->get('shipment_cost', 0);
                
                $rate = array(
                    'id'       => $this->id,
                    'label'    => $this->title,
                    'cost'     => $total_cost,
                    'calc_tax' => 'per_item',
                );

                $this->add_rate($rate);
            }
        }
    }
}

add_action('woocommerce_shipping_init', 'your_shipping_method_init');

function add_your_shipping_method($methods) {
    $methods['your_shipping_method'] = 'Class_Lalamove_Shipping_Method';
    return $methods;
}

add_filter('woocommerce_shipping_methods', 'add_your_shipping_method');