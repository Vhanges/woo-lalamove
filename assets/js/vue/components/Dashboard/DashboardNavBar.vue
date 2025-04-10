<template>
    <div class="dashboard-navbar-container">
      <h2>Dashboard</h2>
      
      <nav>
        <router-link 
          to="/spending-dashboard" 
          class="nav-item"
          active-class="active-link"
        >
          <span class="nav-item__label">Spending</span>
        </router-link>
  
        <router-link 
          to="/orders-dashboard" 
          class="nav-item"
          active-class="active-link"
        >
          <span class="nav-item__label">Orders</span>
        </router-link>
      </nav>
    </div>
  </template>

<script setup>

</script>

<style lang="scss" scoped>
@use '@/css/scss/_variables.scss' as *;


.dashboard-navbar-container {
    height: fit-content;
    width: 100%;

    h2 {
        font-size: $font-size-xxl;
        font-weight: $font-weight-regular;
    }

    nav {
        display: flex;
        flex-direction: row;
        width: 80%;
        border-bottom: 1px solid $border-color;

        .nav-item {
            color: $txt-primary;
            font-size: $font-size-sm;
            padding: 1rem;
            font-weight: 500;
            letter-spacing: 0;
            white-space: nowrap;
            text-decoration: none;

            &.active-link { 
                color: $bg-primary;
                border-bottom: 1px solid $bg-primary;
            }
        }
    }
}




</style>