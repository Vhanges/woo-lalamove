<template>
  <div>
      <canvas id="ServiceBreakdownChart"></canvas>
  </div>
</template>

<script setup>
import { onMounted, ref, onBeforeUnmount } from "vue";
import Chart from "chart.js/auto";

const props = defineProps({
  chartLabel: {type: Array, required: true },
  chartMotorcycle: { type: Number, required: true },
  chartMotorcycleVehicle: { type: Number, required: true },
  chartVan: { type: Number, required: true },
  chartHeavyTruck: { type: Number, required: true },
  chartTruck:  { type: Number, required: true },
});

const chartInstance = ref(null);
const hasInitialized = ref(false);

const initializeChart = () => {
    if (hasInitialized.value || props.chartLabel.length === 0) return;

    const rawData = {
        chartLabel: props.chartLabel,
        chartMotorcycle: props.chartMotorcycle,
        chartMotorcycleVehicle: props.chartMotorcycleVehicle,
        chartVan: props.chartVan,
        chartHeavyTruck: props.chartHeavyTruck, // Fixed typo
        chartTruck: props.chartTruck,
    };

    const totalServiceUsage =
        rawData.chartMotorcycle +
        rawData.chartMotorcycleVehicle +
        rawData.chartVan +
        rawData.chartHeavyTruck +
        rawData.chartTruck;

    if (totalServiceUsage === 0) {
        console.error("Total service usage is zero. Cannot calculate percentages.");
        return;
    }

    const motorcyclePercentage = ((rawData.chartMotorcycle / totalServiceUsage) * 100).toFixed(2);
    const motorcycleVehiclePercentage = ((rawData.chartMotorcycleVehicle / totalServiceUsage) * 100).toFixed(2);
    const vanPercentage = ((rawData.chartVan / totalServiceUsage) * 100).toFixed(2);
    const heavyTruckPercentage = ((rawData.chartHeavyTruck / totalServiceUsage) * 100).toFixed(2);
    const truckPercentage = ((rawData.chartTruck / totalServiceUsage) * 100).toFixed(2);

    console.log(rawData);

    const ctx = document.getElementById("ServiceBreakdownChart")?.getContext("2d");
    if (!ctx) {
        console.error("Canvas context not found!");
        return;
    }

    chartInstance.value = new Chart(ctx, {
        type: "pie",
        data: {
            labels: ["Motorcycle", "Motorcycle Vehicle", "Van", "Heavy Truck", "Truck"],
            datasets: [
                {
                    data: [
                        motorcyclePercentage,
                        motorcycleVehiclePercentage,
                        vanPercentage,
                        heavyTruckPercentage,
                        truckPercentage,
                    ],
                    backgroundColor: [
                        "#FFD700", 
                        "#FFA07A", 
                        "#20B2AA", 
                        "#9370DB", 
                        "#FF6347", 
                    ],
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "right",
                },
                title: {
                    display: true,
                    text: "Order Status Trend",
                    font: {
                        size: 12,
                        family: "Arial, sans-serif", 
                    },
                },
                tooltip: {
                    enabled: true,
                    callbacks: {
                        label: function (tooltipItem) {
                            const dataset = tooltipItem.dataset;
                            const currentValue = dataset.data[tooltipItem.dataIndex];
                            const label = tooltipItem.label;
                            return `${label}: ${currentValue}%`;
                        },
                    },
                },
            },
        },
    });

    hasInitialized.value = true;
};


onMounted(() => {
  if (props.chartLabel.length > 0) initializeChart();
});

// Cleanup
onBeforeUnmount(() => {
  if (chartInstance.value) {
      chartInstance.value.destroy();
  }
});
</script>

<style lang="scss" scoped>
</style>