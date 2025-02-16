<template>
  <div class="wrapper">
    <div class="address-wrapper">
      <form>
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
            <div class="text-container">
              <span class="drag-handle">::</span>
              <input
                type="text"
                :id="element.id"
                class="text-box dragging"
                :placeholder="element.placeholder"
                v-model="element.value"
              />
            </div>
          </template>
        </Sortable>
      </form>
    </div>

    

    <!-- <div>
    <label for="locodeSelect">Select Location:</label>
    <select id="locodeSelect" v-model="selectedLocode">
      <option 
        v-for="loc in locations" 
        :key="loc.locode" 
        :value="loc.locode">
        {{ loc.locode }} - {{ loc.name }}
      </option>
    </select>

    <div v-if="selectedLocation">
      <h3>Details for {{ selectedLocation.name }}</h3>
      <ul>
        <li v-for="service in selectedLocation.services" :key="service.key">
          <strong>{{ service.description }}</strong>
          <div>
            Dimensions: 
            {{ service.dimensions.length.value }} {{ service.dimensions.length.unit }}, 
            {{ service.dimensions.width.value }} {{ service.dimensions.width.unit }}, 
            {{ service.dimensions.height.value }} {{ service.dimensions.height.unit }}
          </div>
          <div>Load: {{ service.load.value }} {{ service.load.unit }}</div>
        </li>
      </ul>
    </div>
  </div> -->

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Sortable } from 'sortablejs-vue3';
import axios from 'axios';

const addresses = ref([
  { id: 'pickup', placeholder: 'Add Pick up address', value: '' },
  { id: 'drop-off', placeholder: 'Add Drop off address', value: '' },
]);

//Sortable JS Configuration
const sortableOptions = {
  animation: 100,
  ghostClass: 'text-container-bg'
};

const locations = ref([]);
const selectedLocode = ref('');

// Compute the currently selected location based on the selectedLocode
const selectedLocation = computed(() => {
  return locations.value.find(loc => loc.locode === selectedLocode.value);
});

const fetchLocations = async () => {
  try {
    const response = await axios.get(
      `${wpApiSettings.root}woo-lalamove/v1/get-city`,
      { headers: { 'X-WP-Nonce': wpApiSettings.nonce } }
    );
    // Assuming the API returns data in { data: [ ... ] }
    locations.value = response.data;
    
    console.log('Locations:', locations.value);
    // Set a default selection if available
    if (locations.value.length > 0) {
      selectedLocode.value = locations.value[0].locode;
    }
  } catch (error) {
    console.error('Error fetching locations:', error);
  }
};

onMounted(() => {
  //fetchLocations();
});
</script>




<style scoped lang="scss">
@use '@/css/scss/_variables.scss' as *;

.wrapper {
  display: flex !important;
  flex-direction: column;
  justify-content: start;
  align-items: center;
  box-sizing: border-box;
  padding: 5%;
  height: 100%;
  width: 100%;
}

.address-wrapper {
  height: 30%;
  width: 100%;
  border: 1px solid $txt-success;
  border-radius: 5px;
}

.text-container {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}
.text-container-bg {
  animation: shake 2s ease infinite normal forwards;
}

.drag-handle {
  cursor: grab;
  margin-right: 10px;
}

.text-box {
  width: 100%;
  padding: 10px;
  border: 1px solid $txt-success;
  border-radius: 5px;
}

.simple-list {
  list-style-type: none;
  padding: 0;
  
.dragging{
  color: $txt-success;
}

@keyframes bounce {
	0% {
		animation-timing-function: ease-in;
		opacity: 1;
		transform: translateY(-45px);
	}

	24% {
		opacity: 1;
	}

	40% {
		animation-timing-function: ease-in;
		transform: translateY(-24px);
	}

	65% {
		animation-timing-function: ease-in;
		transform: translateY(-12px);
	}

	82% {
		animation-timing-function: ease-in;
		transform: translateY(-6px);
	}

	93% {
		animation-timing-function: ease-in;
		transform: translateY(-4px);
	}

	25%,
	55%,
	75%,
	87% {
		animation-timing-function: ease-out;
		transform: translateY(0px);
	}

	100% {
		animation-timing-function: ease-out;
		opacity: 1;
		transform: translateY(0px);
	}
}
}
</style>
