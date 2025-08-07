import { defineStore } from "pinia";
import { ref, reactive, computed, watch } from "vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export const useWooOrderStore = defineStore("wooOrders", () => {
  const selectedRows = reactive([])

  const ordersCount = computed(() => selectedRows.length)

  // Turn this into a method
  function clearSelectedRows() {
    // Remove all items without reassigning
    selectedRows.splice(0, selectedRows.length)
    console.log("CLEARR", selectedRows)
  }

  return {
    selectedRows,
    ordersCount,
    clearSelectedRows
  }
})
