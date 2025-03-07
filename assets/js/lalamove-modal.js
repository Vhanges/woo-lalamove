jQuery(document).ready(function ($) {
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
            #customModal .modal-body {
                overflow: auto;
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
                height: 35vh;
                border-radius: 8px;

            }
            .vehicle {
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 1rem;
                width: 150px;
                max-width: 150px;
                min-width: 150px;
                border-radius: 5px;
                height: 180px;
                border: 2px solid #EDEDED;
            }

            .content{
                display: flex;  
                align-self: flex-end;
                gap: 1rem;
                flex-direction: column;
                justify-content: flex-end;
                align-items: center;
            }


        </style>
        <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    `;
  $("head").append(customModalCss);

  // Remove any previously bound click handlers for the radio button
  $(document).off("click", 'input[name="radio-control-0"]');

  // Use event delegation with an async function for the click event
  $(document).on("click", 'input[id="radio-control-0-your_shipping_method"]', async function () {
    var selectedValue = $(this).val();
    if (selectedValue === "your_shipping_method") {
      $(this).css("background-color", "yellow");
      $(this).next("label").css({
        color: "blue",
        "font-weight": "bold",
      });
      console.log("Clicked shipping method radio button:", $(this));

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

                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-primary w-100" id="saveLocation">Save</button>
                    </div>
                </div>
              </div>
            </div>
        `;
      $("body").append(modalHtml);

      // Trigger the modal
      $("#customModal").modal("show");

      // When modal is shown, remove aria-hidden and load data
      $("#customModal").on("shown.bs.modal", async function () {
        $(this).removeAttr("aria-hidden");

        var siteUrl = window.location.origin;
        try {

          // Fetch city data via AJAX
          let get_city = await $.ajax({
            url: siteUrl + "/wp-json/woo-lalamove/v1/get-city",
            method: "GET",
            data: { shipping_method: selectedValue },
          });

          
          // Fetch checkout product data via AJAX
          let productData = await $.ajax({
            url: pluginAjax.ajax_url,
            method: "POST",
            data: {
              action: "get_checkout_product_data",
              nonce: pluginAjax.nonce,
              image: pluginAjax.image_url,
            },
          });

          if (productData.success) {
            console.log("Product data:", productData.data);
          }
            
            const [cebu, manila, luzon] = get_city;
            window.cebu = cebu;
            window.manila = manila.services;
            window.luzon = luzon;
            window.totalWeight = 0;

            console.log("Cebu services:", window.cebu);
            console.log("Manila services:", window.manila);
            console.log("Luzon services:", window.luzon);
            productData.data.forEach(product => {
              window.totalWeight = product.quantity * product.weight;
            });

            console.log("Total weight:", window.totalWeight);


          
            $("#customModal .modal-body").html(`
                 <div id="map"></div>
                  <form id="deliveryForm" class="d-flex flex-column justify-content-start align-items-center delivery">

                  <p class="header">VEHICLE TYPE</p>
                    <div class="vehicle-wrapper w-100 d-flex flex-row justify-content-around">

                    </div>
                  </form>
                `);


          // Initialize the Leaflet map with enhanced geolocation accuracy
          initMap();
        } catch (error) {
          console.error("AJAX request failed:", error);
          $("#customModal .modal-body").html(`
                        <p>Error loading data: ${error.statusText || error}</p>
                    `);
        }
      });

      // Remove modal from DOM when hidden
      $("#customModal").on("hidden.bs.modal", function () {
        $(this).remove();
      });
    }
  });

  // Flag to prevent multiple location fetches
  let locationFetched = false;

  function groupAddServices(specialRequests) {
    // Group the specialRequests by parent_type
    const groupedSpecialRequests = specialRequests.reduce((acc, request) => {
      if (request.parent_type) {
        // If the parent_type group does not exist in the accumulator, create it
        if (!acc.withParentType[request.parent_type]) {
          acc.withParentType[request.parent_type] = [];
        }
        // Add the current request to the appropriate group
        acc.withParentType[request.parent_type].push(request);
      } else {
        // Add to the group without parent_type
        acc.withoutParentType.push(request);
      }
      return acc;
    }, { withParentType: {}, withoutParentType: [] });
  
    // Assign the grouped result to addServices.value
    addServices.value = groupedSpecialRequests;
  
    // Log the grouped special requests
    console.log('Grouped Special Requests:', groupedSpecialRequests);
  };

  // Initialize the Leaflet map and continuously update user's position
  function initMap() {
    var map = L.map("map").setView([12.8797, 121.774], 6); // Default center on the Philippines

    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 18,
      attribution: "© OpenStreetMap",
    }).addTo(map);

    var marker = L.marker([12.8797, 121.774], { draggable: true }).addTo(map);

    marker.on("dragend", function (e) {
      var latlng = marker.getLatLng();
      console.log(
        "Marker dragged to: Latitude: " +
          latlng.lat +
          ", Longitude: " +
          latlng.lng
      );
      window.markerLat = latlng.lat;
      window.markerLng = latlng.lng;
    });

    // Geolocation options for high accuracy
    var options = {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 0,
    };

    function success(position) {
      if (locationFetched) return; // Prevent multiple location fetches

      var lat = position.coords.latitude;
      var lon = position.coords.longitude;

      map.setView([lat, lon], 15); // Zoom to user's location
      marker.setLatLng([lat, lon]); // Update marker position
      console.log("User Location - Latitude: " + lat + ", Longitude: " + lon);
      window.markerLat = lat;
      window.markerLng = lon;

      // List of Lalamove available regions
      const cebuIslandwide = ["Cebu"];

      const manilaNCRSouthLuzon = [
        "Metro Manila",
        "Cavite",
        "Laguna",
        "Batangas",
        "Rizal",
        "Quezon",
      ];

      const northCentralLuzon = [
        "Pampanga",
        "Bulacan",
        "Bataan",
        "Zambales",
        "Tarlac",
        "Nueva Ecija",
        "La Union",
        "Ilocos Norte",
        "Ilocos Sur",
        "Abra",
        "Apayao",
        "Kalinga",
        "Benguet",
        "Ifugao",
        "Mountain Province",
        "Cagayan",
        "Isabela",
        "Nueva Vizcaya",
      ];

      // Reverse geocode to get region name
      $.getJSON(
        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`,
        function (data) {
          if (data && data.address) {
            var state = data.address.state;
            console.log("User is in: " + state);

            // Check if the detected region is in the Lalamove regions list
            if (cebuIslandwide.includes(state)) {
                window.cebu.forEach(service => {
                $("#customModal .vehicle-wrapper").prepend(
                  `
                  <div class="vehicle-content">
                      <div class="vehicle">
                          <div class="content">
                              <img src="/wp-content/plugins/woo-lalamove/assets/images/motorcycle.png" alt="motor" style="height: 60px; width: auto;">
                              <span id="vehicle-type" class="vehicle-type">${service.key}</span>
                          </div>
                      </div>
                  </div>
                  `
                );
                });
            } else if (manilaNCRSouthLuzon.includes(state)) {
                console.log("Manila and South Luzon", window.manila);

                let filteredArray = window.manila.filter(function(value) {
                return window.totalWeight < value.load.value;
                });

                // Sort the filtered array by the difference between load.value and totalWeight in ascending order
                filteredArray.sort(function(a, b) {
                return (a.load.value - window.totalWeight) - (b.load.value - window.totalWeight);
                });

                // Slice the first three elements from the sorted array
                let nearestValues = filteredArray.slice(0, 3);

              // Display these three nearest values in the modal
              nearestValues.reverse().forEach(function(value) {
                $("#customModal .vehicle-wrapper").prepend(
                    `
                    <div class="d-flex flex-column vehicle-content">
                        <div class="vehicle">
                            <div class="content">
                                <img src="/wp-content/plugins/woo-lalamove/assets/images/motorcycle.png" alt="motor" style="height: 60px; width: auto;">
                                <span id="vehicle-type" class="vehicle-type">${value.key}</span>
                            </div>
                        </div>
                    </div>
                    `
                );
              });

            } else if (northCentralLuzon.includes(state)) {
                window.luzon.forEach(service => {

                  $("#customModal .vehicle-wrapper").prepend(
                    `
                    <div class="vehicle-content">
                        <div class="vehicle">
                            <div class="content">
                                <img src="/wp-content/plugins/woo-lalamove/assets/images/motorcycle.png" alt="motor" style="height: 60px; width: auto;">
                                <span id="vehicle-type" class="vehicle-type">${service.key}</span>
                            </div>
                        </div>
                    </div>
                    `
                  );
                  });
            } else {
              $("#customModal .vehicle-type").prepend(
                `<p><strong>Detected Region:</strong> ${state} (Not available in Lalamove service area)</p>`
              );
              alert("Lalamove does not service your location.");
            }
          } else {
            alert("Region not found for the current location.");
          }
        }
      );

      locationFetched = true; // Set the flag to true after fetching the location
    }

    function error(err) {
      console.warn(`ERROR(${err.code}): ${err.message}`);
      alert(
        "Error getting your location. Please try again or adjust your settings."
      );
    }

    // Get current position and start watching for updates
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(success, error, options);
      navigator.geolocation.watchPosition(success, error, options);
    } else {
      alert("Geolocation is not supported by this browser.");
    }
  }
});