<template>
    <div v-if="loading" class="loading-container">
      <div class="loading-spinner"></div>
    </div>
    <div v-else class="spending-wrapper">
        <div class="utility-actions">
            <ExcelExport
                :data="data"
                :filename="fileName"
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
        

        <div v-else class="spending-body">
            <div class="key-performance-indicator-container">
                <div class="key-performance-indicator bordered">
                    <p>
                        Total Spending
                        <span class="material-symbols-outlined icon">payments</span>
                    </p>
                    <h2 style="margin-bottom: 0;">{{ formatNumber(totalSpending) }}</h2>
                </div>
                <div class="key-performance-indicator bordered">
                    <p>
                        Net Spending
                        <span class="material-symbols-outlined icon">account_balance</span>
                    </p>
                    <h2 style="margin-bottom: 0;">{{ formatNumber(netSpending) }}</h2>
                </div>
                <div class="key-performance-indicator bordered">
                    <p>
                        Customer Spent
                        <span class="material-symbols-outlined icon">account_circle</span>
                    </p>
                    <h2 style="margin-bottom: 0;">{{ formatNumber(customerSpent) }}</h2>
                </div>
                <div class="key-performance-indicator bordered">
                    <p>
                        Base Delivery Cost
                        <span class="material-symbols-outlined icon">local_shipping</span>
                    </p>
                    <h2 style="margin-bottom: 0;">{{ formatNumber(baseDeliveryCost) }}</h2>
                </div>
                <div class="key-performance-indicator bordered">
                    <p>
                        Shipping Subsidy
                        <span class="material-symbols-outlined icon">account_balance</span>
                    </p>
                    <h2 style="margin-bottom: 0;">{{ formatNumber(shippingSubsidy) }}</h2>
                </div>
                <div class="key-performance-indicator bordered">
                    <p>
                        Priority Fee
                        <span class="material-symbols-outlined icon">attach_money</span>
                    </p>
                    <h2 style="margin-bottom: 0;">{{ formatNumber(priorityFee) }}</h2>
                </div>
            </div>

            <!-- First Section -->
            <section class="first-section ">
                <div class="table-wrapper">
                    <table class="woo-order-table">
                        <thead>
                            <tr>
                                <th>Lala ID</th>
                                <th>Ordered By</th>
                                <th>Service Type</th>
                                <th>Payment Source</th>
                                <th>Overall Expense</th>
                                <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in dashboardTable.slice(0, 10)" :key="order.wc_order_id">
                    
                            <td style="vertical-align: center;">
                                {{ order.lalamove_order_id }}
                            </td>
                            <td>{{ order.ordered_by}}</td>
                            <td>{{ order.service_type }}</td>
                            <td v-html=" order.payment_method"></td>
                            <td>{{ order.overall_expense}}</td>
                            <td>{{ order.status_name}}</td>
                        </tr>
                    </tbody>
                </table>    
            </div>
            
            <ServiceCostBreakdown
            class="bordered"
            :chartLabel = "chartLabel"
            :chartMotorcycle = "chartMotorcycle"
            :chartMotorcycleVehicle = "chartMotorVehicle"
            :chartVan = "chartVan"
            :chartHeavyTruck = "chartHeavyTruck"
            :chartTruck = "chartTruck"
            v-if="showChart"/>
            </section>

            <!-- Second Section -->
            <WalletBalanceTrend v-if="showChart" 
            class="bordered WalletBalanceTrend"
            :chartLabel = "chartLabel"
            :chartWalletBalance = "chartWalletBalance"
            />
            <ShippingSpendingTrend  v-if="showChart"
            class="bordered ShippingSpendingTrend"
            :chartLabel = "chartLabel"
            :chartTotalSpending = "chartTotalSpending" 
            :chartNetSpending = "chartNetSpending" 
            />

            <!-- Third Section -->
            <SubsidyVsCustomerPaid v-if="showChart"
            class="bordered SubsidyVsCustomerPaid" 
            :chartLabel = "chartLabel"
            :chartTotalCustomerSpending = "chartTotalCustomerSpending" 
            :chartTotalSubsidySpending = "chartTotalSubsidySpending" 
            />
            <AdditionalFeeFrequency  v-if="showChart" 
            class="bordered AdditionalFeeFrequency"
            :chartLabel = "chartLabel"
            :chartSurchargeSpending = "chartSurchargeSpending" 
            :chartPriorityFeeSpending = "chartPriorityFeeSpending" 
            />
        </div>
    </div>
