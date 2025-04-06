<template>
    <div class="table-wrapper">
        <table class="woo-order-table">
            <thead>
                <tr>
                    <th>Woo ID</th>
                    <th>Order Date</th>
                    <th>Schedule Date</th>
                    <th>Drop Off Location</th>
                    <th>Contacts</th>
                    <th>Quantity</th>
                    <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="order in orders" :key="order.id">
         
                <td>  <input type="checkbox" name="wc_order_id" id="" value="{{ order.wc_order_id }}">{{ order.wc_order_id }}</td>
                <td>{{ order.ordered_on }}</td>
                <td>{{ order.schedule_on_on }}</td>
                <td>{{ order.drop_off_location }}</td>
                <td>{{ order.contacts }}</td>
                <td>{{ order.quantity }}</td>
                <td>{{ order.status }}</td>
            </tr>
        </tbody>
    </table>
</div>
</template>

<script setup>
import { ref } from 'vue';

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
   }
}
</style>