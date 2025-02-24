<script setup>
import { ref, useAttrs } from 'vue'; 

const attrs = useAttrs();
const handleDelete = () => {
  // Your delete logic here, e.g., clearing the input value
  attrs.value = '';
};
</script>


<template>
    <div class="text-container">
      <span class="drag-handle">::</span>
      <input v-bind="attrs" />
      <button class="delete-button" @click="handleDelete">
        <span class="material-symbols-outlined">close</span>
      </button>
    </div>
  </template>
  

<style lang="scss" scoped>
@use '@/css/scss/_variables.scss' as *;

.text-container {
  display: flex;
  align-items: center;
  box-sizing: border-box;
}

.text-box {
  width: 100%;
  height: 3rem;
  border: none;
  border-bottom: 1px solid $border-color;
  font-size: $font-size-xs;
}

.text-box:focus {
  outline: none;
  border: none;
  box-shadow: none;
  border-bottom: 1px solid $border-color;
}

.drag-handle {
  cursor: grab;
}

.delete-button {
  background: none;
  border: none;
  color: $txt-primary;
  cursor: pointer;
  height: 1.625rem;
  width: 1.625rem;
  display: flex;
  align-items: center;
  justify-content: center;

  &:hover {
    color: $txt-primary; 
    border-radius: 100%;
    background-color: $x-hover;
  }

  &:focus {
    outline: none;
  }
}

.material-symbols-outlined {
  height: auto;
  font-size: $font-size-sm; 
}



</style>