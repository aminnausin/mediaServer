export interface HttpResponse {
    success?: 'true' | 'false';
    status?: string;
    message?: string | null;
    data: string | number | string[] | number[] | null;
}

export interface PulseResponse {
    cache?: any;
    exceptions?: any;
    queues?: any;
    servers?: {
        servers: { [key: string]: PulseServerResponse };
        runAt: string;
        time: number;
    };
    slow_jobs?: any;
    slow_outgoing_requests?: any;
    slow_queries?: any;
    slow_requests?: any;
    usage?: any;
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

export interface ChartData {
    labels: string[];
}

export interface ChartOptions {}
