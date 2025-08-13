<template>
  <div class="price-breakdown-wrapper">
    <!-- Empty State -->
    <div v-if="!hasValidPrices" class="empty-state">
      <div class="empty-icon">
        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="currentColor"/>
        </svg>
      </div>
      <h3 class="empty-title">Get Your Quote</h3>
      <p class="empty-description">Configure your delivery details to see pricing breakdown</p>
    </div>

    <!-- Price Breakdown -->
    <div v-else class="price-breakdown">
      <header class="breakdown-header">
        <h3 class="breakdown-title">Price Breakdown</h3>
        <span class="currency-badge">{{ priceBreakdown.currency ?? 'PHP' }}</span>
      </header>

      <div class="price-items">
        <!-- Base Fare -->
        <div v-if="priceBreakdown.base" class="price-item">
          <div class="item-info">
            <span class="item-label">Base Fare</span>
            <span class="item-description">Standard delivery fee</span>
          </div>
          <span class="item-price">{{ formatPrice(priceBreakdown.base) }}</span>
        </div>

        <!-- Extra Mileage -->
        <div v-if="priceBreakdown.extraMileage" class="price-item">
          <div class="item-info">
            <span class="item-label">Extra Mileage</span>
            <span class="item-description">Additional distance charges</span>
          </div>
          <span class="item-price">{{ formatPrice(priceBreakdown.extraMileage) }}</span>
        </div>

        <!-- Surcharge -->
        <div v-if="priceBreakdown.surcharge" class="price-item">
          <div class="item-info">
            <span class="item-label">Surcharge</span>
            <span class="item-description">Additional service fees</span>
          </div>
          <span class="item-price">{{ formatPrice(priceBreakdown.surcharge) }}</span>
        </div>
      </div>

      <!-- Total Section -->
      <div class="total-section">
        <div class="total-row">
          <span class="total-label">Total Amount</span>
          <div class="total-amount">
            <span class="total-currency">{{ priceBreakdown.currency ?? 'PHP' }}</span>
            <span class="total-price">{{ formatPrice(priceBreakdown.total) }}</span>
          </div>
        </div>
        <p class="total-note">Final price may vary based on actual distance and delivery conditions</p>
      </div>
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
@use "@/css/scss/_mixins.scss" as *;

.price-breakdown-wrapper {
  width: 100%;
}

.empty-state {
  text-align: center;
  padding: 2rem 1rem;
  
  @include respond-above('md') {
    padding: 3rem 1.5rem;
  }
}

.empty-icon {
  color: rgba($txt-primary, 0.4);
  margin-bottom: 1rem;
  
  svg {
    width: 48px;
    height: 48px;
    
    @include respond-above('md') {
      width: 56px;
      height: 56px;
    }
  }
}

.empty-title {
  @include heading-3;
  color: rgba($txt-primary, 0.8);
  margin-bottom: 0.5rem;
}

.empty-description {
  @include small-text;
  color: rgba($txt-primary, 0.6);
  max-width: 280px;
  margin: 0 auto;
}

.price-breakdown {
  width: 100%;
}

.breakdown-header {
  @include flex-between;
  margin-bottom: 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid $bg-gray;
  
  @include respond-above('md') {
    margin-bottom: 2rem;
    padding-bottom: 1.25rem;
  }
}

.breakdown-title {
  @include heading-3;
  margin: 0;
}

.currency-badge {
  background-color: rgba($bg-primary, 0.1);
  color: $bg-primary;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: $font-size-xs;
  font-weight: $font-weight-medium;
  letter-spacing: 0.05em;
}

.price-items {
  margin-bottom: 1.5rem;
  
  @include respond-above('md') {
    margin-bottom: 2rem;
  }
}

.price-item {
  @include flex-between;
  align-items: flex-start;
  padding: 1rem 0;
  border-bottom: 1px solid rgba($border-color, 0.5);
  
  &:last-child {
    border-bottom: none;
  }
  
  @include respond-above('md') {
    padding: 1.25rem 0;
  }
}

.item-info {
  flex: 1;
  margin-right: 1rem;
}

.item-label {
  display: block;
  @include body-text;
  font-weight: $font-weight-medium;
  margin-bottom: 0.25rem;
}

.item-description {
  @include small-text;
  color: rgba($txt-primary, 0.7);
}

.item-price {
  @include body-text;
  font-weight: $font-weight-medium;
  color: $txt-orange;
  font-size: $font-size-lg;
  white-space: nowrap;
}

.total-section {
  background: linear-gradient(135deg, rgba($bg-primary, 0.05) 0%, rgba($bg-primary, 0.02) 100%);
  border: 2px solid rgba($bg-primary, 0.1);
  border-radius: 12px;
  padding: 1.25rem;
  
  @include respond-above('md') {
    padding: 1.5rem;
  }
}

.total-row {
  @include flex-between;
  align-items: center;
  margin-bottom: 0.75rem;
}

.total-label {
  @include heading-3;
  margin: 0;
  color: $txt-secondary;
}

.total-amount {
  display: flex;
  align-items: baseline;
  gap: 0.25rem;
}

.total-currency {
  font-size: $font-size-md;
  font-weight: $font-weight-medium;
  color: $txt-primary;
}

.total-price {
  font-size: $font-size-xxl;
  font-weight: $font-weight-bold;
  color: $bg-primary;
  line-height: 1;
  
  @include respond-above('md') {
    font-size: clamp(24px, 28px, 32px);
  }
}

.total-note {
  @include small-text;
  color: rgba($txt-primary, 0.7);
  margin: 0;
  text-align: center;
  font-style: italic;
}

// Mobile optimizations
@media (max-width: 767px) {
  .price-breakdown-wrapper {
    font-size: 14px;
  }
  
  .breakdown-header {
    margin-bottom: 1rem;
    padding-bottom: 0.75rem;
  }
  
  .price-item {
    padding: 0.75rem 0;
  }
  
  .item-info {
    margin-right: 0.75rem;
  }
  
  .total-section {
    padding: 1rem;
  }
  
  .total-price {
    font-size: $font-size-xl;
  }
}

// High contrast mode
@media (prefers-contrast: high) {
  .total-section {
    border: 2px solid $border-color;
    background: $bg-high-light;
  }
  
  .currency-badge {
    border: 1px solid $bg-primary;
  }
}

// Print styles
@media print {
  .price-breakdown {
    break-inside: avoid;
  }
  
  .total-section {
    background: transparent;
    border: 2px solid #000;
  }
}
</style>
