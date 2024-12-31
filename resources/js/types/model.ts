/* eslint-disable @typescript-eslint/no-explicit-any */
export interface Category {
    id: number;
    name: string;
    media_content: boolean;
    folders?: Folder[];
}
export interface Folder {
    id: number;
    category_id: number;
    name: string;
    path: string;
    videos?: Video[];
    series?: Series;
    category?: Category;
}
export interface Metadata {
    id: number;
    video_id?: number;
    composite_id: string;
    title?: string;
    season?: number;
    episode?: number;
    duration?: number;
    view_count?: number;
    description?: string;
    date_released?: string;
    tags?: string;
    editor_id?: number;
    created_at?: string;
    updated_at?: string;
    uuid?: any;
    file_size?: number;
    date_scanned?: string;
    date_released_formatted: any;
    video?: Video;
    editor?: User;
    poster_url?: string;
    playbacks?: Playback[];
    video_tags?: VideoTag[];
}
export interface Playback {
    id: number;
    metadata_id?: number;
    progress: number;
    created_at?: string;
    updated_at?: string;
    count: number;
    metadata?: Metadata;
}
export interface Record {
    id: number;
    user_id: number;
    video_id?: number;
    name?: string;
    created_at?: string;
    updated_at?: string;
    metadata_id?: number;
    user?: User;
    video?: Video;
    metadata?: Metadata;
}
export interface Series {
    id: number;
    folder_id?: number;
    composite_id: string;
    title?: string;
    description?: string;
    studio?: string;
    rating?: any;
    seasons?: number;
    episodes?: number;
    films?: number;
    date_start?: string;
    date_end?: string;
    thumbnail_url?: string;
    editor_id?: number;
    created_at?: string;
    updated_at?: string;
    folder?: Folder;
    editor?: User;
}
export interface Tag {
    id: number;
    creator_id?: number;
    name: string;
    created_at?: string;
    updated_at?: string;
    creator?: User;
    metadata?: Metadata;
}
export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    created_at?: string;
    updated_at?: string;
}
export interface Video {
    id: number;
    folder_id: number;
    name: string;
    path: string;
    date: string;
    title?: string;
    duration?: number;
    episode?: number;
    season?: number;
    view_count?: number;
    description?: string;
    uuid?: any;
    folder?: Folder;
    metadata?: Metadata;
}
export interface VideoTag {
    id: number;
    tag_id: number;
    metadata_id: number;
    created_at?: string;
    updated_at?: string;
    metadata?: Metadata;
    tag?: Tag;
}
