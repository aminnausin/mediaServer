import { API } from '@/service/api';

export async function fetchSyncedLyrics(metadataId: number, query?: string) {
    const { data } = await API.get(`/metadata/${metadataId}/import/lyrics?${query ?? ''}`);
    return data.lrclib?.syncedLyrics ?? null;
}
