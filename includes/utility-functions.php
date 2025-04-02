<?php

if (!defined('ABSPATH'))
    exit;
function get_shop_logo(){
	// Get logo data
	$logo_html = '';
	$custom_logo_id = get_theme_mod('custom_logo');
	
	if ($custom_logo_id) {
		$logo_path = get_attached_file($custom_logo_id);
		
		if ($logo_path && file_exists($logo_path)) {
			// Convert logo to base64
			$logo_data = file_get_contents($logo_path);
			$logo_base64 = base64_encode($logo_data);
			$logo_html = '<img src="data:image/png;base64,'.$logo_base64.'" alt="Shop Logo" width="50" height="40">';

			return $logo_html;
		}
	}
}

function get_delivery_details($order_id){

	global $wpdb;

    $orders_table = $wpdb->prefix . 'wc_lalamove_orders';
	$transaction_table = "{$wpdb->prefix}wc_lalamove_transaction";

	// Use INNER JOIN to fetch data from both tables
	$delivery_data = $wpdb->get_row($wpdb->prepare(
		"SELECT orders.*, transactions.* 
		FROM {$orders_table} orders 
		INNER JOIN {$transaction_table} transactions
		ON orders.transaction_id = transactions.transaction_id
		WHERE orders.wc_order_id = %d",
		$order_id
	));

	$order_id = $delivery_data->wc_order_id;
	$customer_name = $delivery_data->ordered_by;
	$delivery_id = $delivery_data->lalamove_order_id;
	$ordered_on = $delivery_data->ordered_on;

	// Create a DateTime object
	$date = new DateTime($ordered_on);
	$ordered_on = $date->format('F j, Y');

	$drop_off_location = $delivery_data->drop_off_location;

	$delivery_details = [
		"order_id" => $order_id,
		"customer_name" => $customer_name,
		"delivery_id" => $delivery_id,
		"ordered_on" => $ordered_on,
		"drop_off_location" => $drop_off_location,
	];

	return $delivery_details;

}

function get_weight_unit() {
    // Fetch weight unit from WooCommerce settings
    $weight_unit = get_option('woocommerce_weight_unit');

    return $weight_unit;
}


function get_order_details($order_id) {
    $order = wc_get_order($order_id);

    if (!$order) {
        return "Invalid Order ID";
    }

    // Initialize arrays and variables
    $product_details = [];
    $total_weight = 0;
    $total_quantity = 0;

    // Loop through the order items
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();

        if ($product) {
            // Fetch quantity and weight
            $quantity = $item->get_quantity();
            $weight = $product->get_weight();

            // Accumulate totals
            $total_weight += $weight * $quantity;
            $total_quantity += $quantity;

            // Store individual product details
            $product_details[] = [
                'product_name' => $product->get_name(),
                'quantity' => $quantity,
                'weight' => $weight,
            ];
        }
    }

    // Add total details to the result
    $product_details['totals'] = [
        'total_quantity' => $total_quantity,
        'total_weight' => $total_weight,
    ];

    return $product_details;
}

function format_address($text, $max_length = 45) {
    return wordwrap(htmlspecialchars($text), $max_length, "<br>", true);
}


