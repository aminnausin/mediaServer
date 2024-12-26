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

// queued: { [key: string]: string | null };
// processing: { [key: string]: string | null };
// processed: { [key: string]: string | null };
// released: { [key: string]: string | null };
// failed: { [key: string]: string | null };

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

export interface ChartData {
    labels: string[];
}

export interface ChartOptions {}
