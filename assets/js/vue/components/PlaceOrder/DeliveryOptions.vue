<template>
  <div class="additional-request">
    <p class="header">DELIVERY OPTIONS</p>
    <div class="input-group">
      <label for="proof_of_delivery">Proof Of Delivery</label>
      <input type="checkbox" name="proof_of_delivery" id="" />
    </div>

    <div class="input-group">
      <label for="optimize_route">Optimize Route</label>
      <input type="checkbox" name="optimize_route" id="" />
    </div>
  </div>
</template>

<script setup></script>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;

.additional-request {
  display: flex;
  flex-direction: column;
  gap: 10%;
  font-size: $font-size-sm;

  .input-group {
    width: auto;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
  }
}

.header {
  font-size: $font-size-xs;
  font-weight: $font-weight-bold;
}
</style>
