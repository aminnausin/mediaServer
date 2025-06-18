import type { NullConnector, PusherConnector, SocketIoConnector } from 'laravel-echo/dist/connector';
import type { Component, DefineComponent } from 'vue';

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

export type TaskStatus = 'pending' | 'processing' | 'completed' | 'cancelled' | 'failed' | 'incomplete';

export const MediaType = {
    VIDEO: 0,
    AUDIO: 1,
} as const;

export type MediaTypeValue = (typeof MediaType)[keyof typeof MediaType];

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
    iconStyle?: string;
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
    hidden?: boolean;
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

export declare type FieldType = 'text' | 'textArea' | 'number' | 'date' | 'url' | 'multi' | 'select' | 'password';

export interface FormField {
    name: string;
    text?: string;
    subtext?: string;
    type: FieldType;
    required?: boolean;
    value?: any;
    placeholder?: string;
    default?: any;
    min?: number;
    max?: number;
    class?: string;
    disabled?: boolean;
    autocomplete?: string;
    ariaAutocomplete?: 'list' | 'none' | 'inline' | 'both';
}

export interface SelectItem {
    id: number;
    name: string;
    relationships?: any;
}

export declare type SortOption = {
    title: string;
    value: string;
    disabled?: boolean;
};

export interface TableProps<T> {
    useToolbar?: boolean;
    usePagination?: boolean;
    usePaginationIcons?: boolean;
    useGrid?: string;
    data: T[];
    row: DefineComponent<any, any, any> | Component;
    rowAttributes?: Record<string, any>;
    loading?: boolean;
    clickAction?: (id: number, ...args: any[]) => void;
    otherAction?: (...args: any[]) => void;
    sortAction?: (sortKey: keyof T, direction: 1 | -1) => void;
    sortingOptions?: SortOption[];
    itemsPerPage?: number;
    itemName?: string;
    searchQuery?: string;
    selectedID?: number | string | null;
    tableStyles?: string;
    startAscending?: boolean;
    paginationClass?: string;
    maxVisiblePages?: number;
    noResultsMessage?: string;
}

export interface DropdownMenuItem {
    name: string;
    url?: string;
    text: string;
    title?: string;
    icon?: Component;
    disabled?: boolean;
    hidden?: boolean;
    external?: boolean;
    action?: () => void;
    shortcut?: string;
    iconStrokeWidth?: number;
}

export interface AppManifest {
    version: string;
    commit: string | null;
}

export interface BreadCrumbItem {
    name: string;
    url: string;
    icon?: Component;
}

export interface LrcLibResult {
    id: number;
    name: string;
    trackName: string;
    artistName: string;
    albumName: string;
    duration: number;
    syncedLyrics?: string;
    plainLyrics?: string;
}

export interface RawLyricItem {
    text: string;
    time?: number;
    percentage?: number;
}

export interface LyricItem {
    text: string;
    time: number;
    percentage: number;
}

export interface SidebarTabItem {
    name: string;
    title?: string;
    description?: string;
    info?: { value: string; icon?: Component };
    icon?: Component;
    disabled?: boolean;
}
