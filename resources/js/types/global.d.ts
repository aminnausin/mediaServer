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

    interface AudioTrack {
        enabled: boolean;
        readonly id: string;
        kind: string;
        readonly label: string;
        language: string;
    }

    interface AudioTrackList extends EventTarget {
        readonly length: number;
        onaddtrack: ((this: AudioTrackList, ev: Event) => any) | null;
        onchange: ((this: AudioTrackList, ev: Event) => any) | null;
        onremovetrack: ((this: AudioTrackList, ev: Event) => any) | null;
        [index: number]: AudioTrack;
        getTrackById(id: string): AudioTrack | null;
    }

    interface HTMLMediaElement {
        readonly audioTracks: AudioTrackList;
    }
}
