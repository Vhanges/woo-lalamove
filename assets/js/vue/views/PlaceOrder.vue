
<template>
  <main class="wrapper">
    <div class="content">
      <div class="message">Message</div>

      <Map
        id="map"
        class="map"
      />

      <div class="vehicle-container" >
        <p class="header">VEHICLE TYPE</p>
        <VehicleSelection/>
      </div>

      
      <DeliveryOptions
        class="additional-request"
      />

      <ScheduleDate
        class="schedule-container"
      />

      
      <AdditionalNotes
        class="additional-notes-container"
      />
      
      <PriceBreakdown
        class="price-breakdown"
      />

      <div class="total-container">
        <p>Total:</p>
        <p>0</p>
      </div>

      <button class="enter">Get Quotation</button>
    </div>
  </main>
</template>

<script setup>
import {onMounted} from 'vue'
import { storeToRefs } from 'pinia';

import { useLalamoveStore } from '../store/lalamoveStore';
import Map from "../components/PlaceOrder/Map/PlaceOrderMap";
import VehicleSelection from "../components/PlaceOrder/Vehicle/VehicleSelection.vue";
import DeliveryOptions from "../components/PlaceOrder/DeliveryOptions.vue";
import ScheduleDate from "../components/PlaceOrder/ScheduleDate.vue";
import AdditionalNotes from "../components/PlaceOrder/AdditionalNotes.vue";
import PriceBreakdown from "../components/PlaceOrder/PriceBreakdown.vue";

const lalamove = useLalamoveStore();
const {services} = storeToRefs(lalamove);
const {fetchCity} = lalamove;

onMounted( () => {
  fetchCity();
})
</script>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;

.wrapper {
  height: 1300px;
  display: flex;
  justify-content: center;
  align-items: start;
  padding-top: 5%;
}

.content {
  height: 80%;
  width: 90%;

  .message {
    grid-area: 1 / 1 / 2 / 2;
    padding: 2%;
    border-bottom: 2px solid $border-color;
  }

  .vehicle-container {
    grid-area: 2 / 1 / 6 / 2;
    display: flex !important;
    flex-direction: column;
    width: 500px;
    padding: 0;

  }
  .additional-request {
    grid-area: 6 / 1 / 8 / 2;
  }
  .schedule-container {
    grid-area: 8 / 1 / 10 / 2;
  }
  .additional-notes-container {
    grid-area: 10 / 1 / 13 / 2;
  }
  .map {
    grid-area: 1 / 2 / 10 / 3;
  }
  .price-breakdown {
    grid-area: 10 / 2 / 13 / 3;
  }
  .total-container {
    grid-area: 13 / 2 / 15 / 3;
    display: flex; 
    justify-content: space-around;
    align-items: center;
    background-color: $bg-high-light;
    border: 1px solid $border-color;
    border-radius: 5px;

    & > p:first-child{
        font-size: $font-size-sm;
        font-weight: $font-weight-bold;
    }

    & > p:last-child{
        color: $txt-orange;
        font-size: $font-size-xxl;
        font-weight: $font-weight-bold;
    }

  }
  .enter {
    grid-area: 15 / 2 / 16 / 3;
    color: $txt-light;
    border: none;
    border-radius: 5px;
    font-size: $font-size-lg;
    background-color: $bg-primary;

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
    grid-template-columns: 1fr 2fr; 
    grid-template-rows: repeat(15, 50px);
    grid-column-gap: 50px;
    grid-row-gap: 15px;
  }
}



@media (max-width: 500px) {
  .content {
    display: grid;
    grid-template-columns: 1fr;
    grid-template-rows: repeat(30, 50px);
    grid-row-gap: 15px;
  }

  .message {
    grid-area: 1 / 1 / 2 / 2 !important;
  }

  .map {
    grid-area: 2 / 1 / 8 / 2 !important;
  }

  .vehicle-container {
    grid-area: 8 / 1 / 12 / 2 !important;
  }

  .additional-request {
    grid-area: 12 / 1 / 14 / 2 !important;
  }

  .schedule-container {
    grid-area: 14 / 1 / 16 / 2 !important;
  }

  .additional-notes-container {
    grid-area: 16 / 1 / 18 / 2 !important;
  }

  .price-breakdown {
    grid-area: 18 / 1 / 21 / 2 !important;
  }

  .total-container {
    grid-area: 21 / 1 / 23 / 2 !important;
  }

  .enter {
    grid-area: 23 / 1 / 24 / 2 !important;
  }
}


</style>
