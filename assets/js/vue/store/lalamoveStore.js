import { defineStore, storeToRefs } from "pinia";
import { ref, reactive, computed, watch, unref, toRaw } from "vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { useWooOrderStore } from "./wooOrderStore";

export const useLalamoveStore = defineStore("lalamove", () => {
  const wooOrders = useWooOrderStore();
  const { selectedRows } = storeToRefs(wooOrders);
  // ====================
  // State Definitions
  // ====================
  const services = ref([]);
  const scheduleAt = ref("");
  const serviceType = ref("");
  const language = ref("en_PH");
  const isRouteOptimized = ref(false);
  const isPodEnabled = ref(false);
  const additionalNotes = ref("");
  const isServicesLoading = ref(false);
  const quotation = ref(null);

  // Address-related state
  const addresses = reactive([
    {
      id: "stop-1",
      address: "Club Manila East, Taytay",
      coordinates: { lat: "14.5586", lng: "121.1362" },
    },
    {
      id: "stop-2",
      address: "Angono Municipal Hall, Angono",
      coordinates: { lat: "14.5259", lng: "121.1551" },
    },
  ]);

  const selectedAddress = ref(null);
  const addressSuggestions = ref([]);
  const locode = ref(null);

  // Item state
  const item = ref({
    quantity: 0,
    weight: 0,
    // categories: ["FOOD_DELIVERY", "OFFICE_ITEM"],
    // handlingInstructions: ["KEEP_UPRIGHT"],
  });

  // ====================
  // Computed Properties
  // ====================
  const quotationBody = computed(() => ({
    data: {
      scheduleAt: scheduleAt.value,
      serviceType: serviceType.value,
      language: language.value,
      stops: addresses.map(({ address, coordinates }) => ({
        address,
        coordinates,
      })),
      isRouteOptimized: isRouteOptimized.value,
      item: {
        quantity:  String(item.value.quantity),
        weight: String(item.value.weight),
        // categories: [...item.value.categories],
        // handlingInstructions: [...item.value.handlingInstructions],
      },
    },
  }));

  const placeOrderBody = computed(() => {
    if (!quotation.value?.data?.stops || addresses.length === 0) {
      return null;
    }

    // Map quotation stops to get stopIds
    const quotationStops = quotation.value.data.stops;

    // Extract sender (first address)
    const senderAddress = addresses[0];
    const senderStop = quotationStops[0];

    // Extract recipients (remaining addresses)
    const recipients = addresses.slice(1).map((addr, index) => {
      const stop = quotationStops[index + 1];
      return {
        stopId: stop.stopId,
        name: addr.name,
        phone: addr.phone,
        ...(addr.remarks && { remarks: addr.remarks }),
      };
    });

    return {
      data: {
        quotationId: quotation.value.data.quotationId,
        sender: {
          stopId: senderStop.stopId,
          name: senderAddress.name,
          phone: senderAddress.phone,
        },
        recipients,
        isPODEnabled: isPodEnabled.value,
        ...(additionalNotes.value && {
          additionalNotes: additionalNotes.value,
        }),
      },
    };
  });

  const priceBreakdown = computed(() => {
    if (
      quotation.value &&
      quotation.value.data &&
      quotation.value.data.priceBreakdown
    ) {
      return quotation.value.data.priceBreakdown;
    }
    return {};
  });

  const canRequestQuote = computed(
    () =>
      scheduleAt.value !== "" &&
      serviceType.value !== "" &&
      addresses.length >= 2 &&
      item.value.quantity !== "" &&
      item.value.weight !== ""
  );

  const canPlaceOrder = computed(() =>
    Boolean(quotation.value?.data.quotationId)
  );

  // ====================
  // Actions
  // ====================
  function updateAddressCoordinates(index, lat, lng) {
    if (index >= 0 && index < addresses.length) {
      addresses[index].coordinates = { lat, lng };
    }
  }

  function reorderAddresses(oldIndex, newIndex) {
    if (oldIndex === newIndex) return;
    console.log("Before reorder:", JSON.parse(JSON.stringify(addresses)));
    const [movedItem] = addresses.splice(oldIndex, 1);
    addresses.splice(newIndex, 0, movedItem);
    console.log("After reorder:", JSON.parse(JSON.stringify(addresses)));
  }

  function updateAddress(index, newAddress) {
    if (index >= 0 && index < addresses.length) {
      addresses[index].address = newAddress;
    }
  }

  function setItemDetails(details) {
    item.value = { ...item.value, ...details };
  }

  // ====================
  // API Actions
  // ====================
  async function fetchCity() {
    try {
      isServicesLoading.value = true;
      const apiRoot = window?.wpApiSettings?.root || "";
      const apiNonce = window?.wpApiSettings?.nonce || "";

      const response = await axios.get(`${apiRoot}woo-lalamove/v1/get-city`, {
        headers: { "X-WP-Nonce": apiNonce },
      });

      // Process and sort services
      const ncrSouth = response.data.find((c) => c.locode === "PH MNL");
      services.value = [...(ncrSouth?.services || [])].sort(
        (a, b) => Number(a.load?.value) - Number(b.load?.value)
      );

      return services.value;
    } catch (error) {
      console.error("Error fetching locations:", error);
      throw error;
    } finally {
      isServicesLoading.value = false;
    }
  }

  async function getQuotation() {
    if (!canRequestQuote.value) {
      toast.error("Cannot request quote - please fill all required fields");
      throw new Error("Cannot request quote - missing required data");
    }

    let toastId = null;
    try {
      const apiRoot = window?.wpApiSettings?.root || "";
      const apiNonce = window?.wpApiSettings?.nonce || "";

      // Show loading toast
      toastId = toast.loading("Processing your quotation request...", {
        autoClose: false,
        closeButton: false,
        closeOnClick: false,
        draggable: false,
      });

      const response = await axios.post(
        `${apiRoot}woo-lalamove/v1/get-quotation`,
        JSON.stringify(quotationBody.value),
        {
          headers: {
            "X-WP-Nonce": apiNonce,
            "Content-Type": "application/json",
          },
        }
      );

      console.log("response: ", response.data);
      // Check for custom error structure in successful response
      if (response.data?.errors && Array.isArray(response.data.errors)) {
        const firstError = response.data.errors[0];
        const errorMessage =
          firstError.message || firstError.id || "Unknown error";

        throw new Error(`API Error: ${errorMessage}`);
      }

      quotation.value = response.data;
      console.log("PLACE ORDER BODY: ", placeOrderBody.value);

      // Update to success toast
      toast.update(toastId, {
        render: "Quotation received successfully!",
        type: toast.TYPE.SUCCESS,
        isLoading: false,
        autoClose: 3000,
        closeButton: true,
        closeOnClick: true,
        draggable: true,
      });

      return response.data;
    } catch (error) {
      console.error("Quotation error:", error);

      let errorMessage = "Failed to get quotation";

      // Handle Axios errors
      if (axios.isAxiosError(error)) {
        // Handle custom error structure in error response
        if (
          error.response?.data?.errors &&
          Array.isArray(error.response.data.errors)
        ) {
          const firstError = error.response.data.errors[0];
          errorMessage =
            firstError.message || firstError.id || "API returned an error";
        }
        // Handle standard error message
        else if (error.response?.data?.message) {
          errorMessage = error.response.data.message;
        }
      }
      // Handle custom errors thrown by us
      else if (error.message && error.message.startsWith("API Error:")) {
        errorMessage = error.message.replace("API Error: ", "");
      }
      // Handle other errors
      else if (error.message) {
        errorMessage = error.message;
      }

      // Update to error toast if we have toastId
      if (toastId) {
        toast.update(toastId, {
          render: `Quotation failed: ${errorMessage}`,
          type: toast.TYPE.ERROR,
          isLoading: false,
          autoClose: 5000,
          closeButton: true,
          closeOnClick: true,
          draggable: true,
        });
      } else {
        // Fallback if toast wasn't created
        toast.error(`Quotation failed: ${errorMessage}`);
      }

      throw error;
    }
  }

  async function setShipmentOrder() {
    // Validate before proceeding
    if (!canPlaceOrder.value) {
      throw new Error("Cannot place order - missing valid quotation");
    }

    if (!placeOrderBody.value) {
      throw new Error("Cannot place order - invalid order data");
    }

    // Validate address data
    const missingFields = addresses.some((addr) => !addr.name || !addr.phone);
    if (missingFields) {
      throw new Error("Missing name or phone in addresses");
    }

    let toastId = null;
    try {
      const apiRoot = window?.wpApiSettings?.root || "";
      const apiNonce = window?.wpApiSettings?.nonce || "";

      toastId = toast.loading("Placing your order...", {
        autoClose: false,
        closeButton: false,
        closeOnClick: false,
        draggable: false,
      });

      console.log("Place Order Body: ", placeOrderBody.value);

      const response = await axios.post(
        `${apiRoot}woo-lalamove/v1/place-order`,
        JSON.stringify(placeOrderBody.value),
        {
          headers: {
            "X-WP-Nonce": apiNonce,
            "Content-Type": "application/json",
          },
        }
      );

      // Handle response correctly

      if (response.data.error && response.data.error.id) {
        const { id, message } = response.data.error;

        switch (id) {
          case "ERR_INSUFFICIENT_STOPS":
            throw new Error(
              "Order Error: Not enough stops — must include at least 2."
            );
          case "ERR_INVALID_FIELD":
            throw new Error(
              `Order Error: Invalid field format. ${
                message || "Please check your request body."
              }`
            );
          case "ERR_MISSING_FIELD":
            throw new Error(
              `Order Error: Missing required fields. ${
                message || "Please complete all fields in the request body."
              }`
            );
          case "ERR_TOO_MANY_STOPS":
            throw new Error(
              "Order Error: You've exceeded the stop limit (1 pickup and up to 15 drop-offs)."
            );
          case "ERR_INVALID_QUOTATION_ID":
            throw new Error(
              "Order Error: Quotation ID is invalid or expired. Please regenerate quotation before placing the order."
            );
          default:
            throw new Error(
              `Order Error: ${message || "An unexpected API error occurred."}`
            );
        }
      }

      console.log("Order Placement Response: ", response.data.data);
      
      const { orderId, priceBreakdown } = response.data.data;

      const data = {
        orderId,
        serviceType: unref(serviceType),
        scheduleAt: unref(scheduleAt),
        priceBreakdown: priceBreakdown,
        quotationBody: toRaw(unref(quotationBody)),
        addresses: toRaw(unref(addresses)),
      };

      await axios.post(
      `${apiRoot}woo-lalamove/v1/store-order`,
      JSON.stringify(data),
      {
        headers: {
          "X-WP-Nonce": apiNonce,
          "Content-Type": "application/json",
        },
      });

      toast.update(toastId, {
        render: `Order placed successfully!`,
        type: toast.TYPE.SUCCESS,
        isLoading: false,
        autoClose: 5000,
        closeButton: true,
        closeOnClick: true,
        draggable: true,
      });

      return orderId;

    } catch (error) {
      console.error("Place order error:", error);

      if (toastId) {
        toast.update(toastId, {
          render: `Order placement failed: ${error.message}`,
          type: toast.TYPE.ERROR,
          isLoading: false,
          autoClose: 7000,
          closeButton: true,
          closeOnClick: true,
          draggable: true,
        });
      } else {
        // Fallback if toast wasn't created
        toast.error(`Order placement failed: ${errorMessage}`);
      }
    }
  }

  // Clears quotation value when changes is made
  watch(
    quotationBody,
    () => {
      quotation.value = null;
    },
    { deep: true }
  );

  const mountImportedAddresses = async () => {
    const ajaxUrl = window.ajaxurl || `${window.location.origin}/wp-admin/admin-ajax.php`;

    let sellerAddress = null;

    try {
      const response = await axios.get(ajaxUrl, {
        params: {
          action: "get_seller_delivery_address",
        },
      });

      if (response.data?.success) {
        sellerAddress = response.data.data;
        toast.success("Seller address retrieved!", {
          position: "top-right",
          autoClose: 4000,
        });
        console.log("Seller Address:", sellerAddress);
      } else {
        throw new Error(response.data?.data || "Unable to fetch seller delivery address.");
      }
    } catch (error) {
      const errorMessage =
        error.response?.data?.data ||
        error.message ||
        "Unknown error occurred while fetching seller address.";

      console.error("Error fetching seller address:", error);
      toast.error(errorMessage, {
        position: "top-right",
        autoClose: 7000,
      });
    }

    addresses.splice(0, addresses.length);
    item.value.quantity = 0;
    item.value.weight = 0;

    if (sellerAddress && sellerAddress.address && sellerAddress.lat && sellerAddress.lng) {
      addresses.push({
        id: "stop-0",
        wooId: null,
        address: sellerAddress.address,
        coordinates: {
          lat: sellerAddress.lat,
          lng: sellerAddress.lng,
        },
        name: sellerAddress.name,
        phone: sellerAddress.phone_number || "",
      });
    }

    selectedRows.value.forEach((row, index) => {
      addresses.push({
        id: `stop-${index + 1}`,
        wooId: row.wooID,
        address: row.stops.address,
        coordinates: {
          lat: row.stops.coordinates.lat,
          lng: row.stops.coordinates.lng,
        },
        remarks: row.remarks || "",
        name: row.name,
        phone: row.phone,
      });

      const qty = parseInt(row.item.quantity, 10) || 0;
      const weight = parseFloat(row.item.weight) || 0;

      item.value.quantity += qty;
      item.value.weight += weight * qty;
    });

    console.log("ADDRESSES MOUNTED", addresses);
  };



  const clearStates = () => {
    // Reset selected WooCommerce orders
    wooOrderStore.selectedRows = [];

    // Reset shipment configuration states
    services.value = [];
    scheduleAt.value = "";
    serviceType.value = "";
    language.value = "en_PH";
    isRouteOptimized.value = false;
    isPodEnabled.value = false;
    additionalNotes.value = "";
    isServicesLoading.value = false;
    quotation.value = null;

    addresses.splice(0, addresses.length); 

    addresses.push(
      {
        id: "stop-1",
        address: "Club Manila East, Taytay",
        coordinates: { lat: "14.5586", lng: "121.1362" },
      },
      {
        id: "stop-2",
        address: "Angono Municipal Hall, Angono",
        coordinates: { lat: "14.5259", lng: "121.1551" },
      }
    );

    selectedAddress.value = null;
    addressSuggestions.value = [];
    locode.value = null;

    // Reset item information
    item.value.quantity = 0;
    item.value.weight = 0;

    // item.value.categories = [];
    // item.value.handlingInstructions = [];

    console.log("States successfully reset.");
  };

  // ====================
  // Return Store API
  // ====================
  return {
    // State
    services,
    scheduleAt,
    serviceType,
    language,
    addresses,
    isRouteOptimized,
    item,
    quotation,
    isPodEnabled,
    additionalNotes,
    selectedAddress,
    addressSuggestions,
    locode,
    isServicesLoading,

    // Computed
    quotationBody,
    placeOrderBody,
    priceBreakdown,
    canRequestQuote,
    canPlaceOrder,

    // Actions
    reorderAddresses,
    updateAddressCoordinates,
    updateAddress,
    fetchCity,
    getQuotation,
    setShipmentOrder,
    setItemDetails,
    mountImportedAddresses,
    clearStates,
  };
});
