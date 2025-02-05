<template>
      <Navbar />
      <router-view :key="$route.path"/>
</template>
  
  <script>
  
  import Navbar from './components/Navbar.vue';
  
  export default {
    name: 'App',
    components: {
      Navbar,
    },
  };
  </script>

<style lang="scss">
  @use '@/css/scss/_variables.scss' as *;
</style>

  