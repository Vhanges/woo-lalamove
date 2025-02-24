<template>
    <MarketSelection class="MarketSelection"/>
    <div class="map-container" v-bind="attrs">
    <div id="map" ref="mapContainer"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, useAttrs } from 'vue'
import MarketSelection from '../Map/MarketSelection.vue';
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'


const attrs = useAttrs();


const map = ref(null)
const mapContainer = ref(null)

const initMap = () => {
  if (!mapContainer.value) return

  map.value = L.map(mapContainer.value).setView([14.5995, 120.9842], 12) // Manila

  // Add OpenStreetMap tile layer
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
  }).addTo(map.value)

  // Add a marker
  L.marker([14.5995, 120.9842])
    .addTo(map.value)
    .bindPopup('This is Manila!')
    .openPopup()
}

// Run initMap() after the component is mounted
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
  height: 100%;
}

.MarketSelection {
  position: absolute;
  right: 0;
  z-index: 1000;
}
</style>
