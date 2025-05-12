<template>
  <div class="records-wrapper">
    <header>
      <h2>RECORDS</h2>
    </header>

    <nav>
      <utilityNav
      v-model:current-page="currentPage"
      :total-items="apiResponse.length"
      :items-per-page="itemsPerPage"
      :data="data"
      :filename="computedFilename"
      @searchData="handleUtilityData" 
      />
    </nav>

    <main>
      <div class="table-wrapper">

        <div v-if="isTimeout" class="timeout-message">
          <h3>Server Timeout</h3>
          <p>Please try refreshing the page or check your connection</p>
          <button @click="handleUtilityData">Retry</button>
        </div>

                <!-- Skeleton Loader -->
        <div v-else-if="isLoading" class="skeleton-table">
          <div class="skeleton-header">
            <div v-for="header in headers" :key="header" class="skeleton-cell"></div>
          </div>
          <div class="skeleton-body">
            <div v-for="i in 5" :key="`skeleton-row-${i}`" class="skeleton-row">
              <div v-for="header in headers" :key="header" class="skeleton-cell"></div>
            </div>
          </div>
        </div>

        <table v-else class="woo-order-table">
          <thead>
            <tr>
              <th v-for="header in headers" :key="header">{{ header }}</th>
            </tr>
          </thead>
          
          <tbody>

            <!-- Empty State -->
            <tr v-if="!isLoading && !apiResponse.length">
              <td colspan="7" style="text-align: center;">
                No records found.
              </td>
            </tr>

            <!-- Actual Data -->
            <template v-else>
              <tr 
                v-for="record in paginatedData" 
                :key="`${record.wc_order_id}-${record.lalamove_order_id}`"
                @click="$router.push({ 
                  name: 'records-details', 
                  params: { 
                    lala_id: record.lalamove_order_id, 
                    wc_id: record.wc_order_id 
                  }
                })" 
                style="cursor: pointer;"
              >
                <td>{{ record.wc_order_id }}</td>
                <td>{{ record.lalamove_order_id }}</td>
                <td>{{ record.ordered_on }}</td>
                <td>{{ record.scheduled_on }}</td>
                <td>{{ record.drop_off_location }}</td>
                <td>{{ record.ordered_by || 'N/A' }}</td>
                <td>{{ record.service_type || 'N/A' }}</td>
              </tr>
            </template>
          </tbody>
        </table>    
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, defineAsyncComponent, computed } from 'vue';
import axios from 'axios';

const utilityNav = defineAsyncComponent(() => import('../components/Utilities/UtilityHeader.vue'));

let debounceTimer;
const itemsPerPage = ref(10);
const currentPage = ref(1);
const apiResponse = ref([]); 
const isLoading = ref(true);
const isTimeout = ref(false); // New timeout state
const headers = ref([
  'Woo ID', 
  'Lala ID',
  'Order Date', 
  'Schedule Date', 
  'Drop Off Location', 
  'Contact', 
  'Service', 
]);



let data = computed(() => {
  return {
    RecordsReport: apiResponse.value.map(apiResponse => ({
      wcOrderId: apiResponse.wc_order_id,
      lalamoveOrderId: apiResponse.lalamove_order_id,
      dropOffLocation: apiResponse.drop_off_location,
      orderedBy: apiResponse.ordered_by || 'N/A',
      orderedOn: apiResponse.ordered_on,
      scheduledOn: apiResponse.scheduled_on,
      serviceType: apiResponse.service_type || 'N/A',
      orderJsonBody: apiResponse.order_json_body || null,
    })),
  };
});

const computedFilename = computed(() => {
  return 'Lalamove Records ' + new Date().toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
});

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return apiResponse.value.slice(start, end);
});


async function handleUtilityData({ searchQuery, selectedOption, dateRange, refreshData }) {
  
  if (refreshData) {
    currentPage.value = 1; 
    fetchData(); 
  }

  // Inner function to handle API requests
  async function fetchData() {
    try {
      isLoading.value = true;
      isTimeout.value = false; // Reset timeout state on new request

      const response = await axios.get(
        `${wooLalamoveAdmin.root}woo-lalamove/v1/records-data/?from=${dateRange.startDate}&to=${dateRange.endDate}&status=${selectedOption}&search_input=${searchQuery}`,
        {
          headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': wooLalamoveAdmin.api_nonce,
          },
          timeout: 10000
        }
      );

      apiResponse.value = response.data;


    } catch (error) {
      if (error.code === 'ECONNABORTED') {
        isTimeout.value = true;
      }
      console.error("Error fetching data:", error);
    } finally {
      isLoading.value = false;
    }
  }

  // Debounce logic
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(async () => {
    await fetchData(); // Calling the inner function
  }, 2000);
}

</script>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;

.records-wrapper {
  display: flex; 
  flex-direction: column;
  position: relative;
  gap: 2vh;
  height: fit-content;
  margin: 2rem;
  
  header {
    display: flex;
    justify-content: space-between;
    height: 10vh;
    width: 100%;
    align-items: center;
  }

  nav {
    height: fit-content;
    width: 100%;
  }

  main {
    .table-wrapper {
      border: 2px solid $border-color;
      width: 100%;
      overflow: auto;
      max-height: 500px;
      border-radius: 5px;
      background-color: $bg-high-light;

      .woo-order-table {
        width: 100%;
        border-collapse: collapse;

        thead{
          position: sticky;
          top: 0;
          z-index: 2;
        }
        
        th, td {
          padding: 1rem;
          text-align: left;
          border-bottom: 1px solid $bg-gray;
        }
        
        th {
          background-color: $bg-gray;
          font-weight: bold;
        }

        tr:hover {
          background-color: $bg-primary-light;
          box-shadow: inset 2px 0 0 0 $bg-primary;
        }

        .selected-row {
          background-color: $bg-primary-light;
          box-shadow: inset 2px 0 0 0 $bg-primary;
        }
      }
    }
  }

  .timeout-message {
    text-align: center;
    padding: 2rem;
    color: #ff6b6b;
    
    h3 {
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
    }
    
    button {
      margin-top: 1rem;
      padding: 0.5rem 1rem;
      background: #ff6b6b;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      
      &:hover {
        background: #ff5252;
      }
    }
  }



.skeleton-header {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1rem;
  padding: 1rem;
  position: sticky;
  top: 0;
  background: $bg-gray;
  z-index: 2;

  .skeleton-cell {
    height: 20px;
    background: rgba(0, 0, 0, 0.1);
    border-radius: 4px;
  }
}

.skeleton-body {
  padding: 0 1rem;
}

.skeleton-row {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1rem;
  padding: 1rem 0;
  position: relative;
  overflow: hidden;

  &::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(
      90deg,
      transparent 25%,
      rgba(255, 255, 255, 0.2) 50%,
      transparent 75%
    );
    animation: shimmer 1.5s infinite;
  }
}

.skeleton-cell {
  height: 20px;
  background: $bg-gray;
  border-radius: 4px;
  position: relative;
  z-index: 1;
}

@keyframes shimmer {
  0% { transform: translateX(-100%); }
  100% { transform: translateX(100%); }
}
}
</style>