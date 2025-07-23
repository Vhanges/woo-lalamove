<template>
  <div class="map-container" v-bind="attrs">
    <div id="map" ref="mapContainer"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { useAttrs } from "vue";
import { storeToRefs } from "pinia";
import { useLalamoveStore } from "../../../store/lalamoveStore";

const attrs = useAttrs();
const mapContainer = ref(null);
const map = ref(null);
const markers = ref([]);

const lalamove = useLalamoveStore();
const { addresses, selectedAddress } = storeToRefs(lalamove);

// Initialize Leaflet map
const initMap = () => {
  if (!mapContainer.value) return;

  map.value = window.L.map(mapContainer.value).setView([14.5995, 121.15], 11.5);

  window.L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "&copy; OpenStreetMap contributors",
  }).addTo(map.value);
};

const renderMarkers = () => {
  // Clear old markers
  markers.value.forEach((marker) => map.value.removeLayer(marker));
  markers.value = [];

  addresses.value.forEach((loc, idx) => {

    const { lat, lng } = map.value?.getCenter();
    const coordinates =
      !loc.coordinates?.lat || !loc.coordinates?.lng
        ? [lat, lng]
        : [parseFloat(loc.coordinates.lat), parseFloat(loc.coordinates.lng)];

    const label = `Stop ${idx + 1}`;

    const newMarker = window.L.marker(coordinates, { draggable: true })
      .addTo(map.value)
      .bindTooltip(label, {
        permanent: true,
        direction: "top",
        offset: [0, -10],
        className: "custom-tooltip",
      });

    // Add dragend event listener
    newMarker.on("dragend", (event) => {
      const marker = event.target;
      const {lat, lng} = marker.getLatLng();

      lalamove.updateAddressCoordinates(idx, lat.toString(), lng.toString());
      
    });

    markers.value.push(newMarker);
  });
};

watch(
  addresses,
  () => {
    if (!map.value) return;
    renderMarkers();
  },
  { deep: true }
);

watch(
  selectedAddress,
  (newVal) => {
    if (!map.value || !newVal?.lat || !newVal?.lng) return;

    // Fly to the selected location with animation
    map.value.flyTo([newVal.lat, newVal.lng], 15, {
      duration: 1, // Animation duration in seconds
      easeLinearity: 0.25,
    });

    markers.value.forEach((marker) => {
      const latLng = marker.getLatLng();
      if (latLng.lat === newVal.lat && latLng.lng === newVal.lng) {
        marker.setZIndexOffset(1000);
        marker.openTooltip();
      }
    });
  },
  { deep: true }
);

onMounted(() => {
  initMap();
  renderMarkers();
});
</script>

<style scoped>
.custom-tooltip {
  background-color: white;
  color: #333;
  font-weight: bold;
  padding: 3px 6px;
  border: 1px solid #ccc;
  border-radius: 4px;
}

.map-container {
  position: relative;
  height: 100%;
  width: 100%;
}
#map {
  height: 100%;
  border-radius: 5px;
}
</style>
