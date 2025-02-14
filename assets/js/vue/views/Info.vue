<template>
  <div>
    <h2>Info</h2>
    <p>Manage and track your shipments here.</p>
    <div v-if="cities">
      <h3>Available Cities:</h3>
      <ul>
        <li v-for="city in cities" :key="city.locode">{{ city.name }}</li>
      </ul>
    </div>
    <div v-else>
      Loading cities...
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import CryptoJS from 'crypto-js';

const cities = ref(null);

const fetchCities = async () => {
  try {
    const timestamp = String(Date.now());
    const rawSignature = `${timestamp}\r\nGET\r\n/v3/cities\r\n\r\n`;
    const apiSecret = 'sk_test_ry3Koh7+wYmbKy3ADj03QT7MWszG1tjRYjDtHfadM9KD0IydKKO9ceQ5dfDqHYKT';
    const apiKey = 'pk_test_7e46272f5929b740c3be63781e31c2d9';

    const signature = CryptoJS.HmacSHA256(rawSignature, apiSecret).toString(CryptoJS.enc.Hex);
    const token = `${apiKey}:${timestamp}:${signature}`;

    const config = {
      method: 'get',
      url: 'https://rest.sandbox.lalamove.com/v3/cities',
      headers: { 
        'Content-Type': 'application/json', 
        'Authorization': `hmac ${token}`, 
        'Market': 'PH'
      }
    };

    const response = await axios.request(config);
    cities.value = response.data.data;
    console.log('Cities:', cities.value); 
  } catch (error) {
    console.error('Error fetching cities:', error);
  }
};

onMounted(() => {
  fetchCities();
});
</script>
