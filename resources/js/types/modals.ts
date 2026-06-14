import type { ImageResource, MetadataResource, SeriesResource } from './resources';

export interface SeriesImageEditorProps {
    resource: SeriesResource;
    images: ImageResource[];
    isMajorityAudio: boolean;
    title?: string;
    queryKeys?: string[][];
}

export interface MediaImageEditorProps {
    resource: MetadataResource;
    images: ImageResource[];
    title?: string;
}
