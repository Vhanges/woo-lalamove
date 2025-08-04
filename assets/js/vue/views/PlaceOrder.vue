<template>
  <main class="wrapper">
    <div class="content">
      <div class="message">Message</div>
      <div class="content-1">
        <AddressInput class="address" />

        <div class="vehicle-container">
          <p class="header">VEHICLE TYPE</p>
          <VehicleSelection />
        </div>

        <DeliveryOptions class="additional-request" />

        <ScheduleDate class="schedule-container" />

        <AdditionalNotes class="additional-notes-container" />
      </div>

      <div class="content-2">
        <Map id="map" class="map" />

        <PriceBreakdown class="price-breakdown" />

        <!-- Show this button if user can't place order yet -->
        <button
          v-show="!canPlaceOrder"
          class="enter"
          :disabled="!canRequestQuote || disableButton"
          @click="handleGetQuote()"
        >
          Get Quotation
        </button>

        <!-- Show this button if user can place order now -->
        <button
          v-show="canPlaceOrder"
          class="enter"
          :disabled="disableButton"
          @click="handlePlaceOrder()"
        >
          Place order
        </button>
      </div>
    </div>
  </main>
</template>

<script setup>
import { onMounted, ref, watch } from "vue";
import { storeToRefs } from "pinia";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { useRouter } from "vue-router";

import { useLalamoveStore } from "../store/lalamoveStore";
import AddressInput from "../components/PlaceOrder/ShippingAddress.vue";
import Map from "../components/PlaceOrder/Map/PlaceOrderMap";
import VehicleSelection from "../components/PlaceOrder/VehicleSelection.vue";
import DeliveryOptions from "../components/PlaceOrder/DeliveryOptions.vue";
import ScheduleDate from "../components/PlaceOrder/ScheduleDate.vue";
import AdditionalNotes from "../components/PlaceOrder/AdditionalNotes.vue";
import PriceBreakdown from "../components/PlaceOrder/PriceBreakdown.vue";

const router = useRouter();

const lalamove = useLalamoveStore();
const disableButton = ref(false);
const { canRequestQuote, canPlaceOrder, quotationBody, quotation, addresses } =
  storeToRefs(lalamove);
watch(quotationBody, (newVal, oldVal) => {
  console.log("NEW ", newVal);
  console.log("OLD ", oldVal);
});
watch(quotation, (newVal, oldVal) => {
  console.log("NEW ", newVal);
  console.log("OLD ", oldVal);
});
watch(addresses, (newVal, oldVal) => {
  console.log("NEW ", newVal);
  console.log("OLD ", oldVal);
});

const { fetchCity, getQuotation, setShipmentOrder, clearStates } = lalamove;

async function handleGetQuote() {
  try {
    disableButton.value = true;
    await getQuotation();
  } catch (error) {
    toast.error(error.message);
  } finally {
    disableButton.value = false;
  }
}

async function handlePlaceOrder() {
  try {
    disableButton.value = true;
    const lala_id = await setShipmentOrder();
    router.push({
        name: 'records-details', 
        params: { 
          lala_id: lala_id, 
        }
    });
    clearStates();

  } catch (error) {
    toast.error(error.message);
  } finally {
    disableButton.value = false;
  }
}

onMounted(() => {
  fetchCity();
  lalamove.mountImportedAddresses();
});
</script>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;

.wrapper {
  height: auto;
  margin-bottom: 20%;
  display: flex;
  justify-content: center;
  align-items: start;
  padding-top: 5%;
}

.content {
  height: 80%;
  width: 90%;

  .message {
    grid-area: 1 / 1 / 2 / 3;
  }

  .content-1 {
    grid-area: 2 / 1 / 3 / 2;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 1rem;
    max-width: 1fr;
  }

  .content-2 {
    grid-area: 2 / 2 / 3 / 3;
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 1rem;
  }

  .enter {
    color: $txt-light;
    border: none;
    border-radius: 5px;
    padding: 2% 12px;
    font-size: $font-size-lg;
    background-color: $bg-primary;

    &:hover {
      background-color: $bg-primary-hovered;
    }

    &:disabled {
      background-color: $bg-primary-disabled;
    }
  }

  .header {
    font-size: $font-size-xs;
    font-weight: $font-weight-bold;
  }

  label {
    font-size: $font-size-sm;
    font-weight: $font-weight-regular;
  }
}

@media (min-width: 900px) {
  .content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-template-rows: 10% auto;
    grid-column-gap: 50px;
    grid-row-gap: 15px;
  }
}

@media (max-width: 500px) {
  .content {
    display: grid;
    grid-template-columns: 1fr;
    grid-template-rows: 10% auto;
    grid-row-gap: 15px;
  }
}
</style>
