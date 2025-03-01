<script setup>
import { provide, ref, reactive } from "vue";
import StepOne from "./Steps/StepOne.vue";
import StepTwo from "./Steps/StepTwo.vue";
import StepOneActions from "./StepsActions/StepOneActions.vue";
import StepTwoActions from "./StepsActions/StepTwoActions.vue";

const step = ref(1);

const nextStep = () => {
  step.value += 1;
};

const previousStep = () => {
  step.value -= 1;
};

const scheduleAt = ref("2022-04-01T14:30:00.00Z");
const serviceType = ref("MOTORCYCLE");
const specialRequests = ref(["CASH_ON_DELIVERY"]);
const stops = ref([
  {
  //   coordinates: {
  //     lat: "14.555566",
  //     lng: "121.130056"
  //   },
  //   address: "GFT Textile Main Office, Unit E, Sitio Malabon, Barangay San Juan, Hwy 2000, Taytay, 1920 Rizal"
  // },
  // {
  //   coordinates: {
  //     lat: "14.557909",
  //     lng: "121.137259"
  //   },
  //   address: "134 Cabrera Rd, Taytay, 1920 Rizal, Philippines"
  }
]);
const isRouteOptimized = ref(false);
const item = ref({
  quantity: "12",
  weight: "LESS_THAN_3_KG",
  categories: ["FOOD_DELIVERY", "OFFICE_ITEM"],
  handlingInstructions: ["KEEP_UPRIGHT"]
});

let body = {
    "data": {
       // "scheduleAt": "2022-04-01T14:30:00.00Z", // optional
        "serviceType": "MOTORCYCLE",
        "specialRequests": ["CASH_ON_DELIVERY"], // optional
        "language": "en_PH",
        "stops": [
           {
               "coordinates": {
                    "lat": "14.555566",
                    "lng": "121.130056"
                },
                "address": "GFT Textile Main Office, Unit E, Sitio Malabon, Barangay San Juan, Hwy 2000, Taytay, 1920 Rizal"
           },
           {
               "coordinates": {
                    "lat": "14.557909",
                    "lng": "121.137259"
                },
                "address": "134 Cabrera Rd, Taytay, 1920 Rizal, Philippines"
           }
        ],
        "isRouteOptimized": false, // optional only for quotations
        "item":{
              "quantity": "12",
              "weight": "LESS_THAN_3_KG",
              "categories": [
                 "FOOD_DELIVERY",
                 "OFFICE_ITEM"
              ],
              "handlingInstructions": [
                 "KEEP_UPRIGHT"
              ]
       },
    }
};


provide("nextStep", nextStep);
provide("previousStep", previousStep);
</script>

<template>
  <div class="forms-wrapper">
    <StepOne v-if="step === 1" />
    <StepTwo v-if="step === 2" />


    
    <!-- <div class="actions">
      <StepOneActions v-if="step === 1"/>
      <StepTwoActions v-if="step === 2"/>
    </div> -->
  </div>
</template>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;

.forms-wrapper {
  display: flex;
  position: relative;
  overflow-y: auto;
  height: 100%;
  background-color: $bg-light;
}

.forms-wrapper::-webkit-scrollbar {
  display: none;
}

.actions {
  position: absolute;
  height: auto;
  width: 100%;
  bottom: 0;
  left: 0;
  display: flex;
  justify-content: flex-end;
  box-sizing: border-box;
  padding: 1rem;
}
</style>
