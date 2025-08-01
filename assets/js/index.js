  import { createApp } from 'vue';
  import App from './vue/App.vue';
  import router from './vue/router';
  import Vue3Toastify from 'vue3-toastify';
  import Vue3Dragscroll from 'vue3-dragscroll'
  import { createPinia } from 'pinia';
  import 'vue3-toastify/dist/index.css';


  const app = createApp(App);
  const pinia = createPinia();

  
  app.use(Vue3Toastify, {
    autoClose: 3000,
    style: {
      top: '5%',
    },
  });
  
  app.use(Vue3Dragscroll);
  app.use(router);
  app.use(pinia);
  pinia.devtools = true;
  pinia.use(({ store }) => {
  store.$onAction(({ name }) => {
    console.log(`[Pinia] Action: ${name}`);
    });
  });
  app.mount('#lalamove-app');