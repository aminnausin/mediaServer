import type { MediaInfoResource } from '@/contracts/mediaInfo';

import { API } from '@/service/api';

export const getMediaDetails = async (videoId: number): Promise<MediaInfoResource> => {
    const { data } = await API.get(`/videos/${videoId}/info`, { headers: { 'X-Skip-Toast': 'true' } });
    return data;
};
