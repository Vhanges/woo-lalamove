<template>
    <div>
        <canvas id="shipmentChart"></canvas>
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import Chart from "chart.js/auto";

onMounted(() => {
    const ctx = document.getElementById("shipmentChart").getContext("2d");
    new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["Dec", "Jan", "Feb", "Mar", "Apr"],
            datasets: [
                {
                    label: "Subsidy Paid",
                    data: [500, 700, 800, 600, 750],
                    backgroundColor: "#6CA0D9", 
                },
                {
                    label: "Customer Paid",
                    data: [300, 400, 450, 350, 500],
                    backgroundColor: "#94C66C", 
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: "Subsidy vs Customer Paid Shipment Analysis (Dec - Apr)",
                },
            },
        },
    });
});
</script>

<style lang="scss" scoped>
/* Add any custom styles for your chart container here */
</style>