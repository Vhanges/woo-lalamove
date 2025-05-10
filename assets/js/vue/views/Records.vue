<template>
  <div class="records-wrapper">

    <header>
      <h2>RECORDS</h2>
    </header>

    <nav >
      <utilityNav @searchData = "handleUtilityData"/>
    </nav>

    <main>
      <div class="table-wrapper">
        <table class="woo-order-table">
            <thead>
                <tr>
                    <th>Woo ID</th>
                    <th>Order Date</th>
                    <th>Schedule Date</th>
                    <th>Drop Off Location</th>
                    <th>Contact</th>
                    <th>Service</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody v-if="showTable">
              
              <tr 
                v-for="record in apiResponse.slice(0, 10)" 
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
                <td>{{ record.ordered_on }}</td>
                <td>{{ record.scheduled_on }}</td>
                <td>{{ record.drop_off_location }}</td>
                <td>{{ record.ordered_by || 'N/A' }}</td>
                <td>{{ record.service_type || 'N/A' }}</td>
                <td>{{ record.status_name }}</td>
              </tr>

            </tbody>
        </table>    
      </div>

      
    </main>
    
  </div>
  
</template>


<script setup>
import { ref, defineAsyncComponent } from 'vue';
import axios from 'axios';

const utilityNav = defineAsyncComponent(() => import('../components/Utilities/UtilityHeader.vue'));

let debounceTimer;
const apiResponse = ref([]); 
const isLoading = ref(false); 
const showTable = ref(false);

async function handleUtilityData({ searchQuery, selectedOption, dateRange }) {
    console.log("Search Query:", searchQuery);
    console.log("Selected Option:", selectedOption);
    console.log("Date Range:", dateRange);




    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(async () => {
        try {
            const response = await axios.get(
                `${wooLalamoveAdmin.root}woo-lalamove/v1/records-data/?from=${dateRange.startDate}&to=${dateRange.endDate}&status=${selectedOption}&search_input=${searchQuery}`,
                {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': wooLalamoveAdmin.api_nonce,
                    },
                }
            );

            apiResponse.value = response.data;
            console.log("API Response:", apiResponse.value);

            console.log("API Response:", apiResponse.value.wc_order_id);

            showTable.value = true;
        } catch (error) {
            console.error("Error fetching data:", error);
        } finally {
            isLoading.value = false; 
            showTable.value = true;
        }
    }, 2000);
};
</script>




<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;

.records-wrapper{
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

    nav{
      height: fit-content;
      width: 100%;
    }

    main {
      .table-wrapper{
        border: 2px solid $border-color;
        width: 100%;
        border-radius: 5px;
        background-color: $bg-high-light;


        .woo-order-table {
            width: 100%;
            border-collapse: collapse;
            
            th, td {
                padding: 1rem;
                text-align: left;
                border-bottom: 1px solid $bg-gray;
            }
            
            th {
                background-color:  $bg-gray;
                font-weight: bold;
            }
            

            tr:hover {
                background-color: $bg-primary-light;
                box-shadow: inset 2px 0 0 0 $bg-primary;
            }

            .selected-row{
                background-color: $bg-primary-light;
                box-shadow: inset 2px 0 0 0 $bg-primary;
            }
        }
      }
    }




}
</style>
