
<script setup>
import { ref, onMounted } from 'vue';  

const additionalServices = defineProps({
    withParentType: Array,
    withoutParentType: Array
});

const isOpen = ref('');

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};
</script>


<template>
    <div>
      <div v-for="(requests, parentType) in additionalServices.withParentType" :key="parentType">
        <span class="parent-type">
          <span class="material-symbols-outlined">{{ isOpen ? 'arrow_drop_down' : 'arrow_right' }}</span>
          <p class="header" @click="toggleDropdown">
            {{ parentType }}
          </p>
        </span>
        <ul class="child-type">
          <li v-if="isOpen" v-for="request in requests" :key="request.name" >
              <input type="checkbox" :id="request.name" :value="request.name">
              <label :for="request.name" class="services">{{ request.description.replace(/^.*·\s*/, '') }}</label>
          </li>
        </ul>
      </div>
      <div>
        <ul>
          <li v-for="request in additionalServices.withoutParentType" :key="request.name" class="single-service">
            <input type="checkbox" :id="request.name" :value="request.name">
            <label :for="request.name" class="services">{{ request.description}}</label>
          </li>
        </ul>
      </div>
    </div>
  </template>
<style lang="scss" scoped>
@use '@/css/scss/_variables.scss' as *;
.parent-type {
  display: flex;
  align-items: center;
  gap: 0;
}

.child-type {
  margin-left: 3rem;
}

.single-service{
  display: flex;
  align-items: center;
}


p {
  font-size: $font-size-sm;
  margin-bottom: 1rem;
}
</style>