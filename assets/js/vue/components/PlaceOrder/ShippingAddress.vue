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
  <div class="shipping-address-wrapper">
    <!-- Empty State -->
    <div v-if="!addresses || addresses.length === 0" class="empty-state">
      <div class="empty-icon">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 2L14.09 8.26L20 6.27L17.91 12.53L20 18.8L14.09 16.81L12 23L9.91 16.81L4 18.8L6.09 12.53L4 6.27L9.91 8.26L12 2Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <h3 class="empty-title">Add Your Route</h3>
      <p class="empty-description">Start by adding pickup and delivery locations for your order</p>
      <button class="empty-action-btn" @click="addStop(0)">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        Add First Location
      </button>
    </div>

    <!-- Address List -->
    <div v-else class="address-list">
      <!-- Header -->
      <div class="list-header">
        <h3 class="list-title">Delivery Route</h3>
        <span class="stops-counter">{{ addresses.length }}/20 stops</span>
      </div>

      <!-- Route Items -->
      <Sortable
        v-model:list="addresses"
        item-key="id"
        handle=".drag-handle"
        tag="div"
        class="route-container"
        :options="sortableOptions"
        dragClass="dragging"
        @end="onEnd"
      >
        <template #item="{ element, index }">
          <div class="address-item" :class="{ 'address-item--first': index === 0, 'address-item--last': index === addresses.length - 1 }">
            <!-- Route Indicator -->
            <div class="route-indicator">
              <div class="route-number">{{ index + 1 }}</div>
              <div class="route-line" v-if="index < addresses.length - 1"></div>
            </div>

            <!-- Address Input -->
            <div class="address-input-section" @click="handleAddressClick(index)">
              <div class="input-header">
                <span class="address-label">
                  {{ index === 0 ? 'Pickup Location' : index === addresses.length - 1 ? 'Delivery Location' : `Stop ${index}` }}
                </span>
                <button 
                  v-if="addresses.length > 2" 
                  class="drag-handle" 
                  :aria-label="`Reorder ${index === 0 ? 'pickup' : 'stop ' + index}`"
                >
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <path d="M8 6H16M8 12H16M8 18H16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                  </svg>
                </button>
              </div>

              <div class="input-group">
                <input
                  v-model="element.address"
                  class="address-input"
                  :placeholder="index === 0 ? 'Enter pickup address...' : index === addresses.length - 1 ? 'Enter delivery address...' : `Enter stop ${index} address...`"
                  @input="geocodeClicked(index, $event.target.value)"
                  :class="{ 'input-loading': isLoading[index] }"
                />

                <!-- Input Status Icons -->
                <div class="input-status">
                  <!-- Loading -->
                  <div v-if="isLoading[index]" class="loading-spinner">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                      <path d="M12 2V6M12 18V22M4.93 4.93L7.76 7.76M16.24 16.24L19.07 19.07M2 12H6M18 12H22M4.93 19.07L7.76 16.24M16.24 7.76L19.07 4.93" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </div>
                  
                  <!-- Success -->
                  <div v-else-if="element.coordinates?.lat && element.coordinates?.lng" class="success-icon">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                      <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </div>
                </div>

                <!-- Suggestions Dropdown -->
                <div v-if="suggestionsMap[index]?.length && !isLoading[index]" class="suggestions-dropdown">
                  <ul class="suggestions-list">
                    <li
                      v-for="(suggestion, i) in suggestionsMap[index]"
                      :key="i"
                      class="suggestion-item"
                      @click="selectSuggestion(index, suggestion)"
                    >
                      <div class="suggestion-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                          <path d="M21 10C21 17 12 23 12 23S3 17 3 10C3 5.03 7.03 1 12 1S21 5.03 21 10Z" stroke="currentColor" stroke-width="2"/>
                          <circle cx="12" cy="10" r="3" stroke="currentColor" stroke-width="2"/>
                        </svg>
                      </div>
                      <span class="suggestion-text">{{ suggestion.name }}</span>
                    </li>
                  </ul>
                </div>

                <!-- No Results -->
                <div 
                  v-if="hasSearched[index] && suggestionsMap[index]?.length === 0 && element.address.length >= 3 && !isLoading[index]" 
                  class="no-results"
                >
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                    <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/>
                    <path d="M21 21L16.65 16.65" stroke="currentColor" stroke-width="2"/>
                  </svg>
                  <span>No locations found. Try a different search term.</span>
                </div>
              </div>
            </div>

            <!-- Delete Button -->
            <button 
              v-if="addresses.length > 2" 
              class="delete-button" 
              @click.prevent="handleDelete(index)"
              :aria-label="`Remove ${index === 0 ? 'pickup location' : 'stop ' + index}`"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </button>
          </div>
        </template>
      </Sortable>

      <!-- Add Stop Button -->
      <div class="add-stop-section" v-if="addresses.length < 20">
        <button class="add-stop-btn" @click.prevent="addStop(addresses.length)">
          <div class="add-stop-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
              <path d="M12 5V19M5 12H19" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
          <span>Add Another Stop</span>
        </button>
        <p class="add-stop-hint">You can add up to {{ 20 - addresses.length }} more stops</p>
      </div>
    </div>
  </div>
