import './bootstrap';

import { VueQueryPlugin } from '@tanstack/vue-query';
import { createPinia } from 'pinia';
import { createApp } from 'vue';

import ToastService from './service/toastService';
import router from './router';
import App from './App.vue';

const application = createApp(App);
const pinia = createPinia();

application.use(router);
application.use(pinia);
application.use(ToastService);
application.use(VueQueryPlugin);
application.mount('#app');
