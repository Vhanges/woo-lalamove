<template>
    <div>
        <canvas id="subsidyVsCustomerPaidChart"></canvas>
    </div>
</template>

<script setup>
import { onMounted, ref, onBeforeUnmount } from "vue";
import Chart from "chart.js/auto";

const props = defineProps({
    chartLabel: { type: Array, required: true },
    chartTotalCustomerSpending: { type: Array, required: true },
    chartTotalSubsidySpending: { type: Array, required: true },
});

const chartInstance = ref(null);
const hasInitialized = ref(false);

const initializeChart = () => {
    
    if (hasInitialized.value || props.chartLabel.length === 0) return;
    
    const rawData = { 
        chartLabel: [...props.chartLabel],
        chartTotalCustomerSpending: [...props.chartTotalCustomerSpending],
        chartTotalSubsidySpending: [...props.chartTotalSubsidySpending],
    };

    console.log('Chart Data:', rawData);

    const ctx = document.getElementById("subsidyVsCustomerPaidChart").getContext("2d");
    chartInstance.value = new Chart(ctx, {
        type: "bar",
        data: {
            labels: rawData.chartLabel,
            datasets: [
                {
                    label: "Customer Spending",
                    data: rawData.chartTotalCustomerSpending,
                    backgroundColor: "#20B2AA", 
                },
                {
                    label: "Subsidy Spending",
                    data: rawData.chartTotalSubsidySpending,
                    backgroundColor: "#FFA07A", 
                },
            ],
        },
        options: { 
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: "Subsidy and Customer Shipment Spending",
                    font: {
                        size: 12,
                        family: "Noto Sans, sans-serif",
                    },
                },
            },
            scales: {
                y: {
                  beginAtZero: true,  
                },
            },
        }
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