</template>

<script setup>

import {ref, computed, onMounted} from 'vue';
import axios from 'axios';

import AdditionalFeeFrequency from './Charts/Spending/AdditionalFeeFrequency.vue';
import ServiceCostBreakdown from './Charts/Spending/ServiceBreakdown.vue';
import ShippingSpendingTrend from './Charts/Spending/ShippingSpendingTrend.vue';
import SubsidyVsCustomerPaid from './Charts/Spending/SubsidyVsCustomerPaid.vue';
import WalletBalanceTrend from './Charts/Spending/WalletBalanceTrend.vue';
import ExcelExport from '../Controls/ExcelExport.vue';
import DateRangePicker from '../Controls/DateRangePicker.vue';

const loading = ref(true);
const bodyLoading = ref(true);

const dashboardTable = ref([]);
const dashboardChart = ref([]);

const totalSpending = ref(0);
const netSpending = ref(0);
const customerSpent = ref(0);
const baseDeliveryCost = ref(0);
const shippingSubsidy = ref(0);
const priorityFee = ref(0);

const chartLabel = ref([]);
const chartMotorcycle = ref(0);
const chartMotorVehicle = ref(0);
const chartVan = ref(0);
const chartHeavyTruck = ref(0);
const chartTruck = ref(0);

const chartTotalSpending = ref([]);
const chartNetSpending = ref([]);
const chartTotalCustomerSpending = ref([]);
const chartTotalSubsidySpending = ref([]);
const chartBaseDeliveryCost = ref([]);
const chartPriorityFeeSpending = ref([]);
const chartSurchargeSpending = ref([]);
const chartWalletBalance = ref([]);

const showChart = ref(true);

let data = computed(() => ({
  spendingKPI: [
    {
      totalSpending: totalSpending.value,
      netSpending: netSpending.value,
      customerSpent: customerSpent.value,
      baseDeliveryCost: baseDeliveryCost.value,
      shippingSubsidy: shippingSubsidy.value,
      priorityFee: priorityFee.value,
    },
  ],
  spendingChartData: dashboardChart.value.map(entry => ({
    chartLabel: entry.chart_label,
    motorcycleCount: entry.motorcycle_count,
    motorVehicleCount: entry.motor_vehicle_count,
    vanCount: entry.van_count,
    heavyTruckCount: entry.heavy_truck_count,
    truckCount: entry.truck_count,
    totalSpending: entry.total_spending,
    netSpending: entry.net_spending,
    customerSpent: entry.total_customer_spending,
    subsidySpent: entry.total_subsidy_spending,
    baseDeliveryCost: entry.base_delivery_cost,
    priorityFee: entry.priority_fee_spending,
    surcharge: entry.surcharge_spending,
    walletBalance: entry.wallet_balance,
  })),
  TransactionData: dashboardTable.value.map(entry => ({
    lalaID: entry.lalamove_order_id,
    wooID: entry.wc_order_id,
    orderedBy: entry.ordered_by,
    orderedOn: entry.ordered_on,
    overallExpense: entry.overall_expense,
    paymentMethod: entry.payment_method,
    serviceType: entry.service_type,
    statusName: entry.status_name,
  }))
}));

const fileName = ref();
async function  handleDateRange({startDate, endDate}) {

  showChart.value = false
  await fetchSpendingDashboardData(startDate, endDate)
  showChart.value = true
  await fillChartData()
};

function toggleRefresh(){
    window.location.reload();
}

