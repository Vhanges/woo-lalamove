<template>
    <div class="table-wrapper">
        <table class="woo-order-table">
            <thead>
                <tr>
                    <th>Woo ID</th>
                    <th>Order Date</th>
                    <th>Schedule Date</th>
                    <th>Drop Off Location</th>
                    <th>Contact</th>
                    <th>Quantity</th>
                    <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="order in orders" :key="order.wc_order_id" 
            @click="toggleRowSelection(order)"
            :class="{ 'selected-row': selectedRows.includes(order.wc_order_id) }">
         
                <td style="vertical-align: center;">
                    <input 
                        type="checkbox" 
                        name="wc_order_id"
                        :value="order.wc_order_id"
                          :disabled="!selectedRows.includes(order.wc_order_id)" 
                            v-model="selectedRows"
                    >
                    <span style="height: 16px">{{ order.wc_order_id }}</span>
                </td>
                <td v-html=" order.ordered_on"></td>
                <td v-html=" order.scheduled_on"></td>
                <td>{{ order.drop_off_location }}</td>
                <td v-if="order.ordered_by || order.customer_phone || order.customer_email">
                    <span v-if="order.ordered_by">{{ order.ordered_by }}</span>
                    <br v-if="order.ordered_by && (order.customer_phone || order.customer_email)">
                    <span v-if="order.customer_phone">{{ order.customer_phone }}</span>
                    <br v-if="order.customer_phone && order.customer_email">
                    <span v-if="order.customer_email">{{ order.customer_email }}</span>
                </td>
                <td>{{ order.quantity }}</td>
                <td>{{ order.status_name }}</td>
            </tr>
        </tbody>
    </table>    
</div>
</template>

<script setup>
import { ref } from 'vue';
import { toast, ToastifyContainer } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const selectedRows = ref([]);

function toggleRowSelection(order) {

    if(order.status_name === 'Processed'){  
        toast.error('This is order is already processed', { autoClose: 2000 });

        return;
    }

    const index = selectedRows.value.indexOf(order.wc_order_id);
    if (index === -1) {
        selectedRows.value.push(order.wc_order_id);
    } else {
        selectedRows.value.splice(index, 1);
    }
}


defineProps({
    orders: {
        type: Array,
        default: () => [],
    },
});




</script>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;

.table-wrapper{
    border: 2px solid $border-color;
    width: 100%;
    border-radius: 5px;
    background-color: $bg-high-light;


    .woo-order-table {
        width: 100%;
        border-collapse: collapse;
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid $bg-gray;
        }
        
        th {
            background-color:  $bg-gray;
            font-weight: bold;
        }
        

        tr:hover {
            background-color: $bg-primary-light;
            box-shadow: inset 2px 0 0 0 $bg-primary;
        }

        .selected-row{
            background-color: $bg-primary-light;
            box-shadow: inset 2px 0 0 0 $bg-primary;
        }
   }
}
</style>    