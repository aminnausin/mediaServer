import type { PlaybackProgressStoreRequest } from '@/types/requests';
import { API } from '../api';

export const getProgress = (mediaId: number) => API.get(`/metadata/${mediaId}/progress`);
export const upsertProgress = (mediaId: number, payload: PlaybackProgressStoreRequest) =>
    API.put(`/metadata/${mediaId}/progress`, payload, {
        headers: {
            'X-Skip-Progress': true,
        },
    });
