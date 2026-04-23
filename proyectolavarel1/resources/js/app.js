import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';

// Make alertify available globally
import alertify from 'alertifyjs';
window.alertify = alertify;

const app = createApp(App);
app.mount('#appSistema');
