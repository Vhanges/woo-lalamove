<template>
  <div class="dashboard-container">
    <DashboardNavBar/>

    <router-view v-slot="{ Component }">
      <div class="router-dashboard-view">
        <component :is="Component" />
      </div>
    </router-view>
  </div>
</template>

<script setup>
import DashboardNavBar from '../components/Dashboard/DashboardNavBar.vue';
</script>

<style lang="scss" scoped>
.dashboard-container {
  height: fit-content;
  padding: 2rem;
}

.router-dashboard-view {
  height: fit-content;
}
</style>
