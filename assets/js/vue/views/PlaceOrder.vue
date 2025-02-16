<template>
  <div class="wrapper">
    <StepOne class="StepOne"/>
    <PlaceOrderMap class="PlaceOrderMap"/>
    <MarketSelection class="MarketSelection"/>
  </div>
</template>

<script setup>
import StepOne from '../components/PlaceOrder/Drawers/StepOne.vue';
import PlaceOrderMap from '../components/PlaceOrder/PlaceOrderMap.vue';
import MarketSelection from '../components/Controls/MarketSelection.vue';


</script>

<style scoped>
.wrapper{
  flex: 1;
  display: grid;
  grid-template-columns: 40% 60%;
  grid-template-rows: 1fr;
  grid-template-areas: 'drawer map';
  height: 100%;
  width: 100%;
  margin: 0;
}

.PlaceOrderMap {
  grid-area: map;
}

.StepOne {
  grid-area: drawer;
}

.MarketSelection {
  grid-area: map ;
  justify-self: flex-end;
  align-self: flex-start;
  z-index: 1000;
}
</style>