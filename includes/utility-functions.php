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


function short_code_delivery_status(){

	$html = '        
                <div class="return-button ">
                    <a href="/my-account/orders" class="d-flex align-items-center" style="text-decoration: none;">
                    <span class="material-symbols-outlined">chevron_left</span> Return to Orders</a>
                </div>

                <div class="d-flex flex-column w-100 ">
                    <div class="d-flex flex-column justify-content-center align-items-center">
                        <p>Tracking Details</p>

                        <div class="status-container w-100 d-flex flex-row align-items-center justify-content-center">

                            <span class="d-flex flex-column" >
                                <div class="preparing-indicator d-flex flex-row align-items-center justify-content-center" style="background-color: yellow; border-radius: 100%; height: 50px; width: 50px;">
                                    <span class="material-symbols-outlined">orders</span> 
                                </div>
                                
                            </span>

                                <hr style="width: 5vw; height: 2px; background-color: #ODOCOC;">

                            <span class="d-flex flex-column" >
                                <div class="packed-indicator d-flex flex-row align-items-center justify-content-center" style="background-color: yellow; border-radius: 100%; height: 50px; width: 50px;">
                                    <span class="material-symbols-outlined">package</span> 
                                </div>
                                
                            </span>

                                <hr style="width: 5vw; height: 2px; background-color: #ODOCOC;">


                            <span class="d-flex flex-column" >
                                <div class="shipped-indicator d-flex flex-row align-items-center justify-content-center" style="background-color: yellow; border-radius: 100%; height: 50px; width: 50px;">
                                    <span class="material-symbols-outlined">local_shipping</span>
                                </div>
                                
                            </span>

                                <hr style="width: 5vw; height: 2px; background-color: #ODOCOC;">

                            <span class="d-flex flex-column" >
                                <div class="delivered-indicator d-flex flex-row align-items-center justify-content-center" style="background-color: yellow; border-radius: 100%; height: 50px; width: 50px;">
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

function short_code_delivery_location(){
	$html = '
		<div class="d-flex flex-column align-items-center mb-5">
			<p>Estimated Time to arrive in alasdowsi</p>
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
			<div class="col-3 p-0">
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