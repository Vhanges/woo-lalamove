jq = jQuery.noConflict();

jq(document).ready(function ($) {  

  $('input[name="shipping_method[0]"]').each(function () {
    if ($(this).val() == "your_shipping_method") {
        $(this).prop("checked", false); 
    }
  });

  $('input[name="billing_phone"]').each(function () {
    $(this).attr({
      'type': 'tel',
      'inputmode': 'numeric',
      'pattern': '^\\+[1-9]\\d{1,14}$',
      'title': 'Please enter a valid phone number in E.164 format (e.g., +63211234567)',
      'maxlength': '15',
      'required': true
    });
  });

  $('input[name="billing_phone"]').on('blur', function () {
      var input = $(this).val();
      var phoneNumber = libphonenumber.parsePhoneNumberFromString(input, 'NZ'); // Replace 'NZ' with dynamic country code if needed

      if (!phoneNumber || !phoneNumber.isValid()) {
          alert("Invalid phone number. Please enter a valid one in +63 format.");
          $(this).addClass('error'); // Add error class for styling
      } else {
          $(this).removeClass('error');
          $(this).val(phoneNumber.format('E.164')); // Format the phone number to E.164
      }
  });

  const prefix = '+63';
  const $phone = $('#billing_phone');

  $phone.on('focus', function() {
    let val = $(this).val();
    if (!val.startsWith(prefix)) {
      $(this).val(prefix);
    }
    this.setSelectionRange(prefix.length, prefix.length);
  });

  $phone.on('keydown', function(e) {
    const pos = this.selectionStart;
    if ((e.key === 'Backspace'  && pos <= prefix.length) ||
        (e.key === 'Delete'     && pos <  prefix.length)) {
      e.preventDefault();
    }
  });

  $phone.on('blur', function() {
    let val = $(this).val();
    if (!val.startsWith(prefix)) {
      $(this).val(prefix);
    }
  });


  function fieldsChecker (){

      let isFilled = true;

      const oldRequiredFields = [
        "#billing_first_name",
        "#billing_last_name",
        "#billing_address_1",
        "#billing_city",
        "#billing_postcode",
        "#billing_country",
        "#billing_phone"
    ];
    
    const newRequiredFields = [
        "#billing-first_name",
        "#billing-last_name",
        "#billing-address_1",
        "#billing-city",
        "#billing-postcode",
        "#billing-country",
        "#billing-phone",
    ];
    
    function areFieldsFilled(fieldSelectors) {
        return fieldSelectors.every((selector) => {
            const fieldValue = $(selector).val();
            return fieldValue && fieldValue.trim() !== ""; 
        });
    }
    
    // Check if either old or new fields are valid
    const oldFieldsValid = areFieldsFilled(oldRequiredFields);
    const newFieldsValid = areFieldsFilled(newRequiredFields);
    
    if (!oldFieldsValid && !newFieldsValid) {
        alert("Please fill in all required fields before proceeding.");
        $('input[id="radio-control-0-your_shipping_method"], #shipping_method_0_your_shipping_method').prop("checked", false);
        isFilled = false;
    }  

    return isFilled;
  }

  var SessionData = { 
    quotationID: null,
    coordinates:{},
    serviceType: null,
    stops:{}, 
    scheduleDate: null, 
    additionalNotes: "", 
    optimizeRoute: null, 
    proofOfDelivery: null,
    priceBreakdown: {}, 
  };

  loadSessionData();


  // Custom Modal HTML
  const modalHtml = `
    <div class="custom-modal" id="customModal" aria-hidden="true">
      <div class="custom-modal-dialog">
        <div class="custom-modal-content">
          <div class="custom-modal-header">
            <h5 class="custom-modal-title">Lalamove</h5>
            <span class="custom-modal-close" id="closeModal">&times;</span>
          </div>
          <div class="custom-modal-body"></div>
          <div class="custom-modal-footer">
            <button type="button" class="custom-modal-button" id="saveLocation">Save</button>
          </div>
        </div>
      </div>
    </div>
  `;

  // Append modal HTML to the body
  $("body").append(modalHtml);

  // Custom Modal CSS
  const modalCss = `
    <style>
      .custom-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
      }
      .custom-modal-dialog {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
      }
      .custom-modal-content {
        background: white;
        border-radius: 8px;
        width: 600px;
        max-width: 90%;
        padding: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      }
      .custom-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #ddd;
        padding-bottom: 10px;
      }
      .custom-modal-title {
        font-size: calc(1.2rem + 0.5vw);
        margin: 0;
      }
      .custom-modal-close {
        font-size: calc(1.2rem + 0.5vw);
        cursor: pointer;
      }
      .custom-modal-body {
        margin: 20px 0;
        max-height: 500px;
        overflow-y: auto;
      }
      .custom-modal-footer {
        display: flex;
        justify-content: flex-end;
        justify-item: center;
        align-items: center;
        gap: 10px;
        border-top: 1px solid #ddd;
        padding-top: 10px;
      }
      
    .total-wrapper {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      height: auto;
      background: white;
      border-radius: 8px;
      overflow: hidden;
      border: 1px solid #EDEDED;
      padding: 1rem;
      box-sizing: border-box;
    }

    .total-label {
      font-size: calc(0.9rem + 0.3vw);
      font-weight: 400;
      color: #333;
      flex: 1;
    }

    .total-amount {
      display: flex;
      font-size: calc(1rem + 0.5vw);
      font-weight: 600;
      color: #f16622;
      flex: 1;
      justify-content: flex-end;
    }

    .custom-modal-button {
      background-color: #f16622;
      color: white;
      border: none;
      padding: 0.75rem 1.5rem;
      border-radius: 5px;
      cursor: pointer;
      width: 100%;
      max-width: 200px;
      text-align: center;
    }

    .custom-modal-button:hover {
      background-color: #d4551c;
    }

    .custom-modal-button:disabled {
      background-color: #d4551c;
    }

    .pricing-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      margin-bottom: 0.5rem;
    }

    .item-name {
      font-size: calc(0.9rem + 0.3vw);
      font-weight: 400;
      color: #333;
      flex: 1;
    }

    .item-value {
      font-size: calc(0.9rem + 0.3vw);
      font-weight: 600;
      color: #f16622;
      text-align: right;
      flex: 1;
    }
    .loading-spinner {
      border: 4px solid #f3f3f3;
      border-top: 4px solid #f16622;
      border-radius: 50%;
      width: 40px;
      height: 40px;
      margin: 20px auto;
      animation: spin 1s linear infinite;
      display: block;
    }
    .total-loading-spinner {
      border: 4px solid #f3f3f3;
      border-top: 4px solid #f16622;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      animation: spin 1s linear infinite;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }
      100% {
        transform: rotate(360deg);
      }
    }
      #map {
      height: 300px;
      margin-bottom: 1rem;
    }

    #deliveryForm {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-start;
      margin-bottom: 1.5rem;
      max-width: 600px;
      padding: 1rem;
    }

    .header {
      font-size: calc(1rem + 0.4vw);
      font-weight: bold;
      margin-bottom: 0.5rem;
    }
    #schedule-date {
      display: flex;
      flex-direction: row;
      align-items: center;
      width: 100%;
      padding: 0.5rem;
      background: #fff;
      border: 1px solid #ccc;
      cursor: pointer;
      margin-bottom: 1rem;
    }

    textarea {
      width: 100%;
      margin-bottom: 1rem;
      padding: 0.5rem;
      resize: vertical;
      border: 1px solid #ccc;
    }

    .form-group {
      display: flex;
      justify-content: space-between;
      align-items: center;
      width: 100%;
      margin-bottom: 1rem;
    }

    .form-text {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }

    .switch {
      display: flex;
      align-items: center;
    }

    .switch input[type="checkbox"] {
      width: 20px;
      height: 20px;
      margin-right: 0.5rem;
    }

    .pricing-details {
      width: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: flex-start;
      margin-bottom: 1.5rem;
    }
    
    .vehicle-wrapper {
      width: 100%;
      height: fit-content;
      display: flex;
      flex-direction: column;
      justify-content: space-around;
      margin-bottom: 1rem;
      gap: 1rem;
    }

    .vehicle {
      display: flex;
      flex-direction: column;
      align-items: center;
      width: 100%; 
      padding: 0.5rem 1rem; 
      cursor: pointer;
      border-radius: 5px;
      background: #FCFCFC;
      border: 2px solid #EDEDED;
    }
    .vehicle-content {
      display: flex;
      justify-content: start;
      align-items: center;
      width: 100%; 
      background: #FCFCFC;
      gap: 1rem;
    }


    .vehicle-content-inner {
      display: flex;
      width: 100%;
      height: fit-content;
      flex-direction: column;
      align-items: center;
      gap: 0.5rem; 
    }

    .vehicle-description {
      width: 100%;
      padding: 1rem;
      border: 1px solid #EDEDED;
      border-radius: 5px;
      background: #f9f9f9;
      color: #555;
    }

    .vehicle-image {
      width: 40px;
      height: auto; 
    }

    .vehicle-type {
      font-size: 1rem; /* Standard font size */
      font-weight: 500;
      color: #333;
    }

    #schedule-date { 
      border: 2px solid #EDEDED;
      border-radius: 5px;
    }

    #additionalNotes { 
      border: 2px solid #EDEDED;
      border-radius: 5px;
    }

    input.error {
      border: 4px solid red;
    }

    @media (max-width: 450px) {
      .custom-modal-body {
        max-height: 400px;
      }

      .total-wrapper {
        flex-direction: column;
      }
    }

    </style>
  `;

  // Append modal CSS to the head
  $("head").append(modalCss);

  // Open the modal
  function openModal() {
    $("#customModal").fadeIn();
    $("body").css("overflow", "hidden");
    $("#customModal .custom-modal-body").html(`
      <div class="loading-spinner"></div>
    `);
    
    $('#saveLocation').prop("disabled", true);

  }

  // Close the modal
  function closeModal() {
    $("#customModal").fadeOut();
    $("body").css("overflow", "auto");
  }

  // Event listener for opening the modal
  $(document).on("click", 'input[name="shipping_method[0]"]', async function () {
    const selectedValue = $(this).val();

    if(fieldsChecker()){
      // Open the modal if shipping method is selected

      if (selectedValue === "your_shipping_method") {
        openModal();
        await fetchShippingData();
      }
    } 
    
    return;
  });


  // Event listener for closing the modal
  $(document).on("click", "#closeModal, .custom-modal", function (e) {
    if ($(e.target).is(".custom-modal") || $(e.target).is("#closeModal")) {
      closeModal();
    }
  });

  // Close modal on ESC key
  $(document).keydown(function (e) {
    if (e.key === "Escape") {
      closeModal();
    }
  });

  // Save button functionality
  $(document).on("click", "#saveLocation", function () {

    $(this).prop("disabled", true);

    saveSessionData();
    alert("Shipping details saved!");
    closeModal();

    setTimeout(function () {
      $(this).prop("disabled", false); 
    }, 1000);

  });

  // Example AJAX call (replace with your actual logic) */ 
  async function fetchShippingData() {
    try {


      await loadQuotationData();
      
      $("#customModal .custom-modal-body").html(`
         <!-- Map Container -->
         <div id="map"></div>
      `);
        
      // Initialize the Leaflet map with enhanced geolocation accuracy
      initMap();
          
      $("#customModal .custom-modal-body").append(`
        
        <!-- Delivery Form -->
        <form id="deliveryForm">
          <!-- Vehicle Type Section -->
          <p class="header">VEHICLE TYPE</p>
          <div class="vehicle-wrapper">
            <!-- Add vehicle type options here -->
          </div>

          <!-- Delivery Date & Contact Information Section -->
          <p class="header">Delivery Date & Contact Information</p>
          <div id="schedule-date">
            <!-- Date schedule content goes here -->
          </div>

          <!-- Additional Notes Section -->
          <p class="header">ADDITIONAL NOTES</p>
          <textarea id="additionalNotes" rows="3" placeholder="Enter any additional notes here..."></textarea>

          <!-- Optimize Route Switch -->
          <div class="form-group">
            <div class="form-text">
              <p>Optimize Route</p>  
              <p>Find the most efficient route</p>
            </div>
            <div class="switch">
              <input type="checkbox" id="optimizeRoute">
              <label for="optimizeRoute"></label>
            </div>
          </div>

          <!-- Proof of Delivery (POD) Switch -->
          <div class="form-group">
            <div class="form-text">
              <p>Proof of Delivery(POD)</p>  
              <p>POD for picked up and received product</p>
            </div>
            <div class="switch">
              <input type="checkbox" id="proofOfDelivery">
              <label for="proofOfDelivery"></label>
            </div>
          </div>

          <!-- Pricing Details Section -->
          <div class="pricing-details">
            <!-- Pricing details content goes here -->
          </div>
        </form>
      `);

      $("#additionalNotes").on("mouseleave", function() {
        console.log("Hovered: ", $(this).val());
        SessionData.additionalNotes = $(this).val();
      });

      $("#optimizeRoute").prop("checked", SessionData.optimizeRoute);
      $("#proofOfDelivery").prop("checked", SessionData.proofOfDelivery);

      let pricingDetailsContent = "";

      if (SessionData.priceBreakdown.base) {
        pricingDetailsContent += `
          <div class="form-group m-0 d-flex justify-content-between align-items-between w-100">
            <p class="m-0 align-self-center justify-self-center">Base Fare</p>
            <p class="m-0 align-self-center justify-self-center">${SessionData.priceBreakdown.currency +
          " " +
          SessionData.priceBreakdown.base
          }</p>
          </div>
        `;
      }

      if (SessionData.priceBreakdown.extraMileage) {
        pricingDetailsContent += `
          <div class="form-group m-0 d-flex justify-content-between align-items-between w-100">
            <p class="m-0 align-self-center justify-self-center">Additional Distance Fee</p>
            <p class="m-0 align-self-center justify-self-center">${SessionData.priceBreakdown.currency +
          " " +
          SessionData.priceBreakdown.extraMileage
          }</p>
          </div>
        `;
      }

      if (SessionData.priceBreakdown.surcharge) {
        pricingDetailsContent += `
          <div class="form-group m-0 d-flex justify-content-between align-items-between w-100">
            <p class="m-0 align-self-center justify-self-center">Surcharge</p>
            <p class="m-0 align-self-center justify-self-center">${SessionData.priceBreakdown.currency +
          " " +
          SessionData.priceBreakdown.surcharge
          }</p>
          </div>
        `;
      }

      let totalContent = "";

      if (SessionData.priceBreakdown.total) {
        totalContent += `
          <div class="total-wrapper w-100 d-inline-flex flex-column justify-content-end align-items-start gap-2 p-3" style="width: auto; height: 80px; background: white; border-radius: 5px; overflow: hidden; border: 1px solid #EDEDED;">
            <div class="text-dark" style="font-size: 14px; font-weight: 400;">TOTAL: </div>
            <div class="text-dark" style="font-size: 20px; font-weight: 600;">${SessionData.priceBreakdown.currency +
          " " +
          SessionData.priceBreakdown.total
          }</div>
          </div>  
        `;
      }

      $("#customModal .modal-footer").prepend(totalContent);
      $("#customModal .pricing-details").append(pricingDetailsContent);


      $("#customModal .vehicle-wrapper").empty();

      var manilaServices = filterServices(window.ncr_south).reverse();
      manilaServices.forEach(function (value) {
        var selectedVehicle =
          SessionData.serviceType === value.key
            ? 'style="border: solid 2px #f16622"'
            : "";

        let vehicleName;
        switch (value.key) {
          case "MOTORCYCLE":
            vehicleName = "Motorcycle";
            break;
          case "SEDAN":
            vehicleName = "Sedan";
            break;
          case "SEDAN_INTERCITY":
            vehicleName = "Sedan Intercity";
            break;
          case "MPV":
            vehicleName = "MPV";
            break;
          case "MPV_INTERCITY":
            vehicleName = "MPV Intercity";
            break;
          case "VAN":
            vehicleName = "Van";
            break;
          case "VAN_INTERCITY":
            vehicleName = "Van Intercity";
            break;
          case "VAN1000":
            vehicleName = "Van 1000kg";
            break;
          case "2000KG_ALUMINUM_LD":
            vehicleName = "2000kg Aluminum LD";
            break;
          case "2000KG_FB_LD":
            vehicleName = "2000kg FB LD";
            break;
          case "TRUCK550":
            vehicleName = "Truck 550kg";
            break;
          case "10WHEEL_TRUCK":
            vehicleName = "10-Wheel Truck";
            break;
          case "LD_10WHEEL_TRUCK":
            vehicleName = "LD 10-Wheel Truck";
            break;
          default:
            vehicleName = "Unknown Vehicle";
        }

        console.log(vehicleName);

        $("#customModal .vehicle-wrapper").prepend(
          `
              <div class="vehicle" ${selectedVehicle} data-index="${value.key}">
                <div class="vehicle-content-inner">
                  <div class="vehicle-content">
                    <img src="/wp-content/plugins/woo-lalamove/assets/images/vehicles/${value.key}.png" alt="motor" class="vehicle-image">
                    <span class="vehicle-type">${vehicleName}</span>
                  </div>

                  <div class="vehicle-description">
                    <p style="margin-bottom: 1rem;">Load capacity of ${value.load.value} ${value.load.unit}</p>
                    <p>${value.description}</p>
                  </div>
                </div>
              </div>
          `
        );
      });

      $(".vehicle-description").hide(); 


      var startDate = null;
      if (SessionData.scheduleDate) {
        startDate = moment(SessionData.scheduleDate, moment.ISO_8601);
      } else {
        startDate = moment().add(1, "days");
      }

      var endDate = moment().add(30, "days");

      function cb(start, end) {
        console.log("start:", start.format("MMMM D, YYYY hh:mm:ss A"));
        window.scheduleDate = start.toISOString();
        SessionData.scheduleDate = window.scheduleDate;

        console.log("Selected schedule date:", window.scheduleDate);
        $("#customModal #schedule-date").empty();
        $("#customModal #schedule-date").append(`
            <p style="margin: 0;">${start.format(
          "MMMM D, YYYY hh:mm A"
        )}</p>
            <i class="bi bi-calendar" style="margin-left: auto;"></i>
        `);

      }

      var date = jQuery.noConflict();

      date("#schedule-date").daterangepicker(
        {
          startDate: startDate,
          endDate: endDate,
          minDate: moment().add(1, "days"), 
          maxDate: moment().add(30, "days"), 
          singleDatePicker: true, 
          timePicker: true,
          timePicker24Hour: false,
          timePickerSeconds: false,
          timePickerIncrement: 15,
          autoApply: true,
          opens: "right",
          drops: "bottom", 
          showDropdowns: true,
          locale: {
            format: "DD/MM/YYYY HH:mm",
            applyLabel: "Apply", 
            cancelLabel: "Cancel",
          },
        },
        cb
      );

      cb(startDate, endDate);
    } catch (error) {
      console.error("Error fetching shipping data:", error);
      $("#customModal .custom-modal-body").html(`
        <p>Error loading shipping data.</p>
      `);
    }
  }

  // Trigger data fetch when modal is opened
  $(document).on("click", "#shipping_method_0_your_shipping_method", function () {
    fetchShippingData();
  });

  async function loadQuotationData() {

    try {
      window.siteUrl = window.location.origin;

      let quotationID = SessionData.quotationID;
      let serviceType = SessionData.serviceType;
      let scheduleDate = SessionData.scheduleDate;
      let additionalNotes = SessionData.additionalNotes;
      let coordinates = SessionData.coordinates;
      let stops = SessionData.stops;
      let optimizeRoute = SessionData.optimizeRoute;
      let proofOfDelivery = SessionData.proofOfDelivery;
      let priceBreakdown = SessionData.priceBreakdown;

      window.quotationData = {
        quotationID,
        serviceType,
        scheduleDate,
        additionalNotes,
        coordinates,
        stops,
        optimizeRoute,
        proofOfDelivery,
        priceBreakdown,
      };

      // Fetch city data via AJAX
      let get_city = await $.ajax({
        url: window.siteUrl + "/wp-json/woo-lalamove/v1/get-city",
        method: "GET",
      });

      // Fetch checkout product data via AJAX
      let shippingData = await $.ajax({
        url: pluginAjax.ajax_url,
        method: "POST",
        data: {
          action: "get_shipping_data",
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

      console.log("SERVICES", window.ncr_south);

      shippingData.data.products.forEach((product) => {
        window.totalWeight += product.quantity * product.weight;
        window.quantity += product.quantity;
      });

      window.sender_address = shippingData.data.store.address;
      window.shipping_lat = shippingData.data.store.lat;
      window.shipping_lng = shippingData.data.store.lng;
      window.shipping_phone_number = shippingData.data.store.phone_number;

      let customer_address = {
        address_1: 
          document.getElementById('shipping-address_1')?.value || 
          document.getElementById('shipping_address_1')?.value || 
          document.getElementById('billing-address_1')?.value || 
          document.getElementById('billing_address_1')?.value || '',
          
        address_2: 
          document.getElementById('shipping-address_2')?.value || 
          document.getElementById('shipping_address_2')?.value || 
          document.getElementById('billing-address_2')?.value || 
          document.getElementById('billing_address_2')?.value || '',
          
        city: 
          document.getElementById('shipping-city')?.value || 
          document.getElementById('shipping_city')?.value || 
          document.getElementById('billing-city')?.value || 
          document.getElementById('billing_city')?.value || '',
          
        state: 
          document.getElementById('shipping-state')?.value || 
          document.getElementById('shipping_state')?.value || 
          document.getElementById('billing-state')?.value || 
          document.getElementById('billing_state')?.value || '',
          
        postcode: 
          document.getElementById('shipping-postcode')?.value || 
          document.getElementById('shipping_postcode')?.value || 
          document.getElementById('billing-postcode')?.value || 
          document.getElementById('billing_postcode')?.value || '',
          
        country: 
          document.getElementById('shipping-country')?.value || 
          document.getElementById('shipping_country')?.value || 
          document.getElementById('billing-country')?.value || 
          document.getElementById('billing_country')?.value || ''
      };

      window.customer_address =
        (customer_address.address_1 ?? "") +
        " " +
        (customer_address.address_2 ?? "") +
        " " +
        (customer_address.city ?? "") +
        " " +
        (customer_address.state ?? "") +
        " " +
        (customer_address.postcode ?? "");

      console.log("Customer Address:", window.customer_address);

      window.customerFName = document.getElementById("shipping-first_name")?.value || 
             document.getElementById("billing-first_name")?.value || 
             document.getElementById("shipping_first_name")?.value || 
             document.getElementById("billing_first_name")?.value || 
             document.getElementsByName("shipping_first_name")[0]?.value || 
             document.getElementsByName("billing_first_name")[0]?.value || 
             "";

      window.customerLName = document.getElementById("shipping-last_name")?.value || 
             document.getElementById("billing-last_name")?.value || 
             document.getElementById("shipping_last_name")?.value || 
             document.getElementById("billing_last_name")?.value || 
             document.getElementsByName("shipping_last_name")[0]?.value || 
             document.getElementsByName("billing_last_name")[0]?.value || 
             "";

      window.customerPhoneNo = (function() {
        let phone = document.getElementById("shipping-phone")?.value || 
                    document.getElementById("billing-phone")?.value || 
                    document.getElementById("shipping_phone")?.value || 
                    document.getElementById("billing_phone")?.value || 
                    document.getElementsByName("shipping_phone")[0]?.value || 
                    document.getElementsByName("billing_phone")[0]?.value || 
                    "";
        // Remove all spaces in the number
        phone = phone.replace(/\s/g, "");
    
        return phone;
      })();
          
    } catch (error) {
      console.error("Error loading quotation data:", error);
    }
  }

   // Function to save state to sessionStorage
   function saveSessionData() {
    

    console.log("ADD NAWTS: ", additionalNotes);

      // Log the collected data
      console.log("Saving data...");

      console.log("Quotation Body", window.body);

      SessionData.quotationID = window.quotationData.quotationID; 
      SessionData.coordinates.lat = window.quotationData.coordinates.lat;
      SessionData.coordinates.lng = window.quotationData.coordinates.lng;
      SessionData.serviceType = window.quotationData.serviceType;
      SessionData.stops.stopID0 = window.quotationData.stops.stopID0;
      SessionData.stops.stopID1 = window.quotationData.stops.stopID1;
      SessionData.optimizeRoute = window.quotationData.optimizeRoute;
      SessionData.proofOfDelivery = window.quotationData.proofOfDelivery;
      SessionData.priceBreakdown = window.quotationData.priceBreakdown;

      sessionStorage.setItem("SessionData", JSON.stringify(SessionData));
      console.log("SessionData saved to sessionStorage:", SessionData);
      console.log("Scheduled Date", window.quotationData.scheduleDate);
      console.log("Drop Off Location", window.customer_address);

      // Set the quotation ID as a session variable
      jQuery.ajax({
        url: pluginAjax.ajax_url,
        method: "POST",
        data: {
          action: "set_quotation_data_session",
          quotationBody: window.body,
          quotationID:SessionData.quotationID,
          stopId0: SessionData.stops.stopID0,
          stopId1: SessionData.stops.stopID1,
          customerFName: window.customerFName,
          customerLName: window.customerLName,
          scheduledOn: SessionData.scheduleDate,
          dropOffLocation: window.customer_address,
          customerPhoneNo: window.customerPhoneNo,
          additionalNotes: SessionData.additionalNotes,
          proofOfDelivery: window.quotationData.optimizeRoute,
          serviceType: SessionData.serviceType,
          priceBreakdown: JSON.stringify(SessionData.priceBreakdown),
        },
        success: function (response) {
          console.log("RESPONSE", response);
          if (response.success) {
            console.log("Quotation ID set in session successfully.");
            console.log("PHONE NO: ", customerPhoneNo);
          } else {
            console.error(
              "Failed to set Quotation ID in session:",
              response.data.message
            );
          }
        },
        error: function (response, error, xhr) {
          console.log("RESPONSE", response);
          console.error("MAY MALI!!!", error);
          console.error("XHR Response Text:", xhr.responseText);
        },
      });

      let ajaxTimer;
      clearTimeout(ajaxTimer);

      (ajaxTimer = setTimeout(function () {
        // Send the shipping cost to the server via AJAX
        jQuery.ajax({
          url: pluginAjax.ajax_url, 
          method: "POST",
          data: {
            action: "update_shipping_rate", // Custom AJAX action
            shipping_cost: quotationData.priceBreakdown.total||SessionData.priceBreakdown.total,
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
                data: { shipping_cost: 100 },
              });
            }

            $(document.body).trigger("update_checkout");
            
            console.log("Shipping rate updated successfully.");

          },
          error: function (error) {
            console.error("Error updating shipping rate:", error);
          },
        });
      })),
        500;


  }

  // Function to load state from sessionStorage
  function loadSessionData() {
    var storedState = sessionStorage.getItem("SessionData");
    if (storedState) {
      SessionData = JSON.parse(storedState);
      console.log("SessionData loaded from sessionStorage:", SessionData);
    }
  }


  function resetSessionData() {

    SessionData = {
        quotationID: null,
        coordinates: {},
        serviceType: null,
        stops: {}, 
        scheduleDate: null,
        additionalNotes: "",
        optimizeRoute: null,
        proofOfDelivery: null,
        priceBreakdown: {},
    };

    sessionStorage.removeItem("SessionData");

    $('input[name="shipping_method[0]"][value="your_shipping_method"]')
        .prop('checked', false)
        .trigger('change');

   }


  
  // Initialize the Leaflet map and conditionally fetch user location
  function initMap() {
    const defaultLat = 12.8797;
    const defaultLng = 121.774;

    $(".modal-body #additionalNotes").append(SessionData.additionalNotes);

    //Verify if a service is already selected
    var isVehicleSelected = window.serviceType ? true : false;

    // Use stored session data if available, otherwise fallback to defaults
    let mapLat = SessionData.lat || defaultLat;
    let mapLng = SessionData.lng || defaultLng;

      // Validate coordinates
    if (typeof mapLat === "undefined" || typeof mapLng === "undefined") {
      console.error("Invalid coordinates: mapLat or mapLng is undefined. Using defaults.");
      mapLat = defaultLat;
      mapLng = defaultLng;
    }

    console.log("Using Coordinates:", mapLat, mapLng);

    // Initialize the map and marker
    const map = L.map("map").setView([mapLat, mapLng], 15);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 18,
      attribution: "© OpenStreetMap",
    }).addTo(map);

    const marker = L.marker([mapLat, mapLng], { draggable: true }).addTo(map);

   // Fix map rendering issues
    setTimeout(() => {
      map.invalidateSize();
    }, 300);

    // Bind service events using initial coordinates
    bindServiceEvents(mapLat, mapLng);

    // Update marker location on dragend
    marker.on("dragend", () => {
      console.log("Marker dragged to new location.");
      if(isVehicleSelected === false) {
        isVehicleSelected = window.serviceType ? true : false;
      }
      const latlng = marker.getLatLng();
      mapLat = latlng.lat;
      mapLng = latlng.lng;

      SessionData.lat = mapLat;
      SessionData.lng = mapLng;
      sessionStorage.setItem("sessionData", JSON.stringify(SessionData));

      triggerServiceEvents(mapLat, mapLng, isVehicleSelected);
    });

    marker.bindPopup("You").openPopup();
  

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
      maximumAge: 6000,
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
    if (isVehicleSelected) {
      quotationAjax(lat, lng);

      $(document)
        .off("click", "#optimizeRoute")
        .on("click", "#optimizeRoute", function () {
          if ($(this).is(":checked")) {
            quotationAjax(lat, lng);
          }
        });

      return;
    }

    // Bind the click event on the switch button
    $(document)
      .off("click", "#optimizeRoute")
      .on("click", "#optimizeRoute", function () {
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
  
      $(".vehicle-description").slideUp();
  
      const description = $(this).closest(".vehicle").find(".vehicle-description");
  
      description.slideDown();
  
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
    
    $("#customModal .custom-modal-footer").empty();
    $("#customModal .custom-modal-footer").prepend(`
      <div class="total-wrapper">
        <div class="total-label">TOTAL:</div>
        <div class="total-amount">
            <div class="total-loading-spinner"></div>
        </div>
      </div>
      <button type="button" id="saveLocation" class="custom-modal-button">Save</button>
    `);
    
    $('#saveLocation').prop("disabled", true);


    var isRouteOptimized = document.getElementById("optimizeRoute").checked;

    window.body = {
      data: {
        scheduleAt: window.scheduleDate || SessionData.scheduleDate,
        serviceType: window.serviceType || SessionData.serviceType,
        language: "en_PH",
        stops: [
          {
            coordinates: {
              lat: window.shipping_lat.toString(),
              lng: window.shipping_lng.toString(),
            },
            address:
              window.sender_address.toString(),
          },
          {
            coordinates: {
              lat: lat.toString(),
              lng: lng.toString(),
            },
            address: window.customer_address.toString(),
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
      url: window.siteUrl + '/wp-json/woo-lalamove/v1/get-quotation',
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify(body),
      success: function (response) {

        if(response.errors){
          console.error("Error in response:", response.errors);
          alert("Error: " + response.errors[0].message);
          return;
        }

        console.log("Response received:", response);

        console.log("Price Breakdown ", SessionData.priceBreakdown);

        var currency = response.data.priceBreakdown.currency
          ? response.data.priceBreakdown.currency
          : SessionData.priceBreakdown.currency;
        var total = response.data.priceBreakdown.total
          ? response.data.priceBreakdown.total
          : SessionData.priceBreakdown.total;
        var base = response.data.priceBreakdown.base
          ? response.data.priceBreakdown.base
          : SessionData.priceBreakdown.base;
        var extraMileage = response.data.priceBreakdown.extraMileage
          ? response.data.priceBreakdown.extraMileage
          : SessionData.priceBreakdown.extraMileage;
        var surcharge = response.data.priceBreakdown.surcharge
          ? response.data.priceBreakdown.surcharge
          : SessionData.priceBreakdown.surcharge;

        $("#customModal .custom-modal-footer").empty();
        

        setTimeout(function () {
          $('#saveLocation').prop("disabled", false); 
        }, 1000);

        $("#customModal .custom-modal-footer").prepend(`
          <div class="total-wrapper">
            <div class="total-label">TOTAL:</div>
            <div class="total-amount">${currency + " " + total}</div>
          </div>
          <button type="button" id="saveLocation" class="custom-modal-button">Save</button>
        `);

        
       
        $("#customModal .pricing-details").empty();
        
        let baseContent = `
          <div class="pricing-item">
            <p class="item-name">Base Fare</p>  
            <p class="item-value">${currency + " " + base}</p>  
          </div>
        `;
        let extraMileageContent = `
          <div class="pricing-item">
            <p class="item-name">Additional Distance Fee</p>  
            <p class="item-value">${currency + " " + extraMileage}</p>  
          </div>
        `;
        let surchargeContent = `
          <div class="pricing-item">
            <p class="item-name">Surcharge</p>  
            <p class="item-value">${currency + " " + surcharge}</p>  
          </div>
        `;
        
        $("#customModal .pricing-details").prepend(`
          <p class="header">Pricing Details</p>
        `);
        
        if (base) {
          $("#customModal .pricing-details").append(baseContent);
        }
        if (extraMileage) {
          $("#customModal .pricing-details").append(extraMileageContent);
        }
        if (surcharge) {
          $("#customModal .pricing-details").append(surchargeContent);
        }
        

        const optimizeRoute = $("#optimizeRoute").is(":checked");
        const proofOfDelivery = $("#proofOfDelivery").is(":checked");
        const additionalNotes = $("#additionalNotes").val();

        let quotationID = response.data.quotationId;
        let serviceType = window.serviceType;
        let scheduleDate = window.scheduleDate;
        let stopID0 = response.data.stops[0].stopId;
        let stopID1 = response.data.stops[1].stopId;
        let stops = { stopID0, stopID1 };
        let coordinates = { lat, lng };
        let priceBreakdown = response.data.priceBreakdown;


        window.quotationData = {
          quotationID,
          serviceType,
          scheduleDate,
          additionalNotes,
          coordinates,
          stops,
          optimizeRoute,
          proofOfDelivery,
          priceBreakdown
        };

        console.log(window.quotationData);

      },
      error: function (xhr, status, error) {
        console.error("Error occurred:", status, error);
      },
    });
  }

    
  $(document).on(
    "change",
    // Properly formatted single selector string
    '#shipping-address_1, #shipping-address_2, #shipping-city, #shipping-state, #shipping-postcode, #shipping-country, ' +
    '#billing-address_1, #billing-address_2, #billing-city, #billing-state, #billing-postcode, #billing-country, ' +
    '#shipping_address_1, #shipping_address_2, #shipping_city, #shipping_state, #shipping_postcode, #shipping_country, ' +
    '#billing_address_1, #billing_address_2, #billing_city, #billing_state, #billing_postcode, #billing_country, ' +
    'input[name^="shipping_"], input[name^="billing_"], ' +
    'select[name^="shipping_"], select[name^="billing_"]',
    
    function() {
      if (SessionData.quotationID === null) {
        return;
      }

      resetSessionData();
        
      saveSessionData();

      closeModal();        

    }
  );

  // Prevent order submission if Lalamove is selected but not configured
  $(document.body).on('click', '#place_order', function(e) {
      const isLalamoveSelected = $('input[name="shipping_method[0]"][value="your_shipping_method"]:checked').length > 0;
      
      if (isLalamoveSelected) {
          // Check if session data exists
          if (!SessionData.quotationID || 
              typeof SessionData.quotationID === 'undefined' || 
              SessionData.quotationID === null
          ) {
              // Show alert with custom message
              alert('Please configure your Lalamove shipping details before placing the order.');



              resetSessionData();
                
              saveSessionData();
              
              // Prevent form submission
              e.preventDefault();
              e.stopImmediatePropagation();
              return false;
          }
      }
  });
  
  
    
});