<template>
  <div class="wrapper">
    <PlaceOrderMap class="PlaceOrderMap"/>
    <StepsContainer class="StepsContainer"/>
  </div>

</template>

<script setup>
import PlaceOrderMap from '../components/PlaceOrder/Map/PlaceOrderMap.vue';
import StepsContainer from '../components/PlaceOrder/OrderForm/StepsContainer.vue';

</script>

<style scoped>
.wrapper{
  display: grid;
  grid-template-columns: 50% 50%;
  grid-template-rows: 1fr;
  grid-template-areas: 'form map';
  height: 100%;
  width: 100%;
}

.StepsContainer {
  grid-area: form;
}

.PlaceOrderMap {
  grid-area: map;
}



</style>