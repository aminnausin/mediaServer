import type { MediaInfoResource } from '@/contracts/mediaInfo';
import type { AxiosResponse } from 'axios';

import { API } from '@/service/api';

export const getMediaDetails = async (videoId: number): Promise<AxiosResponse<MediaInfoResource>> => {
    const { data } = await API.get(`/videos/${videoId}/info`, { headers: { 'X-Skip-Toast': 'true' } });
    return data;
};
