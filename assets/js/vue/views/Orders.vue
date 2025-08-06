<template>
  <div class="orders-wrapper">
    <header>
      <h2>ORDERS</h2>
      <!-- <EditLocation /> -->
    </header>
    <div class="utility-header">
      <UtilityHeader 
        v-model:current-page="currentPage"
        :total-items="apiResponse.length"
        :items-per-page="itemsPerPage"
        :data="data"
        :fileName="fileName"
        @searchData="fetchOrders" 
      />
    </div>
    <main>
      <WooOrderTable :orders="paginatedData" :isLoading="isLoading" :isTimeout="isTimeout"/>
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
import { ref, onMounted, computed} from 'vue';
import axios from 'axios';

import EditLocation from '../components/Orders/EditLocation.vue';
import WooOrderTable from '../components/Orders/WooOrderTable.vue';
import SelectedOrdersFooter from '../components/Orders/SelectedOrdersFooter.vue';
import UtilityHeader from '../components/Utilities/UtilityHeader.vue';

let debounceTimer;
const itemsPerPage = ref(10);
const currentPage = ref(1);
const apiResponse = ref([]); 
const isLoading = ref(true);
const isTimeout = ref(false); 
const from = ref();
const to = ref();


let data = computed(() => {
  return {
    OrdersReport: apiResponse.value.map(apiResponse => ({
      wcOrderId: apiResponse.wc_order_id,
      lalamoveOrderId: apiResponse.lalamove_order_id,
      dropOffLocation: apiResponse.drop_off_location,
      orderedBy: apiResponse.ordered_by || 'N/A',
      phone: apiResponse.customer_phone || 'N/A',
      email: apiResponse.customer_email || 'N/A',
      orderedOn: apiResponse.ordered_on,
      scheduledOn: apiResponse.scheduled_on,
      serviceType: apiResponse.service_type || 'N/A',
      orderJsonBody: apiResponse.order_json_body || null,
    })),
  };
});

const fileName = computed(() => {
  return `WooCommerce Lalamove Orders from ${from.value} to ${to.value} `;
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return apiResponse.value.slice(start, end);
});


const fetchOrders = async ({ searchQuery, selectedOption, dateRange, refreshData }) => {
  isLoading.value = true;
  isTimeout.value = false;
  from.value = new Date(dateRange.startDate).toLocaleDateString('en-US', {
      month: 'long',
      day: 'numeric',
      year: 'numeric'
    });
    
    to.value = new Date(dateRange.endDate).toLocaleDateString('en-US', {
      month: 'long',
      day: 'numeric',
      year: 'numeric'
    });
  
  if (refreshData) {
    currentPage.value = 1; 
    console.log("REFRESHED")
  }

  try {
    const response = await axios.get(
      `${wooLalamoveAdmin.root}woo-lalamove/v1/get-lalamove-orders/?from=${dateRange.startDate}&to=${dateRange.endDate}&status=${selectedOption}&search_input=${searchQuery}`,
      {
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': wooLalamoveAdmin.api_nonce,
        },
      }
    );

    apiResponse.value = response.data;
    console.log('Woo Lalamove Orders: ', apiResponse.value);
  } catch (error) {
    if (error.code === 'ECONNABORTED') {
      isTimeout.value = true;
    }
    console.error("Error fetching data:", error);
  } finally {
    isLoading.value = false;
  }
};


// onMounted(() => {

//   fetchOrders();
// });
</script>