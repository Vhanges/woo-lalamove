<template>
  <div class="orders-wrapper">
    <header>
      <h2>ORDERS</h2>
      <!-- <EditLocation /> -->
    </header>
    <div class="utility-header">
      <UtilityHeader />
    </div>
    <main>
      <WooOrderTable :orders="WooLalamoveOrders" />
    </main>
    <SelectedOrdersFooter/>
  </div>
</template>


<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;

  .orders-wrapper {
    position: relative;
    display: flex; 
    flex-direction: column;
    gap: 2vh;
    height: 100%;
    padding-bottom: 10rem;

    header {
      display: flex;
      justify-content: space-between;
      height: 10vh;
      align-items: center;
      margin: 2rem 2rem 0 2rem;
    }

    .utility-header {
      height: fit-content;
      margin: 0 2rem;
    }

    main {
      height: fit-content;
      margin: 0 2rem 5rem 2rem;
    }
  }

</style>

<script setup>
import { ref, onMounted} from 'vue';
import axios from 'axios';

import EditLocation from '../components/Orders/EditLocation.vue';
import WooOrderTable from '../components/Orders/WooOrderTable.vue';
import SelectedOrdersFooter from '../components/Orders/SelectedOrdersFooter.vue';
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
