<?php

function lalamove_check_is_woocommerce_active() {
	$active_plugins = (array) get_option( 'active_plugins', array() );
	if ( is_multisite() ) {
		$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	}
	if ( in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins ) ) {
		return true;
	} else {
		return false;
	}
}

/**
 * Calculate the estimated travel time using OSRM.
 *
 * @param float $startLat Latitude of the starting location.
 * @param float $startLon Longitude of the starting location.
 * @param float $endLat   Latitude of the destination.
 * @param float $endLon   Longitude of the destination.
 * @return string|WP_Error Estimated travel time in minutes, or WP_Error on failure.
 */
function get_estimated_time($startLat, $startLon, $endLat, $endLon) {
    // OSRM API endpoint (public demo server)
    $url = "http://router.project-osrm.org/route/v1/driving/{$startLon},{$startLat};{$endLon},{$endLat}?overview=false";
    
    // Make the HTTP GET request using wp_remote_get
    $response = wp_remote_get($url);
    
    if (is_wp_error($response)) {
        return new WP_Error("osrm_error", "Failed to get route: " . $response->get_error_message());
    }
    
    // Decode the JSON response
    $body = json_decode(wp_remote_retrieve_body($response), true);
    
    // Check if a route was found
    if (!isset($body['routes'][0]['duration'])) {
        return new WP_Error("osrm_error", "No route found.");
    }
    
    // Extract the duration in seconds and convert to minutes
	$duration = $body['routes'][0]['duration'];
    
	$estimation = format_travel_time($duration);
    
    return $estimation;
}
/**
 * Convert travel time in seconds into a human-friendly string.
 *
 * @param float $seconds Travel time in seconds.
 * @return string Formatted travel time.
 */
function format_travel_time($seconds) {
    if ($seconds < 60) {
        return round($seconds) . " seconds";
    } elseif ($seconds < 3600) { // less than 1 hour
        $minutes = $seconds / 60;
        return round($minutes,0) . " minutes";
    } elseif ($seconds < 86400) { // less than 1 day
        $hours = $seconds / 3600;
        return round($hours,0) . " hours";
    } elseif ($seconds < 2592000) { // less than 30 days (1 month)
        $days = $seconds / 86400;
        return round($days,0) . " days";
    } else {
        $months = $seconds / 2592000;
        return round($months,0) . " months";
    }
}





function short_code_delivery_status($orderStatus){

	$packed_class = 'background-color: #FFCB2F; color: #0D0C0C;';
	$preparing_class = 'background-color: #FFCB2F; color: #0D0C0C;';
	$shipped_class = 'background-color: #FFCB2F; color: #0D0C0C;';
	$delivered_class = 'background-color: #FFCB2F; color: #0D0C0C;';

	$preparing_class = ($orderStatus === 'processing') ? 'background-color: #1A4DAF; color: #FCFCFC;' : 'background-color: #FFCB2F; color: #0D0C0C;';

	switch($orderStatus){

		case 'packed':
			$packed_class = 'background-color: #1A4DAF; color: #FCFCFC;' ;
			$preparing_class = 'background-color: #1A4DAF; color: #FCFCFC;';
			$shipped_class =  'background-color: #FFCB2F; color: #0D0C0C;' ;
			$delivered_class =  'background-color: #FFCB2F; color: #0D0C0C;' ;
		break;

		case 'out-for-deliver':
			$packed_class = 'background-color: #1A4DAF; color: #FCFCFC;' ;
			$preparing_class = 'background-color: #1A4DAF; color: #FCFCFC;';
			$shipped_class =  'background-color: #1A4DAF; color: #FCFCFC;' ;
			$delivered_class =  'background-color: #FFCB2F; color: #0D0C0C;' ;

			
		break;
		case 'delivered':
			$packed_class = 'background-color: #1A4DAF; color: #FCFCFC;' ;
			$preparing_class = 'background-color: #1A4DAF; color: #FCFCFC;';
			$shipped_class =  'background-color: #1A4DAF; color: #FCFCFC;' ;
			$delivered_class =  'background-color: #1A4DAF; color: #FCFCFC;' ;

		break;
	}


	$html = '        
                <div class="return-button ">
                    <a href="/my-account/orders" class="d-flex align-items-center" style="text-decoration: none; color: inherit;">
                    <span class="material-symbols-outlined">chevron_left</span> Return to Orders</a>
                </div>

                <div class="d-flex flex-column w-100 ">
                    <div class="d-flex flex-column justify-content-center align-items-center mt-3">
                        <p class="h5 font-weight-bold">Tracking Details</p>

                        <div class="status-container w-100 d-flex flex-row align-items-center justify-content-center mt-3">

                            <span class="d-flex flex-column" >
                                <div class="preparing-indicator d-flex flex-row align-items-center justify-content-center" style="'. $preparing_class .' border-radius: 100%; height: 50px; width: 50px;">
                                    <span class="material-symbols-outlined">orders</span> 
                                </div>
                                
                            </span>

                                <hr style="width: 5vw; height: 2px; background-color: #ODOCOC;">

                            <span class="d-flex flex-column" >
                                <div class="packed-indicator d-flex flex-row align-items-center justify-content-center" style="'. $packed_class .' border-radius: 100%; height: 50px; width: 50px;">
                                    <span class="material-symbols-outlined">package</span> 
                                </div>
                                
                            </span>

                                <hr style="width: 5vw; height: 2px; background-color: #ODOCOC;">


                            <span class="d-flex flex-column" >
                                <div class="shipped-indicator d-flex flex-row align-items-center justify-content-center" style="'.$shipped_class.' border-radius: 100%; height: 50px; width: 50px;">
                                    <span class="material-symbols-outlined">local_shipping</span>
                                </div>
                                
                            </span>

                                <hr style="width: 5vw; height: 2px; background-color: #ODOCOC;">

                            <span class="d-flex flex-column" >
                                <div class="delivered-indicator d-flex flex-row align-items-center justify-content-center" style="'. $delivered_class .' border-radius: 100%; height: 50px; width: 50px;">
                                    <span class="material-symbols-outlined">home</span> 
                                </div>
                                
                            </span>
                        </div>

                        <div class="status-label d-flex flex-row align-items-center justify-content-center mt-1" style="gap: 5vw;">
                            <p style="text-align: center;">Preparing</p>
                            <p style="text-align: center;">Packed</p>
                            <p style="text-align: center;">Shipped</p>
                            <p style="text-align: center;">Delivered</p>
                        </div>
                    </div>
                </div>
                <div class=""></div>
            ';

	return $html;
}

