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
        <tr
          v-for="order in orders"
          :key="order.wc_order_id"
          @click.stop="toggleRowSelection(order)"
          :class="{ 'selected-row': isSelected(order.wc_order_id) }"
          :disabled="isSelected(order.wc_order_id)"
        >
          <td style="vertical-align: center">
            <input
              type="checkbox"
              name="wc_order_id"
              :value="order.wc_order_id"
              :checked="isSelected(order.wc_order_id)"
            />
            <span style="height: 16px">{{ order.wc_order_id }}</span>
          </td>
          <td v-html="order.ordered_on"></td>
          <td v-html="order.scheduled_on"></td>
          <td>{{ order.drop_off_location }}</td>
          <td
            v-if="
              order.ordered_by || order.customer_phone || order.customer_email
            "
          >
            <span v-if="order.ordered_by">{{ order.ordered_by }}</span>
            <br
              v-if="
                order.ordered_by &&
                (order.customer_phone || order.customer_email)
              "
            />
            <span v-if="order.customer_phone">{{ order.customer_phone }}</span>
            <br v-if="order.customer_phone && order.customer_email" />
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
import { ref } from "vue";
import { toast, ToastifyContainer } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { useWooOrderStore } from "../../store/wooOrderStore";
import { storeToRefs } from "pinia";

const wooOrder = useWooOrderStore();
const { selectedRows, ordersCount } = storeToRefs(wooOrder);

function isSelected(id) {
  return selectedRows.value.some((item) => item.wooID === id);
}

function toggleRowSelection(order) {

  if(ordersCount.value >= 15){
    toast.error("Maximum number of stops reached", { autoClose: 7000 });
    return;
  }

  const id = order.wc_order_id;
  const idx = selectedRows.value.findIndex((item) => item.wooID === id);

  let stops;
  let item;
  try {
    const parsed = JSON.parse(order.order_json_body);
    stops = parsed?.data?.stops || [];
    item = parsed?.data?.item || [];
  } catch (error) {
    toast.error("An error occured on our side. Please try again", { autoClose: 2000 });
    return;
  }

  // Already selected → remove from array
  if (isSelected(id)) {
    selectedRows.value.splice(idx, 1);
    return;
  }

  // Prevent selection if already processed
  if (order.status_name === "Processed") {
    toast.error("This order is already processed", { autoClose: 4000 });
    return;
  }

  // Add to selection
  selectedRows.value.push({
    wooID: id,
    stopId: 0,
    name: order.ordered_by.replace(/\s*\(.*\)$/, ''),
    phone: order.customer_phone,
    remarks: order.remarks ?? "none",
    stops: stops[stops.length - 1] || null,
    item: item || null
  });

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

.table-wrapper {
  border: 1px solid $border-color;
  width: 100%;
  border-radius: 5px;
  background-color: $bg-high-light;

  .woo-order-table {
    width: 100%;
    border-collapse: collapse;

    th,
    td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid $bg-gray;
    }

    th {
      background-color: $bg-gray;
      font-weight: bold;
    }

    tr:hover {
      background-color: $bg-primary-light;
      box-shadow: inset 2px 0 0 0 $bg-primary;
    }

    .selected-row {
      background-color: $bg-primary-light;
      box-shadow: inset 2px 0 0 0 $bg-primary;
    }
  }
}
</style>
