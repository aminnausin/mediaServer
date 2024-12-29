import type { Category, Folder, Metadata, User } from './model';
import type { TaskStatus } from './types';

export interface UserResource {
    id: number;
    name: string;
    email: string;
    last_active: string;
    created_at: string;
}

export interface CategoryResource {
    id: number;
    name: string;
    folders: FolderResource[];
    folders_count: number;
    default_folder_id?: number;
    created_at?: string;
    last_scan: number;
}
export interface FolderResource {
    id: number;
    name: string;
    path: string;
    file_count: number;
    total_size: number;
    category_id: number;
    videos?: VideoResource[];
    series?: SeriesResource;
    created_at?: string;
    last_scan: number;
}
export interface MetadataResource {
    id: number;
    attributes: {
        title?: string;
        description?: string;
        season?: number;
        episode?: number;
        duration?: number;
        view_count?: number;
        file_size?: number;
        date_released?: string;
        date_updated?: string;
    };
    relationships: {
        video_id?: number;
        editor_id?: number;
        // editor_name?: string;
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
        // video_id?: number;
    };
}
export interface RecordResource {
    id: number;
    attributes: {
        // name?: string;
        created_at?: string;
        updated_at?: string;
    };
    relationships: {
        // user_id: number;
        // user_name: string;
        folder?: Folder | { name: string };
        metadata?: Metadata;
        category?: Category;
        video_id?: number;
        video_name?: string;
        file_name?: string;
    };
}
export interface SeriesResource {
    id: number;
    folder_id?: number;
    editor_id?: number;
    // editor_name?: string;
    title?: string;
    description?: string;
    studio?: string;
    rating?: number;
    seasons?: number;
    episodes?: number;
    films?: number;
    date_start?: string;
    date_end?: string;
    thumbnail_url?: string;
    date_updated?: string;
}
export interface TagResource {
    id: number;
    name: string;
    relationships: {
        creator_id?: number;
    };
}
// export interface UserResource {
//     id: number;
//     name: string;
//     email: string;
//     email_verified_at?: string;
//     created_at?: string;
//     updated_at?: string;
// };
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
    view_count?: number;
    file_size?: number;
    video_tags: VideoTagResource[];
    date_released?: string;
    date_updated?: string;
    // folder_id: number;
    metadata?: Metadata;
    // editor?: User;
}
export interface VideoTagResource {
    video_tag_id: number; // video tag (this) id
    name?: string;
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
    started_at?: Date;
    ended_at?: Date;
    created_at: Date;
    updated_at: Date;
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
    started_at?: Date;
    ended_at?: Date;
    created_at: Date;
    updated_at: Date;
}
