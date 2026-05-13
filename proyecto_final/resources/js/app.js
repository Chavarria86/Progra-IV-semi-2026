import './bootstrap';
import { createApp } from 'vue';
import DashboardApp from './components/DashboardApp.vue';

// Si existe el elemento con ID 'app' (que creamos en app.blade.php), montamos la app Vue
const appElement = document.getElementById('app');
if (appElement) {
    const app = createApp(DashboardApp);
    app.mount('#app');
}
