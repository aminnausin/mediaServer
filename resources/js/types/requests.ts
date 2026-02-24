export interface MetadataStoreRequest extends MetadataUpdateRequest {
    video_id: number;
}

export interface MetadataUpdateRequest {
    title: string;
    description?: string;
    lyrics?: string;
    artist?: string;
    album?: string;
    episode?: string;
    season?: string;
    poster_url?: string;
    released_at?: string;
    video_tags: { name: string; id: number; video_tag_id?: number }[];
    deleted_tags: number[];
    intro_start?: number | null;
    intro_duration: number;
}

export interface LyricsUpdateRequest {
    track?: string;
    artist?: string;
    album?: string;
    lyrics?: string;
}

export interface SeriesStoreRequest extends SeriesUpdateRequest {
    folder_id: number;
}

export interface SeriesUpdateRequest {
    title: string;
    description?: string | null;
    studio?: string | null;
    rating?: string | null;
    seasons?: string | null;
    episodes?: string | null;
    films?: string | null;
    started_at?: string | null;
    ended_at?: string | null;
    thumbnail_url?: string | null;
    avg_intro_duration: number;
    tags: { name: string; id: number; folder_tag_id?: number }[];
    deleted_tags: number[];
}

export interface ChangePasswordRequest {
    current_password: string;
    password: string;
    password_confirmation: string;
}

export interface ChangeEmailRequest {
    email: string;
    password: string;
}

export interface RecordStoreRequest {
    video_id: number;
}

export interface PasswordRequest {
    password: string;
}
