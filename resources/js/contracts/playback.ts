import type { Category, Metadata } from '@/types/model';

export interface PlaybackResource {
    id: number;
    attributes: {
        progress: number;
    };
    relationships: {
        metadata_id?: number;
    };
}
export interface RecordResource {
    id: number;
    metadata?: Metadata;
    category?: Category;
    video_id?: number; // from metadata so eventually remove
    file_name?: string;
    video_name?: string;
    folder_name?: string;
    created_at?: string;
    updated_at?: string;
}
