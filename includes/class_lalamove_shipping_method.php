<?php

function init_shipping_method() {
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
                        'title'       => __('Title', 'woocommerce-lalamove-extension'),
                        'type'        => 'text',
                        'description' => __('This controls the title which the user sees during checkout.', 'woocommerce-lalamove-extension'),
                        'default'     => __('Lalamove Delivery', 'woocommerce-lalamove-extension'),
                        'desc_tip'    => true,
                    ),
                    'base_cost' => array(
                        'title'       => __('Base Cost', 'woocommerce-lalamove-extension'),
                        'type'        => 'number',
                        'description' => __('Base shipping cost (excluding dynamic fees)', 'woocommerce-lalamove-extension'),
                        'default'     => 10,
                        'desc_tip'    => true,
                        'placeholder' => '0.00',
                    ),
                    'shipping_cost_strategy' => array(
                        'title'       => __('Shipping Cost Strategy', 'woocommerce-lalamove-extension'),
                        'type'        => 'select',
                        'description' => __('Who pays for the shipping cost?', 'woocommerce-lalamove-extension'),
                        'default'     => 'customer',
                        'options'     => array(
                            'customer' => __('Customer pays full cost', 'woocommerce-lalamove-extension'),
                            'admin'    => __('Admin pays full cost', 'woocommerce-lalamove-extension'),
                            'split'    => __('Split cost between customer and admin', 'woocommerce-lalamove-extension'),
                        ),
                        'desc_tip'    => true,
                    ),
                    'admin_cost_percentage' => array(
                        'title'       => __('Admin Cost Percentage', 'woocommerce-lalamove-extension'),
                        'type'        => 'number',
                        'description' => __('Percentage of shipping cost paid by admin (when using split strategy)', 'woocommerce-lalamove-extension'),
                        'default'     => 50,
                        'desc_tip'    => true,
                        'placeholder' => '50',
                        'custom_attributes' => array(
                            'min' => '0',
                            'max' => '100',
                            'step' => '1'
                        )
                    ),
                    'markup_percentage' => array(
                        'title'       => __('Markup Percentage', 'woocommerce-lalamove-extension'),
                        'type'        => 'number',
                        'description' => __('Additional markup percentage on shipping cost (when customer pays)', 'woocommerce-lalamove-extension'),
                        'default'     => 0,
                        'desc_tip'    => true,
                        'placeholder' => '0',
                        'custom_attributes' => array(
                            'min' => '0',
                            'max' => '100',
                            'step' => '1'
                        )
                    )
                );
            }

            /**
             * Calculate shipping
             */
            public function calculate_shipping($package = array()) {
                
                // Get dynamic cost from session
                $lalamove_cost = (float) WC()->session->get('shipment_cost', 0);
                
                if ($lalamove_cost <= 0) {
                    return; // No shipping cost available
                }
                
                // Get shipping strategy settings
                $strategy = $this->get_option('shipping_cost_strategy', 'customer');
                $admin_percentage = (float) $this->get_option('admin_cost_percentage', 50);
                $markup_percentage = (float) $this->get_option('markup_percentage', 0);
                
                // Calculate costs based on strategy
                $customer_cost = 0;
                $admin_cost = 0;
                $final_shipping_cost = 0;
                
                switch ($strategy) {
                    case 'admin':
                        // Admin pays full cost
                        $customer_cost = 0;
                        $admin_cost = $lalamove_cost;
                        $final_shipping_cost = 0;
                        break;
                        
                    case 'split':
                        // Split cost between admin and customer
                        $admin_cost = ($lalamove_cost * $admin_percentage) / 100;
                        $customer_cost = $lalamove_cost - $admin_cost;
                        $final_shipping_cost = $customer_cost;
                        break;
                        
                    case 'customer':
                    default:
                        // Customer pays full cost (with optional markup)
                        $customer_cost = $lalamove_cost;
                        $admin_cost = 0;
                        $markup_amount = ($lalamove_cost * $markup_percentage) / 100;
                        $final_shipping_cost = $customer_cost + $markup_amount;
                        break;
                }
                
                // Store cost breakdown in session for order processing
                WC()->session->set('lalamove_cost_breakdown', array(
                    'lalamove_actual_cost' => $lalamove_cost,
                    'customer_cost' => $customer_cost,
                    'admin_cost' => $admin_cost,
                    'final_shipping_cost' => $final_shipping_cost,
                    'strategy' => $strategy,
                    'markup_percentage' => $markup_percentage
                ));
                
                $rate = array(
                    'id'       => $this->id,
                    'label'    => $this->title,
                    'cost'     => $final_shipping_cost,
                    'calc_tax' => 'per_item',
                );

                $this->add_rate($rate);
            }
        }
    }
}

add_action('woocommerce_shipping_init', 'init_shipping_method');

function set_shipping_method($methods) {
    $methods['your_shipping_method'] = 'Class_Lalamove_Shipping_Method';
    return $methods;
}

add_filter('woocommerce_shipping_methods', 'set_shipping_method');