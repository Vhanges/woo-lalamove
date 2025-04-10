<template>
    <div>
        <canvas id="additionalChargesChart"></canvas>
    </div>
</template>

<script setup>
import { onMounted } from "vue";
import Chart from "chart.js/auto";

onMounted(() => {
    const ctx = document.getElementById("additionalChargesChart").getContext("2d");
    new Chart(ctx, {
        type: "bar",
        data: {
            labels: ["Dec", "Jan", "Feb", "Mar", "Apr"],
            datasets: [
                {
                    label: "Priority Fees",
                    data: [500, 700, 800, 600, 750],
                    backgroundColor: "#FFD700", 
                },
                {
                    label: "Surcharges",
                    data: [300, 400, 450, 350, 500],
                    backgroundColor: "#FF6347", 
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: "Priority Fees & Surcharges Frequency (Dec - Apr)",
                },
            },
        },
    });
});
</script>

<style lang="scss" scoped>
/* Add any custom styles for your chart container here */
</style>