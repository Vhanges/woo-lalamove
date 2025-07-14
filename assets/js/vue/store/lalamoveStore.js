import { defineStore } from "pinia";
import {ref, computed} from "vue";
import axios from "axios";

export const useLalamoveStore = defineStore("lalamove", () => {
    const body = ref([])
    const locode = ref([])
    const quotation = ref([]); 
    const services = ref([]);

    async function fetchCity() {
        try {
            const apiRoot = window?.wpApiSettings?.root || ''
            const apiNonce = window?.wpApiSettings?.nonce || ''

            const response = await axios.get(`${apiRoot}woo-lalamove/v1/get-city`, {
            headers: { 'X-WP-Nonce': apiNonce }
            })

            const city = response.data;

            const [cebu, ncr_south, north_central] = [
                city.find(c => c.locode === 'PH CEB'),
                city.find(c => c.locode === 'PH MNL'),
                city.find(c => c.locode === 'PH PAM'),
            ]

            // Sort them in ascending manner
            services.value = (ncr_south?.services || []).slice().sort((a, b) => {
                return Number(a.load?.value) - Number(b.load?.value);
            });

            console.log("success");
        } catch (error) {
            console.error('Error fetching locations:', error);
        }
    }

    

    return {
        //States 
        body,
        quotation,
        services,
        //Actions
        fetchCity,

    };

});

