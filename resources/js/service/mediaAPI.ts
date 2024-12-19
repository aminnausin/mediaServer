/*
    ACTIONS for categories, folders and videos

    Have create, get, update actions
    Run and commit to database (store) -> ?? What does that mean?
*/
import type { MetadataResource, SeriesResource, VideoResource } from '@/types/resources';
import { API } from './api';

export default {
    viewVideo(id: number) {
        return API.patch(`/videos/watch/${id}`);
    },
    updateVideo(id: number, data: VideoResource) {
        return API.patch(`/videos/${id}`, data);
    },
    createMetadata(data: MetadataResource) {
        return API.post(`/metadata/`, data);
    },
    updateMetadata(id: number, data: MetadataResource) {
        return API.patch(`/metadata/${id}`, data);
    },
    createSeries(data: SeriesResource) {
        return API.post(`/series`, data);
    },
    updateSeries(id: number, data: SeriesResource) {
        return API.patch(`/series/${id}`, data);
    },
    getCategory(query: string) {
        return API.get(`/${query}`);
    },
    getFolder(id: number) {
        return API.get(`/folders/${id}?videos=true`);
    },
    getVideos(data: { folder_id: number }) {
        return API.get(`/videos?folder_id=${data.folder_id}`);
    },
    getTags() {
        return API.get('/tags');
    },
    createTag(data: { name: string }) {
        return API.post('/tags', data);
    },
    getPlayback(id: number) {
        return API.get(`/playback/${id}`);
    },
    createPlayback(data: { entries: { metadata_id: number; progress: number }[] }) {
        return API.post('/playback', data);
    },
};

export function getCategories() {
    return API.get(`/categories`);
}
