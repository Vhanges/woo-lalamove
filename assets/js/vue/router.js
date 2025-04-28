import { createRouter, createWebHashHistory } from 'vue-router';

const Dashboard = () => import('@views/Dashboard.vue');
const OrdersDashboard = () => import('./components/Dashboard/OrdersDashboard.vue'); 
const SpendingDashboard = () => import('./components/Dashboard/SpendingDashboard.vue');
const Orders = () => import('@views/Orders.vue');
const PlaceOrder = () => import('@views/PlaceOrder.vue');
const Settings = () => import('@views/Settings.vue');
const Records = () => import('@views/Records.vue');
const RecordsDetails = () => import('@views/RecordsDetails.vue');
const Info = () => import('@views/Info.vue'); 

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    { 
      path: '/',
      component: PlaceOrder,
      name: 'records'
    },
    { 
      path: '/place-order',
      component: PlaceOrder,
      name: 'place-order'
    },
    { 
      path: '/dashboard',
      component: Dashboard,
      name: 'dashboard',
      children: [
        {
          path: '',
          component: SpendingDashboard,
          name: 'spending-dashboard',
        }, 
        {
          path: '/spending-dashboard',
          component: SpendingDashboard, 
        },
        {
          path: '/orders-dashboard',
          component: OrdersDashboard, 
        },
      ],
    },
    { 
      path: '/orders',
      component: Orders,
      name: 'orders'
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
      path: '/records-details/:lala_id/:wc_id',
      component: RecordsDetails,
      name: 'records-details',
      props: true,
    },
    { 
      path: '/info',
      component: Info,
      name: 'info'
    },
  ]
});

export default router;
