/*
    ACTIONS for categories, folders and videos

    Have create, get, update actions
    Run and commit to database (store) -> ?? What does that mean?
*/
import type { MetadataStoreRequest, MetadataUpdateRequest, SeriesStoreRequest, SeriesUpdateRequest } from '@/types/requests';

import { API } from '@/service/api';

export default {
    viewVideo(id: number) {
        return API.patch(`/videos/watch/${id}`);
    },
    createMetadata(data: MetadataStoreRequest) {
        return API.post(`/metadata/`, data);
    },
    updateMetadata(id: number, data: MetadataUpdateRequest) {
        return API.patch(`/metadata/${id}`, data);
    },
    createSeries(data: SeriesStoreRequest) {
        return API.post(`/series`, data);
    },
    updateSeries(id: number, data: SeriesUpdateRequest) {
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

export function getFolders(category_id: number) {
    return API.get(`/folders/?category_id=${category_id}`);
}

export function updateCategory(id: number, data: { default_folder_id: number }) {
    return API.patch(`/categories/${id}`, data);
}

export function getUserViewCount(id: number) {
    return API.get(`/user-view-count/${id}`);
}
