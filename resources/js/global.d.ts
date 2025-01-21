import type { Broadcaster } from '@/types/types';

import Pusher from 'pusher-js';
import Echo from 'laravel-echo';

declare global {
    interface Window {
        axios: AxiosStatic;
        Pusher: typeof Pusher;
        Echo: Echo<keyof Broadcaster> | null;
    }
}
