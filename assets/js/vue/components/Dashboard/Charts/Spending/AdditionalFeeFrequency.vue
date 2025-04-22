<template>
    <div>
        <canvas id="additionalFeeFrequencyChart"></canvas>
    </div>
</template>

<script setup>
import { onMounted, ref, onBeforeUnmount } from "vue";
import Chart from "chart.js/auto";

const props = defineProps({
    chartLabel: { type: Array, required: true },
    chartSurchargeSpending: { type: Array, required: true },
    chartPriorityFeeSpending: { type: Array, required: true },
});

const chartInstance = ref(null);
const hasInitialized = ref(false);

const initializeChart = () => {
    
    if (hasInitialized.value || props.chartLabel.length === 0) return;
    
    const rawData = { 
        chartLabel: [...props.chartLabel],
        chartSurchargeSpending: [...props.chartSurchargeSpending],
        chartPriorityFeeSpending: [...props.chartPriorityFeeSpending],
    };

    console.log('Chart Data:', rawData);

    const ctx = document.getElementById("additionalFeeFrequencyChart").getContext("2d");
    chartInstance.value = new Chart(ctx, {
        type: "bar",
        data: {
            labels: rawData.chartLabel,
            datasets: [
                {
                    label: "Surcharge Fees",
                    data: rawData.chartSurchargeSpending,
                    backgroundColor: "#FF6347", 
                },
                {
                    label: "Priority Fees",
                    data: rawData.chartPriorityFeeSpending,
                    backgroundColor: "#FFD700", 
                },
            ],
        },
        options: { 
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: "Surcharge and Priority Fees Frequency",
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