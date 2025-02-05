<template>
  <div>
    <h2>Records</h2>
    <p>View and manage your shipment records here.</p>
    <Sortable
      :list="records"
      item-key="id"
      tag="ul"
      :options="{ animation: 300 }"
    >
      <template #item="{ element }">
        <li>{{ element.name }}</li>
      </template>
    </Sortable>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { Sortable } from 'sortablejs-vue3';

const records = ref([
  { id: 1, name: 'Record 1' },
  { id: 2, name: 'Record 2' },
  { id: 3, name: 'Record 3' },
]);
</script>

<style scoped>
/* Add your styles here */
</style>
