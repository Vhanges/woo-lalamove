<template>
  <div class="orders-wrapper">
    <header>
      <h2>ORDERS</h2>
      <EditLocation />
    </header>
    <div class="utility-header">
      <UtilityHeader />
    </div>
    <main>
      <WooOrderTable />
    </main>
  </div>
</template>


<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;

  .orders-wrapper {
    display: flex; 
    flex-direction: column;
    gap: 2vh;
    height: 100%;
    margin: 2rem;

    header {
      display: flex;
      justify-content: space-between;
      height: 10vh;
      width: 100%;
      align-items: center;
    }

    .utility-header {
      height: fit-content;
      width: 100%;
    }

    main {
      height: 60vh;
      max-height: 60vh;
      width: 100%;
    }
  }

  h2 {
    font-size: $font-size-xxl;
    font-weight: $font-weight-regular;
  }




</style>

<script setup>
import { ref, onMounted } from 'vue';

import EditLocation from '../components/Orders/EditLocation.vue';
import WooOrderTable from '../components/Orders/WooOrderTable.vue';
import UtilityHeader from '../components/Utilities/UtilityHeader.vue';


</script>
