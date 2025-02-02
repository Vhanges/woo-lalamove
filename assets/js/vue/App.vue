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

  