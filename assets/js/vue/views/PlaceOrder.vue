<template>
  <div class="wrapper">
    <StepOne />
    <PlaceOrderMap />
  </div>
</template>

<script>
import StepOne from '../components/PlaceOrder/Drawers/StepOne.vue';
import PlaceOrderMap from '../components/PlaceOrder/PlaceOrderMap.vue';


export default {
  name: 'PlaceOrder',
  components: {
    PlaceOrderMap,
    StepOne
  },
};
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
</style>