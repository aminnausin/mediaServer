import type { PlaybackProgressStoreRequest } from '@/types/requests';
import type { PlaybackProgressResponse } from '@/types/types';

import { API } from '../api';

export const getProgress = (mediaId: number) => API.get(`/metadata/${mediaId}/progress`);
export const upsertProgress = (mediaId: number, payload: PlaybackProgressStoreRequest) =>
    API.put<PlaybackProgressResponse>(`/metadata/${mediaId}/progress`, payload, {
        headers: {
            'X-Skip-Progress': true,
        },
    });
