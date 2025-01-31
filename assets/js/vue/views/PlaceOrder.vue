<template>
  <div class="wrapper">
    <h2>Place Order</h2>
    <PlaceOrderMap />
  </div>
</template>

<script>
import PlaceOrderMap from '../components/maps/PlaceOrderMap.vue';

export default {
  name: 'PlaceOrder',
  components: {
    PlaceOrderMap 
  },
};
</script>

<style scoped>
.wrapper{
  height: 800px;
  width: 100%;
}
</style>