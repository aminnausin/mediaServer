import './bootstrap';

import { VueQueryPlugin } from '@tanstack/vue-query';
import { createPinia } from 'pinia';
import { createApp } from 'vue';

import router from './router/index';
import App from './App.vue';

const application = createApp(App);
const pinia = createPinia();

application.use(router);
application.use(pinia);
application.use(VueQueryPlugin);
application.mount('#app');
