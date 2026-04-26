import type { FolderTagResource, VideoTagResource } from '@/contracts/tags';
import type { MediaTypeValue } from '@/types/types';

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
    downloads_enabled: boolean;
    downloads_require_auth: boolean;
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
    downloads_enabled: boolean;
}

//#endregion

//#region Media
export interface MetadataResource {
    // Commented out attributes are duplicated in VideoResource?

    // Id
    id: number;
    uuid?: string;

    // Fk
    video_id?: number;
    editor_id?: number;

    // User Editable
    title?: string;
    // description?: string;
    lyrics?: string;
    // episode?: number;
    // season?: number;
    poster_url?: string;
    artist?: string;
    album?: string;
    // released_at?: string;
    // intro_start?: number;
    // intro_duration?: number;
    // video_tags?: VideoTagResource[];

    // API Editable
    // view_count?: number;

    // FFmpeg Generated
    duration?: number;
    // file_size?: number;
    mime_type?: string;
    codec?: string;
    bitrate?: number;
    resolution_width?: number;
    resolution_height?: number;
    frame_rate?: number;
    media_type: MediaTypeValue;

    // Date info
    created_at?: string;
    // updated_at?: string;
    edited_at?: string;

    file_scanned_at?: string;
    file_modified_at?: string;
    first_file_modified_at?: string;
    subtitles_scanned_at?: string;
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
    metadata?: MetadataResource;
    subtitles: SubtitleResource[];
    progress_offset: number;
    progress_percentage: number;
    completion_count: number;
}

export interface SubtitleResource {
    id: number;
    track_id: number;
    metadata_uuid: string;
    language?: string;
    title?: string;
    codec?: string;
    is_default: boolean;
    is_forced: boolean;
}

//#endregion
