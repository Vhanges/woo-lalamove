import { createApp } from 'vue';
import App from './vue/App.vue';
import router from './vue/router';

console.log("Vue app is mounting...");

const app = createApp(App);
app.use(router);
app.mount('#lalamove-app');