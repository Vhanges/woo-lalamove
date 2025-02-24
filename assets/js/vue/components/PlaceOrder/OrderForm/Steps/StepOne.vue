<script setup>
import { ref, computed, onMounted, useAttrs } from 'vue';
import { Sortable } from 'sortablejs-vue3';
import axios from 'axios';
import { eventBus } from '../../../../../utils/eventBus.js';
import AddressInput from './StepOneContent/AddressInput.vue';
import VehicleSelection from './StepOneContent/VehicleSelection.vue';
import AdditionalServices from './StepOneContent/AdditionalServices.vue';


// Define the attributes and reactive properties
const attrs = useAttrs();
const services = ref([]);
const addresses = ref([
  { id: 'pickup', placeholder: 'Add Pick up address', value: '' },
  { id: 'drop-off', placeholder: 'Add Drop off address', value: '' },
]);
const locations = ref([]);
const selectedLocode = ref('');

const addServices = ref([]);

// Sortable JS Configuration
const sortableOptions = {
  animation: 100,
  ghostClass: 'text-container-bg'
};

// Compute the currently selected location based on the selectedLocode
const selectedLocation = computed(() => {
  return locations.value.find((loc) => loc.locode === selectedLocode.value);
});

// Fetch locations from the API
const fetchLocations = async () => {
  try {
    const response = await axios.get(
      `${wpApiSettings.root}woo-lalamove/v1/get-city`,
      { headers: { 'X-WP-Nonce': wpApiSettings.nonce } }
    );
    locations.value = response.data;
    // Set a default selection if available
    if (locations.value.length > 0) {
      selectedLocode.value = locations.value[0].locode;
    }
  } catch (error) {
    console.error('Error fetching locations:', error);
  }
};

// Fetch services from the event bus
const fetchServices = (service) => {
  services.value = service;
};

const fetchAddServices = (specialRequests) => {
  // Group the specialRequests by parent_type
  const groupedSpecialRequests = specialRequests.reduce((acc, request) => {
    if (request.parent_type) {
      // If the parent_type group does not exist in the accumulator, create it
      if (!acc.withParentType[request.parent_type]) {
        acc.withParentType[request.parent_type] = [];
      }
      // Add the current request to the appropriate group
      acc.withParentType[request.parent_type].push(request);
    } else {
      // Add to the group without parent_type
      acc.withoutParentType.push(request);
    }
    return acc;
  }, { withParentType: {}, withoutParentType: [] });

  // Assign the grouped result to addServices.value
  addServices.value = groupedSpecialRequests;

  // Log the grouped special requests
  console.log('Grouped Special Requests:', groupedSpecialRequests);
};

onMounted(() => {
  //fetchLocations();
  eventBus.on('market-services', fetchServices);
});
</script>


<template>
  <div class="wrapper">
    <div class="address-wrapper">
      <p class="header">ROUTE (Max. 20 STOPS)</p>
      <form class="address-content">
        <!-- Working addresses example -->
        <Sortable
          :list="addresses"
          item-key="id"
          handle=".drag-handle"
          tag="div"
          class="drag-area"
          :options="sortableOptions"
          dragClass="dragging"
        >
          <template #item="{ element }">
            <AddressInput
              type="text"
              :id="element.id"q
              class="text-box"
              :placeholder="element.placeholder"
              v-model="element.value"
            />
          </template>
        </Sortable>

        <button class="add-stop">
          <span class="material-symbols-outlined">add</span>
          Add Stop
        </button>
      </form>
    </div>

    <div class="vehicle-wrapper" >
      <p class="header">VEHICLE TYPE</p>
      <div class="vehicle-content" v-dragscroll:nochilddrag>
        <VehicleSelection
          v-for="service in services"
          :name="service.key"
          :description="service.description"
          :dimensions="service.dimensions"
          :load="service.load"
          data-dragscroll
          @click = fetchAddServices(service.specialRequests)
        />
      </div>
    </div>

    <div class="additional-service">
      <p class="header">ADDITIONAL SERVICES</p>
        <div class="additional-service-content">
            <AdditionalServices 
              :withParentType="addServices.withParentType"
              :withoutParentType="addServices.withoutParentType"
            />
        </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
@use '@/css/scss/_variables.scss' as *;

* {
  box-sizing: border-box;
}
.wrapper {
  display: flex !important;
  flex-direction: column;
  justify-content: flex-start;
  align-items: flex-start;
  gap: 5%;
  padding: 3%;
  height: 100%;
  width: 100%;
  margin-bottom: 100px;


  .address-wrapper {
    height: fit-content;
    width: 100%;

    .address-content {
      height: fit-content;
      width: 100%;
      border: 2px solid $border-color;
      border-radius: 5px;
      padding: 0 3%;

      .add-stop {
        border: none;
        outline: none;
        background-color: inherit;
        height: 3rem;
        font-size: $font-size-xs;
        color: $header-active;
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-direction: row;

        &:hover {
          cursor: pointer;
          outline: none;
          border: none;
          border-style: none;
        }

        &:focus {
          outline: none;
          border: none;
          border-style: none;
        }

        &::nth-child(*) {
          color: $header-active;
        }
      }

      .add-stop > .material-symbols-outlined {
        height: auto;
        font-size: $font-size-sm;
      }
    }
  }
//Vehicle Selection Style
  .vehicle-wrapper {
    display: flex !important;
    flex-direction: column;
    height: fit-content;
    width: 100%;
    padding: 0;

    .vehicle-content {
      display: flex;
      flex-direction: row;
      height: fit-content;
      overflow-x: auto;
      width: 100%;
      gap: 1rem;
      user-select: none;
      -webkit-user-select: none;
      -moz-user-select: none;
      -ms-user-select: none;
    }
  }

  .vehicle-content::-webkit-scrollbar {
    display: none;
  }
  //Additional Service Style
  .additional-service{
    width: 100%;
  }
}




  .simple-list {
  list-style-type: none;
  padding: 0;
}

.header {
  font-size: $font-size-sm;
  font-weight: 700;
  color: $header;
}
</style>
