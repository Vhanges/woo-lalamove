<template>
  <div class="chart-container">
    <canvas id="doughnutChart"></canvas>
  </div>
</template>
  
<script setup>
import { onMounted } from "vue";
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);

onMounted(() => {
  const ctx = document.getElementById("doughnutChart").getContext("2d");

  new Chart(ctx, {
    type: "doughnut",
    data: {
      labels: ["Gold", "Light Salmon", "Light Sea Green", "Medium Purple", "Tomato"],
      datasets: [
        {
          data: [20, 20, 20, 20, 20],
          backgroundColor: [
            "#FFD700", // Gold
            "#FFA07A", // Light Salmon
            "#20B2AA", // Light Sea Green
            "#9370DB", // Medium Purple
            "#FF6347", // Tomato
          ],
          borderWidth: 1,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false, 
      plugins: {
        legend: {
          position: "right",
          labels: {
            display: true,  
            position: "right",
            font: {
              size: 12,
              family: "Noto Sans, sans-serif",
            },
            color: "#0D0D0D",
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
        datalabels: {
          font: {
            size: 14,
            family: "Noto Sans, sans-serif",
            weight: "bold",
          },
          color: "#FCFCFC",
          formatter: function (value, context) {
            const sum = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
            const percentage = ((value / sum) * 100).toFixed(2) + "%";
            return percentage;
          },
        },
      },
    },

  });
});
</script>
  
<style lang="scss" scoped>
.chart-container {
  width: 100%;
  height: 250px;
  
}

</style>