/*
    ACTIONS for categories, folders and videos

    Have create, get, update actions
    Run and commit to database (store) -> ?? What does that mean?
*/
import { API } from './api';

export default {
    viewVideo(id: number) {
        return API.patch(`/videos/watch/${id}`);
    },
    updateVideo(id: number, data) {
        return API.patch(`/videos/${id}`, data);
    },
    createMetadata(data) {
        return API.post(`/metadata/`, data);
    },
    updateMetadata(id: number, data) {
        return API.patch(`/metadata/${id}`, data);
    },
    createSeries(data) {
        return API.post(`/series`, data);
    },
    updateSeries(id: number, data) {
        return API.patch(`/series/${id}`, data);
    },
    getCategory(query) {
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
