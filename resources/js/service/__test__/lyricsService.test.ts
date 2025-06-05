import { fetchSyncedLyrics, searchSyncedLyrics, updateLyrics, generateLyricsSearchQuery } from '../lyricsService';
import { API } from '../api';
import { describe, expect, it, vi } from 'vitest';

vi.mock('../api', () => ({
    API: {
        get: vi.fn(),
        patch: vi.fn(),
    },
}));

describe('lyricsService', () => {
    it('fetchSyncedLyrics should return syncedLyrics', async () => {
        (API.get as any).mockResolvedValue({ data: { lrclib: { syncedLyrics: 'abc' } } });

        const result = await fetchSyncedLyrics(1, 'track_name=abc');
        expect(result).toEqual({ syncedLyrics: 'abc' });
        expect(API.get).toHaveBeenCalledWith('/metadata/1/lyrics/import?track_name=abc');
    });

    it('searchSyncedLyrics returns array', async () => {
        (API.get as any).mockResolvedValue({ data: { lrclib: [{ id: 1 }] } });

        const result = await searchSyncedLyrics(1);
        expect(result).toEqual([{ id: 1 }]);
    });

    it('generateLyricsSearchQuery should build correct string', () => {
        const query = generateLyricsSearchQuery({ title: 'test', album: 'a', artist: 'b' } as any, 't', 'x', 'y');
        expect(query).toBe('track_name=t&album_name=x&artist_name=y');
    });
});
