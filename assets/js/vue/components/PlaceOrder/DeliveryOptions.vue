<template>
  <div class="delivery-options-wrapper">
    <div class="options-grid">
      <!-- Proof of Delivery -->
      <div class="option-item">
        <div class="option-content">
          <div class="option-info">
            <div class="option-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M14 2H6C4.9 2 4 2.9 4 4V20C4 21.1 4.9 22 6 22H18C19.1 22 20 21.1 20 20V8L14 2Z" stroke="currentColor" stroke-width="2"/>
                <polyline points="14,2 14,8 20,8" stroke="currentColor" stroke-width="2"/>
                <line x1="16" y1="13" x2="8" y2="13" stroke="currentColor" stroke-width="2"/>
                <line x1="16" y1="17" x2="8" y2="17" stroke="currentColor" stroke-width="2"/>
                <polyline points="10,9 9,10 7,8" stroke="currentColor" stroke-width="2"/>
              </svg>
            </div>
            <div class="option-text">
              <h4 class="option-title">Proof of Delivery</h4>
              <p class="option-description">Get photo confirmation when your package is delivered</p>
            </div>
          </div>
          <label class="toggle-switch" :for="`proof_of_delivery`">
            <input
              type="checkbox"
              id="proof_of_delivery"
              :checked="isPodEnabled"
              v-model="isPodEnabled"
              class="toggle-input"
            />
            <span class="toggle-slider"></span>
          </label>
        </div>
      </div>

      <!-- Route Optimization -->
      <div class="option-item">
        <div class="option-content">
          <div class="option-info">
            <div class="option-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                <path d="M21 16V8C21 6.9 20.1 6 19 6H5C3.9 6 3 6.9 3 8V16C3 17.1 3.9 18 5 18H19C20.1 18 21 17.1 21 16Z" stroke="currentColor" stroke-width="2"/>
                <polyline points="7,10 12,15 17,10" stroke="currentColor" stroke-width="2"/>
              </svg>
            </div>
            <div class="option-text">
              <h4 class="option-title">Optimize Route</h4>
              <p class="option-description">Automatically find the most efficient delivery path</p>
            </div>
          </div>
          <label class="toggle-switch" :for="`optimize_route`">
            <input
              type="checkbox"
              id="optimize_route"
              :checked="isRouteOptimized"
              v-model="isRouteOptimized"
              class="toggle-input"
            />
            <span class="toggle-slider"></span>
          </label>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { storeToRefs } from "pinia";
import { useLalamoveStore } from "../../store/lalamoveStore";

const lalamove = useLalamoveStore();
const { isPodEnabled, isRouteOptimized } = storeToRefs(lalamove);

</script>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;
@use "@/css/scss/_mixins.scss" as *;

.delivery-options-wrapper {
  width: 100%;
}

.options-grid {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  
  @include respond-above('md') {
    gap: 1.25rem;
  }
}

.option-item {
  border: 2px solid $border-color;
  border-radius: 12px;
  padding: 1rem;
  background: $bg-high-light;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  
  &:hover {
    border-color: rgba($bg-primary, 0.3);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  }
  
  @include respond-above('md') {
    padding: 1.25rem;
  }
}

.option-content {
  @include flex-between;
  align-items: flex-start;
  gap: 1rem;
}

.option-info {
  display: flex;
  align-items: flex-start;
  gap: 0.75rem;
  flex: 1;
}

.option-icon {
  @include flex-center;
  width: 40px;
  height: 40px;
  background: rgba($bg-primary, 0.1);
  border-radius: 10px;
  color: $bg-primary;
  flex-shrink: 0;
  
  @include respond-above('md') {
    width: 44px;
    height: 44px;
  }
}

.option-text {
  flex: 1;
}

.option-title {
  @include heading-3;
  font-size: $font-size-md;
  margin: 0 0 0.25rem 0;
  color: $txt-secondary;
}

.option-description {
  @include small-text;
  margin: 0;
  color: rgba($txt-primary, 0.7);
  line-height: 1.4;
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 48px;
  height: 24px;
  flex-shrink: 0;
  cursor: pointer;
}

.toggle-input {
  opacity: 0;
  width: 0;
  height: 0;
  
  &:checked + .toggle-slider {
    background-color: $bg-primary;
    
    &:before {
      transform: translateX(24px);
    }
  }
  
  &:focus + .toggle-slider {
    box-shadow: 0 0 0 2px rgba($bg-primary, 0.3);
  }
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba($border-color, 0.5);
  transition: all 0.3s ease;
  border-radius: 24px;
  
  &:before {
    position: absolute;
    content: "";
    height: 20px;
    width: 20px;
    left: 2px;
    bottom: 2px;
    background-color: $bg-high-light;
    transition: all 0.3s ease;
    border-radius: 50%;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
  }
}

// Mobile adjustments
@media (max-width: 767px) {
  .option-item {
    padding: 0.75rem;
  }
  
  .option-content {
    gap: 0.75rem;
  }
  
  .option-info {
    gap: 0.5rem;
  }
  
  .option-icon {
    width: 36px;
    height: 36px;
  }
  
  .toggle-switch {
    width: 44px;
    height: 22px;
  }
  
  .toggle-input:checked + .toggle-slider:before {
    transform: translateX(22px);
  }
  
  .toggle-slider:before {
    height: 18px;
    width: 18px;
  }
}

// High contrast mode
@media (prefers-contrast: high) {
  .option-item {
    border: 2px solid $border-color;
  }
  
  .toggle-slider {
    border: 1px solid $border-color;
  }
  
  .toggle-input:checked + .toggle-slider {
    border-color: $bg-primary;
  }
}

// Reduced motion
@media (prefers-reduced-motion: reduce) {
  .toggle-slider,
  .toggle-slider:before {
    transition: none;
  }
}
</style>