</template>


<style scoped lang="scss">
@use '@/css/scss/_variables.scss' as *;
@use '@/css/scss/_mixins.scss' as *;

.shipping-address-wrapper {
  width: 100%;
}

.empty-state {
  text-align: center;
  padding: 3rem 1rem;
  
  @include respond-above('md') {
    padding: 4rem 2rem;
  }
}

.empty-icon {
  color: rgba($txt-primary, 0.4);
  margin-bottom: 1.5rem;
  
  svg {
    width: 48px;
    height: 48px;
    
    @include respond-above('md') {
      width: 64px;
      height: 64px;
    }
  }
}

.empty-title {
  @include heading-3;
  color: $txt-secondary;
  margin-bottom: 0.5rem;
}

.empty-description {
  @include body-text;
  color: rgba($txt-primary, 0.8);
  margin-bottom: 2rem;
  max-width: 320px;
  margin-left: auto;
  margin-right: auto;
}

.empty-action-btn {
  @include btn-primary;
  gap: 0.5rem;
}

.address-list {
  width: 100%;
}

.list-header {
  @include flex-between;
  margin-bottom: 1.5rem;
  
  @include respond-above('md') {
    margin-bottom: 2rem;
  }
}

.list-title {
  @include heading-3;
  margin: 0;
}

.stops-counter {
  @include small-text;
  background: rgba($bg-primary, 0.1);
  color: $bg-primary;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-weight: $font-weight-medium;
}

.route-container {
  position: relative;
}

.address-item {
  position: relative;
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  
  @include respond-above('md') {
    gap: 1.5rem;
    margin-bottom: 2rem;
  }
  
  &:last-child {
    margin-bottom: 0;
  }
  
  &.dragging {
    opacity: 0.7;
    transform: rotate(2deg);
  }
}

.route-indicator {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex-shrink: 0;
  position: relative;
}

.route-number {
  @include flex-center;
  width: 32px;
  height: 32px;
  background: $bg-primary;
  color: $txt-light;
  border-radius: 50%;
  font-weight: $font-weight-bold;
  font-size: $font-size-sm;
  z-index: 2;
  
  .address-item--first & {
    background: $bg-success;
  }
  
  .address-item--last & {
    background: $txt-error;
  }
  
  @include respond-above('md') {
    width: 36px;
    height: 36px;
    font-size: $font-size-md;
  }
}

.route-line {
  width: 2px;
  flex: 1;
  background: linear-gradient(to bottom, $bg-primary 0%, rgba($bg-primary, 0.3) 100%);
  margin-top: 0.5rem;
  min-height: 40px;
}

.address-input-section {
  flex: 1;
  background: $bg-high-light;
  border: 2px solid $border-color;
  border-radius: 12px;
  padding: 1rem;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  
  &:hover {
    border-color: rgba($bg-primary, 0.5);
  }
  
  &:focus-within {
    border-color: $bg-primary;
    box-shadow: 0 0 0 3px rgba($bg-primary, 0.1);
  }
  
  @include respond-above('md') {
    padding: 1.25rem;
  }
}

.input-header {
  @include flex-between;
  margin-bottom: 0.75rem;
}

.address-label {
  @include small-text;
  font-weight: $font-weight-medium;
  color: $txt-secondary;
}

.drag-handle {
  @include btn-base;
  background: transparent;
  color: rgba($txt-primary, 0.5);
  padding: 0.25rem;
  border-radius: 4px;
  cursor: grab;
  
  &:hover {
    color: $txt-primary;
    background: rgba($bg-primary, 0.1);
  }
  
  &:active {
    cursor: grabbing;
  }
}

