<script setup>
import { ref, useAttrs } from "vue";
import { storeToRefs } from "pinia";
import { useLalamoveStore } from "../../../store/lalamoveStore";

const lalamove = useLalamoveStore();
const { services } = storeToRefs(lalamove);
const isLoaded = ref(false);


const hoveredIndex = ref(null);
const carouselTrack = ref(null);

const scrollLeft = () => {
  carouselTrack.value?.scrollBy({ left: -300, behavior: "smooth" });
};

const scrollRight = () => {
  carouselTrack.value?.scrollBy({ left: 300, behavior: "smooth" });
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
</script>
<template>
  <div class="carousel-wrapper">
    <button class="nav-button left" @click="scrollLeft">
      <span class="material-symbols-outlined">chevron_left</span>
    </button>

    <div class="vehicle-content" ref="carouselTrack" v-dragscroll:nochilddrag>
      <div
        v-if="isLoaded"
        v-for="(service, index) in services"
        :key="index"
        class="vehicle"
        @mouseenter="hoveredIndex = index"
        @mouseleave="hoveredIndex = null"
        v-bind="$attrs"
        data-dragscroll
      >
        <div class="content">
          <img
            v-if="hoveredIndex !== index"
            :src="`/wp-content/plugins/woo-lalamove/assets/images/vehicles/${service.key}.png`"
            :alt="service.description"
            style="height: 60px; width: auto"
          />
          <span :class="['vehicle-name', { hovered: hoveredIndex === index }]">
            <p>{{ display_name(service.key) }}</p>
            <p v-if="hoveredIndex === index">
              {{ service.load.value }}{{ service.load.unit }}
            </p>
          </span>
          <p class="sub-body" v-if="hoveredIndex === index">
            {{ service.description }}
          </p>
        </div>
      </div>
      <!-- Skeleton while loading -->
      <div v-if="!isLoaded" v-for="index in 5" :key="index" class="vehicle-skeleton"></div>
    </div>

    <button class="nav-button right" @click="scrollRight">
      <span class="material-symbols-outlined">chevron_right</span>
    </button>
  </div>
</template>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;
@use "sass:color";

.carousel-wrapper {
  position: relative;
  height: 100%;

  .nav-button {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 10;
    background-color: $border-orange;
    border: none;
    color: white;
    font-weight: bold;
    padding: 0.75rem 1.25rem;
    border-radius: 50%;
    cursor: pointer;
    transition: background 0.2s, box-shadow 0.2s, color 0.2s;
    font-size: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);

    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;

    &:hover {
      background-color: color.scale($border-orange, $lightness: -10%);
      color: $header-active;
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
    }

    &.left {
      left: -2rem;
    }

    &.right {
      right: -2rem;
    }
  }
}

.vehicle-content {
  box-sizing: border-box;
  display: flex;
  flex-direction: row;
  height: 100%;
  max-width: 100%;
  overflow-x: auto;
  scroll-behavior: smooth;
  gap: 1rem;
  user-select: none;
  cursor: grab;

  &::-webkit-scrollbar {
    display: none;
  }

  &:active {
    cursor: grabbing;
  }
}

.vehicle {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 1rem;
  width: 150px;
  max-width: 150px;
  min-width: 150px;
  border-radius: 5px;
  background-color: $bg-high-light;
  border: 1px solid $border-color;

  .vehicle-name {
    text-align: center;
    text-wrap: wrap;
    word-break: break-word;
  }

  &:hover {
    border: 2px solid $border-orange;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    animation: hoverLift 0.3s ease forwards;
  }
}

.content {
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  align-items: center;
  gap: 0.5rem;

  .vehicle-name > p:first-child {
    font-size: $font-size-sm;
  }

  .vehicle-name.hovered > p:first-child {
    font-weight: $font-weight-bold;
    color: $header-active;
  }

  .vehicle-name.hovered > p:last-child {
    font-size: $font-size-xs;
    font-weight: $font-weight-bold;
  }
}

.vehicle-skeleton {
  max-width: 150px;
  min-width: 150px;
  border-radius: 5px;
  height: 100%;
  max-width: 100%;
  background: linear-gradient(90deg, #eee 25%, #f5f5f5 50%, #eee 75%);
  background-size: 200% 100%;
  border-radius: 5px;
  animation: shimmer 1.2s infinite;
}

.sub-body {
  font-size: $font-size-xs;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

@keyframes hoverLift {
  0% {
    transform: translateY(0);
  }

  50% {
    transform: translateY(-4px);
  }

  100% {
    transform: translateY(0);
  }
}

</style>
