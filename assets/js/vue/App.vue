<template>
      <div id="toast-target"></div>
      <Navbar />
      <router-view :key="$route.path"/>
</template>

<script setup>
import Navbar from './components/Navbar.vue';


  </script>

<style lang="scss">
  @use '@/css/scss/_variables.scss' as *;


</style>

  