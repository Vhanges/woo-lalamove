<template>
  <div class="wrapper">
    <div class="address-wrapper">
      <form>
        <!-- Working addresses example -->
        <draggable 
          v-model="addresses"
          item-key="id"
          handle=".drag-handle"
          tag="div"
          class="drag-area"
        >
          <template #item="{ element }">
            <div class="text-container">
              <span class="drag-handle">::</span>
              <input
                type="text"
                :id="element.id"
                class="text-box"
                :placeholder="element.placeholder"
                v-model="element.value"
              />
            </div>
          </template>
        </draggable>

        <!-- Simple array example -->
        <draggable
          v-model="simpleArray"
          tag="ul"
          item-key="id"
          class="simple-list"
        >
          <template #item="{ element }">
            <li>{{ element }}</li>
          </template>
        </draggable>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import draggable from 'vuedraggable';

const addresses = ref([
  { id: 'pickup', placeholder: 'Add Pick up address', value: '' },
  { id: 'drop-off', placeholder: 'Add Drop off address', value: '' },
]);

// Change simpleArray to use objects with ids
const simpleArray = ref([
  { id: 1, name: 'Item 1' },
  { id: 2, name: 'Item 2' },
  { id: 3, name: 'Item 3' }
]);
</script>

<style scoped lang="scss">
@use '@/css/scss/_variables.scss' as *;

/* Your existing styles */
.wrapper {
  display: flex !important;
  flex-direction: column;
  justify-content: start;
  align-items: center;
  box-sizing: border-box;
  padding: 5%;
  height: 100%;
  width: 100%;
}

.address-wrapper {
  height: 30%;
  width: 100%;
  border: 1px solid $txt-success;
  border-radius: 5px;
}

.text-container {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.drag-handle {
  cursor: grab;
  margin-right: 10px;
}

.text-box {
  width: 100%;
  padding: 10px;
  border: 1px solid $txt-success;
  border-radius: 5px;
}

/* Additional styles for the list */
.simple-list {
  list-style-type: none;
  padding: 0;
  
  li {
    padding: 8px;
    margin: 4px;
    border: 1px solid #ccc;
    border-radius: 4px;
  }
}
</style>