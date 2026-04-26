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
    metadata?: { id: number };
    category?: { name: string };
    video_id?: number; // from metadata so eventually remove
    file_name?: string;
    video_name?: string;
    folder_name?: string;
    created_at?: string;
    updated_at?: string;
}
