<template>
  <div class="map-container">
    <div id="map" ref="mapContainer"></div>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

export default {
  name: "PlaceOrderMap",
  setup() {
    const map = ref(null);
    const mapContainer = ref(null);

    const initMap = () => {
      if (!mapContainer.value) return;

      map.value = L.map(mapContainer.value).setView([14.5995, 120.9842], 12); // Manila

      // Add OpenStreetMap tile layer
      L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
        attribution:
          '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      }).addTo(map.value);

      // Add a marker
      L.marker([14.5995, 120.9842])
        .addTo(map.value)
        .bindPopup("This is Manila!")
        .openPopup();
    };

    // Run initMap() after the component is mounted
    onMounted(() => {
      initMap();
    });

    return {
      mapContainer,
    };
  },
};
</script>

<style scoped>
.map-container {
  height: 100%;
  width: 100%;
}
#map {
  height: 100%;
}
</style>
