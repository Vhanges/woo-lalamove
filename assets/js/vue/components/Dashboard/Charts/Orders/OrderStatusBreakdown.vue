    <template>
        <div>
            <canvas id="orderStatusTrend"></canvas>
        </div>
    </template>

    <script setup>
    import { onMounted, ref, onBeforeUnmount } from "vue";
    import Chart from "chart.js/auto";

    const props = defineProps({
        label: { type: Array, required: true },
        completed: { type: Array, required: true },
        active: { type: Array, required: true },
        pending: { type: Array, required: true },
        failed: { type: Array, required: true },
        rejected: { type: Array, required: true },
    });

    const chartInstance = ref(null);
    const hasInitialized = ref(false);

    const initializeChart = () => {
        
        if (hasInitialized.value || props.label.length === 0) return;
        
        const rawData = {
            label: [...props.label],
            completed: [...props.completed],
            pending: [...props.pending],
            failed: [...props.failed],
            rejected: [...props.rejected]
        };

        console.log('Chart Data:', rawData);

        const ctx = document.getElementById("orderStatusTrend").getContext("2d");
        chartInstance.value = new Chart(ctx, {
            type: "bar",
            data: {
                labels: rawData.label,
                datasets: [
                    {
                        label: "Completed Orders",
                        data: rawData.completed,
                        backgroundColor: "#20B2AA", 
                    },
                    {
                        label: "Pending Orders",
                        data: rawData.pending,
                        backgroundColor: "#FFA07A", 
                    },
                    {
                        label: "Failed Orders",
                        data: rawData.failed,
                        backgroundColor: "#FF6347", 
                    },
                    {
                        label: "Rejected Orders",
                        data: rawData.rejected,
                        backgroundColor: "#FF6347", 
                    },
                ],
            },
            options: { 
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: "Order Status Trend",
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