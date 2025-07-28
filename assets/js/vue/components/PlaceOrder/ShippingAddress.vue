<script setup>
import { ref, nextTick, onMounted } from 'vue';
import { Sortable } from 'sortablejs-vue3';
import { storeToRefs } from 'pinia';
import { useLalamoveStore } from '../../store/lalamoveStore';

const lalamove = useLalamoveStore();
const { reorderAddresses} = lalamove
const { addresses, selectedAddress } = storeToRefs(lalamove);

// Suggestions per address input
const suggestionsMap = ref({});
const isLoading = ref({});
const geocodeTimers = ref({});

const hasSearched = ref({});


// Simple debounce implementation
function debounce(func, delay) {
  let timer;
  return function(...args) {
    clearTimeout(timer);
    timer = setTimeout(() => {
      func.apply(this, args);
    }, delay);
  };
}

// Debounced geocoding function
const debouncedGeocode = debounce(async (index, value) => {
  if (!value || value.length < 3) {
    suggestionsMap.value[index] = [];
    isLoading.value[index] = false;
    return;
  }

  try {
    hasSearched.value[index] = true;
    isLoading.value[index] = true;
    const response = await fetch(
      `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(value)}&limit=5&addressdetails=1&email=your@email.com`
    );

    if (!response.ok) throw new Error("Network response was not ok");

    const data = await response.json();

    if (!data.length) {
      console.warn("No results returned for:", value);
      suggestionsMap.value[index] = [];
      return;
    }

    suggestionsMap.value[index] = data.map(item => ({
      name: item.display_name,
      center: {
        lat: parseFloat(item.lat),
        lng: parseFloat(item.lon)
      }
    }));
  } catch (err) {
    console.error("Geocoding error:", err);
    suggestionsMap.value[index] = [];
  } finally {
    isLoading.value[index] = false;
  }
}, 2000); // 2 secondz delay

function geocodeClicked(index, value) {

  // Clear previous timeout if exists
  if (geocodeTimers.value[index]) {
    clearTimeout(geocodeTimers.value[index]);
  }

  // Clear previous suggestions
  suggestionsMap.value[index] = [];
  
  // Show loading state after 500ms if still typing
  const loadingTimer = setTimeout(() => {
    if (value && value.length >= 3) {
      isLoading.value[index] = true;
    }
  }, 500);
  
  // Set new geocoding timer
  geocodeTimers.value[index] = setTimeout(() => {
    clearTimeout(loadingTimer);
    debouncedGeocode(index, value);
  }, 2000);
}

function selectSuggestion(index, suggestion) {
  hasSearched.value[index] = false;

  // Update address field
  addresses.value[index].address = suggestion.name;

  // Store coordinates
  addresses.value[index].coordinates = {
    lat: suggestion.center.lat.toString(),
    lng: suggestion.center.lng.toString()
  };

  // Clear suggestions for that field
  suggestionsMap.value[index] = [];

}

function handleAddressClick(index) {
    const address = addresses.value[index]; 
    console.log("LAT", address.coordinates.lat)
    console.log("LNG", address.coordinates.lng)
    console.log("LNG", index)
    selectedAddress.value = {
      lat: address.coordinates.lat,
      lng: address.coordinates.lng,
      index
    };
}

function addStop(item) {
  addresses.value.push({
          id: `stop-${item + 1}`,
          address: '',
          coordinates: {}
  })
}
  
function onEnd(event) {
  reorderAddresses({oldIndex: event.oldIndex, newIndex: event.newIndex});
}


const sortableOptions = {
  animation: 100,
  ghostClass: 'text-container-bg',
};

const handleDelete = (index) => {
  addresses.value.splice(index, 1);
  delete suggestionsMap.value[index];
};
</script>

