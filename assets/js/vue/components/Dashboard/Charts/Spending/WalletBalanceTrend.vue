<template>
    <div class="chart-container">
        <canvas id="WalletBalanceTrendChart"></canvas>
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);

onMounted(() => {
    const ctx = document.getElementById("WalletBalanceTrendChart").getContext("2d");

    new Chart(ctx, {
        type: "line",
        data: {
            labels: ["Dec", "Jan", "Feb", "Mar", "Apr"],
            datasets: [
                {
                    label: "Wallet Balance Trend",
                    data: [150, 200, 250, 180, 220],
                    borderColor: "#6FAF75", // Muted green
                    fill: false,
                },
            ],
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
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

    #WalletBalanceTrendChart {
        width: 100%;
        height: 100%;
    }

}
</style>
