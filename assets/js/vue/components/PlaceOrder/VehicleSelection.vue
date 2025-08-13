<script setup>
import { ref, onMounted, computed } from "vue";
import { storeToRefs } from "pinia";
import { useLalamoveStore } from "../../store/lalamoveStore";

const lalamove = useLalamoveStore();
const { services, serviceType, isServicesLoading } = storeToRefs(lalamove);

const carouselTrack = ref(null);
const showLeftButton = ref(false);
const showRightButton = ref(true);

const selectVehicle = (key) => {
  serviceType.value = key;
};

const scrollLeft = () => {
  carouselTrack.value?.scrollBy({ left: -280, behavior: "smooth" });
  updateButtonVisibility();
};

const scrollRight = () => {
  carouselTrack.value?.scrollBy({ left: 280, behavior: "smooth" });
  updateButtonVisibility();
};

const updateButtonVisibility = () => {
  if (!carouselTrack.value) return;
  
  const { scrollLeft, scrollWidth, clientWidth } = carouselTrack.value;
  showLeftButton.value = scrollLeft > 0;
  showRightButton.value = scrollLeft < scrollWidth - clientWidth - 10;
};

const display_name = (vehicle) => {
  switch (vehicle) {
    case "MOTORCYCLE":
      return "Motorcycle";
    case "SEDAN":
      return "Sedan";
    case "SEDAN_INTERCITY":
      return "Sedan Intercity";
    case "MPV":
      return "MPV";
    case "MPV_INTERCITY":
      return "MPV Intercity";
    case "VAN":
      return "Van";
    case "VAN_INTERCITY":
      return "Van Intercity";
    case "VAN1000":
      return "Van 1000kg";
    case "2000KG_ALUMINUM_LD":
      return "2000kg Aluminum LD";
    case "2000KG_FB_LD":
      return "2000kg FB LD";
    case "TRUCK550":
      return "Truck 550kg";
    case "10WHEEL_TRUCK":
      return "10-Wheel Truck";
    case "LD_10WHEEL_TRUCK":
      return "LD 10-Wheel Truck";
    default:
      return "Unknown Vehicle";
  }
};

const getVehicleIcon = (vehicleKey) => {
  const iconMap = {
    'MOTORCYCLE': '🏍️',
    'SEDAN': '🚗',
    'SEDAN_INTERCITY': '🚗',
    'MPV': '🚙',
    'MPV_INTERCITY': '🚙',
    'VAN': '🚐',
    'VAN_INTERCITY': '🚐',
    'VAN1000': '🚐',
    '2000KG_ALUMINUM_LD': '🚚',
    '2000KG_FB_LD': '🚚',
    'TRUCK550': '🚚',
    '10WHEEL_TRUCK': '🚛',
    'LD_10WHEEL_TRUCK': '🚛'
  };
  return iconMap[vehicleKey] || '🚗';
};

onMounted(() => {
  if (carouselTrack.value) {
    carouselTrack.value.addEventListener('scroll', updateButtonVisibility);
    updateButtonVisibility();
  }
});
</script>

<template>
  <div class="vehicle-selection-wrapper">
    <!-- Loading State -->
    <div v-if="isServicesLoading" class="loading-state">
      <div class="skeleton-grid">
        <div v-for="s in 4" :key="s" class="vehicle-skeleton"></div>
      </div>
    </div>

    <!-- Vehicle Selection -->
    <div v-else class="vehicle-selection">
      <!-- Navigation Buttons -->
      <button 
        v-show="showLeftButton"
        class="nav-button nav-left" 
        @click="scrollLeft"
        aria-label="Scroll left"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>

      <button 
        v-show="showRightButton"
        class="nav-button nav-right" 
        @click="scrollRight"
        aria-label="Scroll right"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>

      <!-- Vehicle Grid -->
      <div class="vehicle-grid" ref="carouselTrack">
        <div
          v-for="service in services"
          :key="service.key"
          class="vehicle-card"
          :class="{ 
            'vehicle-card--selected': service.key === serviceType,
            'vehicle-card--available': service.isAvailable !== false
          }"
          @click="selectVehicle(service.key)"
          role="button"
          :aria-pressed="service.key === serviceType"
          :aria-label="`Select ${display_name(service.key)}`"
          tabindex="0"
          @keydown.enter="selectVehicle(service.key)"
          @keydown.space.prevent="selectVehicle(service.key)"
        >
          <!-- Vehicle Image/Icon -->
          <div class="vehicle-image">
            <img
              :src="`/wp-content/plugins/woo-lalamove/assets/images/vehicles/${service.key}.png`"
              :alt="service.description"
              @error="(e) => e.target.style.display = 'none'"
              loading="lazy"
            />
            <span class="vehicle-icon">{{ getVehicleIcon(service.key) }}</span>
          </div>

          <!-- Vehicle Info -->
          <div class="vehicle-info">
            <h4 class="vehicle-name">{{ display_name(service.key) }}</h4>
            <div class="vehicle-specs">
              <span class="vehicle-load">
                {{ service.load.value }}{{ service.load.unit }}
              </span>
              <span class="vehicle-type">{{ service.description }}</span>
            </div>
          </div>

          <!-- Selected Indicator -->
          <div class="selected-indicator" v-if="service.key === serviceType">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
              <path d="M20 6L9 17L4 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
        </div>
      </div>

      <!-- Mobile Scroll Hint -->
      <div class="scroll-hint" v-if="services && services.length > 2">
        <span>Swipe to see more vehicles</span>
      </div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;
