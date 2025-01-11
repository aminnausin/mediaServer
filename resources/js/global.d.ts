import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

declare global {
    interface Window {
        axios: AxiosStatic;
        Pusher: typeof Pusher;
        Echo: Echo<'reverb'>;
    }
}
