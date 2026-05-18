// Volvo Dealers Vue - Main Entry Point
// Uses global Vue objects from CDN (no ES module imports)

const { createApp } = Vue;
const { createRouter, createWebHistory } = VueRouter;
const { createI18n } = VueI18n;

// Import components using relative paths
import App from './App.vue';
import Home from './views/Home.vue';
import Page from './views/Page.vue';
import CarModel from './views/CarModel.vue';

// Import i18n configuration
import i18n from './i18n/index.js';

// Import styles
import './styles/main.scss';

// Create router
const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', component: Home },
    { path: '/:slug', component: Page },
    { path: '/modele/:model', component: CarModel }
  ]
});

// Create app
const app = createApp(App);

// Use plugins
app.use(router);
app.use(i18n);

// Mount app
app.mount('#app');