function short_code_delivery_location($ETA){
	$html = '
		<div class="d-flex flex-column align-items-center mt-3 mb-5">
			<p>Estimated Time to arrive in <strong>'. $ETA .'</strong></p>
			<div id="map" style="height: 350px; width: 100%; z-index: 1"></div>
		</div>
		<script>
			document.addEventListener("DOMContentLoaded", function() {
				var map = L.map("map").setView([51.505, -0.09], 13);

				L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
					attribution: \'&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors\'
				}).addTo(map);

				L.marker([51.505, -0.09]).addTo(map)
					.bindPopup("A pretty CSS3 popup.<br> Easily customizable.")
					.openPopup();
			});
		</script>
	';

	return $html;
}

function short_code_delivery_details($lalamove_order_id, $shareLink, $podImage, $senderAddress, $recipientAddress, $driver_name, $driver_phone, $driver_plate_number){
	$html = '
    <div class="delivery-details d-flex justify-content-between col-12 p-0">
        <div class="col-4 p-0">
            <div class="delivery-info d-flex flex-column mb-3">

                <span class="d-flex flex-row">
                    <p>
                    <strong>
                        Lalamove Order ID: 
                    </strong>
                    </p>
                    <p class="ml-auto">'. $lalamove_order_id .'</p>
                </span>

                <span class="d-flex flex-row">
                    <p>
                    <strong>
                        Lalamove Link: 
                    </strong>
                    </p>
                    <a href="'. $shareLink .'"  target="_blank" rel="noopener noreferrer" class="ml-auto">Click Here</a>
                </span>
';

	if (isset($driver_name)) {
		$html .= '
					<span class="d-flex flex-row">
						<p>
						<strong>
							Courier Name: 
						</strong>
						</p>
						<p class="ml-auto">'. $driver_name .'</p>
					</span>

					<span class="d-flex flex-row">
						<p>
						<strong>
							Courier Number: 
						</strong>
						</p>
						<p class="ml-auto">'. $driver_phone .'</p>
					</span>

					<span class="d-flex flex-row">
						<p>
						<strong>
							Courier Plate#: 
						</strong>
						</p>
						<p class="ml-auto">'. $driver_plate_number .'</p>
					</span>
		';
	}

	$html .= '
				</div>
				
				<div class="delivery-pickup-loc d-flex flex-column mb-3">
					<span class="d-flex flex-column">
						<p>
						<strong>
							Pickup Location: 
						</strong>
						</p>
						<p>'. $senderAddress .'</p>
					</span>
				</div>

				<div class="delivery-drop-loc d-flex flex-column mb-3">
					<span class="d-flex flex-column">
						<p>
						<strong>
							Drop Off Location: 
						</strong>
						</p>
						<p>'. $recipientAddress .'</p>
					</span>
				</div>

			</div>    

			<div class="col-7 p-0">
				<p><strong>Proof of Delivery</strong> (POD)</p>
				<img src="'. $podImage .'" alt="Proof of Delivery" class="img-fluid" style="width: auto; height: auto; max-height: 70%;">
			</div>

		</div>
	';


	return $html;
}