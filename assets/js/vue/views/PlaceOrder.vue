<template>
  <main class="place-order-wrapper">
    <div class="place-order-container">
      <!-- Page Header -->
      <header class="page-header">
        <h1 class="page-title">Place Your Order</h1>
        <p class="page-subtitle">Configure your delivery details and get an instant quote</p>
      </header>

      <!-- Main Content Grid -->
      <div class="content-grid">
        <!-- Left Column: Order Configuration -->
        <section class="order-configuration">
          <!-- Shipping Address Section -->
          <div class="form-section">
            <div class="section-header">
              <h2 class="section-title">Delivery Addresses</h2>
              <p class="section-description">Add pickup and delivery locations</p>
            </div>
            <AddressInput class="address-input" />
          </div>

          <!-- Vehicle Selection Section -->
          <div class="form-section">
            <div class="section-header">
              <h2 class="section-title">Vehicle Type</h2>
              <p class="section-description">Choose the right vehicle for your delivery</p>
            </div>
            <VehicleSelection class="vehicle-selection" />
          </div>

          <!-- Delivery Options Section -->
          <div class="form-section">
            <div class="section-header">
              <h2 class="section-title">Delivery Options</h2>
              <p class="section-description">Select additional services</p>
            </div>
            <DeliveryOptions class="delivery-options" />
          </div>

          <!-- Schedule Section -->
          <div class="form-section">
            <div class="section-header">
              <h2 class="section-title">Schedule Delivery</h2>
              <p class="section-description">Choose your preferred delivery time</p>
            </div>
            <ScheduleDate class="schedule-date" />
          </div>

          <!-- Additional Notes Section -->
          <div class="form-section">
            <div class="section-header">
              <h2 class="section-title">Additional Notes</h2>
              <p class="section-description">Any special instructions for the driver</p>
            </div>
            <AdditionalNotes class="additional-notes" />
          </div>
        </section>

        <!-- Right Column: Map and Summary -->
        <aside class="order-summary">
          <!-- Map Section -->
          <div class="map-section">
            <div class="section-header">
              <h3 class="section-title">Route Preview</h3>
            </div>
            <div class="map-container">
              <Map id="map" class="map" />
            </div>
          </div>

          <!-- Price Breakdown Section -->
          <div class="price-section">
            <PriceBreakdown class="price-breakdown" />
          </div>

          <!-- Action Buttons -->
          <div class="action-buttons">
            <!-- Get Quote Button -->
            <button
              v-show="!canPlaceOrder"
              class="action-btn quote-btn"
              :disabled="!canRequestQuote || disableButton"
              @click="handleGetQuote()"
            >
              <span v-if="!disableButton">Get Quotation</span>
              <span v-else class="loading-text">Getting Quote...</span>
            </button>

            <!-- Place Order Button -->
            <button
              v-show="canPlaceOrder"
              class="action-btn order-btn"
              :disabled="disableButton"
              @click="handlePlaceOrder()"
            >
              <span v-if="!disableButton">Place Order</span>
              <span v-else class="loading-text">Placing Order...</span>
            </button>
          </div>
        </aside>
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
@use "@/css/scss/_mixins.scss" as *;

.place-order-wrapper {
  min-height: 100vh;
  background-color: $bg-light;
  padding: 1rem 0;
  
  @include respond-above('md') {
    padding: 2rem 0;
  }
}

.place-order-container {
  @include container;
  max-width: 1400px;
}

.page-header {
  text-align: center;
  margin-bottom: 2rem;
  
  @include respond-above('md') {
    margin-bottom: 3rem;
  }
}

.page-title {
  @include heading-1;
  color: $txt-secondary;
  margin-bottom: 0.5rem;
}

.page-subtitle {
  @include body-text;
  color: rgba($txt-primary, 0.8);
  max-width: 600px;
  margin: 0 auto;
}

.content-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 2rem;
  
  @include respond-above('lg') {
    grid-template-columns: 1fr 400px;
    gap: 3rem;
  }
  
  @include respond-above('xl') {
    grid-template-columns: 1fr 450px;
    gap: 4rem;
  }
}

.order-configuration {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  
  @include respond-above('md') {
    gap: 2rem;
  }
}

.form-section {
  @include card;
  @include section-spacing;
}

.section-header {
  margin-bottom: 1.5rem;
  
  @include respond-above('md') {
    margin-bottom: 2rem;
  }
}

.section-title {
  @include heading-3;
  margin-bottom: 0.25rem;
}

.section-description {
  @include small-text;
  margin: 0;
}

.order-summary {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
  
  @include respond-above('lg') {
    position: sticky;
    top: 2rem;
    height: fit-content;
  }
}

.map-section {
  @include card;
}

.map-container {
  border-radius: 8px;
  overflow: hidden;
  height: 300px;
  
  @include respond-above('md') {
    height: 350px;
  }
}

.map {
  width: 100%;
  height: 100%;
  border: none;
}

.price-section {
  @include card;
}

.action-buttons {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.action-btn {
  @include btn-primary;
  width: 100%;
  min-height: 56px;
  font-weight: $font-weight-medium;
  letter-spacing: 0.025em;
  
  &.quote-btn {
    background-color: $bg-primary;
    
    &:hover:not(:disabled) {
      background-color: $bg-primary-hovered;
    }
  }
  
  &.order-btn {
    background: linear-gradient(135deg, $bg-primary 0%, $bg-primary-hovered 100%);
    box-shadow: 0 4px 12px rgba($bg-primary, 0.3);
    
    &:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba($bg-primary, 0.4);
    }
  }
  
  &:disabled {
    background-color: $bg-primary-disabled;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
  }
}

.loading-text {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  
  &::after {
    content: '';
    width: 16px;
    height: 16px;
    border: 2px solid transparent;
    border-top: 2px solid currentColor;
    border-radius: 50%;
    animation: spin 1s linear infinite;
  }
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

// Component-specific overrides
.address-input,
.vehicle-selection,
.delivery-options,
.schedule-date,
.additional-notes,
.price-breakdown {
  width: 100%;
}

// Mobile-specific adjustments
@media (max-width: 767px) {
  .place-order-wrapper {
    padding: 0.5rem 0;
  }
  
  .place-order-container {
    padding: 0 0.75rem;
  }
  
  .page-header {
    margin-bottom: 1.5rem;
  }
  
  .form-section {
    margin-bottom: 1rem;
    padding: 1rem;
  }
  
  .section-header {
    margin-bottom: 1rem;
  }
  
  .content-grid {
    gap: 1.5rem;
  }
  
  .order-summary {
    gap: 1rem;
  }
  
  .map-container {
    height: 250px;
  }
}

// High-contrast mode support
@media (prefers-contrast: high) {
  .form-section {
    border: 2px solid $border-color;
  }
  
  .action-btn {
    border: 2px solid transparent;
    
    &:focus {
      border-color: $txt-light;
    }
  }
}

// Reduced motion support
@media (prefers-reduced-motion: reduce) {
  .action-btn {
    transition: none;
    
    &:hover:not(:disabled) {
      transform: none;
    }
  }
  
  .loading-text::after {
    animation: none;
  }
}
</style>
