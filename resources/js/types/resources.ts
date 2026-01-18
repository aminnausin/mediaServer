import type { Category, Metadata } from '@/types/model';
import type { TaskStatus } from '@/types/types';

export interface UserResource {
    id: number;
    name: string;
    email: string;
    last_active: string;
    created_at: string;
    avatar?: string;
}

export interface ProfileResource {
    id: number;
    name: string;
    last_active: string;
    created_at: string;
    avatar?: string;
}

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
    last_scan: number;
}
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
    // attributes: {
    created_at?: string;
    updated_at?: string;
    // };
    // relationships: {
    // folder?: Folder | { name: string };
    metadata?: Metadata;
    category?: Category;
    video_id?: number; // from metadata so eventually remove
    video_name?: string;
    folder_name?: string;
    file_name?: string;
}
// }
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
    date_start?: string;
    date_end?: string;
    date_updated?: string;
    thumbnail_url?: string;
}
export interface TagResource {
    id: number;
    name: string;
    relationships: {
        creator_id: number | null;
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

export interface VideoTagResource {
    video_tag_id: number; // video tag (this) id
    name: string;
    id: number; // tag id
}

export interface FolderTagResource {
    folder_tag_id: number; // folder tag (this) id
    name: string;
    id: number; // tag id
}

export interface TaskResource {
    id: number;
    user: string;
    status: TaskStatus;
    status_key: number;
    name?: string;
    summary?: string;
    description?: string;
    url?: string;
    sub_tasks: SubTaskResource[];
    sub_tasks_total: number;
    sub_tasks_pending: number;
    sub_tasks_complete: number;
    sub_tasks_failed: number;
    duration: number;
    started_at?: string;
    ended_at?: string;
    created_at: string;
    updated_at: string;
}

export interface SubTaskResource {
    id: number;
    task_id: number;
    status: TaskStatus;
    status_key: number;
    name?: string;
    summary?: string;
    progress: number;
    duration: number;
    started_at?: string;
    ended_at?: string;
    created_at: string;
    updated_at: string;
}
