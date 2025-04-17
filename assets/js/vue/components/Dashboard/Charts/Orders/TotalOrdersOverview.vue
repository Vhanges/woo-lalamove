<template>
    <div>
        <canvas id="totalOrdersChart"></canvas>
    </div>
</template>

<script setup>
import { onMounted, ref, watchEffect, onBeforeUnmount } from "vue";
import Chart from "chart.js/auto";

const props = defineProps({
    label: { type: Array, required: true },
    total: { type: Array, required: true },
});

const chartInstance = ref(null);
const hasInitialized = ref(false);


const initializeChart = () => {
    
    if (hasInitialized.value || props.label.length === 0) return;
    
    const rawData = {
        label: [...props.label],
        total: [...props.total],
    };

    console.log('Chart Data:', rawData);

    const ctx = document.getElementById("totalOrdersChart").getContext("2d");
    chartInstance.value = new Chart(ctx, {
        type: "line",
        data: {
            labels: rawData.label,
            datasets: [
                {
                    label: "Total Orders",
                    data: rawData.total,
                    backgroundColor: "#20B2AA", 
                },
            ],
        },
        options: { 
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: "Total Orders Overview",
                    font: {
                        size: 12,
                        family: "Noto Sans, sans-serif",
                    },
                },
            },
            scales  : {
                y: {
                    beginAtZero: true,
                }
            }
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