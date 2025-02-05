<template>
  <div>
    <h2>Shipments</h2>
    <p>Manage and track your shipments here.</p>
    <ul ref="sortableList">
      <li v-for="shipment in shipments" :key="shipment">{{ shipment }}</li>
    </ul>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Sortable from 'sortablejs';

const shipments = ref([
  'Shipment 1',
  'Shipment 2',
  'Shipment 3'
]);

// Create a ref for the sortable list element
const sortableList = ref(null);

onMounted(() => {
  // Use the sortableList ref instead of querySelector
  new Sortable(sortableList.value, {
    animation: 300,
    ghostClass: 'blue-background-class',

  });
});
</script>

<style scoped>
.blue-background-class {
  background-color: blue;
}
</style>