<template>
  <div class="address-wrapper">
    <p class="header">ROUTE (Max. 20 STOPS)</p>
    <div class="address-content">
      <Sortable
        v-model:list="addresses"
        item-key="id"
        handle=".drag-handle"
        tag="div"
        class="drag-area"
        :options="sortableOptions"
        dragClass="dragging"
        @end="onEnd"
      >
        <template #item="{ element, index }">
          <div class="text-container">
            <span class="drag-handle">{{ index + 1 }}</span>
            <div class="input-group" @click="handleAddressClick(index)">
              <input
                v-model="element.address"
                class="text-box"
                :placeholder="`Stop ${index + 1}`"
                @input="geocodeClicked(index, $event.target.value)"
              />

              <!-- Loading indicator -->
              <div v-if="isLoading[index]" class="loading">
                Searching...
              </div>

              <!-- Suggestions dropdown -->
              <ul 
                class="suggestions" 
                v-if="suggestionsMap[index]?.length && !isLoading[index]"
              >
                <li
                  v-for="(suggestion, i) in suggestionsMap[index]"
                  :key="i"
                  @click="selectSuggestion(index, suggestion)"
                >
                  {{ suggestion.name }}
                </li>
              </ul>

              <!-- No results message -->
              <div 
                v-if="hasSearched[index] && suggestionsMap[index]?.length === 0 && element.address.length >= 3 && !isLoading[index]" 
                class="no-results"
              >
                No results found
              </div>
            </div>

            <button class="delete-button" @click.prevent="handleDelete(index)">
              <span class="material-symbols-outlined">close</span>
            </button>
          </div>
        </template>
      </Sortable>

      <button
        class="add-stop"
        @click.prevent="addStop(addresses.length)"
      >
        <span class="material-symbols-outlined">add</span>
        Add Stop
      </button>
    </div>
  </div>
</template>


<style scoped lang="scss">
@use '@/css/scss/_variables.scss' as *;

.address-wrapper {
  width: 100%;
  max-width: inherit;
  height: fit-content;

  .header {
    font-size: $font-size-xs;
    font-weight: $font-weight-bold;
    color: $header;
  }

  .address-content {
    box-sizing: border-box;
    width: 100%;
    height: fit-content;
    border: 1px solid $border-color;
    background-color: $bg-high-light;
    border-radius: 5px;
    padding: 0 3%;

    .drag-area {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }

    .add-stop {
      border: none;
      outline: none;
      background-color: inherit;
      height: 3rem;
      font-size: $font-size-sm;
      color: $header-active;
      display: flex;
      align-items: center;
      gap: 1rem;

      &:hover,
      &:focus {
        cursor: pointer;
        border: none;
        outline: none;
      }
    }

    .material-symbols-outlined {
      font-size: $font-size-sm;
    }
  }
}

.text-container {
  display: flex;
  align-items: center;
  box-sizing: border-box;

  .text-box {
    font-size: $font-size-sm;
    color: $txt-secondary;
    width: 100%;
    height: 3rem;
    border: none;
    border-bottom: 1px solid $border-color;

    &:focus {
      outline: none;
      border: none;
      box-shadow: none;
      border-bottom: 1px solid $border-color;
    }
  }

  .drag-handle {
    cursor: grab;
    padding: 0 0.5rem;
  }

  .delete-button {
    background: none;
    border: none;
    color: $txt-primary;
    cursor: pointer;
    height: 1.625rem;
    width: 1.625rem;
    display: flex;
    align-items: center;
    justify-content: center;

    &:hover {
      border-radius: 100%;
      background-color: $x-hover;
    }

    &:focus {
      outline: none;
    }
  }

  .material-symbols-outlined {
    font-size: $font-size-sm;
  }
}

.input-group {
  position: relative;
  width: 100%;
}

.suggestions {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 1px solid $border-color;
  border-top: none;
  z-index: 1000;
  margin: 0;
  padding: 0;
  list-style: none;

  li {
  padding: 8px;
  cursor: pointer;
  font-size: $font-size-sm;
  font-family: $font-primary;
  font-weight: $font-weight-regular;
  transition: background-color 0.2s ease, color 0.2s ease, font-weight 0.2s ease;

    &:hover {
    background-color: $header-active;
    color: $txt-light;
    font-weight: $font-weight-bold;
    }
  }
}



</style>
