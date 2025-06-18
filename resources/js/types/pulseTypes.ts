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
        cacheKeyInteractions: PulseCacheKeyInteractionResponse[];
        config: {
            enabled: boolean;
            groups: { [key: string]: string };
            ignore: string[];
            sample_rate: number;
        };
    };
    exceptions: {
        config: {
            enabled: boolean;
            sample_rate: number;
            location: boolean;
            ignore: string[];
        };
        exceptions: PulseExceptionsResponse[];
        time: number;
        runAt: string;
    };
    queues: {
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
    requests: {
        requests: { [key: string]: PulseRequestResponse };
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
    servers: {
        servers: { [key: string]: PulseServerResponse };
        time: number;
        runAt: string;
    };
    slow_jobs: {
        config: {
            enabled: boolean;
            ignore: string[];
            sample_rate: number;
            threshold: number;
        };
        runAt: string;
        time: number;
        slowJobs: PulseSlowJobResponse[];
    };
    slow_outgoing_requests: {
        config: {
            enabled: boolean;
            groups: string[];
            ignore: string[];
            sample_rate: number;
            threshold: number;
        };
        runAt: string;
        time: number;
        slowOutgoingRequests: PulseSlowOutgoingRequestResponse[];
    };
    slow_queries: {
        config: {
            enabled: boolean;
            ignore: string[];
            location: boolean;
            max_query_length: number | null;
            sample_rate: number;
            threshold: number;
        };
        runAt: string;
        time: number;
        slowQueries: PulseSlowQueryResponse[];
    };
    slow_requests: {
        config: {
            threshold: number;
            sample_rate: number;
        };
        time: number;
        runAt: string;
        slowRequests: PulseSlowRequestResponse[];
    };
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

export interface PulseCacheKeyInteractionResponse {
    key: string;
    hits: number;
    misses: number;
}

export interface PulseQueueResponse {
    [key: string]: { [key: string]: string | null };
}

export interface PulseRequestResponse {
    [key: string]: { [key: string]: string | null };
}

export interface PulseSlowRequestResponse {
    action: string;
    count: number;
    method: string;
    slowest: number;
    threshold: number;
    uri: string;
}

export interface PulseSlowJobResponse {
    job: string;
    count: number;
    slowest: number;
    threshold: number;
}

export interface PulseSlowQueryResponse {
    count: number;
    location: string;
    slowest: number;
    sql: string;
    threshold: number;
}

export interface PulseSlowOutgoingRequestResponse {
    count: number;
    method: string;
    slowest: number;
    threshold: number;
    uri: string;
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

export interface PulseExceptionsResponse {
    class: string;
    count: string;
    latest: string;
    location: string;
}
