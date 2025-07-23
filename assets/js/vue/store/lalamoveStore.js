import { defineStore } from "pinia";
import { ref, reactive, computed, watch } from "vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { data } from "autoprefixer";

export const useLalamoveStore = defineStore("lalamove", () => {
  // ====================
  // State Definitions
  // ====================
  const services = ref([]);
  const scheduleAt = ref('');
  const serviceType = ref('');
  const language = ref('en_PH');
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
    quantity: "12",
    weight: "LESS_THAN_3_KG",
    categories: ["FOOD_DELIVERY", "OFFICE_ITEM"],
    handlingInstructions: ["KEEP_UPRIGHT"]
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
        coordinates
      })),
      isRouteOptimized: isRouteOptimized.value,
      item: {
        quantity: item.value.quantity,
        weight: item.value.weight,
        categories: [...item.value.categories],
        handlingInstructions: [...item.value.handlingInstructions],
      }
    }
  }));

  const priceBreakdown = computed(() => {
    if (quotation.value && quotation.value.data && quotation.value.data.priceBreakdown) {
      return quotation.value.data.priceBreakdown;
    }
    return {};
  });


  const canRequestQuote = computed(() => (
    scheduleAt.value !== "" &&
    serviceType.value !== "" &&
    addresses.length >= 2 &&
    item.value.quantity !== "" &&
    item.value.weight !== ""
  ));

  const canPlaceOrder = computed(() => (
    Boolean(quotation.value?.data.quotationId)
  ));

  // ====================
  // Actions
  // ====================
  function updateAddressCoordinates(index, lat, lng ) {
    if (index >= 0 && index < addresses.length) {
      addresses[index].coordinates = { lat, lng };
    }
  }

  function reorderAddresses(oldIndex, newIndex) {
    if (oldIndex === newIndex) return;
    
    const [movedItem] = addresses.splice(oldIndex, 1);
    addresses.splice(newIndex, 0, movedItem);
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
      const ncrSouth = response.data.find(c => c.locode === "PH MNL");
      services.value = [...(ncrSouth?.services || [])]
        .sort((a, b) => Number(a.load?.value) - Number(b.load?.value));

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
            "Content-Type": "application/json" 
          }
        }
      );

      console.log("response: ", response.data);
      // Check for custom error structure in successful response
      if (response.data?.errors && Array.isArray(response.data.errors)) {
        const firstError = response.data.errors[0];
        const errorMessage = firstError.message || firstError.id || "Unknown error";
        
        throw new Error(`API Error: ${errorMessage}`);
      }

      quotation.value = response.data;
      
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
        if (error.response?.data?.errors && Array.isArray(error.response.data.errors)) {
          const firstError = error.response.data.errors[0];
          errorMessage = firstError.message || firstError.id || "API returned an error";
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

  // Clears quotation value when changes is made 
  watch(quotationBody, () => {
    quotation.value = null;
  }, {deep: true});



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
    priceBreakdown,
    canRequestQuote,
    canPlaceOrder,

    // Actions
    reorderAddresses,
    updateAddressCoordinates,
    updateAddress,
    fetchCity,
    getQuotation,
    setItemDetails,
  };
});