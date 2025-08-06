<template>
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
    </div>
    <div v-else class="spending-wrapper">
        <div class="utility-actions">
            <ExcelExport
                :data="data"
                :fileName="fileName"
            />
            <DateRangePicker @dateRangeSelected = "handleDateRange"/>
            <div @click = "toggleRefresh" class="action action-refresh">
                    <span class="material-symbols-outlined restart">restart_alt</span>
            </div>
        </div>
       
        <div v-if="bodyLoading" class="loading-container">
            <div class="loading-spinner"></div>
        </div>

        <div v-else-if="noData" class="message-container">
          <h1>{{message}}</h1>
        </div>

        <div v-else class="orders-body">
            <div class="key-performance-indicator-container">
                <div class="key-performance-indicator bordered">
                    <p>
                        Total Orders
                        <span class="material-symbols-outlined icon">orders</span>
                    </p>
                    <h2 style="margin-bottom: 0;">{{ formatNumber(totalOrders) }}</h2>
                </div>
                <div class="key-performance-indicator bordered">
                    <p>
                        Completed Orders
                        <span class="material-symbols-outlined icon">check_box</span>
                    </p>
                    <h2 style="margin-bottom: 0;">{{ formatNumber(completedDeliveries) }}</h2>
                </div>
                <div class="key-performance-indicator bordered">
                    <p>
                        Active Deliveries
                        <span class="material-symbols-outlined icon">delivery_truck_speed</span>
                    </p>
                    <h2 style="margin-bottom: 0;">{{ formatNumber(activeDeliveries) }}</h2>
                </div>
                <div class="key-performance-indicator bordered">
                    <p>
                        Pending Orders
                        <span class="material-symbols-outlined icon">hourglass_top</span>
                    </p>
                    <h2 style="margin-bottom: 0;">{{ formatNumber(pendingOrders) }}</h2>
                </div>
                <div class="key-performance-indicator bordered">
                    <p>
                        Failed Deliveries
                        <span class="material-symbols-outlined icon">disabled_by_default</span>
                    </p>
                    <h2 style="margin-bottom: 0;">{{ formatNumber(failedDeliveries) }}</h2>
                </div>
                <div class="key-performance-indicator bordered">
                    <p>
                        Rejected Orders
                        <span class="material-symbols-outlined icon">disabled_by_default</span>
                    </p>
                    <h2 style="margin-bottom: 0;">{{ formatNumber(rejectedOrders)}}</h2>
                </div>
            </div>


                <div class="table-wrapper">
                    <table class="woo-order-table">
                        <thead>
                            <tr>
                                <th>Woo ID</th>
                                <th>Ordered On</th>
                                <th>Scheduled On</th>
                                <th>Ordered By</th>
                                <th>Drop Off Location</th>
                                <th>Service Type</th>
                                <th>Contacts</th>
                                <th>Status</th>
                                
                            </tr>
                    </thead>
    
                    <tbody>
                        <tr v-for="order in limitedDashboardTable" :key="order.wc_order_id">
                    
                            <td style="vertical-align: center;">
                                {{ order.wc_order_id }}
                            </td>
                            <td v-html=" order.ordered_on"></td>
                            <td v-html=" order.scheduled_on"></td>
                            <td>{{ order.ordered_by}}</td>
                            <td>{{ order.drop_off_location }}</td>
                            <td>{{ order.service_type }}</td>
                            <td v-if="order.customer_phone || order.customer_email">
                                <span v-if="order.customer_phone">{{ order.customer_phone }}</span>
                                <br v-if="order.customer_phone && order.customer_email">
                                <span v-if="order.customer_email">{{ order.customer_email }}</span>
                            </td>
                            <td>{{ order.status_name }}</td>
                        </tr>
                    </tbody>
                </table>    
            </div>

            <section class="second-section" v-if="showChart">
                <TotalOrdersOverview 
                    class="bordered WalletBalanceTrend"
                    :label="label"
                    :total="total"
                />
                <OrderStatusTrend 
                    class="bordered ShippingSpendingTrend"
                    :label="label"
                    :completed="completed"
                    :active="active"
                    :pending="pending"
                    :failed="failed"
                    :rejected="rejected"
                    />
                <OrderFullfilmentBreakdown class="bordered"
                    :label="label"
                    :successful="successful"
                    :unsuccessful="unsuccessful"
                />
            </section>

        </div>

    </div>
