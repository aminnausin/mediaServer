import type { LyricsUpdateRequest } from '@/types/requests';
import type { LrcLibResult } from '@/types/types';
import type { Metadata } from '@/types/model';

import { API } from '@/service/api';

interface LrcLibFetchResponse {
    lrclib: LrcLibResult;
    payload: LrcLibPayload;
}

interface LrcLibSearchResponse {
    lrclib: LrcLibResult[];
    payload: LrcLibPayload;
}

interface LrcLibPayload {
    artist_name: string;
    duration: number;
    track_name: string;
}

interface LrcLibResponse<T> {
    data: T;
}

export async function fetchSyncedLyrics(metadataId: number, query?: string) {
    const { data }: LrcLibResponse<LrcLibFetchResponse> = await API.get(`/metadata/${metadataId}/lyrics/import?${query ?? ''}`);

    return data.lrclib ?? null;
}

export async function searchSyncedLyrics(metadataId: number, query?: string) {
    const { data }: LrcLibResponse<LrcLibSearchResponse> = await API.get(`/metadata/${metadataId}/lyrics/search?${query ?? ''}`);

    return data.lrclib ?? [];
}

export function updateLyrics(id: number, data: LyricsUpdateRequest) {
    return API.patch(`/metadata/${id}/lyrics`, data);
}

export const generateLyricsSearchQuery = (metadata?: Metadata, track?: string, album?: string, artist?: string): string | undefined => {
    if (!metadata) return undefined;

    const params = new URLSearchParams();

    params.set('track_name', `${track ?? metadata.title}`);

    if (album && album !== metadata.album) {
        params.set('album_name', album);
    }

    if (artist && artist !== metadata.artist) {
        params.set('artist_name', artist);
    }

    return params.toString();
};
