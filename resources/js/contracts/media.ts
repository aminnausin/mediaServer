import type { FolderTagResource, VideoTagResource } from '@/contracts/tags';
import type { Metadata } from '@/types/model';

export interface CategoryResource {
    id: number;
    name: string;
    folders: FolderResource[];
    folders_count: number;
    videos_count?: number;
    total_size: number;
    default_folder_id?: number;
    created_at?: string;
    last_scan: number;
    is_private?: boolean;
}

//#region Folders
export interface FolderResource {
    id: number;
    name: string;
    title: string;
    path: string;
    file_count: number;
    total_size: number;
    is_majority_audio: boolean;
    category_id: number;
    videos: VideoResource[];
    series?: SeriesResource;
    scanned_at?: string;
    created_at?: string;
    updated_at?: string;
    edited_at?: string;
    last_scan: number;
}

export interface SeriesResource {
    id: number;
    folder_id?: number;
    editor_id?: number;
    title?: string;
    description?: string;
    studio?: string;
    rating?: number;
    seasons?: number;
    episodes?: number;
    films?: number;
    folder_tags?: FolderTagResource[];
    started_at?: string;
    ended_at?: string;
    thumbnail_url?: string;
    created_at?: string;
    updated_at?: string;
    edited_at?: string;
}

//#endregion

//#region Media
export interface MetadataResource {
    id: number;
    attributes: {
        title?: string;
        description?: string;
        poster_url?: string;
        season?: number;
        episode?: number;
        duration?: number;
        view_count?: number;
        file_size?: number;
        released_at?: string;
        file_modified_at?: string;
        edited_at?: string;
        date_updated?: string;
    };
    relationships: {
        video_id?: number;
        editor_id?: number;
        video_tags?: VideoTagResource[];
    };
}

export interface VideoResource {
    id: number;
    name: string;
    path: string;
    title?: string;
    description?: string;
    duration?: number;
    episode?: number;
    season?: number;
    artist?: string;
    album?: string;
    view_count: number;
    file_size?: number;
    video_tags: VideoTagResource[];
    date_created: string;
    released_at?: string;
    date_updated?: string;
    file_modified_at?: string;
    edited_at?: string;
    metadata?: Metadata;
    subtitles: SubtitleResource[];
}

export interface SubtitleResource {
    id: number;
    track_id: number;
    metadata_uuid: string;
    language?: string;
    codec?: string;
    is_default: boolean;
    is_forced: boolean;
}

//#endregion
