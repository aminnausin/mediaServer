import type { NullConnector, PusherConnector, SocketIoConnector } from 'laravel-echo/dist/connector';
import type { Component } from 'vue';

import type {
    NullChannel,
    NullEncryptedPrivateChannel,
    NullPresenceChannel,
    NullPrivateChannel,
    PusherChannel,
    PusherEncryptedPrivateChannel,
    PusherPresenceChannel,
    PusherPrivateChannel,
    SocketIoChannel,
    SocketIoPresenceChannel,
    SocketIoPrivateChannel,
} from 'laravel-echo/dist/channel';

export interface HttpResponse {
    success?: 'true' | 'false';
    status?: string;
    message?: string | null;
    data: string | number | string[] | number[] | null;
}

export interface PulseResponse {
    cache: {
        allTime: number;
        allRunAt: string;
        allCacheInteractions: {
            hits: number;
            misses: number;
        };
        keyTime: number;
        keyRunAt: string;
        cacheKeyInteractions: [];
    };
    exceptions?: any;
    queues?: {
        queues: { [key: string]: PulseQueueResponse };
        showConnection: boolean;
        time: number;
        runAt: string;
        config: {
            enabled: boolean;
            sample_rate: number;
            ignore: any[];
        };
    };
    requests?: {
        requests: { [key: string]: PulseRquestsResponse };
        showConnection: boolean;
        time: number;
        runAt: string;
        config: {
            sample_rate: number;
            record_informational: boolean;
            record_successful: boolean;
            record_redirection: boolean;
            record_client_error: boolean;
            record_server_error: boolean;
        };
    };
    servers?: {
        servers: { [key: string]: PulseServerResponse };
        time: number;
        runAt: string;
    };
    slow_jobs?: any;
    slow_outgoing_requests?: any;
    slow_queries?: any;
    slow_requests?: any;
    usage: {
        userRequestCounts: PulseUsageResponse[];
        slowRequestsCounts: PulseUsageResponse[];
        jobsCounts: PulseUsageResponse[];
        time: number;
        runAt: string;
        userRequestsConfig: {
            enabled: boolean;
            sample_rate: number;
            ignore: any[];
        };
        slowRequestsConfig: {
            enabled: boolean;
            sample_rate: number;
            threshold: 1000;
            ignore: any[];
        };
        jobsConfig: {
            enabled: boolean;
            sample_rate: number;
            ignore: any[];
        };
    };
}

export interface PulseServerResponse {
    name: string;
    cpu_current: number;
    cpu: { [key: string]: string };
    memory_current: number;
    memory_total: number;
    memory: { [key: string]: string };
    storage: { directory: string; total: number; used: number }[];
    updated_at: string;
    recently_reported: boolean;
    runAt: string;
    time: number;
}

export interface PulseQueueResponse {
    [key: string]: { [key: string]: string | null };
}

export interface PulseRquestsResponse {
    [key: string]: { [key: string]: string | null };
}

export interface PulseUsageResponse {
    key: number;
    user: {
        name: string;
        extra: string;
        avatar?: string;
    };
    count: number;
}

export interface SiteAnalyticsResponse {
    title: string;
    count: number;
    change: number;
    change_pct: number;
    prev: number;
}

export interface TaskStatsResponse {
    avg_duration: number;
    avg_fail_rate: number;
    count_cancelled: number;
    avg_count_sub_tasks: number;
    count_tasks: number;
    count_running: number;
    count_subtasks: number;
}

export interface FormField {
    name: string;
    text: string;
    type?: 'text' | 'url' | 'textArea' | 'date' | 'number' | 'multi' | 'select';
    value?: any;
    subtext?: string;
    default?: any;
    class?: string;
}

export type TaskStatus = 'pending' | 'processing' | 'completed' | 'cancelled' | 'failed' | 'incomplete';

export interface ContextMenuItem {
    text?: string;
    shortcut?: string;
    url?: string;
    external?: boolean;
    action?: () => void;
    style?: string;
    selectedStyle?: string;
    selected?: boolean;
    disabled?: boolean;
    icon?: Component;
}

export interface ContextMenu {
    disabled?: boolean;
    style?: string;
    itemStyle?: string;
    items?: ContextMenuItem[];
}

export interface PopoverItem {
    text?: string;
    title?: string;
    shortcut?: string;
    action?: () => void;
    style?: string;
    selectedStyle?: string;
    selectedIconStyle?: string;
    selected?: boolean;
    disabled?: boolean;
    icon?: Component;
    selectedIcon?: Component;
}

export interface PopoverSlider {
    text?: string;
    title?: string;
    shortcut?: string;
    action?: (...args: any[]) => void;
    wheelAction?: (event: WheelEvent) => void;
    style?: string;
    disabled?: boolean;
    icon?: Component;
    min?: number;
    max?: number;
    step?: number;
}

export declare type Broadcaster = {
    reverb: {
        connector: PusherConnector;
        public: PusherChannel;
        private: PusherPrivateChannel;
        encrypted: PusherEncryptedPrivateChannel;
        presence: PusherPresenceChannel;
    };
    pusher: {
        connector: PusherConnector;
        public: PusherChannel;
        private: PusherPrivateChannel;
        encrypted: PusherEncryptedPrivateChannel;
        presence: PusherPresenceChannel;
    };
    'socket.io': {
        connector: SocketIoConnector;
        public: SocketIoChannel;
        private: SocketIoPrivateChannel;
        encrypted: never;
        presence: SocketIoPresenceChannel;
    };
    null: {
        connector: NullConnector;
        public: NullChannel;
        private: NullPrivateChannel;
        encrypted: NullEncryptedPrivateChannel;
        presence: NullPresenceChannel;
    };
    function: {
        connector: any;
        public: any;
        private: any;
        encrypted: any;
        presence: any;
    };
};

export declare type SortDir = 1 | -1;
