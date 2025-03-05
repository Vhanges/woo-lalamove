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
     * Get Quotation
     * Returns an estimated value of the given quote of the order.
     * @return array Estimated value of the quote.
     *
     **/
    public function get_quotation($body)
    {

        // Calculate the current timestamp in milliseconds
        $timestamp = round(microtime(true) * 1000);

        $requestData = array(
            "data" => array(
                "serviceType" => "MOTORCYCLE",
                "specialRequests" => array("CASH_ON_DELIVERY"),
                "language" => "en_PH",
                "stops" => array(
                    array(
                        "coordinates" => array(
                            "lat" => "14.555566",
                            "lng" => "121.130056"
                        ),
                        "address" => "GFT Textile Main Office, Unit E, Sitio Malabon, Barangay San Juan, Hwy 2000, Taytay, 1920 Rizal"
                    ),
                    array(
                        "coordinates" => array(
                            "lat" => "14.557909",
                            "lng" => "121.137259"
                        ),
                        "address" => "134 Cabrera Rd, Taytay, 1920 Rizal, Philippines"
                    )
                ),
                "isRouteOptimized" => false,
                "item" => array(
                    "quantity" => "12",
                    "weight" => "LESS_THAN_3_KG",
                    "categories" => array("FOOD_DELIVERY", "OFFICE_ITEM"),
                    "handlingInstructions" => array("KEEP_UPRIGHT")
                )
            )
        );

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
                'Market: ' . get_option('lalamove_market', '')
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

}
