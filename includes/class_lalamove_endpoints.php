<?php
namespace Sevhen\WooLalamove;

if (!defined('ABSPATH'))
    exit;

use WP_REST_Request;
use WP_REST_Response;

class Class_Lalamove_Endpoints
{
    private $lalamove_api;

    public function __construct()
    {
        $this->lalamove_api = new Class_Lalamove_Api();
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Rest API Routes
     */
    public function register_routes()
    {
        // Get City
        register_rest_route('woo-lalamove/v1', '/get-city', [
            'methods' => 'GET',
            'callback' => [$this, 'get_city'],
            'permission_callback' => '__return_true'
        ]);

        // Get Quotation
        register_rest_route('woo-lalamove/v1', '/get-quotation', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'get_quotation'],
            'permission_callback' => '__return_true'
        ]);

        // Checkout Package
        register_rest_route('woo-lalamove/v1', '/lalamove-webhook', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'lalamove_webhook'],
            'permission_callback' => '__return_true'
        ]);

        // Waybill QR Code Order Details Linkz`
        register_rest_route('woo-lalamove/v1', '/waybill/(?P<order_id>\d+)', array(
            // Supported methods for this endpoint
            'methods' => \WP_REST_Server::READABLE, 
            // Register the callback for the endpoint
            'callback' => [$this, 'get_waybill'],
            'permission_callback' => '__return_true', // Open for now, adjust for authentication
        ));
    
    }
    function get_waybill($request) {
        $order_id = $request->get_param('order_id');
        
        if (!$order_id) {
            return new WP_REST_Response('Order ID not provided', 400);
        }
    
        $output = '<h1>Waybill</h1>';
    
        return new WP_REST_Response($output, 200, array('Content-Type' => 'text/html'));
    }
    
    /**
     * Callback for QR Code Link Order Details Link
     * 
     * 
     */
    
    function render_order_details(WP_REST_Request $request) {
        header('Content-Type: text/html');
        // Get the Order ID from the request
        $order_id = $request->get_param('order_id');
        $order = \wc_get_order($order_id);
        
        // Validate the order
        if (!$order) {
            header('Content-Type: text/html');
            echo '<html><body><h1>Order not found</h1></body></html>';
            exit;
        }
    
        // Fetch WooCommerce Order and Product Details
        $woo_order_id = $order->get_id();
        $lalamove_order_id = $order->get_meta('lalamove_order_id'); // Assuming custom meta field
        $items = $order->get_items();
    
        // Generate Waybill HTML
        ob_start();
        ?>
        <html>
        <head>
            <title>Waybill</title>
            <style>
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                th, td {
                    border: 1px solid #ccc;
                    padding: 8px;
                    text-align: left;
                }
            </style>
        </head>
        <body>
            <h1>Waybill</h1>
            <p><strong>Woo Order ID:</strong> <?php echo esc_html($woo_order_id); ?></p>
            <p><strong>Lalamove Order ID:</strong> <?php echo esc_html($lalamove_order_id); ?></p>
    
            <h2>Products Ordered</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo esc_html($item->get_name()); ?></td>
                            <td><?php echo esc_html($item->get_quantity()); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </body>
        </html>
        <?php
    
        // Send the output with proper headers
        $output = ob_get_clean();
        echo $output;
        exit;
    }


    /**
     * Callback for get_city route
     * 
     * @return $res
     */
    public function print_waybill()
    {
        $response = $this->lalamove_api->get_city();
        return rest_ensure_response($response);
    }
    /**
     * Callback for get_city route
     * 
     * @return $res
     */
    public function get_city()
    {
        $response = $this->lalamove_api->get_city();
        return rest_ensure_response($response);
    }

    /**
     * Callback for get_quotation route
     * 
     * @return $res
     */
    public function get_quotation(WP_REST_Request $request)
    {   
        $body = $request->get_json_params();

        $response = $this->lalamove_api->get_quotation($body);
        return rest_ensure_response($response);
    }

    /**
     * Callback for get_quotation route
     * 
     * @return $res
     */
    public function lalamove_webhook(WP_REST_Request $request)
    {
        $payload = $request->get_body();
        $data = json_decode( $payload, true );
    
        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return new WP_REST_Response( array( 'message' => 'Invalid JSON' ), 400 );
        }
        
        // Optionally verify a signature or token here for security.
        error_log( 'Lalamove webhook data: ' . print_r( $data, true ) );
    
        return new WP_REST_Response( array( 'message' => 'Webhook received successfully' ), 200 );
    }

}
