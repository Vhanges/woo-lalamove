<template>
    <div class="chart-container">
        <canvas id="ShippingSpendingTrendChart"></canvas>
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);

onMounted(() => {
  const ctx = document.getElementById("ShippingSpendingTrendChart").getContext("2d");

  new Chart(ctx, {
    type: "line",
    data: {
      labels: ["Dec", "Jan", "Feb", "Mar", "Apr"],
      datasets: [
        {
          label: "Active Orders",
          data: [30, 45, 40, 35, 50],
          borderColor: "#5578B1", // Muted blue
          fill: false,
        }
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          display: true,
          position: "top",
          labels: {
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
              return `${dataset.label}: ${currentValue}`;
            },
          },
        },
      },
      scales: {
        y: {
          beginAtZero: true,
        },
      },
    },
  });
});
</script>


<style lang="scss" scoped>
.chart-container {
    width: 1fr;
    height: auto;
}
</style>
