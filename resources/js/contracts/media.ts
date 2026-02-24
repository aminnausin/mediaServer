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
    category_id: number;
    name: string;
    path: string;
    created_at?: string;
    last_scan: number;

    title: string;
    episodes?: number;
    file_count: number;
    total_size: number;
    is_majority_audio: boolean;
    videos: VideoResource[];
    series?: SeriesResource;
    scanned_at?: string;
    updated_at?: string;
    edited_at?: string;
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
    avg_intro_duration: number;
    started_at?: string;
    ended_at?: string;
    thumbnail_url?: string;
    folder_tags?: FolderTagResource[];
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
        artist?: string;
        album?: string;
        view_count?: number;
        file_size?: number;
        intro_start?: number;
        intro_duration?: number;
        released_at?: string;
        file_modified_at?: string;
        edited_at?: string;
        updated_at?: string;
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
    intro_start?: number;
    intro_duration?: number;
    created_at: string;
    released_at?: string;
    updated_at?: string;
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
