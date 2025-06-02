import { API } from '@/service/api';

export async function fetchSyncedLyrics(metadataId: number, query?: string) {
    const { data } = await API.get(`/metadata/${metadataId}/lyrics/import?${query ?? ''}`);
    return data.lrclib?.syncedLyrics ?? null;
}

export async function searchSyncedLyrics(metadataId: number, query?: string) {
    const { data } = await API.get(`/metadata/${metadataId}/lyrics/search?${query ?? ''}`);
    return data.lrclib ?? null;
}
