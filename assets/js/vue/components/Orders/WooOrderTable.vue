<template>
  <!-- Skeleton Loader -->
  <div v-if="isLoading" class="skeleton-table">
    <div class="skeleton-header">
      <div
        v-for="(_, index) in 7"
        :key="`skeleton-header-${index}`"
        class="skeleton-cell"
      ></div>
    </div>
    <div class="skeleton-body">
      <div v-for="i in 5" :key="`skeleton-row-${i}`" class="skeleton-row">
        <div
          v-for="(_, index) in 7"
          :key="`skeleton-cell-${index}`"
          class="skeleton-cell"
        ></div>
      </div>
    </div>
  </div>

  <div v-else class="table-wrapper">
    <!-- Timeout Message -->
    <div v-if="isTimeOut" class="timeout-message">
      <h3>Server Timeout</h3>
      <p>Please try refreshing the page or check your connection</p>
      <button @click="$emit('retry')">Retry</button>
    </div>

    <!-- Actual Table Content -->
    <table v-else class="woo-order-table">
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
        <!-- Empty State -->
        <tr v-if="!orders.length">
          <td colspan="7" style="text-align: center; padding: 2rem">
            No orders found
          </td>
        </tr>

        <!-- Data Rows -->
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
  if (ordersCount.value >= 15) {
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
    toast.error("An error occured on our side. Please try again", {
      autoClose: 2000,
    });
    return;
  }

  if (isSelected(id)) {
    selectedRows.value.splice(idx, 1);
    return;
  }

  if (order.status_name === "Processed") {
    toast.error("This order is already processed", { autoClose: 4000 });
    return;
  }

  selectedRows.value.push({
    wooID: id,
    stopId: 0,
    name: order.ordered_by.replace(/\s*\(.*\)$/, ""),
    phone: order.customer_phone,
    remarks: order.remarks ?? "none",
    stops: stops[stops.length - 1] || null,
    item: item || null,
  });
}

defineProps({
  orders: {
    type: Array,
    default: () => [],
  },
  isTimeOut: {
    type: Boolean,
    default: false,
  },
  isLoading: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["retry"]);
</script>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;

/* Skeleton Loader Styling */
.skeleton-table {

  .skeleton-header {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1rem;
    padding: 1rem;
    background: $bg-gray;
    border-radius: 4px 4px 0 0;

    .skeleton-cell {
      height: 20px;
      background: rgba(0, 0, 0, 0.1);
      border-radius: 4px;
    }
  }

  .skeleton-body {
    padding: 0.5rem 1rem;
  }

  .skeleton-row {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 1rem;
    padding: 1rem 0;
    position: relative;
    overflow: hidden;

    &::after {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(
        90deg,
        transparent 25%,
        rgba(255, 255, 255, 0.2) 50%,
        transparent 75%
      );
      animation: shimmer 1.5s infinite;
    }
  }

  .skeleton-cell {
    height: 20px;
    background: $bg-gray;
    border-radius: 4px;
    position: relative;
    z-index: 1;
  }

  @keyframes shimmer {
    0% {
      transform: translateX(-100%);
    }
    100% {
      transform: translateX(100%);
    }
  }
}

.table-wrapper {
  position: relative;
  border: 1px solid $border-color;
  width: 100%;
  border-radius: 5px;
  background-color: $bg-high-light;

  /* Timeout Message Styling */
  .timeout-message {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    background: rgba(255, 255, 255, 0.9);
    z-index: 10;
    text-align: center;
    padding: 2rem;
    color: #ff6b6b;
    border-radius: 5px;

    h3 {
      font-size: 1.5rem;
      margin-bottom: 0.5rem;
    }

    button {
      margin-top: 1rem;
      padding: 0.5rem 1rem;
      background: #ff6b6b;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;

      &:hover {
        background: #ff5252;
      }
    }
  }


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
