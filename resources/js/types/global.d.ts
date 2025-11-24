import type { Broadcaster } from '@/types/types';

import Pusher from 'pusher-js';
import Echo from 'laravel-echo';

interface PlausibleEvent {
    (event: 'pageview' | string, options?: { u?: string; props?: Record<string, any> }): void;
}

declare global {
    interface Window {
        axios: AxiosStatic;
        Pusher: typeof Pusher;
        Echo: Echo<keyof Broadcaster> | null;
        plausible?: PlausibleEvent;
    }
}
