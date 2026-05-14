export type ServerConfigGroup = 'scanner' | 'metadata' | 'media' | 'performance' | 'storage';
export type ServerConfigType = 'string' | 'bool' | 'integer' | 'array' | 'float';

export interface ServerConfig {
    values: {
        media: MediaConfigRequest;
        performance: PerformanceConfigRequest;
        storage: StorageConfigRequest;
        scanner: ScannerConfigRequest;
    };
    defaults: {
        media: MediaConfigRequest;
        performance: PerformanceConfigRequest;
        storage: StorageConfigRequest;
        scanner: ScannerConfigRequest;
    };
}

export interface ScannerConfigRequest {
    uuid_embed: boolean;
    uuid_write_cache: boolean;
    attachments_extract: boolean;
    thumbnails_generate: boolean;
    art_extract: boolean;
}

export interface StorageConfigRequest {
    cache_path?: string;
    metadata_path?: string;
}

export interface PerformanceConfigRequest {
    max_scan_workers: number;
    max_event_workers: number;
}

export interface MediaConfigRequest {
    supported_extensions: string[];
}
