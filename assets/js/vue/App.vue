<template>
    <div id="app">
      <h1>Testing</h1>
      <Navbar />
      <router-view /> <!-- Render the current route's component -->
    </div>
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
  