</template>



<script setup>

import { ref, computed, onMounted} from 'vue';    
import axios from 'axios';

import TotalOrdersOverview from './Charts/Orders/TotalOrdersOverview.vue';
import OrderStatusTrend from './Charts/Orders/OrderStatusBreakdown.vue';
import OrderFullfilmentBreakdown from './Charts/Orders/OrderFulfillmentBreakdown.vue';
import ExcelExport from '../Controls/ExcelExport.vue';
import DateRangePicker from '../Controls/DateRangePicker.vue';


const loading = ref(true);
const bodyLoading = ref(true);

const dashboardKpi = ref(null);
const dashboardTable = ref(null);
const limitedDashboardTable = ref(null);
const dashboardChart = ref(null);



const totalOrders = computed(() => dashboardKpi.value?.total_orders || 0);
const completedDeliveries = computed(() => dashboardKpi.value?.completed_deliveries || 0);
const activeDeliveries = computed(() => dashboardKpi.value?.active_deliveries || 0);
const pendingOrders = computed(() => dashboardKpi.value?.pending_orders || 0);
const failedDeliveries = computed(() => dashboardKpi.value?.failed_deliveries || 0);
const rejectedOrders = computed(() => dashboardKpi.value?.rejected_orders || 0);

const showChart = ref(true);

const label = ref([]);  
const total = ref([]);  
const completed = ref([]);
const active = ref([]);
const pending = ref([]);
const failed = ref([]);
const rejected = ref([]);

const successful = ref(completedDeliveries);
const unsuccessful = computed(() => failedDeliveries.value + rejectedOrders.value);


let data = computed(() => ({

  ordersKPI: [
    {
      totalOrders: totalOrders.value,
      completedDeliveries: completedDeliveries.value,
      activeDeliveries: activeDeliveries.value,
      pendingOrders: pendingOrders.value,
      failedDeliveries: failedDeliveries.value,
      rejectedOrders: rejectedOrders.value,
    },
  ],
  ordersChartData: dashboardChart.value.map(entry => ({
    chartLabel: entry.chart_label,
    Completed: entry.completed_count,
    Failed: entry.failed_count,
    Pending: entry.pending_count,
    Processed: entry.processed_count,
    Rejected: entry.rejected_count,
    TotalOrders: entry.status_count,
  })),
  OrdersData: dashboardTable.value.map(entry => ({
    wooID: entry.wc_order_id,
    serviceType: entry.service_type,
    orderedOn: entry.ordered_on,
    orderedBy: entry.ordered_by,
    email: entry.customer_email,
    phoneNo: entry.customer_phone,
    dropOffLocation: entry.drop_off_location,
    scheduledOn: entry.scheduled_on,
  }))

}));



const fileName = ref();
async function  handleDateRange({startDate, endDate}) {

  showChart.value = false
  await fetchOrdersDashboardData(startDate, endDate)
  showChart.value = true
  await fillChartData()

}

function toggleRefresh(){
    window.location.reload();
}

function fillChartData(){
    label.value = [];
    total.value = [];
    completed.value = [];
    active.value = []; 
    failed.value = [];
    rejected.value = [];

    dashboardChart.value.forEach(element => {
        label.value.push(element.chart_label || "Unknown"); 
        total.value.push(element.status_count || 0);
        completed.value.push(parseInt(element.completed_count, 10) || 0);
        pending.value.push(parseInt(element.pending_count, 10) || 0);
        failed.value.push(parseInt(element.failed_count, 10) || 0);
        rejected.value.push(parseInt(element.rejected_count, 10) || 0);
    });

}

function formatNumber(num) {
  if (num >= 1_000_000) {
    return (num / 1_000_000).toFixed(1) + 'M';
  } else if (num >= 1_000) {
    return (num / 1_000).toFixed(1) + 'K';
  }
  return num.toString(); 
}

