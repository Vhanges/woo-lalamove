<?php 

namespace Sevhen\WooLalamove;

use WP_Error;

class Class_Lalamove_Api
{
    private $api_key;
    private $api_secret;
    private $base_url;

    private $getMethod = 'GET';
    private $postMethod = 'POST';


    public function __construct() 
    {
        // Retrieve the selected environment
        $environment = get_option('lalamove_environment', 'sandbox');

        if ($environment === 'production') 
        {
            $this->api_key    = get_option('lalamove_production_api_key', '');
            $this->api_secret = get_option('lalamove_production_api_secret', '');
            $this->base_url   = get_option('lalamove_production_url', '');
        } 
        else
        {
            $this->api_key    = get_option('lalamove_sandbox_api_key', '');
            $this->api_secret = get_option('lalamove_sandbox_api_secret', '');
            $this->base_url   = get_option('lalamove_sandbox_url', '');
        }
    }

    /** 
     * Get City 
     * @return array return list of available market 
     */
    public function get_city()
    {
        // Calculate the current timestamp in milliseconds
        $timestamp = round(microtime(true) * 1000);

        // Prepare the raw signature string for HMAC-SHA256
        $rawSignature = "{$timestamp}\r\nGET\r\n/v3/cities\r\n\r\n";

        // Generate the HMAC-SHA256 signature using the API secret
        $signature = hash_hmac('sha256', $rawSignature, $this->api_secret);

        // Build the authorization token
        $token = "{$this->api_key}:{$timestamp}:{$signature}";

        // Initialize cURL session
        $curl = curl_init();

        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://$this->base_url/v3/cities",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: hmac {$token}",
                'Market: ' . get_option('lalamove_market', '')
            ),
        ));

        // Execute the cURL request and get the response
        $response = curl_exec($curl);
        $market = json_decode($response, true);

        // Handle any potential cURL errors
        if (curl_errno($curl)) {
            echo 'Error:' . curl_error($curl);
        }

        // Close the cURL session
        curl_close($curl);

        // Return the list of available markets
        return $market['data'];
    }
    
    /**
     * Get Order Details
     * @param string $orderID The ID of the order to retrieve details for.
     * @return array The order details.
     */
    public function get_order_details($orderID)
    {
        // Calculate the current timestamp in milliseconds
        $timestamp = round(microtime(true) * 1000);

        // Prepare the raw signature string for HMAC-SHA256
        $rawSignature = "{$timestamp}\r\nGET\r\n/v3/orders/{$orderID}\r\n\r\n";

        // Generate the HMAC-SHA256 signature using the API secret
        $signature = hash_hmac('sha256', $rawSignature, $this->api_secret);

        // Build the authorization token
        $token = "{$this->api_key}:{$timestamp}:{$signature}";

        // Initialize cURL session
        $curl = curl_init();

        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://{$this->base_url}/v3/orders/{$orderID}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: hmac {$token}",
                'Market: ' . get_option('lalamove_market', '')
            ),
        ));

        // Execute the cURL request and get the response
        $response = curl_exec($curl);
        $order_details = json_decode($response, true);

        // Handle any potential cURL errors
        if (curl_errno($curl)) {
            echo 'Error:' . curl_error($curl);
            echo $orderID;
        }

        // Close the cURL session
        curl_close($curl);

        // Return order details of given order ID
        return $order_details;
    }
    
    /** 
     * Get Quotation
     * Returns an estimated value of the given quote of the order.
     * @return array Estimated value of the quote.
     *
     **/
    public function get_quotation($body)
    {

        // Calculate the current timestamp in milliseconds
        $timestamp = round(microtime(true) * 1000);



        $requestDataJson = json_encode($body);
        // Prepare the raw signature string for HMAC-SHA256
        $rawSignature = "{$timestamp}\r\nPOST\r\n/v3/quotations\r\n\r\n{$requestDataJson}";
    
        // Generate the HMAC-SHA256 signature using the API secret
        $signature = hash_hmac('sha256', $rawSignature, $this->api_secret);
    
        // Build the authorization token
        $token = "{$this->api_key}:{$timestamp}:{$signature}";
    
        // Initialize cURL session
        $curl = curl_init();
    
        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://$this->base_url/v3/quotations",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => "{$requestDataJson}",
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                "Authorization: hmac {$token}",
                'Market: ' . get_option('lalamove_market', 'PH')
            ),
        ));
    
        // Execute the cURL request and get the response
        $response = curl_exec($curl);
        $quotation = json_decode($response, true);
    
        // Handle any potential cURL errors
        if (curl_errno($curl)) {
            echo 'Error:' . curl_error($curl);
        }
    
        // Close the cURL session
        curl_close($curl);
    
        // Return the estimated value of the quote
        return $quotation;
    }


    /** 
     * Place Order
     * Set quotation on order.
     * @return array of order details.
     *
     **/
    public function place_order(
        $quotationID,
        $stopId0,
        $stopId1,
        $senderName,
        $senderPhone,
        $recipientName,
        $recipientPhone,
        $remarks,
        $isPODEnabled
    )
    {
        $timestamp = round(microtime(true) * 1000);
    
        $order_payload = [
            "data" => [
                "quotationId" => $quotationID,
                "sender" => [
                    "stopId" => $stopId0,
                    "name"    => $senderName,     
                    "phone"   => $senderPhone,    
                ],
                "recipients" => [
                    [
                        "stopId"  => $stopId1,
                        "name"    => $recipientName,  
                        "phone"   => $recipientPhone, 
                        "remarks" => "YYYYYYYY",          
                    ]
                ],
                "isPODEnabled" => $isPODEnabled,        
                "partner"      => $senderName   
            ]
        ];
    
        $order_body = json_encode($order_payload, JSON_UNESCAPED_SLASHES);
    
        $rawSignature = "{$timestamp}\r\nPOST\r\n/v3/orders\r\n\r\n{$order_body}";
    
        $signature = hash_hmac("sha256", $rawSignature, $this->api_secret);
    
        $token = "{$this->api_key}:{$timestamp}:{$signature}";
        error_log("Order Body". $order_body);
        error_log("Token". $token);
    
        $curl = curl_init();
    
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://{$this->base_url}/v3/orders", 
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $order_body,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/json",
                "Authorization: hmac {$token}",
                "Accept: application/json",
                "Market:". get_option('lalamove_market', '')
            ],
        ]);

    
        $response = curl_exec($curl);
    
        if (curl_errno($curl)) {
            echo "Error: " . curl_error($curl);
        }
    
        curl_close($curl);
    
        return json_decode($response, true);
    }


   public function get_driver_details($orderId, $driverId)
   {
    $timestamp = round(microtime(true) * 1000);

    $rawSignature = "{$timestamp}\r\nGET\r\n/v3/orders/{$orderId}/drivers/{$driverId}\r\n\r\n";

    $signature = hash_hmac("sha256", $rawSignature, $this->api_secret);

    $token = "{$this->api_key}:{$timestamp}:{$signature}";

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://{$this->base_url}/v3/orders/{$orderId}/drivers/{$driverId}",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Authorization: hmac {$token}",
            "Accept: application/json",
            "Market:" . get_option('lalamove_market', '')
        ],
    ]);

    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        echo "Error: " . curl_error($curl);
    }

    curl_close($curl);

    return json_decode($response, true);
    }

    
}
