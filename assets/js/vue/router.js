import { createRouter, createWebHashHistory } from 'vue-router';

const Dashboard = () => import('@views/Dashboard.vue');
const Orders = () => import('@views/Orders.vue');
const PlaceOrder = () => import('@views/PlaceOrder.vue');
const Settings = () => import('@views/Settings.vue');
const Records = () => import('@views/Records.vue');
const Info = () => import('@views/Info.vue'); 

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    { 
      path: '/dashboard',
      component: Dashboard,
      name: 'dashboard'
    },
    { 
      path: '/orders',
      component: Orders,
      name: 'orders'
    },
    { 
      path: '/place-order',
      component: PlaceOrder,
      name: 'place-order'
    },
    { 
      path: '/settings',
      component: Settings,
      name: 'settings'
    },
    { 
      path: '/records',
      component: Records,
      name: 'records'
    },
    { 
      path: '/info',
      component: Info,
      name: 'info'
    }
  ]
});

export default router;
