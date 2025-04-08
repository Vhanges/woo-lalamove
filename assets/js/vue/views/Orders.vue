<template>
  <div class="orders-wrapper">
    <header>
      <h2>ORDERS</h2>
      <EditLocation />
    </header>
    <div class="utility-header">
      <UtilityHeader />
    </div>
    <main>
      <WooOrderTable :orders="WooLalamoveOrders" />
    </main>
  </div>
</template>


<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;

  .orders-wrapper {
    display: flex; 
    flex-direction: column;
    gap: 2vh;
    height: 100%;
    margin: 2rem;

    header {
      display: flex;
      justify-content: space-between;
      height: 10vh;
      width: 100%;
      align-items: center;
    }

    .utility-header {
      height: fit-content;
      width: 100%;
    }

    main {
      height: 60vh;
      max-height: 60vh;
      width: 100%;
    }
  }

  h2 {
    font-size: $font-size-xxl;
    font-weight: $font-weight-regular;
  }




</style>

<script setup>
import { ref, onMounted} from 'vue';
import axios from 'axios';

import EditLocation from '../components/Orders/EditLocation.vue';
import WooOrderTable from '../components/Orders/WooOrderTable.vue';
import UtilityHeader from '../components/Utilities/UtilityHeader.vue';

const WooLalamoveOrders = ref([]);

const fetchOrders = async () => {
  try {
    const response = await axios.get(
      wooLalamoveAdmin.root + 'woo-lalamove/v1/get-lalamove-orders',
      {
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': wooLalamoveAdmin.api_nonce,
        },
      }
    );

    WooLalamoveOrders.value = response.data;
    console.log('Woo Lalamove Orders: ', WooLalamoveOrders.value);
  } catch (error) {
    console.error('Error fetching Woo Lalamove Orders:', error.response?.data || error.message);
  }
};


onMounted(() => {

  fetchOrders();
});
</script>
