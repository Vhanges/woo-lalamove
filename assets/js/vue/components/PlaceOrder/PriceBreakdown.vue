<template>
  <div class="price-breakdown" v-if="hasValidPrices">
    <p class="header">PRICE BREAKDOWN</p>

    <div v-if="priceBreakdown.base" class="price-group">
      <p>Base Fare</p>
      <p class="price-num">{{ priceBreakdown.currency ?? '' }} {{ formatPrice(priceBreakdown.base) ?? 0 }}</p>
    </div>

    <div v-if="priceBreakdown.extraMileage" class="price-group">
      <p>Extra Mileage</p>
      <p class="price-num">{{ priceBreakdown.currency ?? '' }} {{ formatPrice(priceBreakdown.extraMileage) ?? 0 }}</p>
    </div>

    <div v-if="priceBreakdown.surcharge" class="price-group">
      <p>Surcharge</p>
      <p class="price-num">{{ priceBreakdown.currency ?? '' }} {{ formatPrice(priceBreakdown.surcharge) ?? 0 }}</p>
    </div>

    <div class="total-container">
      <p>Total:</p>
      <p>{{ priceBreakdown.currency ?? '' }} {{ formatPrice(priceBreakdown.total) ?? 0 }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useLalamoveStore } from '../../store/lalamoveStore';
import { storeToRefs } from 'pinia';

const lalamove = useLalamoveStore();
const { priceBreakdown } = storeToRefs(lalamove);

const hasValidPrices = computed(() =>
  Boolean(priceBreakdown.value.base || priceBreakdown.value.extraMileage || priceBreakdown.value.surcharge)
);


const formatPrice = (value) => {
  if (typeof value !== 'number' && isNaN(parseFloat(value))) return '0.00';
  return parseFloat(value).toLocaleString('en-PH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  });
};


</script>

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

  .total-container {
      display: flex; 
      justify-content: space-around;
      align-items: center;
      background-color: $bg-high-light;
      border: 1px solid $border-color;
      border-radius: 5px;

      & > p:first-child{
          font-size: $font-size-sm;
          font-weight: $font-weight-bold;
      }

      & > p:last-child{
          color: $txt-orange;
          font-size: $font-size-xxl;
          font-weight: $font-weight-bold;
      }

  }
}

.header {
  font-size: $font-size-xs;
  font-weight: $font-weight-bold;
}
</style>
