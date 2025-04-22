<template>
    <div class="filter-status-dropdown">
      <span @click="toggleDropdown" class="dropdown-trigger">{{ selectedStatuesLabel }}</span>
      <div v-if="isOpen" class="markets">
        <span
          v-for="market in markets"
          :key="market.locode"
          :data-value="market.locode"
          :class="['market', { active: selectedStatus === market.name }]"
          @click.stop="selectMarket(market.name, market.services)"
        >
          {{ market.name }}
        </span>
      </div>
    </div>
  </template>
  
  <script setup>
  import { ref, computed, onMounted } from 'vue';
  
  const markets = ref([]);
  const isOpen = ref(false);
  const selectedStatus = ref(null);
  
  const selectedStatuesLabel = computed(() =>
    selectedStatus.value ? selectedStatus.value : 'Filter by Statusessssssssss'
  );
  
  const toggleDropdown = () => {
    isOpen.value = !isOpen.value;
  };
  
  const selectMarket = (name, services) => {
    selectedStatus.value = name;
    isOpen.value = false;
    marketServices(services);
  };
  
  const marketServices = (services) => {
    const sortedServices = services.sort((a, b) => a.load.value - b.load.value);
    eventBus.emit('market-services', sortedServices);
    console.log('Market Services:', sortedServices);
  };
  
  const fetchMarkets = async () => {
    try {
      // Replace with API or use fallback data
      markets.value = [
        {
          locode: 'MKT001',
          name: 'Market One',
          services: [
            { name: 'Service A', load: { value: 10 } },
            { name: 'Service B', load: { value: 5 } },
          ],
        },
        {
          locode: 'MKT002',
          name: 'Market Two',
          services: [
            { name: 'Service C', load: { value: 20 } },
            { name: 'Service D', load: { value: 15 } },
          ],
        },
        {
          locode: 'MKT003',
          name: 'Market Three',
          services: [
            { name: 'Service E', load: { value: 8 } },
            { name: 'Service F', load: { value: 12 } },
          ],
        },
      ];
    } catch (error) {
      console.error('Error fetching markets:', error);
      markets.value = []; // Fallback data
    }
  };
  
  onMounted(fetchMarkets);
  </script>
  
  <style lang="scss" scoped>
  @use '@/css/scss/_variables.scss' as *;
  
  .filter-status-dropdown {
    display: flex;
    flex-direction: column;
    height: 2rem;
    width: 10rem;
    gap: 0.5rem;
    cursor: pointer;
    user-select: none;
    border: 2px solid $border-color;
    border-radius: 5px;
    background-color: $bg-high-light;
  }
  
  .dropdown-trigger {
    height: 100%;
    display: flex;
    align-items: center;
    padding: 3%;
    border-radius: 3%;
    background-color: $bg-high-light;
    font-size: $font-size-sm;
  }
  
  .markets {
    display: flex;
    flex-direction: column;
    max-height: 8rem; 
    width: 100%;
    background-color: $bg-high-light;
    border: 2px solid $border-color;
    border-radius: 3%;
  }
  
  .market {
    padding: 2%;
    font-size: $font-size-sm;
  }
  
  .market:hover {
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