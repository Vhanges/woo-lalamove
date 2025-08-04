<?php

namespace Sevhen\WooLalamove;

if (!defined('ABSPATH'))
    exit;

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

        if ($environment === 'production') {
            $this->api_key    = get_option('lalamove_production_api_key', '');
            $this->api_secret = get_option('lalamove_production_api_secret', '');
            $this->base_url   = get_option('lalamove_production_url', '');
        } else {
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

    public function get_quotation_details($quotationID)
    {

        // Calculate the current timestamp in milliseconds
        $timestamp = round(microtime(true) * 1000);

        // Prepare the raw signature string for HMAC-SHA256
        $rawSignature = "{$timestamp}\r\nGET\r\n/v3/quotations/{$quotationID}\r\n\r\n";


        // Generate the HMAC-SHA256 signature using the API secret
        $signature = hash_hmac('sha256', $rawSignature, $this->api_secret);

        // Build the authorization token
        $token = "{$this->api_key}:{$timestamp}:{$signature}";

        // Initialize cURL session
        $curl = curl_init();

        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://$this->base_url/v3/quotations/$quotationID",
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

        if (!empty($quotation['data']['scheduleAt'])) {
            $scheduled = new \DateTime($quotation['data']['scheduleAt'], new \DateTimeZone('UTC'));
            $now = new \DateTime('now', new \DateTimeZone('UTC'));
            
            // Add 5 minutes buffer to current time for threshold
            $now->modify('+5 minutes');
            
            if ($scheduled < $now) {
                throw new \Exception("Scheduled time must be at least 5 minutes in the future");
            }
            
            // Optional: Reset $now if you need it elsewhere
            $now->modify('-5 minutes');
        }

        // if (!empty($quotation['data']['expiresAt'])) {
        //     error_log(print_r($quotation['data'], true));
        //     error_log("DATE: ". $quotation['data']['expiresAt']);
        //     $expires = new \DateTime($quotation['data']['expiresAt'], new \DateTimeZone('UTC'));
        //     $now = new \DateTime('now', new \DateTimeZone('UTC'));

        //     // For debugging: Log formatted dates
        //     error_log('Expires (UTC): ' . $expires->format('Y-m-d H:i:s'));
        //     error_log('Now (UTC): ' . $now->format('Y-m-d H:i:s'));

        //     // Strict comparison
        //     if ($expires < $now) {
        //         throw new \Exception("The Lalamove quotation has expired. Refresh the page or reinitiate checkout for a new estimate.");
        //     }
        // } 



        // Close the cURL session
        curl_close($curl);

        // Return the estimated value of the quote
        return $quotation;
    }


    /** 
     * Place Order
     * Set quotation on order.
     * @return array
     **/
    public function place_order($body) {
        $timestamp = round(microtime(true) * 1000);

        $order_body = json_encode($body, JSON_UNESCAPED_SLASHES);

        $rawSignature = "{$timestamp}\r\nPOST\r\n/v3/orders\r\n\r\n{$order_body}";

        $signature = hash_hmac("sha256", $rawSignature, $this->api_secret);

        $token = "{$this->api_key}:{$timestamp}:{$signature}";
        // error_log("Order Body" . $order_body);
        error_log("Token" . $token);

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
                "Market:" . get_option('lalamove_market', '')
            ],
        ]);


        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            echo "Error: " . curl_error($curl);
        }

        if (!empty($response['errors']) && is_array($response['errors'])) {
            $firstError = $response['errors'][0] ?? null;

            $errorMessage = $firstError['message'] ?? 'Unknown error';
            $errorId = $firstError['id'] ?? 'Unknown ID';

            throw new \Exception("Lalamove API Error [$errorId]: $errorMessage");
        }

        curl_close($curl);

        return json_decode($response, true);
    }


    /**
     * Cancel Order
     * Cancels an existing order based on the provided ID.
     * @param string $lala_id The ID of the order to cancel.
     * @param mixed $body Request body (not used in this implementation but kept for interface consistency).
     * @return array Response from the cancellation request.
     */
    public function cancel_order($lala_id, $body)
    {
        // Generate timestamp in milliseconds
        $timestamp = round(microtime(true) * 1000);

        // Prepare empty JSON body and API path
        $braces = "{}";
        $path = "/v3/orders/{$lala_id}";

        // Generate signature components
        $rawSignature = "{$timestamp}\r\nDELETE\r\n{$path}\r\n\r\n{$braces}";
        $signature = hash_hmac('sha256', $rawSignature, $this->api_secret);
        $token = "{$this->api_key}:{$timestamp}:{$signature}";

        // Configure cURL request
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://{$this->base_url}{$path}",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_POSTFIELDS => $braces,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "Authorization: hmac {$token}",
                'Market: ' . get_option('lalamove_market', 'PH')
            ],
        ]);

        // Execute request and handle response
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $cancelResponse = json_decode($response, true) ?? [];

        // Error handling
        if (curl_errno($curl)) {
            error_log('cURL Error: ' . curl_error($curl));
            $cancelResponse['curl_error'] = curl_error($curl);
        }
        curl_close($curl);

        // Add status code to response
        $cancelResponse['status_code'] = $httpCode;

        error_log("CANCEL" . print_r($cancelResponse, true));

        // Diagnostic logging
        error_log("Cancel Request Details:
            URL: https://{$this->base_url}{$path}
            Timestamp: {$timestamp}
            ORDER ID: {$lala_id}
            Signature: {$signature}
            Full Token: hmac {$token}
        ");

        return $cancelResponse;
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
