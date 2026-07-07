import type { FolderResource, ImageResource, MetadataResource, SeriesResource, VideoResource } from './resources';

export interface FolderImageEditorProps {
    resource: SeriesResource;
    folderResource: FolderResource;
    images: ImageResource[];
    isMajorityAudio: boolean;
    title?: string;
    queryKeys?: string[][];
}

export interface MediaImageEditorProps {
    resource: MetadataResource;
    mediaResource: VideoResource;
    images: ImageResource[];
    title?: string;
}

export interface MediaMetadataEditorProps {
    mediaResource: VideoResource;
    title?: string;
}

export interface FolderMetadataEditorProps {
    cachedFolder: FolderResource;
    queryKeys: string[][];
    title?: string;
}
