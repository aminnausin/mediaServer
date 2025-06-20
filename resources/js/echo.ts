import Pusher from 'pusher-js';

window.Pusher = Pusher;

const reverbConfig = JSON.parse(document.getElementById('reverb-config')?.dataset?.reverbConfig ?? '');

document.getElementById('reverb-config')?.remove();

export const echoInstance = null;

export const EchoConfig: {
    broadcaster: 'reverb';
    key: any;
    wsHost: any;
    wsPort: any;
    wssPort: any;
    forceTLS: boolean;
    enabledTransports: string[];
} = {
    broadcaster: 'reverb',
    key: reverbConfig.key ?? import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: reverbConfig.host ?? import.meta.env.VITE_REVERB_HOST,
    wsPort: reverbConfig.port ?? import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: reverbConfig.port ?? import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (reverbConfig.scheme ?? import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
};