function fillChartData() {
    if (!dashboardChart.value || !Array.isArray(dashboardChart.value)) {
        console.error('dashboardChart is not a valid array');
        return;
    }

    chartLabel.value = [];
    chartMotorcycle.value = 0;
    chartMotorVehicle.value = 0;
    chartVan.value = 0;
    chartHeavyTruck.value = 0;
    chartTruck.value = 0;

    chartTotalSpending.value = [];
    chartNetSpending.value = [];
    chartTotalCustomerSpending.value = [];
    chartTotalSubsidySpending.value = [];
    chartBaseDeliveryCost.value = [];
    chartPriorityFeeSpending.value = [];
    chartSurchargeSpending.value = [];
    chartWalletBalance.value = [];

    dashboardChart.value.forEach(element => {
        chartLabel.value.push(element.chart_label || "Unknown");
        chartMotorcycle.value += Number(element.motorcycle_count) || 0;
        chartMotorVehicle.value += Number(element.motor_vehicle_count) || 0;
        chartVan.value += Number(element.van_count) || 0;
        chartHeavyTruck.value += Number(element.heavy_truck_count) || 0;
        chartTruck.value += Number(element.truck_count) || 0;
        chartTotalSpending.value.push(Number(element.total_spending) || 0);
        chartNetSpending.value.push(Number(element.net_spending) || 0);
        chartTotalCustomerSpending.value.push(Number(element.total_customer_spending) || 0);
        chartTotalSubsidySpending.value.push(Number(element.total_subsidy_spending) || 0);
        chartBaseDeliveryCost.value.push(Number(element.base_delivery_cost) || 0);
        chartPriorityFeeSpending.value.push(Number(element.priority_fee_spending) || 0);
        chartSurchargeSpending.value.push(Number(element.surcharge_spending) || 0);
        chartWalletBalance.value.push(Number(element.wallet_balance) || 0);
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
const fetchSpendingDashboardData = async (startDate, endDate) => {
  try {

    bodyLoading.value = true;
    
    const response = await axios.get(
      wooLalamoveAdmin.root + 'woo-lalamove/v1/dashboard-spending-data/?from=' + startDate + '&to=' + endDate,
      {
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': wooLalamoveAdmin.api_nonce,
        },
      }
    );

    dashboardTable.value = response.data.table;

    totalSpending.value = response.data.kpi[0].total_spending || 0;
    netSpending.value = response.data.kpi[0].net_spending || 0;
    customerSpent.value = response.data.kpi[0].total_customer_spending || 0;
    baseDeliveryCost.value = response.data.kpi[0].base_delivery_cost || 0;
    shippingSubsidy.value = response.data.kpi[0].total_subsidy_spending || 0;
    priorityFee.value = response.data.kpi[0].priority_fee_spending || 0;

   dashboardChart.value = response.data.chart_data;

    dashboardTable.value       = response.data.table || [];
    noData.value = dashboardTable.value.length === 0;

    bodyLoading.value = false;

    message.value = noData.value ? "No results found" : ""; 

  } catch (error) {
    console.error('Error fetching Woo Lalamove Orders:', error.response?.data || error.message);
  } finally {
    loading.value = false;
  }
};

onMounted(() =>{
    const start = moment().subtract(30, 'days').format('YYYY-MM-DD');
    const end = moment().format('YYYY-MM-DD');
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


    .spending-body {
        display: grid;
        grid-template-columns: 1fr 1fr;
        grid-template-rows:  auto auto auto;
        grid-template-areas: 
            "key-performance-indicator key-performance-indicator"
            "first-section first-section"
            "WalletBalanceTrend ShippingSpendingTrend"
            "SubsidyVsCustomerPaid AdditionalFeeFrequency";
        width: 100%;
        height: 100%;
        gap: 1rem;
        margin-top: 1rem;
    }

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

    .key-performance-indicator {
        grid-area: key-performance-indicator;
    }

    .first-section {
        grid-area: first-section;
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: flex-start;
        gap: 1rem;

        & > :first-child {
            flex: 7;
        }

        & > :nth-child(2) {
            flex: 3;
            height: 300px;
        }
    }

    .WalletBalanceTrend {
        grid-area: WalletBalanceTrend;
    }

    .ShippingSpendingTrend {
        grid-area: ShippingSpendingTrend;
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
    border: 2px solid $border-color;
}

.table-wrapper{
    border: 2px solid $border-color;
    width: 100%;
    border-radius: 5px;
    background-color: $bg-high-light;
    max-height: 300px;
    overflow: scroll;


    .woo-order-table {
        width: 100%;
        border-collapse: collapse;

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

.loading-container {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 80vh;
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