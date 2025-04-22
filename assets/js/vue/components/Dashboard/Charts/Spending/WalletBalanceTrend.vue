<template>
    <div>
        <canvas id="walletBalanceChart"></canvas>
    </div>
  </template>
  
  <script setup>
  import { onMounted, ref, onBeforeUnmount } from "vue";
  import Chart from "chart.js/auto";
  
  const props = defineProps({
    chartLabel: { type: Array, required: true },
    chartWalletBalance: { type: Array, required: true },
  });
  
  const chartInstance = ref(null);
  const hasInitialized = ref(false);
  
  const initializeChart = () => {
    
    if (hasInitialized.value || props.chartLabel.length === 0) return;
    
    const rawData = { 
        chartLabel: [...props.chartLabel],
        chartWalletBalance: [...props.chartWalletBalance],
    };
  
    console.log('Chart Data:', rawData);
  
    const ctx = document.getElementById("walletBalanceChart").getContext("2d");
    chartInstance.value = new Chart(ctx, {
        type: "line",
        data: {
            labels: rawData.chartLabel,
            datasets: [
                {
                    label: "Wallet Balance Trend",
                    data: rawData.chartWalletBalance,
                    backgroundColor: "#20B2AA", 
                    borderColor: "#20B2AA", 
  
                },
            ],
        },
        options: { 
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: "Wallet Balance Trend",
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