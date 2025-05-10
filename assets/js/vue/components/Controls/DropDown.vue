<template>
  <div class="filter-dropdown">
      <span @click="toggleDropdown" class="dropdown-trigger">{{ selectedItemLabel }}</span>
      <div v-if="isOpen" class="dropdown-data">
          <span
              v-for="item in options"
              :key="item.key"
              :data-value="item.key"
              :class="['dropdown-item', { active: selectedItem === item.name }]"
              @click.stop="selectItem(item.name, item.details)"
          >
              {{ item.name }}
          </span>
      </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits } from 'vue';

// Define props to accept dynamic array data
const props = defineProps({
  options: {
      type: Array,
      required: true
  }
});

// Define emits to notify the parent of selection
const emit = defineEmits(['itemSelected']);

const isOpen = ref(false);
const selectedItem = ref(null);

const selectedItemLabel = computed(() => 
  selectedItem.value ? selectedItem.value : 'Select an Option'
);

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const selectItem = (name, details) => {
  selectedItem.value = name;
  isOpen.value = false;
  emit('itemSelected', { name, details }); 
};
</script>
  
  <style lang="scss" scoped>
  @use '@/css/scss/_variables.scss' as *;
  
  .filter-dropdown {
    display: flex;
    flex-direction: column;
    height: 2rem;
    width: 10rem;
    gap: 0.5rem;
    cursor: pointer;
    user-select: none;
    border: 2px solid $border-color;
    border-radius: 5px;
    background-color: $bg-high-light;
  }
  
  .dropdown-trigger {
    height: 100%;
    display: flex;
    align-items: center;
    padding: 3%;
    border-radius: 3%;
    background-color: $bg-high-light;
    font-size: $font-size-sm;
  }
  
  .dropdown-data {
    display: flex;
    flex-direction: column;
    max-height: fit-content; 
    width: 100%;
    background-color: $bg-high-light;
    border: 2px solid $border-color;
    border-radius: 3%;
  }
  
  .dropdown-item {
    padding: 2%;
    font-size: $font-size-sm;
  }
  
  .dropdown-item:hover {
    border-left: 2px solid $bg-primary;
    background-color: $border-color;
    color: $txt-primary;
  }
  
  .dropdown-item.active {
    border-left: 2px solid $bg-primary;
    background-color: $border-color;
    color: $txt-primary;
  }
  </style>