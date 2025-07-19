// src/stores/__tests__/useLyricStore.test.ts
import { describe, beforeEach, expect, it, vi } from 'vitest';
import { setActivePinia, createPinia } from 'pinia';
import { useContentStore } from '@/stores/ContentStore';
import { useLyricStore } from '@/stores/LyricStore';
import { faker } from '@faker-js/faker';
import { ref } from 'vue';

vi.mock('@/service/lyricsService', () => ({
    fetchSyncedLyrics: vi.fn(() => Promise.resolve({ syncedLyrics: 'some lyrics' })),
    searchSyncedLyrics: vi.fn(() => Promise.resolve([{ syncedLyrics: faker.lorem }, { syncedLyrics: faker.animal.cat() }])),
}));

describe('useLyricStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia());
    });

    it('resets lyrics properly', () => {
        const store = useLyricStore();
        store.searchResults = [{ syncedLyrics: 'test' } as any];
        store.dirtyLyric = {
            id: faker.number.int({ min: 1000 }),
            name: faker.music.songName(),
            trackName: faker.music.songName(),
            artistName: faker.music.artist(),
            albumName: faker.music.album(),
            duration: faker.number.int({ min: 1, max: 500 }),
            syncedLyrics: 'abc',
            source: 'search',
        };
        store.hasSearchedForLyrics = true;

        store.resetLyrics();
        expect(store.dirtyLyric).toBe(null);
        expect(store.searchResults).toEqual([]);
        expect(store.hasSearchedForLyrics).toBe(false);
    });

    it('calls fetchSyncedLyrics and updates store', async () => {
        const contentStore = useContentStore();
        contentStore.stateVideo = ref({ metadata: { id: 1, lyrics: '' } }) as any;

        const store = useLyricStore();
        await store.handleGenerateLyrics();

        expect(store.searchResults.length).toBe(1);
        expect(store.dirtyLyric?.syncedLyrics).toBe('some lyrics');
    });
});
