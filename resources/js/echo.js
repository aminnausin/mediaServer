import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    // authorizer: (channel, options) => {
    //     return {
    //         authorize: (socketId, callback) => {
    //             axios
    //                 .post('/api/broadcasting/auth', {
    //                     socket_id: socketId,
    //                     channel_name: channel.name,
    //                 })
    //                 .then((response) => {
    //                     callback(false, response.data);
    //                 })
    //                 .catch((error) => {
    //                     callback(true, error.response);
    //                 });
    //         },
    //     };
    // },
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
