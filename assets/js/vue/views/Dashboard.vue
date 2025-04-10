<template>
  <div class="dashboard-container">
    <DashboardNavBar/>
    <router-view :key="$route.path" class="router-dashboard-view"/>
  </div>
</template>

<style lang="scss" scoped>
 .dashboard-container{
  height: fit-content;
  padding: 2rem;

 }
 .router-dashboard.view{
  height: fit-content;
 }
</style>
<script setup>
import { ref, onMounted } from 'vue';

import DashboardNavBar from '../components/Dashboard/DashboardNavBar.vue';
</script>