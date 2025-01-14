import './bootstrap';

import { VueQueryPlugin } from '@tanstack/vue-query';
import { createPinia } from 'pinia';
import { createApp } from 'vue';

import router from './router';
import App from './App.vue';
// import toastService from './service/toaster/toastService.ts';

const application = createApp(App);
const pinia = createPinia();

application.use(router);
application.use(pinia);
// application.use(toastService);
application.use(VueQueryPlugin);
application.mount('#app');
