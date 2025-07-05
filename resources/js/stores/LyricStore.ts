import type { VideoResource } from '@/types/resources';
import type { LrcLibResult } from '@/types/types';
import type { Metadata } from '@/types/model';
import type { Ref } from 'vue';

import { fetchSyncedLyrics, searchSyncedLyrics } from '@/service/lyricsService';
import { defineStore, storeToRefs } from 'pinia';
import { computed, ref, watch } from 'vue';
import { useContentStore } from '@/stores/ContentStore';
import { toast } from '@/service/toaster/toastService';

import useModal from '@/composables/useModal';

type SelectedLyric = (LrcLibResult & { source: 'search' | 'generated' }) | null;

export const useLyricStore = defineStore('Lyric', () => {
    const { stateVideo } = storeToRefs(useContentStore()) as unknown as { stateVideo: Ref<VideoResource, VideoResource> };

    const editLyricsModal = useModal({ title: 'Edit Song Lyrics', submitText: 'Submit Changes' });

    const searchResults = ref<LrcLibResult[]>([]); // Holds search results
    const hasSearchedForLyrics = ref<boolean>(false);
    const isLoadingLyrics = ref<boolean>(false);

    const dirtyLyric = ref<SelectedLyric>(null);
    const stateLyrics = computed(() => {
        return dirtyLyric.value?.syncedLyrics ?? stateVideo.value.metadata?.lyrics ?? ''; // Holds stateVideo.lyrics (originally) and then any modifications made by user or loaded from import / search functions
    });

    const handleSelectLyrics = (result: LrcLibResult) => {
        dirtyLyric.value = { ...result, source: 'search' };
    };

    const handlePreviewLyrics = (result: LrcLibResult) => {
        if (!result.syncedLyrics) return;
        handleSelectLyrics(result);
        editLyricsModal.toggleModal(false);
    };

    const handleOpenLyricsModal = async () => {
        try {
            validateData(stateVideo.value.metadata);
            editLyricsModal.toggleModal(true);
        } catch (error: unknown) {
            if (error instanceof Error) toast.error(error.message);
        }
    };

    const handleSearchSyncedLyrics = async (query?: string) => {
        try {
            const metadata = validateData(stateVideo.value.metadata);

            searchResults.value = [];

            const results = await runLyricsRequest(() => searchSyncedLyrics(metadata.id, query));
            searchResults.value = results as LrcLibResult[];
            hasSearchedForLyrics.value = true;
        } catch (error: unknown) {
            if (error instanceof Error) toast.error(error.message);
        }
    };

    const handleGenerateLyrics = async () => {
        try {
            const metadata = validateData(stateVideo.value.metadata);
            const result = await runLyricsRequest(() => fetchSyncedLyrics(metadata.id));

            if (!result?.syncedLyrics) {
                toast.error('Lyrics not found...', { description: 'Try searching for options from the edit menu' });
                return;
            }

            searchResults.value = [result];
            dirtyLyric.value = { ...result, source: 'generated' };
        } catch (error: unknown) {
            if (error instanceof Error) toast.error(error.message);
        }
    };

    const runLyricsRequest = async <T>(requestFn: () => Promise<T>, noResultsMessage = 'Lyrics not found...'): Promise<T | null> => {
        isLoadingLyrics.value = true;

        try {
            const results = await requestFn();

            const hasResults = results !== null && results !== undefined;
            if (!hasResults) {
                toast.error(noResultsMessage);
                return null;
            }

            return results;
        } catch (error) {
            toast.error('An error occurred while fetching lyrics.');
            console.error(error);
            return null;
        } finally {
            isLoadingLyrics.value = false;
        }
    };

    const validateData = (metadata: typeof stateVideo.value.metadata): Metadata => {
        if (!metadata) {
            toast.error('Data is malformed.');
            throw new Error('Metadata is missing');
        }
        return metadata;
    };

    const resetLyrics = () => {
        dirtyLyric.value = null;
        searchResults.value = [];
        hasSearchedForLyrics.value = false;
    };

    watch(() => stateVideo.value, resetLyrics, { immediate: true });

    return {
        dirtyLyric,
        stateLyrics,
        searchResults,
        isLoadingLyrics,
        editLyricsModal,
        hasSearchedForLyrics,
        handleSelectLyrics,
        handlePreviewLyrics,
        handleOpenLyricsModal,
        handleSearchSyncedLyrics,
        handleGenerateLyrics,
        resetLyrics,
    };
});
