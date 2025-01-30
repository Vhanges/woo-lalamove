import { createRouter, createWebHashHistory } from 'vue-router';

// Import the views (pages)
import Dashboard from './views/Dashboard.vue';
import Orders from './views/Orders.vue';
import Settings from './views/Settings.vue';
import PlaceOrder from './views/PlaceOrder.vue';
import Records from './views/Records.vue';
import Navbar from './components/Navbar.vue';

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    { path: '/', component: Navbar, name: 'Navbar' },
    { path: '/dashboard', component: Dashboard, name: 'dashboard' },
    { path: '/orders', component: Orders, name: 'orders' },
    { path: '/settings', component: Settings, name: 'settings' },
    { path: '/records', component: Records, name: 'records' },
  ]
});

export default router;
