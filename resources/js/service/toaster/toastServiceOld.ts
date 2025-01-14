import type { Plugin } from 'vue';
import { ToastSymbol } from '../../composables/useToast';
import ToastEventBus from '../toastEventBus';
import type { ToastOptions } from '@/types/pinesTypes';
// import { toast } from './state';

// export { toast };

export default {
    // install: (app: any) => {
    // const ToastService = {
    //     add: (title: string, options: ToastOptions) => {
    //         ToastEventBus.emit('add', { title, options });
    //     },
    //     remove: (id: string) => {
    //         ToastEventBus.emit('close', id);
    //     },
    // };
    // app.config.globalProperties.$toast = ToastService;
    // app.provide(ToastSymbol, ToastService);
    // app.component('ToastController');
    // },
};
