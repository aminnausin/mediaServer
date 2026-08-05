import type { FolderResource, VideoResource } from '@/contracts/media';

import { API } from '@/service/api';

export const getContinueWatching = async (): Promise<VideoResource[]> => {
    const { data } = await API.get('/explore/continue-watching');
    return data.data;
};

export const getRecentlyReleased = async (): Promise<FolderResource[]> => {
    const { data } = await API.get('/explore/recently-released');
    return data.data;
};

export const getRecentlyUpdated = async (): Promise<FolderResource[]> => {
    const { data } = await API.get('/explore/recently-updated');
    return data.data;
};

export const getRecentlyAdded = async (): Promise<FolderResource[]> => {
    const { data } = await API.get('/explore/recently-added');
    return data.data;
};

export const getRecentlyUploaded = async (mediaType?: 'video' | 'audio'): Promise<VideoResource[]> => {
    const { data } = await API.get(`/explore/recently-uploaded`, {
        params: mediaType ? { type: mediaType } : undefined,
    });
    return data.data;
};
