<template>
    <div @click="toggleDropdown" class="market-dropdown" >
      <span class="dropdown-trigger">{{selectMarketText}}</span>
      <div v-if="isOpen" class="markets">
        <span
            v-for="market in markets"
            :key="market.locode"
            :data-value="market.locode"
            :class="['market', { active: selectedMarket === market.name }]"
            @click="selectMarket(market.name)" 
          >
          {{market.name}}
        </span>
      </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const markets = ref(null);
const isOpen = ref(false);
const selectedMarket = ref(null);
const emit = defineEmits(['market-selected']);



const selectMarketText = computed(() => 
  selectedMarket.value ? selectedMarket.value : 'Select a Market'
);

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const selectMarket = (market) => {
  selectedMarket.value = market;
  isOpen.value = false;
  emit('market-selected', market);
};

const fetchMarkets = async () => {
  try {
    const response = await axios.get(
      `${wpApiSettings.root}woo-lalamove/v1/get-city`,
      {headers: { 'X-WP-Nonce': wpApiSettings.nonce }}
    );
    markets.value = response.data;

    console.log('Markets: ', markets.value);
  } catch(error) {
    console.error('Error fetching markets:', error);
  }
}

onMounted(() => {
  fetchMarkets();
});


</script>

<style lang="scss" scoped>
@use '@/css/scss/_variables.scss' as *;



  
.market-dropdown {
  display: flex;
  flex-direction: column;
  height: 600px;
  gap: .5rem;
  cursor: pointer;
  margin: 3%;
  width: 15rem;
  user-select: none;
}

.dropdown-trigger {
  height: 1fr;
  padding: 3%;
  border-radius: 3%;
  background-color: $bg-light;
  font-size: $font-size-sm;
}

.markets {
  display: flex;
  flex-direction: column;
  height: 2fr;
  width: 100%;
  background-color: $bg-light;
  overflow-y: auto;
}

.market {
  padding: 2%;
}

.market:hover{
  border-left: 2px solid $bg-primary;
  background-color: $border-color;
  color: $txt-primary;
}

.market.active {
  border-left: 2px solid $bg-primary;
  background-color: $border-color;
  color: $txt-primary;
}


</style>
