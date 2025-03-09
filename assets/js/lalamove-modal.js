jq = jQuery.noConflict(); 

jq(document).ready(function ($) {
  window.isVehicleSelected = false;
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

  $(document).off("click", 'input[id="radio-control-0-your_shipping_method"]');

  $(document).on("click", 'input[id="radio-control-0-your_shipping_method"]', async function (event) {
    event.preventDefault();
    event.stopPropagation();

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
                    <div class="modal-body ">

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
    
        window.siteUrl = window.location.origin;
        try {
            // Fetch city data via AJAX
            let get_city = await $.ajax({
                url: window.siteUrl + "/wp-json/woo-lalamove/v1/get-city",
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


    
            const [Cebu, NCR_South, North_Central] = get_city;
            window.cebu = Cebu.services;
            window.ncr_south = NCR_South.services;
            window.north_central = North_Central.services;

            window.totalWeight = 0;
            window.quantity = 0;

            productData.data.products.forEach(product => {
              window.totalWeight = product.quantity * product.weight;
              window.quantity += product.quantity;
          });

            window.shipping_address = productData.data.shipping_address;  

            console.log("Product data:", productData.data.products);
            console.log("Product data:", productData.data.shipping_address);



    
      
    
            $("#customModal .modal-body").html(`
              <div id="map" class="mb-4" style="height: 300px;"></div>
              <form id="deliveryForm" class="d-flex flex-column justify-content-center align-items-start delivery mb-6">
             
              <p class="header">VEHICLE TYPE</p>
              <div class="vehicle-wrapper w-100 d-flex flex-row justify-content-around mb-4"></div>

              <p class="header">Delivery Date & Contact Information</p>
              <div id="schedule-date" class="form-control d-flex flex-row align-items-center mb-4" style="background: #fff; cursor: pointer; border: 1px solid #ccc; width: 100%;" >
              </div>

              <p class="header">ADDITIONAL NOTES</p>
              <textarea class="form-control mb-4" id="additionalNotes" rows="3" placeholder="Enter any additional notes here..."></textarea>

              <div class="form-group d-flex justify-content-between align-items-center w-100">
                <p class="m-0 align-self-center justify-self-center">Optimize Route</p>  
                <p class="m-0 align-self-center justify-self-center">Find the most efficient route</p>  
                <div class="custom-control custom-switch">
                  <input type="checkbox" class="custom-control-input" id="customSwitch1">
                  <label class="custom-control-label" for="customSwitch1"></label>
                </div>
              </div>

              <div class="form-group d-flex justify-content-between align-items-center w-100">
                <p class="m-0 align-self-center justify-self-center">Proof of Delivery(POD)</p>  
                <p class="m-0 align-self-center justify-self-center">POD for picked up and received product</p>  
                <div class="custom-control custom-switch">
                  <input type="checkbox" class="custom-control-input" id="customSwitch2">
                  <label class="custom-control-label" for="customSwitch2"></label>
                </div>
              </div>

              <div class="pricing-details d-flex.flex-column.justify-content-center.align-items-start .mb-6 w-100">

              </div>

              </form>
            `);
    
            // Initialize the Leaflet map with enhanced geolocation accuracy
            initMap();
    
            var startDate = moment().startOf('month');
            var endDate = moment().add(30, 'days');

            function cb(start, end) {
              console.log('start:', start.format('MMMM D, YYYY HH:mm:ss'));
              $('#customModal #schedule-date').empty();
              $('#customModal #schedule-date').append(`
                  <p style="margin: 0;">${start.format('MMMM D, YYYY HH:mm:ss')} - ${end.format('MMMM D, YYYY HH:mm:ss')}</p>
                  <i class="bi bi-calendar" style="margin-left: auto;"></i>
              `);
            }
          
            var date = jQuery.noConflict();

            date('#schedule-date').daterangepicker({
              "startDate": startDate,
              "endDate": endDate,
              ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
             },
              minDate: moment().subtract(1, 'days'), // Minimum selectable date
              maxDate: moment().add(30, 'days'), // Maximum selectable date
              singleDatePicker: true, // Set to true for single date selection
              timePicker: true,
              timePicker24Hour: false,
              timePickerSeconds: false,
              timePickerIncrement: 15, // Time picker increment in minutes
              autoApply: true,
              opens: 'right',
              drops: 'up', // Set the position to open at the top
              showDropdowns: true, // Show month and year dropdowns
              locale: {
                format: 'DD/MM/YYYY HH:mm:ss',
                applyLabel: 'Apply', // Custom label for apply button
                cancelLabel: 'Cancel' // Custom label for cancel button
              },
            }, cb);

            cb(startDate, endDate);
    
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
      
      // Perform reverse geocoding when the marker is dragged
      reverseGeocode(latlng.lat, latlng.lng);

    }); 

    // Geolocation options for high accuracy
    var options = {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 6000,
    };

    function success(position) {
      if (locationFetched) return; // Prevent multiple location fetches

      var lat = position.coords.latitude;
      var lon = position.coords.longitude;
      

      map.setView([lat, lon], 15); // Zoom to user's location
      marker.setLatLng([lat, lon]); // Update marker position
      // console.log("User Location - Latitude: " + lat + ", Longitude: " + lon);


      // Perform reverse geocoding when the location is fetched
      reverseGeocode(lat, lon);

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

  function filterServices(state){

    if (!Array.isArray(state)) {
      console.error('Invalid state parameter:', state);
      return [];
  }

    
    let filteredArray = state.filter(function(value) {
      return window.totalWeight < value.load.value;
    });

    // Sort the filtered array by the difference between load.value and totalWeight in ascending order
    filteredArray.sort(function(a, b) {
      return (a.load.value - window.totalWeight) - (b.load.value - window.totalWeight);
    });

    // Slice the first three elements from the sorted array
    let nearestValues = filteredArray.slice(0, 3);

    return nearestValues;
  }

  // Function to perform reverse geocoding
  function reverseGeocode(lat, lon) {
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
          console.log("lat: " + lat + "lon: " + lon); 
          console.log("User is in: " + state);  

          var isRouteOptimized = document.getElementById("customSwitch1").checked;
          var proofOfDelivery = document.getElementById("customSwitch2").checked;

          window.body = {
            "data": {
                // "scheduleAt": "2022-04-01T14:30:00.00Z", // optional
                "serviceType": window.serviceType,
                "language": "en_PH",
                "stops": [
                  {
                      "coordinates": {
                          "lat": "14.555566",
                          "lng": "121.130056"
                      },
                      "address": "GFT Textile Main Office, Unit E, Sitio Malabon, Barangay San Juan, Hwy 2000, Taytay, 1920 Rizal"
                  },
                  {
                      "coordinates": {
                          "lat": lat.toString(),                      
                          "lng": lon.toString()
                      },
                      "address": "134 Cabrera Rd, Taytay, 1920 Rizal, Philippines"
                  }
                ],
                "isRouteOptimized": isRouteOptimized,
                "item": {
                    "quantity": window.quantity.toString(),
                    "weight": window.totalWeight.toString(),
                }
            }
          };

          console.log("Body", window.body); 


          if (cebuIslandwide.includes(state)) {

            centralIslandWideBlock()
            getQuotation(lat, lon);

          } else if (manilaNCRSouthLuzon.includes(state)) {

            manilaNCRSouthLuzonBlock();
            $("#customSwitch1").on("click", function () {
              if(window.isVehicleSelected){

                if ($(this).is(":checked")) {
                  window.body.data.serviceType = window.serviceType;
    
                  quotationAjax(window.body);
                } 

              }
            });

            $("#customSwitch2").on("click", function () {
              if(window.isVehicleSelected){

                if ($(this).is(":checked")) {
                  window.body.data.serviceType = window.serviceType;
    
                  quotationAjax(window.body);
                } 

              }
            });
            
            if(window.isVehicleSelected){


              $(document).on("click", ".vehicle", function () {
                window.serviceType = $(this).get(0).getAttribute("data-index");
  
                window.body.data.serviceType = window.serviceType;
                console.log("CLLICKED", window.serviceType);
  
                quotationAjax(window.body);
  
                return;
              });
            } else {
              getQuotation();
            }
          



          } else if (northCentralLuzon.includes(state)) {

            northCentralLuzonBlock()
            getQuotation(lat, lon);

          } else {
            $("#customModal .vehicle-wrapper").empty();

            alert("Lalamove does not service your location.");
          }
        } else {
          alert("Region not found for the current location.");
        }
      }
    );
  }

  function centralIslandWideBlock(){
    $("#customModal .vehicle-wrapper").empty();

    var cebuServices = filterServices(window.cebu).reverse();
    cebuServices.forEach(function(value, index) {
      $("#customModal .vehicle-wrapper").prepend(
      `
      <div class="d-flex flex-column vehicle-content" >
        <div class="vehicle" data-index="${index}">
          <div class="content">
            <img src="/wp-content/plugins/woo-lalamove/assets/images/motorcycle.png" alt="motor" style="height: 60px; width: auto;">
            <span id="vehicle-type" class="vehicle-type">${value.key}</span>
          </div>
        </div>
      </div>
      `
      );
    });
  }
  function manilaNCRSouthLuzonBlock(){
    $("#customModal .vehicle-wrapper").empty();

    var manilaServices = filterServices(window.ncr_south).reverse();
    manilaServices.forEach(function(value) {
      $("#customModal .vehicle-wrapper").prepend(
      `
      <div class="d-flex flex-column vehicle-content" >
        <div class="vehicle" data-index="${value.key}">
          <div class="content">
            <img src="/wp-content/plugins/woo-lalamove/assets/images/motorcycle.png" alt="motor" style="height: 60px; width: auto;">
            <span id="vehicle-type" class="vehicle-type">${value.key}</span>
          </div>
        </div>
      </div>
      `
      );
    });
 
  }
  function northCentralLuzonBlock(){
    $("#customModal .vehicle-wrapper").empty();

    var NclServices = filterServices(window.north_central).reverse();
    NclServices.forEach(function(value, index) {
      $("#customModal .vehicle-wrapper").prepend(
      `
      <div class="d-flex flex-column vehicle-content" >
        <div class="vehicle" data-index="${index}">
          <div class="content">
            <img src="/wp-content/plugins/woo-lalamove/assets/images/motorcycle.png" alt="motor" style="height: 60px; width: auto;">
            <span id="vehicle-type" class="vehicle-type">${value.key}</span>
          </div>
        </div>
      </div>
      `
      );
    });
  }
  function getQuotation(){
    $(document).on("click", ".vehicle", function () {
      window.isVehicleSelected = true;
      window.serviceType = $(this).get(0).getAttribute("data-index");


      window.body.data.serviceType = window.serviceType;
      console.log("CLLICKED", window.serviceType);

      quotationAjax(window.body);

      return
    });

  }

  function quotationAjax(body){
    let quotation = $.ajax({
      url: `${wpApiSettings.root}woo-lalamove/v1/get-quotation`,
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify(body),
      headers: { 'X-WP-Nonce': wpApiSettings.nonce },
      success: function(response) {

          
          const additionalNotes = document.getElementById("additionalNotes").value;

          // Get the state of the checkboxes
          const optimizeRoute = document.getElementById("customSwitch1").checked;
          const proofOfDelivery = document.getElementById("customSwitch2").checked;

          console.log("Additional Notes:", additionalNotes);
          console.log("Optimize Route:", optimizeRoute);
          console.log("Proof of Delivery:", proofOfDelivery);
        
          console.log("Response received:", response);

          window.quotationId = response.data.quotationId;
          window.currency = response.data.priceBreakdown.currency? response.data.priceBreakdown.currency : null; ; 
          window.total = response.data.priceBreakdown.total? response.data.priceBreakdown.total : null;   
          var base   = response.data.priceBreakdown.base? response.data.priceBreakdown.base : null;  ;    
          var extraMileage = response.data.priceBreakdown.extraMileage? response.data.priceBreakdown.extraMileage : null; ; 
          var surcharge = response.data.priceBreakdown.surcharge? response.data.priceBreakdown.surcharge : null; ;    

          var total= window.total;
          var currency = window.currency;

          $('#customModal .modal-footer').empty();

          $('#customModal .modal-footer').prepend(`
              <div class="total-wrapper w-100 d-inline-flex flex-column justify-content-end align-items-start gap-2 p-3" style="width: auto; height: 80px; background: white; border-radius: 5px; overflow: hidden; border: 1px solid #EDEDED;">
                <div class="text-dark" style="font-size: 14px; font-weight: 400;">TOTAL: </div>
                <div class="text-dark" style="font-size: 20px; font-weight: 600;">${currency + " " + total}</div>
              </div>  
              
              <button type="button" class="btn btn-primary w-100" id="saveLocation">Save</button>

          `);

          $('#customModal .pricing-details').empty();  

          let baseContent = `
            <div class="form-group m-0 d-flex justify-content-between align-items-between w-100">
                  <p class="m-0 align-self-center justify-self-center">Base Fare</p>  
                  <p class="m-0 align-self-center justify-self-center">${currency + " "+base}</p>  
            </div>
          `;
          let extraMileageContent = `
            <div class="form-group m-0 d-flex justify-content-between align-items-between w-100">
                  <p class="m-0 align-self-center justify-self-center">Additional Distance Fee</p>  
                  <p class="m-0 align-self-center justify-self-center">${currency + " "+ extraMileage}</p>  
            </div>
          `;
          let surchargeContent = `
            <div class="form-group m-0 d-flex justify-content-between align-items-between w-100">
                  <p class="m-0 align-self-center justify-self-center">Surcharge</p>  
                  <p class="m-0 align-self-center justify-self-center">${currency + " "+surcharge}</p>  
            </div>
          `;
            
          $('#customModal .pricing-details').prepend(`              
            <p class="header">Pricing Details</p>
          `);

          if(base !== null){
            $('#customModal .pricing-details').append(baseContent);
          }
          if(extraMileage !== null){
            $('#customModal .pricing-details').append(extraMileageContent);
          }
          if(surcharge !== null){
            $('#customModal .pricing-details').append(surchargeContent);
          }

          // Add event listener for saveLocation button
          $('#saveLocation').on('click', function() {
            // Collect data from the form
            const additionalNotes = $('#additionalNotes').val();
            const optimizeRoute = $('#customSwitch1').is(':checked');
            const proofOfDelivery = $('#customSwitch2').is(':checked');


            // Log the collected data
            console.log('Saving data...');
            console.log('Additional Notes:', additionalNotes);
            console.log('Optimize Route:', optimizeRoute);
            console.log('Proof of Delivery:', proofOfDelivery);
            console.log('Quotation ID', window.quotationId);
            console.log('Currency', currency);
            console.log('Total', total);


            // Send the shipping cost to the server via AJAX
            $.ajax({
                url: pluginAjax.ajax_url, // Replace `ajax_object` with your localized object name
                method: 'POST',
                data: {
                    action: 'update_shipping_rate', // Custom AJAX action
                    shipping_cost: total // Cost from modal
                },
                success: function (response) {
                    if (response.success) {
                        console.log('Shipping rate updated successfully.');
    
                        // Trigger checkout refresh to update the totals
                        $('body').trigger('update_checkout');
                    } else {
                        console.error(response.data.message);
                    }
                },
                error: function (error) {
                    console.error('Error updating shipping rate:', error);
                }
            });

            $('#customModal').modal('hide');
          });

      },
      error: function(xhr, status, error) {
          console.error("Error occurred:", status, error);
      }
    });
  }
});

