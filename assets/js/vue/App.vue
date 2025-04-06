<template>
      <div id="toast-target"></div>
      <Navbar />
      <router-view :key="$route.path" class="router-view"/>
</template>

<script setup>
import Navbar from './components/Navbar.vue';


  </script>

<style lang="scss">



</style>

  