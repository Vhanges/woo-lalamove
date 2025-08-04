import { defineStore } from "pinia";
import { ref, reactive, computed, watch } from "vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";

export const useWooOrderStore = defineStore("wooOrders", () => {
    // ====================
    // State Definitions
    // ====================
    const selectedRows = reactive([])

    const ordersCount = computed(() => selectedRows.length);



    return {
        // States
        selectedRows,
        // Getters 
        ordersCount
    }
  
});