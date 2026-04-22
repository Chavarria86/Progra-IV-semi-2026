import { createRouter, createWebHistory } from 'vue-router';
import Pasantes from '../components/Pasantes.vue';
import Supervisores from '../components/Supervisores.vue';

const routes = [
    {
        path: '/',
        name: 'home',
        redirect: '/pasantes'
    },
    {
        path: '/pasantes',
        name: 'pasantes',
        component: Pasantes
    },
    {
        path: '/supervisores',
        name: 'supervisores',
        component: Supervisores
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