const noData = ref(false);
const message = ref('');
const fetchOrdersDashboardData = async (startDate, endDate) => {
  try {

    bodyLoading.value = true;

    const response = await axios.get(
      wooLalamoveAdmin.root + 'woo-lalamove/v1/dashboard-orders-data/?from=' + startDate + '&to=' + endDate,
      {
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': wooLalamoveAdmin.api_nonce,
        },
      }
    );

    limitedDashboardTable.value = response.data.table.slice(0, 10); 
    dashboardTable.value = response.data.table;
    dashboardKpi.value = {
        total_orders: parseInt(response.data.kpi.total_orders, 10) || 0,
        completed_deliveries: parseInt(response.data.kpi.completed_deliveries, 10) || 0,
        active_deliveries: parseInt(response.data.kpi.active_deliveries, 10) || 0,
        pending_orders: parseInt(response.data.kpi.pending_orders, 10) || 0,
        failed_deliveries: parseInt(response.data.kpi.failed_deliveries, 10) || 0,
        rejected_orders: parseInt(response.data.kpi.rejected_orders, 10) || 0,
    };
    dashboardChart.value = response.data.chart_data;

    dashboardTable.value = response.data.table || [];

    noData.value = dashboardTable.value.length === 0;

    bodyLoading.value = false;

    message.value = noData.value ? "No results found" : ""; 


  } catch (error) {
    console.error('Error fetching Woo Lalamove Orders:', error.response?.data || error.message);
    message.value = "An error occured. Contact our developer to address the issue: angelosevhen@gmail.com"; 

  } finally {
    loading.value = false;
  }
};

onMounted(() =>{
    const start = moment().subtract(30, 'days').format('YYYY-MM-DD');
    const end = moment().add(2, 'days').format('YYYY-MM-DD');
    console.log(start, '\n', end);
    handleDateRange({startDate: start, endDate: end});
});
</script>

<style lang="scss" scoped>
@use '@/css/scss/_variables.scss' as *;



* {
    box-sizing: border-box;
}

.spending-wrapper {

    display: flex;
    flex-direction: column;
    margin-top: 1rem;

        .utility-actions {
            grid-area: utility-actions;
            display: flex;
            flex-direction: row;
            gap: 1rem;
            align-items: center;
            z-index: 100;    
            
            .action {
                display: flex;
                justify-content: center; 
                align-items: center;
                cursor: pointer;
                height: 2rem;
                width: 2rem ;
                border-radius: 3%;
                border: 2px solid $border-color;
                background-color: $bg-high-light;
                border-radius: 5px;
            }

            .restart {
                font-size: $font-size-lg;
            }
        }

        .orders-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            grid-template-rows: auto auto auto;
            grid-template-areas: 
                "key-performance-indicator key-performance-indicator"
                "first-section first-section"
                "second-section second-section"
                "SubsidyVsCustomerPaid AdditionalFeeFrequency";
            width: 100%;
            height: 100%;
            gap: 1rem;
            margin-top: 1rem;


            .key-performance-indicator-container {
                grid-area: key-performance-indicator;
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                padding: 0;

                p {
                    display: flex;
                    align-items: center;
                    justify-content: space-between;
                    margin: 0;
                }

                .icon {
                    font-size: $font-size-md;
                    margin-left: 3rem
                }
            }


            .key-performance-indicator {
                grid-area: key-performance-indicator;
            }

            .second-section {
                grid-area: second-section;
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                align-items: flex-start;
                gap: 1rem;

                > * {
                    flex: 1;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100%; 
                }
            }

            
            .SubsidyVsCustomerPaid {
                grid-area: SubsidyVsCustomerPaid;
            }

            .AdditionalFeeFrequency {
                grid-area: AdditionalFeeFrequency ;
            }

            .third-section {
                grid-area: third-section;
                display: flex;
                flex-direction: row;
            }
        }

        .bordered {
            background-color: #FCFCFC;
            padding: 1rem;
            border-radius: 0.5rem;
            border: 1px solid $border-color;
        }
        .table-wrapper {
            border: 1px solid $border-color;
            width: 100%;
            border-radius: 5px;
            background-color: $bg-high-light;
            grid-area: first-section;
            max-height: 300px;
            overflow: scroll;

            .woo-order-table {
                width: 100%;
                border-collapse: collapse;
                position: relative;

                thead {
                    position: sticky;
                    top: 0;
                    z-index: 1;
                    background-color: $bg-gray;
                }

                th, td {
                    padding: 1rem;
                    text-align: left;
                    border-bottom: 1px solid $bg-gray;
                    font-size: $font-size-xs;
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

h1 {
    font-family: $font-primary;
}


.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 80vh;
}

.message-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 50vh;
}

.loading-spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #f16622;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

</style>