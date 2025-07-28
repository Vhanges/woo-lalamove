import { defineStore } from "pinia";
import { ref, reactive, computed, watch } from "vue";
import axios from "axios";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { data } from "autoprefixer";

export const useWooOrderStore = 