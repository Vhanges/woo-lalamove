<template>
    <div @click="toggleDropdown" class="market-dropdown" >
      <span class="dropdown-trigger">{{selectedMarketLabel}}</span>
      <div v-if="isOpen" class="markets">
        <span
            v-for="market in markets"
            :key="market.locode"
            :data-value="market.locode"
            :class="['market', { active: selectedMarket === market.name }]"
            @click="selectMarket(market.name, market.services)" 
          >
          {{market.name}}
        </span>
      </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { eventBus } from '../../../../utils/eventBus.js';
import axios from 'axios';

const markets = ref(null);
const isOpen = ref(false);
const selectedMarket = ref(null);



const selectedMarketLabel = computed(() => 
  selectedMarket.value ? selectedMarket.value : 'Choose your Market'
);

const toggleDropdown = () => {
  isOpen.value = !isOpen.value;
};

const selectMarket = (name, services) => {
  selectedMarket.value = name;
  isOpen.value = false;
  marketSerivces(services);
};


// Send the services data to placeorder component
const marketSerivces = (services) => {
 // Sort the services by load value in ascending order
  const sortedServices = services.sort((a, b) => a.load.value - b.load.value);

  // Emit the sorted services
  eventBus.emit('market-services', sortedServices);

  // Log the sorted services
  console.log('Market Services:', sortedServices);
}

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
  height: 300px;
  gap: .5rem;
  cursor: pointer;
  margin: 1%;
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