.input-group {
  position: relative;
}

.address-input {
  @include form-input;
  border: none;
  padding: 0.75rem 2.5rem 0.75rem 0;
  background: transparent;
  font-size: $font-size-md;
  
  &::placeholder {
    color: rgba($txt-primary, 0.5);
  }
  
  &:focus {
    box-shadow: none;
    border: none;
  }
  
  &.input-loading {
    background: rgba($bg-primary, 0.02);
  }
}

.input-status {
  position: absolute;
  right: 0.5rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
}

.loading-spinner {
  color: $bg-primary;
  animation: spin 1s linear infinite;
}

.success-icon {
  color: $txt-success;
}

@keyframes spin {
  from { transform: translateY(-50%) rotate(0deg); }
  to { transform: translateY(-50%) rotate(360deg); }
}

.suggestions-dropdown {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  z-index: 1000;
  margin-top: 0.25rem;
  background: $bg-high-light;
  border: 2px solid $border-color;
  border-radius: 8px;
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
  overflow: hidden;
}

.suggestions-list {
  list-style: none;
  margin: 0;
  padding: 0;
  max-height: 200px;
  overflow-y: auto;
}

.suggestion-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  cursor: pointer;
  transition: background-color 0.2s ease;
  border-bottom: 1px solid rgba($border-color, 0.5);
  
  &:last-child {
    border-bottom: none;
  }
  
  &:hover {
    background: rgba($bg-primary, 0.05);
  }
  
  &:active {
    background: rgba($bg-primary, 0.1);
  }
}

.suggestion-icon {
  color: rgba($txt-primary, 0.6);
  flex-shrink: 0;
}

.suggestion-text {
  @include body-text;
  flex: 1;
  line-height: 1.4;
}

.no-results {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 1rem;
  color: rgba($txt-primary, 0.6);
  font-size: $font-size-sm;
  border: 1px solid rgba($border-color, 0.5);
  border-radius: 8px;
  margin-top: 0.25rem;
  background: rgba($bg-gray, 0.3);
}

.delete-button {
  @include btn-base;
  background: transparent;
  color: rgba($txt-error, 0.7);
  width: 32px;
  height: 32px;
  border-radius: 50%;
  flex-shrink: 0;
  align-self: flex-start;
  margin-top: 1rem;
  
  &:hover {
    background: rgba($txt-error, 0.1);
    color: $txt-error;
  }
  
  @include respond-above('md') {
    width: 36px;
    height: 36px;
    margin-top: 1.25rem;
  }
}

.add-stop-section {
  text-align: center;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px dashed rgba($border-color, 0.5);
}

.add-stop-btn {
  @include btn-secondary;
  gap: 0.5rem;
  margin-bottom: 0.75rem;
  
  &:hover {
    background: rgba($bg-primary, 0.05);
    border-color: $bg-primary;
    color: $bg-primary;
  }
}

.add-stop-icon {
  @include flex-center;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: rgba($bg-primary, 0.1);
  color: $bg-primary;
}

.add-stop-hint {
  @include small-text;
  color: rgba($txt-primary, 0.6);
  margin: 0;
  font-style: italic;
}

// Mobile adjustments
@media (max-width: 767px) {
  .address-item {
    gap: 0.75rem;
    margin-bottom: 1rem;
  }
  
  .route-number {
    width: 28px;
    height: 28px;
    font-size: $font-size-xs;
  }
  
  .address-input-section {
    padding: 0.75rem;
  }
  
  .input-header {
    margin-bottom: 0.5rem;
  }
  
  .address-input {
    padding: 0.5rem 2rem 0.5rem 0;
    font-size: $font-size-sm;
  }
  
  .delete-button {
    width: 28px;
    height: 28px;
    margin-top: 0.75rem;
  }
}

// High contrast mode
@media (prefers-contrast: high) {
  .address-input-section {
    border: 3px solid $border-color;
    
    &:focus-within {
      border-color: $bg-primary;
    }
  }
  
  .route-number {
    border: 2px solid $txt-light;
  }
}

// Reduced motion
@media (prefers-reduced-motion: reduce) {
  .address-item.dragging {
    transform: none;
  }
  
  .loading-spinner {
    animation: none;
  }
}

// Sortable specific styles
.sortable-ghost {
  opacity: 0.4;
}

.sortable-chosen {
  cursor: grabbing;
}
</style>
