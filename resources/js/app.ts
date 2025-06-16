import '@/bootstrap';

import { VueQueryPlugin } from '@tanstack/vue-query';
import { queryClient } from '@/service/vue-query';
import { createPinia } from 'pinia';
import { createApp } from 'vue';

import nProgress from 'nprogress';
import router from '@/router/index';
import App from '@/App.vue';

import 'nprogress/nprogress.css';

const application = createApp(App);
const pinia = createPinia();

nProgress.configure({
    showSpinner: false,
    parent: '#nprogress-container',
});

application.use(router);
application.use(pinia);
application.use(VueQueryPlugin, { queryClient });

application.mount('#app');
