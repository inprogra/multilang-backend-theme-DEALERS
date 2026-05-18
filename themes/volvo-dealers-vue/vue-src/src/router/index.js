import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Page from '../views/Page.vue'
import CarModel from '../views/CarModel.vue'

const routes = [
  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/:slug',
    name: 'Page',
    component: Page,
    props: true
  },
  {
    path: '/samochody/:slug',
    name: 'CarModel',
    component: CarModel,
    props: true
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
