import type { MediaTypeValue } from '@/types/types';

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
    lyrics?: string;
    date_released?: string;
    tags?: string;
    editor_id?: number;
    created_at?: string;
    updated_at?: string;
    uuid?: any;
    file_size?: number;
    mime_type?: string;
    codec?: string;
    bitrate?: number;
    resolution_width?: number;
    resolution_height?: number;
    frame_rate?: number;
    date_released_formatted: any;
    video?: Video;
    editor?: User;
    poster_url?: string;
    playbacks?: Playback[];
    artist?: string;
    album?: string;
    video_tags?: VideoTag[];
    media_type: MediaTypeValue;
    file_scanned_at?: string;
    subtitles_scanned_at?: string;
}

export interface Subtitle {
    id: number;
    track_id: number;
    metadata_uuid: string;
    language?: string;
    codec?: string;
    format?: string;
    path?: string;
    created_at: string;
    updated_at: string;
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
    started_at?: string;
    ended_at?: string;
    thumbnail_url?: string;
    editor_id?: number;
    created_at?: string;
    updated_at?: string;
    folder?: Folder;
    editor?: User;
    folder_tags?: FolderTag[];
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
export interface Tag {
    id: number;
    creator_id?: number;
    name: string;
    created_at?: string;
    updated_at?: string;
}
export interface VideoTag {
    id: number;
    tag_id: number;
    metadata_id: number;
    created_at?: string;
    updated_at?: string;
}
export interface FolderTag {
    id: number;
    tag_id: number;
    series_id: number;
    created_at?: string;
    updated_at?: string;
}

export interface Session {
    id: string;
    ip_address: string;
    user_agent: string;
    is_current: boolean;
    last_active: string;
}
