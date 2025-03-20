jq = jQuery.noConflict();

jq(document).ready(function ($) {
  var extensionCartUpdate = wc.blocksCheckout;
  extensionCartUpdate.extensionCartUpdate({
    namespace: "your_custom_namespace",
    data: { testData: "testing" },
  });


  var SessionData = {
    lat: null,                  // Latitude from geolocation
    lng: null,                  // Longitude from geolocation
    serviceType: null,          // Selected vehicle/service type
    scheduleDate: null,         // Delivery schedule date (ISO string)
    additionalNotes: "",        // User-provided additional notes
    optimizeRoute: false,       // Whether route optimization is enabled
    proofOfDelivery: false,     // Whether Proof of Delivery (POD) is enabled
    priceBreakdown: {},         // Object to store price breakdown info
    requestBody: {}             // The body data for quotation AJAX calls
  };

  // Function to save state to sessionStorage
  function saveSessionData() {
    sessionStorage.setItem('SessionData', JSON.stringify(SessionData));
    console.log("SessionData saved to sessionStorage:", SessionData);
  }

  // Function to load state from sessionStorage
  function loadSessionData() {
    var storedState = sessionStorage.getItem('SessionData');
    if (storedState) {
      SessionData = JSON.parse(storedState);
      console.log("SessionData loaded from sessionStorage:", SessionData);
    }
  }

  loadSessionData();

 
  
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

`;
  $("head").append(customModalCss);

  $(document).off("click", 'input[id="radio-control-0-your_shipping_method"]');

  $(document).on(
    "click",
    'input[id="radio-control-0-your_shipping_method"]',
    async function (event) {
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

            productData.data.products.forEach((product) => {
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

          
            $("#customModal .vehicle-wrapper").empty();

            var manilaServices = filterServices(window.ncr_south).reverse();
            manilaServices.forEach(function (value) {
              

              var selectedVehicle = (SessionData.serviceType === value.key) 
              ? 'style="border: solid 2px #f16622"'
              : "";
              

              $("#customModal .vehicle-wrapper").prepend(
                `
                <div class="d-flex flex-column vehicle-content">
                  <div class="vehicle" ${selectedVehicle} data-index="${value.key}">
                    <div class="content">
                      <img src="/wp-content/plugins/woo-lalamove/assets/images/motorcycle.png" alt="motor" style="height: 60px; width: auto;">
                      <span id="vehicle-type" class="vehicle-type">${value.key}</span>
                    </div>
                  </div>
                </div>
                `
              );



            });
            

            var startDate = null;
            if(SessionData.scheduleDate){
                startDate = moment(SessionData.scheduleDate, moment.ISO_8601);
            } else{
                startDate = moment().add(1, "days");
            }

            var endDate = moment().add(30, "days");

            function cb(start, end) {
              console.log("start:", start.format("MMMM D, YYYY HH:mm:ss"));
              window.scheduleDate = start.toISOString();   

              console.log("Selected schedule date:", window.scheduleDate);
              $("#customModal #schedule-date").empty();
              $("#customModal #schedule-date").append(`
                  <p style="margin: 0;">${start.format(
                    "MMMM D, YYYY HH:mm"
                  )}</p>
                  <i class="bi bi-calendar" style="margin-left: auto;"></i>
              `);
            }

            var date = jQuery.noConflict();

            date("#schedule-date").daterangepicker(
              {
                startDate: startDate,
                endDate: endDate,
                ranges: {
                  Today: [moment(), moment()],
                  Yesterday: [
                    moment().subtract(1, "days"),
                    moment().subtract(1, "days"),
                  ],
                  "Last 7 Days": [moment().subtract(6, "days"), moment()],
                  "Last 30 Days": [moment().subtract(29, "days"), moment()],
                  "This Month": [
                    moment().startOf("month"),
                    moment().endOf("month"),
                  ],
                  "Last Month": [
                    moment().subtract(1, "month").startOf("month"),
                    moment().subtract(1, "month").endOf("month"),
                  ],
                },
                minDate: moment().add(1, "days"), // Minimum selectable date
                maxDate: moment().add(30, "days"), // Maximum selectable date
                singleDatePicker: true, // Set to true for single date selection
                timePicker: true,
                timePicker24Hour: false,
                timePickerSeconds: false,
                timePickerIncrement: 15, // Time picker increment in minutes
                autoApply: true,
                opens: "right",
                drops: "up", // Set the position to open at the top
                showDropdowns: true, // Show month and year dropdowns
                locale: {
                  format: "DD/MM/YYYY HH:mm:ss",
                  applyLabel: "Apply", // Custom label for apply button
                  cancelLabel: "Cancel", // Custom label for cancel button
                },
              },
              cb
            );

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
    }
  );

  // Initialize the Leaflet map and conditionally fetch user location
  function initMap() {
    const defaultLat = 12.8797;
    const defaultLng = 121.774;

    //Verify if a service is already selected
    var isVehicleSelected = SessionData.serviceType ? true : false;

    // Use stored session data if available, otherwise fallback to defaults
    let mapLat = SessionData.lat || defaultLat;
    let mapLng = SessionData.lng || defaultLng;

    console.log("Using Coordinates:", mapLat, mapLng);

    // Initialize the map and marker
    const map = L.map("map").setView([mapLat, mapLng], 15);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 18,
      attribution: "© OpenStreetMap"
    }).addTo(map);

    const marker = L.marker([mapLat, mapLng], { draggable: true }).addTo(map);

    // Bind service events using initial coordinates
    bindServiceEvents(mapLat, mapLng);

    // Update marker location on dragend
    marker.on("dragend", () => {

      console.log("Dragend event triggered");
      const latlng = marker.getLatLng();
      mapLat = latlng.lat;
      mapLng = latlng.lng;
    
      SessionData.lat = mapLat;
      SessionData.lng = mapLng;
      sessionStorage.setItem("sessionData", JSON.stringify(SessionData));
    
      triggerServiceEvents(mapLat, mapLng, isVehicleSelected);

    });
    
    // If session data exists, skip geolocation fetching
    if (SessionData.lat && SessionData.lng) {
      console.log("Session data exists. Skipping geolocation.");
      return;
    }

    console.log("Fetching user location...");

    // Geolocation options for high accuracy
    const options = {
      enableHighAccuracy: true,
      timeout: 10000,
      maximumAge: 6000
    };

    const success = (position) => {
      mapLat = position.coords.latitude;
      mapLng = position.coords.longitude;

      // Save fetched location to session
      SessionData.lat = mapLat;
      SessionData.lng = mapLng;
      sessionStorage.setItem("sessionData", JSON.stringify(SessionData));

      map.setView([mapLat, mapLng], 15);
      marker.setLatLng([mapLat, mapLng]);
      console.log("User Location - Latitude:", mapLat, "Longitude:", mapLng);

      triggerServiceEvents(mapLat, mapLng, isVehicleSelected);
    };

    const error = (err) => {
      console.warn(`ERROR(${err.code}): ${err.message}`);
    };

    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(success, error, options);
      navigator.geolocation.watchPosition(success, error, options);
    } else {
      alert("Geolocation is not supported by this browser.");
    }
  }

  // Bind service-related events (using the provided lat and lng)
  function bindServiceEvents(lat, lng, isVehicleSelected) {

    if(isVehicleSelected){

        quotationAjax(lat, lng);
        return;

    }

    // Bind the click event on the switch button
    $(document)
      .off("click", "#customSwitch1")
      .on("click", "#customSwitch1", function () {
        if ($(this).is(":checked")) {
          quotationAjax(lat, lng);
        }
      });

    // Bind the click event on vehicle elements
    $(document)
      .off("click", ".vehicle")
      .on("click", ".vehicle", function () {

        $(".vehicle").css("border", "solid 2px #EDEDED");
        $(this).css("border", "solid 2px #f16622");

        window.serviceType = $(this).attr("data-index");
        quotationAjax(lat, lng);
      });
  }

  // Update the service events with the new coordinates
  function triggerServiceEvents(lat, lng, isVehicleSelected) {
    bindServiceEvents(lat, lng, isVehicleSelected);
  }


  function filterServices(state) {
    if (!Array.isArray(state)) {
      console.error("Invalid state parameter:", state);
      return [];
    }

    let filteredArray = state.filter(function (value) {
      return window.totalWeight < value.load.value;
    });

    // Sort the filtered array by the difference between load.value and totalWeight in ascending order
    filteredArray.sort(function (a, b) {
      return (
        a.load.value - window.totalWeight - (b.load.value - window.totalWeight)
      );
    });

    // Slice the first three elements from the sorted array
    let nearestValues = filteredArray.slice(0, 3);

    return nearestValues;
  }

  function quotationAjax(lat, lng) {
    isRouteOptimized = document.getElementById("customSwitch1").checked;

    //Set data for saving session
    SessionData.lat = lat;
    SessionData.lng = lng;
    SessionData.serviceType = window.serviceType;
    SessionData.scheduleDate = window.scheduleDate;

    window.body = {
      data: {
        scheduleAt: SessionData.scheduleDate,
        serviceType: SessionData.serviceType,
        language: "en_PH",
        stops: [
          {
            coordinates: {
              lat: "14.555566",
              lng: "121.130056",
            },
            address:
              "GFT Textile Main Office, Unit E, Sitio Malabon, Barangay San Juan, Hwy 2000, Taytay, 1920 Rizal",
          },
          {
            coordinates: {
              lat: lat.toString(),
              lng: lng.toString(),
            },
            address: "134 Cabrera Rd, Taytay, 1920 Rizal, Philippines",
          },
        ],
        isRouteOptimized: isRouteOptimized,
        item: {
          quantity: window.quantity.toString(),
          weight: window.totalWeight.toString(),
        },
      },
    };

    console.log("MERON NAMAN: ", body);

    let quotation = $.ajax({
      url: `${wpApiSettings.root}woo-lalamove/v1/get-quotation`,
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify(body),
      headers: { "X-WP-Nonce": wpApiSettings.nonce },
      success: function (response) {
        const customerFName = document.getElementById(
          "shipping-first_name"
        ).value;
        const customerLName =
          document.getElementById("shipping-last_name").value;
        const customerPhoneNo = document.getElementById("shipping-phone").value;
        const additionalNotes =
          document.getElementById("additionalNotes").value;

        console.log("Additional Notes:", additionalNotes);
        console.log("Response received:", response);

        window.currency = response.data.priceBreakdown.currency
          ? response.data.priceBreakdown.currency
          : null;
        window.total = response.data.priceBreakdown.total
          ? response.data.priceBreakdown.total
          : null;
        var base = response.data.priceBreakdown.base
          ? response.data.priceBreakdown.base
          : null;
        var extraMileage = response.data.priceBreakdown.extraMileage
          ? response.data.priceBreakdown.extraMileage
          : null;
        var surcharge = response.data.priceBreakdown.surcharge
          ? response.data.priceBreakdown.surcharge
          : null;

        var total = window.total;
        var currency = window.currency;

        $("#customModal .modal-footer").empty();

        $("#customModal .modal-footer").prepend(`
              <div class="total-wrapper w-100 d-inline-flex flex-column justify-content-end align-items-start gap-2 p-3" style="width: auto; height: 80px; background: white; border-radius: 5px; overflow: hidden; border: 1px solid #EDEDED;">
                <div class="text-dark" style="font-size: 14px; font-weight: 400;">TOTAL: </div>
                <div class="text-dark" style="font-size: 20px; font-weight: 600;">${
                  currency + " " + total
                }</div>
              </div>  
              
              <button type="button" class="btn btn-primary w-100" id="saveLocation">Save</button>

          `);

        $("#customModal .pricing-details").empty();

        let baseContent = `
            <div class="form-group m-0 d-flex justify-content-between align-items-between w-100">
                  <p class="m-0 align-self-center justify-self-center">Base Fare</p>  
                  <p class="m-0 align-self-center justify-self-center">${
                    currency + " " + base
                  }</p>  
            </div>
          `;
        let extraMileageContent = `
            <div class="form-group m-0 d-flex justify-content-between align-items-between w-100">
                  <p class="m-0 align-self-center justify-self-center">Additional Distance Fee</p>  
                  <p class="m-0 align-self-center justify-self-center">${
                    currency + " " + extraMileage
                  }</p>  
            </div>
          `;
        let surchargeContent = `
            <div class="form-group m-0 d-flex justify-content-between align-items-between w-100">
                  <p class="m-0 align-self-center justify-self-center">Surcharge</p>  
                  <p class="m-0 align-self-center justify-self-center">${
                    currency + " " + surcharge
                  }</p>  
            </div>
          `;

        $("#customModal .pricing-details").prepend(`              
            <p class="header">Pricing Details</p>
          `);

        if (base !== null) {
          $("#customModal .pricing-details").append(baseContent);
        }
        if (extraMileage !== null) {
          $("#customModal .pricing-details").append(extraMileageContent);
        }
        if (surcharge !== null) {
          $("#customModal .pricing-details").append(surchargeContent);
        }

        // Add event listener for saveLocation button
        $("#saveLocation").on("click", function () {
          // Collect data from the form
          const additionalNotes = $("#additionalNotes").val();
          const optimizeRoute = $("#customSwitch1").is(":checked");
          const proofOfDelivery = $("#customSwitch2").is(":checked");

          // Log the collected data
          console.log("Saving data...");
          console.log("Additional Notes:", additionalNotes);
          console.log("Optimize Route:", optimizeRoute);
          console.log("Proof of Delivery:", proofOfDelivery);
          console.log("Quotation ID", window.quotationId);
          console.log("Quotation Body", body);

          quotationID = window.quotationId;
          // Set the quotation ID as a session variable
          jQuery.ajax({
            url: pluginAjax.ajax_url,
            method: "POST",
            data: {
              action: "set_quotation_data_session",
              quotationID: response.data.quotationId,
              stopId0: response.data.stops[0].stopId,
              stopId1: response.data.stops[1].stopId,
              customerFName: customerFName,
              customerLName: customerLName,
              customerPhoneNo: customerPhoneNo,
              additionalNotes: additionalNotes,
              proofOfDelivery: proofOfDelivery,
            },
            success: function (response) {
              if (response.success) {
                console.log("Quotation ID set in session successfully.");
              } else {
                console.error(
                  "Failed to set Quotation ID in session:",
                  response.data.message
                );
              }
            },
            error: function (error, xhr) {
              console.error("Error setting Quotation ID in session:", error);
              console.error("XHR Response Text:", xhr.responseText);
            },
          });
          console.log("Currency", currency);
          console.log("Total", total);

          let ajaxTimer;
          clearTimeout(ajaxTimer);

          (ajaxTimer = setTimeout(function () {
            // Send the shipping cost to the server via AJAX
            jQuery.ajax({
              url: pluginAjax.ajax_url, // Replace `ajax_object` with your localized object name
              method: "POST",
              data: {
                action: "update_shipping_rate", // Custom AJAX action
                shipping_cost: total, // Cost from modal
              },
              success: function (response) {
                // Solution One
                // if ( typeof wp !== 'undefined' && typeof wp.data !== 'undefined' ) {
                //   wp.data.dispatch('wc/store/cart').invalidateResolutionForStore();
                // }

                if (
                  typeof wc !== "undefined" &&
                  typeof wc.blocksCheckout !== "undefined"
                ) {
                  var extensionCartUpdate = wc.blocksCheckout;
                  extensionCartUpdate.extensionCartUpdate({
                    namespace: "your_custom_namespace",
                    data: { testData: "testing" },
                  });
                }

                console.log("Shipping rate updated successfully.");

                saveSessionData();
              },
              error: function (error) {
                console.error("Error updating shipping rate:", error);
              },
            });
          })),
            500;

          $("#customModal").modal("hide");
        });
      },
      error: function (xhr, status, error) {
        console.error("Error occurred:", status, error);
      },
    });
  }
});
