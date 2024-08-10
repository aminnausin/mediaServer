import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import ToastService from './service/toastService';
import router from './router'
import App from './App.vue';

const application = createApp(App);
const pinia = createPinia()

application.use(router);
application.use(pinia);
application.use(ToastService);
application.mount('#app')