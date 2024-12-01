import type { User } from './model';

export type CategoryResource = {
    id: number;
    name: string;
    folders_count: number;
    folders: FolderResource[];
};
export type FolderResource = {
    id: number;
    name: string;
    path: string;
    file_count: number;
    category_id: number;
    videos?: VideoResource[];
    series?: SeriesResource;
};
export type MetadataResource = {
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
        editor_name?: string;
        video_tags?: VideoTagResource[];
    };
};
export type PlaybackResource = {
    id: number;
    attributes: {
        progress: number;
    };
    relationships: {
        metadata_id?: number;
        video_id?: number;
    };
};
export type RecordResource = {
    id: number;
    attributes?: {
        name?: string;
        created_at?: string;
        updated_at?: string;
    };
    relationships: {
        user_id: number;
        user_name: string;
        video_id?: number;
        video_name?: string;
        file_name?: string;
        folder_id?: number;
        folder_name?: string;
        category_name?: string;
        metadata_id?: number | 'None';
    };
};
export type SeriesResource = {
    id: number;
    folder_id?: number;
    editor_id?: number;
    editor_name?: string;
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
};
export type TagResource = {
    id: number;
    name: string;
    relationships: {
        creator_id?: number;
    };
};
// export type UserResource = {
//     id: number;
//     name: string;
//     email: string;
//     email_verified_at?: string;
//     created_at?: string;
//     updated_at?: string;
// };
export type VideoResource = {
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
    folder_id: number;
    metadata?: MetadataResource;
    editor?: User;
};
export type VideoTagResource = {
    video_tag_id: number; // video tag (this) id
    id: number; // tag id
    name?: string;
};
