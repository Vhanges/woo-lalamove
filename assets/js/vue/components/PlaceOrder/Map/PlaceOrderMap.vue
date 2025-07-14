<template>
  <div class="map-container" v-bind="attrs">
    <div id="map" ref="mapContainer"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, useAttrs } from 'vue'

const attrs = useAttrs()
const map = ref(null)
const mapContainer = ref(null)

const initMap = () => {
  if (!mapContainer.value) return

  // Use global L from enqueued scripts
  map.value = window.L.map(mapContainer.value).setView([14.5995, 120.9842], 12)

  window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
  }).addTo(map.value)

  // Add Leaflet Control Geocoder (global plugin from enqueue)
  window.L.Control.geocoder({
    defaultMarkGeocode: true
  }).addTo(map.value)
}

onMounted(() => {
  initMap()
})
</script>

<style scoped>
.map-container {
  position: relative;
  height: 100%;
  width: 100%;
}
#map {
  border-radius: 5px;  
  height: 100%;
}
.MarketSelection {
  position: absolute;
  right: 0;
  z-index: 1000;
}
</style>
