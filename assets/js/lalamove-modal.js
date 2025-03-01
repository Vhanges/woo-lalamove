jQuery(document).ready(function($) {
    // Add custom CSS styles for the modal and Leaflet map
    var customModalCss = `
        <style>
            /* Custom Bootstrap Modal Styles for WooCommerce */
            #customModal .modal-content {
                background-color: #ffffff;
                color: #333333;
                font-family: inherit;
            }
            #customModal .modal-dialog {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            #customModal .modal-header {
                border-bottom: 1px solid #e0e0e0;
            }
            #customModal .modal-footer {
                border-top: 1px solid #e0e0e0;
            }
            #customModal .modal-title {
                font-size: 1.5rem;
            }
            #customModal .btn-primary {
                background-color: #f16622;
                border-color: #f16622;
            }
            #customModal .btn-secondary {
                background-color: #cccccc;
                border-color: #cccccc;
            }
            /* Leaflet map styles */
            #map {
                width: 100%;
                height: 500px;
            }
        </style>
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    `;
    $('head').append(customModalCss);

    // Remove any previously bound click handlers for the radio button
    $(document).off('click', 'input[name="radio-control-0"]');

    // Use event delegation with an async function for the click event
    $(document).on('click', 'input[name="radio-control-0"]', async function() {
        var selectedValue = $(this).val();
        if (selectedValue === 'your_shipping_method') {
            $(this).css('background-color', 'yellow');
            $(this).next('label').css({
                'color': 'blue',
                'font-weight': 'bold'
            });
            console.log('Clicked shipping method radio button:', $(this));

            // Define and append the modal with a loading state
            var modalHtml = `
                <div class="modal fade" id="customModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content" style="min-height: 600px; max-height: 600px; min-width: 600px; max-width: 600px;">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Shipping Data</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div id="map"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary w-100" id="saveLocation">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modalHtml);

            // Trigger the modal
            $('#customModal').modal('show');

            // When modal is shown, remove aria-hidden and load data
            $('#customModal').on('shown.bs.modal', async function () {
                $(this).removeAttr('aria-hidden');

                var siteUrl = window.location.origin;
                try {
                    let get_city = await $.ajax({
                        url: siteUrl + '/wp-json/woo-lalamove/v1/get-city',
                        method: 'GET',
                        data: { shipping_method: selectedValue }
                    });

                    const [cebu, manila, luzon] = get_city;
                    $('#customModal .modal-body').html(`
                        <div id="map"></div>
                        <p><strong>City:</strong> ${cebu.locode}</p>
                        <p><strong>Other Info:</strong> ${cebu.name}</p>
                    `);
                    console.log('Fetched locode:', cebu.locode);

                    // Initialize the Leaflet map with enhanced geolocation accuracy
                    initMap();
                } catch (error) {
                    console.error('AJAX request failed:', error);
                    $('#customModal .modal-body').html(`
                        <p>Error loading data: ${error.statusText || error}</p>
                    `);
                }
            });

            // Remove modal from DOM when hidden
            $('#customModal').on('hidden.bs.modal', function () {
                $(this).remove();
            });
        }
    });



    
    // Initialize the Leaflet map and continuously update user's position
    function initMap() {
        var map = L.map('map').setView([12.8797, 121.7740], 6); // Default center on the Philippines

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        var marker = L.marker([12.8797, 121.7740], { draggable: true }).addTo(map);

        marker.on('dragend', function(e) {
            var latlng = marker.getLatLng();
            console.log('Marker dragged to: Latitude: ' + latlng.lat + ', Longitude: ' + latlng.lng);
            window.markerLat = latlng.lat;
            window.markerLng = latlng.lng;
        });

        // Geolocation options for high accuracy
        var options = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        };

        function success(position) {
            var lat = position.coords.latitude;
            var lon = position.coords.longitude;
            

            map.setView([lat, lon], 15); // Zoom to user's location
            marker.setLatLng([lat, lon]); // Update marker position
            console.log('User Location - Latitude: ' + lat + ', Longitude: ' + lon);
            window.markerLat = lat;
            window.markerLng = lon;
        }

        function error(err) {
            console.warn(`ERROR(${err.code}): ${err.message}`);
            alert('Error getting your location. Please try again or adjust your settings.');
        }

        // Get current position and start watching for updates
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(success, error, options);
            navigator.geolocation.watchPosition(success, error, options);
        } else {
            alert('Geolocation is not supported by this browser.');
        }
    }
});