@use "@/css/scss/_mixins.scss" as *;

.vehicle-selection-wrapper {
  width: 100%;
  position: relative;
}

.loading-state {
  width: 100%;
}

.skeleton-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1rem;
  
  @include respond-above('md') {
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
  }
}

.vehicle-skeleton {
  @include loading-skeleton;
  height: 140px;
  border-radius: 12px;
  
  @include respond-above('md') {
    height: 160px;
  }
}

.vehicle-selection {
  position: relative;
  width: 100%;
}

.nav-button {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  z-index: 10;
  @include btn-base;
  background: $bg-high-light;
  border: 2px solid $border-color;
  color: $txt-primary;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  
  &:hover:not(:disabled) {
    background: $bg-primary;
    color: $txt-light;
    border-color: $bg-primary;
    transform: translateY(-50%) translateY(-2px);
  }
  
  &.nav-left {
    left: -1rem;
    
    @include respond-above('md') {
      left: -1.5rem;
    }
  }
  
  &.nav-right {
    right: -1rem;
    
    @include respond-above('md') {
      right: -1.5rem;
    }
  }
  
  @include respond-above('md') {
    width: 48px;
    height: 48px;
  }
}

.vehicle-grid {
  display: flex;
  gap: 1rem;
  overflow-x: auto;
  scroll-behavior: smooth;
  padding: 0.5rem 0;
  margin: 0 1rem;
  
  @include respond-above('md') {
    gap: 1.5rem;
    margin: 0 2rem;
    padding: 1rem 0;
  }
  
  @include respond-above('lg') {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    overflow-x: visible;
    margin: 0;
  }
  
  // Hide scrollbar
  &::-webkit-scrollbar {
    display: none;
  }
  
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.vehicle-card {
  @include card(1.25rem, 12px);
  position: relative;
  min-width: 240px;
  max-width: 280px;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 2px solid transparent;
  
  @include respond-above('md') {
    min-width: 280px;
    padding: 1.5rem;
  }
  
  @include respond-above('lg') {
    min-width: auto;
    max-width: none;
  }
  
  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    border-color: rgba($bg-primary, 0.3);
  }
  
  &--selected {
    border-color: $bg-primary;
    background: linear-gradient(135deg, rgba($bg-primary, 0.05) 0%, rgba($bg-primary, 0.02) 100%);
    
    &:hover {
      border-color: $bg-primary;
    }
  }
  
  &:focus {
    outline: 2px solid $bg-primary;
    outline-offset: 2px;
  }
}

.vehicle-image {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 60px;
  margin-bottom: 1rem;
  
  img {
    max-height: 100%;
    max-width: 100%;
    object-fit: contain;
  }
  
  .vehicle-icon {
    font-size: 2.5rem;
    opacity: 0.8;
    
    img[style*="display: none"] + & {
      opacity: 1;
    }
  }
  
  @include respond-above('md') {
    height: 70px;
    margin-bottom: 1.25rem;
    
    .vehicle-icon {
      font-size: 3rem;
    }
  }
}

.vehicle-info {
  text-align: center;
}

.vehicle-name {
  @include heading-3;
  margin: 0 0 0.5rem 0;
  font-size: $font-size-md;
  line-height: 1.3;
  color: $txt-secondary;
  
  .vehicle-card--selected & {
    color: $bg-primary;
  }
}

.vehicle-specs {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.vehicle-load {
  @include small-text;
  font-weight: $font-weight-medium;
  color: $bg-primary;
  background: rgba($bg-primary, 0.1);
  padding: 0.25rem 0.5rem;
  border-radius: 20px;
  display: inline-block;
  font-size: $font-size-xs;
}

.vehicle-type {
  @include small-text;
  color: rgba($txt-primary, 0.7);
  font-size: $font-size-xs;
  line-height: 1.3;
}

.selected-indicator {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  background: $bg-primary;
  color: $txt-light;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  
  @include respond-above('md') {
    width: 28px;
    height: 28px;
    top: 1rem;
    right: 1rem;
  }
}

.scroll-hint {
  text-align: center;
  margin-top: 1rem;
  
  span {
    @include small-text;
    color: rgba($txt-primary, 0.6);
    font-style: italic;
  }
  
  @include respond-above('lg') {
    display: none;
  }
}

// Mobile specific adjustments
@media (max-width: 767px) {
  .vehicle-grid {
    margin: 0 0.5rem;
  }
  
  .vehicle-card {
    min-width: 200px;
    padding: 1rem;
  }
  
  .vehicle-image {
    height: 50px;
    margin-bottom: 0.75rem;
    
    .vehicle-icon {
      font-size: 2rem;
    }
  }
  
  .nav-button {
    width: 36px;
    height: 36px;
    
    &.nav-left {
      left: -0.75rem;
    }
    
    &.nav-right {
      right: -0.75rem;
    }
  }
}

// High contrast mode
@media (prefers-contrast: high) {
  .vehicle-card {
    border: 2px solid $border-color;
    
    &--selected {
      border: 3px solid $bg-primary;
    }
  }
}

// Reduced motion
@media (prefers-reduced-motion: reduce) {
  .vehicle-card {
    transition: none;
    
    &:hover {
      transform: none;
    }
  }
  
  .nav-button:hover:not(:disabled) {
    transform: translateY(-50%);
  }
}
</style>
