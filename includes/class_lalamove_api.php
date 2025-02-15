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

    private $region = 'PH';

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
     */
    public function get_city()
    {

        // **Calculate the current timestamp in milliseconds**
        $timestamp = round(microtime(true) * 1000);

        // **Prepare the raw signature string**
        $rawSignature = "{$timestamp}\r\nGET\r\n/v3/cities\r\n\r\n";



        // **Generate the HMAC-SHA256 signature in hexadecimal**
        $signature = hash_hmac('sha256', $rawSignature, $this->api_secret);

        // **Build the token string**
        $token = "{$this->api_key}:{$timestamp}:{$signature}";

        // **Set up the cURL options using curl_setopt_array**
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://rest.sandbox.lalamove.com/v3/cities',
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
            'Market: PH'
        ),
        ));

        $response = curl_exec($curl);
        $data = json_decode($response, true);

        // **Handle any potential cURL errors**
        if(curl_errno($curl)) {
            echo 'Error:' . curl_error($curl);
        }

        curl_close($curl);
        return $data['data'];
    }
}
