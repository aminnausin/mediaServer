import type { FolderResource, VideoResource } from '@/contracts/media';

import { API } from '@/service/api';

export const getContinueWatching = async (): Promise<VideoResource[]> => {
    const { data } = await API.get('/home/continue-watching');
    return data.data;
};

export const getRecentlyReleased = async (): Promise<FolderResource[]> => {
    const { data } = await API.get('/home/recently-released');
    return data.data;
};

export const getRecentlyUpdated = async (): Promise<FolderResource[]> => {
    const { data } = await API.get('/home/recently-updated');
    return data.data;
};

export const getRecentlyAdded = async (): Promise<FolderResource[]> => {
    const { data } = await API.get('/home/recently-added');
    return data.data;
};

export const getRecentlyUploaded = async (mediaType?: 'video' | 'audio'): Promise<VideoResource[]> => {
    const { data } = await API.get(`/home/recently-uploaded${mediaType ? `?type=${mediaType}` : ''}`);
    return data.data;
};
