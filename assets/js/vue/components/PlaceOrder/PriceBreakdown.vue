<template>
  <div class="price-breakdown">
    <p class="header">PRICE BREAKDOWN</p>
    <div class="price-group">
      <p>Base Fare</p>
      <p class="price-num">0</p>
    </div>
    <div class="price-group">
      <p>Extra Mileage</p>
      <p class="price-num">0</p>
    </div>
    <div class="price-group">
      <p>Surcharge</p>
      <p class="price-num">0</p>
    </div>
  </div>
</template>

<script setup></script>

<style lang="scss" scoped>
@use "@/css/scss/_variables.scss" as *;

.price-breakdown {
  grid-area: 10 / 2 / 13 / 3;

  .price-group {
    display: flex;
    justify-content: space-between;
    align-items: center;

    & > * {
      margin: 0 0 1rem 0;
    }

    & > p:first-child {
      font-size: $font-size-sm;
    }

    .price-num {
      color: $txt-orange;
      font-size: $font-size-lg;
      font-weight: $font-weight-medium;
    }
  }
}

.header {
  font-size: $font-size-xs;
  font-weight: $font-weight-bold;
}
</style>
