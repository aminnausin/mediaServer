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
    thumbnail_url?: string;
    folder_tags?: FolderTagResource[];
    created_at?: string;
    updated_at?: string;
    edited_at?: string;
    date_start?: string;
    date_end?: string;
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
        date_released?: string;
        date_updated?: string;
        date_uploaded?: string;
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
    date: string;
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
    date_released?: string;
    date_updated?: string;
    date_uploaded?: string;
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
