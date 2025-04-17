<template>
    <div>
        <canvas id="orderFullfilmentBreakdown"></canvas>
    </div>
</template>

<script setup>
import { onMounted, ref, onBeforeUnmount } from "vue";
import Chart from "chart.js/auto";

const props = defineProps({
    label: { type: Array, required: true },
    successful: { type: Number, required: true },
    unsuccessful: { type: Number, required: true },
});

const chartInstance = ref(null);
const hasInitialized = ref(false);

const initializeChart = () => {
    
    if (hasInitialized.value || props.label.length === 0) return;
    
    const rawData = {
        label: [...props.label],
        successful: props.successful,
        unsuccessful: props.unsuccessful
    };

    const totalOrders = rawData.successful + rawData.unsuccessful;

    const successfulPercentage = ((rawData.successful / totalOrders) * 100).toFixed(2);
    const unsuccessfulPercentage = ((rawData.unsuccessful / totalOrders) * 100).toFixed(2);

    console.log('Successful Percentage:', successfulPercentage);
    console.log('Unsuccessful Percentage:', unsuccessfulPercentage);

    console.log('Chart Data:', rawData);

    const ctx = document.getElementById("orderFullfilmentBreakdown").getContext("2d");
    chartInstance.value = new Chart(ctx, {
        type: "pie",
        data: {
            labels: ["Successful", "Unsuccessful"],
            datasets: [
                {
                    data: [successfulPercentage, unsuccessfulPercentage],
                    backgroundColor: ["#20B2AA","#FF6347"],
                },
            ],
        },
        options: { 
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                },
                title: {
                    display: true,
                    text: "Order Status Trend",
                    font: {
                        size: 12,
                        family: "Noto Sans, sans-serif",
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
        }
    });

    hasInitialized.value = true;
};


onMounted(() => {
    if (props.label.length > 0) initializeChart();
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