function print_waybill($order_id) {
	// Initialize mPDF first
	$mpdf = new \Mpdf\Mpdf([
		'mode' => 'utf-8',
		'format' => 'A6',
		'margin_left' => 5,
		'margin_right' => 5,
		'margin_top' => 5,
		'margin_bottom' => 5,
	]);

	// QR code generation
	$qrData = get_site_url().'/wp-json/woo-lalamove/v1/order-details?order_id='. $order_id;
	$qrCode = new Mpdf\QrCode\QrCode($qrData);
	$output = new Mpdf\QrCode\Output\Png();
	$qrCodePng = $output->output($qrCode, 100, [255, 255, 255], [0, 0, 0]);
	$qrCodeBase64 = base64_encode($qrCodePng);

	$delivery_data = get_delivery_details($order_id);
	$order_details = get_order_details($order_id);
	$wc_order_id = (int)$order_id;

	// HTML content
	$html = '
	<style>
		table { 
			width: 100%; 
			height: 100%; /* Fill available space */
			border-collapse: collapse; 
			font-size: 10pt;
			table-layout: fixed; /* Crucial for fixed layout */
		}
		td { 
			padding: 4px !important; 
			text-align: center;
			overflow: hidden; /* Prevent content overflow */
		}
		.header { 
			text-align: center; 
			font-weight: bold; 
			font-size: 12pt;
			height: 10mm !important; /* Fixed header height */
		}
		.rotate { 
			width: 8mm !important;
			height: 35mm !important;
			text-rotate: 90;
			font-size: 12pt;
			padding: 1mm !important;
			word-wrap: break-word;
		}
		.content-cell {
			height: 35mm !important; /* Match rotate column height */
			max-height: 35mm !important;
			font-size: 9pt;
			line-height: 1.1;
			overflow: hidden;
			text-align: left !important;
			vertical-align: top !important;
			padding: 5mm;
		}
		img {
			max-width: 100% !important;
			height: auto !important;
			object-fit: contain; /* Maintain aspect ratio */
		}
		.barcodecell {
			height: 15mm !important;
		}
		.nested-table {
			width: 100% !important;
			table-layout: fixed;
		}
		.attempts{
			border: 1px solid #000;	
		}
		.test {
			width: 10%;
		}
	</style>
	
	<table style="page-break-inside:avoid" border="1">
		<!-- Header -->
		<tr>
			<td colspan="1" class="logo" style="width: 15mm; height: 15mm;">
				'. (get_shop_logo() ?: 'No Logo') .'
			</td>
			<td colspan="3" style="width: 85mm; height: 15mm; font-size: 10pt;">
				'. get_bloginfo('name') .'
			</td>
		</tr>    
		<tr>	
			<td colspan="4" class="header" style="height: 10mm;">
				'.$delivery_data['delivery_id'].'
			</td>
		</tr>
		<tr>
			<td colspan="2" style=" height: 8mm; text-align: left;">Order ID: '. $order_id .'</td>
			<td colspan="2" style=" height: 8mm; text-align: left;">Order Date: '. $delivery_data['ordered_on'] .'</td>
		</tr>
	
		<!-- Barcode -->
		<tr>
			<td colspan="4" class="barcodecell" style="height: 30mm;">
				<barcode code="'. $wc_order_id .'" type="C128B" style="width: 100mm; height: 50mm;" />
			</td>
		</tr>
	
		<!-- Buyer Details -->
		<tr>
			<td colspan="1" class="rotate">BUYER</td>
			<td colspan="3" class="content-cell">
				<div style="max-height: 33mm; overflow: hidden;">
					 <strong>buyer123</strong><br><br>
					'. wordwrap($delivery_data['drop_off_location'], 45, "<br>", true) .'
				</div>
			</td>
		</tr>
	
		<!-- Seller Details -->
		<tr>
			<td colspan="1" class="rotate">SELLER</td>
			<td colspan="3" class="content-cell">
				<div style="max-height: 33mm; overflow: hidden;">
					 <strong>'.get_bloginfo('name').'</strong><br><br>
					'. wordwrap(get_option('lalamove_shipping_address', ''), 45, "<br>", true) .'
				</div>
			</td>
		</tr>
	
		<!-- QR Code and Product Details -->
		<tr>
			<td colspan="2" style="width: 20mm; height: 25mm;">
				<img src="data:image/png;base64,'.$qrCodeBase64.'" alt="QR Code" style="width: 25mm; height: 25mm;"/>
			</td>
			<td colspan="2" style="height: 25mm;">
				<div style="padding: 1mm;">
					Product Quantity: '.$order_details['totals']['total_quantity'].'<br><br>
					Weight:'.$order_details['totals']['total_weight'].' '. get_weight_unit().'
				</div>
			</td>
		</tr>
	
		<!-- Return Attempt -->
		<tr>
			<td colspan="2" style="width: 50mm; height: 20mm; padding: 2mm;">
				Thank you for your order!
			</td>
			<td colspan="2" style="width: 50mm; height: 20mm; padding: 2mm;">
				<table class="nested-table">
					<tr>
						<td colspan="4">Return Attempt</td>
					</tr>
					<tr>
						<td class="attempts" style="width: 33%; text-align: center; padding: 1mm;">1</td>
						<td class="attempts" style="width: 33%; text-align: center; padding: 1mm;">2</td>
						<td class="attempts" style="width: 34%; text-align: center; padding: 1mm;">3</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>';

	// Write HTML to PDF
	$mpdf->WriteHTML($html);

	// Output the PDF to the browser
	$mpdf->Output('waybill.pdf', 'I');
}






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