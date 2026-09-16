import type { FontResource, ImageResource, StoryboardResource, SubtitleResource } from './media';

export interface SubtitleInfoResource extends SubtitleResource {
    path: string;
    external_path: string | null;
    created_at: string;
    updated_at: string;
}

export interface FontInfoResource extends FontResource {
    id: number;
    size: number;
    created_at: string;
    updated_at: string;
}

export interface MediaInfoResource {
    id: number;
    name: string;

    images: ImageResource[];
    subtitles: SubtitleInfoResource[];
    fonts: FontInfoResource[];
    storyboard?: StoryboardResource;
    raw_metadata: any;
}
