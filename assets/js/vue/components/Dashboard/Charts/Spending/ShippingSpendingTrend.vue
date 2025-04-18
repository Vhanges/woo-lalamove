<template>
  <div>
      <canvas id="shippingSpendingChart"></canvas>
  </div>
</template>

<script setup>
import { onMounted, ref, onBeforeUnmount } from "vue";
import Chart from "chart.js/auto";

const props = defineProps({
  chartLabel: { type: Array, required: true },
  chartTotalSpending: { type: Array, required: true },
  chartNetSpending: { type: Array, required: true },
});

const chartInstance = ref(null);
const hasInitialized = ref(false);

const initializeChart = () => {
  
  if (hasInitialized.value || props.chartLabel.length === 0) return;
  
  const rawData = { 
      chartLabel: [...props.chartLabel],
      chartTotalSpending: [...props.chartTotalSpending],
      chartNetSpending: [...props.chartNetSpending],
  };

  console.log('Chart Data:', rawData);

  const ctx = document.getElementById("shippingSpendingChart").getContext("2d");
  chartInstance.value = new Chart(ctx, {
      type: "line",
      data: {
          labels: rawData.chartLabel,
          datasets: [
              {
                  label: "Total Spending",
                  data: rawData.chartTotalSpending,
                  backgroundColor: "#20B2AA", 
                  borderColor: "#20B2AA", 

              },
              {
                  label: "Net Spending",
                  data: rawData.chartNetSpending,
                  backgroundColor: "#FFA07A",
                  borderColor: "#FFA07A", 
              },
          ],
      },
      options: { 
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
              title: {
                  display: true,
                  text: "Total and Net Spending Trend",
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