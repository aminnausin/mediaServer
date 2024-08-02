import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router'
import App from './App.vue';

const application = createApp(App);
const pinia = createPinia()

application.use(router);
application.use(pinia);
application.config.globalProperties.window = window;
application.mount('#app')